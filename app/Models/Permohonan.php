<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Permohonan extends Model
{
    public const STATUS_DALAM_SEMAKAN = 'dalam_semakan';
    public const STATUS_DILULUSKAN = 'diluluskan';
    public const STATUS_DITOLAK_GAGAL = 'ditolak_gagal';
    public const STATUS_LAIN = 'lain';
    public const STATUS_AGIHAN_BELUM_DIAGIH = 'belum_diagih';
    public const STATUS_AGIHAN_SEDANG_DIAGIH = 'sedang_diagih';
    public const STATUS_AGIHAN_SELESAI = 'selesai';
    public const JENIS_BANTUAN_MUSIBAH = 'bantuan_musibah';
    public const KATEGORI_KEPERLUAN_ASAS = 'keperluan_asas';
    public const KATEGORI_ALAT_TULIS_PEMBELAJARAN = 'alat_tulis_pembelajaran';
    public const KATEGORI_PERALATAN_PEMBELAJARAN = 'peralatan_pembelajaran';
    public const KATEGORI_SUKAN = 'sukan';

    public const STATUS_GROUP_VALUES = [
        self::STATUS_DALAM_SEMAKAN => [
            'Sedang Disemak',
            'sedang_disemak',
            'Dalam Semakan',
            'dalam_semakan',
            'dalam semakan',
            'Permohonan Dihantar',
            'Semakan Dokumen',
            'Menunggu Kelulusan',
        ],
        self::STATUS_DILULUSKAN => [
            'Diluluskan',
            'diluluskan',
            'Lulus',
            'lulus',
        ],
        self::STATUS_DITOLAK_GAGAL => [
            'Ditolak',
            'ditolak',
            'Gagal',
            'gagal',
        ],
    ];

    public const JENIS_BANTUAN_LABELS = [
        'bantuan_asas_hidup' => 'Bantuan Asas Hidup',
        'bantuan_pembelajaran' => 'Bantuan Pembelajaran',
        'bantuan_sukan' => 'Bantuan Sukan',
        self::JENIS_BANTUAN_MUSIBAH => 'Bantuan Musibah',
    ];

    public const KATEGORI_BANTUAN_LABELS = [
        self::KATEGORI_KEPERLUAN_ASAS => 'Keperluan Asas',
        self::KATEGORI_ALAT_TULIS_PEMBELAJARAN => 'Alat Tulis Pembelajaran',
        self::KATEGORI_PERALATAN_PEMBELAJARAN => 'Peralatan Pembelajaran',
        self::KATEGORI_SUKAN => 'Sukan',
    ];

    public const LEGACY_KATEGORI_BANTUAN_ALIASES = [
        'peralatan_sukan' => self::KATEGORI_SUKAN,
        'pakaian_sukan' => self::KATEGORI_SUKAN,
    ];

    public const LEGACY_DUMMY_KATEGORI_BANTUAN_ALIASES = [
        'pembelajaran' => self::KATEGORI_ALAT_TULIS_PEMBELAJARAN,
        'peralatan_sukan' => self::KATEGORI_SUKAN,
        'pakaian_sukan' => self::KATEGORI_SUKAN,
    ];

    public const DONATION_CATEGORY_MATCHES = [
        self::KATEGORI_KEPERLUAN_ASAS => [self::KATEGORI_KEPERLUAN_ASAS],
        self::KATEGORI_ALAT_TULIS_PEMBELAJARAN => [self::KATEGORI_ALAT_TULIS_PEMBELAJARAN],
        self::KATEGORI_PERALATAN_PEMBELAJARAN => [self::KATEGORI_PERALATAN_PEMBELAJARAN],
        self::KATEGORI_SUKAN => [self::KATEGORI_SUKAN],
        'pembelajaran' => [
            self::KATEGORI_ALAT_TULIS_PEMBELAJARAN,
            self::KATEGORI_PERALATAN_PEMBELAJARAN,
        ],
        'peralatan_sukan' => [self::KATEGORI_SUKAN],
        'pakaian_sukan' => [self::KATEGORI_SUKAN],
    ];

    protected $fillable = [
        'user_id',
        'no_kelompok',
        'tarikh_mohon',
        'jenis_bantuan',
        'status',
        'status_agihan',
        'tarikh_agihan',
        'catatan_agihan',
        'bukti_agihan',
        'diagih_oleh',
        'catatan',
        'admin_catatan',
        'admin_review_date',
        'pakej',
        'jumlah_ahli',
        'nama_group',
        'bilangan_ahli',
        'kategori',
        'organisasi',
    ];

    protected $casts = [
        'tarikh_mohon' => 'date',
        'admin_review_date' => 'datetime',
        'tarikh_agihan' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pelajar(): HasOne
    {
        return $this->hasOne(PermohonanPelajar::class);
    }

    public function bantuan(): HasOne
    {
        return $this->hasOne(PermohonanBantuan::class);
    }

    public function bantuans(): HasMany
    {
        return $this->hasMany(PermohonanBantuan::class);
    }

    public function keluarga(): HasMany
    {
        return $this->hasMany(PermohonanKeluarga::class);
    }

    public function dokumens(): HasMany
    {
        return $this->hasMany(PermohonanDokumen::class);
    }

    public function diagihOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diagih_oleh');
    }

    public static function statusValuesFor(string $statusKey): array
    {
        return self::STATUS_GROUP_VALUES[$statusKey] ?? [];
    }

    public static function normalizeStatus(?string $status): string
    {
        $normalized = Str::of($status ?? '')
            ->trim()
            ->lower()
            ->replace(['_', '-'], ' ')
            ->squish()
            ->toString();

        return match ($normalized) {
            'sedang disemak',
            'dalam semakan',
            'permohonan dihantar',
            'semakan dokumen',
            'menunggu kelulusan' => self::STATUS_DALAM_SEMAKAN,
            'diluluskan',
            'lulus' => self::STATUS_DILULUSKAN,
            'ditolak',
            'gagal' => self::STATUS_DITOLAK_GAGAL,
            default => self::STATUS_LAIN,
        };
    }

    public static function statusLabel(?string $status): string
    {
        return match (self::normalizeStatus($status)) {
            self::STATUS_DALAM_SEMAKAN => 'Dalam Semakan',
            self::STATUS_DILULUSKAN => 'Diluluskan',
            self::STATUS_DITOLAK_GAGAL => 'Ditolak / Gagal',
            default => $status ?: 'Tiada Status',
        };
    }

    public static function statusBadgeClass(?string $status): string
    {
        return match (self::normalizeStatus($status)) {
            self::STATUS_DALAM_SEMAKAN => 'bg-amber-100 text-amber-700',
            self::STATUS_DILULUSKAN => 'bg-emerald-100 text-emerald-700',
            self::STATUS_DITOLAK_GAGAL => 'bg-rose-100 text-rose-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    public static function normalizeStatusAgihan(?string $statusAgihan): string
    {
        return match ($statusAgihan) {
            self::STATUS_AGIHAN_SEDANG_DIAGIH => self::STATUS_AGIHAN_SEDANG_DIAGIH,
            self::STATUS_AGIHAN_SELESAI => self::STATUS_AGIHAN_SELESAI,
            default => self::STATUS_AGIHAN_BELUM_DIAGIH,
        };
    }

    public static function statusAgihanLabel(?string $statusAgihan): string
    {
        return match (self::normalizeStatusAgihan($statusAgihan)) {
            self::STATUS_AGIHAN_SEDANG_DIAGIH => 'Sedang Diagih',
            self::STATUS_AGIHAN_SELESAI => 'Selesai',
            default => 'Belum Diagih',
        };
    }

    public static function statusAgihanBadgeClass(?string $statusAgihan): string
    {
        return match (self::normalizeStatusAgihan($statusAgihan)) {
            self::STATUS_AGIHAN_SEDANG_DIAGIH => 'bg-blue-100 text-blue-700',
            self::STATUS_AGIHAN_SELESAI => 'bg-emerald-100 text-emerald-700',
            default => 'bg-amber-100 text-amber-700',
        };
    }

    public function progressSteps(): array
    {
        $statusKey = $this->status_key;
        $agihanKey = $this->status_agihan_key;

        $states = match ($statusKey) {
            self::STATUS_DALAM_SEMAKAN => [
                'submitted' => 'complete',
                'review' => 'current',
                'decision' => 'pending',
                'distribution' => 'pending',
            ],
            self::STATUS_DILULUSKAN => [
                'submitted' => 'complete',
                'review' => 'complete',
                'decision' => 'complete',
                'distribution' => $agihanKey === self::STATUS_AGIHAN_SEDANG_DIAGIH ? 'current' : 'pending',
            ],
            self::STATUS_DITOLAK_GAGAL => [
                'submitted' => 'complete',
                'review' => 'complete',
                'decision' => 'rejected',
                'distribution' => 'disabled',
            ],
            default => [
                'submitted' => 'complete',
                'review' => 'current',
                'decision' => 'pending',
                'distribution' => 'pending',
            ],
        };

        if ($agihanKey === self::STATUS_AGIHAN_SEDANG_DIAGIH && $statusKey !== self::STATUS_DITOLAK_GAGAL) {
            $states = [
                'submitted' => 'complete',
                'review' => 'complete',
                'decision' => 'complete',
                'distribution' => 'current',
            ];
        }

        if ($agihanKey === self::STATUS_AGIHAN_SELESAI) {
            $states = [
                'submitted' => 'complete',
                'review' => 'complete',
                'decision' => 'complete',
                'distribution' => 'complete',
            ];
        }

        return [
            [
                'key' => 'submitted',
                'number' => 1,
                'label' => 'Permohonan Dihantar',
                'description' => 'Permohonan bantuan telah berjaya dihantar.',
                'state' => $states['submitted'],
            ],
            [
                'key' => 'review',
                'number' => 2,
                'label' => 'Semakan Dokumen',
                'description' => 'Dokumen permohonan disemak oleh pihak urus setia.',
                'state' => $states['review'],
            ],
            [
                'key' => 'decision',
                'number' => 3,
                'label' => 'Keputusan Permohonan',
                'description' => 'Keputusan permohonan akan dipaparkan selepas semakan selesai.',
                'state' => $states['decision'],
            ],
            [
                'key' => 'distribution',
                'number' => 4,
                'label' => 'Agihan Bantuan',
                'description' => 'Bantuan diagihkan selepas permohonan diluluskan.',
                'state' => $states['distribution'],
            ],
        ];
    }

    public static function jenisBantuanLabel(?string $jenisBantuan): string
    {
        if (! filled($jenisBantuan)) {
            return '-';
        }

        return self::JENIS_BANTUAN_LABELS[$jenisBantuan]
            ?? Str::of($jenisBantuan)->replace(['_', '-'], ' ')->squish()->title()->toString();
    }

    public static function kategoriBantuanLabel(?string $kategoriBantuan): string
    {
        if (! filled($kategoriBantuan)) {
            return '-';
        }

        $kategoriBantuan = self::normalizeKategoriBantuan($kategoriBantuan);

        return self::KATEGORI_BANTUAN_LABELS[$kategoriBantuan]
            ?? Str::of($kategoriBantuan)->replace(['_', '-'], ' ')->squish()->title()->toString();
    }

    public static function normalizeKategoriBantuan(?string $kategoriBantuan, bool $includeBroadLegacy = false): ?string
    {
        if (! filled($kategoriBantuan)) {
            return null;
        }

        $key = self::kategoriBantuanKey($kategoriBantuan);
        $aliases = $includeBroadLegacy
            ? self::LEGACY_DUMMY_KATEGORI_BANTUAN_ALIASES
            : self::LEGACY_KATEGORI_BANTUAN_ALIASES;

        return $aliases[$key]
            ?? (array_key_exists($key, self::KATEGORI_BANTUAN_LABELS) ? $key : $kategoriBantuan);
    }

    public static function canonicalKategoriBantuanValues(): array
    {
        return array_keys(self::KATEGORI_BANTUAN_LABELS);
    }

    public static function kategoriBantuanMatchesForDonationCategories(iterable $categories): array
    {
        $matches = [];

        foreach ($categories as $category) {
            if (! filled($category)) {
                continue;
            }

            $key = self::kategoriBantuanKey($category);
            $normalized = self::normalizeKategoriBantuan($category);

            $matches = array_merge(
                $matches,
                self::DONATION_CATEGORY_MATCHES[$key] ?? ($normalized ? [$normalized] : [])
            );
        }

        return collect($matches)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private static function kategoriBantuanKey(string $kategoriBantuan): string
    {
        return Str::of($kategoriBantuan)
            ->replace(['-', ' '], '_')
            ->lower()
            ->toString();
    }

    public static function currentSemesterRange(?CarbonInterface $date = null): array
    {
        $date = $date ? Carbon::parse($date)->startOfDay() : Carbon::today();

        if ($date->month >= 3 && $date->month <= 8) {
            return [
                $date->copy()->month(3)->day(1)->startOfDay(),
                $date->copy()->month(8)->endOfMonth()->endOfDay(),
            ];
        }

        if ($date->month >= 9) {
            return [
                $date->copy()->month(9)->day(1)->startOfDay(),
                $date->copy()->addYear()->month(2)->endOfMonth()->endOfDay(),
            ];
        }

        return [
            $date->copy()->subYear()->month(9)->day(1)->startOfDay(),
            $date->copy()->month(2)->endOfMonth()->endOfDay(),
        ];
    }

    public static function bantuanLocksForUser(int $userId, ?CarbonInterface $date = null): array
    {
        [$semesterStart, $semesterEnd] = self::currentSemesterRange($date);
        $lockableStatuses = array_merge(
            self::statusValuesFor(self::STATUS_DALAM_SEMAKAN),
            self::statusValuesFor(self::STATUS_DILULUSKAN)
        );

        return self::query()
            ->where('user_id', $userId)
            ->where('jenis_bantuan', '!=', self::JENIS_BANTUAN_MUSIBAH)
            ->whereIn('status', $lockableStatuses)
            ->whereBetween('tarikh_mohon', [$semesterStart->toDateString(), $semesterEnd->toDateString()])
            ->latest('admin_review_date')
            ->latest('tarikh_mohon')
            ->latest('id')
            ->get(['id', 'no_kelompok', 'jenis_bantuan', 'status', 'tarikh_mohon'])
            ->groupBy('jenis_bantuan')
            ->map(function ($applications) {
                $application = $applications->first();

                return [
                    'no_kelompok' => $application->no_kelompok,
                    'jenis_bantuan' => $application->jenis_bantuan,
                    'label' => self::jenisBantuanLabel($application->jenis_bantuan),
                    'status' => $application->status,
                    'status_label' => self::statusLabel($application->status),
                    'message' => self::jenisBantuanLabel($application->jenis_bantuan)
                        . ' telah ' . (self::normalizeStatus($application->status) === self::STATUS_DILULUSKAN ? 'diluluskan.' : 'dihantar dan sedang disemak')
                ];
            })
            ->all();
    }

    public static function isBantuanTypeLockedForUser(int $userId, ?string $jenisBantuan, ?CarbonInterface $date = null): bool
    {
        if (! filled($jenisBantuan) || $jenisBantuan === self::JENIS_BANTUAN_MUSIBAH) {
            return false;
        }

        return array_key_exists($jenisBantuan, self::bantuanLocksForUser($userId, $date));
    }

    public static function isLateForProcessing(self $permohonan, ?CarbonInterface $today = null): bool
    {
        if (! $permohonan->tarikh_mohon || self::normalizeStatus($permohonan->status) !== self::STATUS_DALAM_SEMAKAN) {
            return false;
        }

        $today = $today ? Carbon::parse($today)->startOfDay() : Carbon::today();

        return Carbon::parse($permohonan->tarikh_mohon)->startOfDay()->diffInDays($today) > 7;
    }

    public static function statusCounts(?CarbonInterface $today = null): array
    {
        $today = $today ? Carbon::parse($today)->startOfDay() : Carbon::today();
        $lateBeforeDate = $today->copy()->subDays(7)->toDateString();

        $query = self::query();
        $dalamSemakanValues = self::statusValuesFor(self::STATUS_DALAM_SEMAKAN);
        $diluluskanValues = self::statusValuesFor(self::STATUS_DILULUSKAN);
        $ditolakValues = self::statusValuesFor(self::STATUS_DITOLAK_GAGAL);

        $jumlahDalamSemakan = (clone $query)
            ->whereIn('status', $dalamSemakanValues)
            ->count();

        $lewatDiproses = (clone $query)
            ->whereIn('status', $dalamSemakanValues)
            ->whereDate('tarikh_mohon', '<', $lateBeforeDate)
            ->count();

        $diluluskan = (clone $query)
            ->whereIn('status', $diluluskanValues)
            ->count();

        $ditolakGagal = (clone $query)
            ->whereIn('status', $ditolakValues)
            ->count();

        return [
            'jumlah' => (clone $query)->count(),
            'dalam_semakan' => max(0, $jumlahDalamSemakan - $lewatDiproses),
            'dalam_semakan_total' => $jumlahDalamSemakan,
            'diluluskan' => $diluluskan,
            'ditolak_gagal' => $ditolakGagal,
            'lewat_diproses' => $lewatDiproses,
            'selesai' => $diluluskan + $ditolakGagal,
        ];
    }

    public function getStatusKeyAttribute(): string
    {
        return self::normalizeStatus($this->status);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabel($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return self::statusBadgeClass($this->status);
    }

    public function getLewatDiprosesAttribute(): bool
    {
        return self::isLateForProcessing($this);
    }

    public function getStatusAgihanKeyAttribute(): string
    {
        return self::normalizeStatusAgihan($this->status_agihan);
    }

    public function getStatusAgihanLabelAttribute(): string
    {
        return self::statusAgihanLabel($this->status_agihan);
    }

    public function getStatusAgihanBadgeClassAttribute(): string
    {
        return self::statusAgihanBadgeClass($this->status_agihan);
    }
}
