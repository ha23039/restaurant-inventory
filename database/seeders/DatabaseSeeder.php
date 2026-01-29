<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            MenuItemSeeder::class,
            RecipeSeeder::class,
            SimpleProductSeeder::class,
            SupplierSeeder::class,
            PupusaSeeder::class,
            PaymentMethodSeeder::class,
            PrinterSettingsSeeder::class,
            TicketSettingsSeeder::class,
            OrderSettingsSeeder::class,
        ]);
    }
}
