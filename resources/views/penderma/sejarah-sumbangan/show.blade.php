@extends('layouts.app')

@section('content')

@php
    $distributionImpact = $distributionImpact ?? [];
    $categoryLabels = collect(\App\Models\Item::DONATION_CATEGORIES)
        ->mapWithKeys(fn ($category, $key) => [$key => $category['title']]);
    $statusLabels = [
        'selesai' => 'Selesai',
        'menunggu_bayaran' => 'Menunggu Bayaran',
        'menunggu_penghantaran' => 'Menunggu Penghantaran',
        'dalam_semakan' => 'Dalam Semakan',
        'dibatalkan' => 'Dibatalkan',
        'ditolak' => 'Ditolak',
    ];
    $statusClasses = [
        'selesai' => 'bg-green-100 text-green-700',
        'menunggu_bayaran' => 'bg-amber-100 text-amber-700',
        'menunggu_penghantaran' => 'bg-blue-100 text-blue-700',
        'dalam_semakan' => 'bg-yellow-100 text-yellow-700',
        'dibatalkan' => 'bg-red-100 text-red-700',
        'ditolak' => 'bg-red-100 text-red-700',
    ];
    $statusLabel = $statusLabels[$sumbangan->status] ?? ucfirst(str_replace('_', ' ', $sumbangan->status));
    $statusClass = $statusClasses[$sumbangan->status] ?? 'bg-slate-100 text-slate-700';
    $noSumbangan = $sumbangan->no_sumbangan ?? ('SMB-' . str_pad($sumbangan->id, 6, '0', STR_PAD_LEFT));
    $categoryText = $sumbangan->items
        ->pluck('kategori_bantuan')
        ->unique()
        ->map(fn ($category) => $categoryLabels->get($category, $category))
        ->implode(', ');
    $categoryText = $categoryText !== '' ? $categoryText : '-';
    $title = $categoryText !== '-' ? 'Sumbangan ' . $categoryText : 'Sumbangan Bantuan';
    $paymentPayload = $sumbangan->payment_payload ?? [];
    $rawMethod = (string) ($sumbangan->kaedah_sumbangan ?? '');
    $normalizedMethod = strtolower(trim($rawMethod));
    $method = in_array($normalizedMethod, ['simulasi', 'simulasi pembayaran', 'pembayaran atas talian'], true)
        || data_get($paymentPayload, 'method') === 'simulasi'
            ? 'Pembayaran Atas Talian'
            : ($rawMethod ?: 'Tidak dinyatakan');
    $defaultNotes = match ($sumbangan->status) {
        'selesai' => 'Sumbangan telah dibayar dan stok item bantuan telah dikemaskini.',
        'menunggu_bayaran' => 'Sumbangan telah direkodkan dan sedang menunggu pengesahan bayaran ToyyibPay.',
        'dibatalkan' => 'Pembayaran tidak berjaya atau telah dibatalkan. Stok item bantuan tidak dikemaskini.',
        default => 'Sumbangan telah direkodkan.',
    };
    $notes = $sumbangan->catatan ?: $defaultNotes;
    $totalUnits = (int) $sumbangan->jumlah_unit;
    $paymentReference = $sumbangan->payment_reference ?: ($sumbangan->toyyibpay_bill_code ?: '-');
    $donorSnapshot = $sumbangan->donor_snapshot ?? [];
    $donorName = data_get($donorSnapshot, 'name') ?: (auth()->user()?->name ?? 'Penderma');
    $donorEmail = data_get($donorSnapshot, 'email') ?: (auth()->user()?->email ?? '-');
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
    $progress = [
        [
            'label' => 'Rekod Dicipta',
            'time' => optional($sumbangan->created_at)->format('d/m/Y h:i A'),
        ],
        [
            'label' => $statusLabel,
            'time' => optional($sumbangan->updated_at)->format('d/m/Y h:i A'),
        ],
    ];
@endphp

<div class="min-h-screen bg-[linear-gradient(180deg,#f7fbff_0%,#eef4fb_48%,#f8fbff_100%)] py-6">
    <div class="max-w-6xl mx-auto px-6">

        <div class="relative">
            <a href="{{ route('penderma.sejarah-sumbangan') }}"
               aria-label="Kembali"
               class="mb-3 inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#3155E7] text-2xl font-black leading-none text-white shadow-md transition hover:bg-[#2647D6] md:absolute md:left-0 md:top-0 md:mb-0 md:-translate-x-[calc(100%+0.75rem)]">
                &larr;
            </a>

            <div class="rounded-[2rem] border border-slate-200 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.08)] overflow-hidden">
            <div class="bg-[#071633] px-6 py-7 text-white sm:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-cyan-200">RESIT SUMBANGAN</p>
                        <h1 class="mt-5 text-3xl font-semibold tracking-tight text-white">{{ $title }}</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-5 text-slate-200">
                            Rekod sumbangan anda telah berjaya diproses.
                        </p>
                        <div class="mt-3 flex items-center gap-3 flex-wrap">
                            <p class="text-sm text-slate-200">{{ $noSumbangan }}</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
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
            </div>

            <div class="p-7 space-y-6">
                <div class="grid md:grid-cols-4 gap-4">
                    <div class="rounded-3xl border border-[#BFDBFE] bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF] p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Jenis Sumbangan</p>
                        <p class="text-xl font-semibold text-[#071633] mt-3">{{ $categoryText }}</p>
                    </div>

                    <div class="rounded-3xl border border-[#BFDBFE] bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF] p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Kaedah</p>
                        <p class="text-xl font-semibold text-[#071633] mt-3">{{ $method }}</p>
                    </div>

                    <div class="rounded-3xl border border-[#BFDBFE] bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF] p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Status</p>
                        <div class="mt-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-[#BFDBFE] bg-gradient-to-br from-[#F8FBFF] to-[#EEF5FF] p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Rujukan Bayaran</p>
                        <p class="text-xl font-semibold text-[#071633] mt-3 break-all">{{ $paymentReference }}</p>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Maklumat Agihan Sumbangan</h2>
                            <p class="text-sm leading-6 text-slate-500 mt-2">
                                Paparan ini menerangkan proses agihan bagi sumbangan yang telah anda berikan.
                            </p>
                        </div>
                    </div>

                    @if($sumbangan->status !== 'selesai')
                        <div class="mt-5 rounded-2xl border border-amber-100 bg-amber-50 px-5 py-4 text-sm leading-6 text-amber-800">
                            Impak sumbangan akan dipaparkan selepas pembayaran sumbangan disahkan selesai.
                        </div>
                    @elseif(empty($distributionImpact))
                        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm leading-6 text-slate-600">
                            <span class="font-semibold text-slate-800">Menunggu Agihan.</span>
                            Maklumat penerima dan bukti agihan akan dipaparkan selepas admin melengkapkan agihan bantuan untuk kategori sumbangan ini.
                        </div>
                    @else
                        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm leading-6 text-emerald-800">
                            <div class="flex gap-3">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M9 12.75 11.25 15 15 9.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <p>
                                    Terima kasih atas sumbangan anda. Setiap sumbangan yang diterima akan diuruskan oleh pihak pentadbir dan disalurkan kepada pelajar yang layak mengikut kategori bantuan. Semoga sumbangan ini dapat memberikan manfaat kepada pelajar yang memerlukan.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="grid lg:grid-cols-[1.05fr_0.95fr] gap-6">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-4 mb-4">
                            <h2 class="text-lg font-semibold text-slate-900">Jenis Item</h2>
                            <div class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold border border-blue-100">
                                {{ $totalUnits }} unit
                            </div>
                        </div>

                        <div class="space-y-3">
                            @foreach($sumbangan->items as $item)
                                <div class="rounded-2xl border border-slate-200 bg-[linear-gradient(180deg,#ffffff_0%,#f8fbff_100%)] px-5 py-4 flex items-center justify-between gap-4 shadow-sm">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $item->nama_item }}</p>
                                        <p class="text-sm text-slate-500 mt-1">{{ $item->kuantiti }} unit x RM{{ number_format((float) $item->harga_unit, 2) }}</p>
                                    </div>
                                    <p class="text-lg font-semibold text-[#1D4ED8]">RM{{ number_format((float) $item->jumlah, 2) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-3xl border border-slate-200 p-6 bg-[linear-gradient(180deg,_#ffffff_0%,_#f8fbff_100%)] shadow-sm">
                            <h2 class="text-lg font-semibold text-slate-900 mb-4">Jejak Rekod</h2>
                            <div class="space-y-5">
                                @foreach($progress as $step)
                                    <div class="flex gap-4">
                                        <div class="relative mt-1 shrink-0">
                                            <div class="w-4 h-4 rounded-full bg-[#1D4ED8] ring-4 ring-blue-50"></div>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $step['label'] }}</p>
                                            <p class="text-sm text-slate-500 mt-1">{{ $step['time'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-3xl border border-slate-200 bg-[linear-gradient(180deg,_#f8fbff_0%,_#f1f7ff_100%)] p-6 shadow-sm">
                            <h2 class="text-lg font-semibold text-slate-900 mb-3">Maklumat Penyumbang</h2>
                            <div class="space-y-3 text-sm leading-6 text-slate-600">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Nama</p>
                                    <p class="font-semibold text-slate-900">{{ $donorName }}</p>
                                    <p>{{ $donorEmail }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Telefon</p>
                                    <p>{{ $donorPhone }}</p>
                                    @if($donorAltPhone)
                                        <p>Alternatif: {{ $donorAltPhone }}</p>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Alamat</p>
                                    <p class="whitespace-pre-line">{{ $donorAddress ?: '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-slate-200 bg-[linear-gradient(180deg,_#f8fbff_0%,_#f1f7ff_100%)] p-6 shadow-sm">
                            <h2 class="text-lg font-semibold text-slate-900 mb-3">Catatan</h2>
                            <p class="text-sm leading-7 text-slate-600">{{ $notes }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

    </div>
</div>

@endsection
