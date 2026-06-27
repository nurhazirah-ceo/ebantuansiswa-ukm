@extends('layouts.app')

@section('content')
@php
    $items = $items ?? collect();
@endphp

<div class="max-w-6xl mx-auto px-6 py-8">

    <x-page-hero
        class="mb-8"
        eyebrow="Pelajar"
        title="Alat Tulis & Bahan Pembelajaran"
        description="Pilih satu jenis alat tulis atau bahan pembelajaran yang diperlukan."
    />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        @forelse($items as $item)

            <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition duration-300 overflow-hidden">

                <!-- Image -->
                <div class="flex items-center justify-center h-56 p-6">
                    <img
                        src="{{ asset('image/' . $item->image_asset_path) }}"
                        alt="{{ $item->nama_item }}"
                        class="max-h-40 w-auto object-contain transition-transform duration-300 hover:scale-105">
                </div>

                <!-- Content -->
                <div class="px-6 pb-6">

                    <h2 class="text-lg font-semibold text-slate-900 text-center mb-5">
                        {{ $item->nama_item }}
                    </h2>

                    <a
                        href="{{ route('permohonan.index', [
                            'jenis' => 'bantuan_pembelajaran',
                            'kategori' => 'alat_tulis_pembelajaran',
                            'item' => $item->nama_item,
                        ]) }}"
                        class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition duration-300">
                        Mohon Bantuan
                    </a>

                </div>

            </div>

        @empty

            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                <p class="text-base font-semibold text-slate-700">
                    Tiada item pembelajaran aktif buat masa ini.
                </p>

                <p class="mt-2 text-sm text-slate-500">
                    Item yang ditambah oleh admin akan dipaparkan di sini.
                </p>
            </div>

        @endforelse

    </div>

</div>

@endsection