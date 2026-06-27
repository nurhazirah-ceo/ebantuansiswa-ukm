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

        Permohonan::firstOrCreate(
            ['no_kelompok' => 'KA-2026-001'],
            [
            'user_id' => $seedUserId,
            'no_kelompok' => 'KA-2026-001',
            'tarikh_mohon' => '2025-11-17',
            'jenis_bantuan' => 'Keperluan Asas',
            'status' => 'Sedang Disemak',
            'catatan' => 'Permohonan sedang disemak secara keseluruhan.',
            'pakej' => 'Pakej 3 Orang',
            'jumlah_ahli' => 3,
            ]
        );

        Permohonan::firstOrCreate(
            ['no_kelompok' => 'PB-2026-003'],
            [
            'user_id' => $seedUserId,
            'no_kelompok' => 'PB-2026-003',
            'tarikh_mohon' => '2025-11-19',
            'jenis_bantuan' => 'Pembelajaran',
            'status' => 'Diluluskan',
            'catatan' => 'Permohonan telah diluluskan.',
            'nama_group' => 'Kumpulan Bestari',
            'bilangan_ahli' => 5,
            ]
        );

        Permohonan::firstOrCreate(
            ['no_kelompok' => 'SK-2026-002'],
            [
            'user_id' => $seedUserId,
            'no_kelompok' => 'SK-2026-002',
            'tarikh_mohon' => '2025-11-20',
            'jenis_bantuan' => 'Peralatan Sukan',
            'status' => 'Ditolak',
            'catatan' => 'Dokumen sokongan tidak lengkap.',
            'kategori' => 'Kelab / Persatuan Berdaftar UKM',
            'organisasi' => 'Kelab Badminton UKM',
            ]
        );
    }
}
