@extends('layouts.app')

@section('content')
@php
    $basicPreviewItems = $basicPreviewItems ?? collect();
    $learningPreviewItems = $learningPreviewItems ?? collect();
    $sportsPreviewItems = $sportsPreviewItems ?? collect();
@endphp

<div class="max-w-7xl mx-auto px-8 py-8">

    <x-page-hero
        class="mb-8"
        eyebrow="Pelajar"
        title="Jenis Bantuan"
        description="Senarai kategori bantuan yang disediakan untuk pelajar."
    />

    <!-- ================= BANTUAN GRID ================= -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        <!-- ===================================================== -->
        <!-- KEPERLUAN ASAS -->
        <!-- ===================================================== -->
        <a
           href="{{ route('bantuan.asas') }}"
           class="group bg-white border border-slate-200 rounded-3xl shadow-md hover:shadow-xl hover:-translate-y-1 transition duration-300 overflow-hidden block w-full text-left">

            <!-- IMAGE -->
            <div class="bg-white px-6 pt-6 pb-4 flex justify-center">
                <img src="/image/donations/keperluan/makanan1.jpg"
                     alt="Keperluan Asas"
                     class="h-36 w-full object-contain group-hover:scale-105 transition duration-300">
            </div>

            <!-- CONTENT -->
            <div class="p-6">

                <!-- BADGE -->
                <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full mb-4">
                    Keperluan Harian
                </span>

                <!-- TITLE -->
                <h2 class="text-xl font-semibold text-slate-900 mb-4">
                    Keperluan Asas
                </h2>

               

                <!-- LIST -->
                <ul class="text-sm text-slate-500 list-disc pl-5 space-y-2">
                    @forelse($basicPreviewItems as $itemName)
                        <li>{{ $itemName }}</li>
                    @empty
                        <li>Tiada item aktif buat masa ini.</li>
                    @endforelse
                </ul>

                <!-- BUTTON -->
                <div class="flex justify-center mt-8">

                    <div class="inline-flex items-center gap-2
                                bg-[#2563eb]
                                text-white
                                text-sm font-semibold
                                px-6 py-3
                                rounded-full
                                shadow-lg shadow-blue-200/60
                                group-hover:bg-[#1d4ed8]
                                group-hover:scale-105
                                transition-all duration-300">

                        Lihat Bantuan

                        <span class="text-base">→</span>
                    </div>

                </div>

            </div>
        </a>


        <!-- ===================================================== -->
        <!-- PEMBELAJARAN -->
        <!-- ===================================================== -->
        <a
           href="{{ route('pembelajaran') }}"
           class="group bg-white border border-slate-200 rounded-3xl shadow-md hover:shadow-xl hover:-translate-y-1 transition duration-300 overflow-hidden block w-full text-left">

            <!-- IMAGE -->
            <div class="bg-white px-6 pt-6 pb-4 flex justify-center">
                <img src="/image/donations/pembelajaran/stationery.jpg"
                     alt="Pembelajaran"
                     class="h-36 w-full object-contain group-hover:scale-105 transition duration-300">
            </div>

            <!-- CONTENT -->
            <div class="p-6">

                <!-- BADGE -->
                <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full mb-4">
                    Akademik
                </span>

                <!-- TITLE -->
                <h2 class="text-xl font-semibold text-slate-900 mb-4">
                    Pembelajaran
                </h2>

                <!-- DESCRIPTION -->
                

                <!-- LIST -->
                <ul class="text-sm text-slate-500 list-disc pl-5 space-y-2">
                    @forelse($learningPreviewItems as $itemName)
                        <li>{{ $itemName }}</li>
                    @empty
                        <li>Tiada item aktif buat masa ini.</li>
                    @endforelse
                </ul>

                <!-- BUTTON -->
                <div class="flex justify-center mt-8">

                    <div class="inline-flex items-center gap-2
                                bg-[#2563eb]
                                text-white
                                text-sm font-semibold
                                px-6 py-3
                                rounded-full
                                shadow-lg shadow-blue-200/60
                                group-hover:bg-[#1d4ed8]
                                group-hover:scale-105
                                transition-all duration-300">

                        Lihat Bantuan

                        <span class="text-base">→</span>
                    </div>

                </div>

            </div>
        </a>


        <!-- ===================================================== -->
        <!-- SUKAN -->
        <!-- ===================================================== -->
        <a
           href="{{ route('sukan') }}"
           class="group bg-white border border-slate-200 rounded-3xl shadow-md hover:shadow-xl hover:-translate-y-1 transition duration-300 overflow-hidden block w-full text-left">

            <!-- IMAGE -->
            <div class="bg-white px-6 pt-6 pb-4 flex justify-center">
                <img src="/image/donations/sukan/futsal.jpg"
                     alt="Sukan"
                     class="h-36 w-full object-contain group-hover:scale-105 transition duration-300">
            </div>

            <!-- CONTENT -->
            <div class="p-6">

                <!-- BADGE -->
                <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full mb-4">
                    Aktiviti Sukan
                </span>

                <!-- TITLE -->
                <h2 class="text-xl font-semibold text-slate-900 mb-4">
                    Sukan
                </h2>

                <!-- DESCRIPTION -->
            

                <!-- LIST -->
                <ul class="text-sm text-slate-500 list-disc pl-5 space-y-2">
                    @forelse($sportsPreviewItems as $itemName)
                        <li>{{ $itemName }}</li>
                    @empty
                        <li>Tiada item aktif buat masa ini.</li>
                    @endforelse
                </ul>

                <!-- BUTTON -->
                <div class="flex justify-center mt-8">

                    <div class="inline-flex items-center gap-2
                                bg-[#2563eb]
                                text-white
                                text-sm font-semibold
                                px-6 py-3
                                rounded-full
                                shadow-lg shadow-blue-200/60
                                group-hover:bg-[#1d4ed8]
                                group-hover:scale-105
                                transition-all duration-300">

                        Lihat Bantuan

                        <span class="text-base">→</span>
                    </div>

                </div>

            </div>
        </a>

    </div>

</div>
@endsection
