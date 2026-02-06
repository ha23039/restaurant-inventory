<?php

namespace App\Http\Controllers\DigitalMenu;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\SimpleProduct;
use Illuminate\Http\JsonResponse;

class StockController extends Controller
{
    /**
     * Get current stock levels for all digital menu products
     * Lightweight endpoint for polling
     */
    public function index(): JsonResponse
    {
        $stock = [
            'menu_items' => [],
            'simple_products' => [],
            'variants' => [],
            'simple_variants' => [],
        ];

        // Menu items stock
        $menuItemsQuery = MenuItem::where('is_available', true)
            ->where('is_service', false);

        if (\Schema::hasColumn('menu_items', 'show_in_digital_menu')) {
            $menuItemsQuery->where('show_in_digital_menu', true);
        }

        $menuItems = $menuItemsQuery
            ->with(['recipes.product', 'variants.recipes.product'])
            ->get();

        foreach ($menuItems as $item) {
            $stock['menu_items'][$item->id] = $item->available_quantity;

            // Variants
            foreach ($item->variants as $variant) {
                $stock['variants'][$variant->id] = $variant->available_quantity;
            }
        }

        // Simple products stock
        $simpleProductsQuery = SimpleProduct::where('is_available', true);

        if (\Schema::hasColumn('simple_products', 'show_in_digital_menu')) {
            $simpleProductsQuery->where('show_in_digital_menu', true);
        }

        $simpleProducts = $simpleProductsQuery
            ->with(['product', 'variants.recipes.product'])
            ->get();

        foreach ($simpleProducts as $product) {
            $stock['simple_products'][$product->id] = $product->available_quantity;

            // Variants
            if ($product->allows_variants && $product->variants) {
                foreach ($product->variants as $variant) {
                    $stock['simple_variants'][$variant->id] = $variant->available_quantity;
                }
            }
        }

        return response()->json([
            'stock' => $stock,
            'timestamp' => now()->timestamp,
        ]);
    }
}
