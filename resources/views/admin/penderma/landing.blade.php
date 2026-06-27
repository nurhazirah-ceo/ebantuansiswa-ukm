@extends('layouts.admin')

@section('page-title', 'Pengurusan Penderma')
@section('page-subtitle', 'Urus penderma, sumbangan dan paparan homepage dengan lebih teratur.')

@section('content')

<div class="max-w-7xl mx-auto px-6 py-8">

    <x-page-hero
        class="mb-8"
        eyebrow="Penderma"
        title="Pengurusan Penderma"
        description="Pantau dan urus semua maklumat berkaitan penderma dalam sistem eBantuanSiswa UKM."
    />

    <!-- GRID MENU -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- SENARAI PENDERMA -->
        <div class="group bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300">

            <div class="w-14 h-14 rounded-xl bg-indigo-100 flex items-center justify-center mb-5">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-7 h-7 text-indigo-600"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M17 20h5V4H2v16h5m10 0v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6m10 0H7"/>
                </svg>
            </div>

            <h4 class="text-lg font-semibold text-slate-800">
                Senarai Penderma
            </h4>

            <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                Lihat semua penderma yang berdaftar dan kemas kini maklumat mereka dengan mudah.
            </p>

            <a href="{{ route('admin.penderma.index') }}"
               class="inline-flex items-center gap-2 mt-6 text-indigo-600 font-semibold hover:text-indigo-800 transition">

                Lihat Senarai

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-4 h-4"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- DAFTAR PENDERMA -->
        <div class="group bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300">

            <div class="w-14 h-14 rounded-xl bg-emerald-100 flex items-center justify-center mb-5">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-7 h-7 text-emerald-600"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 4v16m8-8H4"/>
                </svg>
            </div>

            <h4 class="text-lg font-semibold text-slate-800">
                Daftar Penderma
            </h4>

            <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                Tambah penderma baharu ke dalam sistem bagi tujuan pengurusan sumbangan.
            </p>

            <a href="{{ route('admin.penderma.create') }}"
               class="inline-flex items-center gap-2 mt-6 text-emerald-600 font-semibold hover:text-emerald-800 transition">

                Daftar Sekarang

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-4 h-4"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

    </div>

</div>

@endsection
