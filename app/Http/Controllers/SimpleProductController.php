<?php

namespace App\Http\Controllers;

use App\Models\SimpleProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SimpleProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', SimpleProduct::class);

        $query = SimpleProduct::with(['product.category', 'variants.recipes.product']);

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
            if ($item->allows_variants) {
                // Para productos con variantes, calcular stock basado en variantes
                $totalVariantStock = 0;
                $hasAnyVariantInStock = false;

                if ($item->relationLoaded('variants')) {
                    foreach ($item->variants as $variant) {
                        if ($variant->is_available) {
                            $variantStock = $variant->available_quantity;
                            $totalVariantStock += $variantStock;
                            if ($variantStock > 0) {
                                $hasAnyVariantInStock = true;
                            }
                        }
                    }
                }

                $item->available_quantity = $totalVariantStock;
                $item->is_in_stock = $hasAnyVariantInStock;
            } elseif ($item->product) {
                // Para productos simples sin variantes
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
            ->get(['id', 'name', 'category_id', 'unit_type', 'current_stock', 'unit_cost']);

        return Inertia::render('SimpleProducts/Index', [
            'simpleProducts' => $simpleProducts,
            'products' => $products,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    /**
     * Display the specified resource (API endpoint for variants)
     */
    public function show(SimpleProduct $simpleProduct)
    {
        $simpleProduct->load(['variants.recipes.product']);

        return response()->json([
            'id' => $simpleProduct->id,
            'name' => $simpleProduct->name,
            'variants' => $simpleProduct->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'variant_name' => $variant->variant_name,
                    'description' => $variant->description,
                    'price' => $variant->price,
                    'attributes' => $variant->attributes,
                    'is_available' => $variant->is_available,
                    'available_quantity' => $variant->available_quantity,
                    'recipes' => $variant->recipes->map(function ($recipe) {
                        return [
                            'id' => $recipe->id,
                            'product_id' => $recipe->product_id,
                            'product_name' => $recipe->product->name ?? null,
                            'quantity_needed' => $recipe->quantity_needed,
                            'unit' => $recipe->unit,
                        ];
                    }),
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', SimpleProduct::class);

        // Si allows_variants es true, product_id y cost_per_unit son opcionales
        $allowsVariants = $request->boolean('allows_variants', false);

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'category' => 'required|string|max:100',
            'is_available' => 'boolean',
            'allows_variants' => 'boolean',
        ];

        if ($allowsVariants) {
            // Si tiene variantes, estos campos son opcionales
            $rules['product_id'] = 'nullable|exists:products,id';
            $rules['sale_price'] = 'nullable|numeric|min:0.01';
            $rules['cost_per_unit'] = 'nullable|numeric|min:0.001';
        } else {
            // Si NO tiene variantes, estos campos son obligatorios
            $rules['product_id'] = 'required|exists:products,id';
            $rules['sale_price'] = 'required|numeric|min:0.01';
            $rules['cost_per_unit'] = 'required|numeric|min:0.001';
        }

        $validated = $request->validate($rules);

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('simple-products', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }
        unset($validated['image']);

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

        // Si allows_variants es true, product_id y cost_per_unit son opcionales
        $allowsVariants = $request->boolean('allows_variants', false);

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_image' => 'nullable|boolean',
            'category' => 'required|string|max:100',
            'is_available' => 'boolean',
            'allows_variants' => 'boolean',
        ];

        if ($allowsVariants) {
            // Si tiene variantes, estos campos son opcionales
            $rules['product_id'] = 'nullable|exists:products,id';
            $rules['sale_price'] = 'nullable|numeric|min:0.01';
            $rules['cost_per_unit'] = 'nullable|numeric|min:0.001';
        } else {
            // Si NO tiene variantes, estos campos son obligatorios
            $rules['product_id'] = 'required|exists:products,id';
            $rules['sale_price'] = 'required|numeric|min:0.01';
            $rules['cost_per_unit'] = 'required|numeric|min:0.001';
        }

        $validated = $request->validate($rules);

        // Handle image removal
        if ($request->boolean('remove_image') && $simpleProduct->image_path) {
            $oldPath = str_replace('/storage/', '', $simpleProduct->image_path);
            Storage::disk('public')->delete($oldPath);
            $validated['image_path'] = null;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($simpleProduct->image_path) {
                $oldPath = str_replace('/storage/', '', $simpleProduct->image_path);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('simple-products', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }
        unset($validated['image'], $validated['remove_image']);

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
