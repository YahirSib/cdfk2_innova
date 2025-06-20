<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tipo_doc', function (Blueprint $table) {
            $table->id('id_tipo'); // Equivalente a AUTO_INCREMENT PRIMARY KEY
            $table->string('nombre', 45)->nullable();
            $table->string('abreviacion', 45)->nullable();
            $table->timestamps(); // Laravel recomienda incluir timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_doc');
    }
};