<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface MenuItemRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get available menu items (with stock)
     */
    public function getAvailableItems(): Collection;

    /**
     * Get menu items by category
     */
    public function getByCategory(int $categoryId): Collection;

    /**
     * Get menu items with recipes
     */
    public function getAllWithRecipes(): Collection;

    /**
     * Calculate available quantity for a menu item based on stock
     */
    public function calculateAvailableQuantity(int $id): int;

    /**
     * Check if menu item has sufficient ingredients
     */
    public function hasSufficientIngredients(int $id, int $quantity): bool;

    /**
     * Get menu items with low stock warning
     */
    public function getItemsWithLowStock(int $threshold = 5): Collection;

    /**
     * Search menu items by name
     */
    public function search(string $query): Collection;
}
