<?php

use App\Models\Item;
use App\Models\Permohonan;
use App\Models\Sumbangan;
use App\Models\User;

function createCompletedDonationWithAgihan(array $overrides = []): array
{
    $donor = User::factory()->create(['role' => 'penderma']);
    $student = User::factory()->create(['role' => 'pelajar']);
    $paidAt = $overrides['paid_at'] ?? now();
    $donationCategory = $overrides['donation_category'] ?? Item::CATEGORY_KEPERLUAN_ASAS;
    $applicationCategory = $overrides['application_category'] ?? Permohonan::KATEGORI_KEPERLUAN_ASAS;

    $item = Item::create([
        'nama_item' => $overrides['item_name'] ?? 'Beras',
        'kategori' => $overrides['legacy_category'] ?? 'keperluan',
        'kategori_bantuan' => $donationCategory,
        'harga' => 10,
        'stok_diperlukan' => 10,
        'stok_disumbang' => 1,
        'status' => 'aktif',
        'is_active' => true,
        'susunan' => 1,
    ]);

    $sumbangan = Sumbangan::create([
        'user_id' => $donor->id,
        'no_sumbangan' => 'SMB/TEST/' . fake()->unique()->numerify('######'),
        'jumlah_unit' => 1,
        'jumlah_keseluruhan' => 10,
        'status' => Sumbangan::STATUS_SELESAI,
        'paid_at' => $paidAt,
    ]);

    $sumbangan->items()->create([
        'item_id' => $item->id,
        'nama_item' => $item->nama_item,
        'kategori_bantuan' => $donationCategory,
        'harga_unit' => 10,
        'kuantiti' => 1,
        'jumlah' => 10,
    ]);

    $permohonan = Permohonan::create([
        'user_id' => $student->id,
        'no_kelompok' => 'PMH/TEST/' . fake()->unique()->numerify('######'),
        'tarikh_mohon' => now()->subDays(5)->toDateString(),
        'jenis_bantuan' => 'bantuan_asas_hidup',
        'status' => Permohonan::STATUS_DILULUSKAN,
        'status_agihan' => Permohonan::STATUS_AGIHAN_SELESAI,
        'tarikh_agihan' => $overrides['tarikh_agihan'] ?? now()->addMinute(),
        'bukti_agihan' => 'agihan-bukti/not-directly-linked.pdf',
    ]);

    $permohonan->pelajar()->create([
        'nama_penuh' => 'Aina Binti Ali',
        'no_matrik' => 'A123456',
        'email_ukm' => 'a123456@siswa.ukm.edu.my',
        'no_telefon' => '0123456789',
        'fakulti' => 'Fakulti Teknologi dan Sains Maklumat',
        'tahun_pengajian' => 'Tahun 2',
    ]);

    $permohonan->bantuan()->create([
        'jenis_bantuan' => 'bantuan_asas_hidup',
        'kategori_bantuan' => $applicationCategory,
    ]);

    return [$donor, $sumbangan, $permohonan];
}

test('paid donation history does not show recipient details for inferred category agihan', function () {
    [$donor, $sumbangan, $permohonan] = createCompletedDonationWithAgihan([
        'paid_at' => now()->subHour(),
        'tarikh_agihan' => now(),
    ]);

    $proofUrl = route('penderma.agihan-bukti', ['sumbangan' => $sumbangan, 'permohonan' => $permohonan]);

    $this
        ->actingAs($donor)
        ->get(route('penderma.sejarah-sumbangan.show', ['id' => $sumbangan->id]))
        ->assertOk()
        ->assertSee('Status agihan kategori')
        ->assertSee('pautan langsung antara resit sumbangan ini dengan rekod agihan pelajar')
        ->assertDontSee('Aina Binti Ali')
        ->assertDontSee('A123456')
        ->assertDontSee('Telah Disalurkan')
        ->assertDontSee('Lihat Bukti Agihan')
        ->assertDontSee($proofUrl, false);

    $this
        ->actingAs($donor)
        ->get($proofUrl)
        ->assertNotFound();

    $this
        ->actingAs($donor)
        ->get(route('dashboard.penderma'))
        ->assertOk()
        ->assertDontSee($proofUrl, false)
        ->assertDontSee('Bukti Agihan');
});

test('old donation receipts do not inherit later same-category recipient proof', function () {
    [$donor, $sumbangan, $permohonan] = createCompletedDonationWithAgihan([
        'paid_at' => now()->subMonth(),
        'tarikh_agihan' => now(),
        'donation_category' => Item::CATEGORY_SUKAN,
        'application_category' => Permohonan::KATEGORI_SUKAN,
        'item_name' => 'Bola Futsal',
        'legacy_category' => 'sukan',
    ]);

    $proofUrl = route('penderma.agihan-bukti', ['sumbangan' => $sumbangan, 'permohonan' => $permohonan]);

    $this
        ->actingAs($donor)
        ->get(route('penderma.sejarah-sumbangan.show', ['id' => $sumbangan->id]))
        ->assertOk()
        ->assertSee('Status agihan kategori')
        ->assertSee('Sukan')
        ->assertDontSee('Telah Disalurkan')
        ->assertDontSee('Lihat Bukti Agihan')
        ->assertDontSee($proofUrl, false);

    $this
        ->actingAs($donor)
        ->get($proofUrl)
        ->assertNotFound();

    $this
        ->actingAs($donor)
        ->get(route('dashboard.penderma'))
        ->assertOk()
        ->assertDontSee($proofUrl, false)
        ->assertDontSee('Bukti Agihan');
});
