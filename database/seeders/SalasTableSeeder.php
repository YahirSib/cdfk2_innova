<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalasTableSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('es_SV');

        foreach (range(1, 10) as $i) {
            DB::table('salas')->insert([
                'codigo' => strtoupper($faker->bothify('SAL-###??')),
                'nombre' => ucfirst($faker->word),
                'descripcion' => $faker->optional()->sentence(4),
                'costo_cacastero' => $faker->randomFloat(2, 100, 1000),
                'costo_tapicero' => $faker->randomFloat(2, 100, 1000),
                'existencia' => 0,
                'estado' => $faker->numberBetween(1, 2), // 0: inactivo, 1: activo
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

