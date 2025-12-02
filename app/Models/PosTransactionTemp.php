<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PosTransactionTemp extends Model {
    // Dipakai PosImportController untuk menampung data CSV sementara
    protected $table = 'pos_transactions_temp';
    protected $guarded = [];
    protected $casts = ['timestamp' => 'datetime', 'tanggal' => 'date'];
}