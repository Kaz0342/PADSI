@extends('layouts.app')

@section('title', 'Verifikasi POS')

@section('content')

<style>
/* Glass Card */
.verify-card {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    border-radius: 16px;
    padding: 30px;
    box-shadow: var(--glass-shadow);
    color: white;
}

/* Table Style */
.table-glass { width: 100%; border-collapse: collapse; margin-top: 20px; }
.table-glass th {
    background: rgba(0,0,0,0.2);
    padding: 16px; text-align: left;
    color: var(--text-muted); font-size: 12px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.5px;
    border-bottom: 1px solid var(--glass-border);
}
.table-glass td {
    padding: 16px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    vertical-align: middle;
}
.table-glass tr:hover td { background: rgba(255,255,255,0.05); }

/* Badges */
.badge { padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; display: inline-block; text-transform: uppercase; border: 1px solid transparent; }
.badge-hadir { background: rgba(74, 222, 128, 0.2); color: #4ade80; border-color: #4ade80; }
.badge-terlambat { background: rgba(250, 204, 21, 0.2); color: #facc15; border-color: #facc15; }
.badge-alpha { background: rgba(248, 113, 113, 0.2); color: #f87171; border-color: #f87171; }
.badge-nopos { background: rgba(255, 255, 255, 0.1); color: #d1d5db; border-color: rgba(255,255,255,0.2); }
.badge-new { background: #facc15; color: #000; margin-left: 8px; box-shadow: 0 0 10px rgba(250,204,21,0.5); }

/* Row Highlight jika tidak ada POS */
.row-nopos td { opacity: 0.5; }
.row-nopos:hover td { opacity: 1; }

/* Buttons */
.btn-approve {
    background: linear-gradient(135deg, #4ade80, #16a34a);
    color: white; border: none; padding: 8px 16px; border-radius: 8px;
    font-weight: 700; font-size: 12px; cursor: pointer; transition: 0.2s;
    box-shadow: 0 4px 10px rgba(74, 222, 128, 0.3);
}
.btn-approve:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(74, 222, 128, 0.5); }

.btn-all {
    background: linear-gradient(135deg, var(--accent), #d97706);
    color: white; padding: 12px 24px; border-radius: 10px; border: none;
    font-weight: 700; cursor: pointer; transition: 0.2s;
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
}
.btn-all:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(245, 158, 11, 0.6); }

.btn-back {
    background: rgba(255,255,255,0.1); color: white; padding: 12px 24px; border-radius: 10px;
    text-decoration: none; font-weight: 600; display: inline-block; transition: 0.2s;
    border: 1px solid rgba(255,255,255,0.2);
}
.btn-back:hover { background: rgba(255,255,255,0.2); }

</style>

<div class="verify-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="margin:0; font-size:24px; text-shadow:0 2px 4px rgba(0,0,0,0.5);">
            <i class="fa-solid fa-list-check" style="color:var(--accent); margin-right:10px;"></i> Verifikasi POS
        </h2>
        <div style="font-size:16px; font-weight:700; background:rgba(255,255,255,0.1); padding:8px 16px; border-radius:8px; border:1px solid rgba(255,255,255,0.2);">
            {{ \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMMM Y') }}
        </div>
    </div>

    <table class="table-glass">
        <thead>
            <tr>
                <th>Pegawai</th>
                <th>Shift Masuk</th>
                <th>Check-in POS</th>
                <th>Status Deteksi</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($rows as $r)
            <tr class="{{ $r['has_pos'] ? '' : 'row-nopos' }}">
                
                {{-- Nama --}}
                <td style="font-weight:700; font-size:15px;">
                    {{ $r['nama'] }}
                    @if($r['has_pos']) <span class="badge badge-new">NEW</span> @endif
                </td>

                {{-- Shift --}}
                <td><i class="fa-regular fa-clock" style="margin-right:6px; opacity:0.6;"></i> {{ $r['shift'] }}</td>

                {{-- Check-in --}}
                <td style="font-family:monospace; font-size:14px; color:#a5f3fc;">
                    {{ $r['first_ts'] ? $r['first_ts']->format('H:i:s') : '-' }}
                </td>

                {{-- Status --}}
                <td>
                    @php $s = $r['status']; @endphp
                    @if($s == 'hadir') <span class="badge badge-hadir">HADIR</span>
                    @elseif($s == 'terlambat') <span class="badge badge-terlambat">TERLAMBAT</span>
                    @elseif($s == 'alpha') <span class="badge badge-alpha">ALPHA</span>
                    @else <span class="badge badge-nopos">TIDAK ADA DATA</span>
                    @endif
                </td>

                {{-- Action --}}
                <td>
                    @if($r['has_pos'])
                        <button class="btn-approve" onclick="approveSingle({{ $r['id'] }})">
                            <i class="fa-solid fa-check"></i> Approve
                        </button>
                    @else
                        <span style="font-size:12px; color:#f87171; font-style:italic;">Data POS Kosong</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top:30px; display:flex; gap:15px; align-items:center;">
        <button class="btn-all" onclick="approveAll()">
            <i class="fa-solid fa-check-double"></i> Approve Semua
        </button>
        <a class="btn-back" href="{{ route('absensi.rekap') }}">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Config Swal Dark Mode
const swalDark = {
    background: '#1f2937', color: '#fff', confirmButtonColor: '#f59e0b', cancelButtonColor: '#4b5563'
};

function approveSingle(id){
    Swal.fire({
        ...swalDark,
        title: 'Approve Absensi?',
        text: 'Data POS akan dipakai sebagai waktu check-in.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Approve'
    }).then((result) => {
        if (!result.isConfirmed) return;

        fetch('/api/pos/approve', {
            method:'POST',
            headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
            body:JSON.stringify({pegawai_id:id, date:"{{ $date }}"})
        })
        .then(r=>r.json())
        .then(res=>{
            Swal.fire({ ...swalDark, icon:'success', title:'Berhasil!', text:'Absensi diperbarui.' })
                .then(()=>location.reload());
        });
    });
}

function approveAll(){
    Swal.fire({
        ...swalDark,
        title: 'Approve Semua?',
        text: 'Seluruh data POS yang valid pada tanggal ini akan disetujui.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Gas!'
    }).then((result)=>{
        if(!result.isConfirmed) return;

        fetch('/api/pos/approve-all',{
            method:'POST',
            headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}' },
            body:JSON.stringify({date:"{{ $date }}"})
        })
        .then(r=>r.json())
        .then(res=>{
            Swal.fire({ ...swalDark, icon:'success', title:'Sukses!', text:'Semua data berhasil di-approve.' })
                .then(()=>location.reload());
        });
    });
}
</script>

@endsection