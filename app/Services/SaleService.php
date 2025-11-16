<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\MenuItem;
use App\Models\SimpleProduct;
use App\Models\CashFlow;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleService
{
    /**
     * Procesar una nueva venta con validación de stock y deducción automática
     */
    public function processSale(array $validatedData, int $userId): Sale
    {
        DB::beginTransaction();

        try {
            // Verificar disponibilidad con locks pesimistas
            $this->verifyStockAvailability($validatedData['items']);

            // Calcular totales
            $totals = $this->calculateTotals($validatedData);

            // Crear venta
            $sale = Sale::create([
                'user_id' => $userId,
                'sale_number' => $this->generateSaleNumber(),
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
                'payment_method' => $validatedData['payment_method'],
                'status' => 'completada',
            ]);

            Log::info('Venta creada exitosamente', [
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'user_id' => $userId,
                'total' => $sale->total,
                'payment_method' => $sale->payment_method,
                'items_count' => count($validatedData['items']),
                'ip' => request()->ip(),
                'timestamp' => now()->toIso8601String(),
            ]);

            // Procesar items y deducir inventario
            $this->processSaleItems($sale, $validatedData['items']);

            // Registrar flujo de efectivo
            $this->recordCashFlow($sale);

            DB::commit();

            Log::info('Transacción de venta completada', [
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'duration_ms' => (microtime(true) - LARAVEL_START) * 1000,
            ]);

            return $sale;

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error procesando venta', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'items_count' => count($validatedData['items'] ?? []),
                'payment_method' => $validatedData['payment_method'] ?? null,
                'ip' => request()->ip(),
                'timestamp' => now()->toIso8601String(),
            ]);

            throw $e;
        }
    }

    /**
     * Verificar disponibilidad de stock con locks pesimistas
     */
    protected function verifyStockAvailability(array $items): void
    {
        foreach ($items as $item) {
            $productType = $item['product_type'] ?? 'menu';

            if ($productType === 'menu') {
                $menuItem = MenuItem::with(['recipes' => function($query) {
                    $query->with(['product' => function($productQuery) {
                        $productQuery->lockForUpdate();
                    }]);
                }])->lockForUpdate()->find($item['id']);

                if (!$menuItem) {
                    throw new \Exception("Platillo no encontrado");
                }

                $availableQty = $this->calculateMenuItemAvailability($menuItem);

                if ($availableQty < $item['quantity']) {
                    throw new \Exception("No hay suficiente stock para {$menuItem->name}. Disponible: {$availableQty}");
                }
            } else {
                $simpleProduct = SimpleProduct::with(['product' => function($query) {
                    $query->lockForUpdate();
                }])->lockForUpdate()->find($item['id']);

                if (!$simpleProduct) {
                    throw new \Exception("Producto no encontrado");
                }

                if ($simpleProduct->available_quantity < $item['quantity']) {
                    throw new \Exception("No hay suficiente stock para {$simpleProduct->name}. Disponible: {$simpleProduct->available_quantity}");
                }
            }
        }
    }

    /**
     * Calcular totales de la venta
     */
    protected function calculateTotals(array $data): array
    {
        $subtotal = collect($data['items'])->sum(function ($item) {
            return $item['quantity'] * $item['unit_price'];
        });

        $discount = $data['discount'] ?? 0;
        $tax = $data['tax'] ?? 0;
        $total = $subtotal - $discount + $tax;

        return compact('subtotal', 'discount', 'tax', 'total');
    }

    /**
     * Procesar items de venta y deducir inventario
     */
    protected function processSaleItems(Sale $sale, array $items): void
    {
        foreach ($items as $item) {
            $productType = $item['product_type'] ?? 'menu';

            if ($productType === 'menu') {
                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'menu_item_id' => $item['id'],
                    'simple_product_id' => null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'product_type' => 'menu',
                ]);

                $this->processMenuItemInventoryDeduction($saleItem);

                Log::info('Item del menú procesado', [
                    'sale_id' => $sale->id,
                    'menu_item_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'total_price' => $saleItem->total_price,
                ]);
            } else {
                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'menu_item_id' => null,
                    'simple_product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'product_type' => 'simple',
                ]);

                $this->processSimpleProductInventoryDeduction($saleItem);

                Log::info('Producto simple procesado', [
                    'sale_id' => $sale->id,
                    'simple_product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'total_price' => $saleItem->total_price,
                ]);
            }
        }
    }

    /**
     * Deducir inventario para menu item
     */
    protected function processMenuItemInventoryDeduction(SaleItem $saleItem): void
    {
        $menuItem = $saleItem->menuItem()->with('recipes.product')->first();

        foreach ($menuItem->recipes as $recipe) {
            $totalQuantityNeeded = $recipe->quantity_needed * $saleItem->quantity;

            InventoryMovement::create([
                'product_id' => $recipe->product_id,
                'user_id' => $saleItem->sale->user_id,
                'movement_type' => 'salida',
                'quantity' => $totalQuantityNeeded,
                'unit_cost' => $recipe->product->unit_cost,
                'total_cost' => $totalQuantityNeeded * $recipe->product->unit_cost,
                'reason' => 'venta_automatica',
                'notes' => "Venta automática: {$menuItem->name} (Qty: {$saleItem->quantity}) - Ticket #{$saleItem->sale->sale_number}",
                'movement_date' => now()->toDateString(),
            ]);

            $recipe->product->decrement('current_stock', $totalQuantityNeeded);
        }
    }

    /**
     * Deducir inventario para producto simple
     */
    protected function processSimpleProductInventoryDeduction(SaleItem $saleItem): void
    {
        $simpleProduct = $saleItem->simpleProduct()->with('product')->first();
        $totalQuantityNeeded = $simpleProduct->cost_per_unit * $saleItem->quantity;

        InventoryMovement::create([
            'product_id' => $simpleProduct->product_id,
            'user_id' => $saleItem->sale->user_id,
            'movement_type' => 'salida',
            'quantity' => $totalQuantityNeeded,
            'unit_cost' => $simpleProduct->product->unit_cost,
            'total_cost' => $totalQuantityNeeded * $simpleProduct->product->unit_cost,
            'reason' => 'venta_automatica',
            'notes' => "Venta individual: {$simpleProduct->name} (Qty: {$saleItem->quantity}) - Ticket #{$saleItem->sale->sale_number}",
            'movement_date' => now()->toDateString(),
        ]);

        $simpleProduct->product->decrement('current_stock', $totalQuantityNeeded);
    }

    /**
     * Registrar flujo de efectivo
     */
    protected function recordCashFlow(Sale $sale): void
    {
        CashFlow::create([
            'user_id' => $sale->user_id,
            'sale_id' => $sale->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => $sale->total,
            'description' => "Venta #{$sale->sale_number}",
            'flow_date' => now()->toDateString(),
        ]);
    }

    /**
     * Calcular cantidad disponible de menu item
     */
    protected function calculateMenuItemAvailability(MenuItem $menuItem): int
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

    /**
     * Generar número único de venta
     */
    protected function generateSaleNumber(): string
    {
        $date = now()->format('Ymd');
        $count = Sale::whereDate('created_at', today())->count() + 1;
        return $date . sprintf('%04d', $count);
    }
}
