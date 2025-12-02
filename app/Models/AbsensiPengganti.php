<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiPengganti extends Model
{
    protected $table = 'absensi_pengganti';
    
    protected $fillable = [
        'pengganti_id',
        'digantikan_id',
        'tanggal',
        'absensi_id',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function absensi(): BelongsTo
    {
        return $this->belongsTo(Absensi::class, 'absensi_id');
    }

    public function pengganti(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pengganti_id');
    }

    public function digantikan(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'digantikan_id');
    }
}