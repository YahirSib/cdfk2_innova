<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalasPiezasTableSeeder extends Seeder
{
    public function run(): void
    {
        $salas_ids = DB::table('salas')->pluck('id_salas')->all();
        $piezas_ids = DB::table('piezas')->pluck('id_pieza')->all();

        foreach (range(1, 50) as $i) {
            DB::table('salas_piezas')->insert([
                'id_sala' => $salas_ids[array_rand($salas_ids)],
                'id_pieza' => $piezas_ids[array_rand($piezas_ids)],
                'cantidad' => rand(1, 3),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
