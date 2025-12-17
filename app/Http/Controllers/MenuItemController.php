<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', MenuItem::class);

        $query = MenuItem::query();

        // Búsqueda
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filtro por disponibilidad
        if ($request->filled('is_available')) {
            $query->where('is_available', $request->is_available === 'true');
        }

        // Filtro por tipo (servicio o platillo)
        if ($request->filled('is_service')) {
            $query->where('is_service', $request->is_service === 'true');
        }

        $menuItems = $query->withCount('recipes')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Estadísticas
        $stats = [
            'total' => MenuItem::count(),
            'available' => MenuItem::where('is_available', true)->count(),
            'unavailable' => MenuItem::where('is_available', false)->count(),
            'with_recipes' => MenuItem::has('recipes')->count(),
            'without_recipes' => MenuItem::doesntHave('recipes')->count(),
            'services' => MenuItem::where('is_service', true)->count(),
            'dishes' => MenuItem::where('is_service', false)->count(),
        ];

        return Inertia::render('Menu/Items', [
            'menuItems' => $menuItems,
            'filters' => $request->only(['search', 'is_available', 'is_service']),
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', MenuItem::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0.01',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_path' => 'nullable|string|max:255',
            'is_available' => 'boolean',
            'is_service' => 'boolean',
        ]);

        // Handle file upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('menu-items', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        $menuItem = MenuItem::create($validated);

        return redirect()->route('menu.items')
            ->with('success', 'Platillo creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(MenuItem $menuItem)
    {
        $this->authorize('view', $menuItem);

        $menuItem->load('recipes.product');

        return Inertia::render('Menu/ItemDetail', [
            'menuItem' => $menuItem,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MenuItem $menuItem)
    {
        $this->authorize('update', $menuItem);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0.01',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_path' => 'nullable|string|max:255',
            'is_available' => 'boolean',
            'is_service' => 'boolean',
        ]);

        // Handle file upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($menuItem->image_path && \Storage::disk('public')->exists(str_replace('/storage/', '', $menuItem->image_path))) {
                \Storage::disk('public')->delete(str_replace('/storage/', '', $menuItem->image_path));
            }

            $path = $request->file('image')->store('menu-items', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        $menuItem->update($validated);

        return redirect()->route('menu.items')
            ->with('success', 'Platillo actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MenuItem $menuItem)
    {
        $this->authorize('delete', $menuItem);

        // Verificar si tiene recetas asociadas
        if ($menuItem->recipes()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un platillo que tiene recetas asociadas');
        }

        // Verificar si se ha vendido
        if ($menuItem->saleItems()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un platillo que ya se ha vendido');
        }

        $menuItem->delete();

        return redirect()->route('menu.items')
            ->with('success', 'Platillo eliminado exitosamente');
    }

    /**
     * Toggle availability status
     */
    public function toggleAvailability(MenuItem $menuItem)
    {
        $this->authorize('update', $menuItem);

        $menuItem->update([
            'is_available' => !$menuItem->is_available,
        ]);

        return back()->with('success', 'Disponibilidad actualizada');
    }

    /**
     * Get variants for a menu item
     */
    public function getVariants(MenuItem $menuItem)
    {
        $this->authorize('view', $menuItem);

        $variants = $menuItem->variants()
            ->with(['recipes.product'])
            ->orderBy('display_order')
            ->orderBy('variant_name')
            ->get()
            ->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'variant_name' => $variant->variant_name,
                    'variant_sku' => $variant->variant_sku,
                    'price' => $variant->price,
                    'is_available' => $variant->is_available,
                    'attributes' => $variant->attributes,
                    'available_quantity' => $variant->available_quantity,
                ];
            });

        return response()->json($variants);
    }
}
