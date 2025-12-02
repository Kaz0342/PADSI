<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cuti extends Model
{
    protected $table = 'cuti';
    
    // Kolom yang bisa diisi
    protected $fillable = [
        'pegawai_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'status', // Contoh: Disetujui, Ditolak, Pending
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}