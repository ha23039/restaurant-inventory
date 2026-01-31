<?php

namespace App\Http\Controllers\DigitalMenu;

use App\Http\Controllers\Controller;
use App\Models\BusinessSettings;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\SimpleProduct;
use App\Models\Table;
use App\Services\ComboService;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function __construct(
        private ComboService $comboService
    ) {
    }

    /**
     * Display the digital menu
     */
    public function index()
    {
        $settings = BusinessSettings::get();

        if (!$settings->digital_menu_enabled) {
            return Inertia::render('DigitalMenu/Closed', [
                'message' => 'El menu digital no esta disponible en este momento.',
            ]);
        }

        // Get available menu items with stock
        // Filter by show_in_digital_menu if the column exists
        $menuItemsQuery = MenuItem::where('is_available', true)
            ->where('is_service', false);

        // Only filter by show_in_digital_menu if the column exists
        if (\Schema::hasColumn('menu_items', 'show_in_digital_menu')) {
            $menuItemsQuery->where('show_in_digital_menu', true);
        }

        $menuItems = $menuItemsQuery
            ->with(['recipes.product', 'variants.recipes.product'])
            ->get()
            ->filter(fn($item) => $item->available_quantity > 0)
            ->map(function ($item) {
                $variants = $item->variants->filter(fn($v) => $v->available_quantity > 0)->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'variant_name' => $variant->variant_name,
                        'price' => $variant->price,
                        'available_quantity' => $variant->available_quantity,
                        'description' => $variant->description,
                        'attributes' => $variant->attributes ?? [],
                    ];
                });

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->price,
                    'image_path' => $item->image_path,
                    'available_quantity' => $item->available_quantity,
                    'has_variants' => $variants->isNotEmpty(),
                    'variants' => $variants->values(),
                ];
            });

        // Get available simple products with stock
        // Filter by show_in_digital_menu if the column exists
        $simpleProductsQuery = SimpleProduct::where('is_available', true);

        if (\Schema::hasColumn('simple_products', 'show_in_digital_menu')) {
            $simpleProductsQuery->where('show_in_digital_menu', true);
        }

        $simpleProducts = $simpleProductsQuery
            ->with(['product', 'product.category', 'variants.recipes.product'])
            ->get()
            ->filter(function ($product) {
                // Productos con variantes están disponibles si tienen variantes
                if ($product->allows_variants && $product->variants->isNotEmpty()) {
                    return true;
                }
                // Productos sin variantes necesitan stock
                return $product->available_quantity > 0;
            })
            ->map(function ($product) {
                // Mapear variantes si existen
                $variants = collect();
                if ($product->allows_variants && $product->variants) {
                    $variants = $product->variants
                        ->filter(fn($v) => $v->is_available)
                        ->map(function ($variant) {
                            return [
                                'id' => $variant->id,
                                'variant_name' => $variant->variant_name,
                                'price' => $variant->price,
                                'available_quantity' => $variant->available_quantity,
                                'description' => $variant->description ?? null,
                                'attributes' => $variant->attributes ?? [],
                            ];
                        });
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'sale_price' => $product->sale_price,
                    'image_path' => $product->image_path,
                    'category' => $product->category,
                    'category_id' => $product->product->category_id ?? null,
                    'category_name' => $product->category ?? ($product->product->category->name ?? null),
                    'available_quantity' => $product->available_quantity,
                    'allows_variants' => $product->allows_variants,
                    'has_variants' => $variants->isNotEmpty(),
                    'variants' => $variants->values(),
                ];
            });

        $categories = Category::orderBy('name')->get();

        // Get available combos using ComboService
        $combos = $this->comboService->getCombosForMenu();

        // Get tables for dine-in (disponibles y ocupadas - pueden recibir múltiples pedidos)
        $availableTables = Table::whereIn('status', ['disponible', 'ocupada'])
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get()
            ->map(function ($table) {
                // Contar pedidos pendientes en esta mesa
                $pendingOrders = \App\Models\Sale::where('table_id', $table->id)
                    ->whereIn('status', ['pendiente', 'en_preparacion'])
                    ->count();

                return [
                    'id' => $table->id,
                    'table_number' => $table->table_number,
                    'capacity' => $table->capacity,
                    'status' => $table->status,
                    'pending_orders' => $pendingOrders,
                ];
            });

        return Inertia::render('DigitalMenu/Index', [
            'menuItems' => $menuItems->values(),
            'simpleProducts' => $simpleProducts->values(),
            'combos' => $combos->values(),
            'categories' => $categories,
            'availableTables' => $availableTables,
            'settings' => [
                'is_open' => $settings->isDigitalMenuOpen(),
                'closed_message' => $settings->digital_menu_closed_message,
                'welcome_message' => $settings->digital_menu_welcome_message,
                'min_order_amount' => $settings->min_order_amount,
                'estimated_prep_time' => $settings->estimated_prep_time,
                'delivery_methods' => $settings->getAvailableDeliveryMethods(),
                'whatsapp_number' => $settings->whatsapp_number,
                'country_code' => $settings->country_code ?? '+503',
                'timezone' => $settings->timezone ?? 'America/El_Salvador',
                'restaurant_name' => $settings->restaurant_name,
                'restaurant_phone' => $settings->restaurant_phone,
                'restaurant_address' => $settings->restaurant_address,
                'logo_path' => $settings->logo_path,
                // Branding colors
                'primary_color' => $settings->primary_color ?? '#f97316',
                'secondary_color' => $settings->secondary_color ?? '#ea580c',
                'accent_color' => $settings->accent_color ?? '#fb923c',
            ],
        ]);
    }
}
