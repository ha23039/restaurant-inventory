<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();

        // Métricas básicas
        $metrics = [
            'low_stock_products' => Product::whereRaw('current_stock <= min_stock')->count(),
            'today_sales' => Sale::whereDate('created_at', $today)
                                ->where('status', 'completada')
                                ->sum('total'),
            'today_transactions' => Sale::whereDate('created_at', $today)
                                       ->where('status', 'completada')
                                       ->count(),
            'today_cash_flow' => Sale::whereDate('created_at', $today)
                                    ->where('status', 'completada')
                                    ->sum('total'),
        ];

        // Datos para gráfica de ventas semanales (últimos 7 días)
        $weekSales = [];
        $weekLabels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $weekLabels[] = $date->format('D');
            $weekSales[] = Sale::whereDate('created_at', $date)
                              ->where('status', 'completada')
                              ->sum('total');
        }

        // Productos más vendidos (top 5)
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('menu_items', function($join) {
                $join->on('sale_items.menu_item_id', '=', 'menu_items.id')
                     ->where('sale_items.product_type', '=', 'menu');
            })
            ->leftJoin('simple_products', function($join) {
                $join->on('sale_items.simple_product_id', '=', 'simple_products.id')
                     ->where('sale_items.product_type', '=', 'simple');
            })
            ->where('sales.status', 'completada')
            ->where('sales.created_at', '>=', today()->subDays(7))
            ->select(
                DB::raw('COALESCE(menu_items.name, simple_products.name) as name'),
                DB::raw('SUM(sale_items.quantity) as quantity')
            )
            ->groupBy('name')
            ->orderBy('quantity', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->name,
                    'quantity' => (int) $item->quantity
                ];
            });

        // Distribución por método de pago (últimos 7 días)
        $paymentMethods = Sale::where('status', 'completada')
            ->where('created_at', '>=', today()->subDays(7))
            ->select('payment_method', DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        return Inertia::render('Dashboard', [
            'metrics' => $metrics,
            'chartData' => [
                'weekSales' => array_values($weekSales),
                'weekLabels' => $weekLabels,
                'topProducts' => $topProducts,
                'paymentMethods' => [
                    'efectivo' => $paymentMethods->get('efectivo', 0),
                    'tarjeta' => $paymentMethods->get('tarjeta', 0),
                    'transferencia' => $paymentMethods->get('transferencia', 0),
                    'mixto' => $paymentMethods->get('mixto', 0),
                ],
            ],
        ]);
    }
}
