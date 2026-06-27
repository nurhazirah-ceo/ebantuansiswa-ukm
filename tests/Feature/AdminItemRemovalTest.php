<?php

use App\Models\Item;
use App\Models\Sumbangan;
use App\Models\User;
use App\Support\AssistanceCatalog;

test('admin removes Highlighter from active displays without deleting historical records', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = User::factory()->create(['role' => 'pelajar']);
    $donor = User::factory()->create(['role' => 'penderma']);

    $item = Item::create([
        'nama_item' => 'Highlighter',
        'kategori' => 'pembelajaran',
        'kategori_bantuan' => Item::CATEGORY_ALAT_TULIS_PEMBELAJARAN,
        'harga' => 4,
        'imej' => 'donations/pembelajaran/highlighter.jpg',
        'stok_diperlukan' => 35,
        'stok_disumbang' => 0,
        'status' => 'aktif',
        'is_active' => true,
        'susunan' => 6,
    ]);

    $sumbangan = Sumbangan::create([
        'user_id' => $donor->id,
        'no_sumbangan' => 'SMB-TEST-HIGHLIGHTER',
        'jumlah_unit' => 1,
        'jumlah_keseluruhan' => 4,
        'status' => Sumbangan::STATUS_SELESAI,
        'kaedah_sumbangan' => 'Simulasi',
    ]);

    $sumbangan->items()->create([
        'item_id' => $item->id,
        'nama_item' => 'Highlighter',
        'kategori_bantuan' => Item::CATEGORY_ALAT_TULIS_PEMBELAJARAN,
        'harga_unit' => 4,
        'kuantiti' => 1,
        'jumlah' => 4,
    ]);

    expect(Item::query()->aktif()->whereKey($item)->exists())->toBeTrue()
        ->and(AssistanceCatalog::learningStationeryItems()->pluck('nama_item'))->toContain('Highlighter');

    $this->actingAs($admin)
        ->get(route('admin.sumbangan.index'))
        ->assertOk()
        ->assertSee('Highlighter');

    $this->actingAs($student)
        ->get(route('alat.tulis'))
        ->assertOk()
        ->assertSee('Highlighter');

    $this->actingAs($student)
        ->get(route('permohonan.index'))
        ->assertOk()
        ->assertSee('Highlighter');

    $this->get(route('penderma.pembelajaran-sumbang'))
        ->assertOk()
        ->assertSee('Highlighter');

    $this->get('/')
        ->assertOk()
        ->assertSee('Highlighter');

    $this->actingAs($admin)
        ->patch(route('admin.sumbangan.remove', $item))
        ->assertRedirect(route('admin.sumbangan.index'))
        ->assertSessionHas('success', 'Item berjaya dibuang daripada senarai aktif.');

    $item->refresh();

    expect($item->is_active)->toBeFalse()
        ->and(Item::query()->whereKey($item)->exists())->toBeTrue()
        ->and(Item::query()->aktif()->whereKey($item)->exists())->toBeFalse()
        ->and(AssistanceCatalog::learningStationeryItems()->pluck('nama_item'))->not->toContain('Highlighter');

    $this->assertDatabaseHas('items', [
        'id' => $item->id,
        'nama_item' => 'Highlighter',
        'is_active' => false,
    ]);

    $this->assertDatabaseHas('sumbangan_items', [
        'sumbangan_id' => $sumbangan->id,
        'item_id' => $item->id,
        'nama_item' => 'Highlighter',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.sumbangan.index'))
        ->assertOk()
        ->assertDontSee('Highlighter');

    $this->actingAs($student)
        ->get(route('alat.tulis'))
        ->assertOk()
        ->assertDontSee('Highlighter');

    $this->actingAs($student)
        ->get(route('permohonan.index'))
        ->assertOk()
        ->assertDontSee('Highlighter');

    $this->get(route('penderma.pembelajaran-sumbang'))
        ->assertOk()
        ->assertDontSee('Highlighter');

    $this->get('/')
        ->assertOk()
        ->assertDontSee('Highlighter');

    $this->actingAs($donor)
        ->from(route('penderma.pembayaran-simulasi'))
        ->post(route('penderma.pembayaran-simulasi.success'), [
            'checkout_reference' => 'TEST-HIGHLIGHTER-INACTIVE',
            'items' => [
                ['id' => $item->id, 'qty' => 1],
            ],
            'donor' => [
                'name' => $donor->name,
                'email' => $donor->email,
                'phone' => '0123456789',
                'address' => 'Bangi, Selangor',
                'city' => 'Bangi',
                'postcode' => '43600',
            ],
        ])
        ->assertRedirect(route('penderma.pembayaran-simulasi'))
        ->assertSessionHasErrors('items');

    $this->actingAs($donor)
        ->get(route('penderma.sejarah-sumbangan.show', $sumbangan->id))
        ->assertOk()
        ->assertSee('Highlighter');
});
