@extends('layouts.app')

@section('content')

@php
    $donorCampaignSlides = [
        [
            'image' => asset('image/donor-slider/poster1.jpg'),
        ],
        [
            'image' => asset('image/donor-slider/poster2.jpg'),
        ],
        [
            'image' => asset('image/donor-slider/poster3.jpg'),
        ],
    ];
    $donorCampaignPrimaryPoster = $donorCampaignSlides[0]['image'];
@endphp

<style>
    @keyframes donorFadeUp {
        from {
            opacity: 0;
            translate: 0 14px;
        }

        to {
            opacity: 1;
            translate: 0 0;
        }
    }

    @keyframes donorProgressFill {
        from {
            transform: scaleX(0);
        }

        to {
            transform: scaleX(1);
        }
    }

    .donor-summary-card {
        opacity: 0;
        animation: donorFadeUp 0.6s ease forwards;
    }

    .donor-summary-card:nth-child(2) {
        animation-delay: 0.1s;
    }

    .donor-summary-card:nth-child(3) {
        animation-delay: 0.2s;
    }

    .donor-progress-bar {
        transform: scaleX(0);
        transform-origin: left;
        animation: donorProgressFill 1s ease forwards 0.25s;
    }

    .donor-campaign-slide {
        height: 100%;
        opacity: 0;
        pointer-events: none;
        transition: none;
        width: 100%;
        z-index: 0;
    }

    .donor-campaign-slide.is-active {
        opacity: 1;
        pointer-events: auto;
        transition: opacity 700ms ease-in-out;
        z-index: 10;
    }

    .donor-campaign-slide img {
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

.donor-campaign-slide::after {
    content: "";
    position: absolute;
    top: 0;
    left: 29%;
    width: 22%;
    height: 100%;
    z-index: 5;
    pointer-events: none;
    background: linear-gradient(
        90deg,
        rgba(7, 22, 51, 0) 0%,
        rgba(15, 63, 145, 0.65) 30%,
        rgba(37, 99, 235, 0.45) 55%,
        rgba(96, 165, 250, 0.20) 78%,
        rgba(96, 165, 250, 0) 100%
    );
}

    @media (prefers-reduced-motion: reduce) {
        .donor-summary-card,
        .donor-progress-bar {
            animation: none;
            opacity: 1;
            transform: none;
            translate: 0 0;
        }
    }
</style>

<div class="min-h-screen bg-[linear-gradient(180deg,#f7fbff_0%,#eef4fb_48%,#f8fbff_100%)]">
    <div class="mx-auto max-w-[1350px] space-y-5 px-6 py-6">

        @if(session('info'))
            <div class="rounded-[1.5rem] border border-blue-200 bg-blue-50 px-5 py-4 text-sm font-medium text-blue-800">
                {{ session('info') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-3 shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
            <div class="relative overflow-hidden rounded-[1.75rem] bg-[#071633]">
                <div id="donorCampaignSlider"
                     data-primary-poster="{{ $donorCampaignPrimaryPoster }}"
                     class="relative h-[300px] overflow-hidden sm:h-[310px] lg:h-[320px]">
                    @foreach($donorCampaignSlides as $index => $slide)
                        <article class="donor-campaign-slide absolute inset-0 overflow-hidden bg-[#071633] {{ $index === 0 ? 'is-active' : '' }}"
                                 data-donor-campaign-slide
                                 aria-hidden="{{ $index === 0 ? 'false' : 'true' }}">
                            <img src="{{ $slide['image'] }}"
                                 data-donor-poster-image
                                 alt="Poster kempen penderma {{ $index + 1 }}"
                                 class="absolute inset-0">
                        </article>
                    @endforeach
                </div>

                <button type="button"
                        data-donor-campaign-prev
                        aria-label="Poster kempen sebelumnya"
                        class="absolute left-4 top-1/2 z-20 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/40 bg-white/90 text-2xl font-bold leading-none text-[#071633] shadow-lg transition duration-300 hover:scale-105 hover:bg-white">
                    &#8249;
                </button>

                <button type="button"
                        data-donor-campaign-next
                        aria-label="Poster kempen seterusnya"
                        class="absolute right-4 top-1/2 z-20 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/40 bg-white/90 text-2xl font-bold leading-none text-[#071633] shadow-lg transition duration-300 hover:scale-105 hover:bg-white">
                    &#8250;
                </button>

                <div class="absolute bottom-4 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2">
                    @foreach($donorCampaignSlides as $index => $slide)
                        <button type="button"
                                data-donor-campaign-dot="{{ $index }}"
                                aria-label="Pergi ke poster kempen {{ $index + 1 }}"
                                class="h-2.5 w-2.5 rounded-full bg-white/60 ring-1 ring-white/50 transition duration-300">
                        </button>
                    @endforeach
                </div>
            </div>
        </section>

        @php
            $donorProfile = $user->donor ?? null;
            $donorName = $user->name ?? '-';
            $donorEmail = $user->email ?? '-';
            $donorPhone = $donorProfile->phone ?? ($user->phone ?? '-');
            $donorAvatarUrl = $user->profile_photo_path
                ? asset('storage/' . $user->profile_photo_path)
                : (($donorProfile && !empty($donorProfile->logo)) ? asset('storage/' . $donorProfile->logo) : null);
            $donorInitial = strtoupper(substr($donorName !== '-' ? $donorName : 'Penderma', 0, 1));
            $donorTypeLabel = match ($donorProfile->donor_type ?? null) {
                'individual' => 'Individu',
                'individu' => 'Individu',
                'company' => 'Syarikat',
                'syarikat' => 'Syarikat',
                'organization' => 'Organisasi',
                'organisasi' => 'Organisasi',
                default => '-',
            };
            $recognitionTier = $recognition['tier'] ?? 'Belum Bermula';
            $recognitionProgress = (int) ($recognition['progress'] ?? 0);
            $displayCashFundPercent = min(100, max(0, (float) ($cashFundPercent ?? 0)));
            $displayCashFundPercentLabel = rtrim(rtrim(number_format($displayCashFundPercent, 2), '0'), '.');
        @endphp

        <section class="grid grid-cols-1 gap-5 lg:grid-cols-[280px_minmax(0,1fr)] lg:items-start">
            <aside class="w-full shrink-0">
                <div class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-lg transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <div class="relative h-28 overflow-hidden">
                        <img src="{{ asset('image/branding/ukm.jpg') }}"
                             alt="UKM"
                             class="h-full w-full object-cover">

                        <div class="absolute inset-0 bg-gradient-to-t from-[#071633]/40 via-transparent to-transparent"></div>
                    </div>

                    <div class="relative -mt-10 flex justify-center">
                        @if($donorAvatarUrl)
                            <img src="{{ $donorAvatarUrl }}"
                                 alt="Profile"
                                 class="h-20 w-20 rounded-full border-4 border-white object-cover shadow-xl">
                        @else
                            <div class="flex h-20 w-20 items-center justify-center rounded-full border-4 border-white bg-gradient-to-br from-blue-500 to-cyan-400 shadow-xl">
                                <span class="text-xl font-bold text-white">
                                    {{ $donorInitial }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="p-5 text-center">
                        <h2 class="text-lg font-extrabold text-slate-900">
                            {{ $donorName }}
                        </h2>

                        <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-green-50 px-4 py-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                            <span class="text-sm font-bold text-green-700">
                                Akaun Aktif
                            </span>
                        </div>

                        <div class="mt-5 space-y-3 rounded-2xl border border-slate-100 bg-slate-50 p-4 text-left">
                            <div class="flex justify-between gap-3">
                                <span class="text-sm text-slate-500">Jenis Penderma</span>
                                <span class="text-right text-sm font-bold text-slate-900">
                                    {{ $donorTypeLabel }}
                                </span>
                            </div>

                            <div class="flex justify-between gap-3">
                                <span class="text-sm text-slate-500">Emel</span>
                                <span class="min-w-0 break-all text-right text-xs font-bold text-slate-900">
                                    {{ $donorEmail ?: '-' }}
                                </span>
                            </div>

                            <div class="flex justify-between gap-3">
                                <span class="text-sm text-slate-500">No Telefon</span>
                                <span class="text-right text-sm font-bold text-slate-900">
                                    {{ $donorPhone ?: '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="w-full space-y-5">
                <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    @foreach($summaryCards as $index => $card)
                        @php
                            $isRecognitionCard = $card['label'] === 'Tahap Pengiktirafan';
                            $summaryLabel = $isRecognitionCard ? 'Pengiktirafan' : $card['label'];
                        @endphp

                        <div class="donor-summary-card flex min-h-[110px] items-center justify-between gap-4 rounded-[1.75rem] border border-slate-200 bg-white px-8 py-6 shadow-[0_16px_40px_rgba(15,23,42,0.06)] transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-500">
                                    {{ $summaryLabel }}
                                </p>
                                <p class="mt-3 whitespace-nowrap text-2xl font-extrabold tracking-tight {{ $card['value_classes'] }}">
                                    {{ $card['value'] }}
                                </p>
                            </div>

                            @if($isRecognitionCard)
                                <button type="button"
                                    data-open-recognition-modal
                                    aria-label="Lihat Tahap Pengiktirafan"
                                    title="Lihat Tahap Pengiktirafan"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-indigo-200 bg-white text-2xl font-semibold leading-none text-indigo-700 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:bg-indigo-50 hover:shadow-md">
                                    <span aria-hidden="true">&rsaquo;</span>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </section>
                <section class="grid grid-cols-1 items-stretch gap-5 xl:grid-cols-[minmax(0,1fr)_300px]">
                    <div class="flex min-h-full">
                        <article class="flex h-full w-full flex-col rounded-[2rem] border border-slate-200 bg-white p-5 shadow-[0_16px_40px_rgba(15,23,42,0.06)] transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-700 ring-1 ring-blue-100">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M12 8v4l3 2" stroke-linecap="round" stroke-linejoin="round"></path>
                                <circle cx="12" cy="12" r="9"></circle>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold tracking-tight text-slate-950">
                                Sejarah Sumbangan
                            </h2>
                            <p class="mt-1 text-sm leading-5 text-slate-500">
                                Rekod sumbangan dan bukti agihan berkaitan kategori yang anda sokong.
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('penderma.sejarah-sumbangan') }}"
                       class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-blue-700 transition hover:text-blue-800">
                        <span>Lihat Semua</span>
                        <span aria-hidden="true">&rsaquo;</span>
                    </a>
                </div>

                <div class="mt-5 overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-50/60">
                    @forelse($recentDonations->take(5) as $record)
                        @php
                            $recordIsCash = ($record['type'] ?? '') === 'cash';
                            $recordDetailUrl = $recordIsCash
                                ? ($record['receipt_url'] ?: route('penderma.sejarah-sumbangan'))
                                : route('penderma.sejarah-sumbangan.show', $record['id']);
                        @endphp

                        <div class="border-b border-slate-200 px-6 py-3 transition duration-300 last:border-b-0 hover:bg-white">
                            <div class="flex items-center gap-4">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-blue-700">
                                        {{ $record['no_sumbangan'] }}
                                    </p>
                                    @if(!empty($record['type_label']))
                                        <p class="mt-0.5 truncate text-sm font-medium text-slate-600">
                                            {{ $record['type_label'] }}
                                        </p>
                                    @endif
                                    <p class="mt-1 text-xs font-medium text-slate-500">
                                        {{ $record['date'] }} <span class="px-1 text-slate-300">&bull;</span>
                                        @if($recordIsCash)
                                            RM{{ number_format($record['amount'], 2) }}
                                        @else
                                            {{ number_format($record['amount'], 0) }} unit
                                        @endif
                                    </p>
                                </div>

                                <div class="hidden shrink-0 items-center gap-2 sm:flex">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $record['status_class'] }}">
                                        {{ $record['status'] }}
                                    </span>

                                    @if($recordIsCash)
                                        @if($record['receipt_url'])
                                            <a href="{{ $record['receipt_url'] }}"
                                               target="_blank"
                                               rel="noopener"
                                               class="inline-flex items-center justify-center rounded-full border border-blue-100 bg-white px-3 py-1 text-xs font-bold text-blue-700 shadow-sm transition hover:bg-blue-50">
                                                Resit
                                            </a>
                                        @else
                                            <span class="inline-flex cursor-not-allowed items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-400">
                                                Resit
                                            </span>
                                        @endif
                                    @elseif($record['proof'])
                                        <button type="button"
                                                data-open-proof-modal
                                                data-proof-url="{{ $record['proof']['url'] }}"
                                                data-proof-category="{{ $record['proof']['category'] }}"
                                                data-proof-date="{{ $record['proof']['date'] }}"
                                                data-proof-extension="{{ $record['proof']['extension'] }}"
                                                data-proof-is-image="{{ $record['proof']['is_image'] ? '1' : '0' }}"
                                                class="inline-flex items-center justify-center rounded-full border border-blue-100 bg-white px-3 py-1 text-xs font-bold text-blue-700 shadow-sm transition hover:bg-blue-50">
                                            Bukti Agihan
                                        </button>
                                    @elseif($record['receipt_url'])
                                        <a href="{{ $record['receipt_url'] }}"
                                           class="inline-flex items-center justify-center rounded-full border border-blue-100 bg-white px-3 py-1 text-xs font-bold text-blue-700 shadow-sm transition hover:bg-blue-50">
                                            Resit
                                        </a>
                                    @else
                                        <span class="inline-flex cursor-not-allowed items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-400">
                                            Bukti
                                        </span>
                                    @endif

                                    <a href="{{ $recordDetailUrl }}"
                                       @if($recordIsCash && $record['receipt_url']) target="_blank" rel="noopener" @endif
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-full text-blue-700 transition hover:bg-blue-50"
                                       aria-label="Lihat rekod {{ $record['no_sumbangan'] }}">
                                        <span class="text-xl leading-none" aria-hidden="true">&rsaquo;</span>
                                    </a>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2 sm:hidden">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $record['status_class'] }}">
                                    {{ $record['status'] }}
                                </span>

                                @if($recordIsCash)
                                    @if($record['receipt_url'])
                                        <a href="{{ $record['receipt_url'] }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="inline-flex items-center justify-center rounded-full border border-blue-100 bg-white px-3 py-1 text-xs font-bold text-blue-700 shadow-sm">
                                            Resit
                                        </a>
                                    @else
                                        <span class="inline-flex cursor-not-allowed items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-400">
                                            Resit
                                        </span>
                                    @endif
                                @elseif($record['proof'])
                                    <button type="button"
                                            data-open-proof-modal
                                            data-proof-url="{{ $record['proof']['url'] }}"
                                            data-proof-category="{{ $record['proof']['category'] }}"
                                            data-proof-date="{{ $record['proof']['date'] }}"
                                            data-proof-extension="{{ $record['proof']['extension'] }}"
                                            data-proof-is-image="{{ $record['proof']['is_image'] ? '1' : '0' }}"
                                            class="inline-flex items-center justify-center rounded-full border border-blue-100 bg-white px-3 py-1 text-xs font-bold text-blue-700 shadow-sm">
                                        Bukti Agihan
                                    </button>
                                @elseif($record['receipt_url'])
                                    <a href="{{ $record['receipt_url'] }}"
                                       class="inline-flex items-center justify-center rounded-full border border-blue-100 bg-white px-3 py-1 text-xs font-bold text-blue-700 shadow-sm">
                                        Resit
                                    </a>
                                @else
                                    <span class="inline-flex cursor-not-allowed items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-400">
                                        Bukti
                                    </span>
                                @endif

                                <a href="{{ $recordDetailUrl }}"
                                   @if($recordIsCash && $record['receipt_url']) target="_blank" rel="noopener" @endif
                                   class="ml-auto inline-flex h-8 w-8 items-center justify-center rounded-full text-blue-700"
                                   aria-label="Lihat rekod {{ $record['no_sumbangan'] }}">
                                    <span class="text-xl leading-none" aria-hidden="true">&rsaquo;</span>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <p class="text-sm font-bold text-slate-900">
                                Tiada rekod sumbangan
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                Rekod sumbangan anda akan muncul selepas transaksi pertama dibuat.
                            </p>
                        </div>
                    @endforelse
                </div>
            </article>

            </div>

            <div class="space-y-5">
                <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-[0_16px_40px_rgba(15,23,42,0.06)] transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-purple-50 text-purple-700 ring-1 ring-purple-100">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M4 7h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z" stroke-linejoin="round"></path>
                                <path d="M16 12h4" stroke-linecap="round"></path>
                                <path d="M6 7V5a2 2 0 0 1 2-2h8" stroke-linecap="round"></path>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-lg font-bold tracking-tight text-slate-950">
                                Tabung Bantuan
                            </h2>
                            <p class="mt-1 text-sm leading-5 text-slate-500">
                                Dana terkumpul untuk membantu pelajar.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex items-end justify-between gap-3">
                        <h3 class="text-3xl font-extrabold tracking-tight text-slate-950">
                            RM{{ number_format($cashFundTotal ?? 0, 2) }}
                        </h3>
                        <p class="text-sm font-extrabold text-slate-500">
                            {{ $displayCashFundPercentLabel }}%
                        </p>
                    </div>

                    <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-100">
                        <div class="donor-progress-bar h-full rounded-full bg-blue-600 transition-all duration-1000"
                             style="width: {{ $displayCashFundPercent }}%">
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between gap-3 text-sm font-medium text-slate-500">
                        <span>Progress Tabung</span>
                        <span>/ RM{{ number_format($cashFundTarget ?? 1000000, 2) }}</span>
                    </div>

                    <a href="{{ route('penderma.tabung') }}"
                       class="mt-5 inline-flex items-center justify-center rounded-full bg-blue-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                        Sumbang Ke Tabung
                    </a>
                </article>

                <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-[0_16px_40px_rgba(15,23,42,0.06)] transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex items-start gap-3">
<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-yellow-50">                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M12 15a6 6 0 1 0 0-12 6 6 0 0 0 0 12Z"></path>
                                <path d="m9 14-1 7 4-2 4 2-1-7" stroke-linejoin="round"></path>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-lg font-bold tracking-tight text-slate-950">
                                Sijil Penghargaan
                            </h2>
                            <p class="mt-1 text-sm leading-5 text-slate-500">
                                Tahap pengiktirafan kini.
                            </p>
                        </div>
                    </div>

                    @if($totalCompletedAmount > 0)
<div class="mt-4 rounded-[1.5rem] border border-amber-200 bg-gradient-to-br from-amber-50 via-yellow-50 to-white p-4">
    <p class="text-xs font-bold uppercase tracking-[0.14em] text-amber-600">
        Tahap Semasa
    </p>

    <h3 class="mt-1 text-2xl font-extrabold text-amber-700">
        {{ $recognitionTier }}
    </h3>

    <p class="mt-1 text-xs font-semibold text-slate-500">
        RM{{ number_format($totalCompletedAmount, 2) }} selesai
    </p>

    <a href="{{ route('penderma.sijil-penghargaan.download') }}"
       data-confirm-certificate-download
       class="mt-4 inline-flex items-center justify-center rounded-full border border-amber-300 bg-white px-4 py-2 text-sm font-bold text-amber-700 shadow-sm transition hover:bg-amber-500 hover:text-white hover:border-amber-500">
        Muat Turun Sijil
    </a>
</div>
                    @else
                        <div class="mt-4 rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-4 text-sm leading-6 text-slate-500">
                            Sijil penghargaan akan tersedia selepas sumbangan pertama anda selesai direkodkan.
                        </div>
                    @endif
                </article>
            </div>
                </section>
            </div>
        </section>



<div id="recognitionModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/60 px-4 py-6">
    <div class="w-full max-w-2xl rounded-[2rem] bg-white p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Tahap Pengiktirafan</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Tahap semasa: <span class="font-semibold text-slate-900">{{ $recognition['tier'] }}</span>
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Jumlah sumbangan selesai: <span class="font-semibold text-slate-900">RM{{ number_format($totalCompletedAmount, 2) }}</span>
                </p>
            </div>

            <button type="button" data-close-recognition-modal class="rounded-full bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-600 hover:bg-slate-200">
                Tutup
            </button>
        </div>

        <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Jumlah Sumbangan</th>
                        <th class="px-4 py-3 text-left font-semibold">Tahap</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($recognitionLevels as $level)
                        <tr class="{{ $level['tier'] === $recognition['tier'] ? 'bg-blue-50' : '' }}">
                            <td class="px-4 py-3 text-slate-700">{{ $level['range'] }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $level['tier'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="proofModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/60 px-4 py-6">
    <div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Bukti Agihan</h2>
                <p class="mt-1 text-sm text-slate-500" id="proofModalMeta">-</p>
            </div>
            <button type="button" data-close-proof-modal class="rounded-full bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-600 hover:bg-slate-200">
                Tutup
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-auto bg-slate-50 p-6">
            <img id="proofModalImage" src="" alt="Bukti agihan" class="hidden max-h-[65vh] w-full rounded-2xl border border-slate-200 bg-white object-contain">
            <iframe id="proofModalPdf" src="" title="Bukti agihan PDF" class="hidden h-[65vh] w-full rounded-2xl border border-slate-200 bg-white"></iframe>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const donorCampaignSlider = document.getElementById('donorCampaignSlider');
    const donorCampaignSlides = donorCampaignSlider
        ? Array.from(donorCampaignSlider.querySelectorAll('[data-donor-campaign-slide]'))
        : [];
    const donorCampaignDots = Array.from(document.querySelectorAll('[data-donor-campaign-dot]'));
    const donorCampaignPrev = document.querySelector('[data-donor-campaign-prev]');
    const donorCampaignNext = document.querySelector('[data-donor-campaign-next]');
    const donorCampaignReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let donorCampaignIndex = 0;
    let donorCampaignTimer = null;

    function updateDonorCampaignSlider(index) {
        if (!donorCampaignSlider || donorCampaignSlides.length === 0) {
            return;
        }

        donorCampaignIndex = (index + donorCampaignSlides.length) % donorCampaignSlides.length;

        donorCampaignSlides.forEach(function (slide, slideIndex) {
            const isActive = slideIndex === donorCampaignIndex;

            slide.classList.toggle('is-active', isActive);
            slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        });

        donorCampaignDots.forEach(function (dot, dotIndex) {
            const isActive = dotIndex === donorCampaignIndex;

            dot.classList.toggle('w-6', isActive);
            dot.classList.toggle('bg-white', isActive);
            dot.classList.toggle('bg-white/60', !isActive);
            dot.setAttribute('aria-current', isActive ? 'true' : 'false');
        });
    }

    function nextDonorCampaignSlide() {
        updateDonorCampaignSlider(donorCampaignIndex + 1);
    }

    function startDonorCampaignTimer() {
        if (donorCampaignSlides.length <= 1 || donorCampaignReducedMotion) {
            return;
        }

        window.clearInterval(donorCampaignTimer);
        donorCampaignTimer = window.setInterval(nextDonorCampaignSlide, 5000);
    }

    function restartDonorCampaignTimer() {
        window.clearInterval(donorCampaignTimer);
        startDonorCampaignTimer();
    }

    if (donorCampaignSlider) {
        const primaryPoster = donorCampaignSlider.dataset.primaryPoster;
        const donorCampaignShell = donorCampaignSlider.closest('section');

        donorCampaignSlider.querySelectorAll('[data-donor-poster-image]').forEach(function (image) {
            image.addEventListener('error', function () {
                if (!image.dataset.usedPrimaryFallback && image.getAttribute('src') !== primaryPoster) {
                    image.dataset.usedPrimaryFallback = '1';
                    image.src = primaryPoster;
                    return;
                }

                image.classList.add('hidden');
            });
        });

        donorCampaignPrev?.addEventListener('click', function () {
            updateDonorCampaignSlider(donorCampaignIndex - 1);
            restartDonorCampaignTimer();
        });

        donorCampaignNext?.addEventListener('click', function () {
            nextDonorCampaignSlide();
            restartDonorCampaignTimer();
        });

        donorCampaignDots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                updateDonorCampaignSlider(Number(dot.dataset.donorCampaignDot || 0));
                restartDonorCampaignTimer();
            });
        });

        if (donorCampaignSlides.length <= 1) {
            donorCampaignPrev?.classList.add('hidden');
            donorCampaignNext?.classList.add('hidden');
            donorCampaignDots.forEach(function (dot) {
                dot.classList.add('hidden');
            });
        }

        donorCampaignShell?.addEventListener('mouseenter', function () {
            window.clearInterval(donorCampaignTimer);
        });

        donorCampaignShell?.addEventListener('mouseleave', startDonorCampaignTimer);
        donorCampaignShell?.addEventListener('focusin', function () {
            window.clearInterval(donorCampaignTimer);
        });
        donorCampaignShell?.addEventListener('focusout', startDonorCampaignTimer);

        updateDonorCampaignSlider(0);
        startDonorCampaignTimer();
    }

    const recognitionModal = document.getElementById('recognitionModal');
    const proofModal = document.getElementById('proofModal');
    const proofImage = document.getElementById('proofModalImage');
    const proofPdf = document.getElementById('proofModalPdf');
    const proofMeta = document.getElementById('proofModalMeta');

    document.querySelector('[data-open-recognition-modal]')?.addEventListener('click', function () {
        recognitionModal.classList.remove('hidden');
        recognitionModal.classList.add('flex');
    });

    document.querySelector('[data-close-recognition-modal]')?.addEventListener('click', function () {
        recognitionModal.classList.add('hidden');
        recognitionModal.classList.remove('flex');
    });

    document.querySelectorAll('[data-open-proof-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            const proofUrl = button.dataset.proofUrl;
            const isImage = button.dataset.proofIsImage === '1';
            const category = button.dataset.proofCategory || '-';
            const date = button.dataset.proofDate || '-';

            proofMeta.textContent = category + ' • ' + date;
            proofImage.classList.toggle('hidden', !isImage);
            proofPdf.classList.toggle('hidden', isImage);

            if (isImage) {
                proofImage.src = proofUrl;
                proofPdf.src = '';
            } else {
                proofPdf.src = proofUrl;
                proofImage.src = '';
            }

            proofModal.classList.remove('hidden');
            proofModal.classList.add('flex');
        });
    });

    document.querySelector('[data-close-proof-modal]')?.addEventListener('click', function () {
        proofModal.classList.add('hidden');
        proofModal.classList.remove('flex');
        proofImage.src = '';
        proofPdf.src = '';
    });

    document.querySelector('[data-confirm-certificate-download]')?.addEventListener('click', function (event) {
        event.preventDefault();

        const downloadUrl = event.currentTarget.href;

        Swal.fire({
            icon: 'question',
            title: 'Muat turun sijil?',
            text: 'Adakah anda ingin memuat turun sijil berbentuk PDF?',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak',
            confirmButtonColor: '#1D4ED8',
            cancelButtonColor: '#64748b'
        }).then(function (result) {
            if (result.isConfirmed) {
                window.location.href = downloadUrl;
            }
        });
    });
});
</script>

@endsection
