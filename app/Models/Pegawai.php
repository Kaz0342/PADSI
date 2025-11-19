<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai';
    protected $fillable = ['user_id','nama','posisi','status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'pegawai_id');
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'pegawai_id');
    }

    public function absensiPenggantiLogs()
    {
        return $this->hasMany(AbsensiPengganti::class, 'pengganti_id');
    }

    public function cuti()
    {
        return $this->hasMany(Cuti::class, 'pegawai_id');
    }
}
