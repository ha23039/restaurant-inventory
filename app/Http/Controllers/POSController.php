<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\MenuItem;
use App\Models\SimpleProduct;
use App\Models\CashFlow;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
public function index()
    {
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

    public function store(Request $request)
    {
        // Debug para ver qué datos llegan
        \Log::info('Datos recibidos en POS:', $request->all());

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required',
            'items.*.product_type' => 'sometimes|in:menu,simple',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:efectivo,tarjeta,transferencia,mixto',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Verificar disponibilidad antes de procesar
            foreach ($validated['items'] as $item) {
                $productType = $item['product_type'] ?? 'menu';
                
                if ($productType === 'menu') {
                    $menuItem = MenuItem::with('recipes.product')->find($item['id']);
                    if (!$menuItem) {
                        throw new \Exception("Platillo no encontrado");
                    }
                    $availableQty = $this->calculateAvailableQuantity($menuItem);
                    
                    if ($availableQty < $item['quantity']) {
                        throw new \Exception("No hay suficiente stock para {$menuItem->name}. Disponible: {$availableQty}");
                    }
                } else {
                    $simpleProduct = SimpleProduct::with('product')->find($item['id']);
                    if (!$simpleProduct) {
                        throw new \Exception("Producto no encontrado");
                    }
                    
                    if ($simpleProduct->available_quantity < $item['quantity']) {
                        throw new \Exception("No hay suficiente stock para {$simpleProduct->name}. Disponible: {$simpleProduct->available_quantity}");
                    }
                }
            }

            // Calcular totales
            $subtotal = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            $discount = $validated['discount'] ?? 0;
            $tax = $validated['tax'] ?? 0;
            $total = $subtotal - $discount + $tax;

            // Crear venta
            $sale = Sale::create([
                'user_id' => auth()->id(),
                'sale_number' => $this->generateSaleNumber(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'status' => 'completada',
            ]);

            \Log::info('Venta creada exitosamente:', ['sale_id' => $sale->id, 'sale_number' => $sale->sale_number]);

            // Crear items de venta y procesar inventario automáticamente
            foreach ($validated['items'] as $item) {
                $productType = $item['product_type'] ?? 'menu';
                
                if ($productType === 'menu') {
                    // Procesar item del menú
                    $saleItem = SaleItem::create([
                        'sale_id' => $sale->id,
                        'menu_item_id' => $item['id'],
                        'simple_product_id' => null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price'],
                        'product_type' => 'menu',
                    ]);

                    // Automatización para items del menú
                    $this->processMenuItemInventoryDeduction($saleItem);
                    \Log::info('Item del menú procesado:', ['menu_item_id' => $item['id']]);
                } else {
                    // Procesar producto simple
                    $saleItem = SaleItem::create([
                        'sale_id' => $sale->id,
                        'menu_item_id' => null,
                        'simple_product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price'],
                        'product_type' => 'simple',
                    ]);

                    // Automatización para productos simples
                    $this->processSimpleProductInventoryDeduction($saleItem);
                    \Log::info('Producto simple procesado:', ['simple_product_id' => $item['id']]);
                }
            }

            // COMENTAR FLUJO DE CAJA TEMPORALMENTE HASTA CREAR LA TABLA
            
            CashFlow::create([
                'user_id' => auth()->id(),
                'sale_id' => $sale->id,
                'type' => 'entrada',
                'category' => 'ventas',
                'amount' => $total,
                'description' => "Venta #{$sale->sale_number}",
                'flow_date' => now()->toDateString(),
            ]);
            

            DB::commit();
            \Log::info('Transacción completada exitosamente');

            return redirect()->route('sales.show', $sale)
                           ->with('success', 'Venta procesada exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error en venta: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    // MÉTODO CLAVE: Calcular cuántos platillos se pueden hacer con el stock actual
    private function calculateAvailableQuantity(MenuItem $menuItem)
    {
        if ($menuItem->recipes->isEmpty()) {
            return 999; // Si no tiene receta, asumimos disponible
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

    // MÉTODO CLAVE: Automatización - Descontar inventario por item del menú
    private function processMenuItemInventoryDeduction(SaleItem $saleItem)
    {
        $menuItem = $saleItem->menuItem()->with('recipes.product')->first();

        foreach ($menuItem->recipes as $recipe) {
            $totalQuantityNeeded = $recipe->quantity_needed * $saleItem->quantity;
            
            // Crear movimiento de inventario
            InventoryMovement::create([
                'product_id' => $recipe->product_id,
                'user_id' => auth()->id(),
                'movement_type' => 'salida',
                'quantity' => $totalQuantityNeeded,
                'unit_cost' => $recipe->product->unit_cost,
                'total_cost' => $totalQuantityNeeded * $recipe->product->unit_cost,
                'reason' => 'venta_automatica',
                'notes' => "Venta automática: {$menuItem->name} (Qty: {$saleItem->quantity}) - Ticket #{$saleItem->sale->sale_number}",
                'movement_date' => now()->toDateString(),
            ]);

            // Actualizar stock del producto
            $recipe->product->decrement('current_stock', $totalQuantityNeeded);
        }
    }

    // MÉTODO CLAVE: Automatización - Descontar inventario por producto simple
    private function processSimpleProductInventoryDeduction(SaleItem $saleItem)
    {
        $simpleProduct = $saleItem->simpleProduct()->with('product')->first();
        $totalQuantityNeeded = $simpleProduct->cost_per_unit * $saleItem->quantity;
        
        // Crear movimiento de inventario
        InventoryMovement::create([
            'product_id' => $simpleProduct->product_id,
            'user_id' => auth()->id(),
            'movement_type' => 'salida',
            'quantity' => $totalQuantityNeeded,
            'unit_cost' => $simpleProduct->product->unit_cost,
            'total_cost' => $totalQuantityNeeded * $simpleProduct->product->unit_cost,
            'reason' => 'venta_automatica',
            'notes' => "Venta individual: {$simpleProduct->name} (Qty: {$saleItem->quantity}) - Ticket #{$saleItem->sale->sale_number}",
            'movement_date' => now()->toDateString(),
        ]);

        // Actualizar stock del producto
        $simpleProduct->product->decrement('current_stock', $totalQuantityNeeded);
    }

    // Generar número de venta único
    private function generateSaleNumber()
    {
        $date = now()->format('Ymd');
        $count = Sale::whereDate('created_at', today())->count() + 1;
        return $date . sprintf('%04d', $count);
    }
}