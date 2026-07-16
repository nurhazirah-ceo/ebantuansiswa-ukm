@extends('layouts.app')

@section('content')
@php
    $items = $items ?? collect();
    $equipmentItemDetails = [
        'Laptop' => [
            'Jenama: Dell Latitude 3440',
            'Pemproses Intel® Core™ i5 Generasi Ke-13',
            'Memori 8GB RAM',
            'Storan 512GB SSD',
            'Skrin 14 inci Full HD',
            'Sesuai untuk tugasan, pembelajaran dan kelas dalam talian',
        ],
        'Tablet' => [
            'Jenama: Samsung Galaxy Tab S9 FE',
            'Disertakan bersama S Pen',
            'Skrin 10.9 inci',
            'Storan dalaman 128GB',
            'Bateri berkapasiti tinggi untuk penggunaan harian',
            'Sesuai untuk nota digital, pembelajaran dan pembentangan',
        ],
        'Kalkulator Saintifik' => [
            'Jenama: Casio fx-570ES PLUS',
            'Kalkulator saintifik bukan boleh atur cara',
            'Paparan Natural Display',
            'Lebih 400 fungsi matematik',
            'Sesuai untuk kursus Sains, Teknologi dan Kejuruteraan',
            'Diluluskan untuk kegunaan akademik',
        ],
    ];

    $items = $items->map(function ($item) use ($equipmentItemDetails) {
        $item->detail_points = $equipmentItemDetails[$item->nama_item] ?? [];

        return $item;
    });
@endphp

<div class="max-w-7xl mx-auto px-6 py-10">

    <x-page-hero
        class="mb-8"
        eyebrow="Pelajar"
        title="Peralatan Pembelajaran"
        description="Pilih satu jenis peralatan yang diperlukan."
    />

    <!-- GRID -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 auto-rows-fr">
        @forelse($items as $item)
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition flex flex-col h-full justify-between">
                <div class="flex justify-center mb-4">
                    <img src="{{ asset('image/' . $item->image_asset_path) }}"
                         alt="{{ $item->nama_item }}"
                         class="h-44 object-contain">
                </div>

                <div class="mb-4 flex-1">
                   <h2 class="text-lg font-semibold text-gray-800 mb-2 min-h-[56px] leading-tight">
                        {{ $item->nama_item }}
                    </h2>

                    <ul class="text-sm text-gray-700 list-disc pl-5 mb-5 space-y-1">
                        @foreach($item->detail_points as $detailPoint)
                            <li>{{ $detailPoint }}</li>
                        @endforeach
                    </ul>
                </div>

                <a
                   href="{{ route('permohonan.index', [
                        'jenis' => 'bantuan_pembelajaran',
                        'kategori' => 'peralatan_pembelajaran',
                        'item' => $item->nama_item,
                    ]) }}"
                   class="w-full text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition mt-auto">
                    Mohon Bantuan
                </a>
            </div>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center">
                <p class="text-sm font-semibold text-slate-700">Tiada peralatan pembelajaran aktif buat masa ini.</p>
                <p class="mt-2 text-sm text-slate-500">Peralatan yang ditambah oleh admin akan dipaparkan di sini.</p>
            </div>
        @endforelse
    </div>

</div>

@endsection
