@extends('layouts.admin')

@section('page-title', 'Statistik Permohonan')

@section('content')
@php
    $monthLabels = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'];

    $monthlyApprovedData = $monthlyApprovedData ?? array_fill(0, 12, 0);
    $monthlyRejectedData = $monthlyRejectedData ?? array_fill(0, 12, 0);
@endphp

<div class="report-page min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-7xl space-y-7 px-6">
        <x-page-hero
            eyebrow="Laporan"
            title="Statistik Permohonan"
            description="Status, kadar kelulusan dan trend permohonan pelajar."
        />

        <div class="no-print flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            <button type="button" onclick="window.print()" class="inline-flex min-w-[120px] items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-100">
                Export PDF
            </button>
            <a href="{{ route('admin.statistik.permohonan.csv') }}" class="inline-flex min-w-[120px] items-center justify-center rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700">
                Export Excel
            </a>
        </div>

        <section class="grid gap-5 md:grid-cols-4">
            @foreach($stats as $stat)
                @php
                    $percentage = $total > 0 ? round(($stat['value'] / $total) * 100, 1) : 0;
                @endphp

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                    <div class="mt-4 flex items-end justify-between gap-3">
                        <p class="text-4xl font-extrabold text-slate-950">{{ number_format($stat['value']) }}</p>
                        <span class="rounded-full {{ $stat['class'] }} px-3 py-1 text-xs font-semibold">
                            {{ number_format($percentage, 1) }}%
                        </span>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Trend Bulanan</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Perbandingan permohonan diluluskan dan ditolak/gagal mengikut bulan untuk {{ $currentYear }}.
                    </p>
                </div>
            </div>

            <div class="h-[360px]">
                <canvas id="monthlyApplicationChart"></canvas>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Permohonan Terkini</h2>
                    <p class="mt-1 text-sm text-slate-500">Rekod terbaru daripada database.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ $latestApplications->count() }} rekod
                </span>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200">
                <div class="hidden md:grid md:grid-cols-[1.2fr_1fr_0.8fr_0.8fr_0.6fr] bg-slate-100 text-sm font-semibold text-slate-700">
                    <div class="px-5 py-4">Nama Pelajar</div>
                    <div class="px-5 py-4">Kategori Bantuan</div>
                    <div class="px-5 py-4">Status</div>
                    <div class="px-5 py-4">Tarikh</div>
                    <div class="px-5 py-4">Detail</div>
                </div>

                <div class="divide-y divide-slate-200">
                    @forelse($latestApplications as $row)
                        <div class="grid gap-3 px-5 py-5 text-sm md:grid-cols-[1.2fr_1fr_0.8fr_0.8fr_0.6fr] md:items-center">
                            <div>
                                <p class="font-semibold text-slate-950">{{ $row->pelajar?->nama_penuh ?? '-' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $row->pelajar?->no_matrik ?? $row->no_kelompok }}</p>
                            </div>

                            <div class="text-slate-600">
                                {{ \App\Models\Permohonan::kategoriBantuanLabel($row->bantuan?->kategori_bantuan) }}
                            </div>

                            <div>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $row->status_badge_class }}">
                                    {{ $row->status_label }}
                                </span>
                            </div>

                            <div class="text-slate-600">
                                {{ optional($row->created_at)->format('d/m/Y') }}
                            </div>

                            <div class="no-print">
                                @if(Route::has('admin.permohonan.show'))
                                    <a href="{{ route('admin.permohonan.show', $row) }}" class="font-semibold text-sky-700 hover:text-sky-900">
                                        Lihat
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <div class="mx-auto max-w-md rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8">
                                <p class="text-sm font-semibold text-slate-700">Tiada permohonan direkodkan.</p>
                                <p class="mt-2 text-sm text-slate-500">Permohonan terbaru akan dipaparkan selepas pelajar menghantar borang.</p>
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
    const monthlyApprovedData = @json($monthlyApprovedData);
    const monthlyRejectedData = @json($monthlyRejectedData);

    new Chart(document.getElementById('monthlyApplicationChart'), {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [
                {
                    label: 'Diluluskan',
                    data: monthlyApprovedData,
                    backgroundColor: '#16A34A',
                    borderRadius: 10,
                    barThickness: 18
                },
                {
                    label: 'Ditolak / Gagal',
                    data: monthlyRejectedData,
                    backgroundColor: '#EF4444',
                    borderRadius: 10,
                    barThickness: 18
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        boxHeight: 8
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    },
                    grid: {
                        color: '#eef2f7'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>
@endsection
