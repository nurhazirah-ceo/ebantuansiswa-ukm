<?php

use App\Models\Donor;
use App\Models\Item;
use App\Models\Sumbangan;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.toyyibpay.base_url' => 'https://dev.toyyibpay.com',
        'services.toyyibpay.secret_key' => 'test-secret-key',
        'services.toyyibpay.category_code' => 'test-category',
        'services.toyyibpay.payment_channel' => '0',
        'services.toyyibpay.charge_to_customer' => '1',
    ]);
});

test('checkout pre-fills donor profile and address details from database', function () {
    $user = donorUserWithAddress();

    $this
        ->actingAs($user)
        ->get(route('penderma.checkout-sumbangan'))
        ->assertOk()
        ->assertSee('Petronas')
        ->assertSee('petronas@example.com')
        ->assertSee('0182532215')
        ->assertSee('01122223333')
        ->assertSee('NO 11 TAMAN MAHSURI INDAH')
        ->assertSee('TAMPIN')
        ->assertSee('73000')
        ->assertSee('Negeri Sembilan')
        ->assertSee('Malaysia');
});

test('donation stores donor snapshot and updates user donor and address profile', function () {
    Http::fake([
        'https://dev.toyyibpay.com/index.php/api/createBill' => Http::response([
            ['BillCode' => 'BILL-SMB-SNAPSHOT'],
        ]),
    ]);

    $user = donorUserWithAddress();
    $item = donationItem();

    $response = $this
        ->actingAs($user)
        ->postJson(route('penderma.sumbangan.store'), [
            'items' => [
                ['id' => $item->id, 'qty' => 2],
            ],
            'kaedah_sumbangan' => 'Pembayaran Atas Talian',
            'donor' => [
                'name' => 'Petronas Dagangan',
                'email' => 'receipt@example.com',
                'phone' => '0198765432',
                'alt_phone' => '01123456789',
                'address' => 'Lot 8 Jalan Industri',
                'city' => 'Bangi',
                'postcode' => '43650',
                'state' => 'Selangor',
                'country' => 'Malaysia',
            ],
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('redirect_url', 'https://dev.toyyibpay.com/BILL-SMB-SNAPSHOT');

    $sumbangan = Sumbangan::query()->firstOrFail();

    expect(data_get($sumbangan->donor_snapshot, 'name'))->toBe('Petronas Dagangan')
        ->and(data_get($sumbangan->donor_snapshot, 'email'))->toBe('receipt@example.com')
        ->and(data_get($sumbangan->donor_snapshot, 'phone'))->toBe('0198765432')
        ->and(data_get($sumbangan->donor_snapshot, 'alt_phone'))->toBe('01123456789')
        ->and(data_get($sumbangan->donor_snapshot, 'address'))->toBe('Lot 8 Jalan Industri')
        ->and(data_get($sumbangan->donor_snapshot, 'city'))->toBe('Bangi')
        ->and(data_get($sumbangan->donor_snapshot, 'postcode'))->toBe('43650')
        ->and(data_get($sumbangan->donor_snapshot, 'state'))->toBe('Selangor')
        ->and(data_get($sumbangan->donor_snapshot, 'country'))->toBe('Malaysia');

    $donor = Donor::with('address')->where('user_id', $user->id)->firstOrFail();
    $user->refresh();

    expect($user->name)->toBe('Petronas Dagangan')
        ->and($user->email)->toBe('receipt@example.com')
        ->and($donor->phone)->toBe('0198765432')
        ->and($donor->alt_phone)->toBe('01123456789')
        ->and($donor->address->address_line_1)->toBe('Lot 8 Jalan Industri')
        ->and($donor->address->city)->toBe('Bangi')
        ->and($donor->address->postcode)->toBe('43650')
        ->and($donor->address->state)->toBe('Selangor')
        ->and($donor->address->country)->toBe('Malaysia');

    Http::assertSent(fn ($request) => $request->url() === 'https://dev.toyyibpay.com/index.php/api/createBill'
        && $request['billTo'] === 'Petronas Dagangan'
        && $request['billEmail'] === 'receipt@example.com'
        && $request['billPhone'] === '0198765432');
});

test('donation validation rejects empty address snapshot and invalid Malaysian postcode', function () {
    Http::fake();

    $user = donorUserWithAddress();
    $item = donationItem();

    $this
        ->actingAs($user)
        ->postJson(route('penderma.sumbangan.store'), [
            'items' => [
                ['id' => $item->id, 'qty' => 1],
            ],
            'kaedah_sumbangan' => 'Pembayaran Atas Talian',
            'donor' => [
                'name' => 'Petronas',
                'email' => 'petronas@example.com',
                'phone' => '0182532215',
                'address' => '',
                'city' => 'Tampin',
                'postcode' => '7300',
                'state' => 'Negeri Sembilan',
                'country' => 'Malaysia',
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['donor.address', 'donor.postcode']);

    expect(Sumbangan::query()->count())->toBe(0);
});

test('receipt displays saved donor snapshot after donor profile changes', function () {
    $user = donorUserWithAddress();
    $sumbangan = Sumbangan::create([
        'user_id' => $user->id,
        'no_sumbangan' => 'SMB/20260628/000001',
        'jumlah_unit' => 1,
        'jumlah_keseluruhan' => 100,
        'status' => Sumbangan::STATUS_SELESAI,
        'kaedah_sumbangan' => 'Pembayaran Atas Talian',
        'donor_snapshot' => [
            'name' => 'Snapshot Donor',
            'email' => 'snapshot@example.com',
            'phone' => '0181111111',
            'alt_phone' => '01111111111',
            'address' => 'Alamat Lama Resit',
            'city' => 'Tampin',
            'postcode' => '73000',
            'state' => 'Negeri Sembilan',
            'country' => 'Malaysia',
        ],
        'paid_at' => now(),
    ]);

    $user->update([
        'name' => 'Profile Baru',
        'email' => 'profile-baru@example.com',
    ]);
    $user->donor->address->update([
        'address_line_1' => 'Alamat Profil Baru',
        'city' => 'Kajang',
        'postcode' => '43000',
        'state' => 'Selangor',
        'country' => 'Malaysia',
    ]);

    $this
        ->actingAs($user)
        ->get(route('penderma.sumbangan.receipt', ['id' => $sumbangan->id]))
        ->assertOk()
        ->assertSee('Snapshot Donor')
        ->assertSee('snapshot@example.com')
        ->assertSee('Alamat Lama Resit')
        ->assertDontSee('profile-baru@example.com')
        ->assertDontSee('Alamat Profil Baru');
});

function donorUserWithAddress(): User
{
    $user = User::factory()->create([
        'name' => 'Petronas',
        'email' => 'petronas@example.com',
        'role' => 'penderma',
    ]);

    $donor = $user->donor()->create([
        'donor_type' => 'syarikat',
        'phone' => '0182532215',
        'alt_phone' => '01122223333',
        'preferred_contact' => 'phone',
    ]);

    $donor->address()->create([
        'address_line_1' => 'NO 11 TAMAN MAHSURI INDAH',
        'address_line_2' => 'Jalan Mahsuri 1',
        'city' => 'TAMPIN',
        'postcode' => '73000',
        'state' => 'Negeri Sembilan',
        'country' => 'Malaysia',
    ]);

    return $user;
}

function donationItem(): Item
{
    return Item::create([
        'nama_item' => 'Peralatan Pembelajaran',
        'kategori' => 'pembelajaran',
        'kategori_bantuan' => Item::CATEGORY_PERALATAN_PEMBELAJARAN,
        'harga' => 50,
        'stok_diperlukan' => 10,
        'stok_disumbang' => 0,
        'status' => 'aktif',
        'is_active' => true,
        'susunan' => 1,
    ]);
}
