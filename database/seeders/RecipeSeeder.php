<?php

namespace Database\Seeders;

use App\Models\Recipe;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        $recipes = [
            // HAMBURGUESA CLÁSICA (ID: 1)
            ['menu_item_id' => 1, 'product_id' => 1, 'quantity_needed' => 1, 'unit' => 'pcs'], // Pan hamburguesa
            ['menu_item_id' => 1, 'product_id' => 3, 'quantity_needed' => 1, 'unit' => 'pcs'], // Torta carne
            ['menu_item_id' => 1, 'product_id' => 12, 'quantity_needed' => 1, 'unit' => 'pcs'], // Queso
            ['menu_item_id' => 1, 'product_id' => 9, 'quantity_needed' => 15, 'unit' => 'g'], // Ketchup
            ['menu_item_id' => 1, 'product_id' => 10, 'quantity_needed' => 10, 'unit' => 'g'], // Mayonesa

            // COMBO HAMBURGUESA + SODA (ID: 2)
            ['menu_item_id' => 2, 'product_id' => 1, 'quantity_needed' => 1, 'unit' => 'pcs'], // Pan hamburguesa
            ['menu_item_id' => 2, 'product_id' => 3, 'quantity_needed' => 1, 'unit' => 'pcs'], // Torta carne
            ['menu_item_id' => 2, 'product_id' => 12, 'quantity_needed' => 1, 'unit' => 'pcs'], // Queso
            ['menu_item_id' => 2, 'product_id' => 9, 'quantity_needed' => 15, 'unit' => 'g'], // Ketchup
            ['menu_item_id' => 2, 'product_id' => 10, 'quantity_needed' => 10, 'unit' => 'g'], // Mayonesa
            ['menu_item_id' => 2, 'product_id' => 6, 'quantity_needed' => 1, 'unit' => 'pcs'], // Coca Cola

            // HOT DOG ESPECIAL (ID: 3)
            ['menu_item_id' => 3, 'product_id' => 2, 'quantity_needed' => 1, 'unit' => 'pcs'], // Pan hot dog
            ['menu_item_id' => 3, 'product_id' => 4, 'quantity_needed' => 1, 'unit' => 'pcs'], // Salchicha
            ['menu_item_id' => 3, 'product_id' => 9, 'quantity_needed' => 10, 'unit' => 'g'], // Ketchup
            ['menu_item_id' => 3, 'product_id' => 11, 'quantity_needed' => 8, 'unit' => 'g'], // Mostaza

            // ALITAS DE POLLO 6 PCS (ID: 4)
            ['menu_item_id' => 4, 'product_id' => 5, 'quantity_needed' => 0.350, 'unit' => 'kg'], // Pollo (350g)
            ['menu_item_id' => 4, 'product_id' => 14, 'quantity_needed' => 0.05, 'unit' => 'lt'], // Aceite para freír

            // PAPAS FRITAS MEDIANAS (ID: 5)
            ['menu_item_id' => 5, 'product_id' => 13, 'quantity_needed' => 0.150, 'unit' => 'kg'], // Papas (150g)
            ['menu_item_id' => 5, 'product_id' => 14, 'quantity_needed' => 0.02, 'unit' => 'lt'], // Aceite

            // COMBO HOT DOG + PAPAS + SODA (ID: 6)
            ['menu_item_id' => 6, 'product_id' => 2, 'quantity_needed' => 1, 'unit' => 'pcs'], // Pan hot dog
            ['menu_item_id' => 6, 'product_id' => 4, 'quantity_needed' => 1, 'unit' => 'pcs'], // Salchicha
            ['menu_item_id' => 6, 'product_id' => 9, 'quantity_needed' => 10, 'unit' => 'g'], // Ketchup
            ['menu_item_id' => 6, 'product_id' => 11, 'quantity_needed' => 8, 'unit' => 'g'], // Mostaza
            ['menu_item_id' => 6, 'product_id' => 13, 'quantity_needed' => 0.120, 'unit' => 'kg'], // Papas
            ['menu_item_id' => 6, 'product_id' => 14, 'quantity_needed' => 0.02, 'unit' => 'lt'], // Aceite
            ['menu_item_id' => 6, 'product_id' => 7, 'quantity_needed' => 1, 'unit' => 'pcs'], // Pepsi
        ];

        foreach ($recipes as $recipe) {
            Recipe::create($recipe);
        }
    }
}