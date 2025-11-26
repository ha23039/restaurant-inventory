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
    ) {}

    /**
     * Procesar una nueva venta con validación de stock y deducción automática
     */
    public function processSale(array $validatedData, int $userId): Sale
    {
        return DB::transaction(function () use ($validatedData, $userId) {
            $isFreeSale = $validatedData['is_free_sale'] ?? false;

            // Para ventas libres, omitir validación de stock
            if (! $isFreeSale) {
                // Verificar disponibilidad de stock
                $this->verifyStockAvailability($validatedData['items']);
            }

            // Calcular totales
            $totals = $isFreeSale
                ? $this->calculateFreeSaleTotals($validatedData)
                : $this->calculateTotals($validatedData);

            // Obtener sesión de caja activa
            $cashRegisterSession = $this->cashRegisterService->getCurrentSession();

            // Crear venta usando repository
            $sale = $this->saleRepository->create([
                'user_id' => $userId,
                'cash_register_session_id' => $cashRegisterSession?->id,
                'sale_number' => $this->generateSaleNumber(),
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'] ?? 0,
                'tax' => $totals['tax'] ?? 0,
                'total' => $totals['total'],
                'payment_method' => $validatedData['payment_method'],
                'status' => 'completada',
                'is_free_sale' => $isFreeSale,
                'free_sale_description' => $isFreeSale ? $validatedData['free_sale_description'] : null,
            ]);

            Log::info('Venta creada exitosamente', [
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'user_id' => $userId,
                'total' => $sale->total,
                'is_free_sale' => $isFreeSale,
            ]);

            // Procesar items (si existen)
            // Nota: Para ventas libres completas no hay items, para ventas normales siempre hay items
            if (! $isFreeSale && isset($validatedData['items'])) {
                // Procesar items y deducir inventario solo para items no-free
                $this->processSaleItems($sale, $validatedData['items']);
            }

            // Registrar flujo de efectivo
            $this->cashFlowService->recordSaleIncome($sale);

            return $sale->fresh(['saleItems', 'user', 'cashFlow']);
        });
    }

    /**
     * Verificar disponibilidad de stock
     */
    protected function verifyStockAvailability(array $items): void
    {
        foreach ($items as $item) {
            $productType = $item['product_type'] ?? 'menu';
            $quantity = $item['quantity'];

            // Saltar verificación para ventas libres (no afectan inventario)
            if ($productType === 'free') {
                continue;
            }

            if ($productType === 'menu') {
                // Verificar ingredientes del menu item
                $this->inventoryService->verifyMenuItemStock($item['id'], $quantity);
            } else {
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
                $saleItemData['simple_product_id'] = null;
            } else {
                // Para productos regulares
                $saleItemData['menu_item_id'] = $productType === 'menu' ? $item['id'] : null;
                $saleItemData['simple_product_id'] = $productType === 'simple' ? $item['id'] : null;
                $saleItemData['unit_price'] = $item['unit_price'];
                $saleItemData['total_price'] = $item['quantity'] * $item['unit_price'];
            }

            $saleItem = SaleItem::create($saleItemData);

            // Deducir inventario solo para productos regulares (no para ventas libres)
            if ($productType === 'menu') {
                $this->inventoryService->deductMenuItemStock($saleItem);
            } elseif ($productType === 'simple') {
                $this->inventoryService->deductSimpleProductStock($saleItem);
            }

            Log::info('Item procesado', [
                'sale_id' => $sale->id,
                'product_type' => $productType,
                'quantity' => $item['quantity'],
                'is_free_sale' => $productType === 'free',
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

        return $date.str_pad($sequence, 4, '0', STR_PAD_LEFT);
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
}
