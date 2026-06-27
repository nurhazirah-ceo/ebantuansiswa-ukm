<?php

use App\Models\AppSetting;
use App\Models\CashDonation;
use App\Models\Sumbangan;
use App\Models\User;
use App\Support\DonorRecognition;

test('donor recognition tiers preserve threshold boundaries', function (float $amount, string $expectedTier) {
    expect(DonorRecognition::tierForAmount($amount))->toBe($expectedTier);
})->with([
    'RM99' => [99.00, DonorRecognition::TIER_PENYUMBANG_PRIHATIN],
    'RM100' => [100.00, DonorRecognition::TIER_RAKAN_SOKONGAN],
    'RM499' => [499.00, DonorRecognition::TIER_RAKAN_SOKONGAN],
    'RM500' => [500.00, DonorRecognition::TIER_PENAJA_HARAPAN],
    'RM1999' => [1999.00, DonorRecognition::TIER_PENAJA_HARAPAN],
    'RM2000' => [2000.00, DonorRecognition::TIER_PENAJA_UTAMA],
]);

test('donor dashboard keeps dana terkumpul global but recognition individual', function () {
    $donor = User::factory()->create([
        'name' => 'Penderma A',
        'role' => 'penderma',
    ]);
    $otherDonor = User::factory()->create([
        'name' => 'Penderma B',
        'role' => 'penderma',
    ]);

    AppSetting::put('tabung_target', 1000);

    CashDonation::create([
        'user_id' => $donor->id,
        'amount' => 250,
        'payment_status' => CashDonation::STATUS_SUCCESS,
        'paid_at' => now(),
    ]);
    CashDonation::create([
        'user_id' => $otherDonor->id,
        'amount' => 50,
        'payment_status' => CashDonation::STATUS_SUCCESS,
        'paid_at' => now(),
    ]);
    CashDonation::create([
        'user_id' => $otherDonor->id,
        'amount' => 900,
        'payment_status' => CashDonation::STATUS_PENDING,
    ]);
    Sumbangan::create([
        'user_id' => $otherDonor->id,
        'jumlah_unit' => 1,
        'jumlah_keseluruhan' => 10000,
        'status' => Sumbangan::STATUS_SELESAI,
        'paid_at' => now(),
    ]);

    $this
        ->actingAs($donor)
        ->get(route('dashboard.penderma'))
        ->assertOk()
        ->assertSee('RM300.00')
        ->assertSee('30%')
        ->assertSeeInOrder([
            'Tahap Semasa',
            DonorRecognition::TIER_RAKAN_SOKONGAN,
            'Jumlah sumbangan selesai: RM250.00',
        ]);
});

test('certificate view renders actual shared recognition tier text', function () {
    $tier = DonorRecognition::tierForAmount(500);

    $html = view('penderma.sijil-penghargaan.certificate', [
        'donorName' => 'Penderma Ujian',
        'recognitionTier' => $tier,
        'certificateTemplate' => DonorRecognition::certificateTemplateForTier($tier),
        'totalDonationAmount' => 500,
        'generatedDate' => '25/06/2026',
    ])->render();

    expect($html)
        ->toContain(DonorRecognition::TIER_PENAJA_HARAPAN)
        ->toContain('recognition-tier');
});
