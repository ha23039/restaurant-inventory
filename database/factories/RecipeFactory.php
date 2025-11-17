<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecipeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'menu_item_id' => MenuItem::factory(),
            'product_id' => Product::factory(),
            'quantity_needed' => fake()->randomFloat(3, 0.05, 1),
            'unit' => fake()->randomElement(['kg', 'lt', 'pcs', 'g', 'ml']),
        ];
    }
}
