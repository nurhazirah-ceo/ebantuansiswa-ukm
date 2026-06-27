@extends('layouts.app')

@section('content')
@php
    $stationeryPreviewItems = $stationeryPreviewItems ?? collect();
    $equipmentPreviewItems = $equipmentPreviewItems ?? collect();
@endphp

<div class="max-w-7xl mx-auto px-6 py-10">

    <x-page-hero
        class="mb-8"
        eyebrow="Pelajar"
        title="Keperluan Pembelajaran"
        description="Pilih jenis bantuan pembelajaran yang diperlukan."
    />

    <!-- Cards -->
    <div class="grid md:grid-cols-2 gap-6">

        <!-- ========================= -->
        <!-- Alat Tulis & Bahan -->
        <!-- ========================= -->
        <div class="bg-white p-6 rounded-xl shadow hover:shadow-xl hover:-translate-y-1 transition transform">

            <!-- Image -->
            <div class="bg-white-50 p-3 rounded mb-4">
                <img src="{{ asset('image/donations/pembelajaran/stationery.jpg') }}" 
                     class="w-full h-40 object-contain">
            </div>

            <!-- Title -->
            <h2 class="text-xl font-semibold mb-2">
                Alat Tulis & Bahan Pembelajaran
            </h2>

            <!-- List -->
            <ul class="text-sm text-gray-500 list-disc pl-4 mb-4">
                @forelse($stationeryPreviewItems as $itemName)
                    <li>{{ $itemName }}</li>
                @empty
                    <li>Tiada item aktif buat masa ini.</li>
                @endforelse
            </ul>

            <!-- Button -->
            <a
                href="{{ route('alat.tulis') }}"
                class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                Lihat Bantuan →
            </a>

        </div>

        <!-- ========================= -->
        <!-- Peralatan Pembelajaran -->
        <!-- ========================= -->
        <div class="bg-white p-6 rounded-xl shadow hover:shadow-xl hover:-translate-y-1 transition transform">

            <!-- Image -->
            <div class="bg-white-50 p-3 rounded mb-4">
                <img src="{{ asset('image/donations/pembelajaran/device.jpg') }}" 
                     class="w-full h-40 object-contain">
            </div>

            <!-- Title -->
            <h2 class="text-xl font-semibold mb-2">
                Peralatan Pembelajaran
            </h2>

            <!-- List -->
            <ul class="text-sm text-gray-500 list-disc pl-4 mb-4">
                @forelse($equipmentPreviewItems as $itemName)
                    <li>{{ $itemName }}</li>
                @empty
                    <li>Tiada item aktif buat masa ini.</li>
                @endforelse
            </ul>

            <!-- Button -->
            <a
                href="{{ route('peralatan') }}"
                class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                Lihat Bantuan →
            </a>

        </div>

    </div>

</div>

@endsection
