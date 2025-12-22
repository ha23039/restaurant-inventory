<?php

namespace App\Http\Controllers\DigitalMenu;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Inertia\Inertia;

class OrderTrackingController extends Controller
{
    /**
     * Show order tracking page
     */
    public function show(string $saleNumber)
    {
        $sale = Sale::where('sale_number', $saleNumber)
            ->where('source', 'digital_menu')
            ->with([
                'saleItems.menuItem',
                'saleItems.menuItemVariant.menuItem',
                'saleItems.simpleProduct',
                'digitalCustomer',
                'table',
            ])
            ->firstOrFail();

        return Inertia::render('DigitalMenu/OrderTracking', [
            'sale' => [
                'sale_number' => $sale->sale_number,
                'status' => $sale->status,
                'total' => $sale->total,
                'subtotal' => $sale->subtotal,
                'estimated_ready_at' => $sale->estimated_ready_at,
                'created_at' => $sale->created_at,
                'items' => $sale->saleItems->map(function ($item) {
                    $name = match ($item->product_type) {
                        'menu' => $item->menuItem->name,
                        'variant' => $item->menuItemVariant->menuItem->name . ' - ' . $item->menuItemVariant->variant_name,
                        'simple' => $item->simpleProduct->name,
                        default => 'Producto desconocido',
                    };

                    return [
                        'name' => $name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                    ];
                }),
                'delivery_method' => $sale->delivery_method,
                'customer_notes' => $sale->customer_notes,
                'customer_address' => $sale->customer_address,
                'table_number' => $sale->table ? $sale->table->table_number : null,
            ],
        ]);
    }

    /**
     * Get current order status (for polling)
     */
    public function getStatus(string $saleNumber)
    {
        $sale = Sale::where('sale_number', $saleNumber)
            ->where('source', 'digital_menu')
            ->select('status', 'estimated_ready_at')
            ->firstOrFail();

        return response()->json([
            'status' => $sale->status,
            'estimated_ready_at' => $sale->estimated_ready_at,
        ]);
    }
}
