<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryMovement::with(['product.category', 'user', 'supplier']);

        // Filtros
        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->movement_type) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->date_from) {
            $query->whereDate('movement_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('movement_date', '<=', $request->date_to);
        }

        $movements = $query->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Inventory/Movements', [
            'movements' => $movements,
            'filters' => $request->only(['product_id', 'movement_type', 'date_from', 'date_to']),
        ]);
    }
}
