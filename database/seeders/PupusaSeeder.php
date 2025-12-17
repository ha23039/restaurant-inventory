<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\MenuItem;
use App\Models\MenuItemVariant;
use App\Models\Recipe;

class PupusaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear categoria para ingredientes de pupusas
        $category = Category::firstOrCreate(
            ['name' => 'Ingredientes Pupusas'],
            ['description' => 'Ingredientes para preparacion de pupusas']
        );

        // 2. Crear productos de inventario (ingredientes)
        $masaMaiz = Product::create([
            'category_id' => $category->id,
            'name' => 'Masa de Maiz',
            'description' => 'Masa de maiz para pupusas',
            'unit_type' => 'kg',
            'current_stock' => 50.000,
            'min_stock' => 10.000,
            'max_stock' => 100.000,
            'unit_cost' => 1.50,
        ]);

        $masaArroz = Product::create([
            'category_id' => $category->id,
            'name' => 'Masa de Arroz',
            'description' => 'Masa de arroz para pupusas',
            'unit_type' => 'kg',
            'current_stock' => 30.000,
            'min_stock' => 10.000,
            'max_stock' => 80.000,
            'unit_cost' => 2.00,
        ]);

        $frijol = Product::create([
            'category_id' => $category->id,
            'name' => 'Frijol Molido',
            'description' => 'Frijol molido para relleno',
            'unit_type' => 'kg',
            'current_stock' => 25.000,
            'min_stock' => 5.000,
            'max_stock' => 50.000,
            'unit_cost' => 2.50,
        ]);

        $queso = Product::create([
            'category_id' => $category->id,
            'name' => 'Queso Duro',
            'description' => 'Queso duro rallado para pupusas',
            'unit_type' => 'kg',
            'current_stock' => 20.000,
            'min_stock' => 5.000,
            'max_stock' => 40.000,
            'unit_cost' => 4.00,
        ]);

        $revuelta = Product::create([
            'category_id' => $category->id,
            'name' => 'Revuelta (Chicharron)',
            'description' => 'Mezcla de chicharron, frijol y queso',
            'unit_type' => 'kg',
            'current_stock' => 15.000,
            'min_stock' => 5.000,
            'max_stock' => 30.000,
            'unit_cost' => 5.00,
        ]);

        $ayote = Product::create([
            'category_id' => $category->id,
            'name' => 'Ayote',
            'description' => 'Ayote cocido para relleno',
            'unit_type' => 'kg',
            'current_stock' => 10.000,
            'min_stock' => 3.000,
            'max_stock' => 20.000,
            'unit_cost' => 3.00,
        ]);

        $loroco = Product::create([
            'category_id' => $category->id,
            'name' => 'Loroco',
            'description' => 'Loroco para relleno',
            'unit_type' => 'kg',
            'current_stock' => 8.000,
            'min_stock' => 2.000,
            'max_stock' => 15.000,
            'unit_cost' => 6.00,
        ]);

        // 3. Crear menu item padre con variantes
        $pupusa = MenuItem::create([
            'name' => 'Pupusa',
            'description' => 'Pupusa tradicional salvadorena - Selecciona tu masa y relleno favorito',
            'price' => 0.00, // No se usa, cada variante tiene su precio
            'is_available' => true,
            'has_variants' => true,
        ]);

        // 4. Crear variantes de pupusas
        // Estructura: [masa, relleno, precio, ingredientes]
        $variants = [
            // Frijol con Queso - $0.50
            [
                'masa' => 'maiz',
                'masa_producto' => $masaMaiz,
                'relleno' => 'frijol_queso',
                'relleno_nombre' => 'Frijol con Queso',
                'precio' => 0.50,
                'ingredientes' => [
                    ['producto' => $frijol, 'cantidad' => 0.030],
                    ['producto' => $queso, 'cantidad' => 0.020],
                ],
            ],
            [
                'masa' => 'arroz',
                'masa_producto' => $masaArroz,
                'relleno' => 'frijol_queso',
                'relleno_nombre' => 'Frijol con Queso',
                'precio' => 0.50,
                'ingredientes' => [
                    ['producto' => $frijol, 'cantidad' => 0.030],
                    ['producto' => $queso, 'cantidad' => 0.020],
                ],
            ],
            // Revuelta - $1.00
            [
                'masa' => 'maiz',
                'masa_producto' => $masaMaiz,
                'relleno' => 'revuelta',
                'relleno_nombre' => 'Revuelta',
                'precio' => 1.00,
                'ingredientes' => [
                    ['producto' => $revuelta, 'cantidad' => 0.080],
                ],
            ],
            [
                'masa' => 'arroz',
                'masa_producto' => $masaArroz,
                'relleno' => 'revuelta',
                'relleno_nombre' => 'Revuelta',
                'precio' => 1.00,
                'ingredientes' => [
                    ['producto' => $revuelta, 'cantidad' => 0.080],
                ],
            ],
            // Ayote - $1.00
            [
                'masa' => 'maiz',
                'masa_producto' => $masaMaiz,
                'relleno' => 'ayote',
                'relleno_nombre' => 'Ayote',
                'precio' => 1.00,
                'ingredientes' => [
                    ['producto' => $ayote, 'cantidad' => 0.070],
                ],
            ],
            [
                'masa' => 'arroz',
                'masa_producto' => $masaArroz,
                'relleno' => 'ayote',
                'relleno_nombre' => 'Ayote',
                'precio' => 1.00,
                'ingredientes' => [
                    ['producto' => $ayote, 'cantidad' => 0.070],
                ],
            ],
            // Loroco - $1.00
            [
                'masa' => 'maiz',
                'masa_producto' => $masaMaiz,
                'relleno' => 'loroco',
                'relleno_nombre' => 'Loroco',
                'precio' => 1.00,
                'ingredientes' => [
                    ['producto' => $loroco, 'cantidad' => 0.040],
                    ['producto' => $queso, 'cantidad' => 0.030],
                ],
            ],
            [
                'masa' => 'arroz',
                'masa_producto' => $masaArroz,
                'relleno' => 'loroco',
                'relleno_nombre' => 'Loroco',
                'precio' => 1.00,
                'ingredientes' => [
                    ['producto' => $loroco, 'cantidad' => 0.040],
                    ['producto' => $queso, 'cantidad' => 0.030],
                ],
            ],
            // Queso - $1.00
            [
                'masa' => 'maiz',
                'masa_producto' => $masaMaiz,
                'relleno' => 'queso',
                'relleno_nombre' => 'Queso',
                'precio' => 1.00,
                'ingredientes' => [
                    ['producto' => $queso, 'cantidad' => 0.060],
                ],
            ],
            [
                'masa' => 'arroz',
                'masa_producto' => $masaArroz,
                'relleno' => 'queso',
                'relleno_nombre' => 'Queso',
                'precio' => 1.00,
                'ingredientes' => [
                    ['producto' => $queso, 'cantidad' => 0.060],
                ],
            ],
        ];

        // 5. Crear cada variante con sus recetas
        $displayOrder = 0;
        foreach ($variants as $variantData) {
            $masaNombre = $variantData['masa'] === 'maiz' ? 'Maiz' : 'Arroz';
            $variantName = "{$masaNombre} - {$variantData['relleno_nombre']}";
            $variantSku = "PUP-" . strtoupper($variantData['masa']) . "-" . strtoupper($variantData['relleno']);

            // Crear variante
            $variant = MenuItemVariant::create([
                'menu_item_id' => $pupusa->id,
                'variant_name' => $variantName,
                'variant_sku' => $variantSku,
                'price' => $variantData['precio'],
                'attributes' => [
                    'masa' => $variantData['masa'],
                    'relleno' => $variantData['relleno'],
                ],
                'is_available' => true,
                'display_order' => $displayOrder++,
            ]);

            // Crear receta para la masa
            Recipe::create([
                'menu_item_variant_id' => $variant->id,
                'product_id' => $variantData['masa_producto']->id,
                'quantity_needed' => 0.120, // 120 gramos de masa por pupusa
                'unit' => 'kg',
            ]);

            // Crear recetas para los ingredientes del relleno
            foreach ($variantData['ingredientes'] as $ingrediente) {
                Recipe::create([
                    'menu_item_variant_id' => $variant->id,
                    'product_id' => $ingrediente['producto']->id,
                    'quantity_needed' => $ingrediente['cantidad'],
                    'unit' => 'kg',
                ]);
            }
        }

        $this->command->info('Pupusas creadas exitosamente con sus variantes y recetas!');
        $this->command->info("Total de variantes creadas: " . count($variants));
    }
}
