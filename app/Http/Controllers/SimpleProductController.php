<?php

namespace App\Http\Controllers;

use App\Models\SimpleProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SimpleProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', SimpleProduct::class);

        $query = SimpleProduct::with(['product.category']);

        // Búsqueda
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filtro por categoría
        if ($request->filled('category')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        $simpleProducts = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Calcular disponibilidad para cada producto
        $simpleProducts->getCollection()->transform(function ($item) {
            if ($item->product) {
                $currentStock = floatval($item->product->current_stock);
                $costPerUnit = floatval($item->cost_per_unit);
                $available = $costPerUnit > 0 ? floor($currentStock / $costPerUnit) : 0;

                $item->available_quantity = $available;
                $item->is_in_stock = $available > 0;
            } else {
                $item->available_quantity = 0;
                $item->is_in_stock = false;
            }
            return $item;
        });

        // Obtener productos disponibles para el formulario
        $products = Product::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'category_id', 'unit_type', 'current_stock']);

        return Inertia::render('SimpleProducts/Index', [
            'simpleProducts' => $simpleProducts,
            'products' => $products,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', SimpleProduct::class);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'sale_price' => 'required|numeric|min:0.01',
            'cost_per_unit' => 'required|numeric|min:0.001',
            'category' => 'nullable|string|max:100',
        ]);

        SimpleProduct::create($validated);

        return redirect()->route('simple-products.index')
            ->with('success', 'Producto simple creado exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SimpleProduct $simpleProduct)
    {
        $this->authorize('update', $simpleProduct);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'sale_price' => 'required|numeric|min:0.01',
            'cost_per_unit' => 'required|numeric|min:0.001',
            'category' => 'nullable|string|max:100',
        ]);

        $simpleProduct->update($validated);

        return redirect()->route('simple-products.index')
            ->with('success', 'Producto simple actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SimpleProduct $simpleProduct)
    {
        $this->authorize('delete', $simpleProduct);

        // Verificar si se ha vendido
        if ($simpleProduct->saleItems()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un producto que ya se ha vendido');
        }

        $simpleProduct->delete();

        return redirect()->route('simple-products.index')
            ->with('success', 'Producto simple eliminado exitosamente');
    }
}
