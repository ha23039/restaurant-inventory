<?php

namespace App\Http\Controllers\DigitalMenu;

use App\Http\Controllers\Controller;
use App\Models\BusinessSettings;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\SimpleProduct;
use App\Models\Table;
use Inertia\Inertia;

class MenuController extends Controller
{
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
        $menuItems = MenuItem::where('is_available', true)
            ->where('is_service', false)
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
        $simpleProducts = SimpleProduct::where('is_available', true)
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

        // Get available tables for dine-in
        $availableTables = Table::where('status', 'disponible')
            ->orderBy('table_number')
            ->get()
            ->map(function ($table) {
                return [
                    'id' => $table->id,
                    'table_number' => $table->table_number,
                    'capacity' => $table->capacity,
                ];
            });

        return Inertia::render('DigitalMenu/Index', [
            'menuItems' => $menuItems->values(),
            'simpleProducts' => $simpleProducts->values(),
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
                'restaurant_name' => $settings->restaurant_name,
                'restaurant_phone' => $settings->restaurant_phone,
                'restaurant_address' => $settings->restaurant_address,
                'logo_path' => $settings->logo_path,
            ],
        ]);
    }
}
