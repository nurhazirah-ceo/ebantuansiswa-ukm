<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'nama_item' => 'Pakej 1 Orang',
                'kategori' => 'keperluan',
                'kategori_bantuan' => 'keperluan_asas',
                'harga' => 20,
                'imej' => 'donations/keperluan/makanan1.jpg',
                'stok_diperlukan' => 25,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 1,
            ],
            [
                'nama_item' => 'Pakej 3 Orang',
                'kategori' => 'keperluan',
                'kategori_bantuan' => 'keperluan_asas',
                'harga' => 45,
                'imej' => 'donations/keperluan/makanan2.jpg',
                'stok_diperlukan' => 20,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 2,
            ],
            [
                'nama_item' => 'Pakej 5 Orang',
                'kategori' => 'keperluan',
                'kategori_bantuan' => 'keperluan_asas',
                'harga' => 70,
                'imej' => 'donations/keperluan/makanan3.jpg',
                'stok_diperlukan' => 16,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 3,
            ],
            [
                'nama_item' => 'Pakej 7 Orang',
                'kategori' => 'keperluan',
                'kategori_bantuan' => 'keperluan_asas',
                'harga' => 95,
                'imej' => 'donations/keperluan/makanan4.jpg',
                'stok_diperlukan' => 12,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 4,
            ],
            [
                'nama_item' => 'Pakej 10 Orang',
                'kategori' => 'keperluan',
                'kategori_bantuan' => 'keperluan_asas',
                'harga' => 130,
                'imej' => 'donations/keperluan/makanan5.jpg',
                'stok_diperlukan' => 10,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 5,
            ],
            [
                'nama_item' => 'Buku Nota',
                'kategori' => 'pembelajaran',
                'kategori_bantuan' => Item::CATEGORY_ALAT_TULIS_PEMBELAJARAN,
                'harga' => 6,
                'imej' => 'donations/pembelajaran/book.jpg',
                'stok_diperlukan' => 50,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 1,
            ],
            [
                'nama_item' => 'Test Pad',
                'kategori' => 'pembelajaran',
                'kategori_bantuan' => Item::CATEGORY_ALAT_TULIS_PEMBELAJARAN,
                'harga' => 4,
                'imej' => 'donations/pembelajaran/testpad.jpg',
                'stok_diperlukan' => 40,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 2,
            ],
            [
                'nama_item' => 'Pen',
                'kategori' => 'pembelajaran',
                'kategori_bantuan' => Item::CATEGORY_ALAT_TULIS_PEMBELAJARAN,
                'harga' => 2,
                'imej' => 'donations/pembelajaran/pen.jpg',
                'stok_diperlukan' => 60,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 3,
            ],
            [
                'nama_item' => 'Set Pensil',
                'kategori' => 'pembelajaran',
                'kategori_bantuan' => Item::CATEGORY_ALAT_TULIS_PEMBELAJARAN,
                'harga' => 8,
                'imej' => 'donations/pembelajaran/setpensil.jpg',
                'stok_diperlukan' => 40,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 4,
            ],
            [
                'nama_item' => 'Folder',
                'kategori' => 'pembelajaran',
                'kategori_bantuan' => Item::CATEGORY_ALAT_TULIS_PEMBELAJARAN,
                'harga' => 5,
                'imej' => 'donations/pembelajaran/filefolder.jpg',
                'stok_diperlukan' => 35,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 5,
            ],
            [
                'nama_item' => 'Highlighter',
                'kategori' => 'pembelajaran',
                'kategori_bantuan' => Item::CATEGORY_ALAT_TULIS_PEMBELAJARAN,
                'harga' => 4,
                'imej' => 'donations/pembelajaran/highlighter.jpg',
                'stok_diperlukan' => 35,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 6,
            ],
            [
                'nama_item' => 'Laptop',
                'kategori' => 'pembelajaran',
                'kategori_bantuan' => Item::CATEGORY_PERALATAN_PEMBELAJARAN,
                'harga' => 1800,
                'imej' => 'donations/pembelajaran/dellaptop.jpg',
                'stok_diperlukan' => 5,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 7,
            ],
            [
                'nama_item' => 'Tablet',
                'kategori' => 'pembelajaran',
                'kategori_bantuan' => Item::CATEGORY_PERALATAN_PEMBELAJARAN,
                'harga' => 700,
                'imej' => 'donations/pembelajaran/samsungtab.jpg',
                'stok_diperlukan' => 8,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 8,
            ],
            [
                'nama_item' => 'Kalkulator Saintifik',
                'kategori' => 'pembelajaran',
                'kategori_bantuan' => Item::CATEGORY_PERALATAN_PEMBELAJARAN,
                'harga' => 45,
                'imej' => 'donations/pembelajaran/kalkulator.jpg',
                'stok_diperlukan' => 15,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 9,
            ],
            [
                'nama_item' => 'Raket Badminton',
                'kategori' => 'sukan',
                'kategori_bantuan' => 'sukan',
                'harga' => 95,
                'imej' => 'donations/sukan/badminton.jpg',
                'stok_diperlukan' => 12,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 1,
            ],
            [
                'nama_item' => 'Shuttlecock',
                'kategori' => 'sukan',
                'kategori_bantuan' => 'sukan',
                'harga' => 42,
                'imej' => 'donations/sukan/shuttlecock.jpg',
                'stok_diperlukan' => 20,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 2,
            ],
            [
                'nama_item' => 'Bola Futsal',
                'kategori' => 'sukan',
                'kategori_bantuan' => 'sukan',
                'harga' => 89,
                'imej' => 'donations/sukan/futsal.jpg',
                'stok_diperlukan' => 10,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 3,
            ],
            [
                'nama_item' => 'Bola Volleyball',
                'kategori' => 'sukan',
                'kategori_bantuan' => 'sukan',
                'harga' => 75,
                'imej' => 'donations/sukan/bolatampar.jpg',
                'stok_diperlukan' => 10,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 4,
            ],
            [
                'nama_item' => 'Bola Netball',
                'kategori' => 'sukan',
                'kategori_bantuan' => 'sukan',
                'harga' => 85,
                'imej' => 'donations/sukan/bolajaring.jpg',
                'stok_diperlukan' => 10,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 5,
            ],
            [
                'nama_item' => 'Paddle Ping Pong',
                'kategori' => 'sukan',
                'kategori_bantuan' => 'sukan',
                'harga' => 35,
                'imej' => 'donations/sukan/pingpong.jpg',
                'stok_diperlukan' => 15,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 6,
            ],
            [
                'nama_item' => 'Bola Ping Pong',
                'kategori' => 'sukan',
                'kategori_bantuan' => 'sukan',
                'harga' => 12,
                'imej' => 'donations/sukan/bolapingpong.jpg',
                'stok_diperlukan' => 30,
                'stok_disumbang' => 0,
                'status' => 'aktif',
                'susunan' => 7,
            ],
        ];

        $inserted = 0;
        $updated = 0;

        foreach ($items as $item) {
            $model = Item::updateOrCreate(
                [
                    'nama_item' => $item['nama_item'],
                ],
                $item
            );

            $model->wasRecentlyCreated ? $inserted++ : $updated++;
        }

        if ($this->command) {
            $this->command->info("Default assistance items seeded. Inserted: {$inserted}. Updated: {$updated}.");
        }
    }
}
