@extends('layouts.admin')

@section('page-title', 'Agihan Bantuan')

@section('content')
@php
    $formatLabel = fn ($value) => filled($value)
        ? \Illuminate\Support\Str::of($value)->replace(['_', '-'], ' ')->squish()->title()
        : '-';

    $statusBelum = \App\Models\Permohonan::STATUS_AGIHAN_BELUM_DIAGIH;
    $statusSedang = \App\Models\Permohonan::STATUS_AGIHAN_SEDANG_DIAGIH;
    $statusSelesai = \App\Models\Permohonan::STATUS_AGIHAN_SELESAI;

    $workflowLabels = [
        $statusBelum => [
            'label' => 'Menunggu Agihan',
            'description' => 'Permohonan telah diluluskan dan menunggu tindakan mula agihan.',
            'badge' => 'bg-amber-100 text-amber-700',
        ],
        $statusSedang => [
            'label' => 'Dalam Penghantaran',
            'description' => 'Agihan sedang diuruskan. Lengkapkan bukti sebelum sahkan selesai.',
            'badge' => 'bg-blue-100 text-blue-700',
        ],
        $statusSelesai => [
            'label' => 'Selesai Disalurkan',
            'description' => 'Bantuan telah diterima pelajar dan rekod agihan lengkap.',
            'badge' => 'bg-emerald-100 text-emerald-700',
        ],
    ];

    $workflowSteps = [
        ['key' => 'diluluskan', 'label' => 'Diluluskan'],
        ['key' => $statusSedang, 'label' => 'Sedang Diagih'],
        ['key' => $statusSelesai, 'label' => 'Selesai'],
    ];

    $stepState = function (string $status, string $stepKey) use ($statusSedang, $statusSelesai) {
        if ($status === $statusSelesai) {
            return 'completed';
        }

        if ($status === $statusSedang) {
            return $stepKey === 'diluluskan'
                ? 'completed'
                : ($stepKey === $statusSedang ? 'active' : 'pending');
        }

        return $stepKey === 'diluluskan' ? 'active' : 'pending';
    };

    $stepDotClass = [
        'completed' => 'border-emerald-500 bg-emerald-500 text-white',
        'active' => 'border-blue-600 bg-blue-600 text-white',
        'pending' => 'border-slate-300 bg-white text-slate-400',
    ];

    $stepTextClass = [
        'completed' => 'text-emerald-700',
        'active' => 'text-blue-700',
        'pending' => 'text-slate-400',
    ];
@endphp

<div class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-7xl space-y-7 px-6">
        <x-page-hero
        eyebrow="AGIHAN BANTUAN PELAJAR"
        title="Aliran Agihan Bantuan"
        description="Pantau permohonan yang telah diluluskan, mulakan agihan, lengkapkan bukti dan sahkan bantuan."
    />

        @foreach(['success' => 'bg-emerald-50 text-emerald-800 border-emerald-200', 'warning' => 'bg-amber-50 text-amber-800 border-amber-200', 'info' => 'bg-blue-50 text-blue-800 border-blue-200'] as $flashKey => $flashClass)
            @if(session($flashKey) && $flashKey !== 'success')
                <div class="rounded-2xl border px-5 py-4 text-sm font-medium {{ $flashClass }}">
                    {{ session($flashKey) }}
                </div>
            @endif
        @endforeach

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
                <p class="font-semibold">Sila semak semula maklumat agihan.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            @foreach($distributionStats as $stat)
                @php
                    $percentage = $totalDistribution > 0 ? round(($stat['value'] / $totalDistribution) * 100, 1) : 0;
                @endphp
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $stat['class'] }}">
                            {{ $stat['badge'] }}
                        </span>
                    </div>
                    <div class="mt-5 flex items-end justify-between">
                        <h2 class="text-4xl font-extrabold text-slate-950">{{ $stat['value'] }}</h2>
                        <span class="text-sm font-semibold text-slate-500">{{ $percentage }}%</span>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-5">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
                <h2 class="text-lg font-bold text-slate-950">Status Agihan Bantuan</h2>
                <p class="mt-1 text-sm text-slate-500">Pecahan permohonan yang menunggu, sedang diurus, dan telah selesai disalurkan.</p>
                <div class="mx-auto mt-6 max-w-md">
                    <canvas id="distributionStatusChart" data-chart='@json($distributionChart)'></canvas>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-3">
                <h2 class="text-lg font-bold text-slate-950">Panduan Aliran Kerja</h2>
                <p class="mt-1 text-sm text-slate-500">Setiap rekod bergerak daripada kelulusan kepada agihan aktif sebelum disahkan selesai.</p>

                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-sm font-bold text-emerald-800">Diluluskan</p>
                        <p class="mt-2 text-xs leading-5 text-emerald-700">Permohonan masuk automatik ke senarai agihan selepas admin meluluskan.</p>
                    </div>
                    <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                        <p class="text-sm font-bold text-blue-800">Dalam Penghantaran</p>
                        <p class="mt-2 text-xs leading-5 text-blue-700">Admin mula agihan dan melengkapkan bukti serahan serta catatan.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-bold text-slate-800">Selesai Disalurkan</p>
                        <p class="mt-2 text-xs leading-5 text-slate-600">Tarikh agihan, pegawai, bukti, dan notifikasi pelajar direkodkan.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Senarai Agihan</h2>
                        <p class="mt-1 text-sm text-slate-500">Permohonan diluluskan yang sedia untuk proses agihan bantuan.</p>
                    </div>
                    <span class="w-fit rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-600">
                        {{ $agihanRows->count() }} rekod
                    </span>
                </div>
            </div>

            <div class="divide-y divide-slate-200">
                @forelse($agihanRows as $row)
                    @php
                        $jenisBantuan = $row->bantuan?->jenis_bantuan ?? $row->jenis_bantuan;
                        $kategoriBantuan = $row->bantuan?->kategori_bantuan;
                        $statusKey = $row->status_agihan_key;
                        $workflow = $workflowLabels[$statusKey] ?? $workflowLabels[$statusBelum];
                        $proofExtension = filled($row->bukti_agihan)
                            ? strtolower(pathinfo($row->bukti_agihan, PATHINFO_EXTENSION))
                            : null;
                        $proofIsImage = in_array($proofExtension, ['jpg', 'jpeg', 'png'], true);
                        $proofUrl = filled($row->bukti_agihan) ? route('admin.agihan.bukti', $row) : null;
                        $proofDownloadUrl = filled($row->bukti_agihan) ? route('admin.agihan.bukti', ['permohonan' => $row, 'download' => 1]) : null;
                    @endphp

                    <article class="p-6 transition hover:bg-slate-50">
                        <div class="grid gap-6 xl:grid-cols-[1.1fr_1.4fr_0.9fr]">
                            <div class="space-y-4">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $workflow['badge'] }}">
                                            {{ $workflow['label'] }}
                                        </span>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $row->status_agihan_badge_class }}">
                                            {{ $row->status_agihan_label }}
                                        </span>
                                    </div>
                                    <h3 class="mt-3 text-lg font-bold text-slate-950">
                                        {{ $row->pelajar?->nama_penuh ?? '-' }}
                                    </h3>
                                    <p class="mt-1 text-sm font-semibold text-blue-700">
                                        {{ $row->no_kelompok ?? '-' }}
                                    </p>
                                </div>

                                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">No Matrik</dt>
                                        <dd class="mt-1 font-medium text-slate-800">{{ $row->pelajar?->no_matrik ?? '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Jenis Bantuan</dt>
                                        <dd class="mt-1 font-medium text-slate-800">{{ $formatLabel($jenisBantuan) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kategori</dt>
                                        <dd class="mt-1 font-medium text-slate-800">{{ $formatLabel($kategoriBantuan) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Pegawai Agihan</dt>
                                        <dd class="mt-1 font-medium text-slate-800">{{ $row->diagihOleh?->name ?? 'Belum direkodkan' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="space-y-5">
                                <div>
                                    <div class="flex items-center">
                                        @foreach($workflowSteps as $step)
                                            @php
                                                $state = $stepState($statusKey, $step['key']);
                                            @endphp
                                            <div class="flex min-w-0 flex-1 items-center">
                                                <div class="flex min-w-0 flex-col items-center text-center">
                                                    <span class="flex h-9 w-9 items-center justify-center rounded-full border-2 text-xs font-bold {{ $stepDotClass[$state] }}">
                                                        {{ $loop->iteration }}
                                                    </span>
                                                    <span class="mt-2 text-xs font-bold {{ $stepTextClass[$state] }}">
                                                        {{ $step['label'] }}
                                                    </span>
                                                </div>

                                                @if(! $loop->last)
                                                    <span class="mx-3 h-0.5 flex-1 rounded-full {{ $state === 'completed' ? 'bg-emerald-400' : 'bg-slate-200' }}"></span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="mt-4 text-sm leading-6 text-slate-500">{{ $workflow['description'] }}</p>
                                </div>

                                <div class="grid gap-3 text-sm sm:grid-cols-3">
                                    <div class="border-l-2 border-emerald-300 pl-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Diluluskan</p>
                                        <p class="mt-1 font-semibold text-slate-800">{{ $row->admin_review_date?->format('d/m/Y h:i A') ?? '-' }}</p>
                                    </div>
                                    <div class="border-l-2 border-blue-300 pl-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tarikh Mohon</p>
                                        <p class="mt-1 font-semibold text-slate-800">{{ $row->tarikh_mohon?->format('d/m/Y') ?? '-' }}</p>
                                    </div>
                                    <div class="border-l-2 border-slate-300 pl-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tarikh Selesai</p>
                                        <p class="mt-1 font-semibold text-slate-800">{{ $row->tarikh_agihan?->format('d/m/Y h:i A') ?? '-' }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                                    @if(filled($row->bukti_agihan))
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">
                                            Bukti telah dimuat naik
                                        </span>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">
                                            Bukti belum dimuat naik
                                        </span>
                                    @endif

                                    @if(filled($row->catatan_agihan))
                                        <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700">
                                            Catatan direkodkan
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                @if($statusKey === $statusBelum)
                                    <form method="POST"
                                          action="{{ route('admin.agihan.mula', $row) }}"
                                          data-confirm
                                          data-confirm-title="Mulakan agihan bantuan?"
                                          data-confirm-text="Status agihan akan ditukar kepada sedang diagih."
                                          data-confirm-button="Ya, mulakan">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex w-full items-center justify-center rounded-2xl bg-[#071633] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#102544]">
                                            Mula Agihan
                                        </button>
                                    </form>
                                @elseif($statusKey === $statusSedang)
                                    <form method="POST"
                                          action="{{ route('admin.agihan.selesai', $row) }}"
                                          enctype="multipart/form-data"
                                          class="space-y-4"
                                          data-confirm
                                          data-confirm-title="Sahkan agihan selesai?"
                                          data-confirm-text="Bantuan akan ditandakan sebagai selesai diagihkan kepada pelajar."
                                          data-confirm-button="Ya, sahkan selesai"
                                          data-confirm-color="#059669">
                                        @csrf
                                        @method('PATCH')

                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700">
                                                Catatan admin
                                            </label>
                                            <textarea name="catatan_agihan"
                                                      rows="3"
                                                      class="mt-2 w-full rounded-2xl border-slate-200 text-sm text-slate-700 shadow-sm focus:border-[#071633] focus:ring-[#071633]"
                                                      placeholder="Catatan agihan (pilihan)">{{ old('catatan_agihan') }}</textarea>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700">
                                                Bukti agihan
                                                <span class="text-rose-600">*</span>
                                            </label>
                                            <input type="file"
                                                   name="bukti_agihan"
                                                   required
                                                   accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                                   class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm file:mr-3 file:rounded-xl file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                                            <p class="mt-2 text-xs leading-5 text-slate-400">
                                                PDF, JPG atau PNG. Maksimum 5MB. Bukti wajib sebelum rekod boleh disahkan selesai.
                                            </p>
                                        </div>

                                        <button type="submit"
                                                class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                                            Sahkan Selesai
                                        </button>
                                    </form>
                                @else
                                    <div class="space-y-3">
                                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            Selesai
                                        </span>

                                        @if(filled($row->catatan_agihan))
                                            <p class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                                                {{ $row->catatan_agihan }}
                                            </p>
                                        @endif

                                        @if(filled($row->bukti_agihan))
                                            <button type="button"
                                                    data-proof-preview
                                                    data-proof-url="{{ $proofUrl }}"
                                                    data-proof-download-url="{{ $proofDownloadUrl }}"
                                                    data-proof-extension="{{ $proofExtension }}"
                                                    data-proof-is-image="{{ $proofIsImage ? '1' : '0' }}"
                                                    class="inline-flex w-full items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100">
                                                Lihat Bukti
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="px-6 py-12 text-center">
                        <div class="mx-auto max-w-md rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8">
                            <p class="text-sm font-semibold text-slate-700">Tiada permohonan diluluskan untuk agihan bantuan.</p>
                            <p class="mt-2 text-sm text-slate-500">Rekod akan muncul di sini selepas admin meluluskan permohonan pelajar.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-proof-preview]').forEach(function (button) {
            button.addEventListener('click', function () {
                const proofUrl = button.dataset.proofUrl;
                const downloadUrl = button.dataset.proofDownloadUrl;
                const extension = (button.dataset.proofExtension || '').toUpperCase();
                const isImage = button.dataset.proofIsImage === '1';

                if (isImage) {
                    Swal.fire({
                        title: 'Bukti Agihan',
                        imageUrl: proofUrl,
                        imageAlt: 'Bukti agihan',
                        imageWidth: '100%',
                        showCancelButton: true,
                        confirmButtonText: 'Muat Turun',
                        cancelButtonText: 'Tutup',
                        confirmButtonColor: '#071633',
                        cancelButtonColor: '#64748b'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            window.open(downloadUrl, '_blank');
                        }
                    });

                    return;
                }

                Swal.fire({
                    icon: 'info',
                    title: 'Bukti Agihan PDF',
                    text: `Fail ${extension || 'PDF'} tersedia untuk dimuat turun.`,
                    showCancelButton: true,
                    confirmButtonText: 'Muat Turun PDF',
                    cancelButtonText: 'Tutup',
                    confirmButtonColor: '#071633',
                    cancelButtonColor: '#64748b'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        window.open(downloadUrl, '_blank');
                    }
                });
            });
        });

        const distributionChart = document.getElementById('distributionStatusChart');

        if (distributionChart) {
            const distributionChartData = JSON.parse(distributionChart.dataset.chart);
            const distributionLabels = distributionChartData.labels;
            const distributionData = distributionChartData.data;
            const distributionColors = distributionChartData.colors;
            const totalDistribution = distributionData.reduce((sum, value) => sum + value, 0);

            const centerTextPlugin = {
                id: 'centerText',
                beforeDraw(chart) {
                    const chartArea = chart.chartArea;
                    if (!chartArea) return;

                    const ctx = chart.ctx;
                    const centerX = (chartArea.left + chartArea.right) / 2;
                    const centerY = (chartArea.top + chartArea.bottom) / 2;

                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = '700 28px Arial';
                    ctx.fillStyle = '#0f172a';
                    ctx.fillText(totalDistribution, centerX, centerY - 6);
                    ctx.font = '500 12px Arial';
                    ctx.fillStyle = '#64748b';
                    ctx.fillText('Jumlah', centerX, centerY + 16);
                    ctx.restore();
                }
            };

            new Chart(distributionChart, {
                type: 'doughnut',
                data: {
                    labels: distributionLabels,
                    datasets: [{
                        data: distributionData,
                        backgroundColor: distributionColors,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '72%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 16,
                                color: '#475569',
                                generateLabels(chart) {
                                    const dataset = chart.data.datasets[0];

                                    return chart.data.labels.map((label, index) => {
                                        const value = dataset.data[index];
                                        const percentage = totalDistribution > 0
                                            ? ((value / totalDistribution) * 100).toFixed(1)
                                            : 0;

                                        return {
                                            text: `${label} (${percentage}%)`,
                                            fillStyle: dataset.backgroundColor[index],
                                            strokeStyle: dataset.backgroundColor[index],
                                            lineWidth: 0,
                                            hidden: false,
                                            index
                                        };
                                    });
                                }
                            }
                        }
                    }
                },
                plugins: [centerTextPlugin]
            });
        }
    });
</script>
@endsection
