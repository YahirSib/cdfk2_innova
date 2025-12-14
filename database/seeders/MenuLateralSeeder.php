<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuLateralSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('menu_lateral')->insert([
            ['id_menu' => 1, 'nombre' => 'Mantenimientos', 'ruta' => null, 'padre' => null, 'icono' => 'bx bxs-layer', 'ordenamiento' => 1],
            ['id_menu' => 2, 'nombre' => 'Trabajadores', 'ruta' => 'trabajadores.index', 'padre' => 1, 'icono' => null, 'ordenamiento' => 3],
            ['id_menu' => 3, 'nombre' => 'Piezas', 'ruta' => 'piezas.index', 'padre' => 1, 'icono' => null, 'ordenamiento' => 4],
            ['id_menu' => 4, 'nombre' => 'Salas', 'ruta' => 'salas.index', 'padre' => 1, 'icono' => null, 'ordenamiento' => 5],
            ['id_menu' => 5, 'nombre' => 'Movimientos', 'ruta' => null, 'padre' => null, 'icono' => 'bx bxs-box', 'ordenamiento' => 2],
            ['id_menu' => 6, 'nombre' => 'Nota de Pieza', 'ruta' => 'nota-pieza.index', 'padre' => 5, 'icono' => null, 'ordenamiento' => null],
            ['id_menu' => 7, 'nombre' => 'Reportes', 'ruta' => null, 'padre' => null, 'icono' => 'bxs-file', 'ordenamiento' => 3],
            ['id_menu' => 8, 'nombre' => 'Perfiles', 'ruta' => 'perfil.index', 'padre' => 1, 'icono' => null, 'ordenamiento' => 1],
            ['id_menu' => 9, 'nombre' => 'Usuarios', 'ruta' => 'usuarios.index', 'padre' => 1, 'icono' => null, 'ordenamiento' => 2],
            ['id_menu' => 10, 'nombre' => 'Agrupación Sala', 'ruta' => 'agrupacion-sala.index', 'padre' => 5, 'icono' => null, 'ordenamiento' => null],
            ['id_menu' => 11, 'nombre' => 'Traslado Tapiceria', 'ruta' => 'traslado-tapiceria.index', 'padre' => 5, 'icono' => null, 'ordenamiento' => null],
        ]);
    }
}

