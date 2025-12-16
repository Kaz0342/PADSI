@extends('layouts.app')

@section('title', 'Rekapitulasi Absensi')
@section('page_title', 'Rekap Kehadiran Bulanan')

@section('content')

{{-- ALERT KHUSUS IMPORT CSV (JIKA ADA DATA PENDING) --}}
@php $lastImport = \App\Models\CsvImport::latest()->first(); @endphp
@if($lastImport && $lastImport->rows()->where('approved',false)->exists())
    <div style="background: rgba(247, 162, 10, 0.15); border: 1px solid rgba(247, 162, 10, 0.3); padding: 15px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; backdrop-filter: blur(10px);">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-triangle-exclamation" style="color: #facc15; font-size: 20px;"></i>
            <div>
                <strong style="color: white; font-size: 14px;">Import CSV Menunggu Verifikasi</strong>
                <div style="font-size: 12px; color: #d1d5db; margin-top: 2px;">Ada {{ $lastImport->rows()->where('approved',false)->count() }} baris data baru yang belum di-approve.</div>
            </div>
        </div>
        <a href="{{ route('csv.import.process', $lastImport->id) }}" class="btn" style="padding: 8px 16px; font-size: 12px;">Verifikasi Sekarang</a>
    </div>
@endif

<style>
/* =========================================
   🎨 REKAP PAGE SPECIFIC STYLES
   ========================================= */
:root {
    --text-mid: #9ca3af;
}

/* Glass Card Container */
.calendar-card {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    border-radius: 16px;
    box-shadow: var(--glass-shadow);
    padding: 24px;
    color: white;
    position: relative;
    overflow: hidden;
}

/* Header */
.calendar-header {
    display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;
}

.nav-btn {
    background: rgba(255,255,255,0.1);
    border: 1px solid var(--glass-border);
    padding: 8px 14px; border-radius: 8px;
    font-weight: 600; color: white; cursor: pointer;
    transition: 0.2s;
}
.nav-btn:hover { background: var(--accent); border-color: var(--accent); color: #fff; }

/* Calendar Grid */
.calendar-grid-wrap { overflow-x: auto; }
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(90px, 1fr));
    gap: 8px; min-width: 700px;
}

.calendar-day-header {
    padding: 10px; text-align: center;
    font-weight: 700; color: var(--text-mid); font-size: 13px;
    text-transform: uppercase; letter-spacing: 0.5px;
    background: rgba(0,0,0,0.2); border-radius: 8px;
}

/* Calendar Cell */
.calendar-cell {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px; min-height: 95px;
    padding: 10px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    cursor: pointer; transition: all 0.2s; position: relative;
}

.calendar-cell:hover:not(.not-current-month) {
    background: rgba(247, 162, 10, 0.15);
    border-color: var(--accent);
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.not-current-month {
    background: rgba(0,0,0,0.2) !important;
    opacity: 0.5; cursor: default; border-color: transparent !important;
}

.today-cell {
    border: 2px solid var(--accent);
    background: rgba(247, 162, 10, 0.1) !important;
}

.calendar-cell.selected {
    outline: 2px solid #4ade80;
    box-shadow: 0 0 15px rgba(74, 222, 128, 0.4);
}

/* Cell Content */
.cell-date { font-weight: 800; font-size: 16px; margin-bottom: 6px; color: white; text-shadow: 0 1px 2px rgba(0,0,0,0.5); }
.cell-dots { display: flex; gap: 4px; margin-bottom: 6px; }
.dot-small { width: 6px; height: 6px; border-radius: 50%; box-shadow: 0 0 5px rgba(255,255,255,0.5); }
.dot-green { background: #4ade80; }
.dot-yellow { background: #facc15; }
.dot-red { background: #f87171; }
.cell-info { font-size: 11px; color: var(--text-mid); font-weight: 600; text-align: center; }

/* Legend */
.legend { display: flex; justify-content: center; gap: 20px; margin-top: 20px; color: var(--text-mid); font-size: 12px; font-weight: 600; }

/* Modal Detail (Glass) */
.modal-back {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.8); backdrop-filter: blur(5px);
    z-index: 999; align-items: center; justify-content: center; padding: 20px;
}
.modal-detail-content {
    background: rgba(30, 30, 30, 0.9);
    border: 1px solid var(--glass-border);
    backdrop-filter: blur(15px);
    width: 100%; max-width: 600px;
    border-radius: 16px; padding: 24px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.5);
    color: white; position: relative;
    max-height: 85vh; overflow-y: auto;
}
.close-btn { position: absolute; right: 20px; top: 20px; cursor: pointer; color: var(--text-mid); font-size: 20px; }
.close-btn:hover { color: white; }

.summary-box {
    display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;
}
.summary-item {
    flex: 1; background: rgba(255,255,255,0.05); padding: 10px; border-radius: 10px; text-align: center;
}
.summary-val { font-size: 20px; font-weight: 800; margin-bottom: 4px; }
.summary-lbl { font-size: 11px; opacity: 0.7; }

.detail-item-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    padding: 12px; border-radius: 10px; margin-bottom: 10px;
}
.status-badge { padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
.status-hadir-modal { background: rgba(74, 222, 128, 0.2); color: #4ade80; border: 1px solid #4ade80; }
.status-terlambat-modal { background: rgba(250, 204, 21, 0.2); color: #facc15; border: 1px solid #facc15; }
.status-alpha-modal { background: rgba(248, 113, 113, 0.2); color: #f87171; border: 1px solid #f87171; }
.status-pengganti-modal { background: rgba(96, 165, 250, 0.2); color: #60a5fa; border: 1px solid #60a5fa; }

</style>

<div class="calendar-card">
    {{-- LOADING SPINNER --}}
    <div id="calendar-loading" style="display:none; position:absolute; inset:0; background:rgba(0,0,0,0.7); z-index:40; align-items:center; justify-content:center; backdrop-filter: blur(2px);">
        <i class="fa-solid fa-spinner fa-spin" style="font-size:36px; color:var(--accent)"></i>
    </div>

    {{-- HEADER --}}
    <div class="calendar-header">
        <h3 style="margin:0; font-size:20px; display:flex; align-items:center; gap:10px;">
            <i class="fa-regular fa-calendar-days" style="color:var(--accent)"></i> Kalender Absensi
        </h3>

        <div class="calendar-nav">
            <button class="nav-btn" id="prevBtn"><i class="fa-solid fa-chevron-left"></i> Prev</button>
            <div id="currentMonthLabel" style="font-weight:800; min-width:140px; text-align:center; font-size:16px;">Loading...</div>
            <button class="nav-btn" id="nextBtn">Next <i class="fa-solid fa-chevron-right"></i></button>
        </div>
    </div>

    {{-- GRID --}}
    <div class="calendar-grid-wrap">
        <div class="calendar-grid" id="calendarGrid">
            <div class="calendar-day-header">Min</div><div class="calendar-day-header">Sen</div>
            <div class="calendar-day-header">Sel</div><div class="calendar-day-header">Rab</div>
            <div class="calendar-day-header">Kam</div><div class="calendar-day-header">Jum</div>
            <div class="calendar-day-header">Sab</div>
        </div>
    </div>

    {{-- LEGEND --}}
    <div class="legend">
        <div style="display:flex;align-items:center;gap:6px;"><span class="dot-small dot-green"></span> Hadir</div>
        <div style="display:flex;align-items:center;gap:6px;"><span class="dot-small dot-yellow"></span> Terlambat</div>
        <div style="display:flex;align-items:center;gap:6px;"><span class="dot-small dot-red"></span> Absen</div>
    </div>
</div>

{{-- MODAL DETAIL --}}
<div class="modal-back" id="detailModal">
    <div class="modal-detail-content">
        <span class="close-btn" onclick="closeModal('detailModal')">&times;</span>
        <h3 id="detail-title" style="margin:0 0 20px 0; font-weight:700; color:var(--accent);">Detail Absensi</h3>

        <div class="summary-box">
            <div class="summary-item">
                <div class="summary-val" style="color:#4ade80;" id="present-count">0</div>
                <div class="summary-lbl">Hadir</div>
            </div>
            <div class="summary-item">
                <div class="summary-val" style="color:#facc15;" id="late-count">0</div>
                <div class="summary-lbl">Terlambat</div>
            </div>
            <div class="summary-item">
                <div class="summary-val" style="color:#f87171;" id="alpha-count">0</div>
                <div class="summary-lbl">Alpha</div>
            </div>
        </div>

        <div id="detail-list">
            <div style="text-align:center; padding:20px; opacity:0.6;">Memuat data...</div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentMonth = {{ now()->month }};
let currentYear  = {{ now()->year }};

const calendarGrid = document.getElementById('calendarGrid');
const currentMonthLabel = document.getElementById('currentMonthLabel');
const loader = document.getElementById('calendar-loading');

document.getElementById('prevBtn').addEventListener('click', () => changeMonth(-1));
document.getElementById('nextBtn').addEventListener('click', () => changeMonth(1));

function openModal(id){ document.getElementById(id).style.display = 'flex'; }
function closeModal(id){ document.getElementById(id).style.display = 'none'; }

document.addEventListener('DOMContentLoaded', () => { fetchCalendar(currentMonth, currentYear); });

function changeMonth(dir){
    currentMonth += dir;
    if (currentMonth > 12){ currentMonth = 1; currentYear++; }
    if (currentMonth < 1) { currentMonth = 12; currentYear--; }
    fetchCalendar(currentMonth, currentYear);
}

// 🔥 FUNGSI FETCH FIX (Handling Structure)
function fetchCalendar(month, year){
    loader.style.display = 'flex';

    fetch(`/api/absensi/rekap/calendar?month=${month}&year=${year}`)
        .then(r => r.json())
        .then(data => {
            // Kita jaga-jaga: kalo backend kirim object {calendar: [...]}, kita ambil .calendar
            // Kalo backend kirim array langsung [...], kita ambil langsung
            const days = data.calendar || data || [];
            
            renderCalendar(days, month, year);
            loader.style.display = 'none';
        })
        .catch(err => {
            console.error(err);
            currentMonthLabel.innerText = 'Error';
            loader.style.display = 'none';
        });
}

// 🔥 FUNGSI RENDER FIX (Auto Labeling)
function renderCalendar(days, month, year){
    // Set label bulan (Pake JS lokal biar gak perlu nunggu data backend)
    const date = new Date(year, month - 1, 1);
    currentMonthLabel.innerText = date.toLocaleDateString('id-ID', {
        month: 'long',
        year: 'numeric'
    });

    // Reset grid (Keep Header)
    const headers = Array.from(calendarGrid.children).slice(0, 7);
    calendarGrid.innerHTML = '';
    headers.forEach(h => calendarGrid.appendChild(h));

    if (!Array.isArray(days)) {
        console.error("Format data kalender salah:", days);
        return;
    }

    days.forEach(day => {
        const cell = document.createElement('div');

        let classes = 'calendar-cell';
        if (!day.isCurrentMonth) classes += ' not-current-month';
        if (day.isToday) classes += ' today-cell';

        cell.className = classes;
        
        // Render Cell Content
        // Label tanggal kita ambil dari 'label' (kiriman backend) atau fallback ke 'date'
        const label = day.label ? String(day.label).padStart(2,'0') : day.date.split('-')[2];

        cell.innerHTML = `
            <div class="cell-date">${label}</div>
            <div class="cell-dots">
                ${day.hadir > 0 ? '<span class="dot-small dot-green"></span>' : ''}
                ${day.terlambat > 0 ? '<span class="dot-small dot-yellow"></span>' : ''}
                ${day.alpha > 0 ? '<span class="dot-small dot-red"></span>' : ''}
            </div>
        `;

        if (day.isCurrentMonth) {
            cell.dataset.date = day.date;
            cell.onclick = () => openDayDetail(cell);
        }

        calendarGrid.appendChild(cell);
    });
}

function openDayDetail(el){
    const date = el.dataset.date;
    if (!date) return;

    openModal('detailModal');
    const list = document.getElementById('detail-list');
    document.getElementById('present-count').innerText = document.getElementById('late-count').innerText = document.getElementById('alpha-count').innerText = '...';
    list.innerHTML = `<div style="text-align:center;padding:20px;opacity:0.6;"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div>`;

    fetch(`/api/absensi/rekap/detail?date=${date}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('detail-title').innerText = data.date_formatted;
            
            const summary = data.summary || {};
            document.getElementById('present-count').innerText = (summary.hadir || 0) + (summary.pengganti || 0);
            document.getElementById('late-count').innerText = summary.terlambat || 0;
            document.getElementById('alpha-count').innerText = summary.alpha || 0;

            list.innerHTML = '';
            const rows = data.rows || [];
            
            if (!rows.length) {
                list.innerHTML = `<div style="text-align:center;padding:20px;opacity:0.6;">Tidak ada data absensi.</div>`;
                return;
            }

            rows.forEach(r => {
                const item = document.createElement('div');
                item.className = 'detail-item-card';
                
                let stClass = 'status-hadir-modal';
                if(r.status_kehadiran == 'terlambat') stClass = 'status-terlambat-modal';
                if(r.status_kehadiran == 'alpha') stClass = 'status-alpha-modal';
                if(r.status_kehadiran == 'pengganti') stClass = 'status-pengganti-modal';

                item.innerHTML = `
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div style="font-weight:700;">${r.nama} <span style="opacity:0.6;font-weight:400;font-size:12px;"> · ${r.posisi || '-'}</span></div>
                        <span class="status-badge ${stClass}">${(r.status_kehadiran || '').toUpperCase()}</span>
                    </div>
                    <div style="margin-top:8px; font-size:13px; opacity:0.8;">
                        <i class="fa-solid fa-clock"></i> In: ${r.check_in || '-'} | Out: ${r.check_out || '-'}
                        ${r.catatan ? `<div style="margin-top:4px; font-style:italic;">"${r.catatan}"</div>` : ''}
                    </div>
                `;
                list.appendChild(item);
            });
        })
        .catch(err => list.innerHTML = `<div style="text-align:center;color:#f87171;">Gagal memuat data.</div>`);
}
</script>
@endpush