<?php

namespace App\Support;

class StudentAcademicProfile
{
    public const ACADEMIC_SESSION = '2025/2026';

    /**
     * @return array<int, string>
     */
    public static function faculties(): array
    {
        return [
            'Fakulti Kejuruteraan dan Alam Bina',
            'Fakulti Sains dan Teknologi',
            'Fakulti Teknologi dan Sains Maklumat',
            'Fakulti Ekonomi dan Pengurusan',
            'Fakulti Sains Sosial dan Kemanusiaan',
            'Fakulti Undang-Undang',
            'Fakulti Pendidikan',
            'Fakulti Pengajian Islam',
            'Fakulti Perubatan',
            'Fakulti Pergigian',
            'Fakulti Farmasi',
            'Fakulti Sains Kesihatan',
            'Fakulti Bahasa dan Linguistik',
        ];
    }

    public static function academicSession(): string
    {
        return self::ACADEMIC_SESSION;
    }
}
