<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $table = 'shifts';
    
    // Kolom yang bisa diisi
    protected $fillable = [
        'nama',
        'start_time',
        'end_time',
    ];

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'shift_id');
    }
}