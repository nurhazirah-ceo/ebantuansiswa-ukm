<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\CashDonation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CashDonationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));

        $cashDonations = $this->filteredCashDonationsQuery($request)
            ->orderByRaw('COALESCE(paid_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total_success' => (float) CashDonation::query()
                ->successful()
                ->sum('amount'),
            'success_count' => CashDonation::query()
                ->successful()
                ->count(),
            'pending_count' => CashDonation::query()
                ->pending()
                ->count(),
            'failed_count' => CashDonation::query()
                ->where('payment_status', CashDonation::STATUS_FAILED)
                ->count(),
        ];

        $tabungTarget = max(1, (float) AppSetting::value('tabung_target', 1000000));
        $tabungProgress = min(100, round(($summary['total_success'] / $tabungTarget) * 100, 2));

        return view('admin.tabung.index', compact('cashDonations', 'summary', 'status', 'q', 'tabungTarget', 'tabungProgress'));
    }

    public function export(Request $request)
    {
        $cashDonations = $this->filteredCashDonationsQuery($request)
            ->orderByRaw('COALESCE(paid_at, created_at) DESC')
            ->orderByDesc('id')
            ->get();

        return response()->streamDownload(function () use ($cashDonations) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Nama Penderma',
                'Jumlah',
                'Status',
                'Tarikh',
                'Transaksi / Bill Code',
            ]);

            foreach ($cashDonations as $donation) {
                $references = array_filter([
                    $donation->transaction_id,
                    $donation->bill_code,
                ], fn ($value) => filled($value));

                fputcsv($handle, [
                    $donation->user?->name ?? 'Penderma',
                    number_format((float) $donation->amount, 2, '.', ''),
                    $this->cashDonationStatusLabel($donation->payment_status),
                    optional($donation->paid_at ?? $donation->created_at)->format('Y-m-d H:i:s') ?: '-',
                    $references === [] ? '-' : implode(' / ', $references),
                ]);
            }

            fclose($handle);
        }, 'transaksi-tabung.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function updateTarget(Request $request)
    {
        $validated = $request->validate([
            'target_amount' => ['required', 'numeric', 'min:1'],
        ], [
            'target_amount.required' => 'Sila masukkan sasaran tabung.',
            'target_amount.numeric' => 'Sasaran tabung mesti dalam format nombor.',
            'target_amount.min' => 'Sasaran tabung mesti sekurang-kurangnya RM1.00.',
        ]);

        AppSetting::put('tabung_target', round((float) $validated['target_amount'], 2));

        return redirect()
            ->route('admin.tabung.index')
            ->with('success', 'Target tabung berjaya dikemaskini.');
    }

    private function filteredCashDonationsQuery(Request $request): Builder
    {
        $status = (string) $request->query('status', '');
        $q = trim((string) $request->query('q', ''));

        return CashDonation::query()
            ->with('user:id,name,email')
            ->when($this->isFilterableStatus($status), fn (Builder $query) => $query->where('payment_status', $status))
            ->when($q !== '', function (Builder $query) use ($q) {
                $query->where(function (Builder $query) use ($q) {
                    $query
                        ->whereHas('user', fn (Builder $query) => $query->where('name', 'like', "%{$q}%"))
                        ->orWhere('transaction_id', 'like', "%{$q}%")
                        ->orWhere('bill_code', 'like', "%{$q}%");
                });
            });
    }

    private function isFilterableStatus(string $status): bool
    {
        return in_array($status, [
            CashDonation::STATUS_PENDING,
            CashDonation::STATUS_SUCCESS,
            CashDonation::STATUS_FAILED,
        ], true);
    }

    private function cashDonationStatusLabel(?string $status): string
    {
        return match ($status) {
            CashDonation::STATUS_SUCCESS => 'Selesai',
            CashDonation::STATUS_PENDING => 'Menunggu Bayaran',
            CashDonation::STATUS_FAILED => 'Gagal',
            default => filled($status) ? ucfirst((string) $status) : '-',
        };
    }
}
