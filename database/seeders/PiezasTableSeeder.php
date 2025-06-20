<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PiezasTableSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('es_SV');

        foreach (range(1, 10) as $i) {
            DB::table('piezas')->insert([
                'codigo' => strtoupper($faker->bothify('PZ-###??')),
                'nombre' => $faker->word,
                'descripcion' => $faker->sentence(4),
                'costo_cacastero' => $faker->randomFloat(3, 10, 500),
                'costo_tapicero' => $faker->randomFloat(3, 10, 500),
                'existencia' => 0,
                'estado' => $faker->numberBetween(1,2),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
