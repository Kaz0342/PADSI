<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawai';

    protected $fillable = [
        'user_id',
        'nama',
        'jabatan',
        'status'
    ];

    /* ===================== RELASI USER ===================== */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* ===================== ABSENSI ===================== */
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'pegawai_id');
    }

    /* ===================== CUTI ===================== */
    public function cutis()
    {
        return $this->hasMany(Cuti::class)
            ->orderBy('id', 'desc');
    }

    /* ===================== JADWAL SHIFT (legacy) ===================== */
    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'pegawai_id');
    }

    /* ===================== ABSENSI PENGGANTI ===================== */
    public function absensiPenggantiLogs(): HasMany
    {
        return $this->hasMany(AbsensiPengganti::class, 'pengganti_id');
    }

    public function absensiPenggantiOriginal(): HasMany
    {
        return $this->hasMany(AbsensiPengganti::class, 'digantikan_id');
    }

    /* ===================== TANGGAL KERJA BARU (GANTT CHART) ===================== */
    public function tanggalKerjas()
    {
        return $this->belongsToMany(
            TanggalKerja::class,
            'pegawai_tanggal_kerja',
            'pegawai_id',
            'tanggal_kerja_id'
        )->withTimestamps();
    }
}
