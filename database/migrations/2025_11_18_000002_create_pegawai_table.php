<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->unique(); 
            $table->string('nama', 200);
            $table->string('jabatan', 100)->nullable(); 
            
            $table->enum('status', ['Aktif', 'Cuti', 'Nonaktif'])->default('Aktif'); 
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};