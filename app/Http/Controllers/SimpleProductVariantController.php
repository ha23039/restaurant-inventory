<?php

namespace App\Http\Controllers;

use App\Models\SimpleProduct;
use App\Models\SimpleProductVariant;
use App\Models\SimpleProductVariantRecipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SimpleProductVariantController extends Controller
{
    /**
     * Store a newly created variant
     */
    public function store(Request $request, SimpleProduct $simpleProduct)
    {
        $validated = $request->validate([
            'variant_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0.01',
            'attributes' => 'nullable|array',
            'is_available' => 'boolean',
            'recipes' => 'nullable|array',
            'recipes.*.product_id' => 'required_with:recipes|exists:products,id',
            'recipes.*.quantity_needed' => 'required_with:recipes|numeric|min:0.001',
            'recipes.*.unit' => 'required_with:recipes|string|in:kg,lt,pcs,g,ml',
        ]);

        DB::beginTransaction();
        try {
            // Crear la variante
            $variant = $simpleProduct->variants()->create([
                'variant_name' => $validated['variant_name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'attributes' => $validated['attributes'] ?? null,
                'is_available' => $validated['is_available'] ?? true,
                'restaurant_id' => 1,
            ]);

            // Crear las recetas si se proporcionaron
            if (!empty($validated['recipes'])) {
                foreach ($validated['recipes'] as $recipeData) {
                    SimpleProductVariantRecipe::create([
                        'simple_product_variant_id' => $variant->id,
                        'product_id' => $recipeData['product_id'],
                        'quantity_needed' => $recipeData['quantity_needed'],
                        'unit' => $recipeData['unit'],
                        'restaurant_id' => 1,
                    ]);
                }
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
    public function update(Request $request, SimpleProductVariant $variant)
    {
        $validated = $request->validate([
            'variant_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0.01',
            'attributes' => 'nullable|array',
            'is_available' => 'boolean',
            'recipes' => 'nullable|array',
            'recipes.*.id' => 'nullable|exists:simple_product_variant_recipes,id',
            'recipes.*.product_id' => 'required_with:recipes|exists:products,id',
            'recipes.*.quantity_needed' => 'required_with:recipes|numeric|min:0.001',
            'recipes.*.unit' => 'required_with:recipes|string|in:kg,lt,pcs,g,ml',
        ]);

        DB::beginTransaction();
        try {
            // Actualizar datos de la variante
            $variant->update([
                'variant_name' => $validated['variant_name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'attributes' => $validated['attributes'] ?? null,
                'is_available' => $validated['is_available'] ?? true,
            ]);

            // Sincronizar recetas
            $newRecipeIds = [];
            if (!empty($validated['recipes'])) {
                foreach ($validated['recipes'] as $recipeData) {
                    if (!empty($recipeData['id'])) {
                        // Actualizar receta existente
                        $recipe = SimpleProductVariantRecipe::find($recipeData['id']);
                        if ($recipe && $recipe->simple_product_variant_id === $variant->id) {
                            $recipe->update([
                                'product_id' => $recipeData['product_id'],
                                'quantity_needed' => $recipeData['quantity_needed'],
                                'unit' => $recipeData['unit'],
                            ]);
                            $newRecipeIds[] = $recipe->id;
                        }
                    } else {
                        // Crear nueva receta
                        $recipe = SimpleProductVariantRecipe::create([
                            'simple_product_variant_id' => $variant->id,
                            'product_id' => $recipeData['product_id'],
                            'quantity_needed' => $recipeData['quantity_needed'],
                            'unit' => $recipeData['unit'],
                            'restaurant_id' => 1,
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
    public function destroy(SimpleProductVariant $variant)
    {
        // Check if variant has been sold
        if ($variant->saleItems()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar una variante que ya se ha vendido']);
        }

        $variant->delete();

        return back()->with('success', 'Variante eliminada exitosamente');
    }

    /**
     * Toggle availability status
     */
    public function toggleAvailability(SimpleProductVariant $variant)
    {
        $variant->update([
            'is_available' => !$variant->is_available,
        ]);

        return back()->with('success', 'Disponibilidad actualizada');
    }
}
