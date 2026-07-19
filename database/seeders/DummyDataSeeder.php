<?php

namespace Database\Seeders;

use App\Models\CashDonation;
use App\Models\Donor;
use App\Models\Item;
use App\Models\Permohonan;
use App\Models\PermohonanBantuan;
use App\Models\PermohonanDokumen;
use App\Models\PermohonanKeluarga;
use App\Models\PermohonanPelajar;
use App\Models\Sumbangan;
use App\Models\SumbanganItem;
use App\Models\User;
use App\Support\StudentAcademicProfile;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DummyDataSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'Password123!';

    public function run(): void
    {
        $admin = User::query()
            ->whereKey(1)
            ->where('role', 'admin')
            ->first()
            ?? User::query()->where('role', 'admin')->first();

        if (! $admin) {
            throw new RuntimeException('Admin user is required before dummy data can be seeded.');
        }

        if ($this->hasExistingTransactionalData()) {
            $this->command?->warn('Transactional data already exists. DummyDataSeeder aborted without deleting or duplicating data.');

            return;
        }

        $proofPaths = [];

        DB::transaction(function () use ($admin, &$proofPaths): void {
            $students = $this->createStudentUsers();
            $donors = $this->createDonorUsers();

            $proofPaths = $this->createApplications($students, $admin);
            $this->createCashDonations($donors);
            $this->createItemDonations($donors);
        });

        $this->writeAgihanProofPlaceholders($proofPaths);

        $this->command?->info('Dummy data generated successfully. Default dummy password: ' . self::DEFAULT_PASSWORD);
    }

    private function hasExistingTransactionalData(): bool
    {
        return User::query()->whereIn('role', ['pelajar', 'penderma'])->exists()
            || Donor::query()->exists()
            || Permohonan::query()->exists()
            || CashDonation::query()->exists()
            || Sumbangan::query()->exists()
            || DB::table('physical_donations')->exists();
    }

    /**
     * @return array<int, array{user: User, matrik: string, faculty: string, year: string}>
     */
    private function createStudentUsers(): array
    {
        $names = [
            'Nur Aisyah Mohd Zaki',
            'Muhammad Amirul Hakim Roslan',
            'Siti Nur Balqis Ahmad',
            'Ahmad Farhan Abdullah',
            'Nurul Huda Azmi',
            'Mohd Danish Irfan',
            'Farah Nabilah Ismail',
            'Aiman Hakimi Saiful',
            'Nadia Syahirah Rahman',
            'Muhammad Izzat Haziq',
            'Nur Imanina Yusof',
            'Amir Hakim Jamaluddin',
            'Syarifah Alya Sofea',
            'Muhammad Hafiz Hilmi',
            'Wan Nur Aqilah',
            'Syafiq Danish Zulkifli',
            'Nur Sabrina Kamarul',
            'Muhammad Afiq Firdaus',
            'Aina Batrisyia Ridzuan',
            'Mohd Luqman Hakim',
            'Nur Amirah Sofea',
            'Muhammad Syazwan Naim',
            'Puteri Nur Qistina',
            'Irfan Harith Azman',
            'Nur Fatin Najwa',
            'Muhammad Faiz Ikhwan',
            'Dayang Nur Adlina',
            'Mohd Alif Haziq',
            'Nurul Ain Farhana',
            'Muhammad Syafiq Danial',
            'Alya Maisarah Azmi',
            'Muhammad Harith Iman',
            'Nur Syazwani Zainal',
            'Mohd Hakimi Hafiz',
            'Nurin Aleesya Rahim',
            'Muhammad Adam Danish',
            'Nur Anis Irdina',
            'Farhan Haziq Baharudin',
            'Siti Hajar Maisarah',
            'Muhammad Rayyan Aqil',
        ];

        $faculties = StudentAcademicProfile::faculties();

        $students = [];

        foreach ($names as $index => $name) {
            $matrik = 'A' . (197001 + $index);
            $faculty = $faculties[$index % count($faculties)];
            $year = 'Tahun ' . (($index % 4) + 1);

            $user = User::query()->create([
                'name' => $name,
                'matrik' => $matrik,
                'fakulti' => $faculty,
                'email' => strtolower($matrik) . '@siswa.ukm.edu.my',
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'role' => 'pelajar',
                'account_status' => 'active',
            ]);

            $user->forceFill([
                'email_verified_at' => Carbon::parse('2026-01-03 09:00:00')->addDays($index),
            ])->save();

            $students[] = [
                'user' => $user,
                'matrik' => $matrik,
                'faculty' => $faculty,
                'year' => $year,
            ];
        }

        return $students;
    }

    /**
     * @return array<int, array{user: User, donor: Donor, logo: string|null}>
     */
    private function createDonorUsers(): array
    {
        $donorProfiles = [
            [
                'name' => 'AEON Co. (M) Bhd',
                'email' => 'csr.aeon@dummy.ebantuansiswa.test',
                'type' => 'syarikat',
                'representative' => 'Puan Noraini Ahmad',
                'phone' => '0129207800',
                'logo' => 'penderma-logo/aeon.jpg',
                'address' => ['Menara AEON, Jalan Dato Onn', 'Kuala Lumpur', '50480', 'Wilayah Persekutuan Kuala Lumpur'],
            ],
            [
                'name' => 'Hup Seng Industries Berhad',
                'email' => 'komuniti.hupseng@dummy.ebantuansiswa.test',
                'type' => 'syarikat',
                'representative' => 'Encik Lim Han Wei',
                'phone' => '0174321321',
                'logo' => 'penderma-logo/hupseng.jpg',
                'address' => ['Lot 14, Kawasan Perindustrian Tongkang Pecah', 'Batu Pahat', '83010', 'Johor'],
            ],
            [
                'name' => 'Malayan Banking Berhad',
                'email' => 'maybank.foundation@dummy.ebantuansiswa.test',
                'type' => 'syarikat',
                'representative' => 'Puan Siti Mariam Hassan',
                'phone' => '0132070883',
                'logo' => 'penderma-logo/maybank.jpg',
                'address' => ['Menara Maybank, 100 Jalan Tun Perak', 'Kuala Lumpur', '50050', 'Wilayah Persekutuan Kuala Lumpur'],
            ],
            [
                'name' => 'MR D.I.Y. Group',
                'email' => 'community.mrdiy@dummy.ebantuansiswa.test',
                'type' => 'syarikat',
                'representative' => 'Encik Azhar Rosli',
                'phone' => '0189611338',
                'logo' => 'penderma-logo/mrdiy.jpg',
                'address' => ['Jalan SB Jaya, Taman Industri SB Jaya', 'Seri Kembangan', '43300', 'Selangor'],
            ],
            [
                'name' => 'Mydin Mohamed Holdings Berhad',
                'email' => 'csr.mydin@dummy.ebantuansiswa.test',
                'type' => 'syarikat',
                'representative' => 'Puan Khairunnisa Abdul Rahman',
                'phone' => '0198073600',
                'logo' => 'penderma-logo/mydin.jpg',
                'address' => ['Persiaran Subang Permai, USJ 1', 'Subang Jaya', '47500', 'Selangor'],
            ],
            [
                'name' => 'Nestle (Malaysia) Berhad',
                'email' => 'nestlecares@dummy.ebantuansiswa.test',
                'type' => 'syarikat',
                'representative' => 'Encik Mohd Hafiz Rahim',
                'phone' => '01139656000',
                'logo' => 'penderma-logo/nestle.jpg',
                'address' => ['Surian Tower, Mutiara Damansara', 'Petaling Jaya', '47810', 'Selangor'],
            ],
            [
                'name' => 'Sime Darby Foundation',
                'email' => 'yayasan.simedarby@dummy.ebantuansiswa.test',
                'type' => 'syarikat',
                'representative' => 'Puan Marina Yusof',
                'phone' => '0127623200',
                'logo' => 'penderma-logo/simedarby.jpg',
                'address' => ['Menara Sime Darby, Oasis Corporate Park', 'Petaling Jaya', '47301', 'Selangor'],
            ],
            [
                'name' => 'SLB Malaysia',
                'email' => 'slb.malaysia.community@dummy.ebantuansiswa.test',
                'type' => 'syarikat',
                'representative' => 'Encik Faizal Idris',
                'phone' => '0132166000',
                'logo' => 'penderma-logo/SLB.png',
                'address' => ['The Intermark, Jalan Tun Razak', 'Kuala Lumpur', '50400', 'Wilayah Persekutuan Kuala Lumpur'],
            ],
            [
                'name' => '99 Speed Mart Sdn Bhd',
                'email' => 'csr.speedmart@dummy.ebantuansiswa.test',
                'type' => 'syarikat',
                'representative' => 'Puan Lee Siew Mei',
                'phone' => '0163362799',
                'logo' => 'penderma-logo/speedmart.jpg',
                'address' => ['Lot PT 2811, Jalan Angsa', 'Klang', '41150', 'Selangor'],
            ],
            [
                'name' => 'Persatuan Alumni UKM Prihatin',
                'email' => 'alumni.prihatin@dummy.ebantuansiswa.test',
                'type' => 'ngo',
                'representative' => 'Dr. Mohd Rizal Ibrahim',
                'phone' => '0122104568',
                'logo' => null,
                'address' => ['Kompleks Alumni UKM, Lingkungan Ilmu', 'Bangi', '43600', 'Selangor'],
            ],
            [
                'name' => 'Yayasan Ihsan Bangi',
                'email' => 'sekretariat@yib-dummy.test',
                'type' => 'ngo',
                'representative' => 'Puan Haslina Othman',
                'phone' => '0193345901',
                'logo' => null,
                'address' => ['No. 22, Jalan 7/7B, Seksyen 7', 'Bandar Baru Bangi', '43650', 'Selangor'],
            ],
            [
                'name' => 'Kelab Rotary Kajang',
                'email' => 'rotary.kajang@dummy.ebantuansiswa.test',
                'type' => 'ngo',
                'representative' => 'Encik Ravi Subramaniam',
                'phone' => '0122887742',
                'logo' => null,
                'address' => ['No. 18, Jalan Reko Sentral 2', 'Kajang', '43000', 'Selangor'],
            ],
            [
                'name' => 'Encik Azlan Zainal',
                'email' => 'azlan.zainal@dummy.ebantuansiswa.test',
                'type' => 'individu',
                'representative' => 'Encik Azlan Zainal',
                'phone' => '0123987654',
                'logo' => null,
                'address' => ['No. 8, Jalan Cempaka 3, Taman Desa Saujana', 'Kajang', '43000', 'Selangor'],
            ],
            [
                'name' => 'Puan Siti Rahimah Ismail',
                'email' => 'siti.rahimah@dummy.ebantuansiswa.test',
                'type' => 'individu',
                'representative' => 'Puan Siti Rahimah Ismail',
                'phone' => '0177451230',
                'logo' => null,
                'address' => ['B-12-07, Pangsapuri Ilmu, Jalan 2/3A', 'Bangi', '43600', 'Selangor'],
            ],
            [
                'name' => 'Dr. Faridah Omar',
                'email' => 'faridah.omar@dummy.ebantuansiswa.test',
                'type' => 'individu',
                'representative' => 'Dr. Faridah Omar',
                'phone' => '0199881204',
                'logo' => null,
                'address' => ['No. 41, Jalan Setia Tropika 4/8', 'Johor Bahru', '81200', 'Johor'],
            ],
        ];

        $donors = [];

        foreach ($donorProfiles as $index => $profile) {
            $user = User::query()->create([
                'name' => $profile['name'],
                'matrik' => null,
                'email' => $profile['email'],
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'role' => 'penderma',
                'account_status' => 'active',
            ]);

            $user->forceFill([
                'email_verified_at' => Carbon::parse('2026-01-05 10:00:00')->addDays($index),
            ])->save();

            $donor = Donor::query()->create([
                'user_id' => $user->id,
                'donor_type' => $profile['type'],
                'representative_name' => $profile['representative'],
                'phone' => $profile['phone'],
                'preferred_contact' => $index % 3 === 0 ? 'phone' : 'email',
                'admin_note' => 'Profil dummy untuk demonstrasi data penderma 2026.',
                'logo' => $profile['logo'],
                'homepage_label' => $profile['name'],
                'homepage_order' => $index + 1,
                'show_on_homepage' => $index < 12,
            ]);

            $donor->address()->create([
                'address_line_1' => $profile['address'][0],
                'address_line_2' => null,
                'city' => $profile['address'][1],
                'postcode' => $profile['address'][2],
                'state' => $profile['address'][3],
                'country' => 'Malaysia',
            ]);

            $donors[] = [
                'user' => $user,
                'donor' => $donor,
                'logo' => $profile['logo'],
            ];
        }

        return $donors;
    }

    /**
     * @param array<int, array{user: User, matrik: string, faculty: string, year: string}> $students
     * @return array<int, string>
     */
    private function createApplications(array $students, User $admin): array
    {
        $jenisPlan = [
            'bantuan_asas_hidup',
            'bantuan_pembelajaran',
            'bantuan_sukan',
            'bantuan_musibah',
        ];

        $statusPlan = array_merge(
            array_fill(0, 21, 'Diluluskan'),
            array_fill(0, 8, 'Dalam Semakan'),
            ['Ditolak', 'Gagal', 'Ditolak', 'Gagal', 'Ditolak', 'Gagal']
        );

        $agihanPlan = array_merge(
            array_fill(0, 7, Permohonan::STATUS_AGIHAN_BELUM_DIAGIH),
            array_fill(0, 8, Permohonan::STATUS_AGIHAN_SEDANG_DIAGIH),
            array_fill(0, 6, Permohonan::STATUS_AGIHAN_SELESAI)
        );

        $guardianNames = [
            'Mohd Zaki Ahmad',
            'Roslan Ismail',
            'Aminah Salleh',
            'Abdullah Rahman',
            'Azmi Yusof',
            'Noraini Hassan',
            'Kamarul Baharin',
            'Zulkifli Omar',
        ];

        $proofPaths = [];
        $approvedIndex = 0;

        foreach (range(0, 34) as $index) {
            $student = $students[$index % count($students)];
            $jenisBantuan = $jenisPlan[$index % count($jenisPlan)];
            $status = $statusPlan[$index];
            $tarikhMohon = Carbon::create(2026, 7, 9, 9 + ($index % 7), 15, 0)->subDays($index);
            $adminReviewDate = in_array($status, ['Diluluskan', 'Ditolak', 'Gagal'], true)
                ? $tarikhMohon->copy()->addDays(5)->setTime(11, 0)
                : null;
            $lastActivityAt = $adminReviewDate?->copy() ?? $tarikhMohon->copy()->addHours(2);
            $kategoriBantuan = $this->kategoriForJenisBantuan($jenisBantuan, $index);
            $prefix = $this->permohonanPrefix($jenisBantuan);

            $agihanStatus = null;
            $tarikhAgihan = null;
            $catatanAgihan = null;
            $buktiAgihan = null;
            $diagihOleh = null;

            if ($status === 'Diluluskan') {
                $agihanStatus = $agihanPlan[$approvedIndex];
                $diagihOleh = $agihanStatus === Permohonan::STATUS_AGIHAN_BELUM_DIAGIH ? null : $admin->id;

                if ($agihanStatus === Permohonan::STATUS_AGIHAN_SEDANG_DIAGIH) {
                    $catatanAgihan = 'Agihan sedang diselaraskan bersama kolej kediaman.';
                    $lastActivityAt = $adminReviewDate->copy()->addDay()->setTime(10, 30);
                }

                if ($agihanStatus === Permohonan::STATUS_AGIHAN_SELESAI) {
                    $tarikhAgihan = $adminReviewDate->copy()->addDays(4)->setTime(14, 30);
                    $catatanAgihan = 'Bantuan telah diserahkan kepada pelajar dan direkodkan sebagai data dummy.';
                    $buktiAgihan = sprintf('agihan-bukti/dummy-agihan-%04d.pdf', $approvedIndex + 1);
                    $proofPaths[] = $buktiAgihan;
                    $lastActivityAt = $tarikhAgihan->copy();
                }

                $approvedIndex++;
            }

            $permohonan = Permohonan::query()->create([
                'user_id' => $student['user']->id,
                'no_kelompok' => sprintf('%s-2026-%04d', $prefix, $index + 1),
                'tarikh_mohon' => $tarikhMohon->toDateString(),
                'jenis_bantuan' => $jenisBantuan,
                'status' => $status,
                'status_agihan' => $agihanStatus,
                'tarikh_agihan' => $tarikhAgihan,
                'catatan_agihan' => $catatanAgihan,
                'bukti_agihan' => $buktiAgihan,
                'diagih_oleh' => $diagihOleh,
                'catatan' => $this->applicationNote($jenisBantuan),
                'admin_catatan' => $status === 'Ditolak' || $status === 'Gagal'
                    ? 'Dokumen sokongan atau justifikasi tidak mencukupi untuk kelulusan.'
                    : ($status === 'Diluluskan' ? 'Permohonan memenuhi kriteria bantuan pelajar.' : null),
                'admin_review_date' => $adminReviewDate,
                'pakej' => $jenisBantuan === 'bantuan_asas_hidup' ? ['Pakej 1 Orang', 'Pakej 3 Orang', 'Pakej 5 Orang'][$index % 3] : null,
                'jumlah_ahli' => $jenisBantuan === 'bantuan_asas_hidup' ? [1, 3, 5][$index % 3] : null,
                'nama_group' => $jenisBantuan === 'bantuan_pembelajaran' ? 'Kumpulan Tutorial ' . chr(65 + ($index % 6)) : null,
                'bilangan_ahli' => $jenisBantuan === 'bantuan_pembelajaran' ? 2 + ($index % 4) : null,
                'kategori' => $jenisBantuan === 'bantuan_sukan' ? ['Kelab Sukan UKM', 'Kolej Kediaman', 'Pasukan Fakulti'][$index % 3] : null,
                'organisasi' => $jenisBantuan === 'bantuan_sukan' ? ['Kelab Badminton UKM', 'Kolej Pendeta Zaaba', 'Fakulti Sains Kesihatan'][$index % 3] : null,
            ]);

            Permohonan::withoutTimestamps(function () use ($permohonan, $tarikhMohon, $lastActivityAt): void {
                $permohonan->forceFill([
                    'created_at' => $tarikhMohon,
                    'updated_at' => $lastActivityAt,
                ])->save();
            });

            PermohonanPelajar::query()->create([
                'permohonan_id' => $permohonan->id,
                'nama_penuh' => $student['user']->name,
                'no_matrik' => $student['matrik'],
                'email_ukm' => $student['user']->email,
                'no_telefon' => '+601' . (($index % 8) + 1) . str_pad((string) (2300000 + ($index * 1379)), 7, '0', STR_PAD_LEFT),
                'fakulti' => $student['faculty'],
                'tahun_pengajian' => StudentAcademicProfile::academicSession(),
                'created_at' => $tarikhMohon,
                'updated_at' => $tarikhMohon,
            ]);

            PermohonanBantuan::query()->create([
                'permohonan_id' => $permohonan->id,
                'jenis_bantuan' => $jenisBantuan,
                'kategori_bantuan' => $kategoriBantuan,
                'data' => [
                    'fakulti' => $student['faculty'],
                    'sesi_akademik' => StudentAcademicProfile::academicSession(),
                    'anggaran_kos' => $this->estimatedAidCost($jenisBantuan, $index),
                    'justifikasi' => $this->applicationNote($jenisBantuan),
                ],
                'created_at' => $tarikhMohon,
                'updated_at' => $tarikhMohon,
            ]);

            PermohonanKeluarga::query()->create([
                'permohonan_id' => $permohonan->id,
                'jenis' => 'penjaga',
                'nama' => $guardianNames[$index % count($guardianNames)],
                'no_kp' => '7' . str_pad((string) (100101100000 + $index), 11, '0', STR_PAD_LEFT),
                'hubungan' => $index % 2 === 0 ? 'Bapa' : 'Ibu',
                'telefon' => '+601' . (($index % 7) + 2) . str_pad((string) (4100000 + ($index * 777)), 7, '0', STR_PAD_LEFT),
                'pekerjaan' => ['Peniaga kecil', 'Pembantu kedai', 'Pemandu e-hailing', 'Kerani', 'Tidak bekerja'][$index % 5],
                'umur' => 42 + ($index % 15),
                'status' => 'Berkahwin',
                'kesihatan' => $index % 9 === 0 ? 'Menghidap penyakit kronik' : 'Baik',
                'pendapatan' => [900, 1200, 1500, 1800, 2200][$index % 5],
                'created_at' => $tarikhMohon,
                'updated_at' => $tarikhMohon,
            ]);

            PermohonanKeluarga::query()->create([
                'permohonan_id' => $permohonan->id,
                'jenis' => 'tanggungan',
                'nama' => ['Nur Qaseh', 'Muhammad Rafiq', 'Siti Sarah', 'Aina Humaira'][$index % 4] . ' ' . ($index + 1),
                'no_kp' => null,
                'hubungan' => ['Adik', 'Kakak', 'Abang'][$index % 3],
                'telefon' => null,
                'pekerjaan' => $index % 3 === 0 ? 'Pelajar sekolah' : 'Pelajar',
                'umur' => 8 + ($index % 12),
                'status' => 'Tanggungan',
                'kesihatan' => 'Baik',
                'pendapatan' => 0,
                'created_at' => $tarikhMohon,
                'updated_at' => $tarikhMohon,
            ]);

            if ($index % 3 === 0) {
                PermohonanKeluarga::query()->create([
                    'permohonan_id' => $permohonan->id,
                    'jenis' => 'tanggungan',
                    'nama' => ['Muhammad Adam', 'Nur Damia', 'Izz Amir', 'Sofia Imani'][$index % 4] . ' ' . ($index + 2),
                    'no_kp' => null,
                    'hubungan' => 'Adik',
                    'telefon' => null,
                    'pekerjaan' => 'Pelajar sekolah',
                    'umur' => 6 + ($index % 10),
                    'status' => 'Tanggungan',
                    'kesihatan' => $index % 12 === 0 ? 'Memerlukan rawatan susulan' : 'Baik',
                    'pendapatan' => 0,
                    'created_at' => $tarikhMohon,
                    'updated_at' => $tarikhMohon,
                ]);
            }

            $this->createApplicationDocuments($permohonan, $jenisBantuan, $tarikhMohon, $index);
        }

        return $proofPaths;
    }

    /**
     * @param array<int, array{user: User, donor: Donor, logo: string|null}> $donors
     */
    private function createCashDonations(array $donors): void
    {
        $successfulByMonth = [
            1 => [500, 350],
            2 => [700, 500],
            3 => [400, 250],
            4 => [1500, 800],
            5 => [1000, 750],
            6 => [3000, 2050],
            7 => [2000, 1400],
            8 => [500, 400],
            9 => [1800, 1000],
            10 => [1000, 500],
            11 => [2500, 1700],
            12 => [1200, 900],
        ];

        $sequence = 1;

        foreach ($successfulByMonth as $month => $amounts) {
            foreach ($amounts as $offset => $amount) {
                $date = $this->correctedSuccessfulCashDonationDate(
                    $sequence,
                    Carbon::create(2026, $month, 6 + ($offset * 11), 10 + $offset, 20, 0)
                );
                $donor = $donors[($sequence + $month) % count($donors)]['user'];

                $cashDonation = CashDonation::query()->create([
                    'user_id' => $donor->id,
                    'amount' => $amount,
                    'message' => 'Sumbangan dummy Tabung Bantuan Pelajar UKM.',
                    'bill_code' => sprintf('DUMMY-TAB-%04d', $sequence),
                    'transaction_id' => sprintf('TPD-2026-%06d', $sequence),
                    'payment_status' => CashDonation::STATUS_SUCCESS,
                    'paid_at' => $date,
                    'resolved_at' => $date,
                    'raw_response' => [
                        'dummy' => true,
                        'environment' => 'test',
                        'status' => 'success',
                        'bill_code' => sprintf('DUMMY-TAB-%04d', $sequence),
                    ],
                    'created_at' => $date->copy()->subMinutes(12),
                    'updated_at' => $date,
                ]);

                $this->backfillCashDonationReferenceNo($cashDonation);

                $sequence++;
            }
        }

        $failedDonations = [
            [3, 22, 300],
            [5, 27, 120],
            [7, 18, 600],
            [9, 21, 450],
            [11, 16, 800],
            [12, 20, 250],
        ];

        foreach ($failedDonations as $failedIndex => [$month, $day, $amount]) {
            $failedSequence = $failedIndex + 1;
            $date = $this->correctedFailedCashDonationDate(
                $failedSequence,
                Carbon::create(2026, $month, $day, 16, 5, 0)
            );
            $donor = $donors[($failedIndex * 2) % count($donors)]['user'];

            $cashDonation = CashDonation::query()->create([
                'user_id' => $donor->id,
                'amount' => $amount,
                'message' => 'Transaksi dummy gagal untuk paparan status pembayaran.',
                'bill_code' => sprintf('DUMMY-TAB-FAILED-%02d', $failedSequence),
                'transaction_id' => sprintf('TPD-FAILED-2026-%03d', $failedSequence),
                'payment_status' => CashDonation::STATUS_FAILED,
                'paid_at' => null,
                'resolved_at' => $date->copy()->addMinutes(20),
                'raw_response' => [
                    'dummy' => true,
                    'environment' => 'test',
                    'status' => 'failed',
                    'reason' => 'Simulasi pembayaran tidak berjaya.',
                ],
                'created_at' => $date,
                'updated_at' => $date->copy()->addMinutes(20),
            ]);

            $this->backfillCashDonationReferenceNo($cashDonation);
        }
    }

    private function backfillCashDonationReferenceNo(CashDonation $cashDonation): void
    {
        DB::table('cash_donations')
            ->where('id', $cashDonation->id)
            ->whereNull('reference_no')
            ->update([
                'reference_no' => sprintf(
                    'TAB/%s/%06d',
                    ($cashDonation->created_at ?? now())->format('Ymd'),
                    $cashDonation->id
                ),
            ]);
    }

    private function correctedSuccessfulCashDonationDate(int $sequence, Carbon $date): Carbon
    {
        $correctedDates = [
            15 => [2026, 1, 24, 10, 20, 0],
            16 => [2026, 1, 29, 11, 20, 0],
            17 => [2026, 2, 24, 10, 20, 0],
            18 => [2026, 2, 27, 11, 20, 0],
            19 => [2026, 3, 24, 10, 20, 0],
            20 => [2026, 4, 24, 11, 20, 0],
            21 => [2026, 5, 24, 10, 20, 0],
            22 => [2026, 6, 24, 11, 20, 0],
            23 => [2026, 7, 4, 10, 20, 0],
            24 => [2026, 7, 12, 11, 20, 0],
        ];

        return isset($correctedDates[$sequence])
            ? Carbon::create(...$correctedDates[$sequence])
            : $date;
    }

    private function correctedFailedCashDonationDate(int $sequence, Carbon $date): Carbon
    {
        $correctedDates = [
            4 => [2026, 4, 28, 16, 5, 0],
            5 => [2026, 6, 28, 16, 5, 0],
            6 => [2026, 7, 14, 16, 5, 0],
        ];

        return isset($correctedDates[$sequence])
            ? Carbon::create(...$correctedDates[$sequence])
            : $date;
    }

    /**
     * @param array<int, array{user: User, donor: Donor, logo: string|null}> $donors
     */
    private function createItemDonations(array $donors): void
    {
        $donationPlans = [
            [1, 7, true, [[1, 30], [8, 80]]],
            [1, 19, true, [[14, 12]]],
            [2, 8, true, [[2, 18], [6, 60]]],
            [2, 22, true, [[15, 6], [16, 8]]],
            [3, 9, false, [[13, 2]]],
            [3, 25, true, [[3, 10], [10, 40]]],
            [4, 5, true, [[12, 3]]],
            [4, 20, true, [[17, 5], [18, 4]]],
            [5, 6, false, [[5, 7]]],
            [5, 24, true, [[7, 75], [9, 35]]],
            [6, 4, true, [[4, 8], [1, 20]]],
            [6, 14, true, [[13, 4], [14, 15]]],
            [6, 28, true, [[15, 4], [21, 30]]],
            [7, 7, true, [[2, 16], [8, 90]]],
            [7, 19, true, [[12, 2]]],
            [8, 11, false, [[18, 5], [20, 10]]],
            [8, 26, true, [[6, 120], [10, 50]]],
            [9, 10, true, [[5, 6], [3, 12]]],
            [9, 22, true, [[17, 4], [19, 5]]],
            [10, 8, false, [[13, 3]]],
            [10, 24, true, [[14, 18], [11, 45]]],
            [11, 6, true, [[1, 25], [2, 15]]],
            [11, 23, true, [[15, 4], [16, 6], [21, 20]]],
            [12, 7, true, [[12, 2], [13, 3]]],
            [12, 18, false, [[4, 5], [20, 12]]],
        ];

        foreach ($donationPlans as $index => [$month, $day, $completed, $items]) {
            $date = Carbon::create(2026, $month, $day, 12 + ($index % 5), 10, 0);
            $status = $completed ? Sumbangan::STATUS_SELESAI : 'dibatalkan';
            $paymentStatus = $completed ? 'success' : 'failed';
            $donor = $donors[$index % count($donors)]['user'];

            $itemRows = [];
            $totalUnits = 0;
            $totalAmount = 0.0;

            foreach ($items as [$itemId, $quantity]) {
                $item = Item::query()->findOrFail($itemId);
                $lineTotal = round((float) $item->harga * $quantity, 2);

                $itemRows[] = [
                    'item' => $item,
                    'quantity' => $quantity,
                    'total' => $lineTotal,
                ];

                $totalUnits += $quantity;
                $totalAmount += $lineTotal;
            }

            $sumbangan = Sumbangan::query()->create([
                'user_id' => $donor->id,
                'no_sumbangan' => sprintf('DUMMY-SMB-2026-%04d', $index + 1),
                'jumlah_unit' => $totalUnits,
                'jumlah_keseluruhan' => $totalAmount,
                'status' => $status,
                'kaedah_sumbangan' => 'simulasi',
                'catatan' => $completed
                    ? 'Sumbangan barang dummy telah disahkan selesai.'
                    : 'Sumbangan barang dummy gagal untuk paparan status.',
                'toyyibpay_bill_code' => sprintf('DUMMY-SMB-BILL-%04d', $index + 1),
                'payment_reference' => sprintf('DUMMY-SMB-REF-%04d', $index + 1),
                'payment_status' => $paymentStatus,
                'payment_payload' => [
                    'dummy' => true,
                    'environment' => 'test',
                    'status' => $paymentStatus,
                ],
                'paid_at' => $completed ? $date : null,
                'cancelled_at' => $completed ? null : $date->copy()->addMinutes(25),
                'created_at' => $date->copy()->subMinutes(15),
                'updated_at' => $completed ? $date : $date->copy()->addMinutes(25),
            ]);

            foreach ($itemRows as $row) {
                $item = $row['item'];

                SumbanganItem::query()->create([
                    'sumbangan_id' => $sumbangan->id,
                    'item_id' => $item->id,
                    'nama_item' => $item->nama_item,
                    'kategori_bantuan' => $item->kategori_bantuan,
                    'harga_unit' => $item->harga,
                    'kuantiti' => $row['quantity'],
                    'jumlah' => $row['total'],
                    'created_at' => $sumbangan->created_at,
                    'updated_at' => $sumbangan->updated_at,
                ]);

                if ($completed) {
                    $item->increment('stok_disumbang', $row['quantity']);
                }
            }
        }
    }

    private function createApplicationDocuments(Permohonan $permohonan, string $jenisBantuan, Carbon $date, int $index): void
    {
        $documents = [
            [
                'jenis_dokumen' => 'Kad Matrik',
                'file_path' => sprintf('dokumen_permohonan/dummy/%s/kad-matrik.pdf', strtolower($permohonan->no_kelompok)),
            ],
        ];

        if ($index % 2 === 0) {
            $documents[] = [
                'jenis_dokumen' => 'Penyata Pendapatan Keluarga',
                'file_path' => sprintf('dokumen_permohonan/dummy/%s/penyata-pendapatan.pdf', strtolower($permohonan->no_kelompok)),
            ];
        }

        if ($jenisBantuan === 'bantuan_musibah') {
            $documents[] = [
                'jenis_dokumen' => 'Laporan Musibah',
                'file_path' => sprintf('dokumen_permohonan/dummy/%s/laporan-musibah.pdf', strtolower($permohonan->no_kelompok)),
            ];
        }

        foreach ($documents as $document) {
            PermohonanDokumen::query()->create([
                'permohonan_id' => $permohonan->id,
                'jenis_dokumen' => $document['jenis_dokumen'],
                'file_path' => $document['file_path'],
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }

    private function kategoriForJenisBantuan(string $jenisBantuan, int $index): string
    {
        return match ($jenisBantuan) {
            'bantuan_pembelajaran' => intdiv($index, 4) % 2 === 0
                ? Permohonan::KATEGORI_ALAT_TULIS_PEMBELAJARAN
                : Permohonan::KATEGORI_PERALATAN_PEMBELAJARAN,
            'bantuan_sukan' => Permohonan::KATEGORI_SUKAN,
            default => Permohonan::KATEGORI_KEPERLUAN_ASAS,
        };
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

    private function applicationNote(string $jenisBantuan): string
    {
        return match ($jenisBantuan) {
            'bantuan_pembelajaran' => 'Memerlukan sokongan pembelajaran untuk bahan kuliah, tugasan dan akses peranti.',
            'bantuan_sukan' => 'Memerlukan peralatan sukan untuk latihan dan penyertaan aktiviti kolej atau fakulti.',
            'bantuan_musibah' => 'Keluarga pelajar terkesan akibat musibah dan memerlukan bantuan segera.',
            default => 'Memerlukan bantuan asas hidup untuk menampung keperluan makanan dan harian.',
        };
    }

    private function estimatedAidCost(string $jenisBantuan, int $index): int
    {
        return match ($jenisBantuan) {
            'bantuan_pembelajaran' => [180, 260, 450, 900][$index % 4],
            'bantuan_sukan' => [150, 280, 520, 750][$index % 4],
            'bantuan_musibah' => [300, 500, 850, 1200][$index % 4],
            default => [120, 180, 250, 350][$index % 4],
        };
    }

    /**
     * @param array<int, string> $proofPaths
     */
    private function writeAgihanProofPlaceholders(array $proofPaths): void
    {
        foreach ($proofPaths as $index => $path) {
            Storage::disk('local')->put($path, $this->minimalPdf('Bukti agihan dummy ' . ($index + 1)));
        }
    }

    private function minimalPdf(string $title): string
    {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $title);

        return "%PDF-1.4\n"
            . "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n"
            . "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n"
            . "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 300 160] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n"
            . "4 0 obj << /Length 64 >> stream\n"
            . "BT /F1 14 Tf 24 96 Td ($text) Tj ET\n"
            . "endstream endobj\n"
            . "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n"
            . "xref\n"
            . "0 6\n"
            . "0000000000 65535 f \n"
            . "trailer << /Root 1 0 R /Size 6 >>\n"
            . "startxref\n"
            . "0\n"
            . "%%EOF\n";
    }
}
