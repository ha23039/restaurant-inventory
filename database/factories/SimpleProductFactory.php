<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class SimpleProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'sale_price' => fake()->randomFloat(2, 20, 100),
            'cost_per_unit' => fake()->randomFloat(3, 0.1, 2),
            'is_available' => true,
            'category' => fake()->randomElement(['Bebidas', 'Postres', 'Extras', 'Botanas']),
        ];
    }
}
