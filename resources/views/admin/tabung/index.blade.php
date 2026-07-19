@extends('layouts.admin')

@section('page-title', 'Sumbangan Tabung')

@section('content')

@php
    $statusLabels = [
        'success' => 'Selesai',
        'pending' => 'Menunggu Bayaran',
        'failed' => 'Gagal',
    ];
    $statusClasses = [
        'success' => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
        'pending' => 'bg-amber-100 text-amber-700 border border-amber-200',
        'failed' => 'bg-rose-100 text-rose-700 border border-rose-200',
    ];
    $targetErrors = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $targetInputValue = old('target_amount', number_format((float) ($tabungTarget ?? 1000000), 2, '.', ''));
    $status = $status ?? null;
    $q = $q ?? '';
    $activeTransactionFilters = [];
    $statusFilterQuery = filled($q) ? ['q' => $q] : [];
    $statusOnlyQuery = filled($status) ? ['status' => $status] : [];

    if (filled($status)) {
        $activeTransactionFilters['status'] = $status;
    }

    if (filled($q)) {
        $activeTransactionFilters['q'] = $q;
    }
@endphp

<div class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-7xl space-y-7 px-6">
<x-page-hero
    class="relative"
    eyebrow="PENDERMA"
    title="Sumbangan Tabung"
    description="Senarai sumbangan wang umum yang direkod melalui Tabung Bantuan Pelajar."
>
    <button type="button"
            data-open-tabung-target-modal
            class="mt-6 inline-flex items-center justify-center rounded-2xl bg-[#3155E7] px-6 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-[#2647D6] lg:absolute lg:right-8 lg:top-1/2 lg:mt-0 lg:-translate-y-1/2">
        + Tetapkan Target Tabung
    </button>
</x-page-hero>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Dana Terkumpul</p>
                <h2 class="mt-3 text-3xl font-bold text-slate-950">RM{{ number_format($summary['total_success'], 2) }}</h2>
                <p class="mt-3 text-sm text-slate-500">
                    Sasaran tabung: <span class="font-semibold text-slate-900">RM{{ number_format($tabungTarget, 2) }}</span>
                </p>
                <div class="mt-4">
                    <div class="flex justify-between text-xs font-semibold text-slate-500">
                        <span>Progress</span>
                        <span>{{ number_format($tabungProgress, 2) }}%</span>
                    </div>
                    <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-blue-600 transition-all duration-1000"
                             style="width: {{ $tabungProgress }}%"></div>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-emerald-700">Transaksi Berjaya</p>
                <h2 class="mt-3 text-3xl font-bold text-emerald-700">{{ number_format($summary['success_count']) }}</h2>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-amber-700">Menunggu Bayaran</p>
                <h2 class="mt-3 text-3xl font-bold text-amber-700">{{ number_format($summary['pending_count']) }}</h2>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-rose-700">Gagal</p>
                <h2 class="mt-3 text-3xl font-bold text-rose-700">{{ number_format($summary['failed_count']) }}</h2>
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-5 border-b border-slate-200 px-6 py-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 flex-1">
                    <h2 class="text-lg font-bold text-slate-950">Senarai Transaksi Tabung</h2>
                    <p class="mt-1 text-sm text-slate-500">Nama penderma, jumlah, status, tarikh dan rujukan transaksi.</p>

                    <form method="GET" action="{{ route('admin.tabung.index') }}" class="mt-5 flex w-full flex-col gap-2 sm:flex-row lg:max-w-3xl">
                        @if(filled($status))
                            <input type="hidden" name="status" value="{{ $status }}">
                        @endif

                        <label for="tabung-search" class="sr-only">Cari transaksi tabung</label>
                        <input id="tabung-search"
                               type="search"
                               name="q"
                               value="{{ $q }}"
                               placeholder="Cari penderma, transaksi atau bill code"
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100 sm:flex-1">

                        <div class="flex flex-wrap gap-2">
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-2xl bg-[#3155E7] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#2647D6]">
                                Cari
                            </button>

                            @if(filled($q))
                                <a href="{{ route('admin.tabung.index', $statusOnlyQuery) }}"
                                   class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="flex w-full flex-col gap-3 lg:w-auto lg:items-end">
                    <a href="{{ route('admin.tabung.export', $activeTransactionFilters) }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                        Export CSV
                    </a>

                    <div class="flex flex-wrap gap-2">
                        @foreach(['' => 'Semua', 'success' => 'Selesai', 'failed' => 'Gagal'] as $key => $label)
                            <a href="{{ route('admin.tabung.index', $key === '' ? $statusFilterQuery : array_merge($statusFilterQuery, ['status' => $key])) }}"
                               class="rounded-full px-4 py-2 text-sm font-semibold transition {{ (string) $status === (string) $key || ($key === '' && blank($status)) ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-100 text-left text-slate-700">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Nama Penderma</th>
                            <th class="px-5 py-4 font-semibold">Jumlah</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 font-semibold">Tarikh</th>
                            <th class="px-5 py-4 font-semibold">Rujukan / Transaksi / Bill Code</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($cashDonations as $donation)
                            @php
                                $statusLabel = $statusLabels[$donation->payment_status] ?? ucfirst($donation->payment_status);
                                $statusClass = $statusClasses[$donation->payment_status] ?? 'bg-slate-100 text-slate-700 border border-slate-200';
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">{{ $donation->user?->name ?? 'Penderma' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $donation->user?->email ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-900">
                                    RM{{ number_format((float) $donation->amount, 2) }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-700">
                                    {{ optional($donation->resolved_at ?? $donation->paid_at ?? $donation->created_at)->format('d/m/Y h:i A') }}
                                </td>
                                <td class="px-5 py-4 text-slate-700">
                                    <p class="break-all font-semibold">{{ $donation->reference_no ?: '-' }}</p>
                                    <p class="mt-1 break-all text-xs text-slate-500">{{ $donation->transaction_id ?: '-' }}</p>
                                    <p class="mt-1 break-all text-xs text-slate-500">{{ $donation->bill_code ?: '-' }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <div class="mx-auto max-w-md rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8">
                                        <p class="text-sm font-semibold text-slate-700">Tiada rekod sumbangan tabung ditemui.</p>
                                        <p class="mt-2 text-sm text-slate-500">Rekod akan dipaparkan di sini selepas transaksi tabung dibuat.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-6 py-5">
                {{ $cashDonations->links() }}
            </div>
        </section>
    </div>
</div>

<div id="tabungTargetModal"
     class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/60 px-4 py-6">
    <div class="w-full max-w-md rounded-[2rem] bg-white p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Tetapkan Target Tabung</h2>
                <p class="mt-1 text-sm text-slate-500">Kemaskini sasaran kutipan tabung bantuan.</p>
            </div>
            <button type="button"
                    data-close-tabung-target-modal
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-xl font-bold text-slate-500 transition hover:bg-slate-200">
                &times;
            </button>
        </div>

        <form method="POST" action="{{ route('admin.tabung.target.update') }}" class="mt-6 space-y-5">
            @csrf
            @method('PATCH')

            <div>
                <label for="target_amount" class="block text-sm font-semibold text-slate-900">Sasaran Tabung (RM)</label>
                <input id="target_amount"
                       name="target_amount"
                       type="number"
                       min="1"
                       step="0.01"
                       value="{{ $targetInputValue }}"
                       class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                       required>
                @if($targetErrors->has('target_amount'))
                    <p class="mt-2 text-sm font-semibold text-rose-600">{{ $targetErrors->first('target_amount') }}</p>
                @endif
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button"
                        data-close-tabung-target-modal
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-[#3155E7] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#2647D6]">
                    Simpan Target
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('tabungTargetModal');
    const openButtons = document.querySelectorAll('[data-open-tabung-target-modal]');
    const closeButtons = document.querySelectorAll('[data-close-tabung-target-modal]');

    function openModal() {
        modal?.classList.remove('hidden');
        modal?.classList.add('flex');
    }

    function closeModal() {
        modal?.classList.add('hidden');
        modal?.classList.remove('flex');
    }

    openButtons.forEach(button => button.addEventListener('click', openModal));
    closeButtons.forEach(button => button.addEventListener('click', closeModal));

    modal?.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    @if($targetErrors->has('target_amount'))
        openModal();
    @endif
});
</script>

@endsection
