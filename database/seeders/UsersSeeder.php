<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'id' => 3,
                'name' => 'YAHIR SIBRIAN',
                'email' => 'yahirstewart16@gmail.com',
                'email_verified_at' => null,
                'password' => Hash::make('password'), // Note: Original hash preserved below
                'remember_token' => null,
                'created_at' => Carbon::create(2025, 6, 26, 10, 24, 35),
                'updated_at' => Carbon::create(2025, 6, 29, 3, 53, 53),
                'perfil_id' => 1
            ]
        ]);

        // If you need to preserve the exact original password hash:
        DB::table('users')
            ->where('id', 3)
            ->update(['password' => '$2y$12$zYFjwFWda/PseLulkJ/chuvBy4H1fZ4hZbjLWQEVxhfuKkDroYj5S']);
    }
}