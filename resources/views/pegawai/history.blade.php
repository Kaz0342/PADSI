@extends('layouts.app')
@section('title', 'Riwayat Saya')

@section('content')

<div style="margin-bottom:25px;">
    <h2 style="font-weight:800; color:white;">Riwayat Kehadiran</h2>
    <p style="opacity:.8; color:white;">Semua catatan absensi kamu.</p>
</div>

<div style="background:rgba(255,255,255,0.15); backdrop-filter:blur(10px); padding:20px; border-radius:16px; margin-bottom:20px;">

    <form method="GET" style="display:flex; gap:15px; margin-bottom:10px;">
        <select name="month" class="form-control" style="max-width:160px;">
            @for($m=1; $m<=12; $m++)
                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endfor
        </select>

        <select name="year" class="form-control" style="max-width:120px;">
            @for($y=2023; $y <= now()->year; $y++)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>

        <button class="btn btn-warning">Filter</button>
    </form>
</div>

@if($history->count() == 0)
    <div style="padding:25px; text-align:center; color:white; opacity:.8;">
        <i class="fa-regular fa-folder-open" style="font-size:40px;"></i>
        <p style="margin-top:10px;">Tidak ada data absensi bulan ini.</p>
    </div>
@else
    <div style="display:flex; flex-direction:column; gap:15px;">

        @foreach($history as $row)
        <div style="
            background:rgba(255,255,255,0.15);
            backdrop-filter:blur(10px);
            padding:18px;
            border-radius:14px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        ">
            <div>
                <div style="color:#ffd27f; font-weight:700; font-size:16px;">
                    {{ $row['tanggal'] }}
                </div>
                <div style="color:white; margin-top:6px; font-size:14px;">
                    ⏱ Check-in: <b>{{ $row['check_in'] }}</b>  
                    —  
                    Check-out: <b>{{ $row['check_out'] }}</b>
                </div>
                <div style="color:white; opacity:.85; margin-top:6px; font-size:13px;">
                    Durasi: <b>{{ $row['durasi'] }}</b>
                </div>
            </div>

            <div>
                <span style="
                    background:#f7a20a;
                    padding:6px 12px;
                    border-radius:8px;
                    font-weight:700;
                    color:#fff;
                    font-size:13px;
                ">
                    {{ $row['status'] }}
                </span>
            </div>
        </div>
        @endforeach

    </div>
@endif

@endsection
