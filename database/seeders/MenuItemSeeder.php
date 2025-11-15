<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $menuItems = [
            ['name' => 'Hamburguesa Clásica', 'description' => 'Hamburguesa con carne, queso y condimentos', 'price' => 8.50],
            ['name' => 'Combo Hamburguesa + Soda', 'description' => 'Hamburguesa clásica con bebida', 'price' => 10.50],
            ['name' => 'Hot Dog Especial', 'description' => 'Hot dog con condimentos variados', 'price' => 5.75],
            ['name' => 'Alitas de Pollo (6 pcs)', 'description' => 'Alitas de pollo fritas', 'price' => 12.00],
            ['name' => 'Papas Fritas Medianas', 'description' => 'Porción mediana de papas fritas', 'price' => 3.50],
            ['name' => 'Combo Hot Dog + Papas + Soda', 'description' => 'Hot dog con papas y bebida', 'price' => 9.75],
        ];

        foreach ($menuItems as $item) {
            MenuItem::create($item);
        }
    }
}