@extends('layouts.admin')

@section('page-title', 'Laporan Agihan Bantuan')

@section('content')
@php
    $latestAgihanSearch = $latestAgihanSearch ?? '';
@endphp

<div class="report-page min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-7xl space-y-7 px-6">
        <x-page-hero
            eyebrow="Laporan"
            title="Laporan Agihan Bantuan"
            description="Status penyaluran bantuan dan rekod agihan terkini."
        />

        <div class="no-print flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            <button type="button" onclick="window.print()" class="inline-flex min-w-[120px] items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-100">Export PDF</button>
            <a href="{{ route('admin.laporan.agihan.csv') }}" class="inline-flex min-w-[120px] items-center justify-center rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700">Export Excel</a>
        </div>

        <section class="grid gap-5 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Jumlah Agihan</p>
                <p class="mt-3 text-3xl font-extrabold text-slate-950">{{ number_format($summary['total']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-sky-700">Sedang Diagih</p>
                <p class="mt-3 text-3xl font-extrabold text-sky-700">{{ number_format($summary['sedang_diagih']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-emerald-700">Selesai Diagih</p>
                <p class="mt-3 text-3xl font-extrabold text-emerald-700">{{ number_format($summary['selesai']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-amber-700">Menunggu Bukti Agihan</p>
                <p class="mt-3 text-3xl font-extrabold text-amber-700">{{ number_format($summary['menunggu_bukti']) }}</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Agihan Bantuan Bulanan</h2>
                <p class="mt-1 text-sm text-slate-500">Jumlah bantuan diagihkan mengikut bulan untuk tahun semasa.</p>
                <div class="mt-6">
                    <canvas id="monthlyDistributionChart" height="105"></canvas>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Status Agihan Bantuan</h2>
                <p class="mt-1 text-sm text-slate-500">Pecahan agihan mengikut status.</p>
                <div class="mx-auto mt-6 max-w-sm">
                    <canvas id="distributionStatusBreakdownChart"></canvas>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 space-y-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Rekod Agihan Terkini</h2>
                        <p class="mt-1 text-sm text-slate-500">10 rekod terbaru dalam aliran agihan.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $latestAgihan->count() }} rekod</span>
                </div>

                <form method="GET" action="{{ route('admin.laporan.agihan') }}" class="no-print flex w-full flex-col gap-2 sm:flex-row lg:max-w-3xl">
                    <label for="latest-agihan-search" class="sr-only">Cari rekod agihan terkini</label>
                    <input id="latest-agihan-search"
                           type="search"
                           name="q_agihan"
                           value="{{ $latestAgihanSearch }}"
                           placeholder="Cari pelajar, no matrik, kategori, status atau pegawai"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100 sm:flex-1">

                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-2xl bg-[#3155E7] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#2647D6]">
                        Cari
                    </button>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200">
                <div class="hidden lg:grid lg:grid-cols-[1fr_1fr_1fr_0.85fr_0.85fr_0.6fr] bg-slate-100 text-sm font-semibold text-slate-700">
                    <div class="px-5 py-4">Pelajar</div>
                    <div class="px-5 py-4">Kategori</div>
                    <div class="px-5 py-4">Status</div>
                    <div class="px-5 py-4">Pegawai</div>
                    <div class="px-5 py-4">Tarikh Agihan</div>
                    <div class="px-5 py-4">Detail</div>
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse($latestAgihan as $row)
                        <div class="grid gap-3 px-5 py-5 text-sm lg:grid-cols-[1fr_1fr_1fr_0.85fr_0.85fr_0.6fr] lg:items-center">
                            <div>
                                <p class="font-semibold text-slate-950">{{ $row->pelajar?->nama_penuh ?? '-' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $row->pelajar?->no_matrik ?? $row->no_kelompok }}</p>
                            </div>
                            <div class="text-slate-600">{{ \App\Models\Permohonan::kategoriBantuanLabel($row->bantuan?->kategori_bantuan) }}</div>
                            <div>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $row->status_agihan_badge_class }}">
                                    {{ $row->status_agihan_label }}
                                </span>
                            </div>
                            <div class="text-slate-600">{{ $row->diagihOleh?->name ?? '-' }}</div>
                            <div class="text-slate-600">{{ optional($row->tarikh_agihan)->format('d/m/Y') ?: '-' }}</div>
                            <div class="no-print">
                                @if(Route::has('admin.permohonan.show'))
                                    <a href="{{ route('admin.permohonan.show', $row) }}" class="font-semibold text-sky-700 hover:text-sky-900">Lihat</a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <div class="mx-auto max-w-md rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8">
                                <p class="text-sm font-semibold text-slate-700">Tiada rekod agihan.</p>
                                <p class="mt-2 text-sm text-slate-500">Rekod agihan terkini akan dipaparkan selepas bantuan mula diagihkan.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const monthLabels = @json($monthLabels);
    const monthlyAgihanData = @json($monthlyAgihanData);
    const agihanStatusLabels = @json(collect($statusData)->pluck('label'));
    const agihanStatusValues = @json(collect($statusData)->pluck('value'));
    const agihanStatusColors = @json(collect($statusData)->pluck('color'));
    const agihanStatusTotal = @json($summary['total']);

    const agihanCenterTextPlugin = {
        id: 'agihanCenterText',
        beforeDraw(chart) {
            if (chart.config.type !== 'doughnut') return;
            const area = chart.chartArea;
            if (!area) return;
            const ctx = chart.ctx;
            const x = (area.left + area.right) / 2;
            const y = (area.top + area.bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.font = '700 24px Arial';
            ctx.fillStyle = '#0f172a';
            ctx.fillText(Number(agihanStatusTotal || 0).toLocaleString('ms-MY'), x, y - 6);
            ctx.font = '500 12px Arial';
            ctx.fillStyle = '#64748b';
            ctx.fillText('Total agihan', x, y + 16);
            ctx.restore();
        }
    };

    new Chart(document.getElementById('monthlyDistributionChart'), {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                data: monthlyAgihanData,
                borderColor: '#0284c7',
                backgroundColor: 'rgba(14, 165, 233, 0.16)',
                fill: true,
                tension: 0.38,
                pointRadius: 4,
                pointBackgroundColor: '#0284c7'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label(context) {
                            return Number(context.raw || 0).toLocaleString('ms-MY') + ' agihan';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        callback(value) { return Number(value || 0).toLocaleString('ms-MY'); }
                    },
                    grid: { color: '#eef2f7' }
                },
                x: { grid: { display: false } }
            }
        }
    });

    new Chart(document.getElementById('distributionStatusBreakdownChart'), {
        type: 'doughnut',
        data: {
            labels: agihanStatusLabels,
            datasets: [{ data: agihanStatusValues, backgroundColor: agihanStatusColors, borderWidth: 0 }]
        },
        options: {
            responsive: true,
            cutout: '72%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        generateLabels(chart) {
                            const dataset = chart.data.datasets[0];
                            return chart.data.labels.map((label, index) => ({
                                text: `${label} (${agihanStatusTotal > 0 ? ((dataset.data[index] / agihanStatusTotal) * 100).toFixed(1) : '0.0'}%)`,
                                fillStyle: dataset.backgroundColor[index],
                                strokeStyle: dataset.backgroundColor[index],
                                lineWidth: 0,
                                hidden: false,
                                index
                            }));
                        }
                    }
                }
            }
        },
        plugins: [agihanCenterTextPlugin]
    });
</script>
@endsection
