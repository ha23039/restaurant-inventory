<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Abarrotes', 'description' => 'Productos básicos y conservas', 'color' => '#f59e0b'],
            ['name' => 'Bebidas', 'description' => 'Sodas, jugos y bebidas', 'color' => '#3b82f6'],
            ['name' => 'Carnes', 'description' => 'Carnes y embutidos', 'color' => '#ef4444'],
            ['name' => 'Panadería', 'description' => 'Panes y productos horneados', 'color' => '#a855f7'],
            ['name' => 'Condimentos', 'description' => 'Especias y condimentos', 'color' => '#10b981'],
            ['name' => 'Lácteos', 'description' => 'Quesos, leche y derivados', 'color' => '#6366f1'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}