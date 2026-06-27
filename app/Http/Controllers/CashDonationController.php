<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CashDonation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CashDonationController extends Controller
{
    public function create()
    {
        return view('penderma.tabung.create', [
            'amountOptions' => [10, 20, 50, 100],
        ]);
    }

    public function store(Request $request)
    {
        $amountChoice = (string) $request->input('amount_choice', '');
        $request->merge([
            'amount' => $amountChoice === 'custom'
                ? $request->input('custom_amount')
                : $amountChoice,
        ]);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:999999.99'],
            'message' => ['nullable', 'string', 'max:1000'],
        ], [
            'amount.required' => 'Sila pilih atau masukkan jumlah sumbangan.',
            'amount.numeric' => 'Jumlah sumbangan mesti dalam format nombor.',
            'amount.min' => 'Jumlah minimum sumbangan ialah RM1.00.',
        ]);

        $this->assertToyyibPayConfigured();

        $cashDonation = CashDonation::create([
            'user_id' => Auth::id(),
            'amount' => round((float) $validated['amount'], 2),
            'message' => $validated['message'] ?? null,
            'payment_status' => CashDonation::STATUS_PENDING,
            'paid_at' => null,
        ]);

        $reference = $this->cashDonationReference($cashDonation);
        $cashDonation->update([
            'transaction_id' => $reference,
            'raw_response' => [
                'category' => 'tabung',
                'payment_method' => 'toyyibpay',
                'reference' => $reference,
                'status' => 'pending',
                'verified_by_gateway' => false,
            ],
        ]);

        try {
            $bill = $this->createToyyibPayBill($cashDonation);

            $cashDonation->update([
                'bill_code' => $bill['bill_code'],
                'raw_response' => array_merge($cashDonation->raw_response ?? [], [
                    'bill_code' => $bill['bill_code'],
                    'status' => 'bill_created',
                    'toyyibpay' => $bill['raw'],
                ]),
            ]);
        } catch (ValidationException $exception) {
            $cashDonation->update([
                'payment_status' => CashDonation::STATUS_FAILED,
                'raw_response' => array_merge($cashDonation->raw_response ?? [], [
                    'status' => 'bill_creation_failed',
                    'errors' => $exception->errors(),
                ]),
            ]);

            throw $exception;
        }

        return redirect()->away($bill['payment_url']);
    }

    public function receipt(CashDonation $cashDonation)
    {
        $this->authorizeCashDonation($cashDonation);

        if (! $this->cashDonationReceiptAvailable($cashDonation)) {
            return $this->unavailableCashReceiptResponse();
        }

        return view('penderma.tabung.receipt', [
            'cashDonation' => $cashDonation->loadMissing('user'),
            'reference' => $this->cashDonationReference($cashDonation),
        ]);
    }

    public function status(CashDonation $cashDonation)
    {
        $this->authorizeCashDonation($cashDonation);

        return $this->statusView($cashDonation);
    }

    public function downloadReceipt(CashDonation $cashDonation)
    {
        $this->authorizeCashDonation($cashDonation);

        if (! $this->cashDonationReceiptAvailable($cashDonation)) {
            return $this->unavailableCashReceiptResponse();
        }

        $reference = $this->cashDonationReference($cashDonation);
        $fileName = 'resit-sumbangan-' . Str::of($reference)
            ->replace(['/', '\\'], '-')
            ->replaceMatches('/[^A-Za-z0-9\-]+/', '-')
            ->trim('-')
            ->toString() . '.pdf';

        return Pdf::loadView('penderma.tabung.receipt-pdf', [
            'cashDonation' => $cashDonation->loadMissing('user'),
            'reference' => $reference,
        ])
            ->setPaper('a4')
            ->download($fileName);
    }

    public function return(Request $request)
    {
        $payload = $request->all();
        $cashDonation = $this->findCashDonationForToyyibPayPayload($payload);

        if (! $cashDonation || (int) $cashDonation->user_id !== (int) Auth::id()) {
            return $this->statusView(null, [
                'cashDonation' => null,
                'reference' => null,
                'statusLabel' => 'Rekod Tidak Ditemui',
                'statusClass' => 'bg-rose-100 text-rose-700 border border-rose-200',
                'message' => 'Rekod pembayaran ToyyibPay tidak ditemui untuk akaun ini.',
                'paymentUrl' => null,
            ]);
        }

        $paymentData = $this->paymentDataFromToyyibPayPayload($payload);
        $transaction = $this->fetchToyyibPayTransaction($paymentData['bill_code'] ?: $cashDonation->bill_code);

        if ($transaction) {
            $paymentData = $this->paymentDataFromToyyibPayPayload(array_merge(
                $transaction,
                [
                    'billcode' => $paymentData['bill_code'] ?: $cashDonation->bill_code,
                    'order_id' => $paymentData['order_id'] ?: ($transaction['billExternalReferenceNo'] ?? null),
                    'return_payload' => $payload,
                ]
            ));
        }

        if ($transaction || $this->isFailedPaymentStatus($paymentData['status'])) {
            $this->applyToyyibPayPaymentStatus($cashDonation, $paymentData);
        }

        $cashDonation->refresh();

        if (in_array($cashDonation->payment_status, [CashDonation::STATUS_SUCCESS, CashDonation::STATUS_FAILED], true)) {
            return view('penderma.tabung.toyyibpay-result', [
                'alertTitle' => $cashDonation->payment_status === CashDonation::STATUS_SUCCESS
                    ? 'Payment Successful'
                    : 'Payment Failed',
                'alertMessage' => $cashDonation->payment_status === CashDonation::STATUS_SUCCESS
                    ? 'Your donation has been recorded successfully.'
                    : 'Your payment was unsuccessful or has been cancelled.',
                'alertIcon' => $cashDonation->payment_status === CashDonation::STATUS_SUCCESS ? 'success' : 'error',
                'redirectUrl' => route('penderma.tabung.status', $cashDonation),
            ]);
        }

        return $this->statusView($cashDonation);
    }

    public function callback(Request $request)
    {
        $payload = $request->all();

        if (! $this->isValidToyyibPayHash($payload)) {
            Log::warning('ToyyibPay cash donation callback rejected because hash validation failed.', [
                'payload' => $payload,
            ]);

            return response('Invalid callback hash', 403);
        }

        $cashDonation = $this->findCashDonationForToyyibPayPayload($payload);

        if (! $cashDonation) {
            Log::warning('ToyyibPay callback received for unknown cash donation.', [
                'payload' => $payload,
            ]);

            return response('Cash donation not found', 404);
        }

        $this->applyToyyibPayPaymentStatus($cashDonation, $this->paymentDataFromToyyibPayPayload($payload));

        return response('OK');
    }

    private function assertToyyibPayConfigured(): void
    {
        if (blank(config('services.toyyibpay.secret_key')) || blank(config('services.toyyibpay.category_code'))) {
            throw ValidationException::withMessages([
                'toyyibpay' => 'Tetapan ToyyibPay belum lengkap. Sila tetapkan TOYYIBPAY_SECRET_KEY dan TOYYIBPAY_CATEGORY_CODE.',
            ]);
        }
    }

    private function createToyyibPayBill(CashDonation $cashDonation): array
    {
        $user = Auth::user()?->loadMissing('donor');
        $reference = $this->cashDonationReference($cashDonation);
        $amountInCents = (int) round(((float) $cashDonation->amount) * 100);
        $billPhone = $this->toyyibPayBillPhone($user?->donor?->phone);

        $payload = [
            'userSecretKey' => config('services.toyyibpay.secret_key'),
            'categoryCode' => config('services.toyyibpay.category_code'),
            'billName' => $this->toyyibPayText('Tabung Bantuan Pelajar', 30),
            'billDescription' => $this->toyyibPayText('Sumbangan wang umum eBantuanSiswa UKM ' . $reference, 100),
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $amountInCents,
            'billReturnUrl' => route('penderma.tabung.return'),
            'billCallbackUrl' => route('penderma.tabung.callback'),
            'billExternalReferenceNo' => $reference,
            'billTo' => $user?->name ?? 'Penderma',
            'billEmail' => $user?->email ?? '',
            'billPhone' => $billPhone,
            'billSplitPayment' => 0,
            'billSplitPaymentArgs' => '',
            'billPaymentChannel' => config('services.toyyibpay.payment_channel', '0'),
            'billContentEmail' => 'Terima kasih atas sumbangan Tabung Bantuan Pelajar.',
            'billChargeToCustomer' => config('services.toyyibpay.charge_to_customer', '1'),
        ];

        try {
            $response = Http::asForm()
                ->timeout(30)
                ->post($this->toyyibPayApiUrl('createBill'), $payload);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'toyyibpay' => 'ToyyibPay tidak dapat dihubungi. Sila cuba lagi.',
            ]);
        }

        $data = $response->json();
        $billCode = is_array($data)
            ? (data_get($data, '0.BillCode') ?: data_get($data, 'BillCode'))
            : null;

        if (! $response->successful() || blank($billCode)) {
            throw ValidationException::withMessages([
                'toyyibpay' => $this->toyyibPayErrorMessage($data),
            ]);
        }

        return [
            'bill_code' => $billCode,
            'payment_url' => $this->toyyibPayPaymentUrl($billCode),
            'raw' => [
                'request' => collect($payload)
                    ->except('userSecretKey')
                    ->all(),
                'response' => $data,
            ],
        ];
    }

    private function applyToyyibPayPaymentStatus(CashDonation $cashDonation, array $paymentData): void
    {
        DB::transaction(function () use ($cashDonation, $paymentData) {
            $locked = CashDonation::query()
                ->lockForUpdate()
                ->findOrFail($cashDonation->id);

            if ($this->isSuccessfulPaymentStatus($paymentData['status'])) {
                $locked->update([
                    'payment_status' => CashDonation::STATUS_SUCCESS,
                    'transaction_id' => $paymentData['reference'] ?: $locked->transaction_id,
                    'bill_code' => $paymentData['bill_code'] ?: $locked->bill_code,
                    'raw_response' => $paymentData['raw'],
                    'paid_at' => $locked->paid_at ?: now(),
                ]);

                return;
            }

            if ($locked->payment_status === CashDonation::STATUS_SUCCESS) {
                return;
            }

            if ($this->isFailedPaymentStatus($paymentData['status'])) {
                $locked->update([
                    'payment_status' => CashDonation::STATUS_FAILED,
                    'transaction_id' => $paymentData['reference'] ?: $locked->transaction_id,
                    'bill_code' => $paymentData['bill_code'] ?: $locked->bill_code,
                    'raw_response' => $paymentData['raw'],
                ]);

                return;
            }

            if ($locked->payment_status !== CashDonation::STATUS_FAILED) {
                $locked->update([
                    'payment_status' => CashDonation::STATUS_PENDING,
                    'transaction_id' => $paymentData['reference'] ?: $locked->transaction_id,
                    'bill_code' => $paymentData['bill_code'] ?: $locked->bill_code,
                    'raw_response' => $paymentData['raw'],
                ]);
            }
        });
    }

    private function findCashDonationForToyyibPayPayload(array $payload): ?CashDonation
    {
        $paymentData = $this->paymentDataFromToyyibPayPayload($payload);

        if (filled($paymentData['bill_code'])) {
            $cashDonation = CashDonation::query()
                ->where('bill_code', $paymentData['bill_code'])
                ->first();

            if ($cashDonation) {
                return $cashDonation;
            }
        }

        $cashDonationId = $this->cashDonationIdFromReference((string) ($paymentData['order_id'] ?? ''));

        return $cashDonationId ? CashDonation::query()->find($cashDonationId) : null;
    }

    private function fetchToyyibPayTransaction(?string $billCode): ?array
    {
        if (blank($billCode) || blank(config('services.toyyibpay.secret_key'))) {
            return null;
        }

        try {
            $response = Http::asForm()
                ->timeout(20)
                ->post($this->toyyibPayApiUrl('getBillTransactions'), [
                    'billCode' => $billCode,
                ]);
        } catch (\Throwable $exception) {
            Log::warning('ToyyibPay cash donation transaction lookup failed.', [
                'bill_code' => $billCode,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return is_array($data) ? data_get($data, '0') : null;
    }

    private function paymentDataFromToyyibPayPayload(array $payload): array
    {
        $status = (string) (
            $payload['status']
            ?? $payload['status_id']
            ?? $payload['billpaymentStatus']
            ?? ''
        );

        return [
            'status' => $status,
            'bill_code' => $payload['billcode'] ?? $payload['billCode'] ?? $payload['BillCode'] ?? null,
            'order_id' => $payload['order_id'] ?? $payload['billExternalReferenceNo'] ?? null,
            'reference' => $payload['refno']
                ?? $payload['transaction_id']
                ?? $payload['billpaymentInvoiceNo']
                ?? $payload['fpx_transaction_id']
                ?? null,
            'amount' => $payload['amount'] ?? $payload['billpaymentAmount'] ?? null,
            'raw' => $payload,
        ];
    }

    private function isValidToyyibPayHash(array $payload): bool
    {
        $receivedHash = (string) ($payload['hash'] ?? '');

        if ($receivedHash === '') {
            return false;
        }

        $expectedHash = md5(
            (string) config('services.toyyibpay.secret_key')
            . (string) ($payload['status'] ?? $payload['status_id'] ?? '')
            . (string) ($payload['order_id'] ?? '')
            . (string) ($payload['refno'] ?? '')
            . 'ok'
        );

        return hash_equals($expectedHash, $receivedHash);
    }

    private function isSuccessfulPaymentStatus(?string $status): bool
    {
        return (string) $status === '1';
    }

    private function isFailedPaymentStatus(?string $status): bool
    {
        return (string) $status === '3';
    }

    private function statusPresentation(string $status): array
    {
        return match ($status) {
            CashDonation::STATUS_SUCCESS => [
                'label' => 'Pembayaran Berjaya',
                'class' => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                'message' => 'Sumbangan tabung anda telah direkodkan sebagai berjaya.',
            ],
            CashDonation::STATUS_FAILED => [
                'label' => 'Pembayaran Tidak Berjaya',
                'class' => 'bg-rose-100 text-rose-700 border border-rose-200',
                'message' => 'Pembayaran tidak berjaya atau telah dibatalkan.',
            ],
            default => [
                'label' => 'Menunggu Pengesahan',
                'class' => 'bg-amber-100 text-amber-700 border border-amber-200',
                'message' => 'Pembayaran sedang disahkan oleh ToyyibPay. Sila semak sejarah sumbangan sebentar lagi.',
            ],
        };
    }

    private function cashDonationReference(CashDonation $cashDonation): string
    {
        return sprintf('TAB/%s/%06d', ($cashDonation->created_at ?? now())->format('Ymd'), $cashDonation->id);
    }

    private function authorizeCashDonation(CashDonation $cashDonation): void
    {
        abort_unless((int) $cashDonation->user_id === (int) Auth::id(), 404);
    }

    private function cashDonationReceiptAvailable(CashDonation $cashDonation): bool
    {
        return $cashDonation->payment_status === CashDonation::STATUS_SUCCESS;
    }

    private function statusView(?CashDonation $cashDonation, array $overrides = [])
    {
        if (! $cashDonation) {
            return view('penderma.tabung.status', $overrides + [
                'cashDonation' => null,
                'reference' => null,
                'statusLabel' => 'Rekod Tidak Ditemui',
                'statusClass' => 'bg-rose-100 text-rose-700 border border-rose-200',
                'message' => 'Rekod pembayaran ToyyibPay tidak ditemui untuk akaun ini.',
                'paymentUrl' => null,
            ]);
        }

        $status = $this->statusPresentation($cashDonation->payment_status);

        return view('penderma.tabung.status', $overrides + [
            'cashDonation' => $cashDonation,
            'reference' => $this->cashDonationReference($cashDonation),
            'statusLabel' => $status['label'],
            'statusClass' => $status['class'],
            'message' => $status['message'],
            'paymentUrl' => $cashDonation->bill_code
                ? $this->toyyibPayPaymentUrl($cashDonation->bill_code)
                : null,
        ]);
    }

    private function unavailableCashReceiptResponse()
    {
        return redirect()
            ->route('penderma.sejarah-sumbangan')
            ->with('warning', 'Resit hanya boleh dijana selepas pembayaran berjaya.');
    }

    private function cashDonationIdFromReference(string $reference): ?int
    {
        if (preg_match('/^TAB\/\d{8}\/(\d+)$/', $reference, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/^TAB-(\d+)$/', $reference, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function toyyibPayApiUrl(string $endpoint): string
    {
        return rtrim((string) config('services.toyyibpay.base_url', 'https://dev.toyyibpay.com'), '/')
            . '/index.php/api/'
            . ltrim($endpoint, '/');
    }

    private function toyyibPayPaymentUrl(string $billCode): string
    {
        return rtrim((string) config('services.toyyibpay.base_url', 'https://dev.toyyibpay.com'), '/')
            . '/'
            . ltrim($billCode, '/');
    }

    private function toyyibPayBillPhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?: '';

        if (strlen($digits) >= 9 && strlen($digits) <= 15) {
            return $digits;
        }

        return '0100000000';
    }

    private function toyyibPayText(string $value, int $limit): string
    {
        $clean = preg_replace('/[^A-Za-z0-9 _]+/', ' ', $value) ?: 'Sumbangan';
        $clean = trim(preg_replace('/\s+/', ' ', $clean) ?: 'Sumbangan');

        return Str::limit($clean !== '' ? $clean : 'Sumbangan', $limit, '');
    }

    private function toyyibPayErrorMessage(mixed $data): string
    {
        if (is_array($data)) {
            $message = data_get($data, '0.msg')
                ?: data_get($data, '0.Message')
                ?: data_get($data, 'msg')
                ?: data_get($data, 'Message')
                ?: data_get($data, '0.error')
                ?: data_get($data, 'error');

            if (filled($message)) {
                return (string) $message;
            }
        }

        return 'Bill ToyyibPay tidak dapat dicipta. Sila cuba lagi.';
    }
}
