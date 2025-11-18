<?php

namespace App\Repositories\Contracts;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

interface SaleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get sales by date range
     */
    public function getByDateRange(Carbon $startDate, Carbon $endDate): Collection;

    /**
     * Get sales by user
     */
    public function getByUser(int $userId): Collection;

    /**
     * Get sales by status
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get last sale of the day for generating sale number
     */
    public function getLastSaleOfDay(string $date): ?Sale;

    /**
     * Get sales with all relationships
     */
    public function getAllWithRelationships(): Collection;

    /**
     * Get total sales amount by date range
     */
    public function getTotalSalesByDateRange(Carbon $startDate, Carbon $endDate): float;

    /**
     * Get sales count by date range
     */
    public function getSalesCountByDateRange(Carbon $startDate, Carbon $endDate): int;

    /**
     * Get top selling items
     */
    public function getTopSellingItems(int $limit = 10): Collection;

    /**
     * Find sale by sale number
     */
    public function findBySaleNumber(string $saleNumber): ?Sale;
}
