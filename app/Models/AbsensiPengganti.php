<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiPengganti extends Model
{
    protected $table = 'absensi_pengganti';
    protected $fillable = ['pengganti_id','digantikan_id','tanggal','absensi_id','keterangan'];

    public function pengganti()
    {
        return $this->belongsTo(Pegawai::class,'pengganti_id');
    }

    public function digantikan()
    {
        return $this->belongsTo(Pegawai::class,'digantikan_id');
    }

    public function absensi()
    {
        return $this->belongsTo(Absensi::class,'absensi_id');
    }
}
