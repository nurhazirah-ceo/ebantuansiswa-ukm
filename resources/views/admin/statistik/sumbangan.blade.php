@extends('layouts.admin')

@section('page-title', 'Statistik Sumbangan')

@section('content')
@php
    $monthLabels = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'];
    $latestTransactionsSearch = $latestTransactionsSearch ?? '';
@endphp

<div class="report-page min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-7xl space-y-7 px-6">
        <x-page-hero
            eyebrow="Laporan"
            title="Statistik Sumbangan"
            description="Kutipan selesai, tabung bantuan dan transaksi penderma."
        />

        <div class="no-print flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            <button type="button" onclick="window.print()" class="inline-flex min-w-[120px] items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-100">Export PDF</button>
            <a href="{{ route('admin.statistik.sumbangan.csv') }}" class="inline-flex min-w-[120px] items-center justify-center rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700">Export Excel</a>
        </div>

        <section class="grid gap-5 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Total Kutipan Selesai</p>
                <p class="mt-3 text-3xl font-extrabold text-slate-950">RM{{ number_format($overallTotal, 2) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-cyan-700">Total Dana Tabung</p>
                <p class="mt-3 text-3xl font-extrabold text-cyan-700">RM{{ number_format($totalCashFund, 2) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Jumlah Transaksi</p>
                <p class="mt-3 text-3xl font-extrabold text-slate-950">{{ number_format($transactionCount) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-emerald-700">Sumbangan Atas Talian</p>
                <p class="mt-3 text-3xl font-extrabold text-emerald-700">RM{{ number_format($simulationTotal, 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $simulationDonations->count() }} transaksi</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Kutipan Sumbangan Bulanan</h2>
                <p class="mt-1 text-sm text-slate-500">Jumlah sumbangan barang dan tabung selesai untuk {{ $currentYear }}.</p>
                <div class="mt-6">
                    <canvas id="monthlyDonationChart" height="105"></canvas>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Kategori Sumbangan</h2>
                <p class="mt-1 text-sm text-slate-500">Pecahan jumlah mengikut kategori.</p>
                <div class="mx-auto mt-6 max-w-sm">
                    @if($yearTotal > 0)
                        <canvas id="donationCategoryChart"></canvas>
                    @else
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center">
                            <p class="text-sm font-semibold text-slate-700">Tiada transaksi selesai.</p>
                            <p class="mt-2 text-sm text-slate-500">Carta kategori akan dipaparkan selepas ada transaksi selesai.</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 space-y-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Transaksi Terbaru</h2>
                        <p class="mt-1 text-sm text-slate-500">Gabungan sumbangan barang dan tabung bantuan.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $latestTransactions->count() }} rekod</span>
                </div>

                <form method="GET" action="{{ route('admin.statistik.sumbangan') }}" class="no-print flex w-full flex-col gap-2 sm:flex-row lg:max-w-3xl">
                    <label for="latest-transaction-search" class="sr-only">Cari transaksi terbaru</label>
                    <input id="latest-transaction-search"
                           type="search"
                           name="q_sumbangan"
                           value="{{ $latestTransactionsSearch }}"
                           placeholder="Cari penderma, rujukan, jenis, kaedah atau status"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100 sm:flex-1">

                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-2xl bg-[#3155E7] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#2647D6]">
                        Cari
                    </button>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200">
                <div class="hidden lg:grid lg:grid-cols-[1fr_1fr_0.8fr_0.75fr_0.8fr_0.8fr] bg-slate-100 text-sm font-semibold text-slate-700">
                    <div class="px-5 py-4">Rujukan</div>
                    <div class="px-5 py-4">Penderma</div>
                    <div class="px-5 py-4">Jenis</div>
                    <div class="px-5 py-4">Jumlah</div>
                    <div class="px-5 py-4">Kaedah</div>
                    <div class="px-5 py-4">Tarikh</div>
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse($latestTransactions as $transaction)
                        <div class="grid gap-3 px-5 py-5 text-sm lg:grid-cols-[1fr_1fr_0.8fr_0.75fr_0.8fr_0.8fr] lg:items-center">
                            <div class="font-semibold text-blue-700 break-all">{{ $transaction['reference'] }}</div>
                            <div>
                                <p class="font-semibold text-slate-950">{{ $transaction['donor'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $transaction['email'] }}</p>
                            </div>
                            <div class="text-slate-600">{{ $transaction['type'] }}</div>
                            <div class="font-semibold text-slate-950">RM{{ number_format($transaction['amount'], 2) }}</div>
                            <div class="text-slate-600">{{ $transaction['method'] }}</div>
                            <div class="text-slate-600">{{ optional($transaction['date'])->format('d/m/Y') }}</div>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <div class="mx-auto max-w-md rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8">
                                <p class="text-sm font-semibold text-slate-700">Tiada transaksi selesai.</p>
                                <p class="mt-2 text-sm text-slate-500">Transaksi sumbangan selesai akan dipaparkan di sini.</p>
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
    const monthlyData = @json($monthly);
    const donationLabels = @json(collect($categories)->pluck('label'));
    const donationValues = @json(collect($categories)->pluck('value'));
    const donationColors = @json(collect($categories)->pluck('color'));
    const donationTotal = @json($yearTotal);

    const centerTextPlugin = {
        id: 'centerText',
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
            ctx.font = '700 20px Arial';
            ctx.fillStyle = '#0f172a';
            ctx.fillText('RM' + Number(donationTotal || 0).toLocaleString('ms-MY'), x, y - 6);
            ctx.font = '500 12px Arial';
            ctx.fillStyle = '#64748b';
            ctx.fillText('Jumlah', x, y + 16);
            ctx.restore();
        }
    };

    new Chart(document.getElementById('monthlyDonationChart'), {
        type: 'line',
        data: {
            labels: @json($monthLabels),
            datasets: [{
                data: monthlyData,
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
                    ticks: { callback(value) { return 'RM ' + Number(value || 0).toLocaleString('ms-MY'); } },
                    grid: { color: '#eef2f7' }
                },
                x: { grid: { display: false } }
            }
        }
    });

    if (document.getElementById('donationCategoryChart')) {
        new Chart(document.getElementById('donationCategoryChart'), {
            type: 'doughnut',
            data: {
                labels: donationLabels,
                datasets: [{ data: donationValues, backgroundColor: donationColors, borderWidth: 0 }]
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
                                    text: `${label} (${donationTotal > 0 ? ((dataset.data[index] / donationTotal) * 100).toFixed(1) : '0.0'}%)`,
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
            plugins: [centerTextPlugin]
        });
    }
</script>
@endsection
