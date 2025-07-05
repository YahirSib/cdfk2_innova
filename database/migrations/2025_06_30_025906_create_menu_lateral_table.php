<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menu_lateral', function (Blueprint $table) {
            $table->bigIncrements('id_menu');
            $table->string('nombre', 50);
            $table->string('ruta', 50)->nullable();
            $table->unsignedBigInteger('padre')->nullable();
            $table->string('icono', 50)->nullable();
            $table->unsignedBigInteger('ordenamiento')->nullable();

            $table->foreign('padre')->references('id_menu')->on('menu_lateral')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_lateral');
    }
};

