<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (() => {
        const labels = @json($labels);
        const values = @json($values);
        const colors = @json($colors);
        const total = {{ $total }};

        const centerTextPlugin = {
            id: 'centerText',
            beforeDraw(chart) {
                const area = chart.chartArea;
                if (!area) return;

                const ctx = chart.ctx;
                const x = (area.left + area.right) / 2;
                const y = (area.top + area.bottom) / 2;

                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.font = '700 28px Arial';
                ctx.fillStyle = '#0f172a';
                ctx.fillText(total, x, y - 6);
                ctx.font = '500 12px Arial';
                ctx.fillStyle = '#64748b';
                ctx.fillText('Jumlah', x, y + 16);
                ctx.restore();
            }
        };

        new Chart(document.getElementById('{{ $chartId }}'), {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data: values, backgroundColor: colors, borderWidth: 0 }]
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
                                return chart.data.labels.map((label, index) => ({
                                    text: `${label} (${((dataset.data[index] / total) * 100).toFixed(1)}%)`,
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
    })();
</script>
