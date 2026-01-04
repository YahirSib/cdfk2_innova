<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalasTable extends Migration
{
    public function up(): void
    {
        Schema::create('salas', function (Blueprint $table) {
            $table->id('id_salas');
            $table->string('codigo', 45);
            $table->string('nombre', 45);
            $table->string('descripcion', 225)->nullable();
            $table->double('costo_cacastero');
            $table->double('costo_tapicero');
            $table->integer('existencia')->default(0);
            $table->integer('existencia_traslado')->default(0);
            $table->integer('existencia_tapizado')->default(0);
            $table->integer('estado');
            $table->timestamps(); // si deseas registrar created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salas');
    }
}
