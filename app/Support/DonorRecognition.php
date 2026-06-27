<?php

namespace App\Support;

class DonorRecognition
{
    public const TIER_PENAJA_UTAMA = 'Penaja Utama';
    public const TIER_PENAJA_HARAPAN = 'Penaja Harapan';
    public const TIER_RAKAN_SOKONGAN = 'Rakan Sokongan';
    public const TIER_PENYUMBANG_PRIHATIN = 'Penyumbang Prihatin';
    public const TIER_BELUM_BERMULA = 'Belum Bermula';

    public static function forAmount(float $totalCompletedAmount): array
    {
        if ($totalCompletedAmount >= 2000) {
            return [
                'tier' => self::TIER_PENAJA_UTAMA,
                'description' => 'Sokongan anda memberi impak besar kepada kesinambungan bantuan pelajar.',
                'class' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                'progress' => 100,
            ];
        }

        if ($totalCompletedAmount >= 500) {
            return [
                'tier' => self::TIER_PENAJA_HARAPAN,
                'description' => 'Sumbangan anda membantu memperkukuh kesinambungan bantuan pelajar.',
                'class' => 'bg-blue-50 text-blue-700 border-blue-100',
                'progress' => 82,
            ];
        }

        if ($totalCompletedAmount >= 100) {
            return [
                'tier' => self::TIER_RAKAN_SOKONGAN,
                'description' => 'Terima kasih kerana terus menjadi rakan sokongan kepada pelajar UKM.',
                'class' => 'bg-blue-50 text-blue-700 border-blue-100',
                'progress' => 66,
            ];
        }

        if ($totalCompletedAmount <= 0) {
            return [
                'tier' => self::TIER_BELUM_BERMULA,
                'description' => 'Tahap pengiktirafan akan dikira selepas sumbangan pertama selesai.',
                'class' => 'bg-slate-50 text-slate-700 border-slate-200',
                'progress' => 0,
            ];
        }

        return [
            'tier' => self::TIER_PENYUMBANG_PRIHATIN,
            'description' => 'Setiap sumbangan anda membantu mengurangkan beban pelajar yang memerlukan.',
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'progress' => 33,
        ];
    }

    public static function tierForAmount(float $totalCompletedAmount): string
    {
        return self::forAmount($totalCompletedAmount)['tier'];
    }

    public static function levels(): array
    {
        return [
            ['range' => 'RM1 - RM99', 'tier' => self::TIER_PENYUMBANG_PRIHATIN],
            ['range' => 'RM100 - RM499', 'tier' => self::TIER_RAKAN_SOKONGAN],
            ['range' => 'RM500 - RM1,999', 'tier' => self::TIER_PENAJA_HARAPAN],
            ['range' => 'RM2,000+', 'tier' => self::TIER_PENAJA_UTAMA],
        ];
    }

    public static function certificateTemplateForTier(string $tier): string
    {
        return match ($tier) {
            self::TIER_RAKAN_SOKONGAN => 'sijil-rakan.png',
            self::TIER_PENYUMBANG_PRIHATIN => 'sijil-prihatin.png',
            default => 'sijil-utama.png',
        };
    }
}
