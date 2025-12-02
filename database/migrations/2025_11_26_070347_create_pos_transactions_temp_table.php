<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('pos_transactions_temp', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pegawai_pos')->nullable();
            $table->string('nama_normalized')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('kedai')->nullable();
            $table->decimal('total', 12, 2)->nullable();
            $table->string('source_file')->nullable();
            $table->timestamps();
            $table->index(['tanggal','nama_normalized']);
        });
    }
    public function down() {
        Schema::dropIfExists('pos_transactions_temp');
    }
};
