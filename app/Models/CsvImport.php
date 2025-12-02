<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CsvImport extends Model
{
    use HasFactory;

    protected $table = 'csv_imports'; // ubah sesuai nama tabel kalau beda
    protected $fillable = [
        'filename',
        'uploaded_by',
        'status',      // pending/processed
        'rows_count',
        'meta',        // json optional
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function rows()
    {
        return $this->hasMany(CsvRow::class, 'import_id');
    }
}
