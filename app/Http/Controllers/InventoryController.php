<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index()
    {
        // Métricas principales
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $lowStockProducts = Product::whereRaw('current_stock <= min_stock')->count();
        $expiredProducts = Product::where('expiry_date', '<', now())->count();
        $expiringSoonProducts = Product::whereBetween('expiry_date', [now(), now()->addDays(7)])->count();

        // Productos con alertas
        $alertProducts = Product::with('category')
            ->where(function ($query) {
                $query->whereRaw('current_stock <= min_stock')
                    ->orWhere('expiry_date', '<', now())
                    ->orWhereBetween('expiry_date', [now(), now()->addDays(7)]);
            })
            ->limit(10)
            ->get();

        // Últimos movimientos
        $recentMovements = InventoryMovement::with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Productos por categoría
        $productsByCategory = Category::withCount('products')->get();

        return Inertia::render('Inventory/Index', [
            'metrics' => [
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
                'low_stock_products' => $lowStockProducts,
                'expired_products' => $expiredProducts,
                'expiring_soon_products' => $expiringSoonProducts,
            ],
            'alert_products' => $alertProducts,
            'recent_movements' => $recentMovements,
            'products_by_category' => $productsByCategory,
        ]);
    }
}
