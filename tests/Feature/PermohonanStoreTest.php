<?php

use App\Models\Permohonan;
use App\Models\PermohonanDokumen;
use App\Models\Item;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

test('pelajar can submit a complete permohonan', function () {
    fakePrivateDocumentDisk();

    $user = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('permohonan.index'))
        ->post(route('permohonan.store'), [
            'nama_penuh' => 'Pelajar Ujian',
            'no_matrik' => 'A123456',
            'email_ukm' => 'a123456@siswa.ukm.edu.my',
            'no_telefon' => '0123456789',
            'fakulti' => 'Fakulti Teknologi dan Sains Maklumat',
            'tahun_pengajian' => 'Tahun 2',
            'penjaga_nama' => 'Penjaga Ujian',
            'penjaga_no_kp' => '800101011234',
            'penjaga_hubungan' => 'Ibu',
            'penjaga_telefon' => '0198765432',
            'penjaga_pekerjaan' => 'Peniaga',
            'penjaga_pendapatan' => 1500,
            'jenis_bantuan' => 'bantuan_asas_hidup',
            'kategori_bantuan' => 'keperluan_asas',
            'bantuan_data' => basicAidData(),
            'dokumen_wajib' => [
                'dokumen_1' => UploadedFile::fake()->create('pendapatan.pdf', 100, 'application/pdf'),
                'dokumen_2' => UploadedFile::fake()->create('alamat.pdf', 100, 'application/pdf'),
            ],
        ]);

    $response
        ->assertRedirect(route('permohonan.index'))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success')
        ->assertSessionHas('sweet_alert_redirect_url', route('status-permohonan.index'));

    $this->assertDatabaseHas('permohonans', [
        'user_id' => $user->id,
        'jenis_bantuan' => 'bantuan_asas_hidup',
        'status' => 'Sedang Disemak',
    ]);

    $this->assertDatabaseHas('permohonan_pelajar', [
        'nama_penuh' => 'Pelajar Ujian',
        'no_matrik' => 'A123456',
        'fakulti' => 'Fakulti Teknologi dan Sains Maklumat',
        'tahun_pengajian' => 'Tahun 2',
    ]);

    $this->assertDatabaseHas('permohonan_keluarga', [
        'jenis' => 'penjaga',
        'nama' => 'Penjaga Ujian',
    ]);

    $this->assertDatabaseHas('permohonan_bantuans', [
        'jenis_bantuan' => 'bantuan_asas_hidup',
        'kategori_bantuan' => 'keperluan_asas',
    ]);

    $this->assertDatabaseCount('permohonan_dokumens', 2);

    $permohonan = Permohonan::query()->where('user_id', $user->id)->firstOrFail();
    $expectedDirectory = "dokumen_permohonan/user_{$user->id}/permohonan_{$permohonan->id}";

    $this->assertDatabaseHas('permohonan_dokumens', [
        'permohonan_id' => $permohonan->id,
        'jenis_dokumen' => 'Dokumen 1',
        'file_path' => "{$expectedDirectory}/dokumen_1.pdf",
    ]);

    $this->assertDatabaseHas('permohonan_dokumens', [
        'permohonan_id' => $permohonan->id,
        'jenis_dokumen' => 'Dokumen 2',
        'file_path' => "{$expectedDirectory}/dokumen_2.pdf",
    ]);

    Storage::disk('local')->assertExists("{$expectedDirectory}/dokumen_1.pdf");
    Storage::disk('local')->assertExists("{$expectedDirectory}/dokumen_2.pdf");

    $dokumen1 = PermohonanDokumen::query()
        ->where('permohonan_id', $permohonan->id)
        ->where('jenis_dokumen', 'Dokumen 1')
        ->firstOrFail();

    $viewResponse = $this->actingAs($user)->get(route('permohonan.dokumen.show', $dokumen1));
    $viewResponse->assertOk();
    $this->assertStringContainsString('inline; filename="dokumen_1.pdf"', $viewResponse->headers->get('Content-Disposition'));

    $downloadResponse = $this->actingAs($user)->get(route('permohonan.dokumen.show', [
        'dokumen' => $dokumen1,
        'download' => 1,
    ]));
    $downloadResponse->assertOk();
    $this->assertStringContainsString('dokumen_1.pdf', $downloadResponse->headers->get('Content-Disposition'));
});

test('uploaded jpg application documents use clean document filenames', function () {
    fakePrivateDocumentDisk();

    $user = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('permohonan.index'))
        ->post(route('permohonan.store'), stepThreePermohonanPayload([
            'jenis_bantuan' => 'bantuan_asas_hidup',
            'kategori_bantuan' => 'keperluan_asas',
            'bantuan_data' => basicAidData(),
            'dokumen_wajib' => [
                'dokumen_1' => UploadedFile::fake()->create('gambar-pendapatan.jpg', 100, 'image/jpeg'),
                'dokumen_2' => UploadedFile::fake()->create('gambar-alamat.jpg', 100, 'image/jpeg'),
            ],
        ]));

    $response
        ->assertRedirect(route('permohonan.index'))
        ->assertSessionHasNoErrors();

    $permohonan = Permohonan::query()->where('user_id', $user->id)->firstOrFail();
    $expectedDirectory = "dokumen_permohonan/user_{$user->id}/permohonan_{$permohonan->id}";

    Storage::disk('local')->assertExists("{$expectedDirectory}/dokumen_1.jpg");
    Storage::disk('local')->assertExists("{$expectedDirectory}/dokumen_2.jpg");

    $this->assertDatabaseHas('permohonan_dokumens', [
        'permohonan_id' => $permohonan->id,
        'file_path' => "{$expectedDirectory}/dokumen_1.jpg",
    ]);

    $this->assertDatabaseHas('permohonan_dokumens', [
        'permohonan_id' => $permohonan->id,
        'file_path' => "{$expectedDirectory}/dokumen_2.jpg",
    ]);
});

test('tanggungan fields are saved from explicit form keys without using legacy phone job columns', function () {
    fakePrivateDocumentDisk();

    $user = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('permohonan.index'))
        ->post(route('permohonan.store'), stepThreePermohonanPayload([
            'jenis_bantuan' => 'bantuan_asas_hidup',
            'kategori_bantuan' => 'keperluan_asas',
            'tanggungan' => [
                [
                    'nama' => 'Nur Aisyah',
                    'hubungan' => 'Adik',
                    'umur' => 14,
                    'status' => 'Tanggungan (7–17 Tahun)',
                    'kesihatan' => 'SAKIT KRONIK (Asma)',
                    'pendapatan' => 0,
                    'telefon' => '0182102282',
                    'pekerjaan' => 'Data lama tidak patut disimpan',
                ],
                [
                    'nama' => 'Muhammad Adam',
                    'hubungan' => 'Adik',
                    'umur' => 9,
                    'status' => 'Tanggungan (7–17 Tahun)',
                    'kesihatan' => 'SIHAT',
                    'pendapatan' => 0,
                ],
            ],
            'bantuan_data' => basicAidData(),
        ]));

    $response
        ->assertRedirect(route('permohonan.index'))
        ->assertSessionHasNoErrors();

    $permohonan = Permohonan::query()->where('user_id', $user->id)->firstOrFail();
    $this->assertDatabaseHas('permohonan_keluarga', [
        'permohonan_id' => $permohonan->id,
        'jenis' => 'penjaga',
        'nama' => 'Penjaga Ujian',
        'telefon' => '0198765432',
        'pekerjaan' => 'Peniaga',
    ]);

    $this->assertSame(3, DB::table('permohonan_keluarga')
        ->where('permohonan_id', $permohonan->id)
        ->count());

    $tanggungan = DB::table('permohonan_keluarga')
        ->where('permohonan_id', $permohonan->id)
        ->where('jenis', 'tanggungan')
        ->where('nama', 'Nur Aisyah')
        ->first();

    expect($tanggungan)->not->toBeNull();
    expect($tanggungan->nama)->toBe('Nur Aisyah');
    expect($tanggungan->hubungan)->toBe('Adik');
    expect((int) $tanggungan->umur)->toBe(14);
    expect($tanggungan->status)->toBe('Tanggungan (7–17 Tahun)');
    expect($tanggungan->kesihatan)->toBe('SAKIT KRONIK (Asma)');
    expect((float) $tanggungan->pendapatan)->toBe(0.0);
    expect($tanggungan->telefon)->toBeNull();
    expect($tanggungan->pekerjaan)->toBeNull();

    $this->assertDatabaseHas('permohonan_keluarga', [
        'permohonan_id' => $permohonan->id,
        'jenis' => 'tanggungan',
        'nama' => 'Muhammad Adam',
        'hubungan' => 'Adik',
        'umur' => 9,
        'status' => 'Tanggungan (7–17 Tahun)',
        'kesihatan' => 'SIHAT',
        'pendapatan' => 0,
    ]);
});

test('validation errors are displayed without saving partial permohonan data', function () {
    $user = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('permohonan.index'))
        ->post(route('permohonan.store'), []);

    $response
        ->assertRedirect(route('permohonan.index'))
        ->assertSessionHasErrors('nama_penuh');

    $this->get(route('permohonan.index'))
        ->assertOk()
        ->assertSee('Permohonan belum dapat dihantar. Sila semak maklumat berikut:');

    $this->assertDatabaseCount('permohonans', 0);
    $this->assertDatabaseCount('permohonan_pelajar', 0);
    $this->assertDatabaseCount('permohonan_keluarga', 0);
    $this->assertDatabaseCount('permohonan_bantuans', 0);
    $this->assertDatabaseCount('permohonan_dokumens', 0);
});

test('application step one uses profile faculty and year of study select', function () {
    $user = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
        'fakulti' => 'Fakulti Undang-Undang',
    ]);

    $this->actingAs($user)
        ->get(route('permohonan.index'))
        ->assertOk()
        ->assertSee('Fakulti Undang-Undang')
        ->assertSee('Tahun Pengajian')
        ->assertSee('-- Pilih Tahun --')
        ->assertSee('Tahun 1')
        ->assertSee('Tahun 2')
        ->assertSee('Tahun 3')
        ->assertSee('Tahun 4')
        ->assertDontSee('-- Pilih Fakulti --')
        ->assertDontSee('Sesi Akademik')
        ->assertDontSee('2025/2026');
});

test('application submission requires faculty in student profile', function () {
    fakePrivateDocumentDisk();

    $user = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
        'fakulti' => null,
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('permohonan.index'))
        ->post(route('permohonan.store'), stepThreePermohonanPayload([
            'jenis_bantuan' => 'bantuan_asas_hidup',
            'kategori_bantuan' => 'keperluan_asas',
            'bantuan_data' => basicAidData(),
        ]));

    $response
        ->assertRedirect(route('permohonan.index'))
        ->assertSessionHasErrors([
            'fakulti' => 'Sila kemas kini fakulti dalam profil pelajar sebelum menghantar permohonan.',
        ]);

    $this->assertDatabaseCount('permohonans', 0);
});

test('application saves faculty from profile instead of submitted value', function () {
    fakePrivateDocumentDisk();

    $user = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
        'fakulti' => 'Fakulti Undang-Undang',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('permohonan.index'))
        ->post(route('permohonan.store'), stepThreePermohonanPayload([
            'fakulti' => 'Fakulti Teknologi dan Sains Maklumat',
            'tahun_pengajian' => 'Tahun 2',
            'jenis_bantuan' => 'bantuan_asas_hidup',
            'kategori_bantuan' => 'keperluan_asas',
            'bantuan_data' => basicAidData(),
        ]));

    $response
        ->assertRedirect(route('permohonan.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('permohonan_pelajar', [
        'no_matrik' => 'A123456',
        'fakulti' => 'Fakulti Undang-Undang',
        'tahun_pengajian' => 'Tahun 2',
    ]);
});

test('pelajar cannot submit permohonan identity that differs from authenticated account', function () {
    $user = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('permohonan.index'))
        ->post(route('permohonan.store'), stepThreePermohonanPayload([
            'no_matrik' => 'A654321',
            'email_ukm' => 'a654321@siswa.ukm.edu.my',
            'jenis_bantuan' => 'bantuan_asas_hidup',
            'kategori_bantuan' => 'keperluan_asas',
            'bantuan_data' => basicAidData(),
        ]));

    $response
        ->assertRedirect(route('permohonan.index'))
        ->assertSessionHasErrors(['no_matrik', 'email_ukm']);

    $this->assertDatabaseCount('permohonans', 0);
});

test('learning club application does not require or save wakil fields', function () {
    fakePrivateDocumentDisk();

    $user = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('permohonan.index'))
        ->post(route('permohonan.store'), stepThreePermohonanPayload([
            'jenis_bantuan' => 'bantuan_pembelajaran',
            'kategori_bantuan' => 'alat_tulis_pembelajaran',
            'bantuan_data' => [
                'learning_type' => 'group',
                'group' => [
                    'nama_group' => 'Kelab Teknologi',
                    'bil_ahli' => 12,
                    'nama_wakil' => 'Nama Lama',
                    'no_matrik_wakil' => 'A999999',
                    'items' => [
                        [
                            'selected' => 'Buku Nota',
                            'qty' => 12,
                        ],
                    ],
                ],
            ],
        ]));

    $response
        ->assertRedirect(route('permohonan.index'))
        ->assertSessionHasNoErrors();

    $data = \App\Models\PermohonanBantuan::query()->firstOrFail()->data;

    $this->assertSame('Kelab Teknologi', $data['group']['nama_group']);
    $this->assertSame(12, $data['group']['bil_ahli']);
    $this->assertArrayNotHasKey('nama_wakil', $data['group']);
    $this->assertArrayNotHasKey('no_matrik_wakil', $data['group']);
});

test('selected bantuan branches with justifikasi ringkas require backend justification', function (array $stepThree, string $errorKey) {
    $user = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('permohonan.index'))
        ->post(route('permohonan.store'), stepThreePermohonanPayload($stepThree));

    $response
        ->assertRedirect(route('permohonan.index'))
        ->assertSessionHasErrors([
            $errorKey => 'Sila isi Justifikasi Ringkas sebelum meneruskan permohonan.',
        ]);

    $this->assertDatabaseCount('permohonans', 0);
})->with([
    'alat tulis pembelajaran individu' => [
        [
            'jenis_bantuan' => 'bantuan_pembelajaran',
            'kategori_bantuan' => 'alat_tulis_pembelajaran',
            'bantuan_data' => [
                'learning_type' => 'individu',
                'individu' => [
                    'items' => [
                        [
                            'selected' => 'Buku Nota',
                            'qty' => 1,
                        ],
                    ],
                    'justifikasi' => '',
                ],
            ],
        ],
        'bantuan_data.individu.justifikasi',
    ],
    'peralatan pembelajaran' => [
        [
            'jenis_bantuan' => 'bantuan_pembelajaran',
            'kategori_bantuan' => 'peralatan_pembelajaran',
            'bantuan_data' => [
                'peralatan' => 'Dell Latitude 5440 Laptop',
                'sebab' => 'Rosak',
                'justifikasi' => '',
            ],
        ],
        'bantuan_data.justifikasi',
    ],
    'sukan' => [
        [
            'jenis_bantuan' => 'bantuan_sukan',
            'kategori_bantuan' => 'sukan',
            'bantuan_data' => [
                'peringkat' => 'universiti',
                'nama_kelab_pasukan' => 'Pasukan Badminton UKM',
                'bilangan_peserta' => 8,
                'items' => [
                    [
                        'selected' => 'Raket Badminton',
                        'qty' => 4,
                    ],
                ],
                'justifikasi' => '',
            ],
        ],
        'bantuan_data.justifikasi',
    ],
]);

test('sports application saves activity level and participant count without wakil fields', function () {
    fakePrivateDocumentDisk();

    $user = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('permohonan.index'))
        ->post(route('permohonan.store'), stepThreePermohonanPayload([
            'jenis_bantuan' => 'bantuan_sukan',
            'kategori_bantuan' => 'sukan',
            'bantuan_data' => [
                'peringkat' => 'universiti',
                'nama_kelab_pasukan' => 'Pasukan Badminton UKM',
                'bilangan_peserta' => 8,
                'nama_wakil' => 'Nama Lama',
                'jawatan' => 'Ketua Lama',
                'tarikh' => '2026-07-20',
                'items' => [
                    [
                        'selected' => 'Raket Badminton',
                        'qty' => 4,
                    ],
                ],
                'justifikasi' => 'Peralatan diperlukan untuk pertandingan universiti.',
            ],
        ]));

    $response
        ->assertRedirect(route('permohonan.index'))
        ->assertSessionHasNoErrors();

    $bantuan = \App\Models\PermohonanBantuan::query()->firstOrFail();

    $this->assertSame('sukan', $bantuan->kategori_bantuan);
    $this->assertSame('universiti', $bantuan->data['peringkat']);
    $this->assertSame('Pasukan Badminton UKM', $bantuan->data['nama_kelab_pasukan']);
    $this->assertSame(8, $bantuan->data['bilangan_peserta']);
    $this->assertArrayNotHasKey('nama_wakil', $bantuan->data);
    $this->assertArrayNotHasKey('jawatan', $bantuan->data);
    $this->assertArrayNotHasKey('tarikh', $bantuan->data);
});

test('pakaian sukan category is rejected', function () {
    $user = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('permohonan.index'))
        ->post(route('permohonan.store'), stepThreePermohonanPayload([
            'jenis_bantuan' => 'bantuan_sukan',
            'kategori_bantuan' => 'pakaian_sukan',
        ]));

    $response
        ->assertRedirect(route('permohonan.index'))
        ->assertSessionHasErrors('kategori_bantuan');

    $this->assertDatabaseCount('permohonans', 0);
});

test('step three renders the revised learning and sports content only', function () {
    $user = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
    ]);

    $this->actingAs($user)
        ->get(route('permohonan.index'))
        ->assertOk()
        ->assertSee('Kelab / Persatuan / Kelas')
        ->assertSee('Peringkat Aktiviti / Pertandingan')
        ->assertSee('Nama Kelab / Pasukan')
        ->assertSee('Bilangan Peserta')
        ->assertSee('Senarai Item / Barang')
        ->assertSee('Fakulti')
        ->assertSee('Universiti')
        ->assertSee('Kebangsaan')
        ->assertSee('Antarabangsa')
        ->assertDontSee('Group / Persatuan / Kelas')
        ->assertDontSee('Nama Wakil')
        ->assertDontSee('No Matrik Wakil')
        ->assertDontSee('Jawatan / Peranan')
        ->assertDontSee('Nama Organisasi / Program')
        ->assertDontSee('Tarikh Aktiviti / Pertandingan')
        ->assertDontSee('Pakaian Sukan');
});

test('status detail requires authenticated student ownership', function () {
    $owner = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
    ]);
    $otherStudent = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A654321',
        'email' => 'a654321@siswa.ukm.edu.my',
    ]);
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);
    $permohonan = testPermohonanFor($owner);

    $this->get(route('status-permohonan.show', $permohonan))
        ->assertRedirect(route('login'));

    $this->actingAs($otherStudent)
        ->get(route('status-permohonan.show', $permohonan))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('status-permohonan.show', $permohonan))
        ->assertForbidden();

    $this->actingAs($owner)
        ->get(route('status-permohonan.show', $permohonan))
        ->assertOk();
});

test('status detail displays submitted bantuan data from json payload', function () {
    $owner = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
    ]);
    $permohonan = testPermohonanFor($owner);

    \App\Models\PermohonanBantuan::create([
        'permohonan_id' => $permohonan->id,
        'jenis_bantuan' => 'bantuan_asas_hidup',
        'kategori_bantuan' => 'keperluan_asas',
        'data' => [
            'pakej' => 1,
            'jumlah_ahli' => 0,
            'alamat_rumah' => 'Kolej Pendeta Zaaba, UKM',
            'bandar' => 'Bangi',
            'poskod' => '43600',
            'negeri' => 'Selangor',
            'jenis_kediaman' => 'Kolej Kediaman',
        ],
    ]);

    $this->actingAs($owner)
        ->get(route('status-permohonan.show', $permohonan))
        ->assertOk()
        ->assertSee('Keperluan Asas')
        ->assertSee('Kolej Pendeta Zaaba, UKM')
        ->assertSee('Kolej Kediaman');
});

test('application documents require owner student or admin access', function () {
    fakePrivateDocumentDisk();

    $owner = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
    ]);
    $otherStudent = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A654321',
        'email' => 'a654321@siswa.ukm.edu.my',
    ]);
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);
    $permohonan = testPermohonanFor($owner);

    Storage::disk('local')->put('dokumen_permohonan/test.pdf', 'test-content');

    $dokumen = PermohonanDokumen::create([
        'permohonan_id' => $permohonan->id,
        'jenis_dokumen' => 'Dokumen 1',
        'file_path' => 'dokumen_permohonan/test.pdf',
    ]);

    $this->get(route('permohonan.dokumen.show', $dokumen))
        ->assertRedirect(route('login'));

    $this->actingAs($otherStudent)
        ->get(route('permohonan.dokumen.show', $dokumen))
        ->assertForbidden();

    $this->actingAs($owner)
        ->get(route('permohonan.dokumen.show', $dokumen))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('permohonan.dokumen.show', $dokumen))
        ->assertOk();
});

test('existing public disk application documents can still be served through authorized route', function () {
    fakePrivateDocumentDisk();

    $owner = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
    ]);
    $permohonan = testPermohonanFor($owner);

    Storage::disk('public')->put('dokumen_permohonan/legacy.pdf', 'legacy-content');

    $dokumen = PermohonanDokumen::create([
        'permohonan_id' => $permohonan->id,
        'jenis_dokumen' => 'Dokumen Legacy',
        'file_path' => 'dokumen_permohonan/legacy.pdf',
    ]);

    $this->actingAs($owner)
        ->get(route('permohonan.dokumen.show', $dokumen))
        ->assertOk();
});

function stepThreePermohonanPayload(array $stepThree): array
{
    return array_merge([
        'nama_penuh' => 'Pelajar Ujian',
        'no_matrik' => 'A123456',
        'email_ukm' => 'a123456@siswa.ukm.edu.my',
        'no_telefon' => '0123456789',
        'fakulti' => 'Fakulti Teknologi dan Sains Maklumat',
        'tahun_pengajian' => 'Tahun 2',
        'penjaga_nama' => 'Penjaga Ujian',
        'penjaga_no_kp' => '800101011234',
        'penjaga_hubungan' => 'Ibu',
        'penjaga_telefon' => '0198765432',
        'penjaga_pekerjaan' => 'Peniaga',
        'penjaga_pendapatan' => 1500,
        'dokumen_wajib' => [
            'dokumen_1' => UploadedFile::fake()->create('dokumen-1.pdf', 100, 'application/pdf'),
            'dokumen_2' => UploadedFile::fake()->create('dokumen-2.pdf', 100, 'application/pdf'),
        ],
    ], $stepThree);
}

function basicAidData(array $overrides = []): array
{
    $package = basicPackageItem();

    return array_merge([
        'pakej_item_id' => $package->id,
        'pakej' => $package->nama_item,
        'jumlah_ahli' => 0,
        'nama_ketua' => 'Pelajar Ujian',
        'no_matrik_ketua' => 'A123456',
        'alamat_rumah' => 'Kolej Pendeta Zaaba, UKM',
        'bandar' => 'Bangi',
        'poskod' => '43600',
        'negeri' => 'Selangor',
        'jenis_kediaman' => 'Kolej Kediaman',
    ], $overrides);
}

function basicPackageItem(): Item
{
    return Item::create([
        'nama_item' => 'Pakej 1 Orang',
        'kategori' => 'keperluan',
        'kategori_bantuan' => Item::CATEGORY_KEPERLUAN_ASAS,
        'harga' => 50,
        'stok_diperlukan' => 10,
        'stok_disumbang' => 0,
        'status' => 'aktif',
        'is_active' => true,
        'susunan' => 1,
    ]);
}

function fakePrivateDocumentDisk(): void
{
    $root = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
        .DIRECTORY_SEPARATOR
        .'ebantuansiswa-testing-documents';

    if (! is_dir($root)) {
        mkdir($root, 0775, true);
    }

    config([
        'filesystems.disks.local.root' => $root,
        'filesystems.disks.public.root' => $root,
    ]);

    Storage::forgetDisk('local');
    Storage::forgetDisk('public');
}

function testPermohonanFor(User $user): Permohonan
{
    return Permohonan::create([
        'user_id' => $user->id,
        'no_kelompok' => 'TEST-'.uniqid(),
        'tarikh_mohon' => now(),
        'jenis_bantuan' => 'bantuan_asas_hidup',
        'status' => 'Sedang Disemak',
        'catatan' => 'Permohonan ujian.',
    ]);
}
