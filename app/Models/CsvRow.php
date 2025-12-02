<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CsvRow extends Model
{
    use HasFactory;

    protected $table = 'csv_rows'; // Sesuaikan di migrations
    protected $fillable = [
        'import_id',
        'raw',
        'parsed',
        'approved', // Tambahkan field untuk approval
        'status',
    ];

    protected $casts = [
        'raw' => 'array',
        'parsed' => 'array',
        'approved' => 'boolean', // Ini penting buat query di rekap dashboard owner
    ];

    public function import()
    {
        return $this->belongsTo(CsvImport::class, 'import_id');
    }
}