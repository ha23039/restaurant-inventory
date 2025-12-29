<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario del sistema para órdenes automáticas del menú digital
        User::firstOrCreate(
            ['email' => 'sistema@menudigital.local'],
            [
                'name' => 'Sistema - Menú Digital',
                'password' => Hash::make('sistema-no-login-' . bin2hex(random_bytes(16))),
                'role' => 'cajero', // Rol necesario para poder crear ventas
                'is_active' => false, // Desactivado para que no puedan hacer login
            ]
        );
    }
}
