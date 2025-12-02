<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {

        // 1. Buat tabel TANPA FK ke absensi dulu
        Schema::create('absensi_pengganti', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('pengganti_id');
            $table->unsignedBigInteger('digantikan_id');

            $table->date('tanggal');

            // FK ke absensi bakal ditambahkan SETELAH tabel absensi dibuat
            $table->unsignedBigInteger('absensi_id')->nullable();

            $table->text('keterangan')->nullable();
            $table->timestamps();

            // FK aman (karena pegawai sudah ada)
            $table->foreign('pengganti_id')->references('id')->on('pegawai')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('digantikan_id')->references('id')->on('pegawai')->cascadeOnDelete()->cascadeOnUpdate();
        });

        // 2. Tambahkan FK ke absensi SETELAH tabel absensi dibuat
        //    Ini memutus circular dependency, TANPA bikin migration tambahan
        Schema::table('absensi_pengganti', function (Blueprint $table) {
            $table->foreign('absensi_id')
                  ->references('id')->on('absensi')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
        });
    }

    public function down() {
        Schema::dropIfExists('absensi_pengganti');
    }
};
