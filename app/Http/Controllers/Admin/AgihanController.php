<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Notifications\AgihanSelesaiNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AgihanController extends Controller
{
    public function index(Request $request)
    {
        $approvedStatuses = Permohonan::statusValuesFor(Permohonan::STATUS_DILULUSKAN);
        $categoryOptions = Permohonan::KATEGORI_BANTUAN_LABELS;
        $statusFilterOptions = [
            Permohonan::STATUS_AGIHAN_BELUM_DIAGIH => 'Menunggu Agihan',
            Permohonan::STATUS_AGIHAN_SEDANG_DIAGIH => 'Dalam Penghantaran',
            Permohonan::STATUS_AGIHAN_SELESAI => 'Selesai Disalurkan',
        ];
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'category' => (string) $request->query('category', ''),
            'status' => (string) $request->query('status', ''),
        ];

        if (! array_key_exists($filters['category'], $categoryOptions)) {
            $filters['category'] = '';
        }

        if (! array_key_exists($filters['status'], $statusFilterOptions)) {
            $filters['status'] = '';
        }

        $agihanQuery = Permohonan::query()
            ->with([
                'pelajar:id,permohonan_id,nama_penuh,no_matrik',
                'bantuan:id,permohonan_id,jenis_bantuan,kategori_bantuan',
                'diagihOleh:id,name',
            ])
            ->whereIn('status', $approvedStatuses);

        if ($filters['q'] !== '') {
            $search = '%' . $filters['q'] . '%';

            $agihanQuery->where(function ($query) use ($search) {
                $query->where('no_kelompok', 'like', $search)
                    ->orWhereHas('pelajar', function ($pelajarQuery) use ($search) {
                        $pelajarQuery->where('nama_penuh', 'like', $search)
                            ->orWhere('no_matrik', 'like', $search);
                    });
            });
        }

        if ($filters['category'] !== '') {
            $categoryValues = $this->categoryFilterValues($filters['category']);

            $agihanQuery->whereHas('bantuan', function ($bantuanQuery) use ($categoryValues) {
                $bantuanQuery->whereIn('kategori_bantuan', $categoryValues);
            });
        }

        if ($filters['status'] !== '') {
            if ($filters['status'] === Permohonan::STATUS_AGIHAN_BELUM_DIAGIH) {
                $agihanQuery->where(function ($query) {
                    $query->whereNull('status_agihan')
                        ->orWhere('status_agihan', '')
                        ->orWhere('status_agihan', Permohonan::STATUS_AGIHAN_BELUM_DIAGIH);
                });
            } else {
                $agihanQuery->where('status_agihan', $filters['status']);
            }
        }

        $agihanRows = $agihanQuery
            ->orderByRaw('COALESCE(tarikh_agihan, updated_at, admin_review_date, tarikh_mohon) DESC')
            ->orderByDesc('id')
            ->get();

        $agihanRows->each(fn (Permohonan $permohonan) => $this->appendBuktiAvailability($permohonan));

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
        $agihanRowsByStatus = collect([
            Permohonan::STATUS_AGIHAN_BELUM_DIAGIH => $agihanRows
                ->where('status_agihan_key', Permohonan::STATUS_AGIHAN_BELUM_DIAGIH)
                ->values(),
            Permohonan::STATUS_AGIHAN_SEDANG_DIAGIH => $agihanRows
                ->where('status_agihan_key', Permohonan::STATUS_AGIHAN_SEDANG_DIAGIH)
                ->values(),
            Permohonan::STATUS_AGIHAN_SELESAI => $agihanRows
                ->where('status_agihan_key', Permohonan::STATUS_AGIHAN_SELESAI)
                ->values(),
        ]);
        $agihanSections = collect([
            [
                'key' => Permohonan::STATUS_AGIHAN_BELUM_DIAGIH,
                'label' => 'Menunggu Agihan',
            ],
            [
                'key' => Permohonan::STATUS_AGIHAN_SEDANG_DIAGIH,
                'label' => 'Sedang Diagih',
            ],
            [
                'key' => Permohonan::STATUS_AGIHAN_SELESAI,
                'label' => 'Selesai',
            ],
        ])->map(function (array $section) use ($agihanRowsByStatus): array {
            $section['count'] = $agihanRowsByStatus->get($section['key'], collect())->count();

            return $section;
        })->values();
        $activeAgihanSection = $filters['status'] !== ''
            ? $filters['status']
            : Permohonan::STATUS_AGIHAN_BELUM_DIAGIH;

        return view('admin.agihan.index', compact(
            'agihanRows',
            'agihanRowsByStatus',
            'agihanSections',
            'activeAgihanSection',
            'distributionStats',
            'totalDistribution',
            'distributionChart',
            'categoryOptions',
            'statusFilterOptions',
            'filters'
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

        $buktiPath = $request->file('bukti_agihan')->store('agihan-bukti', 'local');

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
        $buktiFile = $this->buktiStorageFile($permohonan);

        if (! $buktiFile) {
            return redirect()
                ->route('admin.agihan.index')
                ->with('warning', 'Bukti agihan tidak dijumpai dalam storan. Sila muat naik semula bukti untuk permohonan ini.');
        }

        $fileName = basename(str_replace('\\', '/', $buktiFile['path']));

        if ($buktiFile['disk'] === 'absolute') {
            if ($request->boolean('download')) {
                return response()->download($buktiFile['path'], $fileName);
            }

            return response()->file($buktiFile['path']);
        }

        if ($request->boolean('download')) {
            return Storage::disk($buktiFile['disk'])->download($buktiFile['path'], $fileName);
        }

        return Storage::disk($buktiFile['disk'])->response($buktiFile['path']);
    }

    public function laporan(Request $request)
    {
        $approvedStatuses = Permohonan::statusValuesFor(Permohonan::STATUS_DILULUSKAN);
        $currentYear = now()->year;
        $monthLabels = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'];
        $latestAgihanSearch = trim((string) $request->query('q_agihan', ''));

        $agihanRows = Permohonan::query()
            ->with([
                'pelajar:id,permohonan_id,nama_penuh,no_matrik',
                'bantuan:id,permohonan_id,jenis_bantuan,kategori_bantuan',
                'diagihOleh:id,name',
            ])
            ->whereIn('status', $approvedStatuses)
            ->orderByRaw('COALESCE(tarikh_agihan, updated_at, admin_review_date, tarikh_mohon) DESC')
            ->orderByDesc('id')
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

        $latestAgihan = $agihanRows;

        if ($latestAgihanSearch !== '') {
            $latestAgihan = $latestAgihan->filter(
                fn (Permohonan $permohonan) => $this->matchesLatestAgihanSearch($permohonan, $latestAgihanSearch)
            );
        }

        $latestAgihan = $latestAgihan->take(10)->values();
        $total = collect($statusData)->sum('value');

        return view('admin.statistik.agihan', compact(
            'statusData',
            'summary',
            'latestAgihan',
            'latestAgihanSearch',
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
            ->orderByRaw('COALESCE(tarikh_agihan, updated_at, admin_review_date, tarikh_mohon) DESC')
            ->orderByDesc('id')
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

    private function matchesLatestAgihanSearch(Permohonan $permohonan, string $search): bool
    {
        $haystack = collect([
            $permohonan->pelajar?->nama_penuh,
            $permohonan->pelajar?->no_matrik,
            $permohonan->bantuan?->kategori_bantuan,
            Permohonan::kategoriBantuanLabel($permohonan->bantuan?->kategori_bantuan),
            $permohonan->status_agihan_label,
            $permohonan->diagihOleh?->name,
        ])
            ->filter()
            ->implode(' ');

        return str_contains(Str::lower($haystack), Str::lower($search));
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

    private function categoryFilterValues(string $category): array
    {
        return collect([$category])
            ->merge(collect(Permohonan::LEGACY_KATEGORI_BANTUAN_ALIASES)
                ->filter(fn (string $canonical) => $canonical === $category)
                ->keys())
            ->merge(collect(Permohonan::LEGACY_DUMMY_KATEGORI_BANTUAN_ALIASES)
                ->filter(fn (string $canonical) => $canonical === $category)
                ->keys())
            ->unique()
            ->values()
            ->all();
    }

    private function appendBuktiAvailability(Permohonan $permohonan): void
    {
        $buktiFile = $this->buktiStorageFile($permohonan);

        $permohonan->setAttribute('bukti_agihan_exists', $buktiFile !== null);
        $permohonan->setAttribute('bukti_agihan_disk', $buktiFile['disk'] ?? null);
        $permohonan->setAttribute('bukti_agihan_path', $buktiFile['path'] ?? null);
    }

    private function buktiStorageFile(Permohonan $permohonan): ?array
    {
        $path = trim((string) $permohonan->bukti_agihan);

        if ($path === '') {
            return null;
        }

        foreach ($this->buktiStorageCandidates($path) as $candidate) {
            if ($candidate['disk'] === 'absolute') {
                if (is_file($candidate['path'])) {
                    return $candidate;
                }

                continue;
            }

            if (Storage::disk($candidate['disk'])->exists($candidate['path'])) {
                return $candidate;
            }
        }

        return null;
    }

    private function buktiStorageCandidates(string $path): array
    {
        $normalizedPath = preg_replace('#/+#', '/', str_replace('\\', '/', trim($path)));
        $relativePath = ltrim($normalizedPath, '/');
        $candidates = [];

        $addCandidate = function (string $disk, string $candidatePath) use (&$candidates) {
            $candidatePath = ltrim(preg_replace('#/+#', '/', str_replace('\\', '/', trim($candidatePath))), '/');

            if ($candidatePath === '') {
                return;
            }

            $key = $disk . ':' . $candidatePath;

            if (! isset($candidates[$key])) {
                $candidates[$key] = [
                    'disk' => $disk,
                    'path' => $candidatePath,
                ];
            }
        };

        $addCandidate('local', $relativePath);
        $addCandidate('public', $relativePath);

        foreach ([
            'storage/app/private/' => 'local',
            'storage/app/public/' => 'public',
            'public/storage/' => 'public',
            'private/' => 'local',
            'public/' => 'public',
            'storage/' => 'public',
        ] as $prefix => $disk) {
            $position = strpos($relativePath, $prefix);

            if ($position !== false) {
                $addCandidate($disk, substr($relativePath, $position + strlen($prefix)));
            }
        }

        $legacyAbsolutePath = storage_path('app/' . $relativePath);
        $storageRoot = realpath(storage_path('app'));
        $legacyRealPath = is_file($legacyAbsolutePath) ? realpath($legacyAbsolutePath) : false;

        if ($storageRoot && $legacyRealPath && str_starts_with($legacyRealPath, $storageRoot . DIRECTORY_SEPARATOR)) {
            $candidates['absolute:' . $legacyRealPath] = [
                'disk' => 'absolute',
                'path' => $legacyRealPath,
            ];
        }

        return array_values($candidates);
    }
}
