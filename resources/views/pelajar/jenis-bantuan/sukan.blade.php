@extends('layouts.app')

@section('content')
@php
    $items = $items ?? collect();
    $sportsItemDescriptions = [
        'Raket Badminton' => [
            'Jenama: Yonex GR303',
            '1 unit raket badminton',
            'Sesuai untuk latihan dan pertandingan',
        ],
        'Shuttlecock' => [
            'Jenama: Yonex Aerosensa 30',
            '1 tiub (12 biji)',
            'Sesuai untuk latihan badminton',
        ],
        'Bola Futsal' => [
            'Jenama: Molten F9A2000',
            '1 unit bola futsal',
            'Untuk latihan dan pertandingan',
        ],
        'Bola Volleyball' => [
            'Jenama: Mikasa V200W',
            '1 unit bola tampar',
            'Untuk latihan dan pertandingan',
        ],
        'Bola Netball' => [
            'Jenama: Gilbert Eclipse',
            '1 unit bola jaring',
            'Untuk latihan dan pertandingan',
        ],
        'Paddle Ping Pong' => [
            'Jenama: Butterfly Timo Boll',
            '1 pasang paddle',
            'Untuk latihan ping pong',
        ],
        'Bola Ping Pong' => [
            'Jenama: Butterfly 3-Star 40+',
            '1 kotak (6 biji)',
            'Untuk latihan dan pertandingan',
        ],
        'Catur' => [
            'Jenama: Staunton Wooden Chess Set',
            '1 set papan dan buah catur',
            'Untuk latihan dan aktiviti kelab',
        ],
    ];

    $items = $items->map(function ($item) use ($sportsItemDescriptions) {
        $item->description_points = $sportsItemDescriptions[$item->nama_item] ?? [];

        return $item;
    });
@endphp

<div class="max-w-7xl mx-auto px-6 py-10">

    <x-page-hero
        class="mb-8"
        eyebrow="Pelajar"
        title="Sukan"
        description="Pilih peralatan sukan yang diperlukan."
    />

    <!-- GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($items as $item)
            <div class="bg-white rounded-xl shadow hover:shadow-xl transition overflow-hidden h-full flex flex-col">
                <div class="w-full h-48 bg-white-100 flex items-center justify-center">
                    <img src="{{ asset('image/' . $item->image_asset_path) }}"
                         alt="{{ $item->nama_item }}"
                         class="max-h-full max-w-full object-contain p-2">
                </div>

                <div class="p-6 flex flex-1 flex-col">
                    <h2 class="text-xl font-semibold mb-2">{{ $item->nama_item }}</h2>

                    <ul class="text-sm text-gray-700 list-disc pl-5 mb-5 space-y-1">
                        @foreach($item->description_points as $descriptionPoint)
                            <li>{{ $descriptionPoint }}</li>
                        @endforeach
                    </ul>

                    <a
                       href="{{ route('permohonan.index', [
                        'jenis' => 'bantuan_sukan',
                        'kategori' => 'sukan',
                        'item' => $item->nama_item,
                    ]) }}"
                       class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 mt-auto">
                        Mohon Bantuan
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center">
                <p class="text-sm font-semibold text-slate-700">Tiada peralatan sukan aktif buat masa ini.</p>
                <p class="mt-2 text-sm text-slate-500">Peralatan yang ditambah oleh admin akan dipaparkan di sini.</p>
            </div>
        @endforelse
    </div>

</div>

@endsection
