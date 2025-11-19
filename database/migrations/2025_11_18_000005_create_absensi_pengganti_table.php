<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('absensi_pengganti', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengganti_id');   // pegawai yang hadir
            $table->unsignedBigInteger('digantikan_id');  // pegawai yang seharusnya masuk
            $table->date('tanggal');
            $table->unsignedBigInteger('absensi_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('pengganti_id')->references('id')->on('pegawai')->onDelete('cascade');
            $table->foreign('digantikan_id')->references('id')->on('pegawai')->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('absensi_pengganti');
    }
};
