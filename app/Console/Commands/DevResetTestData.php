<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DevResetTestData extends Command
{
    protected $signature = 'dev:reset-test-data';

    protected $description = 'Reset local-only Pelajar and Penderma test data while keeping admin accounts and catalog data.';

    /**
     * WARNING: This command is for local development only.
     * Never use this command for production, staging, or shared real-user data.
     */
    public function handle(): int
    {
        if (! $this->isSafeDevelopmentEnvironment()) {
            $this->error('Refusing to reset data because APP_ENV is not local, development, or testing.');

            return self::FAILURE;
        }

        if (! Schema::hasTable('users')) {
            $this->error('The users table does not exist. Run migrations before using this local reset command.');

            return self::FAILURE;
        }

        $adminCountBefore = User::query()->where('role', 'admin')->count();

        if ($adminCountBefore < 1) {
            $this->error('Refusing to reset data because no admin account exists.');

            return self::FAILURE;
        }

        $this->warn('LOCAL DEVELOPMENT ONLY: clearing Pelajar/Penderma test records and safely referenced upload files.');
        $this->line('Admin accounts, migrations, routes, controllers, and item catalog data will be kept.');

        $counts = [];
        $files = [
            'public' => collect(),
            'local' => collect(),
        ];
        $skippedFiles = collect();

        DB::transaction(function () use (&$counts, &$files, &$skippedFiles): void {
            $pelajarUserIds = $this->idsFrom('users', fn (Builder $query) => $query->where('role', 'pelajar'));
            $pendermaUserIds = $this->idsFrom('users', fn (Builder $query) => $query->where('role', 'penderma'));
            $targetUserIds = $pelajarUserIds->merge($pendermaUserIds)->unique()->values();
            $targetEmails = $this->valuesFrom('users', 'email', fn (Builder $query) => $query->whereIn('id', $targetUserIds));

            $permohonanIds = $this->idsFrom('permohonans');
            $sumbanganIds = $this->idsFrom('sumbangans');
            $donorIds = $this->idsFrom('donors', fn (Builder $query) => $query->whereIn('user_id', $pendermaUserIds));

            $this->collectPublicFiles($files, $skippedFiles, 'permohonan_dokumens', 'file_path', fn (Builder $query) => $query->whereIn('permohonan_id', $permohonanIds));
            $this->collectLocalFiles($files, $skippedFiles, 'permohonans', 'bukti_agihan', fn (Builder $query) => $query->whereIn('id', $permohonanIds));
            $this->collectPublicFiles($files, $skippedFiles, 'physical_donations', 'image_path');
            $this->collectPublicFiles($files, $skippedFiles, 'donors', 'logo', fn (Builder $query) => $query->whereIn('id', $donorIds));
            $this->collectPublicFiles($files, $skippedFiles, 'donors', 'support_document', fn (Builder $query) => $query->whereIn('id', $donorIds));
            $this->collectPublicFiles($files, $skippedFiles, 'users', 'profile_photo_path', fn (Builder $query) => $query->whereIn('id', $targetUserIds));

            if (Schema::hasColumn('users', 'document_path')) {
                $this->collectPublicFiles($files, $skippedFiles, 'users', 'document_path', fn (Builder $query) => $query->whereIn('id', $targetUserIds));
            }

            $counts['password_reset_tokens'] = $this->deleteWhere('password_reset_tokens', fn (Builder $query) => $query->whereIn('email', $targetEmails));
            $counts['sessions'] = $this->deleteWhere('sessions', fn (Builder $query) => $query->whereIn('user_id', $targetUserIds));

            $counts['permohonan_dokumens'] = $this->deleteWhere('permohonan_dokumens', fn (Builder $query) => $query->whereIn('permohonan_id', $permohonanIds));
            $counts['permohonan_bantuans'] = $this->deleteWhere('permohonan_bantuans', fn (Builder $query) => $query->whereIn('permohonan_id', $permohonanIds));
            $counts['permohonan_keluarga'] = $this->deleteWhere('permohonan_keluarga', fn (Builder $query) => $query->whereIn('permohonan_id', $permohonanIds));
            $counts['permohonan_pelajar'] = $this->deleteWhere('permohonan_pelajar', fn (Builder $query) => $query->whereIn('permohonan_id', $permohonanIds));
            $counts['permohonans'] = $this->deleteWhere('permohonans', fn (Builder $query) => $query->whereIn('id', $permohonanIds));

            $counts['sumbangan_items'] = $this->deleteWhere('sumbangan_items', fn (Builder $query) => $query->whereIn('sumbangan_id', $sumbanganIds));
            $counts['sumbangans'] = $this->deleteWhere('sumbangans', fn (Builder $query) => $query->whereIn('id', $sumbanganIds));
            $counts['cash_donations'] = $this->deleteAll('cash_donations');
            $counts['physical_donations'] = $this->deleteAll('physical_donations');

            $counts['addresses'] = $this->deleteWhere('addresses', fn (Builder $query) => $query->whereIn('donor_id', $donorIds));
            $counts['donors'] = $this->deleteWhere('donors', fn (Builder $query) => $query->whereIn('id', $donorIds));
            $counts['pelajar_users'] = $this->deleteWhere('users', fn (Builder $query) => $query->where('role', 'pelajar'));
            $counts['penderma_users'] = $this->deleteWhere('users', fn (Builder $query) => $query->where('role', 'penderma'));
        });

        $deletedFiles = $this->deleteCollectedFiles($files);
        $adminCountAfter = User::query()->where('role', 'admin')->count();

        $this->newLine();
        $this->info('Database rows deleted:');
        $this->table(['Table / record type', 'Deleted'], collect($counts)->map(fn (int $count, string $label) => [$label, $count])->values()->all());

        $this->newLine();
        $this->info('Safely referenced uploaded files deleted:');
        $this->table(['Disk', 'Deleted'], [
            ['public', $deletedFiles['public']],
            ['local', $deletedFiles['local']],
        ]);

        if ($skippedFiles->isNotEmpty()) {
            $this->warn('Some referenced files were skipped because their paths were outside known upload folders.');
            $this->line('Skipped paths: '.$skippedFiles->unique()->implode(', '));
        }

        $this->newLine();
        $this->info("Admin accounts kept: {$adminCountAfter}");
        $this->line('Foreign key checks were not disabled; child records were deleted before parent records.');

        return $adminCountAfter >= $adminCountBefore ? self::SUCCESS : self::FAILURE;
    }

    private function isSafeDevelopmentEnvironment(): bool
    {
        return in_array(app()->environment(), ['local', 'development', 'testing'], true);
    }

    private function idsFrom(string $table, ?callable $scope = null): Collection
    {
        if (! Schema::hasTable($table)) {
            return collect();
        }

        return $this->valuesFrom($table, 'id', $scope)->map(fn ($id) => (int) $id);
    }

    private function valuesFrom(string $table, string $column, ?callable $scope = null): Collection
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return collect();
        }

        $query = DB::table($table)->whereNotNull($column);

        if ($scope) {
            $scope($query);
        }

        return $query->pluck($column)->filter()->unique()->values();
    }

    private function deleteAll(string $table): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        $count = DB::table($table)->count();
        DB::table($table)->delete();

        return $count;
    }

    private function deleteWhere(string $table, callable $scope): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        $query = DB::table($table);
        $scope($query);

        $count = (clone $query)->count();

        if ($count > 0) {
            $query->delete();
        }

        return $count;
    }

    private function collectPublicFiles(array &$files, Collection &$skippedFiles, string $table, string $column, ?callable $scope = null): void
    {
        $this->collectFiles($files, $skippedFiles, 'public', $table, $column, [
            'dokumen_permohonan/',
            'physical-donations/',
            'penderma-logo/',
            'donor-logos/',
            'donor-documents/',
            'profile-photos/',
        ], $scope);
    }

    private function collectLocalFiles(array &$files, Collection &$skippedFiles, string $table, string $column, ?callable $scope = null): void
    {
        $this->collectFiles($files, $skippedFiles, 'local', $table, $column, [
            'agihan-bukti/',
        ], $scope);
    }

    private function collectFiles(array &$files, Collection &$skippedFiles, string $disk, string $table, string $column, array $allowedPrefixes, ?callable $scope = null): void
    {
        $paths = $this->valuesFrom($table, $column, $scope);

        foreach ($paths as $path) {
            $safePath = $this->safeStoragePath((string) $path, $allowedPrefixes);

            if ($safePath === null) {
                $skippedFiles->push("{$disk}:{$path}");
                continue;
            }

            $files[$disk]->push($safePath);
        }
    }

    private function safeStoragePath(string $path, array $allowedPrefixes): ?string
    {
        $path = trim(str_replace('\\', '/', $path));

        if ($path === '' || preg_match('/^[a-z][a-z0-9+.-]*:\/\//i', $path) || preg_match('/^[a-z]:\//i', $path)) {
            return null;
        }

        foreach (['storage/app/public/', 'storage/app/', 'public/storage/', 'storage/', 'public/'] as $prefix) {
            $position = strpos($path, $prefix);

            if ($position !== false) {
                $path = substr($path, $position + strlen($prefix));
                break;
            }
        }

        $path = ltrim($path, '/');

        if ($path === '' || $path === '..' || str_contains($path, '../') || str_contains($path, '/..')) {
            return null;
        }

        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $path;
            }
        }

        return null;
    }

    private function deleteCollectedFiles(array $files): array
    {
        $deleted = [
            'public' => 0,
            'local' => 0,
        ];

        foreach ($deleted as $disk => $_) {
            $files[$disk]
                ->unique()
                ->values()
                ->each(function (string $path) use (&$deleted, $disk): void {
                    if (Storage::disk($disk)->exists($path)) {
                        Storage::disk($disk)->delete($path);
                        $deleted[$disk]++;
                    }
                });
        }

        return $deleted;
    }
}
