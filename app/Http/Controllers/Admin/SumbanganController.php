<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashDonation;
use App\Models\Item;
use App\Models\Sumbangan;
use App\Models\SumbanganItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class SumbanganController extends Controller
{
    public function index()
    {
        $items = Item::query()
            ->aktif()
            ->orderBy('susunan')
            ->orderBy('nama_item')
            ->get()
            ->groupBy('kategori_bantuan');

        $categories = collect(Item::DONATION_CATEGORIES)
            ->map(function (array $category, string $key) use ($items) {
                return [
                    'key' => $key,
                    'title' => $category['title'],
                    'description' => $category['description'],
                    'items' => $items->get($key, collect()),
                ];
            })
            ->values()
            ->all();

        $summaryItems = Item::query()->aktif()->get();
        $totalNeed = $summaryItems->sum('jumlah_diperlukan');
        $totalDonated = $summaryItems->sum('telah_disumbang');
        $totalBalance = $summaryItems->sum('baki');
        $recentSumbangans = Sumbangan::query()
            ->with(['user:id,name,email', 'items:id,sumbangan_id,kategori_bantuan'])
            ->latest()
            ->take(20)
            ->get();

        return view('admin.sumbangan.index', compact(
            'categories',
            'totalNeed',
            'totalDonated',
            'totalBalance',
            'recentSumbangans'
        ));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'nama_item' => ['required', 'string', 'max:255'],
            'harga' => ['required', 'numeric', 'min:0'],
            'jumlah_diperlukan' => ['required', 'integer', 'min:0'],
            'telah_disumbang' => ['required', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', Rule::in(['aktif', 'tidak_aktif'])],
            'susunan' => ['sometimes', 'required', 'integer', 'min:0'],
        ]);

        $payload = [
            'nama_item' => $validated['nama_item'],
            'harga' => $validated['harga'],
            'stok_diperlukan' => $validated['jumlah_diperlukan'],
            'stok_disumbang' => $validated['telah_disumbang'],
        ];

        if (array_key_exists('status', $validated)) {
            $payload['status'] = $validated['status'];
        }

        if (array_key_exists('susunan', $validated)) {
            $payload['susunan'] = $validated['susunan'];
        }

        $item->update($payload);

        return redirect()
            ->route('admin.sumbangan.index')
            ->with('success', 'Stok sumbangan berjaya dikemaskini.');
    }

    public function store(Request $request)
    {
        $validated = $request->validateWithBag('storeItem', [
            'nama_item' => ['required', 'string', 'max:255'],
            'kategori_bantuan' => ['required', Rule::in(array_keys(Item::DONATION_CATEGORIES))],
            'harga' => ['required', 'numeric', 'min:0'],
            'jumlah_diperlukan' => ['required', 'integer', 'min:0'],
            'telah_disumbang' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['aktif', 'tidak_aktif'])],
            'imej' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $imagePath = $this->storeItemImage(
            $request->file('imej'),
            $validated['kategori_bantuan']
        );
        $nextSusunan = ((int) Item::query()
            ->where('kategori_bantuan', $validated['kategori_bantuan'])
            ->max('susunan')) + 1;

        Item::create([
            'nama_item' => $validated['nama_item'],
            'kategori' => Item::legacyCategoryFor($validated['kategori_bantuan']),
            'kategori_bantuan' => $validated['kategori_bantuan'],
            'harga' => $validated['harga'],
            'imej' => $imagePath,
            'stok_diperlukan' => $validated['jumlah_diperlukan'],
            'stok_disumbang' => $validated['telah_disumbang'],
            'status' => $validated['status'],
            'is_active' => $validated['status'] === 'aktif',
            'susunan' => $nextSusunan,
        ]);

        return redirect()
            ->route('admin.sumbangan.index')
            ->with('success', 'Item bantuan baharu berjaya ditambah.');
    }

    public function remove(Item $item)
    {
        $item->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('admin.sumbangan.index')
            ->with('success', 'Item berjaya dibuang daripada senarai aktif.');
    }

    private function storeItemImage($file, string $kategoriBantuan): string
    {
        $folder = Item::legacyCategoryFor($kategoriBantuan);
        $targetDirectory = public_path("image/donations/{$folder}");

        File::ensureDirectoryExists($targetDirectory);

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $baseName = Str::slug($originalName) ?: 'item-bantuan';
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = $baseName . '-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;

        $file->move($targetDirectory, $filename);

        return "donations/{$folder}/{$filename}";
    }

    public function statistik(Request $request)
    {
        $currentYear = now()->year;
        $latestTransactionsSearch = trim((string) $request->query('q_sumbangan', ''));

        $completedSumbangans = Sumbangan::query()
            ->completed()
            ->forDonationYear($currentYear)
            ->with(['user:id,name,email', 'items:id,sumbangan_id,kategori_bantuan'])
            ->get();

        $successfulCashDonations = CashDonation::query()
            ->successful()
            ->forPaymentYear($currentYear)
            ->with('user:id,name,email')
            ->get();

        $allCompletedSumbangans = Sumbangan::query()
            ->completed()
            ->with(['user:id,name,email', 'items:id,sumbangan_id,kategori_bantuan'])
            ->get();

        $allSuccessfulCashDonations = CashDonation::query()
            ->successful()
            ->with('user:id,name,email')
            ->get();

        $itemMonthlyTotals = $completedSumbangans
            ->groupBy(function (Sumbangan $sumbangan) {
                return ($sumbangan->paid_at ?? $sumbangan->created_at)->month;
            })
            ->map(fn (Collection $records) => (float) $records->sum('jumlah_keseluruhan'));

        $cashMonthlyTotals = $successfulCashDonations
            ->groupBy(function (CashDonation $cashDonation) {
                return ($cashDonation->paid_at ?? $cashDonation->created_at)->month;
            })
            ->map(fn (Collection $records) => (float) $records->sum('amount'));

        $monthly = collect(range(1, 12))
            ->map(fn (int $month) => round(
                (float) $itemMonthlyTotals->get($month, 0) + (float) $cashMonthlyTotals->get($month, 0),
                2
            ))
            ->values()
            ->all();

        $categoryTotals = SumbanganItem::query()
            ->whereHas('sumbangan', fn ($query) => $query
                ->completed()
                ->forDonationYear($currentYear))
            ->get(['kategori_bantuan', 'jumlah'])
            ->groupBy('kategori_bantuan')
            ->map(fn ($records) => (float) $records->sum('jumlah'));

        $categoryColors = [
            Item::CATEGORY_KEPERLUAN_ASAS => '#3b82f6',
            Item::CATEGORY_ALAT_TULIS_PEMBELAJARAN => '#06b6d4',
            Item::CATEGORY_PERALATAN_PEMBELAJARAN => '#10b981',
            Item::CATEGORY_SUKAN => '#f59e0b',
        ];

        $categories = collect(Item::DONATION_CATEGORIES)
            ->map(fn (array $category, string $key) => [
                'label' => $category['title'],
                'value' => round((float) $categoryTotals->get($key, 0), 2),
                'color' => $categoryColors[$key] ?? '#64748b',
            ])
            ->values()
            ->push([
                'label' => 'Tabung Bantuan Pelajar',
                'value' => round((float) CashDonation::query()
                    ->successful()
                    ->forPaymentYear($currentYear)
                    ->sum('amount'), 2),
                'color' => '#06b6d4',
            ]);

        $yearTotal = round((float) $categories->sum('value'), 2);
        $overallTotal = round(
            (float) $allCompletedSumbangans->sum('jumlah_keseluruhan') + (float) $allSuccessfulCashDonations->sum('amount'),
            2
        );
        $totalItemCollection = round((float) $allCompletedSumbangans->sum('jumlah_keseluruhan'), 2);
        $totalCashFund = round((float) $allSuccessfulCashDonations->sum('amount'), 2);
        $transactionCount = $allCompletedSumbangans->count() + $allSuccessfulCashDonations->count();
        $simulationDonations = $allCompletedSumbangans
            ->filter(fn (Sumbangan $sumbangan) => in_array(Str::lower(trim((string) $sumbangan->kaedah_sumbangan)), ['simulasi', 'simulasi pembayaran', 'pembayaran atas talian'], true)
                || data_get($sumbangan->payment_payload, 'method') === 'simulasi');
        $simulationTotal = round((float) $simulationDonations->sum('jumlah_keseluruhan'), 2);

        $latestTransactions = $allCompletedSumbangans
            ->map(function (Sumbangan $sumbangan) {
                $fallbackReference = 'SMB-' . str_pad($sumbangan->id, 6, '0', STR_PAD_LEFT);

                return [
                    'type' => 'Sumbangan Barang',
                    'reference' => $sumbangan->no_sumbangan ?: $fallbackReference,
                    'search_reference' => collect([
                        $sumbangan->no_sumbangan,
                        $sumbangan->payment_reference,
                        $sumbangan->toyyibpay_bill_code,
                        $fallbackReference,
                    ])->filter()->implode(' '),
                    'donor' => $sumbangan->user?->name ?? 'Penderma',
                    'email' => $sumbangan->user?->email ?? '-',
                    'amount' => (float) $sumbangan->jumlah_keseluruhan,
                    'method' => $this->paymentMethodLabel($sumbangan->kaedah_sumbangan, $sumbangan->payment_payload),
                    'status' => 'Selesai',
                    'date' => $sumbangan->paid_at ?? $sumbangan->created_at,
                ];
            })
            ->concat($allSuccessfulCashDonations->map(function (CashDonation $cashDonation) {
                $fallbackReference = $this->cashDonationFallbackReference($cashDonation);

                return [
                    'type' => 'Sumbangan Tabung',
                    'reference' => $this->cashDonationReportReference($cashDonation),
                    'search_reference' => collect([
                        $cashDonation->reference_no,
                        $cashDonation->transaction_id,
                        $cashDonation->bill_code,
                        $fallbackReference,
                    ])->filter()->implode(' '),
                    'donor' => $cashDonation->user?->name ?? 'Penderma',
                    'email' => $cashDonation->user?->email ?? '-',
                    'amount' => (float) $cashDonation->amount,
                    'method' => $this->cashDonationMethodLabel($cashDonation),
                    'status' => 'Selesai',
                    'date' => $cashDonation->paid_at ?? $cashDonation->created_at,
                ];
            }));

        if ($latestTransactionsSearch !== '') {
            $latestTransactions = $latestTransactions->filter(
                fn (array $record) => $this->matchesLatestTransactionSearch($record, $latestTransactionsSearch)
            );
        }

        $latestTransactions = $latestTransactions
            ->sortByDesc(fn (array $record) => optional($record['date'])->timestamp ?? 0)
            ->take(10)
            ->values();

        return view('admin.statistik.sumbangan', compact(
            'monthly',
            'categories',
            'yearTotal',
            'overallTotal',
            'currentYear',
            'totalItemCollection',
            'totalCashFund',
            'transactionCount',
            'simulationTotal',
            'simulationDonations',
            'latestTransactions',
            'latestTransactionsSearch'
        ));
    }

    public function statistikCsv()
    {
        $itemRows = Sumbangan::query()
            ->completed()
            ->with('user:id,name,email')
            ->latest('paid_at')
            ->latest('created_at')
            ->get()
            ->map(fn (Sumbangan $sumbangan) => [
                'date' => $sumbangan->paid_at ?? $sumbangan->created_at,
                'row' => [
                    $sumbangan->no_sumbangan ?: ('SMB-' . str_pad($sumbangan->id, 6, '0', STR_PAD_LEFT)),
                    'Sumbangan Barang',
                    $sumbangan->user?->name ?? 'Penderma',
                    $sumbangan->user?->email ?? '-',
                    number_format((float) $sumbangan->jumlah_keseluruhan, 2, '.', ''),
                    $this->paymentMethodLabel($sumbangan->kaedah_sumbangan, $sumbangan->payment_payload),
                    'Selesai',
                    optional($sumbangan->paid_at ?? $sumbangan->created_at)->format('Y-m-d H:i:s'),
                ],
            ]);

        $cashRows = CashDonation::query()
            ->successful()
            ->with('user:id,name,email')
            ->latest('paid_at')
            ->latest('created_at')
            ->get()
            ->map(fn (CashDonation $cashDonation) => [
                'date' => $cashDonation->paid_at ?? $cashDonation->created_at,
                'row' => [
                    $this->cashDonationReportReference($cashDonation),
                    'Sumbangan Tabung',
                    $cashDonation->user?->name ?? 'Penderma',
                    $cashDonation->user?->email ?? '-',
                    number_format((float) $cashDonation->amount, 2, '.', ''),
                    $this->cashDonationMethodLabel($cashDonation),
                    'Selesai',
                    optional($cashDonation->paid_at ?? $cashDonation->created_at)->format('Y-m-d H:i:s'),
                ],
            ]);

        $rows = $itemRows
            ->concat($cashRows)
            ->sortByDesc(fn (array $record) => optional($record['date'])->timestamp ?? 0)
            ->map(fn (array $record) => $record['row'])
            ->values();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Rujukan', 'Jenis', 'Penderma', 'Email', 'Jumlah', 'Kaedah', 'Status', 'Tarikh']);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'laporan-sumbangan-' . now()->format('Ymd-His') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function paymentMethodLabel(?string $method, ?array $payload = null): string
    {
        $normalized = Str::lower(trim((string) $method));

        if (
            in_array($normalized, ['simulasi', 'simulasi pembayaran', 'pembayaran atas talian'], true)
            || data_get($payload, 'method') === 'simulasi'
        ) {
            return 'Pembayaran Atas Talian';
        }

        return filled($method) ? (string) $method : 'ToyyibPay';
    }

    private function cashDonationMethodLabel(CashDonation $cashDonation): string
    {
        if (data_get($cashDonation->raw_response, 'payment_method') === 'simulasi') {
            return 'Pembayaran Atas Talian';
        }

        return $cashDonation->bill_code ? 'ToyyibPay' : 'Pembayaran Atas Talian';
    }

    private function cashDonationReportReference(CashDonation $cashDonation): string
    {
        $referenceNo = trim((string) $cashDonation->reference_no);

        if ($referenceNo !== '') {
            return $referenceNo;
        }

        return $this->cashDonationFallbackReference($cashDonation);
    }

    private function cashDonationFallbackReference(CashDonation $cashDonation): string
    {
        return sprintf('TAB/%s/%06d', ($cashDonation->created_at ?? now())->format('Ymd'), $cashDonation->id);
    }

    private function matchesLatestTransactionSearch(array $record, string $search): bool
    {
        $haystack = collect([
            $record['donor'] ?? null,
            $record['reference'] ?? null,
            $record['search_reference'] ?? null,
            $record['type'] ?? null,
            $record['method'] ?? null,
            $record['status'] ?? null,
        ])
            ->filter()
            ->implode(' ');

        return str_contains(Str::lower($haystack), Str::lower($search));
    }
}
