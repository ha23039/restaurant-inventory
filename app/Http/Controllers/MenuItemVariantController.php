<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\MenuItemVariant;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'recipes' => 'nullable|array',
            'recipes.*.product_id' => 'required_with:recipes|exists:products,id',
            'recipes.*.quantity_needed' => 'required_with:recipes|numeric|min:0.001',
            'recipes.*.unit' => 'required_with:recipes|string|in:kg,lt,pcs,g,ml',
        ]);

        DB::beginTransaction();
        try {
            // Crear la variante
            $variant = $menuItem->variants()->create([
                'variant_name' => $validated['variant_name'],
                'variant_sku' => $validated['variant_sku'] ?? null,
                'price' => $validated['price'],
                'attributes' => $validated['attributes'] ?? null,
                'is_available' => $validated['is_available'] ?? true,
                'display_order' => $validated['display_order'] ?? 0,
            ]);

            // Crear las recetas si se proporcionaron
            if (!empty($validated['recipes'])) {
                foreach ($validated['recipes'] as $recipeData) {
                    Recipe::create([
                        'menu_item_variant_id' => $variant->id,
                        'product_id' => $recipeData['product_id'],
                        'quantity_needed' => $recipeData['quantity_needed'],
                        'unit' => $recipeData['unit'],
                    ]);
                }
            }

            // Mark menu item as having variants
            if (!$menuItem->has_variants) {
                $menuItem->update(['has_variants' => true]);
            }

            DB::commit();
            return back()->with('success', 'Variante creada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear la variante: ' . $e->getMessage()]);
        }
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
            'recipes' => 'nullable|array',
            'recipes.*.id' => 'nullable|exists:recipes,id',
            'recipes.*.product_id' => 'required_with:recipes|exists:products,id',
            'recipes.*.quantity_needed' => 'required_with:recipes|numeric|min:0.001',
            'recipes.*.unit' => 'required_with:recipes|string|in:kg,lt,pcs,g,ml',
        ]);

        DB::beginTransaction();
        try {
            // Actualizar datos de la variante
            $variant->update([
                'variant_name' => $validated['variant_name'],
                'variant_sku' => $validated['variant_sku'] ?? null,
                'price' => $validated['price'],
                'attributes' => $validated['attributes'] ?? null,
                'is_available' => $validated['is_available'] ?? true,
                'display_order' => $validated['display_order'] ?? 0,
            ]);

            // Sincronizar recetas
            $newRecipeIds = [];
            if (!empty($validated['recipes'])) {
                foreach ($validated['recipes'] as $recipeData) {
                    if (!empty($recipeData['id'])) {
                        // Actualizar receta existente
                        $recipe = Recipe::find($recipeData['id']);
                        if ($recipe && $recipe->menu_item_variant_id === $variant->id) {
                            $recipe->update([
                                'product_id' => $recipeData['product_id'],
                                'quantity_needed' => $recipeData['quantity_needed'],
                                'unit' => $recipeData['unit'],
                            ]);
                            $newRecipeIds[] = $recipe->id;
                        }
                    } else {
                        // Crear nueva receta
                        $recipe = Recipe::create([
                            'menu_item_variant_id' => $variant->id,
                            'product_id' => $recipeData['product_id'],
                            'quantity_needed' => $recipeData['quantity_needed'],
                            'unit' => $recipeData['unit'],
                        ]);
                        $newRecipeIds[] = $recipe->id;
                    }
                }
            }

            // Eliminar recetas que ya no están en la lista
            $variant->recipes()
                ->whereNotIn('id', $newRecipeIds)
                ->delete();

            DB::commit();
            return back()->with('success', 'Variante actualizada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar la variante: ' . $e->getMessage()]);
        }
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
