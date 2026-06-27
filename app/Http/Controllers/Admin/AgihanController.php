<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Notifications\AgihanSelesaiNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class AgihanController extends Controller
{
    public function index()
    {
        $approvedStatuses = Permohonan::statusValuesFor(Permohonan::STATUS_DILULUSKAN);

        $agihanRows = Permohonan::query()
            ->with([
                'pelajar:id,permohonan_id,nama_penuh,no_matrik',
                'bantuan:id,permohonan_id,jenis_bantuan,kategori_bantuan',
                'diagihOleh:id,name',
            ])
            ->whereIn('status', $approvedStatuses)
            ->latest('admin_review_date')
            ->latest('tarikh_mohon')
            ->latest('id')
            ->get();

        $distributionStats = [
            [
                'label' => 'Diluluskan',
                'value' => $agihanRows->count(),
                'color' => '#10b981',
                'badge' => 'Masuk aliran agihan',
                'class' => 'bg-emerald-50 text-emerald-700',
            ],
            [
                'label' => 'Menunggu Agihan',
                'value' => $agihanRows
                    ->where('status_agihan_key', Permohonan::STATUS_AGIHAN_BELUM_DIAGIH)
                    ->count(),
                'color' => '#f59e0b',
                'badge' => 'Perlu tindakan',
                'class' => 'bg-amber-50 text-amber-700',
            ],
            [
                'label' => 'Dalam Penghantaran',
                'value' => $agihanRows
                    ->where('status_agihan_key', Permohonan::STATUS_AGIHAN_SEDANG_DIAGIH)
                    ->count(),
                'color' => '#3b82f6',
                'badge' => 'Dalam proses',
                'class' => 'bg-blue-50 text-blue-700',
            ],
            [
                'label' => 'Selesai Disalurkan',
                'value' => $agihanRows
                    ->where('status_agihan_key', Permohonan::STATUS_AGIHAN_SELESAI)
                    ->count(),
                'color' => '#10b981',
                'badge' => 'Lengkap',
                'class' => 'bg-emerald-50 text-emerald-700',
            ],
        ];

        $totalDistribution = $agihanRows->count();
        $chartStats = collect($distributionStats)->slice(1)->values();
        $distributionChart = [
            'labels' => $chartStats->pluck('label')->values()->all(),
            'data' => $chartStats->pluck('value')->values()->all(),
            'colors' => $chartStats->pluck('color')->values()->all(),
        ];

        return view('admin.agihan.index', compact(
            'agihanRows',
            'distributionStats',
            'totalDistribution',
            'distributionChart'
        ));
    }

    public function mula(Permohonan $permohonan)
    {
        if (! $this->isApproved($permohonan)) {
            return redirect()
                ->route('admin.agihan.index')
                ->with('warning', 'Hanya permohonan yang diluluskan boleh diagihkan.');
        }

        if ($permohonan->status_agihan_key !== Permohonan::STATUS_AGIHAN_BELUM_DIAGIH) {
            return redirect()
                ->route('admin.agihan.index')
                ->with('info', 'Status agihan permohonan ini telah dikemaskini.');
        }

        $permohonan->update([
            'status_agihan' => Permohonan::STATUS_AGIHAN_SEDANG_DIAGIH,
            'diagih_oleh' => request()->user()?->id,
        ]);

        return redirect()
            ->route('admin.agihan.index')
            ->with('success', 'Agihan bantuan telah dimulakan.');
    }

    public function selesai(Request $request, Permohonan $permohonan)
    {
        if (! $this->isApproved($permohonan)) {
            return redirect()
                ->route('admin.agihan.index')
                ->with('warning', 'Hanya permohonan yang diluluskan boleh disahkan selesai.');
        }

        if ($permohonan->status_agihan_key !== Permohonan::STATUS_AGIHAN_SEDANG_DIAGIH) {
            return redirect()
                ->route('admin.agihan.index')
                ->with('info', 'Sila mulakan agihan sebelum mengesahkan selesai.');
        }

        $validated = $request->validate([
            'catatan_agihan' => 'nullable|string|max:5000',
            'bukti_agihan' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $buktiPath = $request->file('bukti_agihan')->store('agihan-bukti');

        if (! $buktiPath) {
            return redirect()
                ->route('admin.agihan.index')
                ->withErrors(['bukti_agihan' => 'Bukti agihan gagal dimuat naik. Sila cuba semula.'])
                ->withInput();
        }

        $permohonan->update([
            'status_agihan' => Permohonan::STATUS_AGIHAN_SELESAI,
            'tarikh_agihan' => now(),
            'diagih_oleh' => $permohonan->diagih_oleh ?: $request->user()->id,
            'catatan_agihan' => $validated['catatan_agihan'] ?? null,
            'bukti_agihan' => $buktiPath,
        ]);

        $mailSent = $this->sendAgihanSelesaiEmail($permohonan->fresh(['user', 'pelajar', 'bantuan']));
        $redirect = redirect()
            ->route('admin.agihan.index')
            ->with('success', 'Bantuan berjaya diagihkan kepada pelajar.');

        if (! $mailSent) {
            $redirect->with('info', 'Emel notifikasi pelajar tidak dihantar kerana emel tiada atau konfigurasi emel gagal.');
        }

        return $redirect;
    }

    public function bukti(Request $request, Permohonan $permohonan)
    {
        abort_unless(filled($permohonan->bukti_agihan), 404);
        abort_unless(Storage::disk('local')->exists($permohonan->bukti_agihan), 404);

        if ($request->boolean('download')) {
            return Storage::disk('local')->download($permohonan->bukti_agihan);
        }

        return Storage::disk('local')->response($permohonan->bukti_agihan);
    }

    public function laporan()
    {
        $approvedStatuses = Permohonan::statusValuesFor(Permohonan::STATUS_DILULUSKAN);
        $currentYear = now()->year;
        $monthLabels = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'];

        $agihanRows = Permohonan::query()
            ->with([
                'pelajar:id,permohonan_id,nama_penuh,no_matrik',
                'bantuan:id,permohonan_id,jenis_bantuan,kategori_bantuan',
                'diagihOleh:id,name',
            ])
            ->whereIn('status', $approvedStatuses)
            ->latest('updated_at')
            ->latest('id')
            ->get();

        $menungguAgihan = $agihanRows
            ->where('status_agihan_key', Permohonan::STATUS_AGIHAN_BELUM_DIAGIH)
            ->count();
        $sedangDiagih = $agihanRows
            ->where('status_agihan_key', Permohonan::STATUS_AGIHAN_SEDANG_DIAGIH)
            ->count();
        $selesaiDiagih = $agihanRows
            ->where('status_agihan_key', Permohonan::STATUS_AGIHAN_SELESAI)
            ->count();
        $menungguBukti = $agihanRows
            ->where('status_agihan_key', Permohonan::STATUS_AGIHAN_SEDANG_DIAGIH)
            ->filter(fn (Permohonan $permohonan) => blank($permohonan->bukti_agihan))
            ->count();

        $statusData = [
            [
                'label' => 'Selesai Diagih',
                'value' => $selesaiDiagih,
                'color' => '#10b981',
                'class' => 'bg-emerald-50 text-emerald-700',
            ],
            [
                'label' => 'Sedang Diagih',
                'value' => $sedangDiagih,
                'color' => '#0284c7',
                'class' => 'bg-sky-50 text-sky-700',
            ],
            [
                'label' => 'Menunggu Bukti Agihan',
                'value' => $menungguBukti,
                'color' => '#f59e0b',
                'class' => 'bg-amber-50 text-amber-700',
            ],
        ];

        $monthlyAgihanData = collect(range(1, 12))
            ->map(fn (int $month) => (int) Permohonan::query()
                ->whereIn('status', $approvedStatuses)
                ->where('status_agihan', Permohonan::STATUS_AGIHAN_SELESAI)
                ->whereYear('tarikh_agihan', $currentYear)
                ->whereMonth('tarikh_agihan', $month)
                ->count())
            ->values()
            ->all();

        $summary = [
            'total' => $agihanRows->count(),
            'menunggu_agihan' => $menungguAgihan,
            'sedang_diagih' => $sedangDiagih,
            'selesai' => $selesaiDiagih,
            'menunggu_bukti' => $menungguBukti,
            'dalam_proses' => $menungguAgihan + $sedangDiagih,
        ];

        $latestAgihan = $agihanRows->take(10)->values();
        $total = collect($statusData)->sum('value');

        return view('admin.statistik.agihan', compact(
            'statusData',
            'summary',
            'latestAgihan',
            'total',
            'currentYear',
            'monthLabels',
            'monthlyAgihanData'
        ));
    }

    public function laporanCsv()
    {
        $approvedStatuses = Permohonan::statusValuesFor(Permohonan::STATUS_DILULUSKAN);
        $rows = Permohonan::query()
            ->with([
                'pelajar:id,permohonan_id,nama_penuh,no_matrik',
                'bantuan:id,permohonan_id,jenis_bantuan,kategori_bantuan',
                'diagihOleh:id,name',
            ])
            ->whereIn('status', $approvedStatuses)
            ->latest('updated_at')
            ->get();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No Permohonan', 'Nama Pelajar', 'Kategori Bantuan', 'Status Agihan', 'Pegawai', 'Tarikh Agihan']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->no_kelompok,
                    $row->pelajar?->nama_penuh ?? '-',
                    Permohonan::kategoriBantuanLabel($row->bantuan?->kategori_bantuan),
                    Permohonan::statusAgihanLabel($row->status_agihan),
                    $row->diagihOleh?->name ?? '-',
                    optional($row->tarikh_agihan)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, 'laporan-agihan-' . now()->format('Ymd-His') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function isApproved(Permohonan $permohonan): bool
    {
        return Permohonan::normalizeStatus($permohonan->status) === Permohonan::STATUS_DILULUSKAN;
    }

    private function sendAgihanSelesaiEmail(Permohonan $permohonan): bool
    {
        $email = $this->studentEmailFor($permohonan);

        if (! $email) {
            return false;
        }

        try {
            Notification::route('mail', $email)
                ->notify(new AgihanSelesaiNotification($permohonan));

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Gagal menghantar emel agihan selesai.', [
                'permohonan_id' => $permohonan->id,
                'email' => $email,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function studentEmailFor(Permohonan $permohonan): ?string
    {
        $email = trim((string) ($permohonan->pelajar?->email_ukm ?? ''));

        if ($email !== '') {
            return $email;
        }

        $fallbackEmail = trim((string) ($permohonan->user?->email ?? ''));

        return $fallbackEmail !== '' ? $fallbackEmail : null;
    }
}
