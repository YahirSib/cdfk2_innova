<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerfilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('perfil')->insert([
            [
                'id' => 1,
                'nombre' => 'JEFA ADMINISTRADORA',
                'estado' => '1',
                'created_at' => Carbon::create(2025, 6, 23, 2, 16, 43),
                'updated_at' => Carbon::create(2025, 6, 23, 12, 41, 7)
            ],
            [
                'id' => 3,
                'nombre' => 'ADMINISTRADOR',
                'estado' => '1',
                'created_at' => Carbon::create(2025, 6, 23, 12, 41, 15),
                'updated_at' => Carbon::create(2025, 6, 26, 10, 6, 21)
            ]
        ]);
    }
}