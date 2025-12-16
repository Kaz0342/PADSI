<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $table = 'absensi';

    // FIX: Menggunakan timestamp karena check_in_at dan check_out_at adalah DATETIME/TIMESTAMP
    protected $casts = [
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'tanggal' => 'date',
    ];

    // Kolom yang bisa diisi (berdasarkan schema lo)
    protected $fillable = [
        'pegawai_id',
        'tanggal',
        'check_in_at',
        'check_out_at',
        'lokasi_lat',
        'lokasi_long',
        'location_info',
        'status_kehadiran',
        'tipe_sesi',
        'catatan',
        'absensi_pengganti_id',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function penggantiLog(): BelongsTo
    {
        return $this->belongsTo(AbsensiPengganti::class, 'absensi_pengganti_id');
    }
}