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
     * Verificar si hay stock suficiente para una variante de producto simple
     */
    public function verifySimpleProductVariantStock(int $variantId, int $quantity): void
    {
        $variant = \App\Models\SimpleProductVariant::with('recipes.product', 'simpleProduct')->find($variantId);

        if (!$variant) {
            throw new \Exception("Variante de producto simple no encontrada con ID: {$variantId}");
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
     * Deducir inventario para variante de producto simple
     */
    public function deductSimpleProductVariantStock(SaleItem $saleItem): void
    {
        $variant = \App\Models\SimpleProductVariant::with('recipes.product', 'simpleProduct')->find($saleItem->simple_product_variant_id);

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

            Log::info('Stock deducido para variante de producto simple', [
                'product_id' => $recipe->product_id,
                'quantity' => $quantityNeeded,
                'variant_id' => $variant->id,
                'sale_id' => $saleItem->sale_id,
            ]);
        }
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
     * Deducir inventario para combo
     */
    public function deductComboStock(SaleItem $saleItem): void
    {
        $combo = \App\Models\Combo::with([
            'components.sellable',
            'components.options.sellable',
        ])->find($saleItem->combo_id);

        if (!$combo) {
            return;
        }

        $selections = $saleItem->combo_selections ?? [];

        foreach ($combo->components as $component) {
            // Determinar qué producto deducir
            $sellable = null;
            $variantId = null;

            if ($component->component_type === 'fixed') {
                // Componente fijo: usar el producto asociado
                $sellable = $component->sellable;
            } else {
                // Componente de elección: buscar la selección del cliente
                $selection = $selections[$component->id] ?? null;
                if ($selection && $selection['optionId']) {
                    $option = $component->options->firstWhere('id', $selection['optionId']);
                    if ($option) {
                        $sellable = $option->sellable;
                        $variantId = $selection['variantId'] ?? null;
                    }
                }
            }

            if (!$sellable) {
                continue;
            }

            // Deducir inventario según el tipo de producto
            $quantityMultiplier = $component->quantity * $saleItem->quantity;

            if ($sellable instanceof \App\Models\MenuItem) {
                // Cargar recetas con productos para MenuItem
                $sellable->load('recipes.product');

                // Deducir ingredientes del menu item
                foreach ($sellable->recipes as $recipe) {
                    if (!$recipe->product)
                        continue;

                    $quantityNeeded = $recipe->quantity_needed * $quantityMultiplier;

                    InventoryMovement::create([
                        'product_id' => $recipe->product_id,
                        'user_id' => $saleItem->sale->user_id,
                        'movement_type' => 'salida',
                        'quantity' => $quantityNeeded,
                        'unit_cost' => $recipe->product->unit_cost,
                        'total_cost' => $quantityNeeded * $recipe->product->unit_cost,
                        'reason' => 'venta_automatica',
                        'notes' => "Combo: {$combo->name} - {$sellable->name} x{$quantityMultiplier} - Ticket #{$saleItem->sale->sale_number}",
                        'movement_date' => now()->toDateString(),
                    ]);

                    $this->productRepository->updateStock(
                        $recipe->product_id,
                        $recipe->product->current_stock - $quantityNeeded
                    );
                }
            } elseif ($sellable instanceof \App\Models\SimpleProduct) {
                // Cargar producto base para SimpleProduct
                $sellable->load('product');

                // Deducir del producto base
                if ($sellable->product) {
                    $quantityNeeded = $sellable->cost_per_unit * $quantityMultiplier;

                    InventoryMovement::create([
                        'product_id' => $sellable->product_id,
                        'user_id' => $saleItem->sale->user_id,
                        'movement_type' => 'salida',
                        'quantity' => $quantityNeeded,
                        'unit_cost' => $sellable->product->unit_cost,
                        'total_cost' => $quantityNeeded * $sellable->product->unit_cost,
                        'reason' => 'venta_automatica',
                        'notes' => "Combo: {$combo->name} - {$sellable->name} x{$quantityMultiplier} - Ticket #{$saleItem->sale->sale_number}",
                        'movement_date' => now()->toDateString(),
                    ]);

                    $this->productRepository->updateStock(
                        $sellable->product_id,
                        $sellable->product->current_stock - $quantityNeeded
                    );
                }
            }

            Log::info('Stock deducido para componente de combo', [
                'combo_id' => $combo->id,
                'component_id' => $component->id,
                'sellable_type' => get_class($sellable),
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
     * Restaurar inventario de variante de producto simple
     */
    public function restoreSimpleProductVariantStock(SaleItem $saleItem): void
    {
        $variant = \App\Models\SimpleProductVariant::with('recipes.product')->find($saleItem->simple_product_variant_id);

        if (!$variant) {
            return;
        }

        foreach ($variant->recipes as $recipe) {
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
                'notes' => "Devolución: {$variant->variant_name} x{$saleItem->quantity} - Ticket #{$saleItem->sale->sale_number}",
                'movement_date' => now()->toDateString(),
            ]);

            // Restaurar stock
            $this->productRepository->updateStock(
                $recipe->product_id,
                $recipe->product->current_stock + $quantityToRestore
            );

            Log::info('Stock restaurado para variante de producto simple', [
                'product_id' => $recipe->product_id,
                'quantity' => $quantityToRestore,
                'variant_id' => $variant->id,
                'sale_id' => $saleItem->sale_id,
            ]);
        }
    }

    /**
     * Restaurar inventario de combo (usado en devoluciones)
     */
    public function restoreComboStock(SaleItem $saleItem): void
    {
        $combo = \App\Models\Combo::with([
            'components.sellable',
            'components.options.sellable',
        ])->find($saleItem->combo_id);

        if (!$combo) {
            return;
        }

        $selections = $saleItem->combo_selections ?? [];

        foreach ($combo->components as $component) {
            // Determinar qué producto restaurar
            $sellable = null;

            if ($component->component_type === 'fixed') {
                $sellable = $component->sellable;
            } else {
                $selection = $selections[$component->id] ?? null;
                if ($selection && $selection['optionId']) {
                    $option = $component->options->firstWhere('id', $selection['optionId']);
                    if ($option) {
                        $sellable = $option->sellable;
                    }
                }
            }

            if (!$sellable) {
                continue;
            }

            $quantityMultiplier = $component->quantity * $saleItem->quantity;

            if ($sellable instanceof \App\Models\MenuItem) {
                // Cargar recetas con productos para MenuItem
                $sellable->load('recipes.product');

                // Restaurar ingredientes del menu item
                foreach ($sellable->recipes as $recipe) {
                    if (!$recipe->product)
                        continue;

                    $quantityToRestore = $recipe->quantity_needed * $quantityMultiplier;

                    InventoryMovement::create([
                        'product_id' => $recipe->product_id,
                        'user_id' => auth()->id(),
                        'movement_type' => 'entrada',
                        'quantity' => $quantityToRestore,
                        'unit_cost' => $recipe->product->unit_cost,
                        'total_cost' => $quantityToRestore * $recipe->product->unit_cost,
                        'reason' => 'devolucion',
                        'notes' => "Devolución Combo: {$combo->name} - {$sellable->name} x{$quantityMultiplier} - Ticket #{$saleItem->sale->sale_number}",
                        'movement_date' => now()->toDateString(),
                    ]);

                    $this->productRepository->updateStock(
                        $recipe->product_id,
                        $recipe->product->current_stock + $quantityToRestore
                    );
                }
            } elseif ($sellable instanceof \App\Models\SimpleProduct) {
                // Cargar producto base para SimpleProduct
                $sellable->load('product');

                if ($sellable->product) {
                    $quantityToRestore = $sellable->cost_per_unit * $quantityMultiplier;

                    InventoryMovement::create([
                        'product_id' => $sellable->product_id,
                        'user_id' => auth()->id(),
                        'movement_type' => 'entrada',
                        'quantity' => $quantityToRestore,
                        'unit_cost' => $sellable->product->unit_cost,
                        'total_cost' => $quantityToRestore * $sellable->product->unit_cost,
                        'reason' => 'devolucion',
                        'notes' => "Devolución Combo: {$combo->name} - {$sellable->name} x{$quantityMultiplier} - Ticket #{$saleItem->sale->sale_number}",
                        'movement_date' => now()->toDateString(),
                    ]);

                    $this->productRepository->updateStock(
                        $sellable->product_id,
                        $sellable->product->current_stock + $quantityToRestore
                    );
                }
            }

            Log::info('Stock restaurado para componente de combo', [
                'combo_id' => $combo->id,
                'component_id' => $component->id,
                'sellable_type' => get_class($sellable),
                'sale_id' => $saleItem->sale_id,
            ]);
        }
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
