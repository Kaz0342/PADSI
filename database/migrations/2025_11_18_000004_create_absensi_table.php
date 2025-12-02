<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pegawai_id');
            $table->date('tanggal');
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();

            $table->decimal('lokasi_lat', 10, 8)->nullable();
            $table->decimal('lokasi_long', 11, 8)->nullable();
            $table->string('location_info')->nullable();

            $table->enum('status_kehadiran', [
                'hadir', 'terlambat', 'alpha', 'sakit', 'izin'
            ])->default('hadir');

            $table->text('catatan')->nullable();

            $table->unsignedBigInteger('absensi_pengganti_id')->nullable();

            $table->timestamps();

            $table->foreign('pegawai_id')->references('id')->on('pegawai')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down() {
        Schema::dropIfExists('absensi');
    }
};
