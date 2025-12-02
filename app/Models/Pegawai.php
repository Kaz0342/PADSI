<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Model
{
    protected $table = 'pegawai';
    
    // FIX 1: Mengganti 'posisi' menjadi 'jabatan' dan menambahkan 'status' ke fillable
    protected $fillable = ['user_id', 'nama', 'jabatan', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function absensis(): HasMany // FIX 2: Ubah nama method agar sesuai Controller (plural)
    {
        return $this->hasMany(Absensi::class, 'pegawai_id');
    }

    public function cutis(): HasMany // FIX 3: Ubah nama method agar sesuai Controller (plural)
    {
        return $this->hasMany(Cuti::class, 'pegawai_id');
    }
    
    // Relasi lainnya (dibiarkan singular)
    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'pegawai_id');
    }

    public function absensiPenggantiLogs(): HasMany
    {
        return $this->hasMany(AbsensiPengganti::class, 'pengganti_id');
    }
}