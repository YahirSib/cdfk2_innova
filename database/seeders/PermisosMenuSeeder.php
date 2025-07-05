<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisosMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('permisos_menu')->insert([
            [
                'id_permiso' => 1,
                'id_menu' => 1,
                'id_perfil' => 1,
                'estado' => 1
            ],
            [
                'id_permiso' => 2,
                'id_menu' => 2,
                'id_perfil' => 1,
                'estado' => 1
            ],
            [
                'id_permiso' => 3,
                'id_menu' => 4,
                'id_perfil' => 1,
                'estado' => 1
            ],
            [
                'id_permiso' => 4,
                'id_menu' => 3,
                'id_perfil' => 1,
                'estado' => 1
            ],
            [
                'id_permiso' => 5,
                'id_menu' => 5,
                'id_perfil' => 1,
                'estado' => 1
            ],
            [
                'id_permiso' => 6,
                'id_menu' => 6,
                'id_perfil' => 1,
                'estado' => 1
            ],
            [
                'id_permiso' => 7,
                'id_menu' => 8,
                'id_perfil' => 1,
                'estado' => 1
            ],
            [
                'id_permiso' => 8,
                'id_menu' => 9,
                'id_perfil' => 1,
                'estado' => 1
            ],
        ]);
    }
}