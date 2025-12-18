<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Repositories\Contracts\MenuItemRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;

class MenuItemService
{
    public function __construct(
        private MenuItemRepositoryInterface $menuItemRepository,
        private ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * Obtener menu items disponibles con stock calculado
     */
    public function getAvailableMenuItems()
    {
        $menuItems = $this->menuItemRepository->getAvailableItems();

        return $menuItems->map(function ($item) {
            $item->available_quantity = $this->calculateAvailability($item);
            $item->is_in_stock = $item->available_quantity > 0;

            return $item;
        });
    }

    /**
     * Calcular disponibilidad de un menu item
     * Para productos con variantes, verifica si al menos una variante tiene stock
     */
    public function calculateAvailability(MenuItem $menuItem): int
    {
        // Si el producto tiene variantes, la disponibilidad se basa en sus variantes
        if ($menuItem->has_variants) {
            // Cargar variantes si no están cargadas
            if (!$menuItem->relationLoaded('variants')) {
                $menuItem->load([
                    'variants' => function ($query) {
                        $query->where('is_available', true)->with('recipes.product');
                    }
                ]);
            }

            // Si tiene variantes disponibles, marcar como disponible
            $availableVariantsCount = $menuItem->variants->filter(function ($variant) {
                return $variant->is_available && $variant->available_quantity > 0;
            })->count();

            // Retornar cantidad de variantes disponibles (999 es un placeholder para "múltiples disponibles")
            return $availableVariantsCount > 0 ? 999 : 0;
        }

        // Para productos sin variantes, usar lógica normal basada en recetas
        return $this->menuItemRepository->calculateAvailableQuantity($menuItem->id);
    }

    /**
     * Obtener menu items con stock bajo
     */
    public function getLowStockItems(int $threshold = 5)
    {
        return $this->menuItemRepository->getItemsWithLowStock($threshold);
    }

    /**
     * Verificar si un menu item puede ser vendido
     */
    public function canBeSold(int $menuItemId, int $quantity = 1): array
    {
        $menuItem = $this->menuItemRepository->find($menuItemId);

        if (!$menuItem) {
            return [
                'can_sell' => false,
                'reason' => 'Menu item no encontrado',
            ];
        }

        if (!$menuItem->is_available) {
            return [
                'can_sell' => false,
                'reason' => 'Menu item no disponible',
            ];
        }

        $available = $this->menuItemRepository->calculateAvailableQuantity($menuItemId);

        if ($available < $quantity) {
            return [
                'can_sell' => false,
                'reason' => "Stock insuficiente. Disponible: {$available}",
                'available_quantity' => $available,
            ];
        }

        return [
            'can_sell' => true,
            'available_quantity' => $available,
        ];
    }

    /**
     * Buscar menu items
     */
    public function search(string $query)
    {
        return $this->menuItemRepository->search($query);
    }

    /**
     * Obtener menu items por categoría
     */
    public function getByCategory(int $categoryId)
    {
        return $this->menuItemRepository->getByCategory($categoryId);
    }

    /**
     * Obtener todos los menu items con sus recetas
     */
    public function getAllWithRecipes()
    {
        return $this->menuItemRepository->getAllWithRecipes();
    }
}
