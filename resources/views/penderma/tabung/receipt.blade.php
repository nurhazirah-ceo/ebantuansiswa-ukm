@extends('layouts.app')

@section('content')

@php
    $receiptDate = $cashDonation->paid_at ?? $cashDonation->updated_at ?? $cashDonation->created_at;
    $statusLabel = $cashDonation->payment_status === \App\Models\CashDonation::STATUS_SUCCESS
        ? 'Selesai'
        : ucfirst(str_replace('_', ' ', (string) $cashDonation->payment_status));
    $donorName = optional($cashDonation->user)->name ?? auth()->user()->name ?? 'Penderma';
    $donorEmail = optional($cashDonation->user)->email ?? auth()->user()->email ?? '-';
    $note = $cashDonation->message ?: '-';
    $isLegacyDemoRecord = (bool) data_get($cashDonation->raw_response, 'demo_mode', false);
    $paymentMethod = $cashDonation->bill_code
        ? 'ToyyibPay'
        : ($isLegacyDemoRecord ? 'Simulasi Pembayaran' : 'Pembayaran Atas Talian');
@endphp

<div class="min-h-screen bg-[linear-gradient(180deg,#f7fbff_0%,#eef4fb_48%,#f8fbff_100%)] py-6">
    <div class="max-w-5xl mx-auto px-6">

        <div class="relative mb-5">
            <a href="{{ route('penderma.sejarah-sumbangan') }}"
               aria-label="Kembali"
               class="mb-3 inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#3155E7] text-2xl font-black leading-none text-white shadow-md transition hover:bg-[#2647D6] md:absolute md:left-0 md:top-2.5 md:mb-0 md:-translate-x-[calc(100%+0.5rem)]">
                &larr;
            </a>

            <section class="rounded-[1.5rem] bg-[#071633] px-6 py-5 text-white shadow-lg">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-cyan-200">
                        RESIT SUMBANGAN
                    </p>
                </div>

                <h1 class="mt-4 text-2xl font-semibold tracking-tight text-white">
                    Sumbangan Direkod
                </h1>
                <p class="mt-1.5 max-w-3xl text-sm leading-5 text-slate-200">
                    @if($isLegacyDemoRecord)
                        Rekod ini dijana melalui aliran simulasi lama.
                    @else
                        Bayaran ini telah disahkan melalui ToyyibPay.
                    @endif
                </p>
            </section>
        </div>

        <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
            <div class="bg-[#071633] px-7 py-7 text-white">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-blue-100">No. Sumbangan</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight">{{ $reference }}</p>
                        <p class="mt-3 text-sm text-slate-200">
                            {{ optional($receiptDate)->format('d/m/Y h:i A') }}
                        </p>
                    </div>

                    <a href="{{ route('penderma.tabung.receipt.download', $cashDonation) }}"
                       class="inline-flex min-h-[48px] cursor-pointer items-center justify-center gap-2.5 rounded-full bg-gradient-to-r from-[#2563EB] via-[#1D4ED8] to-[#1E40AF] px-6 py-3 text-sm font-semibold text-white shadow-[0_12px_26px_rgba(29,78,216,0.26)] transition duration-200 hover:-translate-y-0.5 hover:from-[#1D4ED8] hover:via-[#1E40AF] hover:to-[#1E3A8A] hover:shadow-[0_16px_32px_rgba(29,78,216,0.32)] focus:outline-none focus:ring-4 focus:ring-blue-200">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M12 3v12" stroke-linecap="round"></path>
                            <path d="m7 10 5 5 5-5" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M5 21h14" stroke-linecap="round"></path>
                        </svg>
                        Muat Turun Resit
                    </a>
                </div>
            </div>

            <div class="p-7 space-y-6">
                <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="rounded-3xl border border-[#BFDBFE] bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF] p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Nama Penderma</p>
                        <p class="text-xl font-semibold text-[#071633] mt-3">{{ $donorName }}</p>
                        <p class="text-sm text-slate-500 mt-1">{{ $donorEmail }}</p>
                    </div>

                    <div class="rounded-3xl border border-[#BFDBFE] bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF] p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Jenis Sumbangan</p>
                        <p class="text-xl font-semibold text-[#071633] mt-3">Tabung Bantuan</p>
                    </div>

                    <div class="rounded-3xl border border-[#BFDBFE] bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF] p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Kaedah Bayaran</p>
                        <p class="text-xl font-semibold text-[#071633] mt-3">{{ $paymentMethod }}</p>
                    </div>

                    <div class="rounded-3xl border border-[#BFDBFE] bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF] p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Status</p>
                        <p class="text-xl font-semibold text-emerald-700 mt-3">{{ $statusLabel }}</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="grid grid-cols-[minmax(0,1fr)_180px] bg-slate-100 text-slate-700 text-sm font-semibold border-b border-slate-200">
                        <div class="px-5 py-4 border-r border-slate-200">Butiran</div>
                        <div class="px-5 py-4 text-right">Jumlah</div>
                    </div>

                    <div class="grid grid-cols-[minmax(0,1fr)_180px] items-center">
                        <div class="px-5 py-5">
                            <p class="font-semibold text-slate-900">Sumbangan Tabung Bantuan Pelajar</p>
                            <p class="text-sm text-slate-500 mt-1">Catatan: {{ $note }}</p>
                        </div>
                        <div class="px-5 py-5 text-right text-lg font-semibold text-[#1D4ED8]">
                            RM{{ number_format((float) $cashDonation->amount, 2) }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 flex-wrap rounded-3xl border border-slate-900 bg-white p-6 shadow-sm">
                    <div>
                        <p class="text-sm text-slate-500">Rujukan Transaksi</p>
                        <p class="text-xl font-semibold text-slate-900 mt-2 break-all">{{ $cashDonation->transaction_id ?: $reference }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-500">Jumlah Keseluruhan</p>
                        <p class="text-3xl font-bold text-slate-900 mt-2">RM{{ number_format((float) $cashDonation->amount, 2) }}</p>
                    </div>
                </div>

                @if($isLegacyDemoRecord)
                    <div class="rounded-3xl border border-cyan-200 bg-cyan-50 px-5 py-4 text-sm leading-6 text-cyan-800">
                        Resit ini ialah rekod simulasi lama dan bukan bukti bayaran gateway sebenar.
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
