<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrador',
                'email' => 'admin@restaurant.com',
                'password' => Hash::make('password'),
                'role' => 'admin'
            ],
            [
                'name' => 'Chef Principal',
                'email' => 'chef@restaurant.com',
                'password' => Hash::make('password'),
                'role' => 'chef'
            ],
            [
                'name' => 'Almacenero',
                'email' => 'almacen@restaurant.com',
                'password' => Hash::make('password'),
                'role' => 'almacenero'
            ],
            [
                'name' => 'Cajero',
                'email' => 'cajero@restaurant.com',
                'password' => Hash::make('password'),
                'role' => 'cajero'
            ]
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}