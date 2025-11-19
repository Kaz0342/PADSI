<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'pegawai_id',
        'tanggal',
        'check_in_at',
        'check_out_at',
        'lokasi_lat',
        'lokasi_long',
        'location_info',
        'status_kehadiran',
        'catatan',
        'absensi_pengganti_id'
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function penggantiLog()
    {
        return $this->belongsTo(AbsensiPengganti::class, 'absensi_pengganti_id');
    }
}
