<?php

use App\Models\Item;
use App\Models\PhysicalDonation;
use App\Models\User;

test('donor cannot submit physical donation without delivery date', function () {
    $donor = User::factory()->create([
        'role' => 'penderma',
        'email' => 'penderma@example.com',
    ]);

    $response = $this
        ->actingAs($donor)
        ->from(route('penderma.hantar-barang'))
        ->post(route('penderma.serahan-barang.store'), [
            'item_name' => 'Beras 5kg',
            'category' => Item::CATEGORY_KEPERLUAN_ASAS,
            'quantity' => 2,
            'item_condition' => 'Baharu',
            'donor_phone' => '0123456789',
            'donor_address' => 'Bangi, Selangor',
            'delivery_method' => PhysicalDonation::DELIVERY_SELF,
            'expected_delivery_date' => '',
        ]);

    $response
        ->assertRedirect(route('penderma.hantar-barang'))
        ->assertSessionHasErrors([
            'expected_delivery_date' => 'Tarikh serahan barang wajib diisi.',
        ]);

    $this->assertDatabaseCount('physical_donations', 0);
});

test('donor can submit physical donation with delivery date', function () {
    $donor = User::factory()->create([
        'role' => 'penderma',
        'email' => 'penderma@example.com',
    ]);

    $response = $this
        ->actingAs($donor)
        ->from(route('penderma.hantar-barang'))
        ->post(route('penderma.serahan-barang.store'), [
            'item_name' => 'Beras 5kg',
            'category' => Item::CATEGORY_KEPERLUAN_ASAS,
            'quantity' => 2,
            'item_condition' => 'Baharu',
            'donor_phone' => '0123456789',
            'donor_address' => 'Bangi, Selangor',
            'delivery_method' => PhysicalDonation::DELIVERY_SELF,
            'expected_delivery_date' => '2026-07-01',
            'delivery_time' => '10:00 pagi - 12:00 tengah hari',
        ]);

    $physicalDonation = PhysicalDonation::query()->firstOrFail();

    $response
        ->assertRedirect(route('penderma.serahan-barang.show', $physicalDonation))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('physical_donations', [
        'id' => $physicalDonation->id,
        'user_id' => $donor->id,
        'item_name' => 'Beras 5kg',
        'status' => PhysicalDonation::STATUS_PENDING_REVIEW,
    ]);
    expect($physicalDonation->expected_delivery_date->toDateString())->toBe('2026-07-01');
});

test('physical donation forms render required delivery date field', function () {
    $donor = User::factory()->create([
        'role' => 'penderma',
        'email' => 'penderma@example.com',
    ]);

    $this
        ->actingAs($donor)
        ->get(route('penderma.hantar-barang'))
        ->assertOk()
        ->assertSee('name="expected_delivery_date"', false)
        ->assertSee('required', false)
        ->assertSee('Tarikh Serahan Barang');

    $this
        ->actingAs($donor)
        ->get(route('penderma.serahan-barang.create'))
        ->assertOk()
        ->assertSee('name="expected_delivery_date"', false)
        ->assertSee('required', false)
        ->assertSee('Tarikh Serahan Barang');
});
