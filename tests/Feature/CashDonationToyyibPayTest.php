<?php

use App\Models\AppSetting;
use App\Models\CashDonation;
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
        ->and($cashDonation->transaction_id)->toBe(sprintf('TAB/%s/%06d', $cashDonation->created_at->format('Ymd'), $cashDonation->id))
        ->and($cashDonation->bill_code)->toBe('BILL-CASH-001')
        ->and(data_get($cashDonation->raw_response, 'payment_method'))->toBe('toyyibpay')
        ->and(data_get($cashDonation->raw_response, 'toyyibpay.response.0.BillCode'))->toBe('BILL-CASH-001');

    Http::assertSent(fn ($request) => $request->url() === 'https://dev.toyyibpay.com/index.php/api/createBill'
        && $request['billAmount'] === 12550
        && $request['billPhone'] === '0100000000'
        && $request['billExternalReferenceNo'] === $cashDonation->transaction_id
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
    $cashDonation = CashDonation::create([
        'user_id' => User::factory()->create(['role' => 'penderma'])->id,
        'amount' => 50,
        'bill_code' => 'BILL-SUCCESS',
        'transaction_id' => 'TAB/20260625/000001',
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
        ->and($cashDonation->transaction_id)->toBe('FPX-SUCCESS-001')
        ->and($cashDonation->bill_code)->toBe('BILL-SUCCESS')
        ->and(data_get($cashDonation->raw_response, 'status'))->toBe('1');
});

test('failed ToyyibPay callback marks cash donation as failed', function () {
    $cashDonation = CashDonation::create([
        'user_id' => User::factory()->create(['role' => 'penderma'])->id,
        'amount' => 80,
        'bill_code' => 'BILL-FAILED',
        'transaction_id' => 'TAB/20260625/000002',
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
        ->and($cashDonation->transaction_id)->toBe('FPX-FAILED-001')
        ->and(data_get($cashDonation->raw_response, 'status'))->toBe('3');
});

test('cash donation receipt is only available after successful payment', function () {
    $donor = User::factory()->create([
        'role' => 'penderma',
    ]);

    $cashDonation = CashDonation::create([
        'user_id' => $donor->id,
        'amount' => 90,
        'bill_code' => 'BILL-RECEIPT',
        'transaction_id' => 'TAB/20260625/000003',
        'payment_status' => CashDonation::STATUS_PENDING,
    ]);

    $this
        ->actingAs($donor)
        ->get(route('penderma.tabung.receipt', $cashDonation))
        ->assertRedirect(route('penderma.sejarah-sumbangan'));

    $cashDonation->update([
        'payment_status' => CashDonation::STATUS_SUCCESS,
        'paid_at' => now(),
    ]);

    $this
        ->actingAs($donor)
        ->get(route('penderma.tabung.receipt', $cashDonation))
        ->assertOk()
        ->assertSee('ToyyibPay')
        ->assertDontSee('rekod simulasi lama');
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
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.tabung.index'))
        ->assertOk()
        ->assertSee('Dana Terkumpul')
        ->assertSee('RM100.00')
        ->assertDontSee('RM600.00');
});

function toyyibpayCashDonationPayload(CashDonation $cashDonation, string $status, string $reference): array
{
    $orderId = $cashDonation->transaction_id;

    return [
        'status' => $status,
        'billcode' => $cashDonation->bill_code,
        'order_id' => $orderId,
        'refno' => $reference,
        'amount' => (string) $cashDonation->amount,
        'hash' => md5(config('services.toyyibpay.secret_key') . $status . $orderId . $reference . 'ok'),
    ];
}
