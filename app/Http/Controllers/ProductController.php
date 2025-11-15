<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Filtros
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->boolean('low_stock')) {
            $query->whereRaw('current_stock <= min_stock');
        }

        if ($request->boolean('expired')) {
            $query->where('expiry_date', '<', now());
        }

        if ($request->boolean('expiring_soon')) {
            $query->whereBetween('expiry_date', [now(), now()->addDays(7)]);
        }

        $products = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = Category::all();

        return Inertia::render('Inventory/Products', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'low_stock', 'expired', 'expiring_soon'])
        ]);
    }

    public function create()
    {
        $categories = Category::all();
        
        return Inertia::render('Inventory/ProductForm', [
            'categories' => $categories,
            'product' => null
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'unit_type' => 'required|string|max:50',
            'current_stock' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        Product::create($validated);

        return redirect()->route('inventory.products.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'inventoryMovements.user', 'recipes.menuItem']);
        
        return Inertia::render('Inventory/ProductDetail', [
            'product' => $product
        ]);
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        
        return Inertia::render('Inventory/ProductForm', [
            'categories' => $categories,
            'product' => $product
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'unit_type' => 'required|string|max:50',
            'current_stock' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'is_active' => 'boolean'
        ]);

        $product->update($validated);

        return redirect()->route('inventory.products.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Product $product)
    {
        // Verificar que no tenga movimientos recientes o recetas activas
        if ($product->inventoryMovements()->count() > 0 || $product->recipes()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: el producto tiene movimientos o está en recetas.');
        }

        $product->delete();

        return redirect()->route('inventory.products.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }

    // Método para alertas
    public function alerts()
    {
        $lowStock = Product::whereRaw('current_stock <= min_stock')->with('category')->get();
        $expired = Product::where('expiry_date', '<', now())->with('category')->get();
        $expiringSoon = Product::whereBetween('expiry_date', [now(), now()->addDays(7)])->with('category')->get();

        return response()->json([
            'low_stock' => $lowStock,
            'expired' => $expired,
            'expiring_soon' => $expiringSoon
        ]);
    }
}