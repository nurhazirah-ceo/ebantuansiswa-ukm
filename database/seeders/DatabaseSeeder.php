<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permohonan;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ItemSeeder::class);

        $seedUserId = User::query()->whereKey(17)->value('id')
            ?? User::query()->value('id');

        if (! $seedUserId) {
            return;
        }

        $permohonan = Permohonan::updateOrCreate(
            ['no_kelompok' => 'KA-2026-001'],
            [
            'user_id' => $seedUserId,
            'no_kelompok' => 'KA-2026-001',
            'tarikh_mohon' => '2026-07-08',
            'jenis_bantuan' => 'Keperluan Asas',
            'status' => 'Sedang Disemak',
            'status_agihan' => null,
            'tarikh_agihan' => null,
            'catatan' => 'Permohonan sedang disemak secara keseluruhan.',
            'admin_review_date' => null,
            'pakej' => 'Pakej 3 Orang',
            'jumlah_ahli' => 3,
            ]
        );
        Permohonan::withoutTimestamps(function () use ($permohonan): void {
            $permohonan->forceFill([
                'created_at' => '2026-07-08 09:20:00',
                'updated_at' => '2026-07-08 09:40:00',
            ])->save();
        });

        $permohonan = Permohonan::updateOrCreate(
            ['no_kelompok' => 'PB-2026-003'],
            [
            'user_id' => $seedUserId,
            'no_kelompok' => 'PB-2026-003',
            'tarikh_mohon' => '2026-07-04',
            'jenis_bantuan' => 'Pembelajaran',
            'status' => 'Diluluskan',
            'status_agihan' => null,
            'tarikh_agihan' => null,
            'catatan' => 'Permohonan telah diluluskan.',
            'admin_review_date' => '2026-07-09 11:00:00',
            'nama_group' => 'Kumpulan Bestari',
            'bilangan_ahli' => 5,
            ]
        );
        Permohonan::withoutTimestamps(function () use ($permohonan): void {
            $permohonan->forceFill([
                'created_at' => '2026-07-04 10:10:00',
                'updated_at' => '2026-07-09 11:00:00',
            ])->save();
        });

        $permohonan = Permohonan::updateOrCreate(
            ['no_kelompok' => 'SK-2026-002'],
            [
            'user_id' => $seedUserId,
            'no_kelompok' => 'SK-2026-002',
            'tarikh_mohon' => '2026-07-05',
            'jenis_bantuan' => 'Peralatan Sukan',
            'status' => 'Ditolak',
            'status_agihan' => null,
            'tarikh_agihan' => null,
            'catatan' => 'Dokumen sokongan tidak lengkap.',
            'admin_review_date' => '2026-07-10 11:00:00',
            'kategori' => 'Kelab / Persatuan Berdaftar UKM',
            'organisasi' => 'Kelab Badminton UKM',
            ]
        );
        Permohonan::withoutTimestamps(function () use ($permohonan): void {
            $permohonan->forceFill([
                'created_at' => '2026-07-05 14:05:00',
                'updated_at' => '2026-07-10 11:00:00',
            ])->save();
        });
    }
}
