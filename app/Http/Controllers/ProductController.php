<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        // Start with base query instead of collection
        $query = Product::with('category');

        // Aplicar filtros usando query builder
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('current_stock', '<=', 'min_stock');
        }

        if ($request->boolean('expired')) {
            $query->where('expiry_date', '<', now());
        }

        if ($request->boolean('expiring_soon')) {
            $query->where('expiry_date', '<=', now()->addDays(7))
                  ->where('expiry_date', '>=', now());
        }

        // Apply pagination (15 per page)
        $products = $query->where('is_active', true)
                          ->orderBy('name')
                          ->paginate(15)
                          ->withQueryString();

        $categories = Category::all();

        return Inertia::render('Inventory/Products', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'low_stock', 'expired', 'expiring_soon']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        $categories = Category::all();

        return Inertia::render('Inventory/ProductForm', [
            'categories' => $categories,
            'product' => null,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $this->productRepository->create($request->validated());

        return redirect()->route('inventory.products.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);

        $product->load(['category', 'inventoryMovements.user', 'recipes.menuItem']);

        return Inertia::render('Inventory/ProductDetail', [
            'product' => new ProductResource($product),
        ]);
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $categories = Category::all();

        return Inertia::render('Inventory/ProductForm', [
            'categories' => $categories,
            'product' => new ProductResource($product),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->productRepository->update($product->id, $request->validated());

        return redirect()->route('inventory.products.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        // Verificar que no tenga movimientos recientes o recetas activas
        if ($product->inventoryMovements()->count() > 0 || $product->recipes()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: el producto tiene movimientos o está en recetas.');
        }

        $this->productRepository->delete($product->id);

        return redirect()->route('inventory.products.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }

    public function alerts()
    {
        $this->authorize('viewAny', Product::class);

        return response()->json([
            'low_stock' => ProductResource::collection($this->productRepository->getLowStockProducts()),
            'expired' => ProductResource::collection($this->productRepository->getExpiredProducts()),
            'expiring_soon' => ProductResource::collection($this->productRepository->getExpiringSoonProducts()),
        ]);
    }
}
