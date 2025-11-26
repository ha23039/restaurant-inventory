<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\MenuItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Recipe::class);

        $query = MenuItem::with(['recipes.product.category']);

        // Búsqueda
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filtro: solo platillos (excluir servicios)
        if ($request->filled('only_dishes')) {
            $query->where('is_service', false);
        }

        // Filtro: solo con recetas
        if ($request->filled('has_recipes')) {
            if ($request->has_recipes === 'true') {
                $query->has('recipes');
            } else {
                $query->doesntHave('recipes');
            }
        }

        $menuItems = $query->orderBy('name')
            ->withCount('recipes')
            ->paginate(20)
            ->withQueryString();

        // Obtener todos los productos disponibles para el formulario
        $products = Product::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'category_id', 'unit_type', 'current_stock']);

        return Inertia::render('Menu/Recipes', [
            'menuItems' => $menuItems,
            'products' => $products,
            'filters' => $request->only(['search', 'only_dishes', 'has_recipes']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Recipe::class);

        $validated = $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'product_id' => 'required|exists:products,id',
            'quantity_needed' => 'required|numeric|min:0.001',
            'unit' => 'required|string|in:kg,lt,pcs,g,ml',
        ]);

        // Verificar que no exista ya esta combinación
        $exists = Recipe::where('menu_item_id', $validated['menu_item_id'])
            ->where('product_id', $validated['product_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Este ingrediente ya está agregado a la receta');
        }

        Recipe::create($validated);

        return back()->with('success', 'Ingrediente agregado a la receta');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Recipe $recipe)
    {
        $this->authorize('update', $recipe);

        $validated = $request->validate([
            'quantity_needed' => 'required|numeric|min:0.001',
            'unit' => 'required|string|in:kg,lt,pcs,g,ml',
        ]);

        $recipe->update($validated);

        return back()->with('success', 'Cantidad actualizada');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recipe $recipe)
    {
        $this->authorize('delete', $recipe);

        $recipe->delete();

        return back()->with('success', 'Ingrediente eliminado de la receta');
    }

    /**
     * Bulk store multiple ingredients for a menu item
     */
    public function bulkStore(Request $request)
    {
        $this->authorize('create', Recipe::class);

        $validated = $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.product_id' => 'required|exists:products,id',
            'ingredients.*.quantity_needed' => 'required|numeric|min:0.001',
            'ingredients.*.unit' => 'required|string|in:kg,lt,pcs,g,ml',
        ]);

        $menuItemId = $validated['menu_item_id'];
        $created = 0;
        $skipped = 0;

        foreach ($validated['ingredients'] as $ingredient) {
            // Verificar si ya existe
            $exists = Recipe::where('menu_item_id', $menuItemId)
                ->where('product_id', $ingredient['product_id'])
                ->exists();

            if (!$exists) {
                Recipe::create([
                    'menu_item_id' => $menuItemId,
                    'product_id' => $ingredient['product_id'],
                    'quantity_needed' => $ingredient['quantity_needed'],
                    'unit' => $ingredient['unit'],
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        $message = "Se agregaron {$created} ingrediente(s)";
        if ($skipped > 0) {
            $message .= " ({$skipped} ya existían)";
        }

        return back()->with('success', $message);
    }
}
