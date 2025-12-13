<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cuti extends Model
{
    use HasFactory;

    protected $table = 'cuti';

    // FIX: Gue balikin 'jenis' dan 'alasan' biar sinkron sama Controller yang udah kita buat.
    // Kode bawah lo pake 'keterangan', tapi di Controller lo pakainya 'alasan'. Gue ikutin Controller biar ga error.
    protected $fillable = [
        'pegawai_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis',
        'alasan',
        'status',
    ];

    // Fitur bagus dari kode bawah lo, gue pertahanin biar tanggalnya otomatis jadi Carbon object
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}