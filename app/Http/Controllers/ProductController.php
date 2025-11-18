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

        $products = $this->productRepository->getAllWithCategory();

        // Aplicar filtros usando el repositorio
        if ($request->filled('search')) {
            $products = $this->productRepository->search($request->search);
        }

        if ($request->filled('category_id')) {
            $products = $this->productRepository->getByCategory($request->category_id);
        }

        if ($request->boolean('low_stock')) {
            $products = $this->productRepository->getLowStockProducts();
        }

        if ($request->boolean('expired')) {
            $products = $this->productRepository->getExpiredProducts();
        }

        if ($request->boolean('expiring_soon')) {
            $products = $this->productRepository->getExpiringSoonProducts();
        }

        $categories = Category::all();

        return Inertia::render('Inventory/Products', [
            'products' => ProductResource::collection($products),
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
