@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#f4f7fb]">

    @php
        $dashboardUser = auth()->user();
        abort_if(!$dashboardUser, 403);

        $dashboardPhotoUrl = $dashboardUser->profile_photo_path
            ? asset('storage/' . $dashboardUser->profile_photo_path)
            : null;

        $lockedBantuanTypes = $lockedBantuanTypes ?? [];
        $latestPermohonan = $latestPermohonan ?? null;
        $academicSession = \App\Support\StudentAcademicProfile::academicSession();
    @endphp

    <div class="max-w-[1350px] mx-auto px-6 pt-6">
        <x-page-hero
            eyebrow="Dashboard Pelajar"
            :title="'Selamat Datang, ' . $dashboardUser->name"
            description="Urus permohonan bantuan pelajar anda dengan mudah dan pantas."
        />
    </div>

    <!-- MAIN -->
    <div class="max-w-[1350px] mx-auto px-6 py-6 space-y-6">

        {{-- ================= PENGUMUMAN POSTER SLIDER ================= --}}
        <section class="mb-4 bg-white rounded-[2rem] shadow-lg border border-slate-200 p-4">
            {{-- HEADER --}}
            <div class="flex items-center justify-between mb-5">

                <div>
                    <p class="text-[14px] font-semibold text-blue-600 mb-1">
                        Makluman
                    </p>

                    <h2 class="text-2xl font-extrabold text-slate-900 leading-tight">
                        Pengumuman Terkini
                    </h2>
                </div>

                <span class="text-[14px] text-slate-400">
                    Slide Poster
                </span>

            </div>

            {{-- SLIDER --}}
        <div class="relative overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white flex items-center justify-center max-h-[420px]">        {{-- SLIDES --}}
                <div id="announcementSlider"
                    class="flex transition-transform duration-700 ease-in-out">

                    {{-- POSTER 1 --}}
        <div class="min-w-full flex items-center justify-center bg-white">
            <img src="/image/announcements/poster1.jpg"
                alt="Pengumuman 1"
                class="w-full max-h-[420px] object-contain bg-white">
        </div>

        {{-- POSTER 2 --}}
        <div class="min-w-full flex items-center justify-center bg-white">
            <img src="/image/announcements/poster2.jpg"
                alt="Pengumuman 2"
                class="w-full max-h-[420px] object-contain bg-white">
        </div>

        {{-- POSTER 3 --}}
        <div class="min-w-full flex items-center justify-center bg-white">
            <img src="/image/announcements/poster3.jpg"
                alt="Pengumuman 3"
                class="w-full max-h-[420px] object-contain bg-white">
        </div>

        {{-- POSTER 4 --}}
        <div class="min-w-full flex items-center justify-center bg-white">
            <img src="/image/announcements/poster4.jpg"
                alt="Pengumuman 4"
                class="w-full max-h-[420px] object-contain bg-white">
        </div>

                </div>

        {{-- BUTTON LEFT --}}
        <button onclick="prevAnnouncementSlide()"
            class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 shadow border border-slate-200 flex items-center justify-center text-slate-700 hover:bg-white transition">

            ❮
        </button>

        {{-- BUTTON RIGHT --}}
        <button onclick="nextAnnouncementSlide()"
            class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 shadow border border-slate-200 flex items-center justify-center text-slate-700 hover:bg-white transition">

            ❯
        </button>

        {{-- DOTS --}}
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">

            <button type="button"
                onclick="goAnnouncementSlide(0)"
                class="announcement-dot w-2.5 h-2.5 rounded-full bg-blue-600">
            </button>

            <button type="button"
                onclick="goAnnouncementSlide(1)"
                class="announcement-dot w-2.5 h-2.5 rounded-full bg-white/70">
            </button>

            <button type="button"
                onclick="goAnnouncementSlide(2)"
                class="announcement-dot w-2.5 h-2.5 rounded-full bg-white/70">
            </button>

            <button type="button"
                onclick="goAnnouncementSlide(3)"
                class="announcement-dot w-2.5 h-2.5 rounded-full bg-white/70">
            </button>

        </div>

    </div>

</section>


        

 <!-- CONTENT WITH PROFILE + RIGHT SIDE -->
        <div class="flex flex-col lg:flex-row gap-6 items-start">

            <!-- PROFILE -->
            <aside class="w-full lg:w-[260px] shrink-0">
                <div class="bg-white rounded-[1.5rem] shadow-lg border border-slate-200 overflow-hidden">

                    <div class="relative h-28 overflow-hidden">
                        <img src="{{ asset('image/branding/ukm.jpg') }}"
                             alt="UKM"
                             class="w-full h-full object-cover">

                        <div class="absolute inset-0 bg-gradient-to-t from-[#071633]/40 via-transparent to-transparent"></div>
                    </div>

                    <div class="relative -mt-10 flex justify-center">
                        @if($dashboardPhotoUrl)
                            <img src="{{ $dashboardPhotoUrl }}"
                                 alt="Profile"
                                 class="w-20 h-20 rounded-full border-4 border-white object-cover shadow-xl">
                        @else
                            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-cyan-400 border-4 border-white shadow-xl flex items-center justify-center">
                                <span class="text-white text-xl font-bold">
                                    {{ strtoupper(substr($dashboardUser->name ?? 'P', 0, 1)) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="p-5 text-center">
                        <h2 class="text-xl font-extrabold text-slate-900">
                            {{ $dashboardUser->name }}
                        </h2>

                        <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-green-50 px-4 py-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                            <span class="text-green-700 font-bold text-sm">
                                Akaun Aktif
                            </span>
                        </div>

                        <div class="mt-5 rounded-2xl bg-slate-50 border border-slate-100 p-4 text-left space-y-3">
                            <div class="flex justify-between gap-3">
                                <span class="text-slate-500 text-sm">No Matrik</span>
                                <span class="font-bold text-slate-900 text-sm text-right break-words">
                                    {{ filled($dashboardUser->matrik) ? $dashboardUser->matrik : '-' }}
                                </span>
                            </div>

                            <div class="flex items-start justify-between gap-3">
                                <span class="text-slate-500 text-sm">Fakulti</span>
                                <span class="font-bold text-slate-900 text-sm text-right break-words">
                                    {{ filled($dashboardUser->fakulti) ? $dashboardUser->fakulti : 'Tidak lengkap' }}
                                </span>
                            </div>

                            <div class="flex justify-between gap-3">
                                <span class="text-slate-500 text-sm">Sesi</span>
                                <span class="font-bold text-slate-900 text-sm text-right">
                                    {{ $academicSession }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </aside>

            <!-- RIGHT CONTENT -->
            <main class="flex-1 w-full space-y-6">

                <!-- MENU -->
<section class="w-full bg-white rounded-[1.7rem] shadow-lg border border-slate-200 p-6">

    @php
        $dashboardBantuan = [
            [
                'value' => 'bantuan_asas_hidup',
                'name' => 'Bantuan Asas Hidup',
                'description' => 'Bantuan barangan asas untuk keperluan harian pelajar.',
            ],
            [
                'value' => 'bantuan_pembelajaran',
                'name' => 'Bantuan Pembelajaran',
                'description' => 'Sokongan kelengkapan pembelajaran dan akademik.',
            ],
            [
                'value' => 'bantuan_sukan',
                'name' => 'Bantuan Sukan',
                'description' => 'Bantuan kelengkapan aktiviti dan penyertaan sukan.',
            ],
            [
                'value' => 'bantuan_musibah',
                'name' => 'Bantuan Musibah',
                'description' => 'Bantuan segera untuk pelajar yang ditimpa musibah.',
            ],
        ];
    @endphp

    <div class="flex items-start justify-between mb-5">

        <div>
            <p class="text-[14px] font-semibold text-blue-600 mb-1">
                Akses Pantas
            </p>

            <h2 class="text-2xl font-extrabold text-slate-900">
                Senarai Bantuan
            </h2>

            <p class="text-slate-500 mt-1 text-sm">
                Pilih jenis bantuan yang ingin dipohon.
            </p>
        </div>

        <span class="text-xs text-slate-400">
            Permohonan Bantuan
        </span>

    </div>

    <form action="{{ route('permohonan.index') }}" method="GET">

        <div class="overflow-hidden rounded-2xl border border-slate-200">

            <table class="w-full text-sm">

                <thead class="bg-slate-100">
                    <tr>

                        <th class="px-5 py-4 text-left font-bold text-slate-700">
                            Jenis Bantuan
                        </th>

                        <th class="px-5 py-4 text-left font-bold text-slate-700">
                            Keterangan
                        </th>

                        <th class="px-5 py-4 text-center font-bold text-slate-700">
                            Pilih
                        </th>

                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @foreach($dashboardBantuan as $index => $bantuan)
                        @php
                            $lockedInfo = $lockedBantuanTypes[$bantuan['value']] ?? null;
                            $isLocked = $lockedInfo !== null;
                        @endphp

                        <tr
                            class="dashboard-bantuan-row bg-white hover:bg-blue-50/40 transition {{ $isLocked ? 'opacity-60' : '' }}"
                        >

                            <!-- JENIS BANTUAN -->
                            <td class="px-5 py-4 font-bold text-slate-900">
                                {{ $bantuan['name'] }}
                            </td>

                            <!-- DESCRIPTION -->
                            <td class="px-5 py-4 text-slate-600 leading-relaxed">
                                {{ $bantuan['description'] }}
                                @if($isLocked)
                                    <div class="mt-2 text-xs font-semibold text-amber-700">
                                        {{ $lockedInfo['status_label'] }} - tidak boleh dipilih semester ini.
                                    </div>
                                @endif
                            </td>

                            <!-- RADIO -->
                            <td class="px-5 py-4 text-center">

<input
    type="radio"
    id="jenis_bantuan_dashboard_{{ $index }}"
    name="jenis_bantuan"
    value="{{ $bantuan['value'] }}"
    onchange="selectDashboardBantuan(this)"
    @disabled($isLocked)
    required
    class="w-7 h-7 cursor-pointer accent-blue-600"
>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        @if(count($lockedBantuanTypes) > 0)
            <div class="mt-5 rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4">
                @foreach($lockedBantuanTypes as $lockedBantuan)
                    <p class="text-sm font-semibold text-emerald-800 {{ $loop->first ? '' : 'mt-2' }}">
                        {{ $lockedBantuan['message'] }}
                    </p>
                @endforeach
            </div>
        @endif

        <!-- BUTTON -->
        <div
            id="applyButtonWrapper"
            class="hidden flex justify-end mt-5"
        >

            <button
                type="submit"
                class="rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white hover:bg-blue-700 transition duration-300 shadow-md hover:shadow-lg"
            >

                Mohon Bantuan →

            </button>

        </div>

    </form>

</section>

<!-- SCRIPT -->
<script>

    function selectDashboardBantuan(input) {
        if (input.disabled) {
            return;
        }

        document.querySelectorAll('.dashboard-bantuan-row').forEach(row => {

            row.classList.remove(
                'bg-blue-50',
                'ring-1',
                'ring-blue-200'
            );

            row.classList.add('bg-white');

        });

        let selectedRow =
            input.closest('.dashboard-bantuan-row');

        if (selectedRow) {

            selectedRow.classList.remove('bg-white');

            selectedRow.classList.add(
                'bg-blue-50',
                'ring-1',
                'ring-blue-200'
            );

        }

        document.getElementById('applyButtonWrapper')
            ?.classList.remove('hidden');
    }

</script>

                <!-- PROGRESS PERMOHONAN -->
                <section class="w-full bg-white rounded-[1.7rem] shadow-lg border border-slate-200 p-6">

                    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-[14px] font-semibold text-blue-600 mb-1">
                                Status Terkini
                            </p>

                            <h2 class="text-2xl font-extrabold text-slate-900">
                                Progress Permohonan Terkini
                            </h2>

                            <p class="text-slate-500 mt-1 text-sm">
                                Jejak perkembangan permohonan bantuan terbaru anda.
                            </p>
                        </div>

                        <a href="{{ route('status-permohonan.index') }}"
                           class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                            Lihat Semua Permohonan
                        </a>
                    </div>

                    @if($latestPermohonan)
                        @php
                            $latestBantuan = $latestPermohonan->bantuan;
                            $latestSteps = $latestPermohonan->progressSteps();
                            $latestJenisBantuan = $latestBantuan?->jenis_bantuan ?? $latestPermohonan->jenis_bantuan;
                            $latestTarikhMohon = $latestPermohonan->tarikh_mohon ?? $latestPermohonan->created_at;
                            $summaryItems = [
                                [
                                    'label' => 'No Kelompok',
                                    'value' => $latestPermohonan->no_kelompok ?: ('PMH-' . $latestPermohonan->id),
                                ],
                            ];

                            if (filled($latestJenisBantuan)) {
                                $summaryItems[] = [
                                    'label' => 'Jenis Bantuan',
                                    'value' => \App\Models\Permohonan::jenisBantuanLabel($latestJenisBantuan),
                                ];
                            }

                            if (filled($latestPermohonan->pakej)) {
                                $summaryItems[] = [
                                    'label' => 'Pakej',
                                    'value' => $latestPermohonan->pakej,
                                ];
                            }

                            if ($latestTarikhMohon) {
                                $summaryItems[] = [
                                    'label' => 'Tarikh Mohon',
                                    'value' => $latestTarikhMohon->format('d/m/Y'),
                                ];
                            }

                            $stepUi = [
                                'complete' => [
                                    'circle' => 'border-green-500 bg-green-500 text-white',
                                    'title' => 'text-slate-900',
                                    'text' => 'text-slate-500',
                                    'badge' => 'Selesai',
                                    'badgeClass' => 'bg-green-50 text-green-700',
                                ],
                                'current' => [
                                    'circle' => 'border-blue-600 bg-blue-600 text-white animate-pulse',
                                    'title' => 'text-slate-900',
                                    'text' => 'text-slate-500',
                                    'badge' => 'Dalam Proses',
                                    'badgeClass' => 'bg-blue-50 text-blue-700',
                                ],
                                'pending' => [
                                    'circle' => 'border-slate-200 bg-slate-200 text-slate-500',
                                    'title' => 'text-slate-400',
                                    'text' => 'text-slate-400',
                                    'badge' => 'Menunggu',
                                    'badgeClass' => 'bg-slate-100 text-slate-500',
                                ],
                                'rejected' => [
                                    'circle' => 'border-rose-500 bg-rose-500 text-white',
                                    'title' => 'text-rose-700',
                                    'text' => 'text-rose-600',
                                    'badge' => 'Tidak Berjaya',
                                    'badgeClass' => 'bg-rose-50 text-rose-700',
                                ],
                                'disabled' => [
                                    'circle' => 'border-slate-200 bg-slate-100 text-slate-400',
                                    'title' => 'text-slate-300',
                                    'text' => 'text-slate-300',
                                    'badge' => null,
                                    'badgeClass' => 'bg-slate-100 text-slate-400',
                                ],
                            ];
                        @endphp

                        <div class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-2 xl:grid-cols-4">
                            @foreach($summaryItems as $item)
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $item['label'] }}</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $item['value'] }}</p>
                                </div>
                            @endforeach

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status Semasa</p>
                                <span class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $latestPermohonan->status_badge_class }}">
                                    {{ $latestPermohonan->status_label }}
                                </span>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="absolute left-5 top-5 bottom-5 w-0.5 bg-slate-200"></div>

                            <div class="space-y-6">
                                @foreach($latestSteps as $step)
                                    @php
                                        $state = $step['state'];
                                        $ui = $stepUi[$state] ?? $stepUi['pending'];
                                        $badge = $ui['badge'];

                                        if ($step['key'] === 'distribution' && in_array($state, ['current', 'complete'], true)) {
                                            $badge = $latestPermohonan->status_agihan_label;
                                        }
                                    @endphp

                                    <div class="relative flex gap-4">
                                        <div class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 text-sm font-bold shadow {{ $ui['circle'] }}">
                                            @if($state === 'complete')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                                    <path d="M20 6 9 17l-5-5" />
                                                </svg>
                                            @elseif($state === 'rejected')
                                                <span>!</span>
                                            @else
                                                {{ $step['number'] }}
                                            @endif
                                        </div>

                                        <div>
                                            <h3 class="font-bold {{ $ui['title'] }}">
                                                {{ $step['label'] }}
                                            </h3>

                                            <p class="mt-1 text-sm {{ $ui['text'] }}">
                                                {{ $step['description'] }}
                                            </p>

                                            @if($badge)
                                                <span class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $ui['badgeClass'] }}">
                                                    {{ $badge }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                            <p class="text-sm font-semibold text-slate-700">
                                Tiada permohonan direkodkan buat masa ini.
                            </p>

                            <a href="{{ route('permohonan.index') }}"
                               class="mt-4 inline-flex items-center justify-center rounded-xl bg-[#2563EB] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                                Mohon Bantuan
                            </a>
                        </div>
                    @endif

                </section>

                @if(false)
                <!-- PROGRESS PERMOHONAN PLACEHOLDER LAMA -->
                <section class="w-full bg-white rounded-[1.7rem] shadow-lg border border-slate-200 p-6">

                    <div class="mb-6">
                        <p class="text-[14px] font-semibold text-blue-600 mb-1">
                            Status Terkini
                        </p>

                        <h2 class="text-2xl font-extrabold text-slate-900">
                            Progress Permohonan
                        </h2>

                        <p class="text-slate-500 mt-1 text-sm">
                            Jejak perkembangan permohonan bantuan anda.
                        </p>
                    </div>

                    <div class="relative">
                        <div class="absolute left-5 top-5 bottom-5 w-0.5 bg-slate-200"></div>

                        <div class="space-y-6">

                            <div class="relative flex gap-4">
                                <div class="relative z-10 w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center shadow">
                                    ✓
                                </div>

                                <div>
                                    <h3 class="font-bold text-slate-900">
                                        Permohonan Dihantar
                                    </h3>

                                    <p class="text-sm text-slate-500 mt-1">
                                        Permohonan bantuan telah berjaya dihantar.
                                    </p>
                                </div>
                            </div>

                            <div class="relative flex gap-4">
                                <div class="relative z-10 w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center shadow">
                                    ✓
                                </div>

                                <div>
                                    <h3 class="font-bold text-slate-900">
                                        Semakan Dokumen
                                    </h3>

                                    <p class="text-sm text-slate-500 mt-1">
                                        Dokumen permohonan sedang disemak oleh pihak urus setia.
                                    </p>
                                </div>
                            </div>

                            <div class="relative flex gap-4">
                                <div class="relative z-10 w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center shadow animate-pulse">
                                    3
                                </div>

                                <div>
                                    <h3 class="font-bold text-slate-900">
                                        Menunggu Kelulusan
                                    </h3>

                                    <p class="text-sm text-slate-500 mt-1">
                                        Permohonan sedang menunggu keputusan kelulusan.
                                    </p>

                                    <span class="inline-flex mt-3 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                        Dalam Proses
                                    </span>
                                </div>
                            </div>

                            <div class="relative flex gap-4">
                                <div class="relative z-10 w-10 h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center shadow">
                                    4
                                </div>

                                <div>
                                    <h3 class="font-bold text-slate-400">
                                        Agihan Bantuan
                                    </h3>

                                    <p class="text-sm text-slate-400 mt-1">
                                        Bantuan akan diagihkan selepas permohonan diluluskan.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>

                </section>

                @endif

            </main>

        </div>

    </div>

</div>


{{-- ================= SLIDER SCRIPT ================= --}}
<script>
    let announcementIndex = 0;
    const announcementTotal = 4;

    function updateAnnouncementSlider() {

        const slider =
            document.getElementById('announcementSlider');

        const dots =
            document.querySelectorAll('.announcement-dot');

        if (!slider) return;

        slider.style.transform =
            `translateX(-${announcementIndex * 100}%)`;

        dots.forEach((dot, index) => {

            dot.classList.toggle(
                'bg-blue-600',
                index === announcementIndex
            );

            dot.classList.toggle(
                'bg-white/70',
                index !== announcementIndex
            );

        });
    }

    function nextAnnouncementSlide() {

        announcementIndex =
            (announcementIndex + 1) % announcementTotal;

        updateAnnouncementSlider();
    }

    function prevAnnouncementSlide() {

        announcementIndex =
            (announcementIndex - 1 + announcementTotal) % announcementTotal;

        updateAnnouncementSlider();
    }

    function goAnnouncementSlide(index) {

        announcementIndex = index;

        updateAnnouncementSlider();
    }

    setInterval(nextAnnouncementSlide, 5000);
</script>
@endsection
