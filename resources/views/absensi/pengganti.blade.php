@extends('layout')
@section('page_title','Pengganti Hari Ini')

@section('content')
<div style="max-width:520px;margin:30px auto">
  <div style="background:#fff;padding:18px;border-radius:12px">
    <h3>Anda Pengganti Hari Ini</h3>
    <p style="color:var(--muted)">Silakan pilih karyawan yang Anda gantikan</p>

    <form method="POST" action="{{ route('absensi.pengganti.store') }}">
      @csrf
      <input type="hidden" name="absensi_id" value="{{ request('absensi_id') ?? '' }}">

      <div style="margin:12px 0">
        <label>Pilih Karyawan</label>
        <select name="digantikan_id" style="width:100%;padding:10px;border-radius:8px;border:1px solid #eee">
          <option value="">Pilih...</option>
          @foreach($karyawan as $k)
            <option value="{{ $k->id }}">{{ $k->nama ?? $k->name }}</option>
          @endforeach
        </select>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:12px">
        <a href="{{ route('absensi.index') }}" class="btn-outline">Batal</a>
        <button class="btn" type="submit">Konfirmasi Penggantian</button>
      </div>
    </form>
  </div>
</div>
@endsection
