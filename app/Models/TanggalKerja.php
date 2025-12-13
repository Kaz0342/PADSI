<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class TanggalKerja extends Model
{
    protected $table = 'tanggal_kerja';
    protected $fillable = ['tanggal', 'day_name', 'is_open'];

    protected $casts = [
        'tanggal' => 'date',
        'is_open' => 'boolean'
    ];

    public function pegawais(): BelongsToMany
    {
        return $this->belongsToMany(Pegawai::class, 'pegawai_tanggal_kerja', 'tanggal_kerja_id', 'pegawai_id')
            ->withTimestamps();
    }

    // helper: buat day_name dari tanggal
    public static function dayNameFromDate($date)
    {
        return Carbon::parse($date)->locale('id')->isoFormat('dddd'); // e.g. "Senin"
    }
}
