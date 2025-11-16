<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'unit_type' => fake()->randomElement(['kg', 'lt', 'pcs', 'g', 'ml']),
            'current_stock' => fake()->randomFloat(2, 10, 100),
            'min_stock' => fake()->randomFloat(2, 5, 20),
            'max_stock' => fake()->randomFloat(2, 100, 200),
            'unit_cost' => fake()->randomFloat(2, 10, 100),
            'expiry_date' => fake()->optional()->dateTimeBetween('now', '+6 months'),
            'is_active' => true,
        ];
    }
}
