@extends('layouts.app')
@section('title','Jadwal Kerja Mingguan')
@section('content')
@php use Carbon\Carbon; @endphp

<style>
/* =========================================
   🎨 SHIFT PAGE SPECIFIC STYLES
   ========================================= */
:root {
    --text-mid: #9ca3af;
}

/* Glass Card Container */
.container-card {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    border-radius: 16px;
    box-shadow: var(--glass-shadow);
    overflow: hidden;
    color: white;
}

/* Header Section */
.header {
    padding: 20px 24px;
    display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
    background: rgba(0,0,0,0.2);
    border-bottom: 1px solid var(--glass-border);
}

.h-left h2 { margin:0; font-size:20px; color:white; font-weight:800; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
.h-left p { margin:4px 0 0 0; font-size:13px; color:var(--text-mid); }

/* Navigation Buttons */
.btn-week {
    background: rgba(255,255,255,0.1);
    border: 1px solid var(--glass-border);
    padding: 8px 14px;
    border-radius: 8px;
    cursor: pointer;
    transition: .2s;
    font-weight: 700;
    color: white;
}
.btn-week:hover { background: var(--accent); border-color: var(--accent); color: #fff; }

.date-display {
    background: rgba(0,0,0,0.3);
    padding: 8px 16px;
    border-radius: 10px;
    border: 1px solid var(--glass-border);
    color: var(--accent);
    font-family: monospace;
    font-size: 14px;
}

/* --- NEW: SAVE BUTTON STYLE --- */
.btn-save {
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    color: white;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
    transition: all 0.2s;
    display: flex; align-items: center; gap: 8px;
    animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6); }
.btn-save:active { transform: scale(0.95); }

@keyframes popIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }

/* Legend */
.legend { display: flex; gap: 14px; align-items: center; }
.legend .item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #e5e7eb; font-weight: 500; }
.dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; box-shadow: 0 0 5px rgba(255,255,255,0.2); }
.dot.in { background: var(--accent); box-shadow: 0 0 8px var(--accent); }
.dot.off { background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); }
.dot.closed { background: repeating-linear-gradient(45deg, #444, #444 2px, #333 2px, #333 4px); border: 1px solid #555; }

/* Table Styling */
.table-wrap { overflow-x: auto; padding: 0; background: transparent; }
.gantt-table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 900px; }

.gantt-table th {
    padding: 12px; text-align: center;
    border-bottom: 1px solid var(--glass-border);
    background: rgba(255,255,255,0.03);
    color: var(--text-mid);
    font-weight: 600;
}

.gantt-table td {
    padding: 10px; text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    vertical-align: middle;
}

/* Sticky Column */
.gantt-table th:first-child, 
.gantt-table td:first-child {
    position: sticky; left: 0; z-index: 10;
    text-align: left; padding-left: 24px;
    border-right: 1px solid var(--glass-border);
    background: rgba(30, 30, 30, 0.95); 
    backdrop-filter: blur(10px);
}

/* Avatar */
.avatar {
    width: 36px; height: 36px;
    background: linear-gradient(135deg, var(--accent), #d97706);
    border-radius: 10px; color: white;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

/* Shift Button */
.shift-btn {
    width: 100%; height: 44px; border-radius: 10px;
    border: 1px dashed rgba(255,255,255,0.3);
    background: transparent;
    color: transparent; cursor: pointer; transition: .2s cubic-bezier(0.4, 0, 0.2, 1);
}
.shift-btn:hover:not(:disabled) {
    background: rgba(255,255,255,0.1);
    border-color: var(--accent);
}
.shift-btn.active {
    background: var(--accent);
    color: white;
    border: 1px solid var(--accent);
    box-shadow: 0 0 15px rgba(247, 162, 10, 0.4);
    transform: scale(1.02);
}
.shift-btn.disabled {
    background: repeating-linear-gradient(45deg, rgba(0,0,0,0.2), rgba(0,0,0,0.2) 10px, rgba(0,0,0,0.3) 10px, rgba(0,0,0,0.3) 20px);
    cursor: not-allowed; opacity: 0.7;
    border: 1px solid rgba(255,255,255,0.05);
}

/* Footer */
.footer-summary {
    display: flex; justify-content: space-between; align-items: center;
    padding: 16px 24px;
    border-top: 1px solid var(--glass-border);
    background: rgba(0,0,0,0.2);
}
.summary-pill {
    background: rgba(255,255,255,0.1);
    padding: 6px 12px; border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.1);
    display: flex; flex-direction: column; align-items: center;
    min-width: 80px;
}
</style>

<div class="container-card">

    {{-- HEADER --}}
    <div class="header">
        <div class="h-left">
            <h2>Jadwal Kerja Mingguan</h2>
            <p>Klik kotak untuk ubah jadwal, lalu klik <b>Simpan</b>.</p>
        </div>

        <div class="controls" style="display:flex;align-items:center;gap:12px;">
            
            {{-- TOMBOL SIMPAN (Muncul otomatis via JS) --}}
            <button id="btnSave" class="btn-save" style="display: none;">
                <i class="fa-solid fa-floppy-disk"></i> Simpan (<span id="changeCount">0</span>)
            </button>

            {{-- WEEK NAVIGATION --}}
            <button class="btn-week" id="prevWeek"><i class="fa-solid fa-chevron-left"></i></button>
            <div class="date-display">
                <strong id="weekLabel">
                    {{ $monday->format('d M') }} — {{ $monday->copy()->addDays(6)->format('d M Y') }}
                </strong>
            </div>
            <button class="btn-week" id="nextWeek"><i class="fa-solid fa-chevron-right"></i></button>

            {{-- LEGEND --}}
            <div class="legend" style="margin-left: 10px;">
                <div class="item"><span class="dot in"></span>Masuk</div>
                <div class="item"><span class="dot off"></span>Libur</div>
                <div class="item"><span class="dot closed"></span>Tutup</div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="table-wrap">
        <table class="gantt-table">
            <thead>
                <tr>
                    <th style="min-width: 200px;">Pegawai</th>
                    @foreach($dates as $d)
                        <th>
                            <div style="font-weight:700; color:white;">{{ $d['day_name'] }}</div>
                            <div style="font-size:11px; opacity:0.7;">
                                {{ $d['label'] }}
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
            @foreach($pegawais as $p)
                <tr data-pegawai="{{ $p->id }}">
                    <td>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div class="avatar">{{ strtoupper(substr($p->nama,0,1)) }}</div>
                            <div>
                                <div style="font-weight:700; color:white;">{{ $p->nama }}</div>
                                <div style="font-size:11px; color:var(--text-mid)">
                                    {{ '@'.optional($p->user)->username }}
                                </div>
                            </div>
                        </div>
                    </td>

                    @foreach($dates as $d)
                        @php
                            $tgl = $d['date'];
                            // Logic Toko Tutup (Senin / Manual)
                            $isSenin = \Carbon\Carbon::parse($tgl)->isMonday();
                            $tglRow = $tanggalKerjaRows[$tgl] ?? null;
                            
                            if ($isSenin) {
                                $isOpen = false; 
                            } else {
                                $isOpen = $tglRow ? (bool)$tglRow->is_open : true;
                            }

                            $assigned = $p->jadwals->contains(fn($j)=> $j->tanggal->toDateString() === $tgl);
                        @endphp

                        <td>
                            <button
                                class="shift-btn {{ $assigned ? 'active' : '' }} {{ $isOpen ? '' : 'disabled' }}"
                                data-pegawai="{{ $p->id }}"
                                data-tanggal="{{ $tgl }}"
                                {{ $isOpen ? '' : 'disabled' }}
                                title="{{ $isOpen ? 'Klik untuk ubah jadwal' : 'Toko Tutup' }}"
                            >
                                {!! $assigned ? '<i class="fa-solid fa-check" style="font-size:16px;"></i>' : '' !!}
                            </button>
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- SUMMARY --}}
    <div class="footer-summary">
        <div style="display:flex; align-items:center; gap:15px;">
            <small style="color:var(--text-mid); font-weight:600;">Total Shift:</small>
            <div class="summary-list" style="display:flex; gap:10px; overflow-x:auto;">
                @foreach($pegawais as $p)
                    <div class="summary-pill">
                        <div style="font-weight:700; color:white; font-size:13px;">{{ $p->nama }}</div>
                        <div style="font-size:11px; color:var(--accent)">
                            {{ $summary[$p->id] ?? 0 }}x
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div style="color:var(--text-mid); font-size:12px; display:flex; align-items:center; gap:5px;">
            <i class="fa-solid fa-circle-info"></i> Mode: <strong>Manual Save</strong>
        </div>
    </div>
</div>

<div id="toast"></div>

@endsection

@push('scripts')
<script>
(function(){

    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let monday = new Date("{{ $monday->toDateString() }}");
    
    // STATE PENYIMPANAN SEMENTARA
    let pendingChanges = {}; 
    const btnSave = document.getElementById('btnSave');
    const changeCountSpan = document.getElementById('changeCount');

    // UTILS DATE
    const formatISO = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;

    // --- NAVIGATION LOGIC ---
    const goToWeek = () => {
        if (Object.keys(pendingChanges).length > 0) {
            if(!confirm('Ada perubahan belum disimpan! Yakin mau pindah minggu? Perubahan akan hilang.')) return;
        }
        const url = new URL(window.location.href);
        url.searchParams.set('week_start', formatISO(monday));
        window.location.href = url;
    };

    document.getElementById('prevWeek').onclick = () => { monday.setDate(monday.getDate() - 7); goToWeek(); };
    document.getElementById('nextWeek').onclick = () => { monday.setDate(monday.getDate() + 7); goToWeek(); };


    // --- KLIK TOGGLE JADWAL ---
    document.addEventListener('click', e => {
        const btn = e.target.closest('.shift-btn');
        if (!btn || btn.disabled || btn.classList.contains('disabled')) return;

        // 1. Ubah Tampilan Dulu (Optimistic UI)
        const isNowActive = btn.classList.toggle('active');
        btn.innerHTML = isNowActive ? `<i class="fa-solid fa-check" style="font-size:16px;"></i>` : '';

        // 2. Catat Data Perubahan
        const pegawai = btn.dataset.pegawai;
        const tanggal = btn.dataset.tanggal;
        const key = `${pegawai}_${tanggal}`;

        // Simpan ke state
        pendingChanges[key] = {
            pegawai_id: pegawai,
            tanggal: tanggal,
            assign: isNowActive ? 1 : 0
        };

        // 3. Update Tombol Save
        const count = Object.keys(pendingChanges).length;
        changeCountSpan.innerText = count;
        
        // Munculin tombol kalo ada perubahan, sembunyiin kalo 0
        btnSave.style.display = count > 0 ? 'flex' : 'none';
        
        // Safety: Cegah close tab ga sengaja
        window.onbeforeunload = count > 0 ? () => "Ada perubahan belum disimpan!" : null;
    });


    // --- PROSES SIMPAN (BATCH) ---
    btnSave.onclick = async () => {
        const originalText = btnSave.innerHTML;
        
        // Loading State
        btnSave.disabled = true;
        btnSave.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i> Menyimpan...`;

        // Convert Object ke Array
        const payload = Object.values(pendingChanges);

        try {
            // CALL KE ENDPOINT BARU 'saveBatch'
            const res = await fetch("{{ route('shifts.saveBatch') }}", { 
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf,
                    "Accept": "application/json"
                },
                body: JSON.stringify({ shifts: payload })
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Gagal menyimpan');

            // Reset State setelah sukses
            pendingChanges = {};
            btnSave.style.display = 'none';
            window.onbeforeunload = null;

            // Show Success Toast
            if(typeof window.showToast === 'function') window.showToast('Jadwal berhasil diperbarui!', 'success');
            else localShowToast('Jadwal berhasil diperbarui!');

            // Opsional: Reload page biar data fresh dari DB
            // window.location.reload(); 

        } catch (err) {
            console.error(err);
            if(typeof window.showToast === 'function') window.showToast(err.message, 'error');
            else localShowToast(err.message, true);
        } finally {
            btnSave.disabled = false;
            btnSave.innerHTML = originalText;
        }
    };

    // --- TOAST FALLBACK (Jaga-jaga kalo layout ga punya showToast) ---
    function localShowToast(msg, error=false){
        const box = document.getElementById('toast') || document.body;
        const el = document.createElement('div');
        el.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            background: rgba(30, 30, 30, 0.9); backdrop-filter: blur(10px);
            color: #fff; padding: 14px 20px; border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); border: 1px solid ${error ? '#f87171' : '#4ade80'};
            display: flex; align-items: center; gap: 12px; animation: fadeIn 0.3s ease;
        `;
        let icon = error ? '❌' : '✅';
        el.innerHTML = `${icon} <span>${msg}</span>`;
        box.appendChild(el);
        setTimeout(()=> { el.style.opacity='0'; setTimeout(()=>el.remove(),300); }, 3000);
    }

})();
</script>
@endpush