<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

interface CashFlowRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get cash flow entries by date range
     */
    public function getByDateRange(Carbon $startDate, Carbon $endDate): Collection;

    /**
     * Get cash flow entries by type (entrada/salida)
     */
    public function getByType(string $type): Collection;

    /**
     * Get cash flow entries by category
     */
    public function getByCategory(string $category): Collection;

    /**
     * Get total income by date range
     */
    public function getTotalIncomeByDateRange(Carbon $startDate, Carbon $endDate): float;

    /**
     * Get total expenses by date range
     */
    public function getTotalExpensesByDateRange(Carbon $startDate, Carbon $endDate): float;

    /**
     * Get balance by date range
     */
    public function getBalanceByDateRange(Carbon $startDate, Carbon $endDate): float;

    /**
     * Get entries by category and date range
     */
    public function getByCategoryAndDateRange(string $category, Carbon $startDate, Carbon $endDate): Collection;

    /**
     * Get all with relationships
     */
    public function getAllWithRelationships(): Collection;
}
