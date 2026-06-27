<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashDonation;
use App\Models\Item;
use App\Models\Permohonan;
use App\Models\Sumbangan;

class LaporanController extends Controller
{
    public function index()
    {
        $completedDonationTotal = (float) Sumbangan::query()
            ->completed()
            ->sum('jumlah_keseluruhan')
            + (float) CashDonation::query()
                ->successful()
                ->sum('amount');

        $reports = [
            [
                'title' => 'Laporan Permohonan',
                'desc' => 'Status, trend bulanan dan rekod permohonan pelajar terkini.',
                'total_label' => number_format(Permohonan::query()->count()),
                'meta' => 'Jumlah rekod',
                'status' => 'Tersedia',
                'href' => route('admin.statistik.permohonan'),
            ],
            [
                'title' => 'Laporan Agihan Bantuan',
                'desc' => 'Kemajuan agihan, bukti agihan dan rekod penyaluran bantuan.',
                'total_label' => number_format(Permohonan::query()
                    ->whereIn('status', Permohonan::statusValuesFor(Permohonan::STATUS_DILULUSKAN))
                    ->count()),
                'meta' => 'Jumlah agihan',
                'status' => 'Tersedia',
                'href' => route('admin.laporan.agihan'),
            ],
            [
                'title' => 'Laporan Sumbangan',
                'desc' => 'Kutipan selesai, tabung bantuan, pembayaran atas talian dan transaksi terkini.',
                'total_label' => 'RM' . number_format($completedDonationTotal, 2),
                'meta' => 'Jumlah nilai',
                'status' => 'Tersedia',
                'href' => route('admin.statistik.sumbangan'),
            ],
            [
                'title' => 'Laporan Inventori',
                'desc' => 'Status stok bantuan, item rendah dan item yang telah habis.',
                'total_label' => number_format(Item::query()->count()),
                'meta' => 'Jumlah item',
                'status' => 'Tersedia',
                'href' => route('admin.statistik.inventori'),
            ],
        ];

        return view('admin.laporan.index', compact('reports'));
    }

    public function inventori()
    {
        $items = Item::query()
            ->orderBy('kategori_bantuan')
            ->orderBy('nama_item')
            ->get();

        $classifiedItems = $items->map(function (Item $item) {
            $status = $this->inventoryStatusFor($item);

            return [
                'item' => $item,
                'status_key' => $status['key'],
                'status_label' => $status['label'],
                'status_class' => $status['class'],
            ];
        });

        $stats = [
            [
                'label' => 'Stok Mencukupi',
                'key' => 'sufficient',
                'value' => $classifiedItems->where('status_key', 'sufficient')->count(),
                'color' => '#10b981',
                'class' => 'bg-emerald-50 text-emerald-700',
            ],
            [
                'label' => 'Stok Rendah',
                'key' => 'low',
                'value' => $classifiedItems->where('status_key', 'low')->count(),
                'color' => '#f59e0b',
                'class' => 'bg-amber-50 text-amber-700',
            ],
            [
                'label' => 'Habis Stok',
                'key' => 'empty',
                'value' => $classifiedItems->where('status_key', 'empty')->count(),
                'color' => '#ef4444',
                'class' => 'bg-rose-50 text-rose-700',
            ],
        ];

        $summary = [
            'total' => $items->count(),
            'sufficient' => $stats[0]['value'],
            'low' => $stats[1]['value'],
            'empty' => $stats[2]['value'],
        ];

        $categoryStockData = collect(Item::DONATION_CATEGORIES)
            ->map(function (array $category, string $key) use ($items) {
                $categoryItems = $items->where('kategori_bantuan', $key);
                $needed = (int) $categoryItems->sum('jumlah_diperlukan');
                $donated = (int) $categoryItems->sum('telah_disumbang');
                $remaining = max(0, $needed - $donated);

                return [
                    'label' => $category['title'],
                    'needed' => $needed,
                    'donated' => $donated,
                    'remaining' => $remaining,
                ];
            })
            ->values();

        $attentionItems = $classifiedItems
            ->whereIn('status_key', ['low', 'empty'])
            ->values();

        $total = collect($stats)->sum('value');

        return view('admin.statistik.inventori', compact('stats', 'summary', 'attentionItems', 'total', 'categoryStockData'));
    }

    public function inventoriCsv()
    {
        $items = Item::query()
            ->orderBy('kategori_bantuan')
            ->orderBy('nama_item')
            ->get();

        return response()->streamDownload(function () use ($items) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nama Item', 'Kategori', 'Stok Diperlukan', 'Telah Disumbang', 'Baki', 'Status']);

            foreach ($items as $item) {
                $status = $this->inventoryStatusFor($item);

                fputcsv($handle, [
                    $item->nama_item,
                    $item->kategori_bantuan_label,
                    $item->jumlah_diperlukan,
                    $item->telah_disumbang,
                    $item->baki,
                    $status['label'],
                ]);
            }

            fclose($handle);
        }, 'laporan-inventori-' . now()->format('Ymd-His') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function inventoryStatusFor(Item $item): array
    {
        if ($item->jumlah_diperlukan <= 0 || $item->baki <= 0) {
            return [
                'key' => 'empty',
                'label' => 'Habis Stok',
                'class' => 'bg-rose-100 text-rose-700',
            ];
        }

        $lowThreshold = max(2, (int) ceil($item->jumlah_diperlukan * 0.25));

        if ($item->baki <= $lowThreshold) {
            return [
                'key' => 'low',
                'label' => 'Stok Rendah',
                'class' => 'bg-amber-100 text-amber-700',
            ];
        }

        return [
            'key' => 'sufficient',
            'label' => 'Stok Mencukupi',
            'class' => 'bg-emerald-100 text-emerald-700',
        ];
    }
}
