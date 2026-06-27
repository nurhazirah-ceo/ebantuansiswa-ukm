@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-[linear-gradient(180deg,#f7fbff_0%,#eef4fb_48%,#f8fbff_100%)] py-8 sm:py-10">
    <div class="relative mx-auto max-w-4xl px-6">
        <a href="{{ route('penderma.sejarah-sumbangan') }}"
           aria-label="Kembali ke Sejarah Sumbangan"
           class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-[#1D4ED8] text-white shadow-[0_12px_30px_rgba(29,78,216,0.28)] transition hover:bg-[#1E40AF] focus:outline-none focus:ring-4 focus:ring-blue-200 lg:absolute lg:left-0 lg:top-6 lg:mb-0">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                <path d="M19 12H5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="m12 19-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </a>

        <section class="mx-auto max-w-3xl overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
            <div class="bg-[#071633] px-7 py-7 text-white sm:px-8 sm:py-8">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-cyan-200">
                    Status Pembayaran
                </p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-white" style="font-family: 'Poppins', sans-serif;">
                    Tabung Bantuan Pelajar
                </h1>
            </div>

            <div class="p-6 sm:p-7">
                <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-50/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm leading-7 text-slate-600">
                        {{ $message }}
                    </p>

                    <span class="inline-flex w-fit shrink-0 rounded-full px-4 py-2 text-sm font-semibold {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                @if($cashDonation)
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
                            <p class="text-sm text-slate-500">No. Rujukan</p>
                            <p class="mt-2 break-all text-lg font-semibold text-slate-900">{{ $reference }}</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
                            <p class="text-sm text-slate-500">Jumlah</p>
                            <p class="mt-2 text-lg font-semibold text-slate-900">RM{{ number_format((float) $cashDonation->amount, 2) }}</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
                            <p class="text-sm text-slate-500">Kod Bil</p>
                            <p class="mt-2 break-all text-lg font-semibold text-slate-900">{{ $cashDonation->bill_code ?: '-' }}</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
                            <p class="text-sm text-slate-500">Transaksi</p>
                            <p class="mt-2 break-all text-lg font-semibold text-slate-900">{{ $cashDonation->transaction_id ?: '-' }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>

@endsection
