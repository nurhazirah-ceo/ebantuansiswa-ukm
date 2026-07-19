<?php

use App\Models\Donor;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

function useWritablePublicDisk(): void
{
    $root = storage_path('app/testing-public');

    File::deleteDirectory($root);
    File::ensureDirectoryExists($root);

    config(['filesystems.disks.public.root' => $root]);
    Storage::forgetDisk('public');
}

function createListedDonor(
    string $name,
    string $email,
    array $donorAttributes = [],
    ?string $registeredAt = null
): Donor {
    $user = User::factory()->create([
        'role' => 'penderma',
        'name' => $name,
        'email' => $email,
    ]);

    if ($registeredAt) {
        $user->forceFill([
            'created_at' => $registeredAt,
            'updated_at' => $registeredAt,
        ])->save();
    }

    return Donor::create(array_merge([
        'user_id' => $user->id,
        'donor_type' => 'individu',
        'phone' => '0123456789',
        'preferred_contact' => 'email',
        'homepage_order' => null,
        'show_on_homepage' => false,
    ], $donorAttributes));
}

test('admin can create individual donor with Malaysian mobile phone number', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.penderma.create'))
        ->post(route('admin.penderma.store'), [
            'donor_type' => 'individu',
            'name' => 'Ahmad Penderma',
            'email' => 'ahmad@example.com',
            'phone' => '0123456789',
            'preferred_contact' => 'phone',
            'address_line_1' => 'Bangunan Komuniti UKM',
            'address_line_2' => 'Aras 2',
            'city' => 'Bangi',
            'postcode' => '43600',
            'state' => 'Selangor',
            'country' => 'Malaysia',
            'homepage_order' => 1,
        ]);

    $response
        ->assertRedirect(route('admin.penderma.index'))
        ->assertSessionHasNoErrors();

    $user = User::query()->where('email', 'ahmad@example.com')->firstOrFail();
    $donor = Donor::query()->where('user_id', $user->id)->firstOrFail();

    expect($donor->homepage_order)->toBeNull()
        ->and($donor->show_on_homepage)->toBeFalse();

    $this->assertDatabaseHas('donors', [
        'user_id' => $user->id,
        'donor_type' => 'individu',
        'phone' => '0123456789',
    ]);
    $this->assertDatabaseHas('addresses', [
        'donor_id' => $donor->id,
        'postcode' => '43600',
        'country' => 'Malaysia',
    ]);
});

test('admin can create donor with logo support document ranking and homepage flag', function () {
    useWritablePublicDisk();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.penderma.create'))
        ->post(route('admin.penderma.store'), [
            'donor_type' => 'syarikat',
            'company_name' => 'Syarikat Ranking Sdn Bhd',
            'company_email' => 'ranking@example.com',
            'company_phone' => '0123456789',
            'representative_name' => 'Nur Wakil',
            'preferred_contact' => 'email',
            'address_line_1' => 'Bangunan Komuniti UKM',
            'address_line_2' => 'Aras 2',
            'city' => 'Bangi',
            'postcode' => '43600',
            'state' => 'Selangor',
            'country' => 'Malaysia',
            'logo' => UploadedFile::fake()->image('ranking-logo.png', 120, 120),
            'support_document' => UploadedFile::fake()->create('dokumen-sokongan.pdf', 120, 'application/pdf'),
            'homepage_order' => 2,
            'show_on_homepage' => '1',
        ]);

    $response
        ->assertRedirect(route('admin.penderma.index'))
        ->assertSessionHasNoErrors();

    $user = User::query()->where('email', 'ranking@example.com')->firstOrFail();
    $donor = Donor::query()->where('user_id', $user->id)->firstOrFail();

    expect($donor->homepage_order)->toBe(2)
        ->and($donor->show_on_homepage)->toBeTrue()
        ->and($donor->homepage_label)->toBeNull();

    Storage::disk('public')->assertExists($donor->logo);
    Storage::disk('public')->assertExists($donor->support_document);
    expect($donor->support_document)->toStartWith('donor-documents/');
});

test('admin can create organisation donors with representative name', function (array $payload, string $donorType, string $email) {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.penderma.create'))
        ->post(route('admin.penderma.store'), array_merge([
            'donor_type' => $donorType,
            'representative_name' => 'Nur Wakil Organisasi',
            'preferred_contact' => 'email',
            'address_line_1' => 'Bangunan Komuniti UKM',
            'address_line_2' => 'Aras 2',
            'city' => 'Bangi',
            'postcode' => '43600',
            'state' => 'Selangor',
            'country' => 'Malaysia',
            'homepage_order' => 1,
        ], $payload));

    $response
        ->assertRedirect(route('admin.penderma.index'))
        ->assertSessionHasNoErrors();

    $user = User::query()->where('email', $email)->firstOrFail();
    $donor = Donor::query()->where('user_id', $user->id)->firstOrFail();

    $this->assertDatabaseHas('donors', [
        'user_id' => $user->id,
        'donor_type' => $donorType,
        'representative_name' => 'Nur Wakil Organisasi',
        'phone' => $payload[$donorType === 'syarikat' ? 'company_phone' : 'ngo_phone'],
    ]);
    $this->assertDatabaseMissing('donors', [
        'user_id' => $user->id,
        'representative_name' => 'REG-001',
    ]);
    $this->assertDatabaseHas('addresses', [
        'donor_id' => $donor->id,
        'postcode' => '43600',
        'country' => 'Malaysia',
    ]);
})->with([
    'syarikat' => [
        [
            'company_name' => 'Syarikat Prihatin Sdn Bhd',
            'company_email' => 'syarikat@example.com',
            'company_phone' => '0123456789',
        ],
        'syarikat',
        'syarikat@example.com',
    ],
    'ngo' => [
        [
            'ngo_name' => 'NGO Prihatin',
            'ngo_email' => 'ngo@example.com',
            'ngo_phone' => '01123456789',
        ],
        'ngo',
        'ngo@example.com',
    ],
]);

test('admin cannot create donor with invalid Malaysian mobile phone number', function (array $payload, string $field) {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.penderma.create'))
        ->post(route('admin.penderma.store'), array_merge([
            'preferred_contact' => 'phone',
            'address_line_1' => 'Bangunan Komuniti UKM',
            'address_line_2' => 'Aras 2',
            'city' => 'Bangi',
            'postcode' => '43600',
            'state' => 'Selangor',
            'country' => 'Malaysia',
            'homepage_order' => 1,
        ], $payload));

    $response
        ->assertRedirect(route('admin.penderma.create'))
        ->assertSessionHasErrors([
            $field => 'Sila masukkan nombor telefon yang sah. Contoh: 0123456789',
        ]);

    $this->assertDatabaseCount('donors', 0);
})->with([
    'individu terlalu pendek' => [
        [
            'donor_type' => 'individu',
            'name' => 'Ahmad Penderma',
            'email' => 'ahmad@example.com',
            'phone' => '123456',
        ],
        'phone',
    ],
    'individu nombor talian tetap' => [
        [
            'donor_type' => 'individu',
            'name' => 'Aminah Penderma',
            'email' => 'aminah@example.com',
            'phone' => '0312345678',
        ],
        'phone',
    ],
    'syarikat terlalu panjang' => [
        [
            'donor_type' => 'syarikat',
            'company_name' => 'Syarikat Prihatin Sdn Bhd',
            'company_email' => 'syarikat@example.com',
            'company_phone' => '012345678901234',
            'representative_name' => 'Nur Wakil Organisasi',
        ],
        'company_phone',
    ],
    'ngo mengandungi huruf' => [
        [
            'donor_type' => 'ngo',
            'ngo_name' => 'NGO Prihatin',
            'ngo_email' => 'ngo@example.com',
            'ngo_phone' => 'abc123',
            'representative_name' => 'Nur Wakil Organisasi',
        ],
        'ngo_phone',
    ],
]);

test('admin can create donor with custom country from lain-lain option', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.penderma.create'))
        ->post(route('admin.penderma.store'), [
            'donor_type' => 'individu',
            'name' => 'Aisyah Penderma',
            'email' => 'aisyah@example.com',
            'phone' => '01123456789',
            'preferred_contact' => 'email',
            'address_line_1' => 'Bangunan Komuniti UKM',
            'address_line_2' => 'Aras 2',
            'city' => 'Bangi',
            'postcode' => '43600',
            'state' => 'Selangor',
            'country' => 'Lain-lain',
            'country_other' => 'Japan',
            'homepage_order' => 1,
        ]);

    $response
        ->assertRedirect(route('admin.penderma.index'))
        ->assertSessionHasNoErrors();

    $user = User::query()->where('email', 'aisyah@example.com')->firstOrFail();
    $donor = Donor::query()->where('user_id', $user->id)->firstOrFail();

    $this->assertDatabaseHas('addresses', [
        'donor_id' => $donor->id,
        'country' => 'Japan',
    ]);
});

test('admin donor address forms render country selector and postcode controls', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);
    $donorUser = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Syarikat Prihatin Sdn Bhd',
        'email' => 'syarikat@example.com',
    ]);
    $donor = Donor::create([
        'user_id' => $donorUser->id,
        'donor_type' => 'syarikat',
        'representative_name' => 'Wakil Organisasi',
        'phone' => '0123456789',
        'preferred_contact' => 'email',
    ]);
    $donor->address()->create([
        'address_line_1' => 'Bangunan Komuniti UKM',
        'city' => 'Bangi',
        'postcode' => '43600',
        'state' => 'Selangor',
        'country' => 'Japan',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.penderma.create'))
        ->assertOk()
        ->assertSee('data-country-select', false)
        ->assertSee('data-postcode-input', false)
        ->assertSee('Sila nyatakan negara');

    $this
        ->actingAs($admin)
        ->get(route('admin.penderma.edit', $donorUser))
        ->assertOk()
        ->assertSee('data-country-select', false)
        ->assertSee('data-postcode-input', false)
        ->assertSee('Japan');

    $this
        ->actingAs($admin)
        ->get(route('admin.penderma.index'))
        ->assertOk()
        ->assertSee('id="editCountry"', false)
        ->assertSee('data-postcode-input', false);
});

test('admin donor list renders updated columns with pagination and keeps edit payload fields', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    for ($i = 1; $i <= 15; $i++) {
        createListedDonor(
            "Penderma Filler {$i}",
            "filler{$i}@example.com",
            [],
            now()->subDays($i)->toDateTimeString()
        );
    }

    createListedDonor('Penderma Payload', 'payload@example.com', [
        'logo' => 'donor-logos/payload.png',
    ], now()->toDateTimeString());

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.penderma.index'));

    $response
        ->assertOk()
        ->assertSee('placeholder="Cari nama atau emel"', false)
        ->assertSee('No.')
        ->assertSee('Nama Penderma')
        ->assertSee('Jenis')
        ->assertDontSee('>Logo</th>', false)
        ->assertDontSee('>Emel</th>', false)
        ->assertSee('"emel":"payload@example.com"', false)
        ->assertSee('"logoUrl"', false);

    $donors = $response->viewData('donors');

    expect($donors)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class)
        ->and($donors->perPage())->toBe(15)
        ->and($donors->count())->toBe(15)
        ->and($donors->total())->toBe(16);

    $this
        ->actingAs($admin)
        ->get(route('admin.penderma.index', [
            'search' => 'Penderma',
            'sort' => 'name_az',
        ]))
        ->assertOk()
        ->assertSee('search=Penderma', false)
        ->assertSee('sort=name_az', false);
});

test('admin donor list filters by search homepage and donor type', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    createListedDonor('Penderma Individu Dipapar', 'individu-visible@example.com', [
        'donor_type' => 'individu',
        'homepage_order' => 1,
        'show_on_homepage' => true,
    ]);
    createListedDonor('Syarikat Tersembunyi', 'hidden-company@example.com', [
        'donor_type' => 'syarikat',
        'show_on_homepage' => false,
    ]);
    createListedDonor('NGO Dipapar', 'ngo-visible@example.com', [
        'donor_type' => 'ngo',
        'homepage_order' => 2,
        'show_on_homepage' => true,
    ]);

    $searchedNames = $this
        ->actingAs($admin)
        ->get(route('admin.penderma.index', ['search' => 'hidden-company']))
        ->assertOk()
        ->viewData('donors')
        ->getCollection()
        ->map(fn (Donor $donor) => $donor->user->name)
        ->all();

    expect($searchedNames)->toBe(['Syarikat Tersembunyi']);

    $displayedNames = $this
        ->actingAs($admin)
        ->get(route('admin.penderma.index', ['homepage' => 'displayed']))
        ->assertOk()
        ->viewData('donors')
        ->getCollection()
        ->map(fn (Donor $donor) => $donor->user->name)
        ->all();

    expect($displayedNames)
        ->toContain('Penderma Individu Dipapar')
        ->toContain('NGO Dipapar')
        ->not->toContain('Syarikat Tersembunyi');

    $organisationNames = $this
        ->actingAs($admin)
        ->get(route('admin.penderma.index', ['jenis' => 'organisasi']))
        ->assertOk()
        ->viewData('donors')
        ->getCollection()
        ->map(fn (Donor $donor) => $donor->user->name)
        ->all();

    expect($organisationNames)
        ->toContain('Syarikat Tersembunyi')
        ->toContain('NGO Dipapar')
        ->not->toContain('Penderma Individu Dipapar');
});

test('admin donor list sorts by registration name and homepage ranking', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    createListedDonor('Beta Lama', 'beta@example.com', [
        'homepage_order' => 2,
        'show_on_homepage' => true,
    ], now()->subDays(5)->toDateTimeString());
    createListedDonor('Alpha Baharu', 'alpha@example.com', [
        'homepage_order' => 1,
        'show_on_homepage' => true,
    ], now()->subDay()->toDateTimeString());
    createListedDonor('Hidden Ranked', 'hidden-ranked@example.com', [
        'donor_type' => 'syarikat',
        'homepage_order' => 9,
        'show_on_homepage' => false,
    ], now()->subDays(2)->toDateTimeString());

    $latestNames = $this
        ->actingAs($admin)
        ->get(route('admin.penderma.index', ['sort' => 'latest']))
        ->assertOk()
        ->viewData('donors')
        ->getCollection()
        ->map(fn (Donor $donor) => $donor->user->name)
        ->all();

    expect($latestNames[0])->toBe('Alpha Baharu');

    $oldestNames = $this
        ->actingAs($admin)
        ->get(route('admin.penderma.index', ['sort' => 'oldest']))
        ->assertOk()
        ->viewData('donors')
        ->getCollection()
        ->map(fn (Donor $donor) => $donor->user->name)
        ->all();

    expect($oldestNames[0])->toBe('Beta Lama');

    $nameSortedNames = $this
        ->actingAs($admin)
        ->get(route('admin.penderma.index', ['sort' => 'name_az']))
        ->assertOk()
        ->viewData('donors')
        ->getCollection()
        ->map(fn (Donor $donor) => $donor->user->name)
        ->all();

    expect($nameSortedNames[0])->toBe('Alpha Baharu');

    $rankingResponse = $this
        ->actingAs($admin)
        ->get(route('admin.penderma.index', ['sort' => 'ranking']))
        ->assertOk()
        ->assertSee('&mdash;', false);

    $rankingNames = $rankingResponse
        ->viewData('donors')
        ->getCollection()
        ->map(fn (Donor $donor) => $donor->user->name)
        ->all();

    expect($rankingNames[0])->toBe('Alpha Baharu')
        ->and($rankingNames[1])->toBe('Beta Lama')
        ->and(array_search('Hidden Ranked', $rankingNames, true))->toBeGreaterThan(1);
});

test('admin donor list ignores invalid filter and sort values', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    createListedDonor('Penderma Selamat', 'safe@example.com');

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.penderma.index', [
            'homepage' => 'invalid',
            'jenis' => 'invalid',
            'sort' => 'invalid',
        ]));

    $response->assertOk();

    expect($response->viewData('homepageFilter'))->toBe('all')
        ->and($response->viewData('jenisFilter'))->toBe('all')
        ->and($response->viewData('sortBy'))->toBe('latest')
        ->and($response->viewData('donors')->total())->toBe(1);
});

test('admin cannot create donor with invalid postcode', function (string $postcode) {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.penderma.create'))
        ->post(route('admin.penderma.store'), [
            'donor_type' => 'individu',
            'name' => 'Ahmad Penderma',
            'email' => 'ahmad@example.com',
            'phone' => '0123456789',
            'preferred_contact' => 'phone',
            'address_line_1' => 'Bangunan Komuniti UKM',
            'address_line_2' => 'Aras 2',
            'city' => 'Bangi',
            'postcode' => $postcode,
            'state' => 'Selangor',
            'country' => 'Malaysia',
            'homepage_order' => 1,
        ]);

    $response
        ->assertRedirect(route('admin.penderma.create'))
        ->assertSessionHasErrors([
            'postcode' => 'Sila masukkan poskod yang sah (5 digit).',
        ]);

    $this->assertDatabaseCount('donors', 0);
})->with([
    'terlalu pendek' => ['7300'],
    'terlalu panjang' => ['730000'],
    'mengandungi huruf' => ['ABC12'],
]);

test('admin can update donor representative name', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);
    $donorUser = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Syarikat Prihatin Sdn Bhd',
        'email' => 'syarikat@example.com',
    ]);
    $donor = Donor::create([
        'user_id' => $donorUser->id,
        'donor_type' => 'syarikat',
        'representative_name' => 'Wakil Lama',
        'phone' => '0123456789',
        'preferred_contact' => 'email',
    ]);
    $donor->address()->create([
        'address_line_1' => 'Bangunan Komuniti UKM',
        'city' => 'Bangi',
        'postcode' => '43600',
        'state' => 'Selangor',
        'country' => 'Malaysia',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.penderma.index'))
        ->put(route('admin.penderma.update', $donorUser), [
            'name' => 'Syarikat Prihatin Sdn Bhd',
            'email' => 'syarikat@example.com',
            'phone' => '01123456789',
            'representative_name' => 'Wakil Baharu',
            'preferred_contact' => 'email',
            'address_line_1' => 'Bangunan Komuniti UKM',
            'city' => 'Bangi',
            'postcode' => '43600',
            'state' => 'Selangor',
            'country' => 'Singapore',
            'homepage_order' => 1,
        ]);

    $response
        ->assertRedirect(route('admin.penderma.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('donors', [
        'id' => $donor->id,
        'representative_name' => 'Wakil Baharu',
        'phone' => '01123456789',
    ]);
    $this->assertDatabaseHas('addresses', [
        'donor_id' => $donor->id,
        'country' => 'Singapore',
        'postcode' => '43600',
    ]);
});

test('admin cannot update donor with invalid Malaysian mobile phone number', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);
    $donorUser = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Syarikat Prihatin Sdn Bhd',
        'email' => 'syarikat@example.com',
    ]);
    $donor = Donor::create([
        'user_id' => $donorUser->id,
        'donor_type' => 'syarikat',
        'representative_name' => 'Wakil Lama',
        'phone' => '0123456789',
        'preferred_contact' => 'email',
    ]);
    $donor->address()->create([
        'address_line_1' => 'Bangunan Komuniti UKM',
        'city' => 'Bangi',
        'postcode' => '43600',
        'state' => 'Selangor',
        'country' => 'Malaysia',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.penderma.index'))
        ->put(route('admin.penderma.update', $donorUser), [
            'name' => 'Syarikat Prihatin Sdn Bhd',
            'email' => 'syarikat@example.com',
            'phone' => '012345678901234',
            'representative_name' => 'Wakil Baharu',
            'preferred_contact' => 'email',
            'address_line_1' => 'Bangunan Komuniti UKM',
            'city' => 'Bangi',
            'postcode' => '43600',
            'state' => 'Selangor',
            'country' => 'Malaysia',
            'homepage_order' => 1,
        ]);

    $response
        ->assertRedirect(route('admin.penderma.index'))
        ->assertSessionHasErrors([
            'phone' => 'Sila masukkan nombor telefon yang sah. Contoh: 0123456789',
        ]);

    $this->assertDatabaseHas('donors', [
        'id' => $donor->id,
        'representative_name' => 'Wakil Lama',
        'phone' => '0123456789',
    ]);
});

test('admin cannot update donor with invalid postcode', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);
    $donorUser = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Syarikat Prihatin Sdn Bhd',
        'email' => 'syarikat@example.com',
    ]);
    $donor = Donor::create([
        'user_id' => $donorUser->id,
        'donor_type' => 'syarikat',
        'representative_name' => 'Wakil Lama',
        'phone' => '0123456789',
        'preferred_contact' => 'email',
    ]);
    $donor->address()->create([
        'address_line_1' => 'Bangunan Komuniti UKM',
        'city' => 'Bangi',
        'postcode' => '43600',
        'state' => 'Selangor',
        'country' => 'Malaysia',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.penderma.index'))
        ->put(route('admin.penderma.update', $donorUser), [
            'name' => 'Syarikat Prihatin Sdn Bhd',
            'email' => 'syarikat@example.com',
            'phone' => '0123456789',
            'representative_name' => 'Wakil Baharu',
            'preferred_contact' => 'email',
            'address_line_1' => 'Bangunan Komuniti UKM',
            'city' => 'Bangi',
            'postcode' => 'ABC12',
            'state' => 'Selangor',
            'country' => 'Malaysia',
            'homepage_order' => 1,
        ]);

    $response
        ->assertRedirect(route('admin.penderma.index'))
        ->assertSessionHasErrors([
            'postcode' => 'Sila masukkan poskod yang sah (5 digit).',
        ]);

    $this->assertDatabaseHas('addresses', [
        'donor_id' => $donor->id,
        'postcode' => '43600',
        'country' => 'Malaysia',
    ]);
});

test('admin can update donor logo support document ranking and homepage flag', function () {
    useWritablePublicDisk();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);
    $donorUser = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Syarikat Lama Sdn Bhd',
        'email' => 'lama@example.com',
    ]);
    $donor = Donor::create([
        'user_id' => $donorUser->id,
        'donor_type' => 'syarikat',
        'representative_name' => 'Wakil Lama',
        'phone' => '0123456789',
        'preferred_contact' => 'email',
        'logo' => 'donor-logos/old-logo.png',
        'support_document' => 'donor-documents/old-document.pdf',
        'homepage_label' => 'Legacy Label',
        'homepage_order' => 9,
        'show_on_homepage' => false,
    ]);
    $donor->address()->create([
        'address_line_1' => 'Bangunan Komuniti UKM',
        'city' => 'Bangi',
        'postcode' => '43600',
        'state' => 'Selangor',
        'country' => 'Malaysia',
    ]);
    Storage::disk('public')->put('donor-logos/old-logo.png', 'old-logo');
    Storage::disk('public')->put('donor-documents/old-document.pdf', 'old-document');

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.penderma.index'))
        ->put(route('admin.penderma.update', $donorUser), [
            '_editing_user_id' => $donorUser->id,
            'name' => 'Syarikat Baharu Sdn Bhd',
            'email' => 'baharu@example.com',
            'phone' => '01123456789',
            'representative_name' => 'Wakil Baharu',
            'preferred_contact' => 'phone',
            'address_line_1' => 'Bangunan Komuniti UKM',
            'city' => 'Bangi',
            'postcode' => '43600',
            'state' => 'Selangor',
            'country' => 'Malaysia',
            'logo' => UploadedFile::fake()->image('new-logo.jpg', 120, 120),
            'support_document' => UploadedFile::fake()->create('dokumen-sokongan.png', 120, 'image/png'),
            'homepage_order' => 3,
            'show_on_homepage' => '1',
        ]);

    $response
        ->assertRedirect(route('admin.penderma.index'))
        ->assertSessionHasNoErrors();

    $donor->refresh();
    $donorUser->refresh();

    expect($donorUser->name)->toBe('Syarikat Baharu Sdn Bhd')
        ->and($donor->representative_name)->toBe('Wakil Baharu')
        ->and($donor->homepage_order)->toBe(3)
        ->and($donor->show_on_homepage)->toBeTrue()
        ->and($donor->homepage_label)->toBe('Legacy Label');

    Storage::disk('public')->assertExists($donor->logo);
    Storage::disk('public')->assertExists($donor->support_document);
    Storage::disk('public')->assertMissing('donor-logos/old-logo.png');
    Storage::disk('public')->assertMissing('donor-documents/old-document.pdf');
    expect($donor->support_document)->toStartWith('donor-documents/');
});

test('homepage donors are limited to four and ordered by ranking', function () {
    $rankings = [
        'Kelima' => 5,
        'Pertama' => 1,
        'Ketiga' => 3,
        'Kedua' => 2,
        'Keempat' => 4,
    ];

    foreach ($rankings as $name => $ranking) {
        $user = User::factory()->create([
            'role' => 'penderma',
            'name' => $name,
            'email' => strtolower($name) . '@example.com',
        ]);

        Donor::create([
            'user_id' => $user->id,
            'donor_type' => 'syarikat',
            'phone' => '0123456789',
            'preferred_contact' => 'email',
            'homepage_order' => $ranking,
            'show_on_homepage' => true,
        ]);
    }

    $hiddenUser = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Tersembunyi',
        'email' => 'tersembunyi@example.com',
    ]);
    Donor::create([
        'user_id' => $hiddenUser->id,
        'donor_type' => 'syarikat',
        'phone' => '0123456789',
        'preferred_contact' => 'email',
        'homepage_order' => null,
        'show_on_homepage' => false,
    ]);

    $response = $this->get('/');

    $response->assertOk();

    $orderedNames = $response
        ->viewData('homepageDonors')
        ->map(fn (Donor $donor) => $donor->user->name)
        ->values()
        ->all();

    expect($orderedNames)->toBe(['Pertama', 'Kedua', 'Ketiga', 'Keempat']);
});

test('homepage ranking is required when donor is shown on homepage', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.penderma.create'))
        ->post(route('admin.penderma.store'), [
            'donor_type' => 'individu',
            'name' => 'Ahmad Penderma',
            'email' => 'ahmad-ranking@example.com',
            'phone' => '0123456789',
            'preferred_contact' => 'phone',
            'address_line_1' => 'Bangunan Komuniti UKM',
            'address_line_2' => 'Aras 2',
            'city' => 'Bangi',
            'postcode' => '43600',
            'state' => 'Selangor',
            'country' => 'Malaysia',
            'show_on_homepage' => '1',
        ]);

    $response
        ->assertRedirect(route('admin.penderma.create'))
        ->assertSessionHasErrors('homepage_order');

    $this->assertDatabaseMissing('users', [
        'email' => 'ahmad-ranking@example.com',
    ]);
});

test('homepage ranking must be unique when creating a visible donor', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);
    $existingUser = User::factory()->create([
        'role' => 'penderma',
        'email' => 'existing-ranking@example.com',
    ]);

    Donor::create([
        'user_id' => $existingUser->id,
        'donor_type' => 'syarikat',
        'phone' => '0123456789',
        'preferred_contact' => 'email',
        'homepage_order' => 1,
        'show_on_homepage' => true,
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.penderma.create'))
        ->post(route('admin.penderma.store'), [
            'donor_type' => 'individu',
            'name' => 'Penderma Duplikasi',
            'email' => 'duplicate-ranking@example.com',
            'phone' => '0123456789',
            'preferred_contact' => 'phone',
            'address_line_1' => 'Bangunan Komuniti UKM',
            'city' => 'Bangi',
            'postcode' => '43600',
            'state' => 'Selangor',
            'country' => 'Malaysia',
            'show_on_homepage' => '1',
            'homepage_order' => 1,
        ]);

    $response
        ->assertRedirect(route('admin.penderma.create'))
        ->assertSessionHasErrors([
            'homepage_order' => 'Ranking ini telah digunakan oleh penderma lain. Sila pilih ranking yang lain.',
        ]);

    $this->assertDatabaseMissing('users', [
        'email' => 'duplicate-ranking@example.com',
    ]);
});

test('homepage ranking update ignores current donor but rejects another donor ranking', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);
    $firstUser = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Penderma Pertama',
        'email' => 'first-ranking@example.com',
    ]);
    $secondUser = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Penderma Kedua',
        'email' => 'second-ranking@example.com',
    ]);
    $firstDonor = Donor::create([
        'user_id' => $firstUser->id,
        'donor_type' => 'syarikat',
        'phone' => '0123456789',
        'preferred_contact' => 'email',
        'homepage_order' => 1,
        'show_on_homepage' => true,
    ]);
    $secondDonor = Donor::create([
        'user_id' => $secondUser->id,
        'donor_type' => 'syarikat',
        'phone' => '0123456789',
        'preferred_contact' => 'email',
        'homepage_order' => 2,
        'show_on_homepage' => true,
    ]);

    foreach ([$firstDonor, $secondDonor] as $donor) {
        $donor->address()->create([
            'address_line_1' => 'Bangunan Komuniti UKM',
            'city' => 'Bangi',
            'postcode' => '43600',
            'state' => 'Selangor',
            'country' => 'Malaysia',
        ]);
    }

    $this
        ->actingAs($admin)
        ->from(route('admin.penderma.index'))
        ->put(route('admin.penderma.update', $firstUser), [
            'name' => 'Penderma Pertama',
            'email' => 'first-ranking@example.com',
            'phone' => '0123456789',
            'preferred_contact' => 'email',
            'address_line_1' => 'Bangunan Komuniti UKM',
            'city' => 'Bangi',
            'postcode' => '43600',
            'state' => 'Selangor',
            'country' => 'Malaysia',
            'show_on_homepage' => '1',
            'homepage_order' => 1,
        ])
        ->assertRedirect(route('admin.penderma.index'))
        ->assertSessionHasNoErrors();

    $this
        ->actingAs($admin)
        ->from(route('admin.penderma.index'))
        ->put(route('admin.penderma.update', $secondUser), [
            'name' => 'Penderma Kedua',
            'email' => 'second-ranking@example.com',
            'phone' => '0123456789',
            'preferred_contact' => 'email',
            'address_line_1' => 'Bangunan Komuniti UKM',
            'city' => 'Bangi',
            'postcode' => '43600',
            'state' => 'Selangor',
            'country' => 'Malaysia',
            'show_on_homepage' => '1',
            'homepage_order' => 1,
        ])
        ->assertRedirect(route('admin.penderma.index'))
        ->assertSessionHasErrors([
            'homepage_order' => 'Ranking ini telah digunakan oleh penderma lain. Sila pilih ranking yang lain.',
        ]);

    expect($firstDonor->fresh()->homepage_order)->toBe(1)
        ->and($secondDonor->fresh()->homepage_order)->toBe(2);
});

test('homepage ranking is cleared when donor is hidden from homepage', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);
    $donorUser = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Penderma Homepage',
        'email' => 'homepage-hidden@example.com',
    ]);
    $donor = Donor::create([
        'user_id' => $donorUser->id,
        'donor_type' => 'syarikat',
        'phone' => '0123456789',
        'preferred_contact' => 'email',
        'homepage_order' => 4,
        'show_on_homepage' => true,
    ]);
    $donor->address()->create([
        'address_line_1' => 'Bangunan Komuniti UKM',
        'city' => 'Bangi',
        'postcode' => '43600',
        'state' => 'Selangor',
        'country' => 'Malaysia',
    ]);

    $this
        ->actingAs($admin)
        ->from(route('admin.penderma.index'))
        ->put(route('admin.penderma.update', $donorUser), [
            'name' => 'Penderma Homepage',
            'email' => 'homepage-hidden@example.com',
            'phone' => '0123456789',
            'preferred_contact' => 'email',
            'address_line_1' => 'Bangunan Komuniti UKM',
            'city' => 'Bangi',
            'postcode' => '43600',
            'state' => 'Selangor',
            'country' => 'Malaysia',
            'show_on_homepage' => '0',
            'homepage_order' => 4,
        ])
        ->assertRedirect(route('admin.penderma.index'))
        ->assertSessionHasNoErrors();

    $donor->refresh();

    expect($donor->homepage_order)->toBeNull()
        ->and($donor->show_on_homepage)->toBeFalse();
});

test('support document must be pdf jpg jpeg or png', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.penderma.create'))
        ->post(route('admin.penderma.store'), [
            'donor_type' => 'individu',
            'name' => 'Ahmad Penderma',
            'email' => 'ahmad-doc@example.com',
            'phone' => '0123456789',
            'preferred_contact' => 'phone',
            'address_line_1' => 'Bangunan Komuniti UKM',
            'address_line_2' => 'Aras 2',
            'city' => 'Bangi',
            'postcode' => '43600',
            'state' => 'Selangor',
            'country' => 'Malaysia',
            'support_document' => UploadedFile::fake()->create('dokumen.txt', 10, 'text/plain'),
            'homepage_order' => 1,
        ]);

    $response
        ->assertRedirect(route('admin.penderma.create'))
        ->assertSessionHasErrors('support_document');

    $this->assertDatabaseMissing('users', [
        'email' => 'ahmad-doc@example.com',
    ]);
});
