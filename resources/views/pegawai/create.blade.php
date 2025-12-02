@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Tambah Pegawai</h3>
  <form action="{{ route('pegawai.store') }}" method="POST">
    @csrf
    <div class="form-group">
      <label>Nama Lengkap</label>
      <input name="nama" required>
    </div>
    <div class="form-group">
      <label>Username (login)</label>
      <input name="username" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>

    <div class="form-row">
      <div class="col-2 form-group">
        <label>Posisi</label>
        <select name="posisi" required>
          <option value="Barista">Barista</option>
          <option value="Kasir">Kasir</option>
          <option value="Dapur">Dapur</option>
          <option value="Waiter">Waiter</option>
          <option value="Helper">Helper</option>
        </select>
      </div>
      <div class="col-1 form-group">
        <label>Status</label>
        <select name="status" required>
          <option value="aktif">Aktif</option>
          <option value="cuti">Cuti</option>
          <option value="nonaktif">Nonaktif</option>
        </select>
      </div>
    </div>

    <div style="margin-top:12px">
      <a href="{{ route('pegawai.index') }}" class="btn secondary">Batal</a>
      <button class="btn" type="submit">Tambah Pegawai</button>
    </div>
  </form>
</div>
@endsection
