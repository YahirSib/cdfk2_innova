<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvDetallesTable extends Migration
{
    public function up()
    {
        Schema::create('inv_detalles', function (Blueprint $table) {
            $table->increments('id_detalle');
            $table->unsignedInteger('fk_movimiento');
            $table->unsignedInteger('fk_pieza')->nullable();
            $table->unsignedInteger('fk_sala')->nullable();
            $table->integer('unidades');
            $table->double('costo_unitario', 19, 4);
            $table->decimal('costo_total', 19, 4);

            // Clave foránea
            $table->foreign('fk_movimiento')
                  ->references('id_movimiento')
                  ->on('inv_movimiento')
                  ->onDelete('cascade');
            $table->integer('fk_detalle_prin')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inv_detalles');
    }
}
