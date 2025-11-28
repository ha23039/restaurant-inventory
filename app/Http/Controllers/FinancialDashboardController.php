<?php

namespace App\Http\Controllers;

use App\Http\Resources\CashFlowResource;
use App\Repositories\CashFlowRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinancialDashboardController extends Controller
{
    public function __construct(
        protected CashFlowRepository $repository
    ) {
    }

    /**
     * Display the financial dashboard
     */
    public function index(Request $request)
    {
        // Get date range from request or default to current month
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $period = $request->input('period', 'month'); // day, week, month, year

        // Calculate previous period dates for comparison
        $start = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        $diff = $start->diffInDays($end);

        $previousFrom = $start->copy()->subDays($diff + 1)->format('Y-m-d');
        $previousTo = $start->copy()->subDay()->format('Y-m-d');

        // Get summary statistics for current and previous periods
        $currentSummary = $this->repository->getSummaryByDateRange($dateFrom, $dateTo);
        $comparison = $this->repository->comparePeriods($dateFrom, $dateTo, $previousFrom, $previousTo);

        // Get trends data for charts
        $trends = $this->repository->getTrendsByPeriod($dateFrom, $dateTo, $period);

        // Get breakdown by category
        $expensesByCategory = $this->repository->getByCategory($dateFrom, $dateTo);
        $expensesByCategory = array_values(array_filter($expensesByCategory, fn ($item) => $item['type'] === 'salida'));

        // Get payment method breakdown
        $paymentMethods = $this->repository->getByPaymentMethod($dateFrom, $dateTo);

        // Get top expense categories
        $topExpenses = $this->repository->getTopExpenseCategories(5, $dateFrom, $dateTo);

        // Get recent transactions
        $recentTransactions = CashFlowResource::collection(
            $this->repository->getRecent(10)
        )->resolve();

        // Calculate KPIs
        $kpis = [
            'balance' => [
                'current' => $currentSummary['balance'],
                'previous' => $comparison['previous']['balance'],
                'change' => $comparison['changes']['balance_change'],
                'trend' => $comparison['changes']['balance_change'] >= 0 ? 'up' : 'down',
            ],
            'income' => [
                'current' => $currentSummary['income']['total'],
                'previous' => $comparison['previous']['income']['total'],
                'change' => $comparison['changes']['income_change'],
                'trend' => $comparison['changes']['income_change'] >= 0 ? 'up' : 'down',
                'count' => $currentSummary['income']['count'],
            ],
            'expenses' => [
                'current' => $currentSummary['expenses']['total'],
                'previous' => $comparison['previous']['expenses']['total'],
                'change' => $comparison['changes']['expenses_change'],
                'trend' => $comparison['changes']['expenses_change'] >= 0 ? 'up' : 'down',
                'count' => $currentSummary['expenses']['count'],
            ],
            'profit_margin' => [
                'current' => $currentSummary['profit_margin'],
                'previous' => $comparison['previous']['profit_margin'],
                'change' => $currentSummary['profit_margin'] - $comparison['previous']['profit_margin'],
                'trend' => ($currentSummary['profit_margin'] - $comparison['previous']['profit_margin']) >= 0 ? 'up' : 'down',
            ],
        ];

        // Format data for category pie chart
        $categoryChartData = [
            'labels' => array_map(fn ($item) => $item['label'] ?? $item['category'], $expensesByCategory),
            'datasets' => [
                [
                    'label' => 'Gastos por Categoría',
                    'data' => array_map(fn ($item) => $item['total'], $expensesByCategory),
                    'backgroundColor' => [
                        '#ef4444', // red-500
                        '#f59e0b', // amber-500
                        '#3b82f6', // blue-500
                        '#8b5cf6', // violet-500
                        '#10b981', // emerald-500
                        '#ec4899', // pink-500
                    ],
                ],
            ],
        ];

        // Format data for payment method chart
        $paymentMethodChartData = [
            'labels' => array_map(fn ($item) => ucfirst($item['payment_method']), $paymentMethods),
            'datasets' => [
                [
                    'label' => 'Ingresos por Método de Pago',
                    'data' => array_map(fn ($item) => $item['total'], $paymentMethods),
                    'backgroundColor' => [
                        '#22c55e', // green-500
                        '#3b82f6', // blue-500
                        '#f59e0b', // amber-500
                    ],
                ],
            ],
        ];

        return Inertia::render('Dashboard/Financial', [
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'period' => $period,
            ],
            'kpis' => $kpis,
            'trends' => $trends,
            'categoryChart' => $categoryChartData,
            'paymentMethodChart' => $paymentMethodChartData,
            'topExpenses' => $topExpenses,
            'recentTransactions' => $recentTransactions,
            'summary' => $currentSummary,
        ]);
    }

    /**
     * Get KPI data for API calls
     */
    public function getKpis(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $start = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        $diff = $start->diffInDays($end);

        $previousFrom = $start->copy()->subDays($diff + 1)->format('Y-m-d');
        $previousTo = $start->copy()->subDay()->format('Y-m-d');

        $currentSummary = $this->repository->getSummaryByDateRange($dateFrom, $dateTo);
        $comparison = $this->repository->comparePeriods($dateFrom, $dateTo, $previousFrom, $previousTo);

        return response()->json([
            'balance' => [
                'current' => $currentSummary['balance'],
                'change' => $comparison['changes']['balance_change'],
            ],
            'income' => [
                'current' => $currentSummary['income']['total'],
                'change' => $comparison['changes']['income_change'],
            ],
            'expenses' => [
                'current' => $currentSummary['expenses']['total'],
                'change' => $comparison['changes']['expenses_change'],
            ],
            'profit_margin' => [
                'current' => $currentSummary['profit_margin'],
                'change' => $currentSummary['profit_margin'] - $comparison['previous']['profit_margin'],
            ],
        ]);
    }

    /**
     * Get trends data for charts
     */
    public function getTrends(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $period = $request->input('period', 'day');

        $trends = $this->repository->getTrendsByPeriod($dateFrom, $dateTo, $period);

        return response()->json($trends);
    }
}
