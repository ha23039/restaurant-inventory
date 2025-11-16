<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\MenuItem;
use App\Models\SimpleProduct;
use App\Models\CashFlow;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Http\Requests\ProcessSaleRequest;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function index()
    {
        $this->authorize('processSale', \App\Models\Sale::class);

        // Platillos del menú (combos)
        $menuItems = MenuItem::where('is_available', true)
                           ->with(['recipes.product'])
                           ->get();

        $menuItems->each(function ($item) {
            $item->available_quantity = $this->calculateAvailableQuantity($item);
            $item->is_in_stock = $item->available_quantity > 0;
            $item->product_type = 'menu'; // Identificador
        });

        // Productos simples (bebidas individuales, extras) - FORZAR CARGA DE RELACIONES
        $simpleProducts = SimpleProduct::where('is_available', true)
                                     ->with('product') // Cargar relación
                                     ->get();

        $simpleProducts->each(function ($item) {
            $item->product_type = 'simple'; // Identificador
            
            // Agregar propiedades para compatibilidad con el frontend
            $item->price = $item->sale_price;
            
            // FORZAR recalcular disponibilidad
            if ($item->product) {
                $currentStock = floatval($item->product->current_stock);
                $costPerUnit = floatval($item->cost_per_unit);
                $available = $costPerUnit > 0 ? floor($currentStock / $costPerUnit) : 0;
                
                $item->available_quantity = $available;
                $item->is_in_stock = $available > 0;
            } else {
                $item->available_quantity = 0;
                $item->is_in_stock = false;
            }
        });

        \Log::info('POSController@index - Productos simples:',
            $simpleProducts->map(function($item) {
                return [
                    'name' => $item->name,
                    'stock_base' => $item->product?->current_stock,
                    'available_quantity' => $item->available_quantity,
                    'is_in_stock' => $item->is_in_stock
                ];
            })->toArray()
        );

        return Inertia::render('Sales/POS', [
            'menu_items' => $menuItems,
            'simple_products' => $simpleProducts,
        ]);
    }

    public function store(ProcessSaleRequest $request, SaleService $saleService)
    {
        try {
            $sale = $saleService->processSale($request->validated(), auth()->id());

            return redirect()->route('sales.show', $sale)
                           ->with('success', 'Venta procesada exitosamente');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // Calcular cuántos platillos se pueden hacer con el stock actual
    private function calculateAvailableQuantity(MenuItem $menuItem)
    {
        if ($menuItem->recipes->isEmpty()) {
            return 999;
        }

        $minQuantity = PHP_INT_MAX;

        foreach ($menuItem->recipes as $recipe) {
            $product = $recipe->product;
            $neededQuantity = $recipe->quantity_needed;

            if ($neededQuantity > 0) {
                $possibleQuantity = floor($product->current_stock / $neededQuantity);
                $minQuantity = min($minQuantity, $possibleQuantity);
            }
        }

        return max(0, $minQuantity);
    }
}