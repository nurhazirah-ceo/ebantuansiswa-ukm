<?php

use App\Models\AppSetting;
use App\Models\CashDonation;
use App\Models\User;
use Illuminate\Support\Carbon;
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

afterEach(function () {
    Carbon::setTestNow();
});

test('creating a cash donation creates a pending ToyyibPay bill and redirects to payment page', function () {
    Http::fake([
        'https://dev.toyyibpay.com/index.php/api/createBill' => Http::response([
            ['BillCode' => 'BILL-CASH-001'],
        ]),
    ]);

    $donor = User::factory()->create([
        'role' => 'penderma',
    ]);

    $response = $this
        ->actingAs($donor)
        ->post(route('penderma.tabung.store'), [
            'amount_choice' => 'custom',
            'custom_amount' => '125.50',
            'message' => 'Untuk tabung bantuan',
        ]);

    $response->assertRedirect('https://dev.toyyibpay.com/BILL-CASH-001');

    $cashDonation = CashDonation::query()->firstOrFail();

    expect($cashDonation->user_id)->toBe($donor->id)
        ->and((float) $cashDonation->amount)->toBe(125.50)
        ->and($cashDonation->message)->toBe('Untuk tabung bantuan')
        ->and($cashDonation->payment_status)->toBe(CashDonation::STATUS_PENDING)
        ->and($cashDonation->paid_at)->toBeNull()
        ->and($cashDonation->resolved_at)->toBeNull()
        ->and($cashDonation->reference_no)->toBe(sprintf('TAB/%s/%06d', $cashDonation->created_at->format('Ymd'), $cashDonation->id))
        ->and($cashDonation->transaction_id)->toBeNull()
        ->and($cashDonation->bill_code)->toBe('BILL-CASH-001')
        ->and(data_get($cashDonation->raw_response, 'payment_method'))->toBe('toyyibpay')
        ->and(data_get($cashDonation->raw_response, 'toyyibpay.response.0.BillCode'))->toBe('BILL-CASH-001');

    Http::assertSent(fn ($request) => $request->url() === 'https://dev.toyyibpay.com/index.php/api/createBill'
        && $request['billAmount'] === 12550
        && $request['billPhone'] === '0100000000'
        && $request['billExternalReferenceNo'] === $cashDonation->reference_no
        && $request['billReturnUrl'] === route('penderma.tabung.return')
        && $request['billCallbackUrl'] === route('penderma.tabung.callback'));
});

test('cash donation ToyyibPay bill uses normalized donor phone when available', function () {
    Http::fake([
        'https://dev.toyyibpay.com/index.php/api/createBill' => Http::response([
            ['BillCode' => 'BILL-CASH-PHONE'],
        ]),
    ]);

    $donor = User::factory()->create([
        'role' => 'penderma',
    ]);
    $donor->donor()->create([
        'donor_type' => 'individu',
        'phone' => '010-123 4567',
        'preferred_contact' => 'phone',
    ]);

    $this
        ->actingAs($donor)
        ->post(route('penderma.tabung.store'), [
            'amount_choice' => '20',
            'message' => null,
        ])
        ->assertRedirect('https://dev.toyyibpay.com/BILL-CASH-PHONE');

    Http::assertSent(fn ($request) => $request->url() === 'https://dev.toyyibpay.com/index.php/api/createBill'
        && $request['billPhone'] === '0101234567');
});

test('successful ToyyibPay callback marks cash donation as successful', function () {
    Carbon::setTestNow('2026-07-16 09:30:00');

    $cashDonation = CashDonation::create([
        'user_id' => User::factory()->create(['role' => 'penderma'])->id,
        'amount' => 50,
        'bill_code' => 'BILL-SUCCESS',
        'reference_no' => 'TAB/20260625/000001',
        'payment_status' => CashDonation::STATUS_PENDING,
    ]);

    $payload = toyyibpayCashDonationPayload($cashDonation, '1', 'FPX-SUCCESS-001');

    $this
        ->post(route('penderma.tabung.callback'), $payload)
        ->assertOk()
        ->assertSee('OK');

    $cashDonation->refresh();

    expect($cashDonation->payment_status)->toBe(CashDonation::STATUS_SUCCESS)
        ->and($cashDonation->paid_at)->not->toBeNull()
        ->and($cashDonation->paid_at->format('Y-m-d H:i:s'))->toBe('2026-07-16 09:30:00')
        ->and($cashDonation->resolved_at)->not->toBeNull()
        ->and($cashDonation->resolved_at->format('Y-m-d H:i:s'))->toBe('2026-07-16 09:30:00')
        ->and($cashDonation->reference_no)->toBe('TAB/20260625/000001')
        ->and($cashDonation->transaction_id)->toBe('FPX-SUCCESS-001')
        ->and($cashDonation->bill_code)->toBe('BILL-SUCCESS')
        ->and(data_get($cashDonation->raw_response, 'status'))->toBe('1');
});

test('cash donation ToyyibPay callback still matches by bill code', function () {
    Carbon::setTestNow('2026-07-16 09:45:00');

    $cashDonation = CashDonation::create([
        'user_id' => User::factory()->create(['role' => 'penderma'])->id,
        'amount' => 75,
        'bill_code' => 'BILL-BILLCODE-MATCH',
        'reference_no' => 'TAB/20260625/000099',
        'payment_status' => CashDonation::STATUS_PENDING,
    ]);

    $orderId = 'TAB/20990101/999999';
    $reference = 'TP-BILLCODE-001';

    $this
        ->post(route('penderma.tabung.callback'), [
            'status' => '1',
            'billcode' => 'BILL-BILLCODE-MATCH',
            'order_id' => $orderId,
            'refno' => $reference,
            'amount' => (string) $cashDonation->amount,
            'hash' => md5(config('services.toyyibpay.secret_key') . '1' . $orderId . $reference . 'ok'),
        ])
        ->assertOk();

    $cashDonation->refresh();

    expect($cashDonation->payment_status)->toBe(CashDonation::STATUS_SUCCESS)
        ->and($cashDonation->reference_no)->toBe('TAB/20260625/000099')
        ->and($cashDonation->transaction_id)->toBe('TP-BILLCODE-001')
        ->and($cashDonation->bill_code)->toBe('BILL-BILLCODE-MATCH');
});

test('failed ToyyibPay callback marks cash donation as failed', function () {
    Carbon::setTestNow('2026-07-16 10:45:00');

    $cashDonation = CashDonation::create([
        'user_id' => User::factory()->create(['role' => 'penderma'])->id,
        'amount' => 80,
        'bill_code' => 'BILL-FAILED',
        'reference_no' => 'TAB/20260625/000002',
        'payment_status' => CashDonation::STATUS_PENDING,
    ]);

    $payload = toyyibpayCashDonationPayload($cashDonation, '3', 'FPX-FAILED-001');

    $this
        ->post(route('penderma.tabung.callback'), $payload)
        ->assertOk()
        ->assertSee('OK');

    $cashDonation->refresh();

    expect($cashDonation->payment_status)->toBe(CashDonation::STATUS_FAILED)
        ->and($cashDonation->paid_at)->toBeNull()
        ->and($cashDonation->resolved_at)->not->toBeNull()
        ->and($cashDonation->resolved_at->format('Y-m-d H:i:s'))->toBe('2026-07-16 10:45:00')
        ->and($cashDonation->reference_no)->toBe('TAB/20260625/000002')
        ->and($cashDonation->transaction_id)->toBe('FPX-FAILED-001')
        ->and(data_get($cashDonation->raw_response, 'status'))->toBe('3');
});

test('duplicate ToyyibPay callbacks do not overwrite cash donation resolution timestamp', function () {
    Carbon::setTestNow('2026-07-16 11:00:00');

    $cashDonation = CashDonation::create([
        'user_id' => User::factory()->create(['role' => 'penderma'])->id,
        'amount' => 65,
        'bill_code' => 'BILL-DUPLICATE',
        'reference_no' => 'TAB/20260716/000001',
        'payment_status' => CashDonation::STATUS_PENDING,
    ]);

    $payload = toyyibpayCashDonationPayload($cashDonation, '1', 'FPX-DUPLICATE-001');

    $this
        ->post(route('penderma.tabung.callback'), $payload)
        ->assertOk();

    $cashDonation->refresh();

    $firstPaidAt = $cashDonation->paid_at->copy();
    $firstResolvedAt = $cashDonation->resolved_at->copy();

    Carbon::setTestNow('2026-07-16 13:30:00');

    $this
        ->post(route('penderma.tabung.callback'), $payload)
        ->assertOk();

    $cashDonation->refresh();

    expect($cashDonation->payment_status)->toBe(CashDonation::STATUS_SUCCESS)
        ->and($cashDonation->paid_at->equalTo($firstPaidAt))->toBeTrue()
        ->and($cashDonation->resolved_at->equalTo($firstResolvedAt))->toBeTrue()
        ->and($cashDonation->reference_no)->toBe('TAB/20260716/000001')
        ->and($cashDonation->resolved_at->format('Y-m-d H:i:s'))->toBe('2026-07-16 11:00:00');
});

test('pending ToyyibPay callback keeps cash donation unresolved', function () {
    Carbon::setTestNow('2026-07-16 12:15:00');

    $cashDonation = CashDonation::create([
        'user_id' => User::factory()->create(['role' => 'penderma'])->id,
        'amount' => 55,
        'bill_code' => 'BILL-PENDING',
        'reference_no' => 'TAB/20260716/000002',
        'payment_status' => CashDonation::STATUS_PENDING,
    ]);

    $payload = toyyibpayCashDonationPayload($cashDonation, '2', 'FPX-PENDING-001');

    $this
        ->post(route('penderma.tabung.callback'), $payload)
        ->assertOk();

    $cashDonation->refresh();

    expect($cashDonation->payment_status)->toBe(CashDonation::STATUS_PENDING)
        ->and($cashDonation->reference_no)->toBe('TAB/20260716/000002')
        ->and($cashDonation->paid_at)->toBeNull()
        ->and($cashDonation->resolved_at)->toBeNull();
});

test('cash donation receipt is only available after successful payment', function () {
    $donor = User::factory()->create([
        'role' => 'penderma',
    ]);

    $cashDonation = CashDonation::create([
        'user_id' => $donor->id,
        'amount' => 90,
        'bill_code' => 'BILL-RECEIPT',
        'reference_no' => 'TAB/20260625/000003',
        'payment_status' => CashDonation::STATUS_PENDING,
    ]);

    $this
        ->actingAs($donor)
        ->get(route('penderma.tabung.receipt', $cashDonation))
        ->assertRedirect(route('penderma.sejarah-sumbangan'));

    $cashDonation->update([
        'payment_status' => CashDonation::STATUS_SUCCESS,
        'paid_at' => now(),
        'resolved_at' => now(),
    ]);

    $this
        ->actingAs($donor)
        ->get(route('penderma.tabung.receipt', $cashDonation))
        ->assertOk()
        ->assertSee('ToyyibPay')
        ->assertDontSee('rekod simulasi lama');
});

test('admin sumbangan report displays cash donation reference number instead of ToyyibPay identifiers', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $donor = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Tabung Report Donor',
    ]);

    createCashDonationRecord([
        'user_id' => $donor->id,
        'amount' => 150,
        'reference_no' => 'TAB/20260719/000055',
        'bill_code' => 'y2iu867e',
        'transaction_id' => 'TP2607191751758997',
        'payment_status' => CashDonation::STATUS_SUCCESS,
        'paid_at' => '2026-07-19 17:55:00',
        'resolved_at' => '2026-07-19 17:55:00',
        'created_at' => '2026-07-19 17:51:00',
        'updated_at' => '2026-07-19 17:55:00',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.statistik.sumbangan'))
        ->assertOk()
        ->assertSee('TAB/20260719/000055')
        ->assertDontSee('y2iu867e')
        ->assertDontSee('TP2607191751758997');
});

test('admin sumbangan CSV exports cash donation reference number instead of ToyyibPay identifiers', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $donor = User::factory()->create(['role' => 'penderma']);

    createCashDonationRecord([
        'user_id' => $donor->id,
        'amount' => 175,
        'reference_no' => 'TAB/20260719/000056',
        'bill_code' => 'mm9hxx0o',
        'transaction_id' => 'TP2607191751758998',
        'payment_status' => CashDonation::STATUS_SUCCESS,
        'paid_at' => '2026-07-19 18:05:00',
        'resolved_at' => '2026-07-19 18:05:00',
        'created_at' => '2026-07-19 18:01:00',
        'updated_at' => '2026-07-19 18:05:00',
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.statistik.sumbangan.csv'))
        ->assertOk();

    $csv = $response->streamedContent();

    expect($csv)->toContain('TAB/20260719/000056')
        ->not->toContain('mm9hxx0o')
        ->not->toContain('TP2607191751758998');
});

test('cash donation reference number backfill preserves existing payment data', function () {
    $donor = User::factory()->create(['role' => 'penderma']);

    $cashDonation = createCashDonationRecord([
        'user_id' => $donor->id,
        'amount' => 210,
        'reference_no' => null,
        'bill_code' => 'BILL-BACKFILL',
        'transaction_id' => 'TP-BACKFILL-001',
        'payment_status' => CashDonation::STATUS_SUCCESS,
        'paid_at' => '2026-07-19 12:00:00',
        'resolved_at' => '2026-07-19 12:05:00',
        'created_at' => '2026-07-19 11:50:00',
        'updated_at' => '2026-07-19 12:05:00',
    ]);

    $existingReference = createCashDonationRecord([
        'user_id' => $donor->id,
        'amount' => 220,
        'reference_no' => 'TAB/KEEP/000001',
        'bill_code' => 'BILL-KEEP',
        'transaction_id' => 'TP-KEEP-001',
        'payment_status' => CashDonation::STATUS_SUCCESS,
        'paid_at' => '2026-07-19 13:00:00',
        'resolved_at' => '2026-07-19 13:05:00',
        'created_at' => '2026-07-19 12:50:00',
        'updated_at' => '2026-07-19 13:05:00',
    ]);

    $migration = include database_path('migrations/2026_07_19_000001_add_reference_no_to_cash_donations_table.php');
    $migration->up();

    $cashDonation->refresh();
    $existingReference->refresh();

    expect($cashDonation->reference_no)->toBe(sprintf('TAB/20260719/%06d', $cashDonation->id))
        ->and($cashDonation->bill_code)->toBe('BILL-BACKFILL')
        ->and($cashDonation->transaction_id)->toBe('TP-BACKFILL-001')
        ->and($cashDonation->payment_status)->toBe(CashDonation::STATUS_SUCCESS)
        ->and((float) $cashDonation->amount)->toBe(210.0)
        ->and($cashDonation->user_id)->toBe($donor->id)
        ->and($cashDonation->paid_at->format('Y-m-d H:i:s'))->toBe('2026-07-19 12:00:00')
        ->and($cashDonation->resolved_at->format('Y-m-d H:i:s'))->toBe('2026-07-19 12:05:00')
        ->and($cashDonation->updated_at->format('Y-m-d H:i:s'))->toBe('2026-07-19 12:05:00')
        ->and($existingReference->reference_no)->toBe('TAB/KEEP/000001')
        ->and(CashDonation::query()->where('reference_no', $cashDonation->reference_no)->count())->toBe(1);
});

test('pending and failed cash donations do not increase dana terkumpul', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    $donor = User::factory()->create([
        'role' => 'penderma',
    ]);

    AppSetting::put('tabung_target', 1000);

    CashDonation::create([
        'user_id' => $donor->id,
        'amount' => 100,
        'payment_status' => CashDonation::STATUS_SUCCESS,
        'paid_at' => now(),
        'resolved_at' => now(),
    ]);
    CashDonation::create([
        'user_id' => $donor->id,
        'amount' => 200,
        'payment_status' => CashDonation::STATUS_PENDING,
    ]);
    CashDonation::create([
        'user_id' => $donor->id,
        'amount' => 300,
        'payment_status' => CashDonation::STATUS_FAILED,
        'resolved_at' => now(),
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.tabung.index'))
        ->assertOk()
        ->assertSee('Dana Terkumpul')
        ->assertSee('RM100.00')
        ->assertDontSee('RM600.00');
});

test('admin tabung list orders all transactions by newest effective resolution timestamp', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $latestFailedDonor = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Latest Failed Resolved Donor',
    ]);
    $middleSuccessDonor = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Middle Success Resolved Donor',
    ]);
    $oldPendingDonor = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Old Pending Created Donor',
    ]);

    createCashDonationRecord([
        'user_id' => $middleSuccessDonor->id,
        'amount' => 100,
        'payment_status' => CashDonation::STATUS_SUCCESS,
        'paid_at' => '2026-07-08 09:00:00',
        'resolved_at' => '2026-07-08 09:05:00',
        'created_at' => '2026-07-01 09:00:00',
        'updated_at' => '2026-07-08 09:05:00',
    ]);
    createCashDonationRecord([
        'user_id' => $latestFailedDonor->id,
        'amount' => 200,
        'payment_status' => CashDonation::STATUS_FAILED,
        'resolved_at' => '2026-07-10 14:30:00',
        'created_at' => '2026-07-01 08:00:00',
        'updated_at' => '2026-07-10 14:30:00',
    ]);
    createCashDonationRecord([
        'user_id' => $oldPendingDonor->id,
        'amount' => 300,
        'payment_status' => CashDonation::STATUS_PENDING,
        'created_at' => '2026-07-07 10:00:00',
        'updated_at' => '2026-07-07 10:00:00',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.tabung.index'))
        ->assertOk()
        ->assertSeeInOrder([
            'Latest Failed Resolved Donor',
            'Middle Success Resolved Donor',
            'Old Pending Created Donor',
        ]);
});

test('admin tabung success and failed filters preserve newest-first effective timestamp ordering', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $newSuccessDonor = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Newest Success Donor',
    ]);
    $oldSuccessDonor = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Older Success Donor',
    ]);
    $newFailedDonor = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Newest Failed Donor',
    ]);
    $oldFailedDonor = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Older Failed Donor',
    ]);

    createCashDonationRecord([
        'user_id' => $oldSuccessDonor->id,
        'amount' => 100,
        'payment_status' => CashDonation::STATUS_SUCCESS,
        'paid_at' => '2026-07-03 08:00:00',
        'resolved_at' => '2026-07-03 08:00:00',
        'created_at' => '2026-07-01 08:00:00',
        'updated_at' => '2026-07-03 08:00:00',
    ]);
    createCashDonationRecord([
        'user_id' => $newSuccessDonor->id,
        'amount' => 110,
        'payment_status' => CashDonation::STATUS_SUCCESS,
        'paid_at' => '2026-07-09 08:00:00',
        'resolved_at' => '2026-07-09 08:00:00',
        'created_at' => '2026-07-02 08:00:00',
        'updated_at' => '2026-07-09 08:00:00',
    ]);
    createCashDonationRecord([
        'user_id' => $oldFailedDonor->id,
        'amount' => 120,
        'payment_status' => CashDonation::STATUS_FAILED,
        'resolved_at' => '2026-07-04 08:00:00',
        'created_at' => '2026-07-01 08:00:00',
        'updated_at' => '2026-07-04 08:00:00',
    ]);
    createCashDonationRecord([
        'user_id' => $newFailedDonor->id,
        'amount' => 130,
        'payment_status' => CashDonation::STATUS_FAILED,
        'resolved_at' => '2026-07-10 08:00:00',
        'created_at' => '2026-07-01 08:00:00',
        'updated_at' => '2026-07-10 08:00:00',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.tabung.index', ['status' => CashDonation::STATUS_SUCCESS]))
        ->assertOk()
        ->assertSeeInOrder([
            'Newest Success Donor',
            'Older Success Donor',
        ])
        ->assertDontSee('Newest Failed Donor');

    $this
        ->actingAs($admin)
        ->get(route('admin.tabung.index', ['status' => CashDonation::STATUS_FAILED]))
        ->assertOk()
        ->assertSeeInOrder([
            'Newest Failed Donor',
            'Older Failed Donor',
        ])
        ->assertDontSee('Newest Success Donor');
});

test('admin tabung Tarikh displays the same effective timestamp used for sorting', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $donor = User::factory()->create([
        'role' => 'penderma',
        'name' => 'Resolved Date Display Donor',
    ]);

    createCashDonationRecord([
        'user_id' => $donor->id,
        'amount' => 140,
        'payment_status' => CashDonation::STATUS_SUCCESS,
        'paid_at' => '2026-07-10 09:00:00',
        'resolved_at' => '2026-07-11 15:45:00',
        'created_at' => '2026-07-01 09:00:00',
        'updated_at' => '2026-07-11 15:45:00',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.tabung.index'))
        ->assertOk()
        ->assertSee('Resolved Date Display Donor')
        ->assertSee('11/07/2026 03:45 PM')
        ->assertDontSee('10/07/2026 09:00 AM');
});

function toyyibpayCashDonationPayload(CashDonation $cashDonation, string $status, string $reference): array
{
    $orderId = $cashDonation->reference_no;

    return [
        'status' => $status,
        'billcode' => $cashDonation->bill_code,
        'order_id' => $orderId,
        'refno' => $reference,
        'amount' => (string) $cashDonation->amount,
        'hash' => md5(config('services.toyyibpay.secret_key') . $status . $orderId . $reference . 'ok'),
    ];
}

function createCashDonationRecord(array $attributes): CashDonation
{
    return CashDonation::unguarded(fn () => CashDonation::create($attributes));
}
