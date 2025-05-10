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
        Schema::create('products', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->charset('utf8mb4');
            $table->comment('Catalogo de piezas armados de salas');
            //CREACION DE LA TABLA
            $table->bigIncrements('id_producto');
            $table->string('nombre');
            $table->string('descripcion');
            $table->string('codigo')->nullable();
            $table->decimal('costo_cacastero', total: 19, places: 3);
            $table->decimal('costo_tapicero', total: 19, places: 3);
            $table->integer('existencia');
            $table->string('estado');
            $table->timestamps('');
        });

        Schema::create('salas', function(Blueprint $table){
            $table->engine('InnoDB');
            $table->charset('utf8mb4');
            $table->comment('Armado de Salas');
            //CREACION DE LA TABLA
            $table->bigIncrements('id_sala');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->decimal('costo', total: 19, places: 3);
            $table->decimal('precio_mayoreo', total: 19, places: 3);
            $table->decimal('precio_venta', total:19, places: 3);
            $table->string('estado');
            $table->timestamps('');
            $table->foreignId('user_id')->constrained(
                table: 'users', indexName: 'fk_user_id_sala'
            )->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('salas_detalles', function(Blueprint $table){
            $table->engine('InnoDB');
            $table->charset('utf8mb4');
            $table->comment('Detalle del armado de sala');
            //CREACION DE LA TABLA 
            $table->bigIncrements('id_sala_detalle');
            $table->foreignId('sala_id')->constrained('salas', 'id_sala');
            $table->foreignId('producto_id')->constrained('products', 'id_producto'); 
            $table->integer('cantidad');
            $table->timestamps('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('salas');
        Schema::dropIfExists('salas_detalles');
    }
};
