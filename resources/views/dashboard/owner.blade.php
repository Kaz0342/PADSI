@extends('layouts.app')
@section('title', 'Dashboard Owner')

@section('content')

<style>
:root{
    --accent:#f59e0b;
    --card:#ffffff;
    --surface:#fbfdff;
    --border:#e5e7eb;
}

.dashboard-grid{
    display:grid;
    grid-template-columns: repeat(4,1fr);
    gap:18px;
    margin-bottom:28px;
}
.stat-card{
    background:var(--card);
    padding:18px;
    border-radius:12px;
    box-shadow:0 4px 14px rgba(0,0,0,.05);
}
.stat-title{
    font-size:14px;color:#4b5563;font-weight:600;margin-bottom:8px;
}
.stat-value{
    font-size:32px;font-weight:800;display:flex;align-items:center;gap:8px;margin-top:2px;
}

/* calendar wrapper */
.absensi-wrapper{ width:100%; margin-top: 10px; }

.absensi-wrapper .calendar-card{
    background:var(--card);
    padding:20px;
    border-radius:14px;
    box-shadow:0 6px 22px rgba(0,0,0,.05);
    position:relative;
}

.absensi-wrapper .calendar-loading{
    display:none; position:absolute; inset:0;
    background:rgba(255,255,255,.75);
    z-index:99; border-radius:12px;
    justify-content:center; align-items:center;
}

.absensi-wrapper .calendar-header{
    display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;
}
.absensi-wrapper .calendar-nav{display:flex;gap:10px;align-items:center;}

.absensi-wrapper .nav-btn{
    padding:8px 12px;border-radius:10px;border:none;
    background:#f3f4f6;font-weight:600;color:#374151;
    cursor:pointer;box-shadow:inset 0 -1px 0 rgba(0,0,0,.04);
}
.absensi-wrapper .nav-btn:hover{ transform:translateY(-1px); }

.absensi-wrapper .calendar-grid{
    display:grid;
    grid-template-columns: repeat(7,minmax(92px,1fr));
    gap:10px; min-width:700px;
}

.absensi-wrapper .calendar-day-header{
    background:#f8fafc;padding:10px 8px;text-align:center;
    border-radius:8px;font-weight:700;color:#334155;font-size:14px;
}

.absensi-wrapper .calendar-cell{
    background:#ffffff; border:1px solid var(--border);
    border-radius:10px; padding:12px; min-height:92px;
    display:flex; flex-direction:column;
    justify-content:center; align-items:center;
    box-shadow:0 2px 6px rgba(0,0,0,.03);
    cursor:pointer; transition:.2s all;
}
.absensi-wrapper .calendar-cell:hover:not(.not-current-month){
    transform:translateY(-4px);
    background:#fff8e6;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

.absensi-wrapper .cell-date{font-size:16px;font-weight:700;color:#0f172a;margin-bottom:5px;}
.absensi-wrapper .cell-info{font-size:12px;color:#94a3b8;margin-top:6px;font-weight:600;}
.absensi-wrapper .cell-dots{display:flex;gap:5px;margin:4px 0;}

.dot-small{width:8px;height:8px;border-radius:50%;}
.dot-green{background:#22c55e;}
.dot-yellow{background:#f59e0b;}
.dot-red{background:#ef4444;}

.absensi-wrapper .not-current-month{
    background:#f3f4f6 !important;
    border-color:#e5e7eb !important;
    color:#9ca3af !important;
    pointer-events:none;
}

.absensi-wrapper .today-cell{
    border:2px solid var(--accent);
    background:#fff9e6 !important;
}

.absensi-wrapper .calendar-cell.selected{
    outline:2px solid rgba(245,158,11,.25);
    box-shadow:0 6px 18px rgba(245,158,11,.25);
}

.fade-in-up{opacity:0;transform:translateY(10px);animation:fade-in .35s forwards;}
@keyframes fade-in{to{opacity:1;transform:translateY(0);}}

.absensi-wrapper .modal-back{
    display:none;position:fixed;inset:0;
    background:rgba(0,0,0,.6);
    justify-content:center;align-items:center;
    z-index:2000;padding:20px;
}
.absensi-wrapper .modal-detail-content{
    background:#fff;padding:20px;border-radius:12px;
    max-width:720px;width:100%;max-height:90vh;overflow:auto;
    box-shadow:0 14px 40px rgba(0,0,0,.2);
}
.detail-item-card{
    border:1px solid #e5e7eb;border-radius:10px;
    padding:12px;margin-bottom:10px;
}
.status-badge{
    padding:6px 10px;border-radius:6px;font-size:12px;font-weight:700;
}
.status-hadir-modal{background:#ecfdf5;color:#065f46;}
.status-terlambat-modal{background:#fff7ed;color:#92400e;}
.status-alpha-modal{background:#ffeded;color:#7f1d1d;}
.status-pengganti-modal{background:#eef2ff;color:#3730a3;}

/* visual depth: current month vs others */
.calendar-cell.is-current {
    background: #ffffff !important;
    border: 1px solid #e5e7eb !important;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.calendar-cell.other-month {
    background: #f3f4f6 !important;
    color: #9ca3af !important;
    border: 1px solid #e5e7eb !important;
    opacity: 0.9;
}
.calendar-cell.is-current:hover {
    background: #fff8e6 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
}

/* responsive tweaks */
@media (max-width:1100px){ .absensi-wrapper .calendar-grid{ min-width:640px; gap:8px; } }
@media (max-width:780px){ .dashboard-grid { grid-template-columns: repeat(2,1fr); } }
@media (max-width:480px){ .dashboard-grid { grid-template-columns: 1fr; } .absensi-wrapper .calendar-grid { min-width: 100%; grid-template-columns: repeat(7, 1fr); } }

</style>

{{-- WELCOME --}}
<div style="background:var(--accent);color:white;padding:20px;border-radius:12px;margin-bottom:20px;">
    <h2 style="margin:0;">Selamat Datang, {{ $user->name ?? 'Owner' }}!</h2>
    <p style="margin:6px 0 0;font-size:13px;opacity:.9;">Anda memiliki akses penuh ke sistem.</p>
</div>

{{-- STATS --}}
<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-title">Karyawan Aktif</div>
        <div class="stat-value" style="color:#10b981;">{{ $karyawanAktif ?? 0 }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Karyawan Cuti</div>
        <div class="stat-value" style="color:#f59e0b;">{{ $karyawanCuti ?? 0 }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Hadir Hari Ini</div>
        <div class="stat-value" style="color:#3b82f6;">{{ $hadirHariIni ?? 0 }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Stok Rendah</div>
        <div class="stat-value" style="color:#ef4444;">{{ $stokRendah ?? 0 }}</div>
    </div>
</div>

{{-- KALENDAR --}}
<div class="absensi-wrapper">
    <div class="calendar-card">
        <div id="calendar-loading" class="calendar-loading">
            <i class="fa-solid fa-spinner fa-spin" style="font-size:26px; color:var(--accent)"></i>
        </div>

        <div class="calendar-header">
            <h3 style="margin:0;">Kalender Absensi</h3>

            <div class="calendar-nav">
                <button id="prevBtn" class="nav-btn"><i class="fa-solid fa-chevron-left"></i> Bulan Sebelumnya</button>
                <div id="currentMonthLabel" style="font-weight:800;color:var(--accent);min-width:160px;text-align:center;">Loading...</div>
                <button id="nextBtn" class="nav-btn">Bulan Selanjutnya <i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>

        <div class="calendar-grid" id="calendarGrid" aria-live="polite">
            <div class="calendar-day-header">Min</div>
            <div class="calendar-day-header">Sen</div>
            <div class="calendar-day-header">Sel</div>
            <div class="calendar-day-header">Rab</div>
            <div class="calendar-day-header">Kam</div>
            <div class="calendar-day-header">Jum</div>
            <div class="calendar-day-header">Sab</div>
        </div>

        <div style="text-align:center;margin-top:18px;font-size:13px;color:#374151;">
            <span style="margin-right:12px;"><span class="dot-small dot-green"></span> Hadir</span>
            <span style="margin-right:12px;"><span class="dot-small dot-yellow"></span> Terlambat</span>
            <span><span class="dot-small dot-red"></span> Absen</span>
        </div>
    </div>
</div>

{{-- MODAL --}}
<div class="absensi-wrapper">
<div id="detailModal" class="modal-back">
    <div class="modal-detail-content">
        <button style="border:none;background:none;font-size:22px;float:right;cursor:pointer;" onclick="closeModal('detailModal')">&times;</button>

        <h3 id="detail-title" style="margin-top:0;">Detail Absensi</h3>

        <div style="display:flex;gap:14px;margin:12px 0 18px;">
            <div style="flex:1;text-align:center;border:1px solid #e5e7eb;padding:10px;border-radius:10px;">
                <div id="present-count" style="font-size:22px;font-weight:800;">0</div>
                <div style="color:#64748b;font-weight:700;margin-top:4px;">Hadir/Pengganti</div>
            </div>
            <div style="flex:1;text-align:center;border:1px solid #e5e7eb;padding:10px;border-radius:10px;">
                <div id="late-count" style="font-size:22px;font-weight:800;">0</div>
                <div style="color:#64748b;font-weight:700;margin-top:4px;">Terlambat</div>
            </div>
            <div style="flex:1;text-align:center;border:1px solid #e5e7eb;padding:10px;border-radius:10px;">
                <div id="alpha-count" style="font-size:22px;font-weight:800;">0</div>
                <div style="color:#64748b;font-weight:700;margin-top:4px;">Alpha</div>
            </div>
        </div>

        <div id="detail-list">
            <div style="text-align:center;padding:20px;color:#6b7280;">Pilih tanggal untuk melihat detail</div>
        </div>
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

function initDashboardCalendar() {
    fetchCalendar(currentMonth, currentYear);
}

document.addEventListener('DOMContentLoaded', initDashboardCalendar);
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
        initDashboardCalendar();
    }
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
    currentMonthLabel.innerText = data.currentMonthName || `${data.currentMonth}-${data.currentYear}`;

    // keep headers
    const headers = Array.from(calendarGrid.children).slice(0, 7);
    calendarGrid.innerHTML = "";
    headers.forEach(h => calendarGrid.appendChild(h));

    const days = data.calendar || [];

    // render each day provided by backend (includes prev/next month fillers)
    days.forEach((day, idx) => {
        const cell = document.createElement("div");

        // base classes + depth markers
        let classes = "calendar-cell";
        if (day.isCurrentMonth) classes += " is-current";
        else classes += " other-month";

        if (day.isToday) classes += " today-cell";

        cell.className = classes;
        cell.classList.add('fade-in-up');
        cell.style.animationDelay = (idx * 0.012) + "s";

        // clickable only current month
        if (day.isCurrentMonth) {
            cell.dataset.date = day.date;
            cell.onclick = () => {
                document.querySelectorAll(".calendar-cell").forEach(c => c.classList.remove("selected"));
                cell.classList.add("selected");
                openDayDetail(cell);
            };
        }

        // dots
        let dotsHtml = '<div class="cell-dots">';
        (day.dots || []).forEach(d => { if (d) dotsHtml += `<span class="dot-small dot-${d}"></span>`; });
        dotsHtml += '</div>';

        cell.innerHTML = `
            <div class="cell-date">${day.label}</div>
            ${dotsHtml}
            <div class="cell-info">${day.summary || '0 data'}</div>
        `;

        calendarGrid.appendChild(cell);
    });

    // ensure final row complete
    const totalCells = calendarGrid.children.length - 7; // exclude headers
    const remainder = totalCells % 7;
    if (remainder !== 0) {
        const extra = 7 - remainder;
        for (let i=0;i<extra;i++){
            calendarGrid.appendChild(buildEmptyCell());
        }
    }
}

function buildEmptyCell(){
    const div = document.createElement("div");
    div.className = "calendar-cell other-month";
    div.innerHTML = `<div class="cell-date"></div><div class="cell-info"></div>`;
    return div;
}

function openDayDetail(el){
  const date = el.dataset.date;
  if (!date) return;

  openModal('detailModal');
  const list = document.getElementById('detail-list');
  const present = document.getElementById('present-count');
  const late = document.getElementById('late-count');
  const alpha = document.getElementById('alpha-count');

  present.innerText = late.innerText = alpha.innerText = '0';
  list.innerHTML = `<div style="text-align:center;padding:20px;color:#6b7280;"><i class="fa-solid fa-spinner fa-spin"></i> Memuat detail...</div>`;

  fetch(`/api/absensi/rekap/detail?date=${encodeURIComponent(date)}`)
    .then(r => {
      if (!r.ok) throw new Error('Server error: ' + r.status);
      return r.json();
    })
    .then(data => {
      document.getElementById('detail-title').innerText = data.date_formatted || date;

      const summary = data.summary || {};
      present.innerText = (summary.hadir||0) + (summary.pengganti||0);
      late.innerText = summary.terlambat||0;
      alpha.innerText = summary.alpha||0;

      list.innerHTML = "";
      const rows = data.rows || [];
      if (!rows.length) {
          list.innerHTML = `<div style="text-align:center;padding:20px;color:#6b7280;">Tidak ada catatan absensi.</div>`;
          return;
      }

      rows.forEach(r => {
          const item = document.createElement('div');
          item.className = 'detail-item-card';
          const st = (r.status_kehadiran || r.status || '').toLowerCase();
          const badge =
              st==="hadir" ? "status-hadir-modal" :
              st==="terlambat" ? "status-terlambat-modal" :
              st==="alpha" ? "status-alpha-modal" : "status-pengganti-modal";

          item.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <div style="font-weight:700;">${r.nama ?? '-'} <span style="color:#6b7280;">· ${r.posisi ?? '-'}</span></div>
              <div class="status-badge ${badge}">${(st || 'N/A').toUpperCase()}</div>
            </div>
            <div style="margin-top:6px;color:#6b7280;font-size:13px;">
              Check-in: ${r.check_in ?? '-'} | Check-out: ${r.check_out ?? '-'}
              ${ r.catatan ? `<div style="margin-top:6px;">Catatan: ${r.catatan}</div>` : "" }
              ${ r.pengganti ? `<div style="margin-top:6px;color:#0f62fe;font-weight:700;">(Menggantikan: ${r.pengganti})</div>` : "" }
            </div>
          `;
          list.appendChild(item);
      });
    })
    .catch(err => {
      list.innerHTML = `<div style="text-align:center;padding:20px;color:#ef4444;font-weight:700;">Gagal: ${err.message}</div>`;
      console.error('openDayDetail error', err);
    });
}
</script>
@endpush
