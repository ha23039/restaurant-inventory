<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Panadería
            ['category_id' => 4, 'name' => 'Pan de Hamburguesa', 'unit_type' => 'pcs', 'current_stock' => 50, 'min_stock' => 10, 'unit_cost' => 0.75],
            ['category_id' => 4, 'name' => 'Pan para Hot Dog', 'unit_type' => 'pcs', 'current_stock' => 30, 'min_stock' => 8, 'unit_cost' => 0.50],

            // Carnes
            ['category_id' => 3, 'name' => 'Torta de Carne', 'unit_type' => 'pcs', 'current_stock' => 40, 'min_stock' => 15, 'unit_cost' => 2.50],
            ['category_id' => 3, 'name' => 'Salchicha', 'unit_type' => 'pcs', 'current_stock' => 60, 'min_stock' => 20, 'unit_cost' => 1.25],
            ['category_id' => 3, 'name' => 'Pollo Deshuesado', 'unit_type' => 'kg', 'current_stock' => 5, 'min_stock' => 2, 'unit_cost' => 8.50],

            // Bebidas
            ['category_id' => 2, 'name' => 'Coca Cola 355ml', 'unit_type' => 'pcs', 'current_stock' => 100, 'min_stock' => 20, 'unit_cost' => 0.80],
            ['category_id' => 2, 'name' => 'Pepsi 355ml', 'unit_type' => 'pcs', 'current_stock' => 80, 'min_stock' => 15, 'unit_cost' => 0.75],
            ['category_id' => 2, 'name' => 'Agua Embotellada', 'unit_type' => 'pcs', 'current_stock' => 150, 'min_stock' => 30, 'unit_cost' => 0.35],

            // Condimentos
            ['category_id' => 5, 'name' => 'Ketchup', 'unit_type' => 'g', 'current_stock' => 2000, 'min_stock' => 500, 'unit_cost' => 0.01],
            ['category_id' => 5, 'name' => 'Mayonesa', 'unit_type' => 'g', 'current_stock' => 1500, 'min_stock' => 400, 'unit_cost' => 0.012],
            ['category_id' => 5, 'name' => 'Mostaza', 'unit_type' => 'g', 'current_stock' => 1000, 'min_stock' => 300, 'unit_cost' => 0.015],

            // Lácteos
            ['category_id' => 6, 'name' => 'Queso Amarillo', 'unit_type' => 'pcs', 'current_stock' => 200, 'min_stock' => 50, 'unit_cost' => 0.25],

            // Abarrotes
            ['category_id' => 1, 'name' => 'Papas Fritas Congeladas', 'unit_type' => 'kg', 'current_stock' => 10, 'min_stock' => 3, 'unit_cost' => 3.50],
            ['category_id' => 1, 'name' => 'Aceite Vegetal', 'unit_type' => 'lt', 'current_stock' => 8, 'min_stock' => 2, 'unit_cost' => 4.20],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
