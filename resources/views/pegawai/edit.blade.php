@extends('layouts.app')

@section('title', 'Edit Pegawai: ' . $pegawai->nama) 
@section('content')

<div class="card">
    <h3>Edit Pegawai</h3>
    
    {{-- ALERT Cuti Aktif - diambil dari logic controller lo --}}
    @if ($pegawai->status === 'Cuti' && $activeCuti)
        <div style="background-color: #fff3cd; border-color: #ffeeba; color: #856404; padding: 12px; border: 1px solid transparent; border-radius: 6px; margin-bottom: 20px;">
            <i class="fa-solid fa-triangle-exclamation" style="margin-right: 8px;"></i>
            Pegawai **{{ $pegawai->nama }}** sedang dalam status **CUTI AKTIF**!
            <p style="margin-top: 5px; margin-bottom: 0; font-size: 0.9em;">
                Cuti berlaku dari **{{ \Carbon\Carbon::parse($activeCuti->tanggal_mulai)->format('d M Y') }}** sampai **{{ \Carbon\Carbon::parse($activeCuti->tanggal_selesai)->format('d M Y') }}**.
                Edit status dengan hati-hati!
            </p>
        </div>
    @endif
    
    <form action="{{ route('pegawai.update', $pegawai->id) }}" method="POST">
        @csrf @method('PUT')
        
        {{-- Panggil error validation di sini (jika ada) --}}
        @if ($errors->any())
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <strong>Waduh, ada error nih:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-group">
            <label>Nama Lengkap</label>
            <input name="nama" value="{{ old('nama', $pegawai->nama) }}" required>
        </div>
        <div class="form-group">
            <label>Username (login)</label>
            {{-- Menggunakan null coalescing operator untuk jaga-jaga user tidak ada --}}
            <input name="username" value="{{ old('username', optional($pegawai->user)->username) }}" required> 
        </div>
        <div class="form-group">
            <label>Password Baru (kosongkan jika tidak diubah)</label>
            <input type="password" name="password">
        </div>

        <div class="form-row">
            <div class="col-2 form-group">
                <label>Posisi</label>
                <select name="posisi" required>
                    {{-- Posisi dari form lo --}}
                    @php $posisiOptions = ['Barista', 'Kasir', 'Dapur', 'Waiter', 'Helper']; @endphp
                    @foreach($posisiOptions as $posisi)
                        <option value="{{ $posisi }}" {{ old('posisi', $pegawai->posisi) == $posisi ? 'selected' : '' }}>{{ $posisi }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-1 form-group">
                <label>Role User (Level Akses)</label>
                <select name="role" required>
                    {{-- Role dari controller (manager, barista, kasir, waiter, chef, dll) --}}
                    {{-- Karena di controller lo ada variabel $roles, gue pakai itu jika ada --}}
                    @php 
                        $availableRoles = $roles ?? ['pegawai' => 'Pegawai', 'kasir' => 'Kasir', 'barista' => 'Barista']; 
                    @endphp
                    @foreach($availableRoles as $key => $label)
                        <option value="{{ $key }}" {{ old('role', optional($pegawai->user)->role) == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Status Pegawai</label>
            <select name="status" required>
                {{-- Status dari form lo --}}
                <option value="Aktif" {{ old('status', $pegawai->status)=='Aktif'?'selected':'' }}>Aktif</option>
                <option value="Cuti" {{ old('status', $pegawai->status)=='Cuti'?'selected':'' }}>Cuti</option>
                <option value="Nonaktif" {{ old('status', $pegawai->status)=='Nonaktif'?'selected':'' }}>Nonaktif</option>
            </select>
            {{-- Catatan: Status lo di form pakai huruf kecil, tapi di controller lo cek 'Cuti' dengan C kapital. Gue set jadi C kapital di view biar konsisten dengan controller. --}}
        </div>

        <div style="margin-top:20px">
            <a href="{{ route('pegawai.index') }}" class="btn secondary">Batal</a>
            <button class="btn" type="submit">Update Pegawai</button>
        </div>
    </form>
</div>
@endsection