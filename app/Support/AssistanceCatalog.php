<?php

namespace App\Support;

use App\Models\Item;
use Illuminate\Support\Collection;

class AssistanceCatalog
{
    public static function activeItems(?string $category = null): Collection
    {
        return Item::query()
            ->where('is_active', true)
            ->aktif()
            ->when($category, fn ($query, string $category) => $query->kategoriBantuan($category))
            ->orderBy('susunan')
            ->orderBy('nama_item')
            ->get();
    }

    public static function groupedActiveItems(): Collection
    {
        $items = self::activeItems();
        $grouped = $items->groupBy('kategori_bantuan');

        $grouped->put(
            Item::CATEGORY_PEMBELAJARAN,
            $items
                ->filter(fn (Item $item) => in_array($item->kategori_bantuan, Item::learningCategoryKeys(includeLegacy: true), true))
                ->values()
        );

        return $grouped;
    }

    public static function learningStationeryItems(?Collection $items = null): Collection
    {
        $items ??= self::activeItems(Item::CATEGORY_PEMBELAJARAN);

        return $items
            ->filter(fn (Item $item) => $item->kategori_bantuan === Item::CATEGORY_ALAT_TULIS_PEMBELAJARAN
                || ($item->kategori_bantuan === Item::CATEGORY_PEMBELAJARAN && ! self::isLearningEquipment($item)))
            ->values();
    }

    public static function learningEquipmentItems(?Collection $items = null): Collection
    {
        $items ??= self::activeItems(Item::CATEGORY_PEMBELAJARAN);

        return $items
            ->filter(fn (Item $item) => $item->kategori_bantuan === Item::CATEGORY_PERALATAN_PEMBELAJARAN
                || ($item->kategori_bantuan === Item::CATEGORY_PEMBELAJARAN && self::isLearningEquipment($item)))
            ->values();
    }

    public static function isLearningEquipment(Item $item): bool
    {
        $name = str($item->nama_item)->lower();

        return $name->contains([
            'laptop',
            'komputer',
            'notebook',
            'tablet',
            'tab',
            'ipad',
            'kalkulator',
            'calculator',
            'peranti',
            'printer',
            'monitor',
        ]);
    }

    public static function basicPackageLimit(Item $item): int
    {
        if (preg_match('/\d+/', (string) $item->nama_item, $matches)) {
            return max((int) $matches[0], 1);
        }

        return 1;
    }

    public static function itemDetailLines(Item $item): array
    {
        return [
            'RM ' . number_format((float) $item->harga, 2) . ' / unit',
            'Jumlah diperlukan: ' . number_format($item->jumlah_diperlukan),
            'Baki: ' . number_format(max($item->baki, 0)),
        ];
    }

    public static function categoryPreview(Collection $items, int $limit = 3): Collection
    {
        return $items
            ->pluck('nama_item')
            ->filter()
            ->take($limit)
            ->values();
    }
}
