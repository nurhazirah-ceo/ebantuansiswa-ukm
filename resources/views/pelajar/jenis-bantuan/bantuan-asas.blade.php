@extends('layouts.app')

@section('content')
@php
    $items = $items ?? collect();
    $basicFoodItems = [
        'Beras',
        'Minyak masak',
        'Biskut',
        'Bihun',
        'Gula',
        'Uncang teh',
        'Tepung',
        'Maggi',
    ];
@endphp

<div class="max-w-7xl mx-auto px-6 py-10">

    <x-page-hero
        class="mb-8"
        eyebrow="Pelajar"
        title="Keperluan Asas"
        description="Pilih pakej bantuan berdasarkan bilangan penerima."
    />

    <!-- GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($items as $item)
            <div class="bg-white rounded-xl shadow hover:shadow-xl transition overflow-hidden">
                <div class="w-full h-48 bg-white-100 flex items-center justify-center">
                    <img src="{{ asset('image/' . $item->image_asset_path) }}"
                         alt="{{ $item->nama_item }}"
                         class="max-h-full max-w-full object-contain p-2">
                </div>

                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-2">{{ $item->nama_item }}</h2>

                    <ul class="text-sm text-gray-700 list-disc pl-5 mb-5 space-y-1">
                        @foreach($basicFoodItems as $foodItem)
                            <li>{{ $foodItem }}</li>
                        @endforeach
                    </ul>

                    <a
                       href="{{ route('permohonan.index', [
                        'jenis' => 'bantuan_asas_hidup',
                        'kategori' => 'keperluan_asas',
                        'item' => $item->id,
                    ]) }}"
                       class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                        Mohon Bantuan
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center">
                <p class="text-sm font-semibold text-slate-700">Tiada pakej keperluan asas aktif buat masa ini.</p>
                <p class="mt-2 text-sm text-slate-500">Pakej yang ditambah oleh admin akan dipaparkan di sini.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
