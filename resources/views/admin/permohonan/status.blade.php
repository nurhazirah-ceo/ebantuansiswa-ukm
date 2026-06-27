@extends('layouts.admin')

@section('page-title', 'Status Permohonan')

@section('content')
@php
    $formatPermohonanLabel = fn ($value) => filled($value)
        ? ucwords(str_replace(['_', '-'], ' ', (string) $value))
        : '-';
@endphp

<div class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-7xl space-y-7 px-6">
    <x-page-hero
        eyebrow="STATUS PERMOHONAN PELAJAR"
        title="Pecahan Status Permohonan"
        description="Pantau jumlah permohonan yang masih disemak, diluluskan, ditolak dan lewat diproses."
    />

        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($applicationStats as $stat)
                @php
                    $percentage = $totalApplications > 0 ? round(($stat['value'] / $totalApplications) * 100, 1) : 0;
                @endphp
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                    <div class="mt-5 flex items-end justify-between">
                        <h2 class="text-4xl font-extrabold text-slate-950">{{ $stat['value'] }}</h2>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $stat['class'] }}">
                            {{ $percentage }}%
                        </span>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-5">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
                <h2 class="text-lg font-bold text-slate-950">Status Permohonan</h2>
                <p class="mt-1 text-sm text-slate-500">Pecahan status permohonan pelajar.</p>
                <div class="mx-auto mt-6 max-w-md">
                    <canvas id="applicationStatusChart"></canvas>
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-3">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 class="text-lg font-bold text-slate-950">Rekod Status Terkini</h2>
                    <p class="mt-1 text-sm text-slate-500">Ringkasan permohonan terkini untuk semakan pentadbir.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Kelompok</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Pelajar</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Bantuan</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Tempoh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse($statusRows as $row)
                                @php
                                    $hariPermohonan = $row->tarikh_mohon
                                        ? \Carbon\Carbon::parse($row->tarikh_mohon)->startOfDay()->diffInDays(now()->startOfDay())
                                        : null;
                                    $namaPelajar = $row->pelajar?->nama_penuh ?? $row->pelajar?->no_matrik ?? '-';
                                    $jenisBantuan = $row->bantuan?->jenis_bantuan ?? $row->jenis_bantuan;
                                    $statusLabel = $row->lewat_diproses ? 'Lewat Diproses' : $row->status_label;
                                    $statusClass = $row->lewat_diproses
                                        ? 'bg-indigo-100 text-indigo-700'
                                        : $row->status_badge_class;
                                @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-5 text-sm font-semibold text-blue-700">{{ $row->no_kelompok ?? '-' }}</td>
                                    <td class="px-6 py-5 text-sm text-slate-700">{{ $namaPelajar }}</td>
                                    <td class="px-6 py-5 text-sm text-slate-700">{{ $formatPermohonanLabel($jenisBantuan) }}</td>
                                    <td class="px-6 py-5">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-sm text-slate-700">{{ $hariPermohonan !== null ? $hariPermohonan . ' hari' : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="mx-auto max-w-md rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8">
                                            <p class="text-sm font-semibold text-slate-700">Tiada permohonan ditemui.</p>
                                            <p class="mt-2 text-sm text-slate-500">Rekod status terkini akan dipaparkan selepas permohonan dihantar.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const applicationLabels = @json(collect($applicationStats)->pluck('label'));
    const applicationData = @json(collect($applicationStats)->pluck('value'));
    const applicationColors = @json(collect($applicationStats)->pluck('color'));

    const totalApplications = applicationData.reduce((sum, value) => sum + value, 0);

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
            ctx.fillText(totalApplications, centerX, centerY - 6);
            ctx.font = '500 12px Arial';
            ctx.fillStyle = '#64748b';
            ctx.fillText('Jumlah', centerX, centerY + 16);
            ctx.restore();
        }
    };

    new Chart(document.getElementById('applicationStatusChart'), {
        type: 'doughnut',
        data: {
            labels: applicationLabels,
            datasets: [{
                data: applicationData,
                backgroundColor: applicationColors,
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
                                const percentage = totalApplications > 0 ? ((value / totalApplications) * 100).toFixed(1) : 0;

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
</script>
@endsection
