/*
 * Grafik dashboard admin (Chart.js dari public/vendor/chartjs).
 * Data dari Blade: <canvas data-chart='{"labels":[], "values":[], "format":"number|rupiah", "label":""}'>
 * Satu seri per grafik, satu sumbu Y; judul kartu menamai seri sehingga tidak perlu legenda.
 */
const css = getComputedStyle(document.documentElement);
const ink = css.getPropertyValue('--rk-cocoa').trim() || '#3E2C2E';
const barColor = css.getPropertyValue('--rk-rose-deep').trim() || '#9E5560';

const rupiah = (value) => 'Rp ' + Number(value).toLocaleString('id-ID');
const shortRupiah = (value) => {
    if (value >= 1e9) return 'Rp ' + (value / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' M';
    if (value >= 1e6) return 'Rp ' + (value / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt';
    return rupiah(value);
};

Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
Chart.defaults.color = 'rgba(62, 44, 46, .7)';

document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
    const data = JSON.parse(canvas.dataset.chart);
    const isRupiah = data.format === 'rupiah';

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: data.label,
                data: data.values,
                backgroundColor: barColor,
                hoverBackgroundColor: ink,
                borderRadius: { topLeft: 4, topRight: 4 },
                borderSkipped: 'bottom',
                maxBarThickness: 28,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 400 },
            interaction: { mode: 'index', intersect: false }, // tooltip muncul di seluruh kolom bulan, bukan hanya di batang
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: ink,
                    displayColors: false,
                    callbacks: {
                        label: (context) => `${data.label}: ${isRupiah ? rupiah(context.parsed.y) : context.parsed.y}`,
                    },
                },
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: 'rgba(62, 44, 46, .08)' },
                    ticks: {
                        precision: 0,
                        maxTicksLimit: 5,
                        callback: (value) => (isRupiah ? shortRupiah(value) : value),
                    },
                },
            },
        },
    });
});
