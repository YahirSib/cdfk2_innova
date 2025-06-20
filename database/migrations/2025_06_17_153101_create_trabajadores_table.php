<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrabajadoresTable extends Migration
{
    public function up()
    {
        Schema::create('trabajadores', function (Blueprint $table) {
            $table->id('id_trabajador');
            $table->string('nombre1', 45);
            $table->string('nombre2', 45)->nullable();
            $table->string('apellido1', 45);
            $table->string('apellido2', 45)->nullable();
            $table->integer('edad');
            $table->integer('tipo'); // 1 o 2
            $table->string('dui', 45)->nullable();
            $table->string('telefono', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trabajadores');
    }
}