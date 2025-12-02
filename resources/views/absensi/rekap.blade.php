@extends('layouts.app')

@section('title', 'Rekapitulasi Absensi')
@section('page_title', 'Rekap Kehadiran Bulanan')

@section('content')

{{-- NEW: Tombol Verifikasi CSV (Owner Priority) --}}
@php $lastImport = \App\Models\CsvImport::latest()->first(); @endphp
@if($lastImport && $lastImport->rows()->where('approved',false)->exists())
    <div style="margin-bottom:12px;">
        <a class="btn" href="{{ route('csv.import.process', $lastImport->id) }}">Verifikasi CSV (Import Terbaru)</a>
        <span class="small-muted">— Terdapat {{ $lastImport->rows()->where('approved',false)->count() }} baris belum di-approve</span>
    </div>
@endif

<style>
:root{
    --accent: #ff9800;
    --muted: #9ca3af;
    --card-bg: #fff;
    --surface: #fbfdff;
    --radius: 12px;
    --border: #e6e9ee;
}

/* Card */
.calendar-card{
    background:var(--card-bg);
    border-radius:14px;
    padding:20px;
    box-shadow:0 8px 30px rgba(15,23,42,0.04);
    color:#111827;
    margin-bottom:22px;
    position:relative;
}

/* Header / nav */
.calendar-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:16px;
}
.calendar-nav{display:flex;align-items:center;gap:10px;}
.nav-btn{
    background:transparent;
    border:1px solid var(--border);
    padding:8px 12px;
    border-radius:10px;
    cursor:pointer;
    font-weight:600;
    color:#374151;
}
.nav-btn:hover{background:#fff7ea;border-color:var(--accent);}

/* wrapper for horizontal scroll on tiny screens */
.calendar-grid-wrap{ overflow-x:auto; padding-bottom:6px; }

/* grid */
.calendar-grid{
    display:grid;
    grid-template-columns: repeat(7, minmax(92px, 1fr));
    gap:10px;
    align-items:start;
    min-width:700px;
}

/* day header */
.calendar-day-header{
    padding:10px 8px;
    background: transparent;
    text-align:center;
    font-weight:700;
    color:#6b7280;
    font-size:13px;
}

/* cell */
.calendar-cell{
    position: relative; /* ADDED: Untuk posisi badge */
    background:var(--surface);
    border-radius:10px;
    min-height:92px;
    padding:12px 8px;
    border:1px solid transparent;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    transition:all .12s;
    cursor:pointer;
    box-shadow: 0 1px 0 rgba(16,24,40,0.02) inset;
}
.calendar-cell:hover:not(.not-current-month){
    transform:translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    background: #fff8e6;
}

/* out of month */
.not-current-month{
    background:#fbfcfd;
    color:#c7cbd1 !important;
    cursor:default;
    opacity:0.95;
}

/* today / selected */
.today-cell{ outline:2px solid var(--accent); background:#fff9ed; }
.calendar-cell.selected{
    border:2px solid var(--accent);
    box-shadow: 0 0 10px rgba(255,120,0,0.25);
}

/* inner content */
.cell-date{ font-weight:700; font-size:15px; color:#1f2937; margin-bottom:8px; }
.cell-dots{ display:flex; gap:6px; align-items:center; justify-content:center; margin-bottom:6px; }
.dot-small{ width:8px; height:8px; border-radius:999px; display:inline-block; box-shadow:0 1px 0 rgba(0,0,0,0.06); }
.dot-green{ background:#22c55e; }
.dot-yellow{ background:#f59e0b; }
.dot-red{ background:#ef4444; }
.cell-info{ font-size:12px; color:var(--muted); font-weight:600; }

/* small legend */
.legend{ display:flex; justify-content:center; gap:24px; margin-top:18px; align-items:center; color:#475569; font-weight:600; font-size:13px; }

/* modal */
.modal-back{ display:none; position:fixed; inset:0; background:rgba(2,6,23,0.6); z-index:50; align-items:center; justify-content:center; padding:18px; }
.modal-detail-content{
    width:100%; max-width:720px; background:#fff; border-radius:14px; padding:20px;
    box-shadow:0 18px 50px rgba(2,6,23,0.35); max-height:90vh; overflow:auto; position:relative;
}
.close-btn{ position:absolute; right:16px; top:12px; cursor:pointer; font-size:22px; color:#94a3b8; }

/* detail items */
.summary-box{ display:flex; gap:12px; justify-content:space-between; padding:12px 0 18px; border-bottom:1px solid #f1f5f9; margin-bottom:12px; }
.detail-item-card{ background:#fff; border:1px solid #f1f5f9; padding:12px; border-radius:10px; margin-bottom:10px; }

/* responsive tweaks */
@media (max-width:1100px){ .calendar-grid{ min-width:640px; gap:8px; } }
@media (max-width:900px){ .calendar-grid{ min-width:540px; } .cell-date{ font-size:14px; } .dot-small{ width:7px; height:7px; } }
@media (max-width:640px){ .calendar-grid{ min-width:420px; grid-template-columns: repeat(7, minmax(60px,1fr)); gap:6px; } .calendar-card{ padding:12px; } .nav-btn{ padding:6px 10px; font-size:13px; } }
@media (max-width:420px){ .calendar-grid{ min-width:360px; grid-template-columns: repeat(7,minmax(52px,1fr)); gap:6px; } .modal-detail-content{ padding:14px; } .summary-box{ flex-direction:column; gap:8px; align-items:flex-start; } }

/* Kotak bulan aktif (bulan yang sedang dilihat) */
.calendar-cell.is-current {
    background: #ffffff !important;
    border: 1px solid #e5e7eb !important;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}

/* Kotak di luar bulan aktif — depth lebih gelap */
.calendar-cell.other-month {
    background: #f3f4f6 !important;
    color: #9ca3af !important;
    border: 1px solid #e5e7eb !important;
    opacity: 0.9;
}

/* Hover hanya untuk bulan aktif */
.calendar-cell.is-current:hover {
    background: #fff8e6 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
}

/* ===== NEW/UPDATED: POS PENDING STYLE ===== */
.pos-pending {
    background: #fff7cc !important;
    border: 2px solid #facc15 !important;
    cursor: pointer !important;
}
.pending-badge {
    position: absolute;
    top: 6px;
    right: 8px;
    background:#f59e0b;
    color:#fff;
    padding:2px 6px;
    border-radius:8px;
    font-size:11px;
    font-weight:700;
}
/* ================================== */


</style>

<div class="card" style="padding:0;">
    <div class="calendar-card">

        <div id="calendar-loading" style="display:none; position:absolute; inset:0; background:rgba(255,255,255,0.8); z-index:40; align-items:center; justify-content:center;">
            <i class="fa-solid fa-spinner fa-spin" style="font-size:30px; color:var(--accent)"></i>
        </div>

        <div class="calendar-header">
            <h3 style="margin:0;">Kalender Absensi</h3>

            <div class="calendar-nav">
                <button class="nav-btn" id="prevBtn"><i class="fa-solid fa-chevron-left"></i> Bulan Sebelumnya</button>
                <div id="currentMonthLabel" style="font-weight:800;color:var(--accent);min-width:160px;text-align:center;">Loading...</div>
                <button class="nav-btn" id="nextBtn">Bulan Selanjutnya <i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>

        <div class="calendar-grid-wrap">
            <div class="calendar-grid" id="calendarGrid">
                {{-- day headers (Sunday-first) --}}
                <div class="calendar-day-header">Min</div>
                <div class="calendar-day-header">Sen</div>
                <div class="calendar-day-header">Sel</div>
                <div class="calendar-day-header">Rab</div>
                <div class="calendar-day-header">Kam</div>
                <div class="calendar-day-header">Jum</div>
                <div class="calendar-day-header">Sab</div>
                {{-- cells will be injected --}}
            </div>
        </div>

        <div class="legend">
            <div><span class="dot-small dot-green" style="display:inline-block;margin-right:8px;"></span> Hadir</div>
            <div><span class="dot-small dot-yellow" style="display:inline-block;margin-right:8px;"></span> Terlambat</div>
            <div><span class="dot-small dot-red" style="display:inline-block;margin-right:8px;"></span> Absen</div>
        </div>

    </div>
</div>

{{-- MODAL --}}
<div class="modal-back" id="detailModal">
    <div class="modal-detail-content">
        <span class="close-btn" onclick="closeModal('detailModal')">&times;</span>
        <h3 id="detail-title" style="margin:0 0 8px 0;font-weight:800;">Detail Absensi Harian</h3>

        <div class="summary-box">
            <div style="color:#16a34a;"><strong id="present-count">0</strong><div class="small-muted">Hadir/Pengganti</div></div>
            <div style="color:#d97706;"><strong id="late-count">0</strong><div class="small-muted">Terlambat</div></div>
            <div style="color:#dc2626;"><strong id="alpha-count">0</strong><div class="small-muted">Alpha</div></div>
        </div>

        <div id="detail-list" style="max-height:420px; overflow:auto;">
            <div style="text-align:center;padding:20px;color:#6b7280;">Pilih tanggal di kalender.</div>
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

document.addEventListener('DOMContentLoaded', () => {
    fetchCalendar(currentMonth, currentYear);
});

function changeMonth(direction){
    currentMonth += direction;
    if (currentMonth > 12){ currentMonth = 1; currentYear++; }
    if (currentMonth < 1) { currentMonth = 12; currentYear--; }
    fetchCalendar(currentMonth, currentYear);
}

function fetchCalendar(month, year){
    loader.style.display = 'flex';

    fetch(`/api/absensi/rekap/calendar?month=${month}&year=${year}`)
        .then(r => {
            if (!r.ok) throw new Error('Server error: ' + r.status);
            return r.json();
        })
        .then(data => {
            renderCalendar(data);
            loader.style.display = 'none';
        })
        .catch(err => {
            console.error(err);
            currentMonthLabel.innerText = '❌ Error';
            loader.style.display = 'none';
            alert('Gagal memuat kalender: ' + err.message);
        });
}

function renderCalendar(data){
    currentMonthLabel.innerText = data.currentMonthName;

    // simpan header
    const headers = Array.from(calendarGrid.children).slice(0, 7);
    calendarGrid.innerHTML = "";
    headers.forEach(h => calendarGrid.appendChild(h));

    const days = data.calendar || [];

    // render semua hari yang dikirim dari backend
    days.forEach((day, idx) => {
        const cell = document.createElement("div");

        // class dasar
        let classes = "calendar-cell";

        // BEDAIN bulan aktif vs bulan lain
        if (day.isCurrentMonth) {
            classes += " is-current";
        } else {
            classes += " other-month";
        }

        // highlight hari ini
        if (day.isToday) classes += " today-cell";

        cell.className = classes;

        // animasi
        cell.classList.add("fade-in-up");
        cell.style.animationDelay = (idx * 0.012) + "s";
        
        // dots
        let dotsHtml = '<div class="cell-dots">';
        (day.dots || []).forEach(d => {
            if (d) dotsHtml += `<span class="dot-small dot-${d}"></span>`;
        });
        dotsHtml += "</div>";

        // isi cell (Badge HTML akan ditambahkan secara programmatic di bawah)
        cell.innerHTML = `
            <div class="cell-date">${day.label}</div>
            ${dotsHtml}
            <div class="cell-info">${day.summary || '0 data'}</div>
        `;


        // HANYA bulan aktif bisa diklik (DEFAULT CLICK)
        if (day.isCurrentMonth) {
            cell.dataset.date = day.date;
            cell.onclick = () => {
                document.querySelectorAll(".calendar-cell")
                    .forEach(c => c.classList.remove("selected"));
                cell.classList.add("selected");
                openDayDetail(cell);
            };
        }

        // ===== NEW: POS PENDING LOGIC (Integrated logic from user) =====
        if (data.pos_pending && data.pos_pending.includes(day.date)) {
            cell.classList.add("pos-pending");
            const badge = document.createElement('div');
            badge.className = 'pending-badge';
            badge.innerText = 'NEW';
            cell.appendChild(badge);

            // klik langsung ke halaman verify
            cell.onclick = () => {
                window.location.href = `/absensi/rekap/verify/${day.date}`;
            };
        }
        // ===================================

        calendarGrid.appendChild(cell);
    });

    // pastikan baris terakhir penuh
    const totalCells = calendarGrid.children.length - 7;
    const remainder = totalCells % 7;

    if (remainder !== 0) {
        const extra = 7 - remainder;
        for (let i = 0; i < extra; i++) {
            calendarGrid.appendChild(buildEmptyCell());
        }
    }
}

function buildEmptyCell(){
    const empty = document.createElement('div');
    empty.className = 'calendar-cell other-month';
    empty.innerHTML = `
        <div class="cell-date"></div>
        <div class="cell-info"></div>
    `;
    return empty;
}


/* --- detail modal fetch --- */
function openDayDetail(el){
    const date = el.dataset.date;
    if (!date) return;

    const list = document.getElementById('detail-list');
    const present = document.getElementById('present-count');
    const late = document.getElementById('late-count');
    const alpha = document.getElementById('alpha-count');

    present.innerText = late.innerText = alpha.innerText = '0';
    list.innerHTML = `<div style="text-align:center;padding:20px;color:#6b7280;"><i class="fa-solid fa-spinner fa-spin"></i> Memuat detail...</div>`;

    openModal('detailModal');

    fetch(`/api/absensi/rekap/detail?date=${date}`)
        .then(r => {
            if (!r.ok) throw new Error('Server error: ' + r.status);
            return r.json();
        })
        .then(data => {
            document.getElementById('detail-title').innerText = 'Detail Absensi: ' + (data.date_formatted || date);

            const summary = data.summary || {};
            present.innerText = (summary.hadir || 0) + (summary.pengganti || 0);
            late.innerText = summary.terlambat || 0;
            alpha.innerText = summary.alpha || 0;

            list.innerHTML = '';

            const rows = data.rows || [];
            if (!rows.length) {
                list.innerHTML = `<div style="text-align:center;padding:20px;color:#6b7280;">Tidak ada absensi pada tanggal ini.</div>`;
                return;
            }

            rows.forEach(r => {
                const item = document.createElement('div');
                item.className = 'detail-item-card';
                const status = (r.status_kehadiran || r.status || 'N/A');
                let statusClass = 'status-hadir-modal';
                if (status === 'terlambat') statusClass = 'status-terlambat-modal';
                if (status === 'alpha') statusClass = 'status-alpha-modal';
                if (status === 'pengganti') statusClass = 'status-pengganti-modal';

                item.innerHTML = `
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div style="font-weight:700;">${r.nama} <span class="small-muted">· ${r.posisi || ''}</span></div>
                        <div><span class="status-badge ${statusClass}">${(status || '').toUpperCase()}</span></div>
                    </div>
                    <div class="small-muted" style="margin-top:8px;">
                        Check-in: ${r.check_in || '-'} | Check-out: ${r.check_out || '-'}
                        ${r.catatan ? `<br>Catatan: ${r.catatan}` : ''}
                    </div>
                `;
                list.appendChild(item);
            });

        })
        .catch(err => {
            console.error(err);
            list.innerHTML = `<div style="text-align:center;padding:20px;color:#ef4444;font-weight:700;">Gagal: ${err.message}</div>`;
        });
}

</script>
@endpush