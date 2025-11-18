<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getLowStockProducts(): Collection
    {
        return $this->model
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->where('is_active', true)
            ->with('category')
            ->get();
    }

    public function getExpiringSoonProducts(int $days = 7): Collection
    {
        return $this->model
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', now())
            ->where('is_active', true)
            ->with('category')
            ->get();
    }

    public function getByCategory(int $categoryId): Collection
    {
        return $this->model
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->get();
    }

    public function search(string $query): Collection
    {
        return $this->model
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->where('is_active', true)
            ->with('category')
            ->get();
    }

    public function getActiveProducts(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->with('category')
            ->get();
    }

    public function getAllWithCategory(): Collection
    {
        return $this->model->with('category')->get();
    }

    public function updateStock(int $id, float $quantity): bool
    {
        $product = $this->find($id);

        if (!$product) {
            return false;
        }

        $product->current_stock = $quantity;

        return $product->save();
    }

    public function hasSufficientStock(int $id, float $quantity): bool
    {
        $product = $this->find($id);

        if (!$product) {
            return false;
        }

        return $product->current_stock >= $quantity;
    }
}
