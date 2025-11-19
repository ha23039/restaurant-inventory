<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get products with low stock (below minimum)
     */
    public function getLowStockProducts(): Collection;

    /**
     * Get products expiring soon
     */
    public function getExpiringSoonProducts(int $days = 7): Collection;

    /**
     * Get expired products
     */
    public function getExpiredProducts(): Collection;

    /**
     * Get products by category
     */
    public function getByCategory(int $categoryId): Collection;

    /**
     * Search products by name
     */
    public function search(string $query): Collection;

    /**
     * Get active products only
     */
    public function getActiveProducts(): Collection;

    /**
     * Get products with their category relationship
     */
    public function getAllWithCategory(): Collection;

    /**
     * Update stock for a product
     */
    public function updateStock(int $id, float $quantity): bool;

    /**
     * Check if product has sufficient stock
     */
    public function hasSufficientStock(int $id, float $quantity): bool;
}
