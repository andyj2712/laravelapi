<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Es mejor usar el modelo

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Borra usuarios existentes para empezar de cero
        User::truncate();

        User::create([
            'usuario' => 'admin',
            'password' => Hash::make('password123'), 
            'role' => 'administrador',
        ]);

        User::create([
            'usuario' => 'contador',
            'password' => Hash::make('contador123'),
            'role' => 'contador',
        ]);

        User::create([
        'usuario' => 'steven',
        'password' => Hash::make('steven321'),
        'role' => 'administrador',
        ]);
    }
}