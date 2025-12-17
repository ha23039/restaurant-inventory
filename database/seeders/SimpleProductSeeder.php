<?php

namespace Database\Seeders;

use App\Models\SimpleProduct;
use Illuminate\Database\Seeder;

class SimpleProductSeeder extends Seeder
{
    public function run(): void
    {
        $simpleProducts = [
            // Bebidas individuales
            [
                'product_id' => 6, // Coca Cola
                'name' => 'Coca Cola 355ml',
                'description' => 'Bebida gaseosa individual',
                'sale_price' => 1,
                'cost_per_unit' => 1,
                'category' => 'bebida',
                'is_available' => true,
            ],
            [
                'product_id' => 7, // Pepsi
                'name' => 'Pepsi 355ml',
                'description' => 'Bebida gaseosa individual',
                'sale_price' => 1,
                'cost_per_unit' => 1,
                'category' => 'bebida',
                'is_available' => true,
            ],
            [
                'product_id' => 8, // Agua
                'name' => 'Agua Embotellada',
                'description' => 'Agua purificada 500ml',
                'sale_price' => 0.50,
                'cost_per_unit' => 1,
                'category' => 'bebida',
                'is_available' => true,
            ],

            // Extras/Acompañantes
            [
                'product_id' => 12, // Queso Amarillo
                'name' => 'Queso Extra',
                'description' => 'Porción adicional de queso',
                'sale_price' => 0.50,
                'cost_per_unit' => 1,
                'category' => 'extra',
                'is_available' => true,
            ],
            [
                'product_id' => 1, // Pan de Hamburguesa
                'name' => 'Pan Extra',
                'description' => 'Pan de hamburguesa adicional',
                'sale_price' => 1.00,
                'cost_per_unit' => 1,
                'category' => 'extra',
                'is_available' => true,
            ],

            // Condimentos en porciones
            [
                'product_id' => 9, // Ketchup
                'name' => 'Porción Ketchup',
                'description' => 'Porción individual de ketchup (20g)',
                'sale_price' => 0.25,
                'cost_per_unit' => 20, // 20 gramos por porción
                'category' => 'condimento',
                'is_available' => true,
            ],
            [
                'product_id' => 10, // Mayonesa
                'name' => 'Porción Mayonesa',
                'description' => 'Porción individual de mayonesa (20g)',
                'sale_price' => 0.25,
                'cost_per_unit' => 20, // 20 gramos por porción
                'category' => 'condimento',
                'is_available' => true,
            ],
        ];

        foreach ($simpleProducts as $product) {
            SimpleProduct::create($product);
        }

        echo '✅ Se crearon '.count($simpleProducts)." productos simples\n";
    }
}
