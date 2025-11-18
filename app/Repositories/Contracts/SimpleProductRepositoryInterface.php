<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface SimpleProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get available simple products (with stock)
     */
    public function getAvailableProducts(): Collection;

    /**
     * Get simple products by category
     */
    public function getByCategory(int $categoryId): Collection;

    /**
     * Get all simple products with their base product
     */
    public function getAllWithProduct(): Collection;

    /**
     * Calculate available quantity for a simple product
     */
    public function calculateAvailableQuantity(int $id): int;

    /**
     * Check if simple product has sufficient stock
     */
    public function hasSufficientStock(int $id, int $quantity): bool;

    /**
     * Get simple products with low stock
     */
    public function getProductsWithLowStock(int $threshold = 5): Collection;

    /**
     * Search simple products by name
     */
    public function search(string $query): Collection;

    /**
     * Update profit margin
     */
    public function updateProfitMargin(int $id, float $salePrice): bool;
}
