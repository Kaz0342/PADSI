<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsvImportsTable extends Migration
{
    public function up()
    {
        Schema::create('csv_imports', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('original_name')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('csv_imports');
    }
}
