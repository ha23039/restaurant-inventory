<?php

namespace App\Repositories\Eloquent;

use App\Models\CashFlow;
use App\Repositories\Contracts\CashFlowRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class CashFlowRepository extends BaseRepository implements CashFlowRepositoryInterface
{
    public function __construct(CashFlow $model)
    {
        parent::__construct($model);
    }

    public function getByDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->model
            ->whereBetween('flow_date', [$startDate, $endDate])
            ->with(['user', 'sale'])
            ->orderBy('flow_date', 'desc')
            ->get();
    }

    public function getByType(string $type): Collection
    {
        return $this->model
            ->where('type', $type)
            ->with(['user', 'sale'])
            ->orderBy('flow_date', 'desc')
            ->get();
    }

    public function getByCategory(string $category): Collection
    {
        return $this->model
            ->where('category', $category)
            ->with(['user', 'sale'])
            ->orderBy('flow_date', 'desc')
            ->get();
    }

    public function getTotalIncomeByDateRange(Carbon $startDate, Carbon $endDate): float
    {
        return $this->model
            ->where('type', 'entrada')
            ->whereBetween('flow_date', [$startDate, $endDate])
            ->sum('amount');
    }

    public function getTotalExpensesByDateRange(Carbon $startDate, Carbon $endDate): float
    {
        return $this->model
            ->where('type', 'salida')
            ->whereBetween('flow_date', [$startDate, $endDate])
            ->sum('amount');
    }

    public function getBalanceByDateRange(Carbon $startDate, Carbon $endDate): float
    {
        $income = $this->getTotalIncomeByDateRange($startDate, $endDate);
        $expenses = $this->getTotalExpensesByDateRange($startDate, $endDate);

        return $income - $expenses;
    }

    public function getByCategoryAndDateRange(string $category, Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->model
            ->where('category', $category)
            ->whereBetween('flow_date', [$startDate, $endDate])
            ->with(['user', 'sale'])
            ->orderBy('flow_date', 'desc')
            ->get();
    }

    public function getAllWithRelationships(): Collection
    {
        return $this->model
            ->with(['user', 'sale'])
            ->orderBy('flow_date', 'desc')
            ->get();
    }
}
