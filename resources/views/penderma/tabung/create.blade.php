@extends('layouts.app')

@section('content')

@php
    $selectedAmount = old('amount_choice', '20');
@endphp

<div class="min-h-screen bg-[linear-gradient(180deg,#f7fbff_0%,#eef4fb_48%,#f8fbff_100%)] py-10">
    <div class="mx-auto max-w-5xl px-6">
        <div class="mb-6">
            <a href="{{ route('dashboard.penderma') }}"
               class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Kembali
            </a>
        </div>

        <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
            <div class="bg-[#071633] px-10 py-12 text-white">
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-cyan-200">
                    Tabung Bantuan Pelajar
                </p>
                <h1 class="mt-5 text-4xl font-extrabold tracking-tight text-white">
                    Sumbangan Wang Umum
                </h1>
                <p class="mt-4 max-w-4xl text-sm leading-6 text-slate-200">
                    Dana tabung akan direkod dalam sistem dan dipantau oleh admin untuk membantu permohonan pelajar yang telah disahkan.
                </p>
            </div>

            <div class="grid gap-0 lg:grid-cols-[0.95fr_1.05fr]">
                <div class="border-b border-slate-200 bg-slate-50/80 p-7 lg:border-b-0 lg:border-r">
                    <h2 class="text-xl font-bold text-slate-900">
                        Maklumat Tabung
                    </h2>

                    <div class="mt-5 space-y-3 text-sm leading-6 text-slate-700">
                        <p class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3">
                            Tabung ini ialah pilihan tambahan untuk sumbangan wang umum.
                        </p>
                        <p class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                            Anda akan dibawa ke halaman ToyyibPay untuk melengkapkan pembayaran.
                        </p>
                        <p class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
                            Sistem menggunakan ToyyibPay Development environment untuk tujuan FYP/demo.
                        </p>
                        <p class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                            Aliran simulator tidak menolak wang sebenar, dan dana hanya dikira selepas ToyyibPay mengesahkan bayaran berjaya.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('penderma.tabung.store') }}" id="cashDonationForm" class="p-7">
                    @csrf

                    @if($errors->any())
                        <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div>
                        <label class="text-sm font-semibold text-slate-900">
                            Pilih Jumlah Sumbangan
                        </label>

                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach($amountOptions as $amount)
                                <label class="group cursor-pointer">
                                    <input type="radio"
                                           name="amount_choice"
                                           value="{{ $amount }}"
                                           class="peer sr-only"
                                           {{ (string) $selectedAmount === (string) $amount ? 'checked' : '' }}>
                                    <span class="flex h-16 items-center justify-center rounded-2xl border border-slate-200 bg-white text-lg font-bold text-slate-800 shadow-sm transition peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-700 group-hover:border-blue-200">
                                        RM{{ $amount }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <label class="mt-3 block cursor-pointer">
                            <input type="radio"
                                   name="amount_choice"
                                   value="custom"
                                   class="peer sr-only"
                                   {{ $selectedAmount === 'custom' ? 'checked' : '' }}>
                            <span class="block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition peer-checked:border-blue-600 peer-checked:bg-blue-50">
                                <span class="block text-sm font-semibold text-slate-700">Custom Amount</span>
                                <span class="mt-3 flex items-center gap-3">
                                    <span class="text-sm font-semibold text-slate-500">RM</span>
                                    <input type="number"
                                           name="custom_amount"
                                           min="1"
                                           step="0.01"
                                           value="{{ old('custom_amount') }}"
                                           placeholder="Masukkan jumlah"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
                                </span>
                            </span>
                        </label>
                    </div>

                    <div class="mt-6">
                        <label for="message" class="text-sm font-semibold text-slate-900">
                            Catatan
                            <span class="font-normal text-slate-400">(optional)</span>
                        </label>
                        <textarea id="message"
                                  name="message"
                                  rows="4"
                                  maxlength="1000"
                                  placeholder="Tulis catatan ringkas"
                                  class="mt-3 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100">{{ old('message') }}</textarea>
                    </div>

                    <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-slate-500">
                            Pembayaran akan diproses melalui ToyyibPay Development dan disahkan melalui callback ToyyibPay.
                        </p>

                        <button type="button"
                                id="cashDonationSubmitButton"
                                class="inline-flex items-center justify-center rounded-2xl bg-[#1D4ED8] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1E40AF]">
                            Bayar Melalui ToyyibPay
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const customInput = document.querySelector('input[name="custom_amount"]');
    const customRadio = document.querySelector('input[name="amount_choice"][value="custom"]');
    const cashDonationForm = document.getElementById('cashDonationForm');
    const cashDonationSubmitButton = document.getElementById('cashDonationSubmitButton');
    let confirmedCashDonation = false;

    customInput?.addEventListener('focus', function () {
        customRadio.checked = true;
    });

    cashDonationSubmitButton?.addEventListener('click', function () {
        cashDonationForm?.requestSubmit();
    });

    cashDonationForm?.addEventListener('submit', function (event) {
        if (confirmedCashDonation) {
            confirmedCashDonation = false;
            return;
        }

        event.preventDefault();

        Swal.fire({
            icon: 'question',
            title: 'Teruskan pembayaran?',
            text: 'Adakah anda ingin teruskan pembayaran?',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak',
            confirmButtonColor: '#1D4ED8',
            cancelButtonColor: '#64748b'
        }).then(function (result) {
            if (result.isConfirmed) {
                confirmedCashDonation = true;
                cashDonationForm.requestSubmit();
            }
        });
    });
});
</script>

@endsection
