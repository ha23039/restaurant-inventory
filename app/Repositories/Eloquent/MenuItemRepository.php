<?php

namespace App\Repositories\Eloquent;

use App\Models\MenuItem;
use App\Repositories\Contracts\MenuItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MenuItemRepository extends BaseRepository implements MenuItemRepositoryInterface
{
    public function __construct(MenuItem $model)
    {
        parent::__construct($model);
    }

    public function getAvailableItems(): Collection
    {
        return $this->model
            ->where('is_available', true)
            ->with(['recipes.product.category'])
            ->get();
    }

    public function getByCategory(int $categoryId): Collection
    {
        return $this->model
            ->where('category_id', $categoryId)
            ->where('is_available', true)
            ->with(['recipes.product'])
            ->get();
    }

    public function getAllWithRecipes(): Collection
    {
        return $this->model
            ->with(['recipes.product.category'])
            ->get();
    }

    public function calculateAvailableQuantity(int $id): int
    {
        $menuItem = $this->model->with('recipes.product')->find($id);

        if (!$menuItem || $menuItem->recipes->isEmpty()) {
            return 0;
        }

        $availableQuantities = [];

        foreach ($menuItem->recipes as $recipe) {
            $product = $recipe->product;
            $quantityNeeded = $recipe->quantity_needed;

            if ($quantityNeeded > 0) {
                $availableQuantities[] = floor($product->current_stock / $quantityNeeded);
            }
        }

        return empty($availableQuantities) ? 0 : min($availableQuantities);
    }

    public function hasSufficientIngredients(int $id, int $quantity): bool
    {
        $availableQuantity = $this->calculateAvailableQuantity($id);

        return $availableQuantity >= $quantity;
    }

    public function getItemsWithLowStock(int $threshold = 5): Collection
    {
        return $this->model
            ->with(['recipes.product'])
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
            ->where('is_available', true)
            ->with(['recipes.product'])
            ->get();
    }
}
