<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalasPiezaTable extends Migration
{
    public function up(): void
    {
        Schema::create('salas_piezas', function (Blueprint $table) {
            $table->id('id_relacion');
            $table->unsignedBigInteger('id_sala');
            $table->unsignedBigInteger('id_pieza');
            $table->integer('cantidad')->default(1);

            $table->foreign('id_sala')->references('id_salas')->on('salas')->onDelete('restrict');
            $table->foreign('id_pieza')->references('id_pieza')->on('piezas')->onDelete('restrict');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salas_piezas');
    }
}
