<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhysicalDonation;
use Illuminate\Http\Request;

class PhysicalDonationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $statuses = [
            PhysicalDonation::STATUS_PENDING_REVIEW,
            PhysicalDonation::STATUS_APPROVED,
            PhysicalDonation::STATUS_REJECTED,
            PhysicalDonation::STATUS_AWAITING_DELIVERY,
            PhysicalDonation::STATUS_RECEIVED,
        ];

        $physicalDonations = PhysicalDonation::query()
            ->with('donor:id,name,email')
            ->when(in_array($status, $statuses, true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'pending_review' => PhysicalDonation::query()->where('status', PhysicalDonation::STATUS_PENDING_REVIEW)->count(),
            'approved' => PhysicalDonation::query()->where('status', PhysicalDonation::STATUS_APPROVED)->count(),
            'awaiting_delivery' => PhysicalDonation::query()->where('status', PhysicalDonation::STATUS_AWAITING_DELIVERY)->count(),
            'received' => PhysicalDonation::query()->where('status', PhysicalDonation::STATUS_RECEIVED)->count(),
        ];

        return view('admin.serahan-barang.index', compact('physicalDonations', 'summary', 'status', 'statuses'));
    }

    public function show(PhysicalDonation $physicalDonation)
    {
        $physicalDonation->load('donor:id,name,email');

        return view('admin.serahan-barang.show', compact('physicalDonation'));
    }

    public function approve(Request $request, PhysicalDonation $physicalDonation)
    {
        abort_unless($physicalDonation->canReview(), 403);

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $physicalDonation->update([
            'status' => PhysicalDonation::STATUS_APPROVED,
            'admin_note' => $validated['admin_note'] ?? null,
            'rejection_reason' => null,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('admin.serahan-barang.show', $physicalDonation)
            ->with('success', 'Serahan barang telah diluluskan.');
    }

    public function reject(Request $request, PhysicalDonation $physicalDonation)
    {
        abort_unless($physicalDonation->canReview(), 403);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Sebab penolakan wajib diisi.',
        ]);

        $physicalDonation->update([
            'status' => PhysicalDonation::STATUS_REJECTED,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()
            ->route('admin.serahan-barang.show', $physicalDonation)
            ->with('success', 'Serahan barang telah ditolak.');
    }

    public function received(Request $request, PhysicalDonation $physicalDonation)
    {
        abort_unless($physicalDonation->canMarkReceived(), 403);

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $physicalDonation->update([
            'status' => PhysicalDonation::STATUS_RECEIVED,
            'admin_note' => $validated['admin_note'] ?? $physicalDonation->admin_note,
            'received_at' => now(),
        ]);

        return redirect()
            ->route('admin.serahan-barang.show', $physicalDonation)
            ->with('success', 'Barang telah disahkan diterima.');
    }
}
