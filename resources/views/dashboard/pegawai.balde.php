@extends('layout')
@section('page_title','Dashboard Owner')

@section('content')
<div style="display:flex;flex-direction:column;gap:18px">

  <div style="background:linear-gradient(90deg,#f7a20a,#e58e00);padding:22px;border-radius:12px;color:#fff;display:flex;justify-content:space-between;align-items:center">
    <div>
      <h3 style="margin:0">Selamat Datang, {{ $user->name }}!</h3>
      <div style="opacity:.95">Anda memiliki akses penuh ke sistem.</div>
    </div>

    <div style="display:flex;gap:10px">
      <a href="{{ route('absensi.index') }}" class="btn">Absensi</a>
      <a href="{{ route('pegawai.index') }}" class="btn">Kelola Karyawan</a>
      <a href="#" class="btn">Stok Opname</a>
    </div>
  </div>

  <div class="grid">
    <div class="card">
      <h4 style="margin:0 0 8px 0">Karyawan Aktif</h4>
      <div style="font-size:26px;font-weight:800;color:var(--orange)">{{ $karyawanAktif ?? 0 }}</div>
    </div>
    <div class="card">
      <h4 style="margin:0 0 8px 0">Karyawan Cuti</h4>
      <div style="font-size:26px;font-weight:800;color:#f2b300">{{ $karyawanCuti ?? 0 }}</div>
    </div>
    <div class="card">
      <h4 style="margin:0 0 8px 0">Hadir Hari Ini</h4>
      <div style="font-size:26px;font-weight:800;color:#1f8b3a">{{ $hadirHariIni ?? 0 }}</div>
    </div>
    <div class="card">
      <h4 style="margin:0 0 8px 0">Stok Rendah</h4>
      <div style="font-size:26px;font-weight:800;color:#e05800">{{ $stokRendah ?? 0 }}</div>
    </div>
  </div>

</div>
@endsection
