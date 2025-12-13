@extends('layouts.app')

@section('title', 'Dashboard Pegawai')

@section('content')

<style>
/* =========================================
   🎨 PEGAWAI DASHBOARD SPECIFIC STYLES
   ========================================= */
:root {
    --text-mid: #9ca3af;
}

/* 1. WELCOME BANNER (GLASS) */
.welcome-card {
    background: linear-gradient(135deg, rgba(247, 162, 10, 0.15), rgba(247, 162, 10, 0.05));
    backdrop-filter: blur(12px);
    border: 1px solid rgba(247, 162, 10, 0.3);
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    color: white;
    position: relative;
    overflow: hidden;
}
.welcome-card::after {
    content: ""; position: absolute; top: -50px; right: -50px;
    width: 150px; height: 150px; background: var(--accent);
    filter: blur(80px); opacity: 0.2; z-index: -1;
}

.jabatan-text { font-size: 15px; opacity: 0.8; margin-top: 6px; }

/* 2. LAYOUT GRID */
.row-flex { display: flex; gap: 24px; flex-wrap: wrap; }
.card-box {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    padding: 24px; border-radius: 16px;
    box-shadow: var(--glass-shadow);
    flex: 1; min-width: 320px;
    color: white;
    display: flex; flex-direction: column;
}

/* 3. HEADINGS */
h3 { margin: 0 0 20px 0; font-size: 18px; font-weight: 700; color: white; display: flex; align-items: center; gap: 10px; }
h3 i { color: var(--accent); }

/* 4. STATUS BADGE (NEON STYLE) */
.status-badge {
    display: inline-block; padding: 8px 16px; border-radius: 10px;
    font-weight: 800; text-transform: uppercase; font-size: 14px;
    margin-top: 5px; letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    border: 1px solid transparent;
}

.status-hadir, .status-pengganti, .status-siap {
    background: rgba(74, 222, 128, 0.15); color: #4ade80; border-color: #4ade80;
    box-shadow: 0 0 20px rgba(74, 222, 128, 0.2);
}
.status-terlambat {
    background: rgba(250, 204, 21, 0.15); color: #facc15; border-color: #facc15;
    box-shadow: 0 0 20px rgba(250, 204, 21, 0.2);
}
.status-alpha, .status-tidak-terjadwal {
    background: rgba(248, 113, 113, 0.15); color: #f87171; border-color: #f87171;
    box-shadow: 0 0 20px rgba(248, 113, 113, 0.2);
}
.status-belum, .status-diluar-jam {
    background: rgba(255, 255, 255, 0.1); color: #d1d5db; border-color: rgba(255, 255, 255, 0.2);
}

/* 5. HISTORY SESI LIST */
.sesi-item {
    padding: 14px 0; border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex; flex-direction: column; gap: 4px;
}
.sesi-item:last-child { border-bottom: none; }

.session-time { font-size: 16px; font-weight: 700; font-family: monospace; }
.session-ok { color: #4ade80; text-shadow: 0 0 10px rgba(74, 222, 128, 0.4); }
.session-active { color: #f87171; animation: pulse 2s infinite; }

@keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.6; } 100% { opacity: 1; } }

/* 6. BUTTONS */
.btn-checkin {
    background: linear-gradient(135deg, var(--accent), #d97706);
    color: white; border: none; padding: 12px 24px; border-radius: 10px;
    font-weight: 700; cursor: pointer; width: 100%; margin-bottom: 10px;
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4); transition: 0.2s;
    display: flex; justify-content: center; align-items: center; gap: 8px;
}
.btn-checkin:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(245, 158, 11, 0.6); }

.btn-secondary-glass {
    background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
    color: white; padding: 12px 24px; border-radius: 10px;
    font-weight: 600; cursor: pointer; width: 100%; text-align: center;
    text-decoration: none; display: inline-block; transition: 0.2s;
}
.btn-secondary-glass:hover { background: rgba(255,255,255,0.2); }
.btn-secondary-glass.warning { border-color: #eab308; color: #fef08a; background: rgba(234, 179, 8, 0.1); }

.btn-checkout-danger {
    background: linear-gradient(135deg, #ef4444, #b91c1c);
    color: white; border: none; padding: 12px 24px; border-radius: 10px;
    font-weight: 700; cursor: pointer; width: 100%;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
}
.btn-checkout-danger:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(239, 68, 68, 0.6); }

/* 7. ALERT BOX */
.warning-box {
    background: rgba(250, 204, 21, 0.15); border: 1px solid rgba(250, 204, 21, 0.3);
    color: #facc15; padding: 14px; border-radius: 10px; margin-bottom: 20px;
    font-size: 14px; display: flex; align-items: center; gap: 10px;
    backdrop-filter: blur(5px);
}

/* 8. MODAL GLASS */
.modal-backdrop {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.8); backdrop-filter: blur(8px);
    z-index: 1000; align-items: center; justify-content: center; padding: 20px;
}
.modal-glass {
    background: rgba(30, 30, 30, 0.9); border: 1px solid var(--glass-border);
    padding: 30px; border-radius: 16px; width: 100%; max-width: 450px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.5); color: white;
    transform: scale(0.95); animation: popIn 0.2s ease forwards;
}
@keyframes popIn { to { transform: scale(1); } }

/* SHIFT INFO BOX */
.shift-info-glass {
    background: rgba(0,0,0,0.2); 
    border-radius: 12px; 
    padding: 15px; 
    margin-bottom: 20px;
    text-align: center;
    border: 1px solid rgba(255,255,255,0.05);
}

</style>

{{-- ERROR MESSAGE --}}
@if (session('error'))
    <div class="warning-box" style="background: rgba(239, 68, 68, 0.15); border-color: #ef4444; color: #f87171;">
        <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
    </div>
@endif

{{-- WELCOME BANNER --}}
<div class="welcome-card">
    <h2 style="margin:0; font-weight:800; font-size: 24px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">Halo, {{ $pegawai->nama }}! 👋</h2>
    <p class="jabatan-text">Selamat bertugas! Hari ini kamu login sebagai <b>{{ ucfirst($pegawai->jabatan) }}</b>.</p>
</div>

{{-- GLOBAL WARNING: WAJIB PENGGANTI --}}
@if ($mustPengganti)
    <div class="warning-box">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>Kamu tidak ada jadwal shift hari ini. Wajib pakai Form Pengganti jika ingin bekerja.</span>
    </div>
@endif

<div class="row-flex">

    {{-- LEFT BOX: STATUS & ACTIONS --}}
    <div class="card-box">
        <h3><i class="fa-solid fa-user-clock"></i> Status Shift Hari Ini</h3>

        {{-- INFO JAM KERJA --}}
        @if($shift)
            <div class="shift-info-glass">
                <span style="display:block; font-size:12px; color:var(--text-mid); margin-bottom:4px;">JAM KERJA</span>
                <span style="font-size:20px; font-weight:800; color:white; font-family:monospace;">
                    {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} — {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                </span>
            </div>
        @else
            <div class="shift-info-glass" style="border-color: rgba(248,113,113,0.3); color: #f87171;">
                <i class="fa-solid fa-calendar-xmark"></i> Tidak Terjadwal
            </div>
        @endif

        {{-- BADGE STATUS --}}
        @php
            $st = \Illuminate\Support\Str::slug($statusAbsen);
            $cls = match(true) {
                str_contains($st, 'hadir') => 'status-hadir',
                str_contains($st, 'pengganti') => 'status-pengganti',
                str_contains($st, 'terlambat') => 'status-terlambat',
                str_contains($st, 'alpha') => 'status-alpha',
                str_contains($st, 'siap') => 'status-siap',
                str_contains($st, 'tidak-terjadwal') => 'status-tidak-terjadwal',
                default => 'status-belum'
            };
        @endphp

        <div style="text-align:center; margin-bottom: 25px;">
            <div class="status-badge {{ $cls }}">{{ strtoupper($statusAbsen) }}</div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div style="margin-top:auto;">
            @if($mustPengganti)
                <a href="{{ route('absensi.pengganti.form') }}" class="btn-secondary-glass warning" style="width:100%">
                    <i class="fa-solid fa-user-group"></i> Isi Form Pengganti
                </a>
            @elseif($canCheckIn)
                <button class="btn-checkin" onclick="submitCheckIn()">
                    <i class="fa-solid fa-fingerprint"></i> Check-In Sekarang
                </button>
            @elseif($active)
                <button class="btn-checkout-danger" onclick="openModal('checkoutModal')">
                    <i class="fa-solid fa-right-from-bracket"></i> Check-Out Sesi
                </button>
                <div style="text-align:center; margin-top:10px; font-size:12px; color:#d1d5db;">
                    Check-in pada: <b>{{ \Carbon\Carbon::parse($active->check_in_at)->format('H:i') }}</b>
                </div>
            @else
                @if(!$shift)
                    <div style="text-align:center; color:var(--text-mid);">Tidak ada jadwal.</div>
                @else
                    <div style="background:rgba(255,255,255,0.05); padding:12px; border-radius:10px; text-align:center; font-size:13px; color:#9ca3af;">
                        <i class="fa-solid fa-lock"></i> Tombol absen terkunci (Diluar Jam).
                    </div>
                @endif
            @endif
        </div>

        {{-- HIDDEN FORM UTAMA --}}
        <form id="checkin-form" action="{{ route('absensi.checkin') }}" method="POST" style="display:none;">
            @csrf
            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="lng" id="lng">
        </form>
    </div>

    {{-- RIGHT BOX: SESI HISTORY --}}
    <div class="card-box">
        <h3><i class="fa-solid fa-list-check"></i> Riwayat Hari Ini</h3>

        @if ($hadirSesi->isEmpty())
            <div style="text-align:center; padding:30px; color:var(--text-mid); opacity:0.7;">
                <i class="fa-regular fa-clock" style="font-size:32px; margin-bottom:10px;"></i><br>
                Belum ada sesi masuk.
            </div>
        @else
            @foreach ($hadirSesi as $s)
                <div class="sesi-item">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <span class="session-time session-ok">{{ \Carbon\Carbon::parse($s->check_in_at)->format('H:i') }}</span>
                            <span style="opacity:0.5; margin:0 5px;">➔</span>
                            @if ($s->check_out_at)
                                <span class="session-time session-ok">{{ \Carbon\Carbon::parse($s->check_out_at)->format('H:i') }}</span>
                            @else
                                <span class="session-time session-active">AKTIF</span>
                            @endif
                        </div>
                        @if(!$s->check_out_at)
                            <div style="font-size:10px; background:rgba(248,113,113,0.2); color:#f87171; padding:2px 6px; border-radius:4px;">ON DUTY</div>
                        @endif
                    </div>
                    @if ($s->catatan)
                        <div style="font-size:12px; color:var(--text-mid); margin-top:4px; font-style:italic;">
                            "{{ $s->catatan }}"
                        </div>
                    @endif
                    @if($s->status_kehadiran == 'pengganti')
                         <div style="font-size:11px; color:#facc15; margin-top:2px;">
                            <i class="fa-solid fa-user-shield"></i> Shift Pengganti
                        </div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>

</div>

{{-- MODAL CHECK-OUT --}}
<div id="checkoutModal" class="modal-backdrop" onclick="closeModal('checkoutModal')">
    <div class="modal-glass" onclick="event.stopPropagation()">

        <h3 style="margin-top:0; color:white;">
            <i class="fa-solid fa-right-from-bracket" style="color:#f87171;"></i>
            Konfirmasi Check-out
        </h3>

        {{-- WARNING PULANG AWAL — PAKAI VARIABEL DARI CONTROLLER --}}
        @if($isEarly)
            <div class="warning-box"
                style="background:rgba(239,68,68,0.1); border-color:#ef4444; color:#f87171; font-size:13px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>
                    <strong>Pulang Awal?</strong><br>
                    Shift kamu seharusnya selesai jam <b>{{ $endTimeFormatted }}</b>.
                </div>
            </div>
        @endif

        <form action="{{ route('absensi.checkout') }}" method="POST">
            @csrf

            <label style="display:block; margin-bottom:8px; font-size:13px; color:#d1d5db;">
                Catatan (Wajib jika pulang awal)
            </label>
            <textarea name="alasan" rows="3"
                placeholder="Contoh: Izin sakit / Urusan keluarga..."
                style="width:100%; padding:12px; border-radius:10px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.2); color:white;">
            </textarea>

            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn-secondary-glass" onclick="closeModal('checkoutModal')">
                    Batal
                </button>
                <button type="submit" class="btn-checkout-danger">Check-out</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// --- MODAL UTILS ---
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

// --- GEO LOCATION LOGIC ---
const GEO_TIMEOUT = 8000;

async function getGeoPosition(timeout){
    return new Promise((resolve) => {
        if (!navigator.geolocation) return resolve(null);
        let resolved = false;
        const timer = setTimeout(()=>{ if(!resolved){resolved = true; resolve(null);} }, timeout);
        navigator.geolocation.getCurrentPosition(pos => {
            if (resolved) return;
            resolved = true; clearTimeout(timer);
            resolve({lat: pos.coords.latitude, lng: pos.coords.longitude});
        }, err => {
            console.error(err);
            if (resolved) return;
            resolved = true; clearTimeout(timer);
            resolve(null);
        }, {enableHighAccuracy:true, maximumAge:0, timeout:timeout});
    });
}

async function submitCheckIn() {
    const btn = document.querySelector('.btn-checkin');
    if(!btn) return;
    const originalText = btn.innerHTML;
    
    // Loading State
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Mendapatkan Lokasi...';

    // Get GPS
    const geo = await getGeoPosition(GEO_TIMEOUT);

    if (geo) {
        document.getElementById('lat').value = geo.lat;
        document.getElementById('lng').value = geo.lng;
        document.getElementById('checkin-form').submit();
    } else {
        // Error State
        btn.disabled = false;
        btn.innerHTML = originalText;
        Swal.fire({
            icon: 'error',
            title: 'Lokasi Gagal!',
            text: 'Gagal mendapatkan GPS. Pastikan izin lokasi aktif dan sinyal bagus.',
            background: '#1f2937', color: '#fff', confirmButtonColor: '#ef4444'
        });
    }
}
</script>
@endpush