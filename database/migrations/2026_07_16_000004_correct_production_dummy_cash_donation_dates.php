<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->applyDates('corrected_date');
    }

    public function down(): void
    {
        $this->applyDates('original_date');
    }

    private function applyDates(string $dateKey): void
    {
        if (! Schema::hasTable('cash_donations')) {
            return;
        }

        foreach ($this->records() as $record) {
            DB::table('cash_donations')
                ->where('transaction_id', $record['transaction_id'])
                ->where('bill_code', $record['bill_code'])
                ->where('payment_status', $record['status'])
                ->where('amount', $record['amount'])
                ->update($this->timestampValues($record['status'], $record[$dateKey]));
        }
    }

    /**
     * @return array<string, string|null>
     */
    private function timestampValues(string $status, string $date): array
    {
        $effectiveAt = Carbon::parse($date);

        if ($status === 'failed') {
            return [
                'paid_at' => null,
                'resolved_at' => $effectiveAt->copy()->addMinutes(20)->toDateTimeString(),
                'created_at' => $effectiveAt->toDateTimeString(),
                'updated_at' => $effectiveAt->copy()->addMinutes(20)->toDateTimeString(),
            ];
        }

        return [
            'paid_at' => $effectiveAt->toDateTimeString(),
            'resolved_at' => $effectiveAt->toDateTimeString(),
            'created_at' => $effectiveAt->copy()->subMinutes(12)->toDateTimeString(),
            'updated_at' => $effectiveAt->toDateTimeString(),
        ];
    }

    /**
     * @return array<int, array{
     *     transaction_id: string,
     *     bill_code: string,
     *     status: string,
     *     amount: string,
     *     original_date: string,
     *     corrected_date: string
     * }>
     */
    private function records(): array
    {
        return [
            [
                'transaction_id' => 'TPD-2026-000015',
                'bill_code' => 'TAB-2026-0015',
                'status' => 'success',
                'amount' => '500.00',
                'original_date' => '2026-08-06 10:20:00',
                'corrected_date' => '2026-01-24 10:20:00',
            ],
            [
                'transaction_id' => 'TPD-2026-000016',
                'bill_code' => 'TAB-2026-0016',
                'status' => 'success',
                'amount' => '400.00',
                'original_date' => '2026-08-17 11:20:00',
                'corrected_date' => '2026-01-29 11:20:00',
            ],
            [
                'transaction_id' => 'TPD-2026-000017',
                'bill_code' => 'TAB-2026-0017',
                'status' => 'success',
                'amount' => '1800.00',
                'original_date' => '2026-09-06 10:20:00',
                'corrected_date' => '2026-02-24 10:20:00',
            ],
            [
                'transaction_id' => 'TPD-2026-000018',
                'bill_code' => 'TAB-2026-0018',
                'status' => 'success',
                'amount' => '1000.00',
                'original_date' => '2026-09-17 11:20:00',
                'corrected_date' => '2026-02-27 11:20:00',
            ],
            [
                'transaction_id' => 'TPD-2026-000019',
                'bill_code' => 'TAB-2026-0019',
                'status' => 'success',
                'amount' => '1000.00',
                'original_date' => '2026-10-06 10:20:00',
                'corrected_date' => '2026-03-24 10:20:00',
            ],
            [
                'transaction_id' => 'TPD-2026-000020',
                'bill_code' => 'TAB-2026-0020',
                'status' => 'success',
                'amount' => '500.00',
                'original_date' => '2026-10-17 11:20:00',
                'corrected_date' => '2026-04-24 11:20:00',
            ],
            [
                'transaction_id' => 'TPD-2026-000021',
                'bill_code' => 'TAB-2026-0021',
                'status' => 'success',
                'amount' => '2500.00',
                'original_date' => '2026-11-06 10:20:00',
                'corrected_date' => '2026-05-24 10:20:00',
            ],
            [
                'transaction_id' => 'TPD-2026-000022',
                'bill_code' => 'TAB-2026-0022',
                'status' => 'success',
                'amount' => '1700.00',
                'original_date' => '2026-11-17 11:20:00',
                'corrected_date' => '2026-06-24 11:20:00',
            ],
            [
                'transaction_id' => 'TPD-2026-000023',
                'bill_code' => 'TAB-2026-0023',
                'status' => 'success',
                'amount' => '1200.00',
                'original_date' => '2026-12-06 10:20:00',
                'corrected_date' => '2026-07-04 10:20:00',
            ],
            [
                'transaction_id' => 'TPD-2026-000024',
                'bill_code' => 'TAB-2026-0024',
                'status' => 'success',
                'amount' => '900.00',
                'original_date' => '2026-12-17 11:20:00',
                'corrected_date' => '2026-07-12 11:20:00',
            ],
            [
                'transaction_id' => 'TPD-FAILED-2026-004',
                'bill_code' => 'TAB-2026-0028',
                'status' => 'failed',
                'amount' => '450.00',
                'original_date' => '2026-09-21 16:05:00',
                'corrected_date' => '2026-04-28 16:05:00',
            ],
            [
                'transaction_id' => 'TPD-FAILED-2026-005',
                'bill_code' => 'TAB-2026-0029',
                'status' => 'failed',
                'amount' => '800.00',
                'original_date' => '2026-11-16 16:05:00',
                'corrected_date' => '2026-06-28 16:05:00',
            ],
            [
                'transaction_id' => 'TPD-FAILED-2026-006',
                'bill_code' => 'TAB-2026-0030',
                'status' => 'failed',
                'amount' => '250.00',
                'original_date' => '2026-12-20 16:05:00',
                'corrected_date' => '2026-07-14 16:05:00',
            ],
        ];
    }
};
