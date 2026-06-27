<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\CashDonation;
use Illuminate\Http\Request;

class CashDonationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $cashDonations = CashDonation::query()
            ->with('user:id,name,email')
            ->when(in_array($status, [
                CashDonation::STATUS_PENDING,
                CashDonation::STATUS_SUCCESS,
                CashDonation::STATUS_FAILED,
            ], true), fn ($query) => $query->where('payment_status', $status))
            ->latest()
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

        return view('admin.tabung.index', compact('cashDonations', 'summary', 'status', 'tabungTarget', 'tabungProgress'));
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
}
