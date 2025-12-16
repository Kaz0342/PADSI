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

.status-hadir, .status-pengganti {
    background: rgba(74, 222, 128, 0.15); color: #4ade80; border-color: #4ade80;
    box-shadow: 0 0 20px rgba(74, 222, 128, 0.2);
}
.status-terlambat {
    background: rgba(250, 204, 21, 0.15); color: #facc15; border-color: #facc15;
    box-shadow: 0 0 20px rgba(250, 204, 21, 0.2);
}
.status-alpha {
    background: rgba(248, 113, 113, 0.15); color: #f87171; border-color: #f87171;
    box-shadow: 0 0 20px rgba(248, 113, 113, 0.2);
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
.btn-checkout-danger {
    background: linear-gradient(135deg, #ef4444, #b91c1c);
    color: white; border: none; padding: 12px 24px; border-radius: 10px;
    font-weight: 700; cursor: pointer; width: 100%;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.btn-checkout-danger:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(239, 68, 68, 0.6); }

.btn-secondary-glass {
    background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
    color: white; padding: 12px 24px; border-radius: 10px;
    font-weight: 600; cursor: pointer; width: 100%; text-align: center;
    text-decoration: none; display: inline-block; transition: 0.2s;
}
.btn-secondary-glass:hover { background: rgba(255,255,255,0.2); }

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

<div class="row-flex">

    {{-- LEFT BOX: STATUS HARI INI (SIMPLE) --}}
    <div class="card-box">
        <h3><i class="fa-solid fa-user-clock"></i> Status Hari Ini</h3>

        @if($todayAbsensi)
            {{-- LOGIC BADGE WARNA --}}
            @php
                $st = \Illuminate\Support\Str::slug($todayAbsensi->status_kehadiran);
                $badgeClass = match(true) {
                    str_contains($st, 'hadir') => 'status-hadir',
                    str_contains($st, 'pengganti') => 'status-pengganti',
                    str_contains($st, 'terlambat') => 'status-terlambat',
                    str_contains($st, 'alpha') => 'status-alpha',
                    default => 'status-hadir'
                };
            @endphp

            <div style="text-align:center; margin-bottom:20px;">
                <div class="status-badge {{ $badgeClass }}">
                    {{ strtoupper($todayAbsensi->status_kehadiran) }}
                </div>
            </div>

            @if($active)
                {{-- TOMBOL CHECKOUT (MEMBUKA MODAL) --}}
                <button class="btn-checkout-danger" onclick="openModal('checkoutModal')">
                    <i class="fa-solid fa-right-from-bracket"></i> Check-Out
                </button>
                <div style="text-align:center; margin-top:10px; font-size:12px; color:#d1d5db;">
                    Check-in: <b>{{ \Carbon\Carbon::parse($active->check_in_at)->format('H:i') }}</b>
                </div>
            @else
                {{-- SUDAH PULANG --}}
                <div style="text-align:center; color:#4ade80; background:rgba(74, 222, 128, 0.1); padding:10px; border-radius:10px;">
                    <i class="fa-solid fa-check-circle"></i> Sudah check-out hari ini.
                </div>
            @endif
        @else
            {{-- BELUM ABSEN --}}
            <div style="text-align:center; color:var(--text-mid); padding:20px;">
                <i class="fa-solid fa-coffee" style="font-size:24px; margin-bottom:10px; display:block;"></i>
                Belum ada data absensi hari ini.
            </div>
        @endif
    </div>

    {{-- RIGHT BOX: SESI HISTORY --}}
    <div class="card-box">
        <h3><i class="fa-solid fa-list-check"></i> Riwayat Sesi</h3>

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
                </div>
            @endforeach
        @endif
    </div>

</div>

{{-- MODAL CHECK-OUT (FORM SUBMIT) --}}
<div id="checkoutModal" class="modal-backdrop" onclick="closeModal('checkoutModal')">
    <div class="modal-glass" onclick="event.stopPropagation()">

        <h3 style="margin-top:0; color:white;">
            <i class="fa-solid fa-right-from-bracket" style="color:#f87171;"></i>
            Konfirmasi Check-out
        </h3>

        <p style="color:#d1d5db; font-size:14px; margin-bottom:20px;">
            Yakin mau pulang sekarang, King?
        </p>

        {{-- 🔥 FORM YANG BENAR: POST KE ROUTE --}}
        <form action="{{ route('absensi.checkout') }}" method="POST">
            @csrf
            <label style="display:block; margin-bottom:8px; font-size:13px; color:#d1d5db;">
                Catatan (Opsional)
            </label>
            <textarea name="alasan" rows="3"
                placeholder="Contoh: Pulang, Izin sakit, dll..."
                style="width:100%; padding:12px; border-radius:10px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.2); color:white; outline:none;">
            </textarea>

           <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button"
                    class="btn-secondary-glass"
                    style="padding:10px 18px; font-size:13px;"
                    onclick="closeModal('checkoutModal')">
                    Batal
                </button>

                <button type="submit"
                    class="btn-checkout-danger"
                    style="padding:10px 18px; font-size:13px;">
                    Check-out
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// --- MODAL UTILS ---
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
</script>
@endpush