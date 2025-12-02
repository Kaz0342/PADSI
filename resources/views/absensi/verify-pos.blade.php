@extends('layouts.app')

@section('title', 'Verifikasi POS')

@section('content')

<style>
.badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-weight:600;
    font-size: 12px;
    display:inline-block;
}

.badge-hadir { background:#d1fae5; color:#047857; }
.badge-terlambat { background:#fef3c7; color:#b45309; }
.badge-alpha { background:#fee2e2; color:#b91c1c; }
.badge-nopos { background:#ffe4e6; color:#be123c; }
.badge-new { background:#ede9fe; color:#5b21b6; margin-left:6px; }

.row-nopos {
    background:#fff1f2 !important;
    border-left:4px solid #fecdd3;
}

.table td { padding:12px; }
</style>

<div class="card" style="padding:25px;">
    <h2 style="margin-bottom:12px;">Verifikasi POS — {{ $date }}</h2>

    <table class="table" style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc; text-align:left;">
                <th style="padding:10px;">Pegawai</th>
                <th style="padding:10px;">Shift Masuk</th>
                <th style="padding:10px;">Check-in POS</th>
                <th style="padding:10px;">Status</th>
                <th style="padding:10px;">Action</th>
            </tr>
        </thead>

        <tbody>
            @foreach($rows as $r)
            <tr class="{{ $r['has_pos'] ? '' : 'row-nopos' }}" style="border-bottom:1px solid #e5e7eb;">
                
                {{-- PEGawai --}}
                <td style="font-weight:600;">
                    {{ $r['nama'] }}

                    @if($r['has_pos'])
                        <span class="badge badge-new">NEW POS</span>
                    @endif
                </td>

                {{-- Shift --}}
                <td>{{ $r['shift'] }}</td>

                {{-- Check-in --}}
                <td>
                    {{ $r['first_ts'] ? $r['first_ts']->format('H:i:s') : '-' }}
                </td>

                {{-- STATUS --}}
                <td>
                    @php
                        $status = $r['status'];
                    @endphp

                    @if($status == 'hadir')
                        <span class="badge badge-hadir">HADIR</span>

                    @elseif($status == 'terlambat')
                        <span class="badge badge-terlambat">TERLAMBAT</span>

                    @elseif($status == 'alpha')
                        <span class="badge badge-alpha">ALPHA</span>

                    @else
                        <span class="badge badge-nopos">NO POS</span>
                    @endif
                </td>

                {{-- ACTION --}}
                <td>
                    @if($r['has_pos'])
                        <button class="btn" onclick="approveSingle({{ $r['id'] }})">Approve</button>
                    @else
                        <span style="color:#dc2626; font-weight:600;">Tidak Ada Data POS</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <br><br>
    <button class="btn" style="background:#f59e0b;" onclick="approveAll()">Approve Semua</button>
    <a class="btn" href="{{ route('absensi.rekap') }}">Kembali ke Rekap</a>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function approveSingle(id){
    Swal.fire({
        title: 'Approve Absensi?',
        text: 'Data POS akan dipakai sebagai check-in.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#dc2626',
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
            Swal.fire({ icon:'success', title:'Berhasil!', text:'Absensi diperbarui.' })
                .then(()=>location.reload());
        });
    });
}

function approveAll(){
    Swal.fire({
        title: 'Approve semua data?',
        text: 'Seluruh data POS pada tanggal ini akan otomatis disetujui.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Approve Semua'
    }).then((result)=>{
        if(!result.isConfirmed) return;

        fetch('/api/pos/approve-all',{
            method:'POST',
            headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}' },
            body:JSON.stringify({date:"{{ $date }}"})
        })
        .then(r=>r.json())
        .then(res=>{
            Swal.fire({ icon:'success', title:'Sukses!', text:'Semua data berhasil di-approve.' })
                .then(()=>location.reload());
        });
    });
}
</script>

@endsection
