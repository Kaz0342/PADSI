<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jadwal extends Model
{
    protected $table = 'jadwal';
    
    protected $fillable = [
        'pegawai_id',
        'shift_id',
        'tanggal',
        'keterangan',
        // Kolom 'mulai' dan 'selesai' harus diisi manual jika tidak pakai relasi Shift
        'mulai', 
        'selesai', 
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}