<?php

namespace App\Repositories;

use App\Models\CashFlow;
use Illuminate\Support\Facades\DB;

class CashFlowRepository
{
    public function __construct(
        protected CashFlow $model
    ) {
    }

    /**
     * Get current balance (sum of all signed amounts)
     */
    public function getBalance(): float
    {
        $income = $this->model->income()->sum('amount');
        $expenses = $this->model->expense()->sum('amount');

        return $income - $expenses;
    }

    /**
     * Get balance for a specific date range
     */
    public function getBalanceByDateRange(string $from, string $to): float
    {
        $income = $this->model->income()
            ->byDateRange($from, $to)
            ->sum('amount');

        $expenses = $this->model->expense()
            ->byDateRange($from, $to)
            ->sum('amount');

        return $income - $expenses;
    }

    /**
     * Get financial summary for a date range
     */
    public function getSummaryByDateRange(string $from, string $to): array
    {
        $income = $this->model->income()
            ->byDateRange($from, $to)
            ->sum('amount');

        $expenses = $this->model->expense()
            ->byDateRange($from, $to)
            ->sum('amount');

        $incomeCount = $this->model->income()
            ->byDateRange($from, $to)
            ->count();

        $expensesCount = $this->model->expense()
            ->byDateRange($from, $to)
            ->count();

        return [
            'income' => [
                'total' => (float) $income,
                'count' => $incomeCount,
            ],
            'expenses' => [
                'total' => (float) $expenses,
                'count' => $expensesCount,
            ],
            'balance' => (float) ($income - $expenses),
            'profit_margin' => $income > 0 ? (($income - $expenses) / $income) * 100 : 0,
        ];
    }

    /**
     * Get daily trends for a period (for charts)
     */
    public function getTrendsByPeriod(string $from, string $to, string $groupBy = 'day'): array
    {
        $driver = DB::connection()->getDriverName();

        // Database-specific date formatting
        if ($driver === 'pgsql') {
            $dateFormat = match ($groupBy) {
                'day' => "to_char(flow_date, 'YYYY-MM-DD')",
                'week' => "to_char(flow_date, 'IYYY-IW')",
                'month' => "to_char(flow_date, 'YYYY-MM')",
                'year' => "to_char(flow_date, 'YYYY')",
                default => "to_char(flow_date, 'YYYY-MM-DD')",
            };
        } else {
            // MySQL
            $mysqlFormat = match ($groupBy) {
                'day' => '%Y-%m-%d',
                'week' => '%Y-%u',
                'month' => '%Y-%m',
                'year' => '%Y',
                default => '%Y-%m-%d',
            };
            $dateFormat = "DATE_FORMAT(flow_date, '{$mysqlFormat}')";
        }

        $trends = DB::table('cash_flow')
            ->select(
                DB::raw("{$dateFormat} as period"),
                'type',
                DB::raw('SUM(amount) as total')
            )
            ->whereBetween('flow_date', [$from, $to])
            ->groupBy('period', 'type')
            ->orderBy('period')
            ->get();

        // Format data for charts
        $periods = $trends->pluck('period')->unique()->values()->all();
        $income = [];
        $expenses = [];

        foreach ($periods as $period) {
            $periodIncome = $trends
                ->where('period', $period)
                ->where('type', 'entrada')
                ->first();

            $periodExpense = $trends
                ->where('period', $period)
                ->where('type', 'salida')
                ->first();

            $income[] = $periodIncome ? (float) $periodIncome->total : 0;
            $expenses[] = $periodExpense ? (float) $periodExpense->total : 0;
        }

        return [
            'labels' => $periods,
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $income,
                ],
                [
                    'label' => 'Gastos',
                    'data' => $expenses,
                ],
            ],
        ];
    }

    /**
     * Get transactions grouped by category
     */
    public function getByCategory(string $from = null, string $to = null): array
    {
        $query = DB::table('cash_flow')
            ->select(
                'category',
                'type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('category', 'type')
            ->orderBy('total', 'desc');

        if ($from && $to) {
            $query->whereBetween('flow_date', [$from, $to]);
        }

        // Category labels mapping
        $categoryLabels = [
            'ventas' => 'Ventas',
            'compras' => 'Compras',
            'compra_productos_insumos' => 'Compra de Productos e Insumos',
            'gastos_operativos' => 'Gastos Operativos',
            'gastos_admin' => 'Gastos Administrativos',
            'servicios_publicos' => 'Servicios Públicos',
            'nomina' => 'Nómina',
            'mantenimiento' => 'Mantenimiento',
            'marketing' => 'Marketing',
            'devoluciones' => 'Devoluciones',
            'otros' => 'Otros',
            'otros_ingresos' => 'Otros Ingresos',
        ];

        return $query->get()->map(function ($item) use ($categoryLabels) {
            return [
                'category' => $item->category,
                'label' => $categoryLabels[$item->category] ?? ucfirst(str_replace('_', ' ', $item->category)),
                'type' => $item->type,
                'count' => $item->count,
                'total' => (float) $item->total,
            ];
        })->toArray();
    }

    /**
     * Get transactions grouped by payment method (from sales)
     */
    public function getByPaymentMethod(string $from = null, string $to = null): array
    {
        $query = DB::table('cash_flow')
            ->join('sales', 'cash_flow.sale_id', '=', 'sales.id')
            ->select(
                'sales.payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(cash_flow.amount) as total')
            )
            ->whereNotNull('cash_flow.sale_id')
            ->where('cash_flow.type', 'entrada')
            ->groupBy('sales.payment_method')
            ->orderBy('total', 'desc');

        if ($from && $to) {
            $query->whereBetween('cash_flow.flow_date', [$from, $to]);
        }

        return $query->get()->map(function ($item) {
            return [
                'payment_method' => $item->payment_method,
                'count' => $item->count,
                'total' => (float) $item->total,
            ];
        })->toArray();
    }

    /**
     * Get top categories by expense
     */
    public function getTopExpenseCategories(int $limit = 5, string $from = null, string $to = null): array
    {
        $query = $this->model->expense()
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->limit($limit);

        if ($from && $to) {
            $query->byDateRange($from, $to);
        }

        return $query->get()->map(function ($item) {
            return [
                'category' => $item->category,
                'total' => (float) $item->total,
                'label' => $item->category_label,
            ];
        })->toArray();
    }

    /**
     * Compare two periods
     */
    public function comparePeriods(string $currentFrom, string $currentTo, string $previousFrom, string $previousTo): array
    {
        $current = $this->getSummaryByDateRange($currentFrom, $currentTo);
        $previous = $this->getSummaryByDateRange($previousFrom, $previousTo);

        $calculateChange = function ($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? 100 : 0;
            }

            return (($current - $previous) / $previous) * 100;
        };

        return [
            'current' => $current,
            'previous' => $previous,
            'changes' => [
                'income_change' => $calculateChange($current['income']['total'], $previous['income']['total']),
                'expenses_change' => $calculateChange($current['expenses']['total'], $previous['expenses']['total']),
                'balance_change' => $calculateChange($current['balance'], $previous['balance']),
            ],
        ];
    }

    /**
     * Get recent transactions
     */
    public function getRecent(int $limit = 10)
    {
        return $this->model
            ->with(['user', 'sale'])
            ->orderBy('flow_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get paginated transactions with filters
     */
    public function getPaginated(array $filters = [], int $perPage = 20)
    {
        $query = $this->model->with(['user', 'sale']);

        // Apply filters
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->byDateRange($filters['date_from'], $filters['date_to']);
        }

        if (!empty($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (!empty($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Order by date desc
        $query->orderBy('flow_date', 'desc')
            ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }
}
