<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PegawaiTanggalKerja extends Model
{
    protected $table = 'pegawai_tanggal_kerja';
    protected $fillable = ['pegawai_id', 'tanggal_kerja_id'];
}
