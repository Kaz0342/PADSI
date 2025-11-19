<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('pos_integration', function (Blueprint $table) {
            $table->id('id_sync');
            $table->string('nama_file')->nullable();
            $table->date('tgl_sync')->nullable();
            $table->string('status')->default('berhasil');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('pos_integration');
    }
};
