<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\PermohonanBantuan;
use App\Models\AppSetting;
use App\Models\CashDonation;
use App\Models\Sumbangan;
use App\Models\User;
use App\Support\DonorRecognition;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return match ($user->role) {
            'pelajar'  => redirect()->route('dashboard.pelajar'),
            'penderma' => redirect()->route('dashboard.penderma'),
            'admin'    => redirect()->route('dashboard.admin'),
            default    => abort(403),
        };
    }

    public function pelajar()
    {
        $user = Auth::user();
        $lockedBantuanTypes = Permohonan::bantuanLocksForUser($user->id);
        $latestPermohonan = Permohonan::query()
            ->with('bantuan:id,permohonan_id,jenis_bantuan,kategori_bantuan')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->first();

        return view('dashboard.dashboard-pelajar', [
            'lockedBantuanTypes' => $lockedBantuanTypes,
            'latestPermohonan' => $latestPermohonan,
        ]);
    }

    public function penderma()
    {
        $user = Auth::user();

        $completedDonations = Sumbangan::query()
            ->with('items:id,sumbangan_id,kategori_bantuan,kuantiti')
            ->where('user_id', $user->id)
            ->where('status', 'selesai')
            ->latest('paid_at')
            ->latest('created_at')
            ->get();

        $successfulCashDonations = CashDonation::query()
            ->where('user_id', $user->id)
            ->successful()
            ->latest('paid_at')
            ->latest('created_at')
            ->get();

        $donationCategories = $completedDonations
            ->flatMap(fn (Sumbangan $sumbangan) => $sumbangan->items)
            ->pluck('kategori_bantuan')
            ->filter()
            ->unique()
            ->values();

        $matchingCategories = Permohonan::kategoriBantuanMatchesForDonationCategories($donationCategories);

        $totalCompletedAmount = (float) $completedDonations->sum('jumlah_keseluruhan')
            + (float) $successfulCashDonations->sum('amount');
        $totalCompletedItems = (int) $completedDonations
            ->flatMap(fn (Sumbangan $sumbangan) => $sumbangan->items)
            ->sum('kuantiti');

        $recognition = DonorRecognition::forAmount($totalCompletedAmount);
        $impactSummary = $this->donorImpactSummary($completedDonations, $matchingCategories, $totalCompletedItems);

        $recentItemDonations = Sumbangan::query()
            ->with('items:id,sumbangan_id,kategori_bantuan')
            ->where('user_id', $user->id)
            ->orderByRaw('COALESCE(paid_at, created_at) DESC')
            ->orderByDesc('id')
            ->take(8)
            ->get()
            ->map(function (Sumbangan $sumbangan) {
                $transactionAt = $sumbangan->paid_at ?? $sumbangan->created_at;

                return [
                    'id' => $sumbangan->id,
                    'type' => 'item',
                    'type_label' => 'Sumbangan Barang',
                    'no_sumbangan' => $sumbangan->no_sumbangan ?: ('SMB-' . $sumbangan->id),
                    'date' => $transactionAt?->format('d/m/Y') ?? '-',
                    'sort_date' => $transactionAt,
                    'sort_timestamp' => $transactionAt?->getTimestamp() ?? 0,
                    'sort_id' => (int) $sumbangan->id,
                    'status' => $this->donationStatusLabel($sumbangan->status),
                    'status_class' => $this->donationStatusClass($sumbangan->status),
                    'amount' => (float) $sumbangan->jumlah_keseluruhan,
                    'receipt_url' => $sumbangan->status === 'selesai'
                        ? route('penderma.sumbangan.receipt', ['id' => $sumbangan->id])
                        : null,
                    'receipt_label' => 'Lihat Resit',
                    'proof' => null,
                    'proof_status' => 'Impak kategori',
                ];
            });

        $recentCashDonations = CashDonation::query()
            ->where('user_id', $user->id)
            ->orderByRaw('COALESCE(paid_at, created_at) DESC')
            ->orderByDesc('id')
            ->take(8)
            ->get()
            ->map(function (CashDonation $cashDonation) {
                $transactionAt = $cashDonation->paid_at ?? $cashDonation->created_at;

                return [
                    'id' => 'cash-' . $cashDonation->id,
                    'type' => 'cash',
                    'type_label' => 'Sumbangan Tabung',
                    'no_sumbangan' => $this->cashDonationReference($cashDonation),
                    'date' => $transactionAt?->format('d/m/Y') ?? '-',
                    'sort_date' => $transactionAt,
                    'sort_timestamp' => $transactionAt?->getTimestamp() ?? 0,
                    'sort_id' => (int) $cashDonation->id,
                    'status' => $this->cashDonationStatusLabel($cashDonation->payment_status),
                    'status_class' => $this->cashDonationStatusClass($cashDonation->payment_status),
                    'amount' => (float) $cashDonation->amount,
                    'receipt_url' => route('penderma.tabung.receipt', $cashDonation),
                    'receipt_label' => 'Lihat Resit',
                    'proof' => null,
                    'proof_status' => 'Dipantau Admin',
                ];
            });

        $recentDonations = $recentItemDonations
            ->concat($recentCashDonations)
            ->sort(fn (array $left, array $right) => [
                $right['sort_timestamp'] ?? 0,
                $right['sort_id'] ?? 0,
            ] <=> [
                $left['sort_timestamp'] ?? 0,
                $left['sort_id'] ?? 0,
            ])
            ->take(8)
            ->values();

        $cashFundTotal = (float) CashDonation::query()
            ->successful()
            ->sum('amount');
        $cashFundTarget = max(1, (float) AppSetting::value('tabung_target', 1000000));
        $cashFundPercent = min(100, round(($cashFundTotal / $cashFundTarget) * 100, 2));

        $summaryCards = [
            [
                'label' => 'Jumlah Sumbangan',
                'value' => 'RM' . number_format($totalCompletedAmount, 2),
                'caption' => 'Jumlah sumbangan selesai dalam akaun anda.',
                'classes' => 'border-blue-100 bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF]',
                'value_classes' => 'text-[#071633]',
            ],
            [
                'label' => 'Jumlah Unit Disumbangkan',
                'value' => number_format($totalCompletedItems),
                'caption' => 'Jumlah kuantiti item daripada sumbangan selesai.',
                'classes' => 'border-emerald-100 bg-gradient-to-br from-emerald-50 to-white',
                'value_classes' => 'text-emerald-700',
            ],
            [
                'label' => 'Tahap Pengiktirafan',
                'value' => $recognition['tier'],
                'caption' => 'Dikira berdasarkan jumlah sumbangan selesai.',
                'classes' => 'border-indigo-100 bg-gradient-to-br from-indigo-50 to-white',
                'value_classes' => 'text-indigo-700',
            ],
        ];

        $recognitionLevels = DonorRecognition::levels();

        return view('dashboard.dashboard-penderma', compact(
            'user',
            'summaryCards',
            'recentDonations',
            'recognition',
            'recognitionLevels',
            'impactSummary',
            'totalCompletedAmount',
            'totalCompletedItems',
            'cashFundTotal',
            'cashFundTarget',
            'cashFundPercent'
        ));
    }

    public function store(Request $request)
    {
        return redirect()->back()->with('success', 'Data berjaya disimpan');
    }

    public function admin()
    {
        $statusCounts = Permohonan::statusCounts();
        $jumlahBulanIni = Permohonan::query()
            ->whereBetween('tarikh_mohon', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->count();

        $stats = [
            [
                'label' => 'Jumlah Permohonan',
                'value' => $statusCounts['jumlah'],
                'badge' => 'Keseluruhan rekod',
                'badgeColor' => 'bg-blue-50 text-blue-700',
                'note' => '+' . $jumlahBulanIni . ' bulan ini',
            ],
            [
                'label' => 'Dalam Semakan',
                'value' => $statusCounts['dalam_semakan'],
                'badge' => 'Perlu disemak',
                'badgeColor' => 'bg-amber-50 text-amber-700',
                'note' => 'Tindakan admin',
            ],
            [
                'label' => 'Diluluskan',
                'value' => $statusCounts['diluluskan'],
                'badge' => 'Sedia untuk agihan',
                'badgeColor' => 'bg-emerald-50 text-emerald-700',
                'note' => 'Boleh diagih',
            ],
            [
                'label' => 'Lewat Diproses',
                'value' => $statusCounts['lewat_diproses'],
                'badge' => 'Melebihi SLA',
                'badgeColor' => 'bg-rose-50 text-rose-700',
                'note' => 'Perlu segera',
            ],
        ];

        $actions = [
            [
                'title' => 'Semak permohonan lewat',
                'desc' => $statusCounts['lewat_diproses'] . ' permohonan telah melebihi tempoh semakan.',
                'href' => route('admin.permohonan.index'),
                'status' => 'Urgent',
                'statusColor' => 'bg-rose-100 text-rose-700',
            ],
            [
                'title' => 'Agihkan bantuan diluluskan',
                'desc' => $statusCounts['diluluskan'] . ' permohonan telah diluluskan dan sedia untuk agihan.',
                'href' => route('admin.permohonan.index'),
                'status' => 'Agihan',
                'statusColor' => 'bg-emerald-100 text-emerald-700',
            ],
            [
                'title' => 'Semak permohonan baharu',
                'desc' => $statusCounts['dalam_semakan'] . ' permohonan masih dalam semakan pentadbir.',
                'href' => route('admin.permohonan.index'),
                'status' => 'Semakan',
                'statusColor' => 'bg-amber-100 text-amber-700',
            ],
        ];

        $applicationStatusLabels = ['Dalam Semakan', 'Diluluskan', 'Ditolak / Gagal', 'Lewat'];
        $applicationStatusData = [
            $statusCounts['dalam_semakan'],
            $statusCounts['diluluskan'],
            $statusCounts['ditolak_gagal'],
            $statusCounts['lewat_diproses'],
        ];
        $distributionStatusCounts = $this->distributionStatusCounts();
        $distributionStatusLabels = ['Belum Diagih', 'Sedang Diagih', 'Selesai'];
        $distributionStatusData = [
            $distributionStatusCounts['belum_diagih'],
            $distributionStatusCounts['sedang_diagih'],
            $distributionStatusCounts['selesai'],
        ];
        $currentYear = now()->year;
        $donorSummary = $this->donorSummary();
        $donationMonthlyData = $this->monthlyCompletedDonationTotals($currentYear);
        $currentMonthDonation = (float) ($donationMonthlyData[now()->month - 1] ?? 0);
        $previousMonthDonation = now()->month > 1
            ? (float) ($donationMonthlyData[now()->subMonthNoOverflow()->month - 1] ?? 0)
            : 0.0;
        $donationYearTotal = round((float) collect($donationMonthlyData)->sum(), 2);
        $donationMonthDelta = round($currentMonthDonation - $previousMonthDonation, 2);

        $aidCategorySummary = $this->aidCategorySummary();
        $failedPayments = $this->failedPaymentsCount();

        $applicationSummary = [
            'jumlah_pelajar_memohon' => $this->countDistinctStudentApplicants(),
            'kategori_bantuan' => collect($aidCategorySummary['data'])
                ->filter(fn (int $total) => $total > 0)
                ->count(),
            'diluluskan' => $statusCounts['diluluskan'],
            'ditolak_gagal' => $statusCounts['ditolak_gagal'],
        ];

        $dashboardKpis = [
            [
                'label' => 'Jumlah Permohonan',
                'value' => number_format($statusCounts['jumlah']),
                'note' => '+' . number_format($jumlahBulanIni) . ' bulan ini',
                'color' => 'text-blue-700',
                'soft' => 'bg-blue-50',
            ],
            [
                'label' => 'Penderma Aktif',
                'value' => number_format($donorSummary['active_donors']),
                'note' => number_format($donorSummary['total_donors']) . ' jumlah penderma',
                'color' => 'text-emerald-700',
                'soft' => 'bg-emerald-50',
            ],
            [
                'label' => 'Dana Tabung',
                'value' => 'RM' . number_format($donorSummary['cash_fund_total'], 2),
                'note' => 'Sumbangan berjaya',
                'color' => 'text-cyan-700',
                'soft' => 'bg-cyan-50',
            ],
            [
                'label' => 'Bantuan Belum Diagih',
                'value' => number_format($distributionStatusCounts['belum_diagih']),
                'note' => 'Perlu tindakan',
                'color' => 'text-amber-700',
                'soft' => 'bg-amber-50',
            ],
        ];

        $actionItems = [
            [
                'label' => number_format($distributionStatusCounts['belum_diagih']) . ' bantuan belum diagihkan',
                'href' => route('admin.agihan.index'),
                'badge' => 'Agihan',
                'class' => 'bg-cyan-50 text-cyan-700',
            ],
            [
                'label' => number_format($distributionStatusCounts['bukti_belum_dimuat_naik']) . ' bukti agihan belum dimuat naik',
                'href' => route('admin.agihan.index'),
                'badge' => 'Bukti',
                'class' => 'bg-amber-50 text-amber-700',
            ],
            [
                'label' => number_format($failedPayments) . ' pembayaran gagal',
                'href' => route('admin.tabung.index', ['status' => CashDonation::STATUS_FAILED]),
                'badge' => 'Bayaran',
                'class' => 'bg-rose-50 text-rose-700',
            ],
            [
                'label' => number_format($statusCounts['dalam_semakan_total'] ?? $statusCounts['dalam_semakan']) . ' permohonan dalam semakan',
                'href' => route('admin.permohonan.index'),
                'badge' => 'Permohonan',
                'class' => 'bg-blue-50 text-blue-700',
            ],
        ];

        $recentActivities = $this->recentAdminActivities();

        return view('dashboard.dashboard-admin', compact(
            'stats',
            'actions',
            'applicationStatusLabels',
            'applicationStatusData',
            'distributionStatusLabels',
            'distributionStatusData',
            'applicationSummary',
            'donorSummary',
            'donationMonthlyData',
            'currentYear',
            'statusCounts',
            'dashboardKpis',
            'donationYearTotal',
            'currentMonthDonation',
            'previousMonthDonation',
            'donationMonthDelta',
            'aidCategorySummary',
            'actionItems',
            'recentActivities'
        ));
    }

    private function donorImpactSummary(Collection $completedDonations, array $matchingCategories, int $totalCompletedItems): array
    {
        $completedAgihanCategories = empty($matchingCategories)
            ? collect()
            : Permohonan::query()
                ->with('bantuan:id,permohonan_id,kategori_bantuan')
                ->where('status_agihan', Permohonan::STATUS_AGIHAN_SELESAI)
                ->whereNotNull('tarikh_agihan')
                ->whereHas('bantuan', fn ($query) => $query->whereIn('kategori_bantuan', $matchingCategories))
                ->get()
                ->pluck('bantuan.kategori_bantuan')
                ->filter()
                ->unique()
                ->values();

        $completedAgihanCount = $completedAgihanCategories->isEmpty()
            ? 0
            : Permohonan::query()
                ->where('status_agihan', Permohonan::STATUS_AGIHAN_SELESAI)
                ->whereNotNull('tarikh_agihan')
                ->whereHas('bantuan', fn ($query) => $query->whereIn('kategori_bantuan', $completedAgihanCategories))
                ->count();

        $supportedCategoryLabels = $completedDonations
            ->flatMap(fn (Sumbangan $sumbangan) => $sumbangan->items)
            ->pluck('kategori_bantuan')
            ->filter()
            ->unique()
            ->values()
            ->map(fn (string $category) => Permohonan::kategoriBantuanLabel($category))
            ->implode(' & ');

        return [
            'completed_agihan_count' => $completedAgihanCount,
            'related_units' => $completedAgihanCount > 0 ? $totalCompletedItems : 0,
            'supported_categories' => $supportedCategoryLabels !== '' ? $supportedCategoryLabels : 'Belum ada kategori',
        ];
    }

    private function donorSummary(): array
    {
        return [
            'total_donors' => User::query()
                ->where('role', 'penderma')
                ->count(),
            'active_donors' => User::query()
                ->where('role', 'penderma')
                ->where(function ($query) {
                    $query
                        ->whereHas('sumbangans', fn ($query) => $query->completed())
                        ->orWhereHas('cashDonations', fn ($query) => $query->successful());
                })
                ->count(),
            'total_donations' => Sumbangan::query()
                ->completed()
                ->count() + CashDonation::query()
                ->successful()
                ->count(),
            'pending_donations' => Sumbangan::query()
                ->pendingConfirmation()
                ->count() + CashDonation::query()
                ->pending()
                ->count(),
            'cash_fund_total' => (float) CashDonation::query()
                ->successful()
                ->sum('amount'),
            'item_units_total' => (int) Sumbangan::query()
                ->completed()
                ->sum('jumlah_unit'),
        ];
    }

    private function monthlyCompletedDonationTotals(int $year): array
    {
        $itemMonthlyTotals = Sumbangan::query()
            ->completed()
            ->forDonationYear($year)
            ->get(['jumlah_keseluruhan', 'paid_at', 'created_at'])
            ->groupBy(function (Sumbangan $sumbangan) {
                return ($sumbangan->paid_at ?? $sumbangan->created_at)->month;
            })
            ->map(fn (Collection $records) => (float) $records->sum('jumlah_keseluruhan'));

        $cashMonthlyTotals = CashDonation::query()
            ->successful()
            ->forPaymentYear($year)
            ->get(['amount', 'paid_at', 'created_at'])
            ->groupBy(function (CashDonation $cashDonation) {
                return ($cashDonation->paid_at ?? $cashDonation->created_at)->month;
            })
            ->map(fn (Collection $records) => (float) $records->sum('amount'));

        return collect(range(1, 12))
            ->map(fn (int $month) => round(
                (float) $itemMonthlyTotals->get($month, 0) + (float) $cashMonthlyTotals->get($month, 0),
                2
            ))
            ->values()
            ->all();
    }

    private function donationStatusLabel(?string $status): string
    {
        return match ($status) {
            Sumbangan::STATUS_SELESAI => 'Selesai',
            Sumbangan::STATUS_MENUNGGU_BAYARAN => 'Menunggu Bayaran',
            Sumbangan::STATUS_DALAM_SEMAKAN => 'Dalam Semakan',
            'dibatalkan' => 'Dibatalkan',
            default => filled($status)
                ? Str::of($status)->replace(['_', '-'], ' ')->squish()->title()->toString()
                : 'Belum Lengkap',
        };
    }

    private function donationStatusClass(?string $status): string
    {
        return match ($status) {
            Sumbangan::STATUS_SELESAI => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
            Sumbangan::STATUS_MENUNGGU_BAYARAN => 'bg-blue-100 text-blue-700 border border-blue-200',
            Sumbangan::STATUS_DALAM_SEMAKAN => 'bg-amber-100 text-amber-700 border border-amber-200',
            'dibatalkan' => 'bg-rose-100 text-rose-700 border border-rose-200',
            default => 'bg-slate-100 text-slate-700 border border-slate-200',
        };
    }

    private function distributionStatusCounts(): array
    {
        $approvedApplications = Permohonan::query()
            ->whereIn('status', Permohonan::statusValuesFor(Permohonan::STATUS_DILULUSKAN));

        return [
            'belum_diagih' => (clone $approvedApplications)
                ->where(function ($query) {
                    $query->whereNull('status_agihan')
                        ->orWhere('status_agihan', '')
                        ->orWhere('status_agihan', 'belum_diagih');
                })
                ->count(),
            'sedang_diagih' => (clone $approvedApplications)
                ->where('status_agihan', 'sedang_diagih')
                ->count(),
            'selesai' => (clone $approvedApplications)
                ->where('status_agihan', 'selesai')
                ->count(),
            'bukti_belum_dimuat_naik' => (clone $approvedApplications)
                ->where('status_agihan', Permohonan::STATUS_AGIHAN_SEDANG_DIAGIH)
                ->where(function ($query) {
                    $query->whereNull('bukti_agihan')
                        ->orWhere('bukti_agihan', '');
                })
                ->count(),
        ];
    }

    private function aidCategorySummary(): array
    {
        $categoryTotals = PermohonanBantuan::query()
            ->whereNotNull('kategori_bantuan')
            ->where('kategori_bantuan', '!=', '')
            ->get(['kategori_bantuan'])
            ->map(fn (PermohonanBantuan $record) => Permohonan::normalizeKategoriBantuan($record->kategori_bantuan, true))
            ->filter(fn (?string $category) => in_array($category, Permohonan::canonicalKategoriBantuanValues(), true))
            ->countBy();

        $orderedTotals = collect(Permohonan::KATEGORI_BANTUAN_LABELS)
            ->map(fn (string $label, string $category) => (int) $categoryTotals->get($category, 0))
            ->filter(fn (int $total) => $total > 0);

        return [
            'labels' => $orderedTotals
                ->keys()
                ->map(fn (string $category) => Permohonan::kategoriBantuanLabel($category))
                ->values()
                ->all(),
            'data' => $orderedTotals
                ->values()
                ->map(fn (int $total) => $total)
                ->all(),
        ];
    }

    private function failedPaymentsCount(): int
    {
        return CashDonation::query()
            ->where('payment_status', CashDonation::STATUS_FAILED)
            ->count()
            + Sumbangan::query()
                ->where('status', 'dibatalkan')
                ->count();
    }

    private function recentAdminActivities(): Collection
    {
        $applications = Permohonan::query()
            ->latest()
            ->take(5)
            ->get(['id', 'status', 'created_at', 'tarikh_mohon'])
            ->map(fn (Permohonan $permohonan) => [
                'type' => 'Permohonan',
                'title' => 'Permohonan pelajar ' . Permohonan::statusLabel($permohonan->status),
                'meta' => optional($permohonan->tarikh_mohon ?? $permohonan->created_at)->format('d/m/Y'),
                'href' => route('admin.permohonan.index'),
                'time' => $permohonan->created_at,
                'class' => 'bg-blue-50 text-blue-700',
            ]);

        $itemDonations = Sumbangan::query()
            ->latest()
            ->take(5)
            ->get(['id', 'jumlah_keseluruhan', 'status', 'created_at'])
            ->map(fn (Sumbangan $sumbangan) => [
                'type' => 'Sumbangan',
                'title' => 'Sumbangan online ' . $this->donationStatusLabel($sumbangan->status),
                'meta' => 'RM' . number_format((float) $sumbangan->jumlah_keseluruhan, 2),
                'href' => route('admin.statistik.sumbangan'),
                'time' => $sumbangan->created_at,
                'class' => 'bg-emerald-50 text-emerald-700',
            ]);

        $cashDonations = CashDonation::query()
            ->latest()
            ->take(5)
            ->get(['id', 'amount', 'payment_status', 'created_at'])
            ->map(fn (CashDonation $cashDonation) => [
                'type' => 'Tabung',
                'title' => 'Tabung bantuan ' . $this->cashDonationStatusLabel($cashDonation->payment_status),
                'meta' => 'RM' . number_format((float) $cashDonation->amount, 2),
                'href' => route('admin.tabung.index'),
                'time' => $cashDonation->created_at,
                'class' => 'bg-cyan-50 text-cyan-700',
            ]);

        $distributions = Permohonan::query()
            ->whereNotNull('tarikh_agihan')
            ->latest('tarikh_agihan')
            ->take(5)
            ->get(['id', 'status_agihan', 'tarikh_agihan', 'created_at'])
            ->map(fn (Permohonan $permohonan) => [
                'type' => 'Agihan',
                'title' => 'Agihan bantuan ' . Permohonan::statusAgihanLabel($permohonan->status_agihan),
                'meta' => optional($permohonan->tarikh_agihan)->format('d/m/Y'),
                'href' => route('admin.agihan.index'),
                'time' => $permohonan->tarikh_agihan ?? $permohonan->created_at,
                'class' => 'bg-sky-50 text-sky-700',
            ]);

        return $applications
            ->concat($itemDonations)
            ->concat($cashDonations)
            ->concat($distributions)
            ->sortByDesc(fn (array $activity) => optional($activity['time'])->timestamp ?? 0)
            ->take(5)
            ->values();
    }

    private function cashDonationReference(CashDonation $cashDonation): string
    {
        return sprintf('TAB/%s/%06d', ($cashDonation->created_at ?? now())->format('Ymd'), $cashDonation->id);
    }

    private function cashDonationStatusLabel(?string $status): string
    {
        return match ($status) {
            CashDonation::STATUS_SUCCESS => 'Selesai',
            CashDonation::STATUS_PENDING => 'Menunggu Bayaran',
            CashDonation::STATUS_FAILED => 'Gagal',
            default => filled($status)
                ? Str::of($status)->replace(['_', '-'], ' ')->squish()->title()->toString()
                : 'Belum Lengkap',
        };
    }

    private function cashDonationStatusClass(?string $status): string
    {
        return match ($status) {
            CashDonation::STATUS_SUCCESS => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
            CashDonation::STATUS_PENDING => 'bg-blue-100 text-blue-700 border border-blue-200',
            CashDonation::STATUS_FAILED => 'bg-rose-100 text-rose-700 border border-rose-200',
            default => 'bg-slate-100 text-slate-700 border border-slate-200',
        };
    }

    private function toyyibPayPaymentUrl(string $billCode): string
    {
        return rtrim((string) config('services.toyyibpay.base_url', 'https://dev.toyyibpay.com'), '/')
            . '/'
            . ltrim($billCode, '/');
    }

    private function countDistinctStudentApplicants(): int
    {
        return Permohonan::query()
            ->with('pelajar:id,permohonan_id,no_matrik')
            ->get(['id', 'user_id'])
            ->map(function (Permohonan $permohonan) {
                $noMatrik = trim((string) ($permohonan->pelajar?->no_matrik ?? ''));

                if ($noMatrik !== '') {
                    return 'matrik:' . strtoupper($noMatrik);
                }

                return $permohonan->user_id ? 'user:' . $permohonan->user_id : null;
            })
            ->filter()
            ->unique()
            ->count();
    }
}
