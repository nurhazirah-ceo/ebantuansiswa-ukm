<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->applyDates($this->correctedDates());
    }

    public function down(): void
    {
        $this->applyDates($this->originalDates());
    }

    /**
     * @param array<string, array{status: string, date: string}> $dates
     */
    private function applyDates(array $dates): void
    {
        if (! Schema::hasTable('cash_donations')) {
            return;
        }

        foreach ($dates as $billCode => $record) {
            DB::table('cash_donations')
                ->where('bill_code', $billCode)
                ->update($this->timestampValues($record['status'], $record['date']));
        }
    }

    /**
     * @return array<string, string|null>
     */
    private function timestampValues(string $status, string $date): array
    {
        $resolvedAt = Carbon::parse($date);

        if ($status === 'failed') {
            return [
                'paid_at' => null,
                'resolved_at' => $resolvedAt->copy()->addMinutes(20)->toDateTimeString(),
                'created_at' => $resolvedAt->toDateTimeString(),
                'updated_at' => $resolvedAt->copy()->addMinutes(20)->toDateTimeString(),
            ];
        }

        return [
            'paid_at' => $resolvedAt->toDateTimeString(),
            'resolved_at' => $resolvedAt->toDateTimeString(),
            'created_at' => $resolvedAt->copy()->subMinutes(12)->toDateTimeString(),
            'updated_at' => $resolvedAt->toDateTimeString(),
        ];
    }

    /**
     * @return array<string, array{status: string, date: string}>
     */
    private function correctedDates(): array
    {
        return [
            'DUMMY-TAB-0015' => ['status' => 'success', 'date' => '2026-01-24 10:20:00'],
            'DUMMY-TAB-0016' => ['status' => 'success', 'date' => '2026-01-29 11:20:00'],
            'DUMMY-TAB-0017' => ['status' => 'success', 'date' => '2026-02-24 10:20:00'],
            'DUMMY-TAB-0018' => ['status' => 'success', 'date' => '2026-02-27 11:20:00'],
            'DUMMY-TAB-0019' => ['status' => 'success', 'date' => '2026-03-24 10:20:00'],
            'DUMMY-TAB-0020' => ['status' => 'success', 'date' => '2026-04-24 11:20:00'],
            'DUMMY-TAB-0021' => ['status' => 'success', 'date' => '2026-05-24 10:20:00'],
            'DUMMY-TAB-0022' => ['status' => 'success', 'date' => '2026-06-24 11:20:00'],
            'DUMMY-TAB-0023' => ['status' => 'success', 'date' => '2026-07-04 10:20:00'],
            'DUMMY-TAB-0024' => ['status' => 'success', 'date' => '2026-07-12 11:20:00'],
            'DUMMY-TAB-FAILED-04' => ['status' => 'failed', 'date' => '2026-04-28 16:05:00'],
            'DUMMY-TAB-FAILED-05' => ['status' => 'failed', 'date' => '2026-06-28 16:05:00'],
            'DUMMY-TAB-FAILED-06' => ['status' => 'failed', 'date' => '2026-07-14 16:05:00'],
        ];
    }

    /**
     * @return array<string, array{status: string, date: string}>
     */
    private function originalDates(): array
    {
        return [
            'DUMMY-TAB-0015' => ['status' => 'success', 'date' => '2026-08-06 10:20:00'],
            'DUMMY-TAB-0016' => ['status' => 'success', 'date' => '2026-08-17 11:20:00'],
            'DUMMY-TAB-0017' => ['status' => 'success', 'date' => '2026-09-06 10:20:00'],
            'DUMMY-TAB-0018' => ['status' => 'success', 'date' => '2026-09-17 11:20:00'],
            'DUMMY-TAB-0019' => ['status' => 'success', 'date' => '2026-10-06 10:20:00'],
            'DUMMY-TAB-0020' => ['status' => 'success', 'date' => '2026-10-17 11:20:00'],
            'DUMMY-TAB-0021' => ['status' => 'success', 'date' => '2026-11-06 10:20:00'],
            'DUMMY-TAB-0022' => ['status' => 'success', 'date' => '2026-11-17 11:20:00'],
            'DUMMY-TAB-0023' => ['status' => 'success', 'date' => '2026-12-06 10:20:00'],
            'DUMMY-TAB-0024' => ['status' => 'success', 'date' => '2026-12-17 11:20:00'],
            'DUMMY-TAB-FAILED-04' => ['status' => 'failed', 'date' => '2026-09-21 16:05:00'],
            'DUMMY-TAB-FAILED-05' => ['status' => 'failed', 'date' => '2026-11-16 16:05:00'],
            'DUMMY-TAB-FAILED-06' => ['status' => 'failed', 'date' => '2026-12-20 16:05:00'],
        ];
    }
};
