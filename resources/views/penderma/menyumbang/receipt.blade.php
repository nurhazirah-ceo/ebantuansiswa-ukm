@extends('layouts.app')

@section('content')

@php
    $receiptDate = $sumbangan->paid_at ?? $sumbangan->updated_at ?? $sumbangan->created_at;
    $paymentPayload = $sumbangan->payment_payload ?? [];
    $rawPaymentMethod = (string) ($sumbangan->kaedah_sumbangan ?? '');
    $normalizedPaymentMethod = strtolower(trim($rawPaymentMethod));
    $isSimulationPayment = in_array($normalizedPaymentMethod, ['simulasi', 'simulasi pembayaran', 'pembayaran atas talian'], true)
        || data_get($paymentPayload, 'method') === 'simulasi';
    $paymentReference = $sumbangan->payment_reference ?: ($sumbangan->toyyibpay_bill_code ?: '-');
    $billCode = $sumbangan->toyyibpay_bill_code ?: '-';
    $paymentMethod = $isSimulationPayment
        ? 'Pembayaran Atas Talian'
        : ($sumbangan->kaedah_sumbangan ?: 'ToyyibPay');
    $statusLabel = $sumbangan->status === 'selesai' ? 'Selesai' : ucfirst(str_replace('_', ' ', $sumbangan->status));
    $donorSnapshot = $sumbangan->donor_snapshot ?? [];
    $donorName = data_get($donorSnapshot, 'name') ?: (optional($sumbangan->user)->name ?? auth()->user()?->name ?? 'Penderma');
    $donorEmail = data_get($donorSnapshot, 'email') ?: (optional($sumbangan->user)->email ?? auth()->user()?->email ?? '-');
    $donorPhone = data_get($donorSnapshot, 'phone') ?: '-';
    $donorAltPhone = data_get($donorSnapshot, 'alt_phone') ?: null;
    $donorCityLine = trim((string) data_get($donorSnapshot, 'postcode') . ' ' . (string) data_get($donorSnapshot, 'city'));
    $donorAddress = collect([
        data_get($donorSnapshot, 'address'),
        $donorCityLine,
        data_get($donorSnapshot, 'state'),
        data_get($donorSnapshot, 'country'),
    ])
        ->filter(fn ($line) => filled($line))
        ->implode("\n");
    $noSumbangan = $sumbangan->no_sumbangan ?? ('SMB-' . str_pad($sumbangan->id, 6, '0', STR_PAD_LEFT));
    $categoryLabels = collect(\App\Models\Item::DONATION_CATEGORIES)
        ->mapWithKeys(fn ($category, $key) => [$key => $category['title']]);
    $categoryText = $sumbangan->items
        ->pluck('kategori_bantuan')
        ->unique()
        ->map(fn ($category) => $categoryLabels->get($category, $category))
        ->implode(', ');
    $categoryText = $categoryText !== '' ? $categoryText : 'Sumbangan Barang';
@endphp

<div class="min-h-screen bg-[linear-gradient(180deg,#f7fbff_0%,#eef4fb_48%,#f8fbff_100%)] py-6">
    <div class="max-w-5xl mx-auto px-6">

        <div class="relative mb-5">
            <a href="{{ route('penderma.sejarah-sumbangan') }}"
               aria-label="Kembali"
               class="mb-3 inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#3155E7] text-2xl font-black leading-none text-white shadow-md transition hover:bg-[#2647D6] md:absolute md:left-0 md:top-2.5 md:mb-0 md:-translate-x-[calc(100%+0.5rem)]">
                &larr;
            </a>

            <section class="rounded-[1.5rem] bg-[#071633] px-6 py-7 text-white shadow-lg">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-cyan-200">
                            RESIT SUMBANGAN
                        </p>
                    </div>

                    <a href="{{ route('penderma.sumbangan.receipt.download', ['id' => $sumbangan->id]) }}"
                       class="inline-flex min-h-[48px] cursor-pointer items-center justify-center gap-2.5 rounded-full bg-gradient-to-r from-[#2563EB] via-[#1D4ED8] to-[#1E40AF] px-6 py-3 text-sm font-semibold text-white shadow-[0_12px_26px_rgba(29,78,216,0.26)] transition duration-200 hover:-translate-y-0.5 hover:from-[#1D4ED8] hover:via-[#1E40AF] hover:to-[#1E3A8A] hover:shadow-[0_16px_32px_rgba(29,78,216,0.32)] focus:outline-none focus:ring-4 focus:ring-blue-200">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M12 3v12" stroke-linecap="round"></path>
                            <path d="m7 10 5 5 5-5" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M5 21h14" stroke-linecap="round"></path>
                        </svg>
                        Muat Turun Resit
                    </a>
                </div>

            <h1 class="mt-5 text-3xl font-semibold tracking-tight text-white"
                style="font-family: 'Poppins', sans-serif;">
                Sumbangan Berjaya
            </h1>
            <p class="mt-2 max-w-3xl text-sm leading-5 text-slate-200"
               style="font-family: 'Poppins', sans-serif;">
                Rekod sumbangan anda telah berjaya diproses.
            </p>
            </section>
        </div>
        
    

        <div class="rounded-[2rem] border border-slate-200 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.08)] overflow-hidden">
            <div class="bg-[#071633] px-7 py-7 text-white">
                <div class="flex items-start justify-between gap-5 flex-wrap">
                    <div>
                        <p class="text-sm text-blue-100">No. Sumbangan</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight">{{ $noSumbangan }}</p>
                        <p class="mt-3 text-sm text-slate-200">
                            {{ optional($receiptDate)->format('d/m/Y h:i A') }}
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-sm text-blue-100">Jumlah Bayaran</p>
                        <p class="mt-2 text-4xl font-bold tracking-tight">
                            RM{{ number_format((float) $sumbangan->jumlah_keseluruhan, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-7 space-y-6">
                <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="rounded-3xl border border-[#BFDBFE] bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF] p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Nama Penderma</p>
                        <p class="text-xl font-semibold text-[#071633] mt-3">{{ $donorName }}</p>
                        <p class="text-sm text-slate-500 mt-1">{{ $donorEmail }}</p>
                        <p class="text-sm text-slate-500 mt-1">{{ $donorPhone }}</p>
                    </div>

                    <div class="rounded-3xl border border-[#BFDBFE] bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF] p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Jenis Sumbangan</p>
                        <p class="text-xl font-semibold text-[#071633] mt-3">{{ $categoryText }}</p>
                    </div>

                    <div class="rounded-3xl border border-[#BFDBFE] bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF] p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Kaedah Bayaran</p>
                        <p class="text-xl font-semibold text-[#071633] mt-3">{{ $paymentMethod }}</p>
                        @unless($isSimulationPayment)
                            <p class="mt-1 text-xs text-slate-500 break-all">Bil: {{ $billCode }}</p>
                        @endunless
                    </div>

                    <div class="rounded-3xl border border-[#BFDBFE] bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF] p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Status</p>
                        <p class="text-xl font-semibold text-emerald-700 mt-3">{{ $statusLabel }}</p>
                        <p class="mt-1 text-xs text-slate-500 break-all">{{ $paymentReference }}</p>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#BFDBFE] bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF] p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Alamat Penyumbang</p>
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 font-semibold text-[#071633]">{{ $donorAddress ?: '-' }}</p>
                    @if($donorAltPhone)
                        <p class="mt-2 text-sm text-slate-500">Telefon alternatif: {{ $donorAltPhone }}</p>
                    @endif
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="hidden md:grid md:grid-cols-[minmax(0,1.5fr)_120px_150px_160px] bg-slate-100 text-slate-700 text-sm font-semibold border-b border-slate-200">
                        <div class="px-5 py-4 border-r border-slate-200">Item</div>
                        <div class="px-5 py-4 border-r border-slate-200">Kuantiti</div>
                        <div class="px-5 py-4 border-r border-slate-200">Harga Unit</div>
                        <div class="px-5 py-4">Jumlah</div>
                    </div>

                    <div class="divide-y divide-slate-200">
                        @foreach($sumbangan->items as $item)
                            <div class="md:grid md:grid-cols-[minmax(0,1.5fr)_120px_150px_160px] items-center">
                                <div class="px-5 py-5">
                                    <p class="font-semibold text-slate-900">{{ $item->nama_item }}</p>
                                    <p class="text-sm text-slate-500 mt-1">{{ $item->kategori_bantuan }}</p>
                                </div>
                                <div class="px-5 py-5 text-sm text-slate-700">{{ $item->kuantiti }} unit</div>
                                <div class="px-5 py-5 text-sm text-slate-700">RM{{ number_format((float) $item->harga_unit, 2) }}</div>
                                <div class="px-5 py-5 text-lg font-semibold text-[#1D4ED8]">RM{{ number_format((float) $item->jumlah, 2) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 flex-wrap rounded-3xl border border-slate-900 bg-white p-6 shadow-sm">
                    <div>
                        <p class="text-sm text-slate-500">Jumlah Unit</p>
                        <p class="text-2xl font-semibold text-slate-900 mt-2">{{ $sumbangan->jumlah_unit }} unit</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-500">Jumlah Keseluruhan</p>
                        <p class="text-3xl font-bold text-slate-900 mt-2">RM{{ number_format((float) $sumbangan->jumlah_keseluruhan, 2) }}</p>
                    </div>
                </div>

                @if($isSimulationPayment)
                    <div class="rounded-3xl border border-cyan-200 bg-cyan-50 px-5 py-4 text-sm leading-6 text-cyan-800">
                        Resit ini dijana melalui aliran pembayaran atas talian.
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@if (session('payment_success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if (session('clear_checkout'))
    localStorage.removeItem('cart');
    [
        'checkout_cart',
        'checkout_summary',
        'checkout_method',
        'checkout_donor'
    ].forEach(key => sessionStorage.removeItem(key));

    window.dispatchEvent(new CustomEvent('cart-updated', {
        detail: []
    }));
    @endif

    Swal.fire({
        icon: 'success',
        title: @json(session('payment_success')),
        confirmButtonText: 'Teruskan',
        confirmButtonColor: '#2563eb',
        background: '#ffffff',
        color: '#0f172a',
        width: 420,
        padding: '1.6rem',
        customClass: {
            popup: 'rounded-3xl shadow-xl',
            title: 'text-2xl font-bold text-slate-900',
            confirmButton: 'rounded-2xl px-7 py-3 font-semibold shadow'
        }
    });
});
</script>
@endif

@endsection
