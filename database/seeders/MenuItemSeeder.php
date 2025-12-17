<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $menuItems = [
            ['name' => 'Hamburguesa Clásica', 'description' => 'Hamburguesa con carne, queso y condimentos', 'price' => 5],
            ['name' => 'Combo Hamburguesa + Soda', 'description' => 'Hamburguesa clásica con bebida', 'price' => 6.50],
            ['name' => 'Hamburguesa Doble', 'description' => 'Hamburguesa con doble carne y queso', 'price' => 7.50],
            ['name' => 'Hot Dog Especial', 'description' => 'Hot dog con condimentos variados', 'price' => 3.5],
            ['name' => 'Alitas de Pollo (6 pcs)', 'description' => 'Alitas de pollo fritas', 'price' => 5],
            ['name' => 'Papas Fritas Medianas', 'description' => 'Porción mediana de papas fritas', 'price' => 3],
            ['name' => 'Combo Hot Dog + Papas + Soda', 'description' => 'Hot dog con papas y bebida', 'price' => 6],
        ];

        foreach ($menuItems as $item) {
            MenuItem::create($item);
        }
    }
}
