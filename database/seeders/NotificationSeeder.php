<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::where('role', 'admin')->first();
        $cajeroUser = User::where('role', 'cajero')->first();

        if (!$adminUser && !$cajeroUser) {
            $this->command->warn('No users found to create notifications.');
            return;
        }

        $users = collect([$adminUser, $cajeroUser])->filter();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'warning',
                'title' => 'Stock Bajo',
                'message' => 'El producto "Pollo" tiene stock bajo (5 kg). Mínimo requerido: 10 kg',
                'read' => false,
                'created_at' => now()->subMinutes(5),
            ]);

            Notification::create([
                'user_id' => $user->id,
                'type' => 'success',
                'title' => 'Venta Completada',
                'message' => 'Nueva venta de $1,250.00 registrada',
                'read' => false,
                'created_at' => now()->subMinutes(15),
            ]);

            Notification::create([
                'user_id' => $user->id,
                'type' => 'info',
                'title' => 'Nueva Devolución',
                'message' => 'Se procesó una devolución de $350.00',
                'read' => true,
                'created_at' => now()->subHour(),
            ]);

            Notification::create([
                'user_id' => $user->id,
                'type' => 'warning',
                'title' => 'Producto por Vencer',
                'message' => 'El producto "Leche" vence el 10/12/2025',
                'read' => false,
                'created_at' => now()->subMinutes(30),
            ]);
        }

        $this->command->info('Created test notifications for users.');
    }
}
