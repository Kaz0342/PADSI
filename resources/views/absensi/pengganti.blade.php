@extends('layouts.app')

@section('title', 'Absensi Pengganti')

@section('content')

<style>
    /* --- PAGE STYLING KHUSUS HALAMAN INI --- */
    .replacement-wrapper {
        min-height: 80vh; /* Biar posisinya agak tengah vertikal */
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .replacement-card {
        background: white;
        border-radius: 16px;
        width: 100%;
        max-width: 480px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        overflow: hidden; /* Biar header nggak bocor radiusnya */
        text-align: center;
    }

    /* HEADER BIRU */
    .card-header-blue {
        background: #3b82f6; /* Warna biru modern (Tailwind Blue-500) */
        color: white;
        padding: 30px 20px;
    }

    .header-icon-circle {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px auto;
        font-size: 28px;
    }

    .header-title {
        font-size: 20px;
        font-weight: 700;
        margin: 0;
    }

    .header-subtitle {
        font-size: 14px;
        opacity: 0.9;
        margin-top: 5px;
        font-weight: 400;
    }

    /* BODY CONTENT */
    .card-body {
        padding: 30px;
        text-align: left; /* Reset text align buat form */
    }

    /* ALERT INFO */
    .info-alert {
        background: #eff6ff; /* Biru sangat muda */
        border: 1px solid #dbeafe;
        color: #1e40af;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 13px;
        line-height: 1.5;
        display: flex;
        gap: 10px;
        align-items: start;
        margin-bottom: 25px;
    }

    /* FORM ELEMENTS */
    label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 8px;
    }

    select, textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        color: #1f2937;
        background-color: #f9fafb;
        transition: all 0.2s;
        margin-bottom: 20px;
    }

    select:focus, textarea:focus {
        border-color: #3b82f6;
        outline: none;
        background-color: white;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* BUTTON GROUP */
    .btn-group-action {
        display: flex;
        gap: 12px;
        margin-top: 10px;
    }

    .btn-cancel {
        flex: 1;
        background: #f3f4f6;
        color: #4b5563;
        border: none;
        padding: 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        transition: background 0.2s;
    }
    .btn-cancel:hover { background: #e5e7eb; }

    .btn-confirm {
        flex: 1;
        background: #60a5fa; /* Biru agak muda dikit biar friendly */
        color: white;
        border: none;
        padding: 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-confirm:hover { background: #3b82f6; }

</style>

<div class="replacement-wrapper">
    <div class="replacement-card">
        
        {{-- HEADER --}}
        <div class="card-header-blue">
            <div class="header-icon-circle">
                <i class="fa-solid fa-user-group"></i>
            </div>
            <div class="header-title">Anda Pengganti Hari Ini</div>
            <div class="header-subtitle">Silakan pilih karyawan yang Anda gantikan</div>
        </div>

        {{-- BODY --}}
        <div class="card-body">
            
            {{-- INFO ALERT --}}
            <div class="info-alert">
                <i class="fa-solid fa-circle-info" style="font-size: 16px; margin-top: 2px;"></i>
                <div>
                    Hari ini Anda tidak terjadwal, tetapi sistem mendeteksi kehadiran Anda sebagai pengganti.
                </div>
            </div>

            <form action="{{ route('absensi.pengganti.store') }}" method="POST">
                @csrf

                {{-- INPUT PENGGANTI --}}
                <label for="menggantikan_id">Menggantikan Karyawan</label>
                <select name="menggantikan_id" id="menggantikan_id" required>
                    <option value="">Pilih karyawan...</option>
                    @forelse ($eligible ?? [] as $k)
                        <option value="{{ $k->id }}">{{ $k->name }} ({{ ucfirst($k->role) }})</option>
                    @empty
                        <option value="" disabled>Tidak ada data karyawan lain</option>
                    @endforelse
                </select>

                {{-- KETERANGAN (Opsional - Gue simpen tapi style-nya rapi) --}}
                <label for="keterangan">Keterangan (Opsional)</label>
                <textarea name="keterangan" rows="2" placeholder="Alasan penggantian..."></textarea>

                {{-- BUTTONS --}}
                <div class="btn-group-action">
                    <a href="{{ route('dashboard') }}" class="btn-cancel">Batal</a>
                    <button type="submit" class="btn-confirm">Konfirmasi Penggantian</button>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection