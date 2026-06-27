@extends('layouts.admin')

@section('page-title', 'Statistik Inventori')

@section('content')
<div class="report-page min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-7xl space-y-7 px-6">
        <x-page-hero
            eyebrow="Laporan"
            title="Statistik Inventori"
            description="Status stok bantuan dan item yang perlu perhatian."
        />

        <div class="no-print flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            <button type="button" onclick="window.print()" class="inline-flex min-w-[120px] items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-100">Export PDF</button>
            <a href="{{ route('admin.statistik.inventori.csv') }}" class="inline-flex min-w-[120px] items-center justify-center rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700">Export Excel</a>
        </div>

        <section class="grid gap-5 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Jumlah Item</p>
                <p class="mt-3 text-3xl font-extrabold text-slate-950">{{ number_format($summary['total']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-emerald-700">Stok Mencukupi</p>
                <p class="mt-3 text-3xl font-extrabold text-emerald-700">{{ number_format($summary['sufficient']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-amber-700">Stok Rendah</p>
                <p class="mt-3 text-3xl font-extrabold text-amber-700">{{ number_format($summary['low']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-rose-700">Habis Stok</p>
                <p class="mt-3 text-3xl font-extrabold text-rose-700">{{ number_format($summary['empty']) }}</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Trend Stok Bantuan</h2>
                <p class="mt-1 text-sm text-slate-500">Jumlah item stok mengikut kategori bantuan.</p>
                <div class="mt-6">
                    <canvas id="inventoryStockTrendChart" height="105"></canvas>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Status Inventori</h2>
                <p class="mt-1 text-sm text-slate-500">Pecahan stok mengikut status semasa.</p>
                <div class="mx-auto mt-6 max-w-sm">
                    <canvas id="inventoryStatusBreakdownChart"></canvas>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Item Stok Rendah / Habis</h2>
                    <p class="mt-1 text-sm text-slate-500">Item yang perlu disemak oleh admin.</p>
                </div>
                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ $attentionItems->count() }} item</span>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200">
                <div class="hidden md:grid md:grid-cols-[1.2fr_0.9fr_0.75fr_0.75fr_0.75fr_0.8fr] bg-slate-100 text-sm font-semibold text-slate-700">
                    <div class="px-5 py-4">Item</div>
                    <div class="px-5 py-4">Kategori</div>
                    <div class="px-5 py-4">Diperlukan</div>
                    <div class="px-5 py-4">Disumbang</div>
                    <div class="px-5 py-4">Baki</div>
                    <div class="px-5 py-4">Status</div>
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse($attentionItems as $row)
                        @php $item = $row['item']; @endphp
                        <div class="grid gap-3 px-5 py-5 text-sm md:grid-cols-[1.2fr_0.9fr_0.75fr_0.75fr_0.75fr_0.8fr] md:items-center">
                            <div class="font-semibold text-slate-950">{{ $item->nama_item }}</div>
                            <div class="text-slate-600">{{ $item->kategori_bantuan_label }}</div>
                            <div class="text-slate-600">{{ number_format($item->jumlah_diperlukan) }}</div>
                            <div class="text-slate-600">{{ number_format($item->telah_disumbang) }}</div>
                            <div class="font-semibold text-slate-950">{{ number_format($item->baki) }}</div>
                            <div>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $row['status_class'] }}">
                                    {{ $row['status_label'] }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <div class="mx-auto max-w-md rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8">
                                <p class="text-sm font-semibold text-slate-700">Tiada item rendah atau habis.</p>
                                <p class="mt-2 text-sm text-slate-500">Item yang memerlukan perhatian akan dipaparkan di sini.</p>
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
    const inventoryCategoryLabels = @json($categoryStockData->pluck('label'));
    const inventoryNeededValues = @json($categoryStockData->pluck('needed'));
    const inventoryDonatedValues = @json($categoryStockData->pluck('donated'));
    const inventoryRemainingValues = @json($categoryStockData->pluck('remaining'));
    const inventoryStatusLabels = @json(collect($stats)->pluck('label'));
    const inventoryStatusValues = @json(collect($stats)->pluck('value'));
    const inventoryStatusColors = @json(collect($stats)->pluck('color'));
    const inventoryStatusTotal = @json($summary['total']);

    const inventoryCenterTextPlugin = {
        id: 'inventoryCenterText',
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
            ctx.fillText(Number(inventoryStatusTotal || 0).toLocaleString('ms-MY'), x, y - 6);
            ctx.font = '500 12px Arial';
            ctx.fillStyle = '#64748b';
            ctx.fillText('Total item', x, y + 16);
            ctx.restore();
        }
    };

    new Chart(document.getElementById('inventoryStockTrendChart'), {
        type: 'bar',
        data: {
            labels: inventoryCategoryLabels,
            datasets: [
                {
                    label: 'Stok Diperlukan',
                    data: inventoryNeededValues,
                    backgroundColor: 'rgba(2, 132, 199, 0.72)',
                    borderRadius: 10
                },
                {
                    label: 'Telah Disumbang',
                    data: inventoryDonatedValues,
                    backgroundColor: 'rgba(16, 185, 129, 0.72)',
                    borderRadius: 10
                },
                {
                    label: 'Baki',
                    data: inventoryRemainingValues,
                    backgroundColor: 'rgba(245, 158, 11, 0.72)',
                    borderRadius: 10
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true }
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            return `${context.dataset.label}: ${Number(context.raw || 0).toLocaleString('ms-MY')}`;
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

    new Chart(document.getElementById('inventoryStatusBreakdownChart'), {
        type: 'doughnut',
        data: {
            labels: inventoryStatusLabels,
            datasets: [{ data: inventoryStatusValues, backgroundColor: inventoryStatusColors, borderWidth: 0 }]
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
                                text: `${label} (${inventoryStatusTotal > 0 ? ((dataset.data[index] / inventoryStatusTotal) * 100).toFixed(1) : '0.0'}%)`,
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
        plugins: [inventoryCenterTextPlugin]
    });
</script>
@endsection
