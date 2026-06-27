@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto px-6 py-10">

    <x-page-hero
        class="mb-10"
        eyebrow="Penderma"
        title="Jenis Bantuan Sumbangan"
        description="Pilih kategori bantuan untuk membuat sumbangan."
    />

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">

        <!-- ========================= -->
        <!-- Keperluan Asas -->
        <!-- ========================= -->
        <a href="{{ route('penderma.keperluan-sumbang') }}"
           class="flex flex-col h-full bg-white p-8 rounded-2xl shadow hover:shadow-xl hover:-translate-y-1 transition transform border border-gray-100">

            <!-- Image -->
            <div class="flex justify-center mb-6 h-32">
                <img src="{{ asset('image/donations/keperluan/makanan5.jpg') }}"
                     alt="Keperluan Asas"
                     class="h-28 object-contain">
            </div>

            <!-- Title -->
            <h2 class="text-2xl font-semibold mb-4 text-gray-800 text-center">
                Keperluan Asas
            </h2>

            <!-- Description -->
            <p class="text-gray-600 text-center leading-relaxed flex-grow min-h-[100px]">
                Sumbangan barangan asas seperti makanan, minuman dan keperluan harian untuk membantu pelajar yang memerlukan.
            </p>

            <!-- Button -->
            <span class="inline-block w-full text-center bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700 transition font-medium mt-6">
                Lihat Sumbangan
            </span>
        </a>


        <!-- ========================= -->
        <!-- Pembelajaran -->
        <!-- ========================= -->
        <a href="{{ route('penderma.pembelajaran-sumbang') }}"
           class="flex flex-col h-full bg-white p-8 rounded-2xl shadow hover:shadow-xl hover:-translate-y-1 transition transform border border-gray-100">

            <!-- Image -->
            <div class="flex justify-center mb-6 h-32">
                <img src="{{ asset('image/donations/pembelajaran/stationery.jpg') }}"
                     alt="Pembelajaran"
                     class="h-28 object-contain">
            </div>

            <!-- Title -->
            <h2 class="text-2xl font-semibold mb-4 text-gray-800 text-center">
                Pembelajaran
            </h2>

            <!-- Description -->
            <p class="text-gray-600 text-center leading-relaxed flex-grow min-h-[100px]">
                Sumbangan alat tulis dan peralatan pembelajaran untuk menyokong keperluan akademik pelajar.
            </p>

            <!-- Button -->
            <span class="inline-block w-full text-center bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700 transition font-medium mt-6">
                Lihat Sumbangan
            </span>
        </a>


        <!-- ========================= -->
        <!-- Sukan -->
        <!-- ========================= -->
        <a href="{{ route('penderma.sukan-sumbang') }}"
           class="flex flex-col h-full bg-white p-8 rounded-2xl shadow hover:shadow-xl hover:-translate-y-1 transition transform border border-gray-100">

            <!-- Image -->
            <div class="flex justify-center mb-6 h-32">
                <img src="{{ asset('image/donations/sukan/bolatampar.jpg') }}"
                     alt="Sukan"
                     class="h-28 object-contain">
            </div>

            <!-- Title -->
            <h2 class="text-2xl font-semibold mb-4 text-gray-800 text-center">
                Sukan
            </h2>

            <!-- Description -->
            <p class="text-gray-600 text-center leading-relaxed flex-grow min-h-[100px]">
                Sumbangan peralatan sukan untuk kegunaan aktiviti kokurikulum dan pembangunan pelajar.
            </p>

            <!-- Button -->
            <span class="inline-block w-full text-center bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700 transition font-medium mt-6">
                Lihat Sumbangan
            </span>
        </a>

    </div>

</div>

@endsection
