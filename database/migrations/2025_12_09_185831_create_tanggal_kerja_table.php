<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('tanggal_kerja', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('day_name', 20); // "Senin", "Selasa", ...
            $table->tinyInteger('is_open')->default(1); // 1 = buka, 0 = tutup (global)
            $table->timestamps();
        });

        Schema::create('pegawai_tanggal_kerja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pegawai_id');
            $table->unsignedBigInteger('tanggal_kerja_id');
            $table->timestamps();

            $table->unique(['pegawai_id', 'tanggal_kerja_id'], 'unique_pegawai_tanggal');
            $table->foreign('pegawai_id')->references('id')->on('pegawai')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('tanggal_kerja_id')->references('id')->on('tanggal_kerja')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down()
    {
        Schema::table('pegawai_tanggal_kerja', function (Blueprint $table) {
            $table->dropForeign(['pegawai_id']);
            $table->dropForeign(['tanggal_kerja_id']);
        });

        Schema::dropIfExists('pegawai_tanggal_kerja');
        Schema::dropIfExists('tanggal_kerja');
    }
};
