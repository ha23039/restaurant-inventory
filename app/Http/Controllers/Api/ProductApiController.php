<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    /**
     * Get all products for API consumption
     */
    public function index(Request $request)
    {
        $query = Product::with('category')
            ->where('is_active', true)
            ->orderBy('name');

        // Optional search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $products = $query->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'unit_type' => $product->unit_type,
                'current_stock' => (float) $product->current_stock,
                'unit_cost' => (float) $product->unit_cost,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                ] : null,
            ];
        });

        return response()->json($products);
    }
}
