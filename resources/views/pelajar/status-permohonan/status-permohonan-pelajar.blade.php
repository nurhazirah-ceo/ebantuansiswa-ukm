{{-- resources/views/pelajar/status-permohonan-pelajar.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">

    <x-page-hero
        class="mb-8"
        eyebrow="Pelajar"
        title="Status Permohonan"
        description="Semak perkembangan dan keputusan permohonan bantuan anda."
    />

    {{-- ================= TABLE CARD ================= --}}
    <div class="bg-white border border-slate-200 rounded-3xl shadow-lg overflow-hidden">

        {{-- Header --}}
        <div class="bg-[#071633] px-8 py-5 text-white">
            <h2 class="text-lg font-semibold tracking-tight">
                Senarai Permohonan Bantuan
            </h2>
        
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">

                {{-- Table Header --}}
                <thead class="bg-slate-100 text-slate-700 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Bil</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">No Kelompok</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Tarikh Mohon</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Jenis Bantuan</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Status Permohonan</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold">Semak Status</th>
                    </tr>
                </thead>

                {{-- Table Body --}}
                <tbody class="divide-y divide-slate-100 bg-white">

                    @forelse($permohonan as $index => $item)
                    <tr class="hover:bg-slate-50 transition">

                        {{-- Bil --}}
                        <td class="px-6 py-5 text-sm text-slate-700">
                            {{ $index + 1 }}
                        </td>

                        {{-- No Kelompok --}}
                        <td class="px-6 py-5 text-sm font-semibold text-blue-700">
                            {{ $item->no_kelompok }}
                        </td>

                        {{-- Tarikh Mohon --}}
                        <td class="px-6 py-5 text-sm text-slate-700">
                            <div>
                                {{ \Carbon\Carbon::parse($item->tarikh_mohon)->format('d/m/Y') }}
                            </div>
                        </td>

                        {{-- Jenis Bantuan --}}
                        <td class="px-6 py-5 text-sm text-slate-800">
                            {{ \App\Models\Permohonan::jenisBantuanLabel($item->jenis_bantuan) }}
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-5">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $item->status_badge_class }}">
                                {{ $item->status_label }}
                            </span>

                            <div class="text-xs text-slate-500 mt-2">
                                {{ $item->catatan }}
                            </div>
                        </td>

                        {{-- Button Lihat --}}
                        <td class="px-6 py-5 text-center">
                            <a href="{{ route('status-permohonan.show', $item->id) }}"
                               class="inline-flex items-center justify-center bg-[#071633] hover:bg-[#102544] text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
                                Lihat
                            </a>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="mx-auto max-w-md rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8">
                                <p class="text-sm font-semibold text-slate-700">Tiada permohonan ditemui.</p>
                                <p class="mt-2 text-sm text-slate-500">Permohonan bantuan anda akan dipaparkan selepas borang dihantar.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection
