<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Calculate date ranges
        $dates = $this->calculateDateRanges($period, $startDate, $endDate);
        $currentStart = $dates['current_start'];
        $currentEnd = $dates['current_end'];
        $previousStart = $dates['previous_start'];
        $previousEnd = $dates['previous_end'];

        // Get current period data
        $currentData = $this->getPeriodData($currentStart, $currentEnd);

        // Get previous period data for comparison
        $previousData = $this->getPeriodData($previousStart, $previousEnd);

        // Calculate comparisons
        $topProducts = $this->calculateComparisons(
            $currentData['top_products'],
            $previousData['top_products']
        );

        $bottomProducts = $this->getBottomProducts($currentStart, $currentEnd);

        // Summary metrics with comparison
        $summary = [
            'total_sales' => $currentData['total_sales'],
            'total_quantity' => $currentData['total_quantity'],
            'total_orders' => $currentData['total_orders'],
            'average_ticket' => $currentData['total_orders'] > 0
                ? round($currentData['total_sales'] / $currentData['total_orders'], 2)
                : 0,
            'previous_total_sales' => $previousData['total_sales'],
            'previous_total_quantity' => $previousData['total_quantity'],
            'previous_total_orders' => $previousData['total_orders'],
            'sales_change' => $this->calculatePercentageChange(
                $previousData['total_sales'],
                $currentData['total_sales']
            ),
            'quantity_change' => $this->calculatePercentageChange(
                $previousData['total_quantity'],
                $currentData['total_quantity']
            ),
            'orders_change' => $this->calculatePercentageChange(
                $previousData['total_orders'],
                $currentData['total_orders']
            ),
        ];

        // Daily sales trend for charts
        $dailyTrend = $this->getDailyTrend($currentStart, $currentEnd);

        return Inertia::render('Reports/Sales', [
            'topProducts' => $topProducts,
            'bottomProducts' => $bottomProducts,
            'summary' => $summary,
            'dailyTrend' => $dailyTrend,
            'filters' => [
                'period' => $period,
                'start_date' => $currentStart->toDateString(),
                'end_date' => $currentEnd->toDateString(),
            ],
        ]);
    }

    private function calculateDateRanges(string $period, ?string $startDate, ?string $endDate): array
    {
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                $currentStart = $now->copy()->startOfDay();
                $currentEnd = $now->copy()->endOfDay();
                $previousStart = $now->copy()->subDay()->startOfDay();
                $previousEnd = $now->copy()->subDay()->endOfDay();
                break;

            case 'week':
                $currentStart = $now->copy()->startOfWeek();
                $currentEnd = $now->copy()->endOfWeek();
                $previousStart = $now->copy()->subWeek()->startOfWeek();
                $previousEnd = $now->copy()->subWeek()->endOfWeek();
                break;

            case 'month':
                $currentStart = $now->copy()->startOfMonth();
                $currentEnd = $now->copy()->endOfMonth();
                $previousStart = $now->copy()->subMonth()->startOfMonth();
                $previousEnd = $now->copy()->subMonth()->endOfMonth();
                break;

            case 'custom':
                $currentStart = Carbon::parse($startDate)->startOfDay();
                $currentEnd = Carbon::parse($endDate)->endOfDay();
                $daysDiff = $currentStart->diffInDays($currentEnd) + 1;
                $previousEnd = $currentStart->copy()->subDay()->endOfDay();
                $previousStart = $previousEnd->copy()->subDays($daysDiff - 1)->startOfDay();
                break;

            default:
                $currentStart = $now->copy()->startOfDay();
                $currentEnd = $now->copy()->endOfDay();
                $previousStart = $now->copy()->subDay()->startOfDay();
                $previousEnd = $now->copy()->subDay()->endOfDay();
        }

        return [
            'current_start' => $currentStart,
            'current_end' => $currentEnd,
            'previous_start' => $previousStart,
            'previous_end' => $previousEnd,
        ];
    }

    private function getPeriodData(Carbon $start, Carbon $end): array
    {
        // Get top products
        $topProducts = SaleItem::select(
                'sale_items.product_type',
                'sale_items.menu_item_id',
                'sale_items.simple_product_id',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total_price) as total_revenue')
            )
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$start, $end])
            ->groupBy('sale_items.product_type', 'sale_items.menu_item_id', 'sale_items.simple_product_id')
            ->orderByDesc('total_quantity')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'product_type' => $item->product_type,
                    'menu_item_id' => $item->menu_item_id,
                    'simple_product_id' => $item->simple_product_id,
                    'name' => $this->getProductName($item),
                    'total_quantity' => (int) $item->total_quantity,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            });

        // Get totals
        $totals = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('COALESCE(SUM(total), 0) as total_sales, COUNT(*) as total_orders')
            ->first();

        $totalQuantity = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$start, $end])
            ->sum('sale_items.quantity');

        return [
            'top_products' => $topProducts,
            'total_sales' => (float) ($totals->total_sales ?? 0),
            'total_orders' => (int) ($totals->total_orders ?? 0),
            'total_quantity' => (int) $totalQuantity,
        ];
    }

    private function getProductName($item): string
    {
        if ($item->product_type === 'menu' && $item->menu_item_id) {
            $menuItem = \App\Models\MenuItem::find($item->menu_item_id);
            return $menuItem ? $menuItem->name : 'Producto eliminado';
        }

        if ($item->product_type === 'simple' && $item->simple_product_id) {
            $simpleProduct = \App\Models\SimpleProduct::find($item->simple_product_id);
            return $simpleProduct ? $simpleProduct->name : 'Producto eliminado';
        }

        return 'Producto desconocido';
    }

    private function calculateComparisons($currentProducts, $previousProducts): array
    {
        $previousMap = [];
        foreach ($previousProducts as $product) {
            $key = $product['product_type'] . '_' . ($product['menu_item_id'] ?? $product['simple_product_id']);
            $previousMap[$key] = $product;
        }

        return $currentProducts->map(function ($product) use ($previousMap) {
            $key = $product['product_type'] . '_' . ($product['menu_item_id'] ?? $product['simple_product_id']);
            $previous = $previousMap[$key] ?? null;

            $previousQuantity = $previous ? $previous['total_quantity'] : 0;
            $previousRevenue = $previous ? $previous['total_revenue'] : 0;

            return [
                ...$product,
                'previous_quantity' => $previousQuantity,
                'previous_revenue' => $previousRevenue,
                'quantity_change' => $this->calculatePercentageChange($previousQuantity, $product['total_quantity']),
                'revenue_change' => $this->calculatePercentageChange($previousRevenue, $product['total_revenue']),
            ];
        })->toArray();
    }

    private function calculatePercentageChange($previous, $current): ?float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function getBottomProducts(Carbon $start, Carbon $end): array
    {
        return SaleItem::select(
                'sale_items.product_type',
                'sale_items.menu_item_id',
                'sale_items.simple_product_id',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total_price) as total_revenue')
            )
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$start, $end])
            ->groupBy('sale_items.product_type', 'sale_items.menu_item_id', 'sale_items.simple_product_id')
            ->orderBy('total_quantity')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $this->getProductName($item),
                    'total_quantity' => (int) $item->total_quantity,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            })
            ->toArray();
    }

    private function getDailyTrend(Carbon $start, Carbon $end): array
    {
        return Sale::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => [
                'date' => $item->date,
                'total' => (float) $item->total,
                'orders' => (int) $item->orders,
            ])
            ->toArray();
    }

    public function exportPdf(Request $request)
    {
        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $dates = $this->calculateDateRanges($period, $startDate, $endDate);
        $currentData = $this->getPeriodData($dates['current_start'], $dates['current_end']);
        $previousData = $this->getPeriodData($dates['previous_start'], $dates['previous_end']);

        $topProducts = $this->calculateComparisons(
            $currentData['top_products'],
            $previousData['top_products']
        );

        $pdf = Pdf::loadView('reports.sales-pdf', [
            'topProducts' => $topProducts,
            'summary' => [
                'total_sales' => $currentData['total_sales'],
                'total_quantity' => $currentData['total_quantity'],
                'total_orders' => $currentData['total_orders'],
            ],
            'period' => $period,
            'startDate' => $dates['current_start']->format('d/m/Y'),
            'endDate' => $dates['current_end']->format('d/m/Y'),
        ]);

        $filename = 'reporte-ventas-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $dates = $this->calculateDateRanges($period, $startDate, $endDate);
        $currentData = $this->getPeriodData($dates['current_start'], $dates['current_end']);
        $previousData = $this->getPeriodData($dates['previous_start'], $dates['previous_end']);

        $topProducts = $this->calculateComparisons(
            $currentData['top_products'],
            $previousData['top_products']
        );

        $filename = 'reporte-ventas-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($topProducts, $currentData) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Headers
            fputcsv($file, ['#', 'Producto', 'Cantidad', 'Ingresos', 'Cambio %']);

            // Data
            foreach ($topProducts as $index => $product) {
                fputcsv($file, [
                    $index + 1,
                    $product['name'],
                    $product['total_quantity'],
                    number_format($product['total_revenue'], 2),
                    ($product['quantity_change'] >= 0 ? '+' : '') . $product['quantity_change'] . '%',
                ]);
            }

            // Summary
            fputcsv($file, []);
            fputcsv($file, ['RESUMEN']);
            fputcsv($file, ['Total Ventas', number_format($currentData['total_sales'], 2)]);
            fputcsv($file, ['Total Productos', $currentData['total_quantity']]);
            fputcsv($file, ['Total Ordenes', $currentData['total_orders']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
