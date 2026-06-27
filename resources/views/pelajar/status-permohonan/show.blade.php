@extends('layouts.app')

@section('content')
@php
    $status = $permohonan->status ?? 'Sedang Disemak';
    $statusKey = \App\Models\Permohonan::normalizeStatus($status);
    $statusLabel = \App\Models\Permohonan::statusLabel($status);
    $agihanKey = \App\Models\Permohonan::normalizeStatusAgihan($permohonan->status_agihan);
    $agihanLabel = match ($agihanKey) {
        \App\Models\Permohonan::STATUS_AGIHAN_SEDANG_DIAGIH => 'Sedang Diagih',
        \App\Models\Permohonan::STATUS_AGIHAN_SELESAI => 'Bantuan Telah Diagihkan',
        default => 'Menunggu Agihan',
    };

    $currentStep = match ($statusKey) {
        \App\Models\Permohonan::STATUS_DILULUSKAN => $agihanKey === \App\Models\Permohonan::STATUS_AGIHAN_SELESAI ? 5 : 4,
        \App\Models\Permohonan::STATUS_DITOLAK_GAGAL => 2,
        \App\Models\Permohonan::STATUS_DALAM_SEMAKAN => match ($status) {
            'Permohonan Dihantar' => 1,
            'Menunggu Kelulusan' => 3,
            default => 2,
        },
        default => 1,
    };

    $steps = [
        1 => 'Permohonan Dihantar',
        2 => 'Semakan Dokumen',
        3 => 'Menunggu Kelulusan',
        4 => 'Agihan Bantuan',
    ];
    $progressSteps = $permohonan->progressSteps();
    $stepUi = [
        'complete' => [
            'circle' => 'border-green-500 bg-green-500 text-white',
            'line' => 'bg-green-500',
            'title' => 'text-slate-800',
            'badge' => 'Selesai',
            'badgeClass' => 'bg-green-50 text-green-700',
        ],
        'current' => [
            'circle' => 'border-blue-600 bg-blue-600 text-white',
            'line' => 'bg-slate-200',
            'title' => 'text-blue-700',
            'badge' => 'Dalam Proses',
            'badgeClass' => 'bg-blue-50 text-blue-700',
        ],
        'pending' => [
            'circle' => 'border-slate-300 bg-white text-slate-400',
            'line' => 'bg-slate-200',
            'title' => 'text-slate-400',
            'badge' => 'Menunggu',
            'badgeClass' => 'bg-slate-100 text-slate-500',
        ],
        'rejected' => [
            'circle' => 'border-red-500 bg-red-500 text-white',
            'line' => 'bg-red-300',
            'title' => 'text-red-700',
            'badge' => 'Tidak Berjaya',
            'badgeClass' => 'bg-red-50 text-red-700',
        ],
        'disabled' => [
            'circle' => 'border-slate-200 bg-slate-100 text-slate-400',
            'line' => 'bg-slate-200',
            'title' => 'text-slate-300',
            'badge' => null,
            'badgeClass' => 'bg-slate-100 text-slate-400',
        ],
    ];

    $bantuan = $permohonan->bantuan;
    $bantuanData = is_array($bantuan?->data) ? $bantuan->data : [];

    $formatValue = function ($value) {
        if (is_array($value)) {
            return '-';
        }

        return filled($value) || $value === 0 ? (string) $value : '-';
    };

    $formatItems = function ($items) {
        return collect($items ?? [])
            ->filter(fn ($item) => filled($item['selected'] ?? null))
            ->map(function ($item) {
                $quantity = $item['qty'] ?? 1;

                return trim(($item['selected'] ?? '').' ('.$quantity.' unit)');
            })
            ->filter()
            ->implode(', ') ?: '-';
    };

    $learningTypeLabels = [
        'individu' => 'Individu',
        'group' => 'Kelab / Persatuan / Kelas',
    ];

    $levelLabels = [
        'fakulti' => 'Fakulti',
        'universiti' => 'Universiti',
        'kebangsaan' => 'Kebangsaan',
        'antarabangsa' => 'Antarabangsa',
    ];

    $bantuanDetails = [];
    $addDetail = function (string $label, $value) use (&$bantuanDetails, $formatValue) {
        $formatted = $formatValue($value);

        if ($formatted !== '-') {
            $bantuanDetails[] = [
                'label' => $label,
                'value' => $formatted,
            ];
        }
    };

    $addDetail('Jenis Bantuan', \App\Models\Permohonan::jenisBantuanLabel($bantuan?->jenis_bantuan ?? $permohonan->jenis_bantuan));
    $addDetail('Kategori Bantuan', \App\Models\Permohonan::kategoriBantuanLabel($bantuan?->kategori_bantuan));
    $addDetail('Pakej', $bantuanData['pakej'] ?? $permohonan->pakej);
    $addDetail('Jumlah Ahli', $bantuanData['jumlah_ahli'] ?? $permohonan->jumlah_ahli);
    $addDetail('Alamat Rumah', $bantuanData['alamat_rumah'] ?? null);
    $addDetail('Bandar', $bantuanData['bandar'] ?? null);
    $addDetail('Poskod', $bantuanData['poskod'] ?? null);
    $addDetail('Negeri', $bantuanData['negeri'] ?? null);
    $addDetail('Jenis Kediaman', $bantuanData['jenis_kediaman'] ?? null);
    $addDetail('Jenis Permohonan', $learningTypeLabels[$bantuanData['learning_type'] ?? ''] ?? null);
    $addDetail('Nama Kelab / Persatuan / Kelas', $bantuanData['group']['nama_group'] ?? null);
    $addDetail('Bilangan Ahli', $bantuanData['group']['bil_ahli'] ?? null);
    $addDetail('Item Pembelajaran', $formatItems($bantuanData['group']['items'] ?? $bantuanData['individu']['items'] ?? []));
    $addDetail('Peralatan Pembelajaran', $bantuanData['peralatan'] ?? null);
    $addDetail('Sebab Permohonan', $bantuanData['sebab'] ?? null);
    $addDetail('Peringkat Aktiviti', $levelLabels[$bantuanData['peringkat'] ?? ''] ?? null);
    $addDetail('Nama Kelab / Pasukan', $bantuanData['nama_kelab_pasukan'] ?? null);
    $addDetail('Bilangan Peserta', $bantuanData['bilangan_peserta'] ?? null);
    $addDetail('Item Sukan', $formatItems($bantuanData['items'] ?? []));
    $addDetail('Justifikasi', $bantuanData['justifikasi'] ?? $bantuanData['individu']['justifikasi'] ?? null);
@endphp

<div class="max-w-7xl mx-auto px-6 py-10">

    <div class="mb-8">
        <x-page-hero
            eyebrow="Pelajar"
            title="Rekod Permohonan"
            description="Paparan status dan maklumat permohonan bantuan anda."
        />

        <div class="mt-4 flex justify-end">
            <a href="{{ route('status-permohonan.index') }}"
               class="rounded-xl bg-[#071633] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#0B1F4D]">
                Kembali
            </a>
        </div>
    </div>

    {{-- STATUS CARD --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">

        {{-- STATUS TOP --}}
        <div class="px-10 py-8">

            <p class="text-xs font-bold uppercase tracking-[0.25em] text-blue-600">
                Status Terkini
            </p>

            <h2 class="mt-4 text-2xl font-semibold text-slate-900">
                Status:
                <span class="
                    @if($statusKey === \App\Models\Permohonan::STATUS_DILULUSKAN)
                        text-green-600
                    @elseif($statusKey === \App\Models\Permohonan::STATUS_DITOLAK_GAGAL)
                        text-red-600
                    @elseif($statusKey === \App\Models\Permohonan::STATUS_DALAM_SEMAKAN)
                        text-amber-600
                    @else
                        text-blue-600
                    @endif
                ">
                    {{ $statusLabel }}
                </span>
            </h2>

            <p class="mt-3 text-sm text-slate-600">
                @if($statusKey === \App\Models\Permohonan::STATUS_DILULUSKAN)
                    Status agihan bantuan: <span class="font-semibold text-slate-900">{{ $agihanLabel }}</span>.
                @else
                    Permohonan anda sedang diproses oleh pihak urus setia.
                @endif
            </p>

            <p class="mt-6 text-sm italic text-slate-400">
                Tarikh permohonan:
                {{ $permohonan->tarikh_mohon ? $permohonan->tarikh_mohon->format('d/m/Y') : '-' }}
            </p>
        </div>

        {{-- STEPPER --}}
        <div class="border-t border-slate-200 px-10 py-10 overflow-x-auto">

            <div class="min-w-[800px] flex items-start">

                @foreach($progressSteps as $step)
                    @php
                        $state = $step['state'];
                        $ui = $stepUi[$state] ?? $stepUi['pending'];
                        $badge = $ui['badge'];

                        if ($step['key'] === 'distribution' && in_array($state, ['current', 'complete'], true)) {
                            $badge = $permohonan->status_agihan_label;
                        }

                        $previousComplete = $loop->first ? true : (($progressSteps[$loop->index - 1]['state'] ?? null) === 'complete');
                    @endphp

                    <div class="flex-1 flex flex-col items-center relative">

                        @if(! $loop->first)
                            <div class="absolute top-5 -left-1/2 w-full h-[2px] {{ $previousComplete ? 'bg-green-500' : $ui['line'] }}"></div>
                        @endif

                        <div class="relative z-10 flex h-10 w-10 items-center justify-center rounded-full border-2 text-sm font-semibold {{ $ui['circle'] }}">
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

                        <div class="mt-4 text-center px-3">
                            <p class="text-sm font-medium {{ $ui['title'] }}">
                                {{ $step['label'] }}
                            </p>

                            @if($badge)
                                <span class="mt-2 inline-flex rounded-full px-3 py-1 text-[11px] font-semibold {{ $ui['badgeClass'] }}">
                                    {{ $badge }}
                                </span>
                            @endif
                        </div>
                    </div>

                @endforeach

            </div>

        </div>

    </div>

        @if(false)
        {{-- STEPPER PLACEHOLDER LAMA --}}
        <div class="border-t border-slate-200 px-10 py-10 overflow-x-auto">

            <div class="min-w-[800px] flex items-start">

                @foreach($steps as $number => $title)

                    @php
                        $completed = $number < $currentStep;
                        $active = $number == $currentStep;
                        $activeBadge = $number === 4 && $statusKey === \App\Models\Permohonan::STATUS_DILULUSKAN
                            ? $agihanLabel
                            : 'Dalam Proses';
                    @endphp

                    <div class="flex-1 flex flex-col items-center relative">

                        {{-- LINE --}}
                        @if($number != 1)
                            <div class="absolute top-5 -left-1/2 w-full h-[2px]
                                {{ $completed ? 'bg-green-500' : 'bg-slate-200' }}">
                            </div>
                        @endif

                        {{-- CIRCLE --}}
                        <div class="relative z-10 flex h-10 w-10 items-center justify-center rounded-full border-2 text-sm font-semibold
                            @if($completed)
                                border-green-500 bg-green-500 text-white
                            @elseif($active)
                                border-blue-600 bg-blue-600 text-white
                            @else
                                border-slate-300 bg-white text-slate-400
                            @endif
                        ">
                            @if($completed)
                                ✓
                            @else
                                {{ $number }}
                            @endif
                        </div>

                        {{-- TEXT --}}
                        <div class="mt-4 text-center px-3">
                            <p class="text-sm font-medium
                                @if($active)
                                    text-blue-700
                                @elseif($completed)
                                    text-slate-800
                                @else
                                    text-slate-400
                                @endif">
                                {{ $title }}
                            </p>

                            @if($active)
                                <span class="mt-2 inline-flex rounded-full bg-blue-50 px-3 py-1 text-[11px] font-semibold text-blue-700">
                                    {{ $activeBadge }}
                                </span>
                            @elseif($completed && $number === 4 && $statusKey === \App\Models\Permohonan::STATUS_DILULUSKAN)
                                <span class="mt-2 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700">
                                    Bantuan Telah Diagihkan
                                </span>
                            @endif
                        </div>
                    </div>

                @endforeach

            </div>

        </div>
    </div>

        @endif
    {{-- Submitted Form Section --}}
    <div class="mt-12 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">

        <h2 class="text-xl font-semibold text-slate-900">
            Maklumat Permohonan Yang Dihantar
        </h2>

        <p class="mt-2 text-sm text-slate-500">
            Anda boleh menyemak semula maklumat permohonan yang telah dihantar.
        </p>

        <div class="mt-8 border-t border-slate-200 pt-8">

            {{-- Maklumat Pemohon --}}
            <div class="mb-10">
                <h3 class="mb-6 text-base font-semibold text-slate-800">
                    Maklumat Pemohon
                </h3>

                <div class="grid gap-y-6 md:grid-cols-3">
                    <div>
                        <p class="text-sm text-slate-800">Nama Pemohon</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $permohonan->pelajar?->nama_penuh ?? auth()->user()->name }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-800">No Matrik</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $permohonan->pelajar?->no_matrik ?? auth()->user()->matrik ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-800">Email</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $permohonan->pelajar?->email_ukm ?? auth()->user()->email }}</p>
                    </div>
                </div>
            </div>

            {{-- Maklumat Permohonan --}}
            <div class="mb-10">
                <h3 class="mb-6 text-base font-semibold text-slate-800">
                    Maklumat Permohonan
                </h3>

                <div class="grid gap-y-6 md:grid-cols-3">
                    <div>
                        <p class="text-sm text-slate-800">No Kelompok</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $permohonan->no_kelompok ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-800">Tarikh Mohon</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $permohonan->tarikh_mohon ? $permohonan->tarikh_mohon->format('d/m/Y') : '-' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-800">Status Permohonan</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $statusLabel }}</p>
                    </div>
                </div>
            </div>

            {{-- Maklumat Bantuan --}}
            <div>
                <h3 class="mb-6 text-base font-semibold text-slate-800">
                    Maklumat Bantuan
                </h3>

                <div class="grid gap-y-6 md:grid-cols-3">
                    @forelse($bantuanDetails as $detail)
                        <div>
                            <p class="text-sm text-slate-800">{{ $detail['label'] }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $detail['value'] }}</p>
                        </div>
                    @empty
                        <div>
                            <p class="text-sm text-slate-800">Maklumat Bantuan</p>
                            <p class="mt-1 text-sm text-slate-500">Tiada maklumat bantuan direkodkan.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    {{-- DOKUMEN & CATATAN --}}
    <div class="mt-8 grid gap-6 lg:grid-cols-2">

        {{-- Dokumen Sokongan --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">

            <div class="mb-6">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600">
                    Lampiran
                </p>

                <h3 class="mt-2 text-xl font-semibold text-slate-900">
                    Dokumen Sokongan
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Senarai dokumen yang telah dimuat naik bersama permohonan.
                </p>
            </div>

            <div class="space-y-5">
                @forelse($permohonan->dokumens as $dokumen)
                    <x-permohonan-document-card :dokumen="$dokumen" />
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                        <p class="text-sm font-semibold text-slate-700">Tiada dokumen sokongan direkodkan.</p>
                        <p class="mt-2 text-sm text-slate-500">Dokumen yang dimuat naik bersama permohonan akan dipaparkan di sini.</p>
                    </div>
                @endforelse

                @if(false)

                <div>
                    <p class="mb-2 text-sm font-medium text-slate-800">
                        Bukti Pendapatan / Tiada Pendapatan
                    </p>

                    <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500 hover:border-blue-200 hover:bg-blue-50 transition">
                        <span>View Document</span>
                        <span class="text-slate-400">👁 ⬇</span>
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-sm font-medium text-slate-800">
                        Bukti Alamat Rumah Sewa / Bil Utiliti
                    </p>

                    <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500 hover:border-blue-200 hover:bg-blue-50 transition">
                        <span>View Document</span>
                        <span class="text-slate-400">👁 ⬇</span>
                    </div>
                </div>

                @endif
            </div>

        </div>

        {{-- Catatan Pegawai --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">

            <div class="mb-6">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-teal-600">
                    Maklum Balas
                </p>

                <h3 class="mt-2 text-xl font-semibold text-slate-900">
                    Catatan Pegawai
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Catatan semakan daripada pihak urus setia.
                </p>
            </div>

            <div class="rounded-2xl border-l-4
                @if($statusKey === \App\Models\Permohonan::STATUS_DITOLAK_GAGAL)
                    border-red-500 bg-red-50
                @else
                    border-teal-500 bg-teal-50
                @endif
                p-5">

                <p class="text-sm leading-relaxed text-slate-600">
                    {{ $permohonan->catatan ?? 'Tiada catatan buat masa ini.' }}
                </p>

            </div>

        </div>

    </div>

</div>
@endsection
