@extends('layouts.app')

@section('content')

@php
    $records = $records ?? collect();
    $records = collect($records);
    $historyStats = $historyStats ?? [
        'active_count' => $records->where('status_category', 'pending')->count(),
        'completed_count' => $records->where('status_category', 'success')->count(),
        'total_amount' => (float) $records->sum('amount'),
    ];
    $userName = auth()->user()->name ?? 'Penderma';
    $userEmail = auth()->user()->email ?? '-';
    $userPhotoUrl = auth()->user()?->profile_photo_path
        ? asset('storage/' . auth()->user()->profile_photo_path)
        : null;
    $completedCount = $historyStats['completed_count'];
    $activeCount = $historyStats['active_count'];
    $totalAmount = (float) $historyStats['total_amount'];
@endphp

<div class="min-h-screen bg-[linear-gradient(180deg,#f7fbff_0%,#eef4fb_48%,#f8fbff_100%)] py-10">
    <div class="max-w-7xl mx-auto px-6">

        <x-page-hero
            class="mb-8"
            eyebrow="Penderma"
            title="Sejarah Sumbangan"
            description="Semak rekod semua sumbangan yang telah dibuat."
        />

        <div class="rounded-[2rem] border border-slate-200 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.08)] p-6 mb-6">
            <div class="flex items-start justify-between gap-6 flex-wrap">
                <div class="flex items-start gap-4">
                    <div class="w-16 h-16 rounded-full border-2 border-[#1D4ED8] shrink-0 overflow-hidden bg-[radial-gradient(circle_at_top,_#dbeafe,_#93c5fd_55%,_#1d4ed8_56%,_#eff6ff_100%)] shadow-inner flex items-center justify-center">
                        @if($userPhotoUrl)
                            <img src="{{ $userPhotoUrl }}"
                                 alt="{{ $userName }}"
                                 class="w-full h-full object-cover">
                        @else
                            <span class="text-white text-xl font-bold">
                                {{ strtoupper(substr($userName, 0, 1)) }}
                            </span>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h2 class="text-2xl font-semibold text-slate-900">{{ $userName }}</h2>
                            <span class="px-4 py-1.5 rounded-xl border border-[#BFDBFE] bg-[#EFF6FF] text-[#1D4ED8] text-sm font-semibold">
                                Penderma Aktif
                            </span>
                        </div>
                        <div class="mt-3 flex items-center gap-5 text-slate-500 text-sm flex-wrap">
                            <span>{{ $userEmail }}</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('penderma.sumbangan') }}"
                   class="px-5 py-3 rounded-2xl bg-[#1D4ED8] text-white font-semibold hover:bg-[#1E40AF] transition">
                    + Buat Sumbangan Baru
                </a>
            </div>

            <div class="grid md:grid-cols-3 gap-4 mt-6">
                <div class="rounded-2xl border border-[#BFDBFE] bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF] px-5 py-4">
                    <p class="text-slate-500 text-sm">Sumbangan Masih Proses</p>
                    <p class="text-3xl font-semibold text-[#071633] mt-2">{{ $activeCount }}</p>
                </div>

                <div class="rounded-2xl border border-[#BFDBFE] bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF] px-5 py-4">
                    <p class="text-slate-500 text-sm">Sumbangan Selesai</p>
                    <p class="text-3xl font-semibold text-[#071633] mt-2">{{ $completedCount }}</p>
                </div>

                <div class="rounded-2xl border border-[#BFDBFE] bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF] px-5 py-4">
                    <p class="text-slate-500 text-sm">Jumlah Keseluruhan Sumbangan</p>
                    <p class="text-3xl font-semibold text-[#071633] mt-2">RM{{ number_format($totalAmount, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl shadow-lg overflow-hidden">
            <div class="bg-[#11244a] px-8 py-5 text-white">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold tracking-tight">
                            Sejarah Sumbangan
                        </h2>
                        <p class="mt-1 text-sm text-slate-300">
                            Rekod semua sumbangan yang telah dibuat.
                        </p>
                    </div>

                    <a href="{{ route('penderma.sejarah-sumbangan.export') }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-[#0f52d9] px-6 py-4 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-100 hover:text-[#071633]">
                        Export
                    </a>
                </div>
            </div>

            <div class="px-6 py-6">
            <div class="flex items-center justify-between gap-4 flex-wrap mb-5">
                <div class="relative w-full max-w-md">
                    <input type="text"
                        id="historySearch"
                        placeholder="Cari melalui kata kunci"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-3.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-[#071633] focus:ring-2 focus:ring-[#DBEAFE]">
                </div>

                <div class="text-sm text-slate-500">
                    <span id="historyCount">{{ $records->count() }}</span> rekod ditemui
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200">
                <div class="hidden md:grid md:grid-cols-[1.25fr_0.95fr_0.85fr_0.75fr_0.9fr_0.9fr_0.8fr] bg-slate-100 text-slate-700 text-sm font-semibold border-b border-slate-200">
                    <div class="px-5 py-4 border-r border-slate-200">ID Sumbangan</div>
                    <div class="px-5 py-4 border-r border-slate-200">Jenis</div>
                    <div class="px-5 py-4 border-r border-slate-200">Tarikh</div>
                    <div class="px-5 py-4 border-r border-slate-200">Jumlah Unit</div>
                    <div class="px-5 py-4 border-r border-slate-200">Jumlah</div>
                    <div class="px-5 py-4 border-r border-slate-200">Status</div>
                    <div class="px-5 py-4">Lihat</div>
                </div>

                <div id="historyRows" class="divide-y divide-slate-200">
                    @forelse($records as $record)
                        <div class="history-row block"
                            data-search="{{ $record['search_text'] }}">
                            <div class="md:grid md:grid-cols-[1.25fr_0.95fr_0.85fr_0.75fr_0.9fr_0.9fr_0.8fr] items-start hover:bg-slate-50 transition">
                                <div class="px-5 py-5">
                                    <p class="text-sm font-semibold text-blue-700 break-all">{{ $record['no_sumbangan'] }}</p>
                                </div>
                                <div class="px-5 py-5 text-sm text-slate-700">
                                    <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                        {{ $record['type_label'] }}
                                    </span>
                                </div>
                                <div class="px-5 py-5 text-sm text-slate-800">
                                    {{ $record['date_label'] }}
                                </div>
                                <div class="px-5 py-5 text-sm text-slate-700">
                                    {{ $record['unit_label'] }}
                                </div>
                                <div class="px-5 py-5 text-sm text-slate-700">
                                    RM{{ number_format((float) $record['amount'], 2) }}
                                </div>
                                <div class="px-5 py-5">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $record['status_class'] }}">
                                        {{ $record['status_label'] }}
                                    </span>
                                </div>
                                <div class="px-5 py-5">
                                    @if($record['view_url'])
                                        <a href="{{ $record['view_url'] }}"
                                            class="inline-flex items-center justify-center bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
                                            {{ $record['action_label'] }}
                                        </a>
                                    @else
                                        <span class="inline-flex cursor-not-allowed items-center justify-center rounded-xl bg-slate-200 px-4 py-2 text-sm font-medium text-slate-500">
                                            {{ $record['action_label'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                    @endforelse
                </div>

                <div id="historyEmptyState" class="hidden bg-white px-5 py-12 text-center">
                    <div class="mx-auto max-w-md rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8">
                        <p class="text-sm font-semibold text-slate-700">
                            {{ $records->isEmpty() ? 'Tiada rekod sumbangan direkodkan lagi.' : 'Tiada rekod yang sepadan dengan carian anda.' }}
                        </p>
                        <p class="mt-2 text-sm text-slate-500">
                            {{ $records->isEmpty() ? 'Sumbangan anda akan dipaparkan selepas direkodkan.' : 'Cuba gunakan kata kunci carian yang lain.' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-between gap-4 flex-wrap text-sm text-slate-500">
                <p id="historyFootnote">Menunjukkan {{ $records->isEmpty() ? 0 : 1 }} hingga {{ $records->count() }} daripada {{ $records->count() }} rekod</p>
                <span class="text-slate-400">Klik `Lihat` untuk buka maklumat terperinci.</span>
            </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = Array.from(document.querySelectorAll('.history-row'));
    const searchInput = document.getElementById('historySearch');
    const historyCount = document.getElementById('historyCount');
    const historyFootnote = document.getElementById('historyFootnote');
    const emptyState = document.getElementById('historyEmptyState');

    function updateVisibleCount() {
        const visibleRows = rows.filter(row => row.style.display !== 'none');
        historyCount.textContent = visibleRows.length;
        historyFootnote.textContent = `Menunjukkan ${visibleRows.length ? 1 : 0} hingga ${visibleRows.length} daripada ${rows.length} rekod`;
        emptyState.classList.toggle('hidden', visibleRows.length !== 0);
    }

    searchInput.addEventListener('input', function () {
        const keyword = this.value.trim().toLowerCase();

        rows.forEach(row => {
            row.style.display = row.dataset.search.includes(keyword) ? 'block' : 'none';
        });

        updateVisibleCount();
    });

    updateVisibleCount();
});
</script>

@endsection
