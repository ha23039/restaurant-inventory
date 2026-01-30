<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleService
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
        private ProductRepositoryInterface $productRepository,
        private InventoryService $inventoryService,
        private CashFlowService $cashFlowService,
        private CashRegisterService $cashRegisterService
    ) {
    }

    /**
     * Procesar una nueva venta con validación de stock y deducción automática
     */
    public function processSale(array $validatedData, int $userId): Sale
    {
        return DB::transaction(function () use ($validatedData, $userId) {
            $isFreeSale = $validatedData['is_free_sale'] ?? false;
            $action = $validatedData['action'] ?? 'complete'; // 'complete' o 'save_pending'
            $existingSaleId = $validatedData['existing_sale_id'] ?? null;

            // Si existe una venta pendiente, agregar items
            if ($existingSaleId) {
                return $this->addItemsToExistingSale($existingSaleId, $validatedData, $action);
            }

            // Para ventas libres, omitir validación de stock
            if (!$isFreeSale) {
                // Verificar disponibilidad de stock
                $this->verifyStockAvailability($validatedData['items']);
            }

            // Calcular totales
            $totals = $isFreeSale
                ? $this->calculateFreeSaleTotals($validatedData)
                : $this->calculateTotals($validatedData);

            // Obtener sesión de caja activa
            $cashRegisterSession = $this->cashRegisterService->getCurrentSession();

            // Determinar estado según acción
            $status = $action === 'save_pending' ? 'pendiente' : 'completada';

            // Crear venta usando repository
            $sale = $this->saleRepository->create([
                'user_id' => $userId,
                'cash_register_session_id' => $cashRegisterSession?->id,
                'table_id' => $validatedData['table_id'] ?? null,
                'sale_number' => $this->generateSaleNumber(),
                'customer_name' => $validatedData['customer_name'] ?? null,
                'notes' => $validatedData['notes'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'] ?? 0,
                'tax' => $totals['tax'] ?? 0,
                'total' => $totals['total'],
                'payment_method' => $validatedData['payment_method'] ?? 'efectivo',
                'status' => $status,
                'is_free_sale' => $isFreeSale,
                'free_sale_description' => $isFreeSale ? $validatedData['free_sale_description'] : null,
            ]);

            Log::info('Venta creada exitosamente', [
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'user_id' => $userId,
                'total' => $sale->total,
                'status' => $status,
                'is_free_sale' => $isFreeSale,
            ]);

            // Procesar items (si existen)
            // Nota: Para ventas libres completas no hay items, para ventas normales siempre hay items
            if (!$isFreeSale && isset($validatedData['items'])) {
                // Procesar items y deducir inventario solo para items no-free
                $this->processSaleItems($sale, $validatedData['items']);
            }

            // Actualizar estado de mesa si fue asignada
            if ($sale->table_id) {
                $this->updateTableStatus($sale);
            }

            // Registrar flujo de efectivo solo si está completada
            if ($status === 'completada') {
                $this->cashFlowService->recordSaleIncome($sale);
            }

            // 🍳 Crear orden de cocina automáticamente
            $this->createKitchenOrder($sale);

            return $sale->fresh(['saleItems', 'user', 'cashFlow', 'table', 'kitchenOrderState']);
        });
    }

    /**
     * Agregar items a una venta existente pendiente
     */
    public function addItemsToExistingSale(int $saleId, array $validatedData, string $action): Sale
    {
        $sale = Sale::with(['saleItems'])->findOrFail($saleId);

        // Verificar que la venta esté pendiente
        if ($sale->status !== 'pendiente') {
            throw new \Exception('Solo se pueden modificar ventas pendientes');
        }

        // Verificar stock de nuevos items
        if (isset($validatedData['items'])) {
            $this->verifyStockAvailability($validatedData['items']);

            // Procesar nuevos items
            $this->processSaleItems($sale, $validatedData['items']);
        }

        // Recalcular totales
        $newTotals = $this->recalculateSaleTotals($sale, $validatedData);

        // Actualizar venta
        $sale->update([
            'subtotal' => $newTotals['subtotal'],
            'discount' => $newTotals['discount'],
            'tax' => $newTotals['tax'],
            'total' => $newTotals['total'],
            'payment_method' => $validatedData['payment_method'] ?? $sale->payment_method,
            'status' => $action === 'save_pending' ? 'pendiente' : 'completada',
        ]);

        // Actualizar estado de mesa si fue asignada
        if ($sale->table_id) {
            $this->updateTableStatus($sale);
        }

        // Si se completa la venta, registrar flujo de efectivo
        if ($action === 'complete') {
            $this->cashFlowService->recordSaleIncome($sale);
        }

        // 🍳 Crear orden de cocina si no existe
        if (!$sale->kitchenOrderState) {
            $this->createKitchenOrder($sale);
        }

        Log::info('Items agregados a venta existente', [
            'sale_id' => $sale->id,
            'sale_number' => $sale->sale_number,
            'new_total' => $sale->total,
            'status' => $sale->status,
        ]);

        return $sale->fresh(['saleItems', 'user', 'cashFlow', 'table', 'kitchenOrderState']);
    }

    /**
     * Recalcular totales de venta basado en items existentes
     */
    protected function recalculateSaleTotals(Sale $sale, array $validatedData): array
    {
        $subtotal = $sale->saleItems()->sum(DB::raw('quantity * unit_price'));

        $discount = $validatedData['discount'] ?? $sale->discount;
        $tax = $validatedData['tax'] ?? $sale->tax;
        $total = $subtotal - $discount + $tax;

        return compact('subtotal', 'discount', 'tax', 'total');
    }

    /**
     * Verificar disponibilidad de stock
     */
    protected function verifyStockAvailability(array $items): void
    {
        foreach ($items as $item) {
            $productType = $item['product_type'] ?? 'menu';
            $quantity = $item['quantity'];

            // Saltar verificación para ventas libres y combos (combos verifican disponibilidad por componente)
            if ($productType === 'free' || $productType === 'combo') {
                continue;
            }

            if ($productType === 'menu') {
                // Verificar ingredientes del menu item
                $this->inventoryService->verifyMenuItemStock($item['id'], $quantity);
            } elseif ($productType === 'variant') {
                // Verificar ingredientes de la variante
                $this->inventoryService->verifyMenuItemVariantStock($item['id'], $quantity);
            } elseif ($productType === 'simple') {
                // Verificar stock del producto simple
                $this->inventoryService->verifySimpleProductStock($item['id'], $quantity);
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
     * Calcular totales para venta libre
     */
    protected function calculateFreeSaleTotals(array $data): array
    {
        $total = $data['free_sale_total'];
        $discount = $data['discount'] ?? 0;
        $tax = $data['tax'] ?? 0;

        // Para venta libre, el total ya está definido
        // Calculamos el subtotal trabajando hacia atrás
        $subtotal = $total + $discount - $tax;

        return compact('subtotal', 'discount', 'tax', 'total');
    }

    /**
     * Procesar items de venta y deducir inventario
     */
    protected function processSaleItems(Sale $sale, array $items): void
    {
        foreach ($items as $item) {
            $productType = $item['product_type'] ?? 'menu';

            // Crear registro de sale item
            $saleItemData = [
                'sale_id' => $sale->id,
                'quantity' => $item['quantity'],
                'product_type' => $productType,
            ];

            // Para ventas libres, usar campos específicos
            if ($productType === 'free') {
                $saleItemData['free_sale_name'] = $item['name'];
                $saleItemData['free_sale_price'] = $item['price'];
                $saleItemData['unit_price'] = $item['price'];
                $saleItemData['total_price'] = $item['quantity'] * $item['price'];
                $saleItemData['menu_item_id'] = null;
                $saleItemData['menu_item_variant_id'] = null;
                $saleItemData['simple_product_id'] = null;
            } elseif ($productType === 'combo') {
                // Para combos
                $saleItemData['combo_id'] = $item['id'];
                $saleItemData['combo_selections'] = $item['combo_selections'] ?? null;
                $saleItemData['unit_price'] = $item['unit_price'];
                $saleItemData['total_price'] = $item['quantity'] * $item['unit_price'];
                $saleItemData['menu_item_id'] = null;
                $saleItemData['menu_item_variant_id'] = null;
                $saleItemData['simple_product_id'] = null;
            } else {
                // Para productos regulares (menu, variant, simple)
                $saleItemData['menu_item_id'] = $productType === 'menu' ? $item['id'] : null;
                $saleItemData['menu_item_variant_id'] = $productType === 'variant' ? $item['id'] : null;
                $saleItemData['simple_product_id'] = $productType === 'simple' ? $item['id'] : null;
                $saleItemData['unit_price'] = $item['unit_price'];
                $saleItemData['total_price'] = $item['quantity'] * $item['unit_price'];
            }

            $saleItem = SaleItem::create($saleItemData);

            // Deducir inventario solo para productos regulares (no para ventas libres ni combos por ahora)
            if ($productType === 'menu') {
                $this->inventoryService->deductMenuItemStock($saleItem);
            } elseif ($productType === 'variant') {
                $this->inventoryService->deductMenuItemVariantStock($saleItem);
            } elseif ($productType === 'simple') {
                $this->inventoryService->deductSimpleProductStock($saleItem);
            } elseif ($productType === 'combo') {
                // Para combos, deducir inventario por cada componente
                $this->inventoryService->deductComboStock($saleItem);
            }

            Log::info('Item procesado', [
                'sale_id' => $sale->id,
                'product_type' => $productType,
                'quantity' => $item['quantity'],
                'is_free_sale' => $productType === 'free',
                'is_combo' => $productType === 'combo',
            ]);
        }
    }

    /**
     * Generar número único de venta
     */
    public function generateSaleNumber(): string
    {
        $date = now()->format('Ymd');
        $lastSale = $this->saleRepository->getLastSaleOfDay($date);

        if ($lastSale) {
            $lastNumber = (int) substr($lastSale->sale_number, -4);
            $sequence = $lastNumber + 1;
        } else {
            $sequence = 1;
        }

        return $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener ventas por rango de fechas
     */
    public function getSalesByDateRange(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate)
    {
        return $this->saleRepository->getByDateRange($startDate, $endDate);
    }

    /**
     * Buscar venta por número
     */
    public function findBySaleNumber(string $saleNumber): ?Sale
    {
        return $this->saleRepository->findBySaleNumber($saleNumber);
    }

    /**
     * Obtener ventas pendientes (órdenes activas)
     */
    public function getPendingSales()
    {
        return Sale::with([
            'saleItems.menuItem',
            'saleItems.simpleProduct',
            'saleItems.menuItemVariant.menuItem',  // Variantes con platillo padre
            'saleItems.combo',  // Combos
            'table',
            'user'
        ])
            ->where('status', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Actualizar estado de mesa según el estado de la venta
     */
    protected function updateTableStatus(Sale $sale): void
    {
        $table = \App\Models\Table::find($sale->table_id);

        if (!$table) {
            return;
        }

        if ($sale->status === 'pendiente') {
            // Mesa ocupada con orden pendiente
            $table->update([
                'status' => 'ocupada',
                'current_sale_id' => $sale->id,
                'last_occupied_at' => now(),
            ]);

            Log::info('Mesa ocupada', [
                'table_id' => $table->id,
                'table_number' => $table->table_number,
                'sale_id' => $sale->id,
            ]);
        } elseif ($sale->status === 'completada') {
            // Liberar mesa cuando se completa la venta
            $table->update([
                'status' => 'disponible',
                'current_sale_id' => null,
            ]);

            Log::info('Mesa liberada', [
                'table_id' => $table->id,
                'table_number' => $table->table_number,
                'sale_id' => $sale->id,
            ]);
        }
    }

    /**
     * Completar una venta pendiente sin agregar items adicionales
     * 
     * Para órdenes del menú digital, adopta la venta al cajero actual:
     * - Actualiza user_id al cajero que completa
     * - Asocia la sesión de caja del cajero
     * - Registra el flujo de caja correspondiente
     */
    public function completePendingSale(Sale $sale, ?string $paymentMethod = null): Sale
    {
        DB::beginTransaction();

        try {
            // Obtener sesión de caja del cajero actual
            $cashRegisterSession = $this->cashRegisterService->getCurrentSession();

            if (!$cashRegisterSession) {
                throw new \Exception('Debes abrir una caja antes de completar ventas.');
            }

            // Preparar datos de actualización
            $updateData = [
                'status' => 'completada',
                'user_id' => auth()->id(),
                'cash_register_session_id' => $cashRegisterSession->id,
            ];

            // Actualizar método de pago si se proporciona
            if ($paymentMethod) {
                $updateData['payment_method'] = $paymentMethod;
            }

            // Verificar si es una orden del menú digital para logging
            $wasDigitalOrder = $sale->source === 'digital_menu';
            $originalUserId = $sale->user_id;

            // Actualizar la venta
            $sale->update($updateData);

            // Registrar flujo de caja
            $this->cashFlowService->recordSaleIncome($sale);

            // Imprimir tickets (cocina y cliente) si están configurados
            if (config('thermal_printer.auto_print_kitchen')) {
                app(\App\Services\ThermalTicketService::class)->printKitchenOrder($sale);
            }

            if (config('thermal_printer.auto_print_customer')) {
                app(\App\Services\ThermalTicketService::class)->printCustomerReceipt($sale);
            }

            // Liberar mesa si estaba asignada
            if ($sale->table_id) {
                $this->releaseTable($sale);
            }

            DB::commit();

            Log::info('Venta pendiente completada', [
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'total' => $sale->total,
                'adopted_from_digital' => $wasDigitalOrder,
                'original_user_id' => $originalUserId,
                'new_user_id' => auth()->id(),
                'cash_register_session_id' => $cashRegisterSession->id,
            ]);

            return $sale->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al completar venta pendiente', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Crear orden de cocina automáticamente
     */
    protected function createKitchenOrder(Sale $sale): void
    {
        // Solo crear si la venta tiene items de menú o productos simples
        // (no crear para ventas libres sin items reales)
        if ($sale->saleItems()->count() === 0) {
            return;
        }

        // Crear estado de cocina
        \App\Models\KitchenOrderState::create([
            'sale_id' => $sale->id,
            'status' => 'nueva',
            'priority' => 0, // Prioridad normal por defecto
        ]);

        Log::info('Orden de cocina creada', [
            'sale_id' => $sale->id,
            'sale_number' => $sale->sale_number,
        ]);
    }

    /**
     * Liberar mesa asignada
     */
    protected function releaseTable(Sale $sale): void
    {
        $table = \App\Models\Table::find($sale->table_id);

        if ($table) {
            $table->update([
                'status' => 'disponible',
                'current_sale_id' => null,
            ]);

            Log::info('Mesa liberada tras completar venta', [
                'table_id' => $table->id,
                'table_number' => $table->table_number,
                'sale_id' => $sale->id,
            ]);
        }
    }
}
