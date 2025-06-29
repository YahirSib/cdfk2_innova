<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrabajadoresTableSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('es_SV');

        foreach (range(1, 10) as $i) {
            DB::table('trabajadores')->insert([
                'nombre1' => $faker->firstName,
                'nombre2' => rand(0, 1) ? $faker->firstName : '',
                'apellido1' => $faker->lastName,
                'apellido2' => rand(0, 1) ? $faker->lastName : '',
                'edad' => rand(18, 60),
                'tipo' => rand(1, 2),
                'dui' => $faker->unique()->numerify('########-#'),
                'telefono' => $faker->phoneNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

