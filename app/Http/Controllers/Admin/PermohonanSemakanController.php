<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Notifications\PermohonanKeputusanNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class PermohonanSemakanController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $permohonan = Permohonan::query()
            ->with([
                'user:id,name,email,matrik',
                'pelajar:id,permohonan_id,nama_penuh,no_matrik,email_ukm',
                'bantuan:id,permohonan_id,jenis_bantuan,kategori_bantuan',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('no_kelompok', 'like', '%' . $search . '%')
                        ->orWhere('jenis_bantuan', 'like', '%' . $search . '%')
                        ->orWhere('status', 'like', '%' . $search . '%')
                        ->orWhereHas('pelajar', function ($pelajarQuery) use ($search) {
                            $pelajarQuery->where('nama_penuh', 'like', '%' . $search . '%')
                                ->orWhere('no_matrik', 'like', '%' . $search . '%')
                                ->orWhere('email_ukm', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('bantuan', function ($bantuanQuery) use ($search) {
                            $bantuanQuery->where('jenis_bantuan', 'like', '%' . $search . '%')
                                ->orWhere('kategori_bantuan', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%')
                                ->orWhere('matrik', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest('tarikh_mohon')
            ->latest('id')
            ->get();

        $stats = [
            'jumlah' => $permohonan->count(),
            'lewat_diproses' => $permohonan->where('lewat_diproses', true)->count(),
            'dalam_semakan' => $permohonan
                ->where('status_key', Permohonan::STATUS_DALAM_SEMAKAN)
                ->where('lewat_diproses', false)
                ->count(),
            'lulus' => $permohonan->where('status_key', Permohonan::STATUS_DILULUSKAN)->count(),
            'gagal' => $permohonan->where('status_key', Permohonan::STATUS_DITOLAK_GAGAL)->count(),
            'selesai' => $permohonan
                ->whereIn('status_key', [Permohonan::STATUS_DILULUSKAN, Permohonan::STATUS_DITOLAK_GAGAL])
                ->count(),
        ];

        return view('admin.permohonan.index', compact('permohonan', 'search', 'stats'));
    }

    public function show(Permohonan $permohonan)
    {
        $permohonan->load([
            'user:id,name,email,matrik',
            'pelajar',
            'keluarga',
            'bantuan',
            'dokumens',
        ]);

        $studentJustifikasi = $this->extractStudentJustification($permohonan->bantuan?->data ?? []);
        $canReview = $permohonan->status_key === Permohonan::STATUS_DALAM_SEMAKAN;

        return view('admin.permohonan.show', compact('permohonan', 'studentJustifikasi', 'canReview'));
    }

    public function keputusan(Request $request, Permohonan $permohonan)
    {
        $permohonan->load(['user', 'pelajar', 'bantuan']);

        if ($permohonan->status_key !== Permohonan::STATUS_DALAM_SEMAKAN) {
            return redirect()
                ->route('admin.permohonan.show', $permohonan)
                ->with('info', 'Keputusan permohonan ini telah direkodkan dan tidak boleh dihantar semula.');
        }

        $validated = $request->validate([
            'keputusan' => 'required|in:Diluluskan,Ditolak',
            'admin_catatan' => 'required|string|min:5|max:5000',
        ], [
            'keputusan.required' => 'Sila pilih keputusan permohonan.',
            'keputusan.in' => 'Keputusan permohonan yang dipilih tidak sah.',
            'admin_catatan.required' => 'Sila isi Catatan / Justifikasi Admin.',
            'admin_catatan.min' => 'Catatan / Justifikasi Admin mestilah sekurang-kurangnya 5 aksara.',
            'admin_catatan.max' => 'Catatan / Justifikasi Admin tidak boleh melebihi 5000 aksara.',
        ]);

        DB::transaction(function () use ($permohonan, $validated) {
            $permohonan->update([
                'status' => $validated['keputusan'],
                'catatan' => $validated['admin_catatan'],
                'admin_catatan' => $validated['admin_catatan'],
                'admin_review_date' => now(),
                'status_agihan' => $validated['keputusan'] === 'Diluluskan'
                    ? Permohonan::STATUS_AGIHAN_BELUM_DIAGIH
                    : null,
            ]);
        });

        $email = $this->studentEmailFor($permohonan);
        $mailSent = false;

        if ($email) {
            try {
                Notification::route('mail', $email)
                    ->notify(new PermohonanKeputusanNotification($permohonan->fresh(['pelajar', 'bantuan'])));
                $mailSent = true;
            } catch (\Throwable $exception) {
                Log::warning('Gagal menghantar emel keputusan permohonan.', [
                    'permohonan_id' => $permohonan->id,
                    'email' => $email,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        if ($validated['keputusan'] === 'Diluluskan') {
            $redirect = redirect()
                ->route('admin.permohonan.show', $permohonan)
                ->with('success', 'Permohonan berjaya diluluskan dan dimasukkan ke proses agihan bantuan.')
                ->with('approval_to_agihan', true);

            if (! $mailSent) {
                $redirect->with('info', 'Emel notifikasi keputusan tidak dihantar kerana emel pelajar tiada atau konfigurasi emel gagal.');
            }

            return $redirect;
        }

        $message = $mailSent
            ? 'Keputusan permohonan berjaya dihantar dan emel notifikasi telah dijana.'
            : 'Keputusan permohonan berjaya disimpan. Emel notifikasi tidak dihantar kerana emel pelajar tiada atau konfigurasi emel gagal.';

        return redirect()
            ->route('admin.permohonan.show', $permohonan)
            ->with($mailSent ? 'success' : 'warning', $message);
    }

    public function status()
    {
        $statusCounts = Permohonan::statusCounts();
        $applicationStats = $this->applicationStats($statusCounts);
        $totalApplications = collect($applicationStats)->sum('value');

        $statusRows = Permohonan::query()
            ->with([
                'user:id,name,email',
                'pelajar:id,permohonan_id,nama_penuh,no_matrik',
                'bantuan:id,permohonan_id,jenis_bantuan,kategori_bantuan',
            ])
            ->latest('tarikh_mohon')
            ->latest('id')
            ->take(8)
            ->get();

        return view('admin.permohonan.status', compact('applicationStats', 'totalApplications', 'statusRows'));
    }

    public function statistik()
    {
        $statusCounts = Permohonan::statusCounts();
        $stats = $this->applicationStats($statusCounts);
        $total = collect($stats)->sum('value');
        $currentYear = now()->year;
        $approvedStatuses = Permohonan::statusValuesFor(Permohonan::STATUS_DILULUSKAN);
        $rejectedStatuses = Permohonan::statusValuesFor(Permohonan::STATUS_DITOLAK_GAGAL);

        $monthlyApprovedData = [];
        $monthlyRejectedData = [];

        foreach (range(1, 12) as $month) {
            $monthlyApprovedData[] = Permohonan::query()
                ->whereIn('status', $approvedStatuses)
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $month)
                ->count();

            $monthlyRejectedData[] = Permohonan::query()
                ->whereIn('status', $rejectedStatuses)
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $month)
                ->count();
        }

        $approvalRate = $statusCounts['jumlah'] > 0
            ? round(($statusCounts['diluluskan'] / $statusCounts['jumlah']) * 100, 1)
            : 0;

        $latestApplications = Permohonan::query()
            ->with([
                'pelajar:id,permohonan_id,nama_penuh,no_matrik',
                'bantuan:id,permohonan_id,jenis_bantuan,kategori_bantuan',
            ])
            ->latest('created_at')
            ->latest('id')
            ->take(8)
            ->get();

        return view('admin.statistik.permohonan', compact(
            'stats',
            'total',
            'statusCounts',
            'approvalRate',
            'monthlyApprovedData',
            'monthlyRejectedData',
            'currentYear',
            'latestApplications'
        ));
    }

    public function statistikCsv()
    {
        $rows = Permohonan::query()
            ->with([
                'user:id,name,email',
                'pelajar:id,permohonan_id,nama_penuh,no_matrik',
                'bantuan:id,permohonan_id,jenis_bantuan,kategori_bantuan',
            ])
            ->latest('created_at')
            ->get();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No Permohonan', 'Nama Pelajar', 'Kategori Bantuan', 'Status', 'Tarikh']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->no_kelompok,
                    $row->pelajar?->nama_penuh ?? $row->user?->name ?? '-',
                    Permohonan::kategoriBantuanLabel($row->bantuan?->kategori_bantuan),
                    Permohonan::statusLabel($row->status),
                    optional($row->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, 'laporan-permohonan-' . now()->format('Ymd-His') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function applicationStats(array $statusCounts): array
    {
        return [
            [
                'label' => 'Dalam Semakan',
                'value' => $statusCounts['dalam_semakan'],
                'color' => '#f59e0b',
                'class' => 'bg-amber-50 text-amber-700',
            ],
            [
                'label' => 'Diluluskan',
                'value' => $statusCounts['diluluskan'],
                'color' => '#10b981',
                'class' => 'bg-emerald-50 text-emerald-700',
            ],
            [
                'label' => 'Ditolak / Gagal',
                'value' => $statusCounts['ditolak_gagal'],
                'color' => '#ef4444',
                'class' => 'bg-rose-50 text-rose-700',
            ],
            [
                'label' => 'Lewat',
                'value' => $statusCounts['lewat_diproses'],
                'color' => '#6366f1',
                'class' => 'bg-indigo-50 text-indigo-700',
            ],
        ];
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

    private function extractStudentJustification(array $data): string
    {
        $matches = [];
        $this->collectJustificationValues($data, $matches);

        return collect($matches)
            ->filter()
            ->unique()
            ->implode("\n");
    }

    private function collectJustificationValues(array $data, array &$matches, string $prefix = ''): void
    {
        foreach ($data as $key => $value) {
            $label = Str::of((string) $key)
                ->replace(['_', '-'], ' ')
                ->squish()
                ->title()
                ->toString();

            $path = trim($prefix . ' ' . $label);
            $normalizedKey = Str::of((string) $key)->lower()->toString();
            $isJustificationKey = Str::contains($normalizedKey, [
                'justifikasi',
                'justification',
                'sebab',
                'alasan',
            ]);

            if (is_array($value)) {
                $this->collectJustificationValues($value, $matches, $path);
                continue;
            }

            if ($isJustificationKey && filled($value)) {
                $matches[] = $path . ': ' . $value;
            }
        }
    }
}
