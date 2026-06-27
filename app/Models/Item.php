<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    public const CATEGORY_KEPERLUAN_ASAS = 'keperluan_asas';
    public const CATEGORY_PEMBELAJARAN = 'pembelajaran';
    public const CATEGORY_ALAT_TULIS_PEMBELAJARAN = 'alat_tulis_pembelajaran';
    public const CATEGORY_PERALATAN_PEMBELAJARAN = 'peralatan_pembelajaran';
    public const CATEGORY_SUKAN = 'sukan';
    public const LEARNING_CATEGORIES = [
        self::CATEGORY_ALAT_TULIS_PEMBELAJARAN,
        self::CATEGORY_PERALATAN_PEMBELAJARAN,
    ];

    public const DONATION_CATEGORIES = [
        self::CATEGORY_KEPERLUAN_ASAS => [
            'title' => 'Keperluan Asas',
            'description' => 'Stok kit makanan mengikut bilangan penerima.',
            'legacy_key' => 'keperluan',
        ],
        self::CATEGORY_ALAT_TULIS_PEMBELAJARAN => [
            'title' => 'Alat Tulis Pembelajaran',
            'description' => 'Stok alat tulis dan bahan pembelajaran harian.',
            'legacy_key' => 'pembelajaran',
        ],
        self::CATEGORY_PERALATAN_PEMBELAJARAN => [
            'title' => 'Peralatan Pembelajaran',
            'description' => 'Stok peralatan akademik seperti peranti dan kalkulator.',
            'legacy_key' => 'pembelajaran',
        ],
        self::CATEGORY_SUKAN => [
            'title' => 'Sukan',
            'description' => 'Stok peralatan sukan untuk kegunaan pelajar.',
            'legacy_key' => 'sukan',
        ],
    ];

    protected $fillable = [
        'nama_item',
        'kategori',
        'kategori_bantuan',
        'harga',
        'imej',
        'stok_diperlukan',
        'stok_disumbang',
        'status',
        'is_active',
        'susunan',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'stok_diperlukan' => 'integer',
        'stok_disumbang' => 'integer',
        'is_active' => 'boolean',
        'susunan' => 'integer',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('status', 'aktif');
    }

    public function scopeKategoriBantuan(Builder $query, string $kategoriBantuan): Builder
    {
        if ($kategoriBantuan === self::CATEGORY_PEMBELAJARAN) {
            return $query->whereIn('kategori_bantuan', self::learningCategoryKeys(includeLegacy: true));
        }

        return $query->where('kategori_bantuan', $kategoriBantuan);
    }

    public function sumbanganItems(): HasMany
    {
        return $this->hasMany(SumbanganItem::class);
    }

    public static function legacyCategoryFor(string $kategoriBantuan): string
    {
        return self::DONATION_CATEGORIES[$kategoriBantuan]['legacy_key'] ?? $kategoriBantuan;
    }

    public static function learningCategoryKeys(bool $includeLegacy = false): array
    {
        return $includeLegacy
            ? array_merge([self::CATEGORY_PEMBELAJARAN], self::LEARNING_CATEGORIES)
            : self::LEARNING_CATEGORIES;
    }

    public function getJumlahDiperlukanAttribute(): int
    {
        return (int) $this->stok_diperlukan;
    }

    public function getTelahDisumbangAttribute(): int
    {
        return (int) $this->stok_disumbang;
    }

    public function getBakiAttribute(): int
    {
        return $this->jumlah_diperlukan - $this->telah_disumbang;
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->jumlah_diperlukan <= 0) {
            return 0;
        }

        return min((int) round(($this->telah_disumbang / $this->jumlah_diperlukan) * 100), 100);
    }

    public function getImageAssetPathAttribute(): string
    {
        if (filled($this->imej)) {
            return $this->imej;
        }

        return match ($this->kategori_bantuan) {
            self::CATEGORY_PEMBELAJARAN,
            self::CATEGORY_ALAT_TULIS_PEMBELAJARAN,
            self::CATEGORY_PERALATAN_PEMBELAJARAN => 'donations/pembelajaran/stationery.jpg',
            self::CATEGORY_SUKAN => 'donations/sukan/bolatampar.jpg',
            default => 'donations/keperluan/makanan5.jpg',
        };
    }

    public function getKategoriBantuanLabelAttribute(): string
    {
        return self::DONATION_CATEGORIES[$this->kategori_bantuan]['title'] ?? $this->kategori_bantuan;
    }
}
