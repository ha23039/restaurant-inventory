<?php

namespace App\Repositories\Eloquent;

use App\Models\Sale;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class SaleRepository extends BaseRepository implements SaleRepositoryInterface
{
    public function __construct(Sale $model)
    {
        parent::__construct($model);
    }

    public function getByDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->model
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['user', 'saleItems'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->with('saleItems')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model
            ->where('status', $status)
            ->with(['user', 'saleItems'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getLastSaleOfDay(string $date): ?Sale
    {
        return $this->model
            ->where('sale_number', 'like', "{$date}%")
            ->orderBy('sale_number', 'desc')
            ->first();
    }

    public function getAllWithRelationships(): Collection
    {
        return $this->model
            ->with(['user', 'saleItems.menuItem', 'saleItems.simpleProduct', 'cashFlow'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getTotalSalesByDateRange(Carbon $startDate, Carbon $endDate): float
    {
        return $this->model
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completada')
            ->sum('total');
    }

    public function getSalesCountByDateRange(Carbon $startDate, Carbon $endDate): int
    {
        return $this->model
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completada')
            ->count();
    }

    public function getTopSellingItems(int $limit = 10): Collection
    {
        return $this->model
            ->with('saleItems')
            ->get()
            ->flatMap(fn ($sale) => $sale->saleItems)
            ->groupBy(function ($item) {
                return $item->product_type === 'menu'
                    ? 'menu_'.$item->menu_item_id
                    : 'simple_'.$item->simple_product_id;
            })
            ->map(function ($items) {
                return [
                    'quantity' => $items->sum('quantity'),
                    'total' => $items->sum('subtotal'),
                    'product' => $items->first(),
                ];
            })
            ->sortByDesc('quantity')
            ->take($limit);
    }

    public function findBySaleNumber(string $saleNumber): ?Sale
    {
        return $this->model
            ->where('sale_number', $saleNumber)
            ->with(['user', 'saleItems'])
            ->first();
    }
}
