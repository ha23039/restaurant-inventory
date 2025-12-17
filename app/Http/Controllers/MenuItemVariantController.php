<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\MenuItemVariant;
use Illuminate\Http\Request;

class MenuItemVariantController extends Controller
{
    /**
     * Store a newly created variant
     */
    public function store(Request $request, MenuItem $menuItem)
    {
        $this->authorize('update', $menuItem);

        $validated = $request->validate([
            'variant_name' => 'required|string|max:255',
            'variant_sku' => 'nullable|string|max:100|unique:menu_item_variants,variant_sku',
            'price' => 'required|numeric|min:0.01',
            'attributes' => 'nullable|array',
            'is_available' => 'boolean',
            'display_order' => 'integer|min:0',
        ]);

        $variant = $menuItem->variants()->create($validated);

        // Mark menu item as having variants
        if (!$menuItem->has_variants) {
            $menuItem->update(['has_variants' => true]);
        }

        return back()->with('success', 'Variante creada exitosamente');
    }

    /**
     * Update the specified variant
     */
    public function update(Request $request, MenuItemVariant $variant)
    {
        $this->authorize('update', $variant->menuItem);

        $validated = $request->validate([
            'variant_name' => 'required|string|max:255',
            'variant_sku' => 'nullable|string|max:100|unique:menu_item_variants,variant_sku,' . $variant->id,
            'price' => 'required|numeric|min:0.01',
            'attributes' => 'nullable|array',
            'is_available' => 'boolean',
            'display_order' => 'integer|min:0',
        ]);

        $variant->update($validated);

        return back()->with('success', 'Variante actualizada exitosamente');
    }

    /**
     * Remove the specified variant
     */
    public function destroy(MenuItemVariant $variant)
    {
        $this->authorize('delete', $variant->menuItem);

        // Check if variant has been sold
        if ($variant->saleItems()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar una variante que ya se ha vendido']);
        }

        $menuItem = $variant->menuItem;
        $variant->delete();

        // Update has_variants flag if no variants left
        if ($menuItem->variants()->count() === 0) {
            $menuItem->update(['has_variants' => false]);
        }

        return back()->with('success', 'Variante eliminada exitosamente');
    }

    /**
     * Toggle availability status
     */
    public function toggleAvailability(MenuItemVariant $variant)
    {
        $this->authorize('update', $variant->menuItem);

        $variant->update([
            'is_available' => !$variant->is_available,
        ]);

        return back()->with('success', 'Disponibilidad actualizada');
    }
}
