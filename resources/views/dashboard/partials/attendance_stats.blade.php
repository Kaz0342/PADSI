{{-- DATA JSON UNTUK JS (HIDDEN) --}}
<div id="chart-data-source"
     data-leaderboard="{{ json_encode($leaderboard) }}"
     data-hours="{{ json_encode($hours) }}"
     style="display:none"></div>

<style>
/* =========================================
   📊 CHART PARTIAL SPECIFIC STYLES
   ========================================= */
:root {
    --text-chart-title: #ffffff;
    --text-chart-muted: #9ca3af;
}

/* Glass Card */
.chart-card {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    border-radius: 16px;
    padding: 24px;
    box-shadow: var(--glass-shadow);
    min-height: 340px;
    position: relative;
    overflow: hidden;
    color: white;
    transition: transform 0.3s ease;
}
.chart-card:hover { transform: translateY(-3px); }

/* Header Chart */
.chart-header {
    margin-bottom: 20px;
    display: flex; align-items: center; gap: 10px;
    font-size: 16px; font-weight: 700;
    color: var(--text-chart-title);
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}
.chart-icon { color: var(--accent); font-size: 18px; }

/* Empty State */
.chart-empty {
    position: absolute;
    top: 55%; left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: var(--text-chart-muted);
    font-size: 14px; font-weight: 500;
    width: 100%;
    z-index: 10; pointer-events: none;
}
.empty-icon {
    font-size: 48px; margin-bottom: 12px; opacity: 0.3;
    display: block;
}

/* Chart Container */
.chart-container {
    width: 100%; height: 300px;
    position: relative; z-index: 5;
}

/* Responsive Grid */
.chart-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
    margin-top: 30px;
}
@media (max-width: 768px) { .chart-grid { grid-template-columns: 1fr; } }

/* ApexCharts Override for Dark Mode */
.apexcharts-text { fill: #d1d5db !important; }
.apexcharts-legend-text { color: #fff !important; }
.apexcharts-grid line { stroke: rgba(255,255,255,0.1) !important; }
</style>

<div class="chart-grid">

    {{-- ================= LEADERBOARD ================== --}}
    <div class="chart-card">
        <h4 class="chart-header">
            <i class="fa-solid fa-trophy chart-icon"></i> 
            Top Rajin ({{ \Carbon\Carbon::createFromDate(null, (int)$month, 1)->isoFormat('MMMM') }})
        </h4>

        @if(count($leaderboard) === 0)
            <div class="chart-empty">
                <i class="fa-regular fa-folder-open empty-icon"></i>
                Belum ada data kehadiran bulan ini.
            </div>
        @endif

        <div id="chart-leaderboard" class="chart-container"></div>
    </div>

    {{-- ================= TOTAL JAM KERJA ================== --}}
    <div class="chart-card">
        <h4 class="chart-header">
            <i class="fa-solid fa-clock chart-icon"></i> 
            Total Jam Kerja
        </h4>

        @if(count($hours) === 0)
            <div class="chart-empty">
                <i class="fa-regular fa-clock empty-icon"></i>
                Belum ada data jam kerja bulan ini.
            </div>
        @endif

        <div id="chart-hours" class="chart-container"></div>
    </div>

</div>


{{-- ===================== CHART JS ======================= --}}
<script>
(function() {

    const el = document.getElementById('chart-data-source');
    if(!el) return;

    const leaderboardData = JSON.parse(el.dataset.leaderboard || "[]");
    const hoursData       = JSON.parse(el.dataset.hours || "[]");

    const lbContainer = document.querySelector("#chart-leaderboard");
    const hrContainer = document.querySelector("#chart-hours");

    // ===== COMMON CHART OPTIONS (DARK MODE) =====
    const commonOptions = {
        chart: { 
            toolbar: { show: false },
            foreColor: '#d1d5db', // Text color global
            fontFamily: 'Inter, sans-serif'
        },
        grid: {
            borderColor: 'rgba(255, 255, 255, 0.1)',
            strokeDashArray: 4,
        },
        dataLabels: {
            enabled: true,
            style: { colors: ['#fff'], fontWeight: 700 }
        },
        tooltip: {
            theme: 'dark', // Tooltip dark mode
            style: { fontSize: '12px' }
        }
    };

    // ===== 1. LEADERBOARD CHART =====
    if (leaderboardData.length === 0) {
        lbContainer.style.display = "none";
    } else {
        lbContainer.style.display = "block";

        const options1 = {
            ...commonOptions,
            series: [{
                name: 'Hadir (hari)',
                data: leaderboardData.map(x => x.hadir_count)
            }],
            chart: { ...commonOptions.chart, type: 'bar', height: 300 },
            plotOptions: { 
                bar: { 
                    horizontal: true, 
                    borderRadius: 6,
                    barHeight: '60%',
                    distributed: false // Satu warna aja biar rapi
                } 
            },
            colors: ['#f59e0b'], // Matari Orange
            xaxis: { 
                categories: leaderboardData.map(x => x.nama),
                labels: { style: { colors: '#fff', fontSize: '12px' } }
            },
            yaxis: {
                labels: { style: { colors: '#fff', fontSize: '13px', fontWeight: 600 } }
            }
        };

        new ApexCharts(lbContainer, options1).render();
    }

    // ===== 2. TOTAL HOURS CHART =====
    if (hoursData.length === 0) {
        hrContainer.style.display = "none";
    } else {
        hrContainer.style.display = "block";

        const options2 = {
            ...commonOptions,
            series: [{
                name: 'Total Jam',
                data: hoursData.map(x => parseFloat(x.hours))
            }],
            chart: { ...commonOptions.chart, type: 'bar', height: 300 },
            plotOptions: { 
                bar: { 
                    borderRadius: 6, 
                    columnWidth: '50%',
                    dataLabels: { position: 'top' }
                } 
            },
            colors: ['#3b82f6'], // Blue neon
            xaxis: { 
                categories: hoursData.map(x => x.nama),
                labels: { style: { colors: '#fff', fontSize: '12px' } }
            },
            dataLabels: {
                enabled: true,
                offsetY: -20,
                style: { colors: ['#fff'] }
            }
        };

        new ApexCharts(hrContainer, options2).render();
    }

})();
</script>