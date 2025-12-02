<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsvRowsTable extends Migration
{
    public function up()
    {
        Schema::create('csv_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('csv_import_id')->constrained('csv_imports')->cascadeOnDelete();
            $table->string('nama')->nullable();
            $table->timestamp('transaction_at')->nullable();
            $table->string('raw_line')->nullable(); // optional: simpan baris mentah
            $table->boolean('approved')->default(false); // owner approve
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('csv_rows');
    }
}
