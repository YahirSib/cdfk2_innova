<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePiezasTable extends Migration
{
    public function up(): void
    {
        Schema::create('piezas', function (Blueprint $table) {
            $table->id('id_pieza');
            $table->string('codigo')->nullable();
            $table->string('nombre');
            $table->string('descripcion');
            $table->decimal('costo_cacastero', 19, 3);
            $table->decimal('costo_tapicero', 19, 3);
            $table->integer('existencia')->default(0);
            $table->integer('exitencia_traslado')->default(0);
            $table->integer('exitencia_tapizado')->default(0);
            $table->string('estado');
            $table->integer('individual')->default(0);
            $table->timestamps(); // Opcional si quieres mantener created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piezas');
    }
}
