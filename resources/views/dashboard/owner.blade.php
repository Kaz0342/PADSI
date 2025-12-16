@extends('layouts.app')
@section('title', 'Dashboard Owner')

@section('content')

@php
    $month = request()->get('month', now()->month);
    $year  = request()->get('year', now()->year);
@endphp

<style>
/* === GLOBAL THEME === */
body {
    background: url('/images/bg-matari.jpg') no-repeat center center/cover fixed !important;
    color: #fff !important;
}
body::before {
    content: ""; position: fixed; inset: 0;
    background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: -1;
}

:root{
    --accent: #f7a20a;
    --glass-bg: rgba(255, 255, 255, 0.12);
    --glass-border: rgba(255, 255, 255, 0.2);
    --text-main: #ffffff;
    --text-muted: #d1d5db;
}

/* DASHBOARD GRID */
.dashboard-grid{ display:grid; grid-template-columns: repeat(4,1fr); gap:18px; margin-bottom:28px; }
.stat-card {
    background: var(--glass-bg); backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border); padding: 18px; border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2); transition: transform 0.2s;
}
.stat-card:hover { transform: translateY(-3px); }
.stat-title { font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 8px; }
.stat-value { font-size: 32px; font-weight: 800; display: flex; align-items: center; gap: 8px; color: var(--text-main); }

/* CALENDAR STYLING */
.absensi-wrapper{ width:100%; margin-top: 10px; }
.absensi-wrapper .calendar-card{
    background: var(--glass-bg); backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border); padding: 24px; border-radius: 18px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.2); position: relative; color: white;
}
.absensi-wrapper .calendar-header{
    display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;
    border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;
}

.absensi-wrapper .calendar-grid{
    display:grid; grid-template-columns: repeat(7,minmax(92px,1fr)); gap:10px; min-width:700px;
}
.absensi-wrapper .calendar-day-header{
    background: rgba(0,0,0,0.3); padding:10px 8px; text-align:center;
    border-radius:8px; font-weight:700; color: var(--text-muted); font-size:14px;
}

/* CELL STYLE */
.absensi-wrapper .calendar-cell{
    background: rgba(255,255,255,0.07); 
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px; padding: 12px; min-height: 92px;
    display: flex; flex-direction: column; justify-content: flex-start; align-items: flex-start;
    transition: 0.2s all; position: relative;
}

/* Hanya hover jika bulan aktif */
.absensi-wrapper .calendar-cell.is-current:hover{
    transform: translateY(-4px); cursor: pointer;
    background: rgba(247, 162, 10, 0.15); border-color: var(--accent);
    box-shadow: 0 5px 15px rgba(247, 162, 10, 0.2);
}

/* Bulan Lain (Redup) */
.absensi-wrapper .not-current-month{
    background: rgba(0,0,0,0.2); opacity: 0.5;
    border-color: transparent; pointer-events: none; /* Gak bisa diklik */
}

.absensi-wrapper .cell-date { font-size:18px; font-weight:700; color:white; margin-bottom:5px; }
.absensi-wrapper .cell-dots { display:flex; gap:5px; margin:4px 0; }

.dot-small{ width:8px; height:8px; border-radius:50%; box-shadow: 0 0 5px rgba(255,255,255,0.5); }
.dot-green{ background:#4ade80; }
.dot-yellow{ background:#facc15; }
.dot-red{ background:#f87171; }

.absensi-wrapper .today-cell{ border: 2px solid var(--accent); background: rgba(247, 162, 10, 0.1) !important; }

/* CONTROLS & MODAL */
.glass-select {
    background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.3); color: white;
    padding: 10px 15px; border-radius: 10px; outline: none; font-weight: 600; cursor: pointer;
}
.glass-select option { background: #333; color: white; }

.absensi-wrapper .modal-back{
    display:none; position:fixed; inset:0; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px);
    justify-content:center; align-items:center; z-index:2000; padding:20px;
}
.absensi-wrapper .modal-detail-content{
    background: rgba(30, 30, 30, 0.85); border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(16px);
    padding: 24px; border-radius: 20px; max-width: 600px; width: 100%; max-height: 90vh; overflow: auto;
    box-shadow: 0 20px 50px rgba(0,0,0,0.5); color: white;
}
.detail-item-card{
    background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px; padding: 14px; margin-bottom: 10px;
}
.calendar-loading {
    display:none; position:absolute; inset:0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);
    z-index:99; border-radius:18px; justify-content:center; align-items:center;
}

@media (max-width:1100px){ .absensi-wrapper .calendar-grid{ min-width:640px; gap:8px; } }
@media (max-width:780px){ .dashboard-grid { grid-template-columns: repeat(2,1fr); } }
@media (max-width:480px){ .dashboard-grid { grid-template-columns: 1fr; } .absensi-wrapper .calendar-grid { min-width: 100%; grid-template-columns: repeat(7, 1fr); } }
</style>

{{-- DASHBOARD CONTENT --}}
<div style="background: rgba(247, 162, 10, 0.25); border:1px solid rgba(247,162,10,0.4); backdrop-filter:blur(10px); color:white; padding:24px; border-radius:16px; margin-bottom:28px; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
    <h2 style="margin:0; font-size:24px; text-shadow:0 2px 4px rgba(0,0,0,0.3);">Selamat Datang, {{ Auth::user()->name ?? 'Owner' }}! 🔥</h2>
    <p style="margin:6px 0 0; font-size:14px; opacity:0.9;">Pantau performa kedai sambil ngopi santai.</p>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-title">Karyawan Aktif</div>
        <div class="stat-value" style="color:#4ade80;"><i class="fa-solid fa-users"></i> {{ $karyawanAktif ?? 0 }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Sedang Cuti</div>
        <div class="stat-value" style="color:#facc15;"><i class="fa-solid fa-plane-departure"></i> {{ $karyawanCuti ?? 0 }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Hadir Hari Ini</div>
        <div class="stat-value" style="color:#60a5fa;"><i class="fa-solid fa-clipboard-check"></i> {{ $hadirHariIni ?? 0 }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Stok Rendah</div>
        <div class="stat-value" style="color:#f87171;"><i class="fa-solid fa-triangle-exclamation"></i> {{ $stokRendah ?? 0 }}</div>
    </div>
</div>

{{-- KALENDER ABSENSI --}}
<div class="absensi-wrapper">
    <div class="calendar-card">
        <div id="calendar-loading" class="calendar-loading">
            <i class="fa-solid fa-spinner fa-spin" style="font-size:36px; color:var(--accent)"></i>
        </div>

        <div class="calendar-header">
            <h3 style="margin:0; font-size:20px; display:flex; align-items:center; gap:10px;">
                <i class="fa-regular fa-calendar-days" style="color:var(--accent)"></i> Kalender Absensi
            </h3>
            <div style="display:flex; gap:10px;">
                <select id="calendar-month" class="glass-select" style="width:140px;">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                <select id="calendar-year" class="glass-select" style="width:110px;">
                     @for ($y = 2023; $y <= 2029; $y++)
                        <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="calendar-grid" id="calendarGrid" aria-live="polite">
            <div class="calendar-day-header">Min</div><div class="calendar-day-header">Sen</div>
            <div class="calendar-day-header">Sel</div><div class="calendar-day-header">Rab</div>
            <div class="calendar-day-header">Kam</div><div class="calendar-day-header">Jum</div>
            <div class="calendar-day-header">Sab</div>
        </div>

        <div style="text-align:center; margin-top:20px; font-size:13px; font-weight:600; color:var(--text-muted); display:flex; justify-content:center; gap:24px;">
            <div style="display:flex;align-items:center;gap:6px;"><span class="dot-small dot-green"></span> Hadir</div>
            <div style="display:flex;align-items:center;gap:6px;"><span class="dot-small dot-yellow"></span> Terlambat</div>
            <div style="display:flex;align-items:center;gap:6px;"><span class="dot-small dot-red"></span> Absen</div>
        </div>
    </div>
</div>

{{-- STATISTIK (CHART) --}}
<div style="display:flex; gap:12px; margin-bottom:20px; align-items:center; margin-top:30px;">
    <h3 style="margin:0; font-size:18px; color:white; margin-right:auto;"><i class="fa-solid fa-chart-simple" style="color:var(--accent)"></i> Statistik</h3>
    <select id="stats-month" class="glass-select" style="width:140px;">
        @for ($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
        @endfor
    </select>
    <select id="stats-year" class="glass-select" style="width:110px;">
        @for ($y = 2023; $y <= now()->year; $y++)
            <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
    </select>
</div>

<div id="stats-container" style="background: var(--glass-bg); backdrop-filter: blur(12px); border: 1px solid var(--glass-border); border-radius:18px; overflow:hidden;">
    <div style="padding:40px; text-align:center; color:white;">
        <i class="fa-solid fa-circle-notch fa-spin" style="font-size:24px; color:var(--accent)"></i> 
        <div style="margin-top:10px;">Memuat data statistik...</div>
    </div>
</div>

{{-- MODAL DETAIL --}}
<div class="absensi-wrapper">
    <div id="detailModal" class="modal-back">
        <div class="modal-detail-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 id="detail-title" style="margin:0; font-size:20px; color:var(--accent);">Detail Absensi</h3>
                <button style="border:none; background:none; font-size:24px; color:white; cursor:pointer;" onclick="closeModal('detailModal')">&times;</button>
            </div>
            <div style="display:flex; gap:14px; margin-bottom:20px;">
                <div style="flex:1; text-align:center; background:rgba(255,255,255,0.05); padding:12px; border-radius:12px;">
                    <div id="present-count" style="font-size:24px; font-weight:800; color:#4ade80;">0</div>
                    <div style="font-size:11px; opacity:0.7; margin-top:4px;">Hadir</div>
                </div>
                <div style="flex:1; text-align:center; background:rgba(255,255,255,0.05); padding:12px; border-radius:12px;">
                    <div id="late-count" style="font-size:24px; font-weight:800; color:#facc15;">0</div>
                    <div style="font-size:11px; opacity:0.7; margin-top:4px;">Terlambat</div>
                </div>
                <div style="flex:1; text-align:center; background:rgba(255,255,255,0.05); padding:12px; border-radius:12px;">
                    <div id="alpha-count" style="font-size:24px; font-weight:800; color:#f87171;">0</div>
                    <div style="font-size:11px; opacity:0.7; margin-top:4px;">Alpha</div>
                </div>
            </div>
            <div id="detail-list">
                <div style="text-align:center; padding:20px; opacity:0.6;">Pilih tanggal untuk melihat detail</div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
// --- AJAX LOAD STATS ---
document.addEventListener("DOMContentLoaded", () => {
    function loadStats() {
        const m = document.getElementById("stats-month").value;
        const y = document.getElementById("stats-year").value;
        const container = document.getElementById("stats-container");

        container.innerHTML = `
            <div style="padding:40px; text-align:center; color:white;">
                <i class="fa-solid fa-circle-notch fa-spin" style="font-size:24px; color:#f7a20a"></i> 
                <div style="margin-top:10px;">Sedang mengambil data...</div>
            </div>
        `;

        fetch(`/dashboard/stats?month=${m}&year=${y}`)
            .then(res => res.text())
            .then(html => {
                container.innerHTML = html;
                const scripts = container.getElementsByTagName("script");
                for(let i=0; i<scripts.length; i++) { eval(scripts[i].innerText); }
            })
            .catch(err => {
                container.innerHTML = `<div style="padding:20px;text-align:center;color:#f87171;">Gagal memuat statistik.</div>`;
            });
    }
    document.getElementById("stats-month").addEventListener("change", loadStats);
    document.getElementById("stats-year").addEventListener("change", loadStats);
    loadStats();
});

// --- 🔥 LOGIC KALENDER UTAMA ---

const calendarMonth = document.getElementById('calendar-month');
const calendarYear  = document.getElementById('calendar-year');
const calendarGrid  = document.getElementById('calendarGrid');
const loader        = document.getElementById('calendar-loading');

function openModal(id){ document.getElementById(id).style.display = 'flex'; }
function closeModal(id){ document.getElementById(id).style.display = 'none'; }

// Event listener ganti bulan/tahun
calendarMonth.addEventListener('change', () => fetchCalendar());
calendarYear.addEventListener('change', () => fetchCalendar());

// Load awal
document.addEventListener('DOMContentLoaded', () => fetchCalendar());

function fetchCalendar(){
  const m = calendarMonth.value;
  const y = calendarYear.value;
  
  loader.style.display = 'flex';
  
  fetch(`/api/absensi/rekap/calendar?month=${m}&year=${y}`)
    .then(r => r.json())
    .then(data => {
      // Backend ngirim JSON: { calendar: [...] }
      renderCalendar(data);
      loader.style.display = 'none';
    })
    .catch(err => {
      console.error(err);
      loader.style.display = 'none';
    });
}

// 🔥 FUNGSI RENDER (Fixed Structure) 🔥
function renderCalendar(days){
    const headers = Array.from(calendarGrid.children).slice(0, 7);
    calendarGrid.innerHTML = "";
    headers.forEach(h => calendarGrid.appendChild(h));

    if (!Array.isArray(days)) {
        console.error("Data kalender bukan array:", days);
        return;
    }

    const activeMonth = Number(calendarMonth.value);
    const activeYear  = Number(calendarYear.value);

    days.forEach(day => {
        if (!day.date) return; // safety

        const dateObj = new Date(day.date + "T00:00:00");
        if (isNaN(dateObj)) return;

        const dayNum = dateObj.getDate();
        const month  = dateObj.getMonth() + 1;
        const year   = dateObj.getFullYear();

        const isCurrentMonth = (month === activeMonth && year === activeYear);

        const cell = document.createElement("div");
        cell.className = "calendar-cell" + (isCurrentMonth ? "" : " not-current-month");

        if (isCurrentMonth) {
            cell.style.pointerEvents = "auto";
            cell.onclick = () => openDayDetail(day.date);
        } else {
            cell.style.pointerEvents = "none";
        }

        cell.innerHTML = `
            <div class="cell-date">${String(dayNum).padStart(2,'0')}</div>
            <div class="cell-dots">
                ${day.hadir > 0 ? '<span class="dot-small dot-green"></span>' : ''}
                ${day.terlambat > 0 ? '<span class="dot-small dot-yellow"></span>' : ''}
                ${(day.alpha ?? 0) > 0 ? '<span class="dot-small dot-red"></span>' : ''}
            </div>
        `;

        calendarGrid.appendChild(cell);
    });
}

// --- MODAL DETAIL ---
function openDayDetail(date) {
    if (!date) return;
    openModal('detailModal');

    // Reset isi modal
    document.getElementById('detail-title').innerText = 'Memuat...';
    document.getElementById('present-count').innerText = '0';
    document.getElementById('late-count').innerText = '0';
    document.getElementById('alpha-count').innerText = '0';
    const list = document.getElementById('detail-list');
    list.innerHTML = `<div style="text-align:center;padding:20px;opacity:0.6;">Sabar King, memuat data...</div>`;

    fetch(`/api/absensi/rekap/detail?date=${encodeURIComponent(date)}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('detail-title').innerText = data.date_formatted || date;
            
            const summary = data.summary || {};
            document.getElementById('present-count').innerText = (summary.hadir || 0) + (summary.pengganti || 0);
            document.getElementById('late-count').innerText = summary.terlambat || 0;
            document.getElementById('alpha-count').innerText = summary.alpha || 0;

            const rows = data.rows || [];
            list.innerHTML = ""; 

            if (rows.length === 0) {
                list.innerHTML = `
                    <div style="text-align:center;padding:20px;opacity:0.7;">
                        Tidak ada absensi pada tanggal ini
                    </div>
                `;
                return;
            }

            rows.forEach(r => {
                const item = document.createElement('div');
                item.className = 'detail-item-card';

                let color = '#fff';
                let st = (r.status || '').toUpperCase(); // status_kehadiran

                if (st === 'HADIR' || st === 'PENGGANTI') color = '#4ade80';
                else if (st === 'TERLAMBAT') color = '#facc15';
                else if (st === 'ALPHA') color = '#f87171';

                item.innerHTML = `
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div style="font-weight:700;">
                            ${r.nama}
                            <span style="opacity:0.6;font-size:12px;"> · ${r.posisi}</span>
                        </div>
                        <div style="color:${color};font-weight:800;font-size:11px;
                                    border:1px solid ${color};
                                    padding:2px 6px;border-radius:4px;">
                            ${st}
                        </div>
                    </div>
                    <div style="margin-top:6px;font-size:13px;opacity:0.8;">
                        In: ${r.check_in ?? '-'} | Out: ${r.check_out ?? '-'}
                        ${ r.catatan ? `<div style="margin-top:4px;font-style:italic;">"${r.catatan}"</div>` : "" }
                    </div>
                `;
                list.appendChild(item);
            });
        })
        .catch(() => {
            list.innerHTML = `<div style="text-align:center;padding:20px;color:#f87171;">Gagal memuat data.</div>`;
        });
}
</script>
@endpush