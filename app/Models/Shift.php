<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $table = 'shifts';
    protected $fillable = ['nama','start_time','end_time'];

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class, 'shift_id');
    }
}
