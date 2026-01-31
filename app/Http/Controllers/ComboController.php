<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\SimpleProduct;
use App\Services\ComboService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ComboController extends Controller
{
    public function __construct(
        private ComboService $comboService
    ) {
    }

    /**
     * Display a listing of combos.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'available', 'category']);
        $combos = $this->comboService->getPaginatedCombos($filters);

        return Inertia::render('Combos/Index', [
            'combos' => $combos,
            'filters' => $filters,
            'menuItems' => $this->getAvailableMenuItems(),
            'simpleProducts' => $this->getAvailableSimpleProducts(),
        ]);
    }

    /**
     * Show the form for creating a new combo.
     */
    public function create()
    {
        return Inertia::render('Combos/Form', [
            'combo' => null,
            'menuItems' => $this->getAvailableMenuItems(),
            'simpleProducts' => $this->getAvailableSimpleProducts(),
        ]);
    }

    /**
     * Store a newly created combo.
     */
    public function store(Request $request)
    {
        $validated = $this->validateComboRequest($request);

        try {
            $this->comboService->createCombo(
                $validated,
                $request->file('image')
            );

            return redirect()->route('combos.index')
                ->with('success', 'Combo creado exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al crear el combo: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified combo.
     */
    public function edit(int $id)
    {
        $combo = $this->comboService->getComboWithComponents($id);

        if (!$combo) {
            return redirect()->route('combos.index')
                ->with('error', 'Combo no encontrado');
        }

        return Inertia::render('Combos/Form', [
            'combo' => $combo,
            'menuItems' => $this->getAvailableMenuItems(),
            'simpleProducts' => $this->getAvailableSimpleProducts(),
        ]);
    }

    /**
     * Update the specified combo.
     */
    public function update(Request $request, int $id)
    {
        $validated = $this->validateComboRequest($request, true);

        try {
            $this->comboService->updateCombo(
                $id,
                $validated,
                $request->file('image'),
                $request->boolean('remove_image')
            );

            return redirect()->route('combos.index')
                ->with('success', 'Combo actualizado exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el combo: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified combo.
     */
    public function destroy(int $id)
    {
        try {
            $this->comboService->deleteCombo($id);

            return redirect()->route('combos.index')
                ->with('success', 'Combo eliminado exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar el combo: ' . $e->getMessage()]);
        }
    }

    /**
     * Duplicate a combo.
     */
    public function duplicate(int $id)
    {
        try {
            $newCombo = $this->comboService->duplicateCombo($id);

            if (!$newCombo) {
                return back()->withErrors(['error' => 'Combo no encontrado']);
            }

            return redirect()->route('combos.edit', $newCombo->id)
                ->with('success', 'Combo duplicado. Edita los detalles y actívalo.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al duplicar el combo: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle combo availability.
     */
    public function toggleAvailability(int $id)
    {
        $result = $this->comboService->toggleAvailability($id);

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Get combo data for API (used in menu/POS).
     */
    public function show(int $id)
    {
        $combo = $this->comboService->getComboWithComponents($id);

        if (!$combo) {
            return response()->json(['error' => 'Combo no encontrado'], 404);
        }

        return response()->json($this->comboService->formatComboForApi($combo));
    }

    /**
     * Validate combo request
     */
    private function validateComboRequest(Request $request, bool $isUpdate = false): array
    {
        // Si components viene como JSON string (desde FormData), parsearlo
        if ($request->has('components') && is_string($request->components)) {
            $request->merge([
                'components' => json_decode($request->components, true) ?? []
            ]);
        }

        // Convertir strings '1'/'0' a booleanos (FormData envía strings)
        $request->merge([
            'is_available' => filter_var($request->is_available, FILTER_VALIDATE_BOOLEAN),
            'show_in_menu' => filter_var($request->show_in_menu, FILTER_VALIDATE_BOOLEAN),
            'show_in_pos' => filter_var($request->show_in_pos, FILTER_VALIDATE_BOOLEAN),
        ]);

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'base_price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'is_available' => 'boolean',
            'show_in_menu' => 'boolean',
            'show_in_pos' => 'boolean',
            'components' => 'required|array|min:1',
            'components.*.component_type' => 'required|in:fixed,choice',
            'components.*.name' => 'nullable|string|max:255',
            'components.*.quantity' => 'required|integer|min:1',
            'components.*.is_required' => 'boolean',
            'components.*.sellable_type' => 'nullable|in:menu_item,simple_product',
            'components.*.sellable_id' => 'nullable|integer',
            'components.*.options' => 'nullable|array',
            'components.*.options.*.sellable_type' => 'required|in:menu_item,simple_product',
            'components.*.options.*.sellable_id' => 'required|integer',
            'components.*.options.*.price_adjustment' => 'required|numeric',
            'components.*.options.*.is_default' => 'boolean',
        ];

        if ($isUpdate) {
            $rules['remove_image'] = 'nullable|boolean';
        }

        return $request->validate($rules);
    }

    /**
     * Helper: Get available menu items for combos.
     */
    private function getAvailableMenuItems()
    {
        $query = MenuItem::where('is_available', true);

        // Only filter by available_in_combos if the column exists
        if (\Schema::hasColumn('menu_items', 'available_in_combos')) {
            $query->where('available_in_combos', true);
        }

        return $query->with([
                'variants' => function ($q) {
                    $q->where('is_available', true);
                }
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'image_path', 'has_variants']);
    }

    /**
     * Helper: Get available simple products for combos.
     */
    private function getAvailableSimpleProducts()
    {
        $query = SimpleProduct::where('is_available', true);

        // Only filter by available_in_combos if the column exists
        if (\Schema::hasColumn('simple_products', 'available_in_combos')) {
            $query->where('available_in_combos', true);
        }

        return $query->with([
                'variants' => function ($q) {
                    $q->where('is_available', true);
                },
                'product.category'
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'sale_price', 'image_path', 'category', 'allows_variants', 'product_id']);
    }
}
