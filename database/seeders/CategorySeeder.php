<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Abarrotes', 'description' => 'Granos, harinas, aceites y productos básicos', 'color' => '#f59e0b'],
            ['name' => 'Bebidas', 'description' => 'Sodas, jugos, agua y bebidas', 'color' => '#3b82f6'],
            ['name' => 'Carnes', 'description' => 'Res, cerdo, pollo y cortes', 'color' => '#ef4444'],
            ['name' => 'Panadería', 'description' => 'Panes, tortillas y productos horneados', 'color' => '#a855f7'],
            ['name' => 'Condimentos', 'description' => 'Salsas, especias y aderezos', 'color' => '#10b981'],
            ['name' => 'Lácteos', 'description' => 'Quesos, crema, leche y derivados', 'color' => '#6366f1'],
            ['name' => 'Verduras', 'description' => 'Vegetales frescos y hortalizas', 'color' => '#22c55e'],
            ['name' => 'Frutas', 'description' => 'Frutas frescas y congeladas', 'color' => '#f97316'],
            ['name' => 'Embutidos', 'description' => 'Salchichas, jamón, chorizo y derivados', 'color' => '#dc2626'],
            ['name' => 'Mariscos', 'description' => 'Pescados, camarones y mariscos', 'color' => '#0ea5e9'],
            ['name' => 'Desechables', 'description' => 'Platos, vasos, servilletas y empaques', 'color' => '#78716c'],
            ['name' => 'Limpieza', 'description' => 'Productos de limpieza e higiene', 'color' => '#06b6d4'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
