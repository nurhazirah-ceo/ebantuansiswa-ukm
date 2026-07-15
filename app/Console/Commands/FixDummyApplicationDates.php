<?php

namespace App\Console\Commands;

use App\Models\Permohonan;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;

class FixDummyApplicationDates extends Command
{
    protected $signature = 'dummy:fix-application-dates {--dry-run : Show records that would be updated without writing changes}';

    protected $description = 'One-time correction for DummyDataSeeder application dates to the June/July 2026 demo period.';

    private const TARGET_COUNT = 35;

    private const JENIS_PLAN = [
        'bantuan_asas_hidup',
        'bantuan_pembelajaran',
        'bantuan_sukan',
        'bantuan_musibah',
    ];

    private const DATE_COLUMNS = [
        'tarikh_mohon',
        'admin_review_date',
        'tarikh_agihan',
        'created_at',
        'updated_at',
    ];

    public function handle(): int
    {
        if (! $this->databaseIsReady()) {
            return self::FAILURE;
        }

        $plans = $this->dummyDateSchedule();
        $records = DB::table('permohonans')
            ->whereIn('no_kelompok', array_keys($plans))
            ->get()
            ->keyBy('no_kelompok');

        $missing = [];
        $skipped = [];
        $pendingUpdates = [];

        foreach ($plans as $noKelompok => $plan) {
            $record = $records->get($noKelompok);

            if (! $record) {
                $missing[] = $noKelompok;
                continue;
            }

            if (! $this->matchesDummySeederShape($record, $plan)) {
                $skipped[] = [
                    $record->id,
                    $record->no_kelompok,
                    $record->jenis_bantuan,
                    $plan['jenis_bantuan'],
                ];
                continue;
            }

            $expected = $this->expectedDateValues($record, $plan);
            $changedFields = $this->changedFields($record, $expected);

            if ($changedFields === []) {
                continue;
            }

            $pendingUpdates[] = [
                'id' => (int) $record->id,
                'no_kelompok' => (string) $record->no_kelompok,
                'fields' => $changedFields,
                'values' => $expected,
            ];
        }

        $this->line('DummyDataSeeder records found: '.$records->count().' / '.self::TARGET_COUNT);

        if ($missing !== []) {
            $this->warn('Missing expected dummy records: '.implode(', ', $missing));
        }

        if ($skipped !== []) {
            $this->warn('Skipped records whose application type does not match DummyDataSeeder.');
            $this->table(['ID', 'No Kelompok', 'Current Type', 'Expected Type'], $skipped);
        }

        if ($pendingUpdates === []) {
            $this->info('No matching records require date updates.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info(($this->option('dry-run') ? 'Dry run - would update' : 'Will update').' '.$this->pluralRecord(count($pendingUpdates)).':');
        $this->table(
            ['ID', 'No Kelompok', 'Fields', 'tarikh_mohon', 'admin_review_date', 'tarikh_agihan', 'created_at', 'updated_at'],
            array_map(fn (array $update): array => [
                $update['id'],
                $update['no_kelompok'],
                implode(', ', $update['fields']),
                $update['values']['tarikh_mohon'] ?? '-',
                $update['values']['admin_review_date'] ?? '-',
                $update['values']['tarikh_agihan'] ?? '-',
                $update['values']['created_at'] ?? '-',
                $update['values']['updated_at'] ?? '-',
            ], $pendingUpdates)
        );

        if ($this->option('dry-run')) {
            $this->warn('Dry run only. No database changes were written.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($pendingUpdates): void {
            foreach ($pendingUpdates as $update) {
                DB::table('permohonans')
                    ->where('id', $update['id'])
                    ->update($update['values']);
            }
        });

        $this->info('Updated '.$this->pluralRecord(count($pendingUpdates)).'.');

        return self::SUCCESS;
    }

    private function databaseIsReady(): bool
    {
        if (! Schema::hasTable('permohonans')) {
            $this->error('The permohonans table does not exist.');

            return false;
        }

        foreach (array_merge(['id', 'no_kelompok', 'jenis_bantuan', 'status', 'status_agihan'], self::DATE_COLUMNS) as $column) {
            if (! Schema::hasColumn('permohonans', $column)) {
                $this->error("The permohonans.{$column} column does not exist.");

                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function dummyDateSchedule(): array
    {
        $schedule = [];

        foreach (range(0, self::TARGET_COUNT - 1) as $index) {
            $jenisBantuan = self::JENIS_PLAN[$index % count(self::JENIS_PLAN)];
            $tarikhMohon = Carbon::create(2026, 7, 9, 9 + ($index % 7), 15, 0)->subDays($index);
            $adminReviewDate = $tarikhMohon->copy()->addDays(5)->setTime(11, 0);
            $noKelompok = sprintf('%s-2026-%04d', $this->permohonanPrefix($jenisBantuan), $index + 1);

            $schedule[$noKelompok] = [
                'index' => $index,
                'no_kelompok' => $noKelompok,
                'jenis_bantuan' => $jenisBantuan,
                'tarikh_mohon' => $tarikhMohon,
                'admin_review_date' => $adminReviewDate,
            ];
        }

        return $schedule;
    }

    /**
     * @param array<string, mixed> $plan
     * @return array<string, string|null>
     */
    private function expectedDateValues(stdClass $record, array $plan): array
    {
        /** @var CarbonInterface $tarikhMohon */
        $tarikhMohon = $plan['tarikh_mohon'];
        /** @var CarbonInterface|null $scheduledReviewDate */
        $scheduledReviewDate = $plan['admin_review_date'];

        $statusKey = Permohonan::normalizeStatus($record->status);
        $agihanKey = Permohonan::normalizeStatusAgihan($record->status_agihan);
        $hasAdminReview = in_array($statusKey, [Permohonan::STATUS_DILULUSKAN, Permohonan::STATUS_DITOLAK_GAGAL], true);
        $adminReviewDate = $hasAdminReview ? $scheduledReviewDate : null;
        $tarikhAgihan = null;

        if ($statusKey === Permohonan::STATUS_DILULUSKAN && $agihanKey === Permohonan::STATUS_AGIHAN_SELESAI && $adminReviewDate) {
            $tarikhAgihan = $adminReviewDate->copy()->addDays(4)->setTime(14, 30);
        }

        $updatedAt = $tarikhMohon->copy()->addHours(2);

        if ($adminReviewDate) {
            $updatedAt = $adminReviewDate->copy();
        }

        if ($statusKey === Permohonan::STATUS_DILULUSKAN && $agihanKey === Permohonan::STATUS_AGIHAN_SEDANG_DIAGIH && $adminReviewDate) {
            $updatedAt = $adminReviewDate->copy()->addDay()->setTime(10, 30);
        }

        if ($tarikhAgihan) {
            $updatedAt = $tarikhAgihan->copy();
        }

        return [
            'tarikh_mohon' => $tarikhMohon->toDateString(),
            'admin_review_date' => $this->formatDateTime($adminReviewDate),
            'tarikh_agihan' => $this->formatDateTime($tarikhAgihan),
            'created_at' => $this->formatDateTime($tarikhMohon),
            'updated_at' => $this->formatDateTime($updatedAt),
        ];
    }

    /**
     * @param array<string, mixed> $plan
     */
    private function matchesDummySeederShape(stdClass $record, array $plan): bool
    {
        return (string) $record->no_kelompok === $plan['no_kelompok']
            && (string) $record->jenis_bantuan === $plan['jenis_bantuan'];
    }

    /**
     * @param array<string, string|null> $expected
     * @return array<int, string>
     */
    private function changedFields(stdClass $record, array $expected): array
    {
        $changed = [];

        foreach ($expected as $field => $value) {
            $current = $field === 'tarikh_mohon'
                ? $this->formatDate($record->{$field})
                : $this->formatDateTime($record->{$field});

            if ($current !== $value) {
                $changed[] = $field;
            }
        }

        return $changed;
    }

    private function permohonanPrefix(string $jenisBantuan): string
    {
        return match ($jenisBantuan) {
            'bantuan_pembelajaran' => 'BPL',
            'bantuan_sukan' => 'BSK',
            'bantuan_musibah' => 'BMS',
            default => 'BAH',
        };
    }

    private function formatDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value)->toDateString();
    }

    private function formatDateTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    private function pluralRecord(int $count): string
    {
        return $count.' record'.($count === 1 ? '' : 's');
    }
}
