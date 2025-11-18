<?php

namespace App\Repositories\Eloquent;

use App\Models\SimpleProduct;
use App\Repositories\Contracts\SimpleProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SimpleProductRepository extends BaseRepository implements SimpleProductRepositoryInterface
{
    public function __construct(SimpleProduct $model)
    {
        parent::__construct($model);
    }

    public function getAvailableProducts(): Collection
    {
        return $this->model
            ->whereHas('product', function ($query) {
                $query->where('is_active', true);
            })
            ->with('product.category')
            ->get()
            ->filter(function ($item) {
                return $this->calculateAvailableQuantity($item->id) > 0;
            });
    }

    public function getByCategory(int $categoryId): Collection
    {
        return $this->model
            ->whereHas('product', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId)
                    ->where('is_active', true);
            })
            ->with('product.category')
            ->get();
    }

    public function getAllWithProduct(): Collection
    {
        return $this->model
            ->with('product.category')
            ->get();
    }

    public function calculateAvailableQuantity(int $id): int
    {
        $simpleProduct = $this->model->with('product')->find($id);

        if (!$simpleProduct || !$simpleProduct->product) {
            return 0;
        }

        $currentStock = $simpleProduct->product->current_stock;
        $costPerUnit = $simpleProduct->cost_per_unit;

        if ($costPerUnit <= 0) {
            return 0;
        }

        return (int) floor($currentStock / $costPerUnit);
    }

    public function hasSufficientStock(int $id, int $quantity): bool
    {
        $availableQuantity = $this->calculateAvailableQuantity($id);

        return $availableQuantity >= $quantity;
    }

    public function getProductsWithLowStock(int $threshold = 5): Collection
    {
        return $this->model
            ->with('product')
            ->get()
            ->filter(function ($item) use ($threshold) {
                return $this->calculateAvailableQuantity($item->id) <= $threshold;
            });
    }

    public function search(string $query): Collection
    {
        return $this->model
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orWhereHas('product', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->with('product.category')
            ->get();
    }

    public function updateProfitMargin(int $id, float $salePrice): bool
    {
        $simpleProduct = $this->find($id);

        if (!$simpleProduct) {
            return false;
        }

        $simpleProduct->sale_price = $salePrice;
        $simpleProduct->profit_margin = $salePrice - ($simpleProduct->cost_per_unit * $simpleProduct->product->unit_cost);

        return $simpleProduct->save();
    }
}
