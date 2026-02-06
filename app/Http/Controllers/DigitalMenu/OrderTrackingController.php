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
                'saleItems.simpleProductVariant.simpleProduct',
                'saleItems.combo',
                'digitalCustomer',
                'table',
                'kitchenOrderState', // Incluir estado de cocina
            ])
            ->firstOrFail();

        // Obtener estado detallado (prioriza estado de cocina)
        $detailedStatus = $this->getDetailedStatus($sale);

        return Inertia::render('DigitalMenu/OrderTracking', [
            'sale' => [
                'sale_number' => $sale->sale_number,
                'status' => $sale->status, // Estado general (pendiente/completada/cancelada)
                'kitchen_status' => $detailedStatus['kitchen_status'], // Estado detallado de cocina
                'status_label' => $detailedStatus['label'], // Etiqueta para mostrar al cliente
                'status_color' => $detailedStatus['color'], // Color del badge
                'elapsed_minutes' => $detailedStatus['elapsed_minutes'], // Tiempo transcurrido
                'total' => $sale->total,
                'subtotal' => $sale->subtotal,
                'estimated_ready_at' => $sale->estimated_ready_at,
                'created_at' => $sale->created_at,
                'items' => $sale->saleItems->map(function ($item) {
                    $name = match ($item->product_type) {
                        'menu' => $item->menuItem?->name ?? 'Platillo',
                        'variant' => ($item->menuItemVariant?->menuItem?->name ?? '') . ' - ' . ($item->menuItemVariant?->variant_name ?? ''),
                        'simple' => $item->simpleProduct?->name ?? 'Producto',
                        'simple_variant' => ($item->simpleProductVariant?->simpleProduct?->name ?? '') . ' - ' . ($item->simpleProductVariant?->variant_name ?? ''),
                        'combo' => $item->combo?->name ?? 'Combo',
                        default => 'Producto',
                    };

                    // Para combos, agregar detalle de componentes
                    $componentsDetail = [];
                    if ($item->product_type === 'combo' && $item->combo_selections) {
                        $selections = is_string($item->combo_selections)
                            ? json_decode($item->combo_selections, true)
                            : $item->combo_selections;
                        $componentsDetail = $selections['components_detail'] ?? [];
                    }

                    return [
                        'name' => $name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'product_type' => $item->product_type,
                        'components_detail' => $componentsDetail,
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
            ->with('kitchenOrderState')
            ->select('id', 'status', 'estimated_ready_at')
            ->firstOrFail();

        $detailedStatus = $this->getDetailedStatus($sale);

        return response()->json([
            'status' => $sale->status,
            'kitchen_status' => $detailedStatus['kitchen_status'],
            'status_label' => $detailedStatus['label'],
            'status_color' => $detailedStatus['color'],
            'elapsed_minutes' => $detailedStatus['elapsed_minutes'],
            'estimated_ready_at' => $sale->estimated_ready_at,
        ]);
    }

    /**
     * Obtener estado detallado combinando Sale y KitchenOrderState
     */
    private function getDetailedStatus(Sale $sale): array
    {
        // Si la venta está cancelada, mostrar eso
        if ($sale->status === 'cancelada') {
            return [
                'kitchen_status' => 'cancelada',
                'label' => 'Cancelada',
                'color' => 'red',
                'elapsed_minutes' => null,
            ];
        }

        // Si tiene estado de cocina, usar ese (más detallado)
        if ($sale->kitchenOrderState) {
            $kitchenStatus = $sale->kitchenOrderState->status;
            $elapsedMinutes = $sale->kitchenOrderState->elapsed_minutes;

            return match ($kitchenStatus) {
                'nueva' => [
                    'kitchen_status' => 'nueva',
                    'label' => 'Pedido Recibido',
                    'color' => 'blue',
                    'elapsed_minutes' => $elapsedMinutes,
                ],
                'preparando' => [
                    'kitchen_status' => 'preparando',
                    'label' => 'En Preparación',
                    'color' => 'yellow',
                    'elapsed_minutes' => $elapsedMinutes,
                ],
                'lista' => [
                    'kitchen_status' => 'lista',
                    'label' => 'Lista para Recoger',
                    'color' => 'green',
                    'elapsed_minutes' => $elapsedMinutes,
                ],
                'entregada' => [
                    'kitchen_status' => 'entregada',
                    'label' => 'Entregada',
                    'color' => 'gray',
                    'elapsed_minutes' => $elapsedMinutes,
                ],
                default => [
                    'kitchen_status' => 'pendiente',
                    'label' => 'Pendiente',
                    'color' => 'blue',
                    'elapsed_minutes' => $elapsedMinutes,
                ],
            };
        }

        // Fallback: usar estado de venta
        return match ($sale->status) {
            'completada' => [
                'kitchen_status' => 'completada',
                'label' => 'Completada',
                'color' => 'gray',
                'elapsed_minutes' => null,
            ],
            'pendiente' => [
                'kitchen_status' => 'pendiente',
                'label' => 'Pendiente',
                'color' => 'blue',
                'elapsed_minutes' => null,
            ],
            default => [
                'kitchen_status' => 'pendiente',
                'label' => 'Pendiente',
                'color' => 'blue',
                'elapsed_minutes' => null,
            ],
        };
    }
}
