<?php

namespace App\Http\Controllers;

use App\Models\KitchenOrderState;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KitchenDisplayController extends Controller
{
    /**
     * Mostrar la pantalla de cocina
     */
    public function index()
    {
        return Inertia::render('Kitchen/Display');
    }

    /**
     * API: Obtener órdenes activas para polling
     */
    public function getOrders()
    {
        $orders = KitchenOrderState::with([
            'sale.saleItems.menuItem',
            'sale.saleItems.simpleProduct',
            'sale.saleItems.menuItemVariant.menuItem',  // Soporte para variantes
            'sale.table'
        ])
            ->active() // Solo órdenes no entregadas
            ->orderByPriority() // Por prioridad y luego por fecha
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'sale_id' => $order->sale_id,
                    'sale_number' => $order->sale->sale_number,
                    'status' => $order->status,
                    'elapsed_minutes' => $order->elapsed_minutes,
                    'color' => $order->color,
                    'priority' => $order->priority,
                    'table_number' => $order->sale->table ? $order->sale->table->table_number : null,
                    'table_name' => $order->sale->table ? $order->sale->table->name : 'Para Llevar',
                    'customer_name' => $order->sale->customer_name,
                    'notes' => $order->sale->notes,
                    'items' => $order->sale->saleItems->map(function ($item) {
                        return [
                            'quantity' => $item->quantity,
                            'name' => $this->getItemName($item),
                        ];
                    }),
                    'created_at' => $order->created_at->format('H:i'),
                ];
            });

        return response()->json($orders);
    }

    /**
     * Obtener nombre del item (soporta menu, variant, simple, free)
     */
    private function getItemName($item): string
    {
        if ($item->product_type === 'variant' && $item->menuItemVariant) {
            // Para variantes, mostrar nombre del platillo padre + nombre de variante
            $parentName = $item->menuItemVariant->menuItem->name ?? '';
            $variantName = $item->menuItemVariant->variant_name;
            return $parentName ? "{$parentName} - {$variantName}" : $variantName;
        } elseif ($item->product_type === 'menu' && $item->menuItem) {
            return $item->menuItem->name;
        } elseif ($item->product_type === 'simple' && $item->simpleProduct) {
            return $item->simpleProduct->name;
        } elseif ($item->product_type === 'free' && $item->free_sale_name) {
            return $item->free_sale_name;
        }

        return 'Producto';
    }

    /**
     * Actualizar estado de una orden
     */
    public function updateStatus(Request $request, KitchenOrderState $order)
    {
        $request->validate([
            'status' => 'required|in:nueva,preparando,lista,entregada',
        ]);

        $newStatus = $request->status;

        // Actualizar timestamps según el estado
        if ($newStatus === 'preparando' && !$order->started_at) {
            $order->started_at = now();
        }

        if ($newStatus === 'lista' && !$order->completed_at) {
            $order->completed_at = now();
        }

        $order->status = $newStatus;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'order' => $order,
        ]);
    }
}
