@extends('layouts.admin')

@section('content')

@php
    $adminName = auth()->user()?->name ?? 'Pentadbir';
    $monthDeltaLabel = ($donationMonthDelta ?? 0) >= 0
        ? '+RM' . number_format((float) $donationMonthDelta, 2)
        : '-RM' . number_format(abs((float) $donationMonthDelta), 2);
    $monthDeltaClass = ($donationMonthDelta ?? 0) >= 0 ? 'text-emerald-800 bg-emerald-50' : 'text-rose-800 bg-rose-50';
    $applicationTotal = array_sum($applicationStatusData ?? []);
    $categoryTotal = array_sum($aidCategorySummary['data'] ?? []);
    $cashFundTotal = (float) ($donorSummary['cash_fund_total'] ?? 0);
    $itemUnitsTotal = (int) ($donorSummary['item_units_total'] ?? 0);
    $activeDonors = (int) ($donorSummary['active_donors'] ?? 0);
    $hasCashFundTarget = isset($cashFundTarget) && (float) $cashFundTarget > 0;
    $cashFundMax = $hasCashFundTarget ? (float) $cashFundTarget : max($cashFundTotal, 1);
    $itemUnitsMax = max($itemUnitsTotal, 1);
    $activeDonorMax = max($activeDonors, 1);
    $performanceRows = [
        [
            'label' => 'Tabung Bantuan',
            'value' => 'RM' . number_format($cashFundTotal, 2),
            'helper' => $hasCashFundTarget ? 'Berbanding sasaran tabung.' : 'Jumlah sumbangan wang berjaya.',
            'width' => $cashFundTotal > 0 ? min(100, round(($cashFundTotal / $cashFundMax) * 100, 2)) : 0,
            'shell' => 'border-blue-100 bg-blue-50/70',
            'dot' => 'bg-blue-600',
            'fill' => 'bg-blue-600',
        ],
        [
            'label' => 'Sumbangan Barang',
            'value' => number_format($itemUnitsTotal) . ' Unit',
            'helper' => 'Jumlah unit sumbangan selesai.',
            'width' => $itemUnitsTotal > 0 ? min(100, round(($itemUnitsTotal / $itemUnitsMax) * 100, 2)) : 0,
            'shell' => 'border-emerald-100 bg-emerald-50/70',
            'dot' => 'bg-emerald-500',
            'fill' => 'bg-emerald-500',
        ],
        [
            'label' => 'Penderma Aktif',
            'value' => number_format($activeDonors),
            'helper' => 'Penderma dengan sumbangan berjaya.',
            'width' => $activeDonors > 0 ? min(100, round(($activeDonors / $activeDonorMax) * 100, 2)) : 0,
            'shell' => 'border-indigo-100 bg-indigo-50/70',
            'dot' => 'bg-indigo-500',
            'fill' => 'bg-indigo-500',
        ],
    ];
@endphp

<div class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-7xl space-y-6 px-6">

        <x-page-hero
            eyebrow="Dashboard Pentadbir"
            :title="'Selamat Datang, ' . $adminName"
            description="Pantau ringkasan permohonan, penderma, sumbangan, tabung bantuan dan agihan pelajar."
        />

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($dashboardKpis as $kpi)
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <p class="text-sm font-semibold text-slate-500">{{ $kpi['label'] }}</p>
                        <span class="h-2.5 w-2.5 rounded-full {{ $kpi['soft'] }}"></span>
                    </div>
                    <p class="mt-4 text-3xl font-bold {{ $kpi['color'] }}">{{ $kpi['value'] }}</p>
                    <p class="mt-2 text-xs font-medium text-slate-400">{{ $kpi['note'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_minmax(320px,0.85fr)]">
            <div class="rounded-2xl border border-sky-100 bg-gradient-to-br from-white via-white to-sky-50/70 p-6 shadow-[0_20px_55px_rgba(3,105,161,0.12)]">
                <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold tracking-tight text-slate-950">Kutipan Sumbangan Bulanan</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Jumlah kutipan sumbangan mengikut bulan semasa.
                        </p>
                    </div>

<div class="flex items-center gap-3">
    
    <div class="rounded-2xl border border-sky-100 bg-sky-50/70 px-5 py-3 shadow-sm">
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sky-700">
            Tahun ini
        </p>

        <p class="mt-1 text-2xl font-semibold text-sky-900">
            RM{{ number_format($donationYearTotal, 2) }}
        </p>
    </div>

    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 px-5 py-3 shadow-sm">
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-emerald-700">
            Bulan ini vs lepas
        </p>

        <p class="mt-1 text-2xl font-semibold text-emerald-800">
            {{ $monthDeltaLabel }}
        </p>
    </div>

</div>
                </div>

                <div class="h-[320px]">
                    <canvas id="donationCollectionChart"></canvas>
                </div>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-7 shadow-[0_10px_30px_rgba(15,23,42,0.06)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Prestasi Sumbangan</h2>
                        <p class="mt-1 text-sm text-slate-500">Ringkasan sumbangan dan penderma</p>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    @foreach($performanceRows as $row)
                        <div class="rounded-2xl border px-4 py-4 shadow-sm {{ $row['shell'] }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $row['dot'] }}" aria-hidden="true"></span>
                                        <p class="truncate text-sm font-bold text-slate-900">{{ $row['label'] }}</p>
                                    </div>
                                    <p class="mt-1 text-xs font-medium text-slate-500">{{ $row['helper'] }}</p>
                                </div>
                                <p class="shrink-0 text-sm font-extrabold text-slate-950">{{ $row['value'] }}</p>
                            </div>

                            <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-100">
                                <div class="js-performance-bar h-full rounded-full {{ $row['fill'] }}"
                                     data-progress-width="{{ $row['width'] }}"
                                     style="width: 0%; transition: width 1000ms ease-out;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-extrabold tracking-tight text-slate-950">Status Permohonan</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ number_format($applicationTotal) }} rekod</p>
                    </div>
                </div>

                <div class="mx-auto mt-5 h-[250px] max-w-sm">
                    <canvas id="applicationStatusChart"></canvas>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Kategori Bantuan</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ number_format($categoryTotal) }} pilihan direkod</p>
                    </div>
                </div>

                <div class="mx-auto mt-5 h-[250px] max-w-sm {{ $categoryTotal > 0 ? '' : 'hidden' }}">
                    <canvas id="aidCategoryChart"></canvas>
                </div>

                @if($categoryTotal <= 0)
                    <div class="mt-5 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                        <p class="text-sm font-semibold text-slate-900">Tiada data kategori</p>
                        <p class="mt-1 text-sm text-slate-500">Kategori bantuan akan dipaparkan selepas permohonan direkodkan.</p>
                    </div>
                @endif
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Perlu Tindakan</h2>
                        <p class="mt-1 text-sm text-slate-500">Queue kerja admin</p>
                    </div>
                </div>

                <div class="mt-5 divide-y divide-slate-100">
                    @foreach($actionItems as $item)
                        <a href="{{ $item['href'] }}"
                           class="flex items-center justify-between gap-4 py-4 transition hover:text-blue-700">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $item['label'] }}</p>
                            </div>
                            <span class="shrink-0 rounded-lg px-3 py-1 text-xs font-bold {{ $item['class'] }}">
                                {{ $item['badge'] }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Aktiviti Terkini</h2>
                    <p class="mt-1 text-sm text-slate-500">5 rekod terbaru merentas sistem</p>
                </div>
                <span class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                    Data semasa
                </span>
            </div>

            <div class="mt-5">
                @forelse($recentActivities as $activity)
                    <a href="{{ $activity['href'] }}"
                       class="grid gap-3 border-t border-slate-100 py-4 first:border-t-0 sm:grid-cols-[140px_minmax(0,1fr)_120px] sm:items-center">
                        <span class="w-fit rounded-lg px-3 py-1 text-xs font-bold {{ $activity['class'] }}">
                            {{ $activity['type'] }}
                        </span>
                        <p class="min-w-0 truncate text-sm font-semibold text-slate-900">{{ $activity['title'] }}</p>
                        <p class="text-sm text-slate-500 sm:text-right">{{ $activity['meta'] }}</p>
                    </a>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                        <p class="text-sm font-semibold text-slate-900">Belum ada aktiviti</p>
                        <p class="mt-1 text-sm text-slate-500">Aktiviti terbaru akan dipaparkan di sini.</p>
                    </div>
                @endforelse
            </div>
        </section>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const chartText = '#334155';
    const gridColor = 'rgba(148, 163, 184, 0.16)';

    const tooltipConfig = {
        backgroundColor: '#071633',
        titleColor: '#ffffff',
        bodyColor: '#ffffff',
        padding: 14,
        cornerRadius: 12
    };

    const centerTextPlugin = {
        id: 'centerText',
        beforeDraw(chart) {
            if (chart.config.type !== 'doughnut') return;

            const area = chart.chartArea;
            if (!area) return;

            const ctx = chart.ctx;
            const data = chart.data.datasets[0].data || [];
            const total = data.reduce((sum, value) => sum + Number(value || 0), 0);
            const x = (area.left + area.right) / 2;
            const y = (area.top + area.bottom) / 2;

            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = '#0f172a';
            ctx.font = '700 30px Arial';
            ctx.fillText(total.toLocaleString('ms-MY'), x, y - 7);
            ctx.fillStyle = '#64748b';
            ctx.font = '600 12px Arial';
            ctx.fillText('Jumlah', x, y + 17);
            ctx.restore();
        }
    };

    const months = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'];
    const donationData = @json($donationMonthlyData);

    const donationCanvas = document.getElementById('donationCollectionChart');
    const donationGradient = donationCanvas.getContext('2d').createLinearGradient(0, 0, 0, 320);
    donationGradient.addColorStop(0, 'rgba(14, 165, 233, 0.35)');
    donationGradient.addColorStop(1, 'rgba(14, 165, 233, 0.04)');

    new Chart(donationCanvas, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Kutipan',
                data: donationData,
                borderColor: '#0369a1',
                backgroundColor: donationGradient,
                fill: true,
                tension: 0.42,
                pointRadius: 4,
                pointHoverRadius: 7,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#0369a1',
                pointBorderWidth: 3,
                borderWidth: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1400,
                easing: 'easeOutQuart'
            },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...tooltipConfig,
                    callbacks: {
                        label(context) {
                            return 'RM ' + Number(context.raw || 0).toLocaleString('ms-MY', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: chartText,
                        font: {
                            weight: '700'
                        },
                        callback(value) {
                            return 'RM ' + Number(value || 0).toLocaleString('ms-MY');
                        }
                    },
                    grid: {
                        color: gridColor,
                        drawTicks: false
                    },
                    border: { display: false }
                },
                x: {
                    ticks: {
                        color: chartText,
                        font: {
                            weight: '700'
                        }
                    },
                    grid: { display: false }
                }
            }
        }
    });

    requestAnimationFrame(() => {
        document.querySelectorAll('.js-performance-bar').forEach((bar) => {
            const width = Math.max(0, Math.min(100, Number(bar.dataset.progressWidth || 0)));
            bar.style.width = `${width}%`;
        });
    });

    function createDonutChart(id, labels, data, colors) {
        const total = data.reduce((sum, value) => sum + Number(value || 0), 0);

        new Chart(document.getElementById(id), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 14,
                            color: chartText,
                            generateLabels(chart) {
                                const dataset = chart.data.datasets[0];
                                const chartTotal = dataset.data.reduce((sum, value) => sum + Number(value || 0), 0);

                                return chart.data.labels.map((label, index) => {
                                    const value = Number(dataset.data[index] || 0);
                                    const percentage = chartTotal > 0 ? ((value / chartTotal) * 100).toFixed(1) : '0.0';

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
                    },
                    tooltip: {
                        ...tooltipConfig,
                        callbacks: {
                            label(context) {
                                const value = Number(context.raw || 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';

                                return `${context.label}: ${value.toLocaleString('ms-MY')} (${percentage}%)`;
                            }
                        }
                    }
                }
            },
            plugins: [centerTextPlugin]
        });
    }

    createDonutChart(
        'applicationStatusChart',
        @json($applicationStatusLabels),
        @json($applicationStatusData),
        ['#f59e0b', '#10b981', '#f43f5e', '#0ea5e9']
    );

    if (@json($categoryTotal > 0)) {
        createDonutChart(
            'aidCategoryChart',
            @json($aidCategorySummary['labels']),
            @json($aidCategorySummary['data']),
            ['#0284c7', '#06b6d4', '#10b981', '#f59e0b', '#f43f5e', '#6366f1']
        );
    }
</script>

@endsection
