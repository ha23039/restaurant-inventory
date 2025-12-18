<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\MenuItem;
use App\Models\SaleItem;
use App\Models\SimpleProduct;
use App\Repositories\Contracts\MenuItemRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SimpleProductRepositoryInterface;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private MenuItemRepositoryInterface $menuItemRepository,
        private SimpleProductRepositoryInterface $simpleProductRepository
    ) {
    }

    /**
     * Verificar si hay stock suficiente para un menu item
     */
    public function verifyMenuItemStock(int $menuItemId, int $quantity): void
    {
        if (!$this->menuItemRepository->hasSufficientIngredients($menuItemId, $quantity)) {
            $menuItem = $this->menuItemRepository->find($menuItemId);
            $available = $this->menuItemRepository->calculateAvailableQuantity($menuItemId);

            throw new \Exception(
                "Stock insuficiente para {$menuItem->name}. Disponible: {$available}, Requerido: {$quantity}"
            );
        }
    }

    /**
     * Verificar si hay stock suficiente para un producto simple
     */
    public function verifySimpleProductStock(int $simpleProductId, int $quantity): void
    {
        if (!$this->simpleProductRepository->hasSufficientStock($simpleProductId, $quantity)) {
            $simpleProduct = $this->simpleProductRepository->find($simpleProductId);
            $available = $this->simpleProductRepository->calculateAvailableQuantity($simpleProductId);

            throw new \Exception(
                "Stock insuficiente para {$simpleProduct->name}. Disponible: {$available}, Requerido: {$quantity}"
            );
        }
    }

    /**
     * Verificar si hay stock suficiente para una variante de menu item
     */
    public function verifyMenuItemVariantStock(int $variantId, int $quantity): void
    {
        $variant = \App\Models\MenuItemVariant::with('recipes.product', 'menuItem')->find($variantId);

        if (!$variant) {
            throw new \Exception("Variante no encontrada con ID: {$variantId}");
        }

        // Si la variante no tiene recetas, se considera siempre disponible
        if ($variant->recipes->isEmpty()) {
            return;
        }

        // Verificar stock de cada ingrediente
        foreach ($variant->recipes as $recipe) {
            if (!$recipe->product) {
                throw new \Exception("Ingrediente no encontrado para variante: {$variant->variant_name}");
            }

            $quantityNeeded = $recipe->quantity_needed * $quantity;
            $available = $recipe->product->current_stock;

            if ($available < $quantityNeeded) {
                throw new \Exception(
                    "Stock insuficiente de {$recipe->product->name} para {$variant->variant_name}. " .
                    "Disponible: {$available}, Requerido: {$quantityNeeded}"
                );
            }
        }
    }

    /**
     * Deducir inventario para menu item
     */
    public function deductMenuItemStock(SaleItem $saleItem): void
    {
        $menuItem = MenuItem::with('recipes.product')->find($saleItem->menu_item_id);

        if (!$menuItem) {
            return;
        }

        foreach ($menuItem->recipes as $recipe) {
            $quantityNeeded = $recipe->quantity_needed * $saleItem->quantity;

            // Crear movimiento de inventario
            InventoryMovement::create([
                'product_id' => $recipe->product_id,
                'user_id' => $saleItem->sale->user_id,
                'movement_type' => 'salida',
                'quantity' => $quantityNeeded,
                'unit_cost' => $recipe->product->unit_cost,
                'total_cost' => $quantityNeeded * $recipe->product->unit_cost,
                'reason' => 'venta_automatica',
                'notes' => "Venta: {$menuItem->name} x{$saleItem->quantity} - Ticket #{$saleItem->sale->sale_number}",
                'movement_date' => now()->toDateString(),
            ]);

            // Deducir stock del producto
            $this->productRepository->updateStock(
                $recipe->product_id,
                $recipe->product->current_stock - $quantityNeeded
            );

            Log::info('Stock deducido para menu item', [
                'product_id' => $recipe->product_id,
                'quantity' => $quantityNeeded,
                'sale_id' => $saleItem->sale_id,
            ]);
        }
    }

    /**
     * Deducir inventario para producto simple
     */
    public function deductSimpleProductStock(SaleItem $saleItem): void
    {
        $simpleProduct = SimpleProduct::with('product')->find($saleItem->simple_product_id);

        if (!$simpleProduct) {
            return;
        }

        $quantityNeeded = $simpleProduct->cost_per_unit * $saleItem->quantity;

        // Crear movimiento de inventario
        InventoryMovement::create([
            'product_id' => $simpleProduct->product_id,
            'user_id' => $saleItem->sale->user_id,
            'movement_type' => 'salida',
            'quantity' => $quantityNeeded,
            'unit_cost' => $simpleProduct->product->unit_cost,
            'total_cost' => $quantityNeeded * $simpleProduct->product->unit_cost,
            'reason' => 'venta_automatica',
            'notes' => "Venta: {$simpleProduct->name} x{$saleItem->quantity} - Ticket #{$saleItem->sale->sale_number}",
            'movement_date' => now()->toDateString(),
        ]);

        // Deducir stock del producto
        $this->productRepository->updateStock(
            $simpleProduct->product_id,
            $simpleProduct->product->current_stock - $quantityNeeded
        );

        Log::info('Stock deducido para producto simple', [
            'product_id' => $simpleProduct->product_id,
            'quantity' => $quantityNeeded,
            'sale_id' => $saleItem->sale_id,
        ]);
    }

    /**
     * Deducir inventario para variante de menu item
     */
    public function deductMenuItemVariantStock(SaleItem $saleItem): void
    {
        $variant = \App\Models\MenuItemVariant::with('recipes.product', 'menuItem')->find($saleItem->menu_item_variant_id);

        if (!$variant) {
            return;
        }

        foreach ($variant->recipes as $recipe) {
            $quantityNeeded = $recipe->quantity_needed * $saleItem->quantity;

            // Crear movimiento de inventario
            InventoryMovement::create([
                'product_id' => $recipe->product_id,
                'user_id' => $saleItem->sale->user_id,
                'movement_type' => 'salida',
                'quantity' => $quantityNeeded,
                'unit_cost' => $recipe->product->unit_cost,
                'total_cost' => $quantityNeeded * $recipe->product->unit_cost,
                'reason' => 'venta_automatica',
                'notes' => "Venta: {$variant->variant_name} x{$saleItem->quantity} - Ticket #{$saleItem->sale->sale_number}",
                'movement_date' => now()->toDateString(),
            ]);

            // Deducir stock del producto
            $this->productRepository->updateStock(
                $recipe->product_id,
                $recipe->product->current_stock - $quantityNeeded
            );

            Log::info('Stock deducido para variante', [
                'product_id' => $recipe->product_id,
                'quantity' => $quantityNeeded,
                'variant_id' => $variant->id,
                'sale_id' => $saleItem->sale_id,
            ]);
        }
    }

    /**
     * Restaurar inventario (usado en devoluciones)
     */
    public function restoreMenuItemStock(SaleItem $saleItem): void
    {
        $menuItem = MenuItem::with('recipes.product')->find($saleItem->menu_item_id);

        if (!$menuItem) {
            return;
        }

        foreach ($menuItem->recipes as $recipe) {
            $quantityToRestore = $recipe->quantity_needed * $saleItem->quantity;

            // Crear movimiento de inventario
            InventoryMovement::create([
                'product_id' => $recipe->product_id,
                'user_id' => auth()->id(),
                'movement_type' => 'entrada',
                'quantity' => $quantityToRestore,
                'unit_cost' => $recipe->product->unit_cost,
                'total_cost' => $quantityToRestore * $recipe->product->unit_cost,
                'reason' => 'devolucion',
                'notes' => "Devolución: {$menuItem->name} x{$saleItem->quantity} - Ticket #{$saleItem->sale->sale_number}",
                'movement_date' => now()->toDateString(),
            ]);

            // Restaurar stock
            $this->productRepository->updateStock(
                $recipe->product_id,
                $recipe->product->current_stock + $quantityToRestore
            );
        }
    }

    /**
     * Restaurar inventario de producto simple
     */
    public function restoreSimpleProductStock(SaleItem $saleItem): void
    {
        $simpleProduct = SimpleProduct::with('product')->find($saleItem->simple_product_id);

        if (!$simpleProduct) {
            return;
        }

        $quantityToRestore = $simpleProduct->cost_per_unit * $saleItem->quantity;

        // Crear movimiento de inventario
        InventoryMovement::create([
            'product_id' => $simpleProduct->product_id,
            'user_id' => auth()->id(),
            'movement_type' => 'entrada',
            'quantity' => $quantityToRestore,
            'unit_cost' => $simpleProduct->product->unit_cost,
            'total_cost' => $quantityToRestore * $simpleProduct->product->unit_cost,
            'reason' => 'devolucion',
            'notes' => "Devolución: {$simpleProduct->name} x{$saleItem->quantity} - Ticket #{$saleItem->sale->sale_number}",
            'movement_date' => now()->toDateString(),
        ]);

        // Restaurar stock
        $this->productRepository->updateStock(
            $simpleProduct->product_id,
            $simpleProduct->product->current_stock + $quantityToRestore
        );
    }

    /**
     * Obtener productos con stock bajo
     */
    public function getLowStockProducts()
    {
        return $this->productRepository->getLowStockProducts();
    }

    /**
     * Obtener productos próximos a expirar
     */
    public function getExpiringSoonProducts(int $days = 7)
    {
        return $this->productRepository->getExpiringSoonProducts($days);
    }
}
