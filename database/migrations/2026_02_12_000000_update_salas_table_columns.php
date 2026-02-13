<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('salas', function (Blueprint $table) {
            $table->decimal('costo_cacastero', 19, 3)->change();
            $table->decimal('costo_tapicero', 19, 3)->change();
            $table->decimal('precio_venta', 19, 3)->nullable()->after('existencia_tapizado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salas', function (Blueprint $table) {
            $table->double('costo_cacastero')->change();
            $table->double('costo_tapicero')->change();
            $table->dropColumn('precio_venta');
        });
    }
};
