@extends('layouts.app')

@section('title', 'Dashboard Pegawai')

@section('content')

<style>
/* ====== STYLING KHUSUS DASHBOARD PEGAWAI (FINAL) ====== */
:root {
--accent: #f7a20a;
--card: #ffffff;
}

.welcome-card {
background: var(--accent);
color: white;
border-radius: 14px;
padding: 28px;
margin-bottom: 25px;
box-shadow: 0 6px 18px rgba(0,0,0,0.06);
}

.jabatan-text {
font-size: 15px;
opacity: .9;
}

.row-flex {
display: flex;
gap: 20px;
flex-wrap: wrap;
}

.card-box {
background: #fff;
padding: 20px;
border-radius: 14px;
box-shadow: 0 4px 15px rgba(0,0,0,0.05);
flex: 1;
min-width: 350px;
}

/* BADGE FIX FINAL */
.status-badge {
display: inline-block;
padding: 6px 12px;
border-radius: 8px;
font-weight: 700;
text-transform: uppercase;
font-size: 13px;
margin-top: 10px;
}

.status-hadir, .status-pengganti {
background: #d6f8df;
color: #0a7a28;
}

.status-terlambat {
background:#fff2c6;
color:#9a6a00;
}

.status-alpha {
background:#ffe0e0;
color:#b01a1a;
}

.status-belum {
background:#e5e5e5;
color:#4b4b4b;
}

/* History sesi box */
.sesi-box {
background: var(--card);
padding: 20px;
border-radius: 14px;
box-shadow: 0 4px 10px rgba(0,0,0,0.06);
}

.sesi-item {
padding: 10px 0;
border-bottom: 1px solid #eee;
font-size: 14px;
}

.sesi-item:last-child {
border-bottom: none;
}

.session-time {
font-size: 15px;
font-weight: 700;
}

.session-active {
color: #e63946; /* Merah untuk Active */
font-weight: 700;
}

.session-ok {
color: #10b981; /* Hijau untuk Check-in/Check-out sukses */
font-weight: 700;
}

/* Alert/Warning Box */
.warning-box {
background: #fff3cd;
color: #856404;
padding: 12px;
border-radius: 10px;
margin-bottom: 20px;
font-size: 14px;
display: flex;
align-items: center;
gap: 8px;
}

.btn-checkout {
background: #e74c3c !important;
color: #fff !important;
}

/* Modal Styling */
.modal-backdrop {
position: fixed;
top: 0;
left: 0;
width: 100%;
height: 100%;
background: rgba(0, 0, 0, 0.5);
display: flex;
justify-content: center;
align-items: center;
z-index: 1000;
}
.modal {
background: white;
padding: 30px;
border-radius: 12px;
width: 90%;
max-width: 450px;
box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
}
.float-right {
text-align: right;
}
</style>

{{-- Status Message from Controller --}}
@if (session('error'))

<div class="card" style="background:#ffe0e0; color:#b01a1a; padding:15px; margin-bottom:20px; border-left:5px solid #d32f2f;">
<i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
</div>
@endif

{{-- ==== HEADER WELCOME ==== --}}

<div class="welcome-card">
<h2 style="margin:0; font-weight:700;">Halo, {{ $pegawai->nama }}!</h2>
<p class="jabatan-text">Selamat bertugas hari ini! Anda bertugas sebagai <b>{{ ucfirst($pegawai->jabatan) }}</b>.</p>
</div>

{{-- ==== TANPA JADWAL (TAPI BELUM CHECK-IN) ==== --}}
@if ($status == 'Belum Check-in' && !$jadwal)

<div class="warning-box">
<i class="fa-solid fa-circle-exclamation"></i>
Anda tidak terjadwal hari ini. Silakan Check-in Pengganti.
</div>
@endif

{{-- ==== MAIN CONTENT 2 COLUMN ==== --}}

<div class="row-flex">

{{-- ===================== LEFT BOX: STATUS & ACTION ===================== --}}
<div class="card-box">
    <h3>Status Kehadiran Hari Ini</h3>

    {{-- Badge Status --}}
    @php
        $statusLower = strtolower($status);
        $statusClass = match($statusLower) {
            'hadir' => 'status-hadir',
            'pengganti' => 'status-pengganti',
            'terlambat' => 'status-terlambat',
            'alpha' => 'status-alpha',
            'belum check-in' => 'status-belum',
            default => 'status-belum'
        };
    @endphp

    @if ($status == 'Belum Check-in')
        <span class="status-badge {{ $statusClass }}">BELUM CHECK-IN</span>
    @else
        <span class="status-badge {{ $statusClass }}">{{ strtoupper($status) }}</span>
    @endif

    <div style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap;">
        @if ($status == 'Belum Check-in' || $statusLower == 'alpha')
            {{-- Tombol Check-in Normal / Late --}}
            <button class="btn" onclick="submitCheckIn()" style="margin-bottom:10px;">
                <i class="fa-solid fa-right-to-bracket"></i> Check-in Sekarang
            </button>

            {{-- Check-in Pengganti --}}
            <a href="{{ route('absensi.pengganti.form') }}" class="btn secondary">
                <i class="fa-solid fa-user-plus"></i> Check-in Pengganti
            </a>

        @else
            {{-- TOMBOL CHECK-OUT --}}
            @if ($hadirSesi->whereNull('check_out_at')->count() > 0)
            <button class="btn btn-checkout" onclick="openModal('checkoutModal')">
                <i class="fa-solid fa-right-from-bracket"></i> Check-out Sesi Aktif
            </button>
            @else
            <span class="status-badge {{ $statusClass }}" style="background:#eee; color:#777;">Sesi Hari Ini Selesai</span>
            @endif
        @endif
    </div>

    {{-- Form tersembunyi untuk Check-in (diisi GPS oleh JS) --}}
    <form id="checkin-form" action="{{ route('absensi.checkin') }}" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="lat" id="lat">
        <input type="hidden" name="lng" id="lng">
        <input type="hidden" name="wifi_connected" id="wifi_connected" value="0">
        <input type="hidden" name="wifi_name" id="wifi_name" value="">
    </form>

</div>


{{-- ===================== RIGHT BOX: SESI HARI INI ===================== --}}
<div class="card-box">
    <h3>Sesi Hari Ini ({{ $hadirSesi->count() }})</h3>

    @if ($hadirSesi->isEmpty())
        <p style="color:#777;">Belum ada sesi masuk hari ini.</p>
    @else
        @foreach ($hadirSesi as $s)
            <div style="padding:10px 0; border-bottom:1px solid #eee;">
                <span class="session-time session-ok">{{ \Carbon\Carbon::parse($s->check_in_at)->format('H:i') }}</span>
                s.d.
                @if ($s->check_out_at)
                    <span class="session-time session-ok">{{ \Carbon\Carbon::parse($s->check_out_at)->format('H:i') }}</span>
                @else
                    <span class="session-time session-active">Sedang Aktif...</span>
                @endif

                @if ($s->catatan)
                <p style="font-size:13px; margin-top:5px; color:#777;">Catatan: {{ $s->catatan }}</p>
                @endif
            </div>
        @endforeach
    @endif
</div>


</div>

{{-- ===================== MODAL CHECK-OUT ===================== --}}

<div id="checkoutModal" class="modal-backdrop" style="display:none;" onclick="closeModal('checkoutModal')">
<div class="modal" onclick="event.stopPropagation()">
<h3 style="margin-top:0;">Konfirmasi Check-out</h3>

    {{-- WARNING EARLY CHECKOUT (Hanya ditampilkan jika Early Checkout terjadi) --}}
    @if(isset($jadwal) && $jadwal && $jadwal->shift)
        @php
            $endToday = \Carbon\Carbon::parse($jadwal->shift->end_time, 'Asia/Jakarta');
            $endToday = \Carbon\Carbon::today('Asia/Jakarta')->setTime($endToday->hour, $endToday->minute);
        @endphp
        
        @if(\Carbon\Carbon::now('Asia/Jakarta')->lessThan($endToday))
            <div class="warning-box" style="background:#ffe0e0; color:#b01a1a;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Anda Check-out sebelum jadwal selesai ({{ $endToday->format('H:i') }}).
            </div>
        @endif
    @endif


    <form action="{{ route('absensi.checkout') }}" method="POST">
        @csrf

        {{-- Lo ganti logic conditional field required di modal dengan field tunggal. Backend lo yang tanggung jawab buat validasi. --}}
        <label style="display:block; margin-bottom:5px;">Catatan Check-out (Wajib jika lebih awal)</label>
        <textarea name="alasan" rows="3" placeholder="Masukkan catatan" style="width:100%; padding:10px;border-radius:8px;border:1px solid #ccc;"></textarea>

        <div class="float-right" style="margin-top:15px;">
            <button type="button" class="btn secondary" onclick="closeModal('checkoutModal')">Batal</button>
            <button type="submit" class="btn">Konfirmasi Check-out</button>
        </div>
    </form>
</div>


</div>

@endsection

@push('scripts')

<script>
// Modal helper
function openModal(id) {
document.getElementById(id).style.display = 'flex';
}

function closeModal(id) {
document.getElementById(id).style.display = 'none';
}

// Global variables for geo/wifi check (Simplified for this file, usually loaded globally)
const WIFI_TIMEOUT_MS = 1500;
const GEO_TIMEOUT_MS = 3000;

// Fetch Location data (GPS)
async function getGeoPosition(timeout){
return new Promise((resolve) => {
if (!navigator.geolocation) return resolve(null);
let resolved = false;
const timer = setTimeout(()=>{ if(!resolved){resolved = true; resolve(null);} }, timeout);
navigator.geolocation.getCurrentPosition(pos => {
if (resolved) return;
resolved = true; clearTimeout(timer);
resolve({lat: pos.coords.latitude, lng: pos.coords.longitude}); // FIX: lng must use longitude
}, err=>{
if (resolved) return;
resolved = true; clearTimeout(timer);
resolve(null);
}, {enableHighAccuracy:true, maximumAge:10000, timeout:timeout});
});
}

// Fungsi utama untuk submit form Check-in
async function submitCheckIn() {
const checkinForm = document.getElementById('checkin-form');

// Tunjukkan loading atau disable tombol saat proses geo
console.log(&quot;Mendapatkan data lokasi...&quot;);

// Dapatkan data GPS
const geo = await getGeoPosition(GEO_TIMEOUT_MS);

if (geo) {
    document.getElementById(&#39;lat&#39;).value = geo.lat;
    document.getElementById(&#39;lng&#39;).value = geo.lng;
} else {
    // Optional: Warning kalau GPS gagal, tapi biarkan backend yang validasi
    console.warn(&quot;Gagal mendapatkan lokasi GPS.&quot;);
}

// Submit form check-in
checkinForm.submit();


}

</script>

@endpush