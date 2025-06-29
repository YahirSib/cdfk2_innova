<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvMovimientoTable extends Migration
{
    public function up()
    {
        Schema::create('inv_movimiento', function (Blueprint $table) {
            $table->increments('id_movimiento');
            $table->string('tipo_mov', 45);
            $table->string('tipo_doc', 45);
            $table->dateTime('fecha_ingreso');
            $table->string('tapicero', 45)->nullable();
            $table->string('cacastero', 45)->nullable();
            $table->decimal('total', 19, 4)->default(0.0000);
            $table->string('comentario', 45)->nullable();
            $table->string('estado', 45);
            $table->integer('correlativo');
            $table->dateTime('fecha_impresion')->nullable();
            $table->dateTime('fecha_anulacion')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inv_movimiento');
    }
}
