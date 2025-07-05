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
        Schema::create('permisos_menu', function (Blueprint $table) {
            $table->bigIncrements('id_permiso');
            $table->bigInteger('id_menu')->unsigned();
            $table->bigInteger('id_perfil')->unsigned();
            $table->bigInteger('estado')->default(1);
            
            $table->foreign('id_perfil')->references('id')->on('perfil');
            $table->foreign('id_menu')->references('id_menu')->on('menu_lateral');
            
            $table->index(['id_menu', 'id_perfil']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permisos_menu');
    }
};
