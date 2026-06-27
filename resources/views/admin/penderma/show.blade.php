@extends('layouts.admin')

@section('page-title', 'Butiran Penderma')
@section('page-subtitle', 'Lihat maklumat lengkap penderma dan tetapan homepage.')

@section('content')

<div class="py-6">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <a href="{{ route('admin.penderma.index') }}"
           class="text-sm font-semibold text-blue-600 hover:underline">
            ← Kembali ke Senarai
        </a>

        <x-page-hero
            eyebrow="Penderma"
            :title="$donor->user->name"
            description="Lihat maklumat lengkap penderma dan tetapan paparan homepage."
        />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- PROFILE CARD --}}
            <div class="bg-white border rounded-2xl shadow-sm p-6 lg:col-span-1">
                <div class="flex flex-col items-center text-center">

                    @if ($donor->logo)
                        <img src="{{ asset('storage/' . $donor->logo) }}"
                             class="w-40 h-40 rounded-2xl object-contain border bg-gray-50 p-4">
                    @else
                        <div class="w-40 h-40 rounded-2xl bg-gray-100 border flex items-center justify-center text-gray-400">
                            Tiada Logo
                        </div>
                    @endif

                    <h2 class="mt-5 text-xl font-bold text-gray-900">
                        {{ $donor->user->name }}
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        {{ $donor->user->email }}
                    </p>

                    <div class="mt-4">
                        @if ($donor->show_on_homepage)
                            <span class="inline-flex px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                Dipaparkan di Homepage
                            </span>
                        @else
                            <span class="inline-flex px-3 py-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded-full">
                                Tidak Dipaparkan
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- DONOR DETAILS --}}
            <div class="bg-white border rounded-2xl shadow-sm p-6 lg:col-span-2">
                <h3 class="text-lg font-bold text-gray-900 mb-5">
                    Maklumat Penderma
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">

                    <div>
                        <p class="text-gray-500">Jenis Penderma</p>
                        <p class="font-semibold text-gray-900 capitalize">
                            {{ $donor->donor_type }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">No. Telefon</p>
                        <p class="font-semibold text-gray-900">
                            {{ $donor->phone ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">No. Telefon Alternatif</p>
                        <p class="font-semibold text-gray-900">
                            {{ $donor->alt_phone ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">Nama Wakil Organisasi</p>
                        <p class="font-semibold text-gray-900">
                            {{ $donor->representative_name ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">Saluran Komunikasi</p>
                        <p class="font-semibold text-gray-900 capitalize">
                            {{ $donor->preferred_contact }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">Status Akaun</p>
                        <p class="font-semibold text-gray-900 capitalize">
                            {{ $donor->user->account_status }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">Tarikh Daftar</p>
                        <p class="font-semibold text-gray-900">
                            {{ $donor->user->created_at->format('d M Y') }}
                        </p>
                    </div>

                </div>
            </div>

        </div>

        {{-- SUPPORT DOCUMENT --}}
        <div class="bg-white border rounded-2xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-5">
                Dokumen Sokongan
            </h3>

            @if ($donor->support_document)
                <div class="flex flex-wrap gap-3">
                    <a href="{{ asset('storage/' . $donor->support_document) }}"
                       target="_blank"
                       rel="noopener"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700">
                        Lihat Dokumen
                    </a>

                    <a href="{{ asset('storage/' . $donor->support_document) }}"
                       download
                       class="inline-flex items-center px-4 py-2 border text-sm font-semibold rounded-lg hover:bg-gray-50">
                        Download
                    </a>
                </div>
            @else
                <p class="text-sm text-gray-500">
                    Tiada dokumen dimuat naik.
                </p>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- HOMEPAGE SETTINGS --}}
            <div class="bg-white border rounded-2xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-5">
                    Tetapan Homepage
                </h3>

                <div class="space-y-4 text-sm">

                    <div>
                        <p class="text-gray-500">Ranking Homepage</p>
                        <p class="font-semibold text-gray-900">
                            {{ $donor->homepage_order ? '#' . $donor->homepage_order : '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">Paparan Homepage</p>
                        <p class="font-semibold text-gray-900">
                            {{ $donor->show_on_homepage ? 'Ya' : 'Tidak' }}
                        </p>
                    </div>

                </div>
            </div>

            {{-- ADDRESS --}}
            <div class="bg-white border rounded-2xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-5">
                    Alamat
                </h3>

                @if ($donor->address)
                    <div class="text-sm text-gray-800 leading-7">
                        <p>{{ $donor->address->address_line_1 }}</p>

                        @if ($donor->address->address_line_2)
                            <p>{{ $donor->address->address_line_2 }}</p>
                        @endif

                        <p>
                            {{ $donor->address->postcode }},
                            {{ $donor->address->city }},
                            {{ $donor->address->state }}
                        </p>

                        <p>{{ $donor->address->country }}</p>
                    </div>
                @else
                    <p class="text-sm text-gray-500">
                        Tiada alamat direkodkan.
                    </p>
                @endif
            </div>

        </div>

        {{-- ADMIN NOTE --}}
        <div class="bg-white border rounded-2xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-3">
                Catatan Admin
            </h3>

            <p class="text-sm text-gray-700 whitespace-pre-line">
                {{ $donor->admin_note ?: 'Tiada catatan.' }}
            </p>
        </div>

    </div>
</div>

@endsection
