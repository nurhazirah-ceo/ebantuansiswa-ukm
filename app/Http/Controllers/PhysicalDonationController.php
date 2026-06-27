<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PhysicalDonation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PhysicalDonationController extends Controller
{
    public function create()
    {
        return view('penderma.serahan-barang.create', [
            'categories' => Item::DONATION_CATEGORIES,
            'conditions' => $this->itemConditions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(array_keys(Item::DONATION_CATEGORIES))],
            'quantity' => ['required', 'integer', 'min:1', 'max:999999'],
            'item_condition' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:2048'],
            'donor_phone' => ['nullable', 'string', 'max:30'],
            'donor_address' => ['nullable', 'string', 'max:500'],
            'delivery_method' => [
                'nullable',
                'string',
                Rule::in([PhysicalDonation::DELIVERY_SELF, PhysicalDonation::DELIVERY_COURIER]),
            ],
            'courier_name' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'expected_delivery_date' => ['required', 'date'],
            'delivery_time' => ['nullable', 'string', 'max:100'],
            'delivery_location' => ['nullable', 'string', 'max:500'],
        ], [
            'item_name.required' => 'Sila masukkan nama barang.',
            'category.required' => 'Sila pilih kategori barang.',
            'quantity.required' => 'Sila masukkan kuantiti barang.',
            'quantity.min' => 'Kuantiti mesti sekurang-kurangnya 1.',
            'item_condition.required' => 'Sila pilih keadaan barang.',
            'image.image' => 'Fail bukti mesti dalam format imej.',
            'image.max' => 'Saiz imej maksimum ialah 2MB.',
            'expected_delivery_date.required' => 'Tarikh serahan barang wajib diisi.',
            'expected_delivery_date.date' => 'Tarikh serahan barang tidak sah.',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('physical-donations', 'public')
            : null;

        $isCourier = ($validated['delivery_method'] ?? null) === PhysicalDonation::DELIVERY_COURIER;
        $description = $this->physicalDonationDescription($validated);

        $physicalDonation = PhysicalDonation::create([
            'user_id' => Auth::id(),
            'category' => $validated['category'],
            'item_name' => $validated['item_name'],
            'quantity' => $validated['quantity'],
            'item_condition' => $validated['item_condition'],
            'description' => $description,
            'image_path' => $imagePath,
            'donor_phone' => $validated['donor_phone'] ?? null,
            'donor_address' => $validated['donor_address'] ?? null,
            'delivery_method' => $validated['delivery_method'] ?? null,
            'courier_name' => $isCourier ? ($validated['courier_name'] ?? null) : null,
            'tracking_number' => $isCourier ? ($validated['tracking_number'] ?? null) : null,
            'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
            'status' => PhysicalDonation::STATUS_PENDING_REVIEW,
        ]);

        return redirect()
            ->route('penderma.serahan-barang.show', $physicalDonation)
            ->with('success', 'Serahan barang berjaya dihantar dan sedang menunggu semakan admin.');
    }

    public function show(PhysicalDonation $physicalDonation)
    {
        abort_unless((int) $physicalDonation->user_id === (int) Auth::id(), 404);

        return view('penderma.serahan-barang.show', [
            'physicalDonation' => $physicalDonation,
            'deliveryMethods' => $this->deliveryMethods(),
        ]);
    }

    public function updateDelivery(Request $request, PhysicalDonation $physicalDonation)
    {
        abort_unless((int) $physicalDonation->user_id === (int) Auth::id(), 404);
        abort_unless($physicalDonation->canUpdateDelivery(), 403);

        $validated = $request->validate([
            'delivery_method' => [
                'required',
                'string',
                Rule::in([PhysicalDonation::DELIVERY_SELF, PhysicalDonation::DELIVERY_COURIER]),
            ],
            'courier_name' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'expected_delivery_date' => ['nullable', 'date'],
        ], [
            'delivery_method.required' => 'Sila pilih kaedah serahan.',
        ]);

        $isCourier = $validated['delivery_method'] === PhysicalDonation::DELIVERY_COURIER;

        $physicalDonation->update([
            'delivery_method' => $validated['delivery_method'],
            'courier_name' => $isCourier ? ($validated['courier_name'] ?? null) : null,
            'tracking_number' => $isCourier ? ($validated['tracking_number'] ?? null) : null,
            'expected_delivery_date' => $isCourier ? ($validated['expected_delivery_date'] ?? null) : null,
            'status' => PhysicalDonation::STATUS_AWAITING_DELIVERY,
        ]);

        return redirect()
            ->route('penderma.serahan-barang.show', $physicalDonation)
            ->with('success', 'Maklumat serahan barang berjaya dikemaskini.');
    }

    private function itemConditions(): array
    {
        return [
            'Baharu',
            'Terpakai - Baik',
            'Terpakai - Perlu Semakan',
        ];
    }

    private function deliveryMethods(): array
    {
        return [
            PhysicalDonation::DELIVERY_SELF => 'Serahan sendiri',
            PhysicalDonation::DELIVERY_COURIER => 'Pos/Kurier',
        ];
    }

    private function physicalDonationDescription(array $validated): ?string
    {
        $lines = [];

        if (filled($validated['description'] ?? null)) {
            $lines[] = trim((string) $validated['description']);
        }

        $deliveryLines = [
            filled($validated['expected_delivery_date'] ?? null)
                ? 'Tarikh penghantaran: ' . $validated['expected_delivery_date']
                : null,
            filled($validated['delivery_time'] ?? null)
                ? 'Masa anggaran: ' . $validated['delivery_time']
                : null,
            filled($validated['delivery_location'] ?? null)
                ? 'Lokasi pusat bantuan: ' . $validated['delivery_location']
                : null,
        ];

        $deliveryLines = array_values(array_filter($deliveryLines));

        if (! empty($deliveryLines)) {
            $lines[] = implode("\n", $deliveryLines);
        }

        $description = trim(implode("\n\n", $lines));

        return $description !== '' ? $description : null;
    }
}
