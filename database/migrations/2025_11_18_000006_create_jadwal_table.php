<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pegawai_id');
            $table->unsignedBigInteger('shift_id');
            $table->date('tanggal');
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('pegawai_id')->references('id')->on('pegawai')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('jadwal');
    }
};
