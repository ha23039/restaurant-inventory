<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        protected SaleService $saleService
    ) {
    }

    /**
     * Mostrar lista de órdenes activas
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        $status = $request->get('status');
        $source = $request->get('source');

        $query = Sale::with([
            'saleItems' => function ($q) {
                $q->with(['menuItem', 'menuItemVariant', 'simpleProduct', 'simpleProductVariant', 'combo', 'cancelledBy']);
            },
            'kitchenOrderState',
            'table',
            'user'
        ])
            ->whereIn('status', ['pendiente'])
            ->orWhereHas('kitchenOrderState', function ($q) {
                $q->whereNotIn('status', ['entregada']);
            });

        // Filtrar por tipo de orden
        switch ($type) {
            case 'local':
                $query->where(function ($q) {
                    $q->whereNotNull('table_id')
                        ->orWhere('delivery_method', 'dine_in');
                });
                break;
            case 'takeaway':
                $query->where(function ($q) {
                    $q->where('delivery_method', 'pickup')
                        ->orWhere(function ($sub) {
                            $sub->whereNull('table_id')
                                ->where(function ($s) {
                                    $s->whereNull('delivery_method')
                                        ->orWhereNotIn('delivery_method', ['delivery', 'dine_in']);
                                });
                        });
                });
                break;
            case 'delivery':
                $query->where('delivery_method', 'delivery');
                break;
        }

        if ($source) {
            $query->where('source', $source);
        }

        if ($status) {
            $query->whereHas('kitchenOrderState', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        $counts = $this->getOrderCounts();

        // Cargar datos de mesas si el tab es 'mesas' o siempre para el tab de mesas
        $tables = [];
        $tableStatistics = [];
        $paymentMethods = [];

        if ($type === 'mesas' || $request->has('include_tables')) {
            $tables = \App\Models\Table::where('is_active', true)
                ->orderBy('table_number')
                ->get()
                ->map(function ($table) {
                    // Obtener ventas pendientes de esta mesa
                    $pendingSales = Sale::where('table_id', $table->id)
                        ->whereIn('status', ['pendiente', 'en_preparacion'])
                        ->get();

                    return [
                        'id' => $table->id,
                        'table_number' => $table->table_number,
                        'name' => $table->name,
                        'capacity' => $table->capacity,
                        'status' => $table->status,
                        'status_label' => $table->status_label,
                        'is_active' => $table->is_active,
                        'pending_sales_count' => $pendingSales->count(),
                        'pending_total' => $pendingSales->sum('total'),
                        'current_sale' => $table->currentSale,
                    ];
                })->toArray();

            $tableStatistics = $this->getTableStatistics();
            $paymentMethods = \App\Models\PaymentMethod::getActive();
        }

        // Agregar conteo de mesas al counts
        $counts['mesas'] = \App\Models\Table::where('is_active', true)
            ->where('status', 'ocupada')
            ->count();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'counts' => $counts,
            'filters' => [
                'type' => $type,
                'status' => $status,
                'source' => $source,
            ],
            'tables' => $tables,
            'tableStatistics' => $tableStatistics,
            'payment_methods' => $paymentMethods,
        ]);
    }

    /**
     * Obtener detalle de una orden (para SlideOver)
     */
    public function show(Sale $sale)
    {
        $sale->load([
            'saleItems' => function ($q) {
                $q->with(['menuItem', 'menuItemVariant', 'simpleProduct', 'simpleProductVariant', 'combo', 'cancelledBy']);
            },
            'kitchenOrderState',
            'table',
            'user',
            'digitalCustomer'
        ]);

        $activeItems = $sale->saleItems->filter(fn($item) => !$item->is_cancelled);
        $activeSubtotal = $activeItems->sum('total_price');
        $cancelledTotal = $sale->saleItems->filter(fn($item) => $item->is_cancelled)->sum('total_price');

        return response()->json([
            'sale' => $sale,
            'active_subtotal' => $activeSubtotal,
            'cancelled_total' => $cancelledTotal,
            'can_edit' => $this->canEditOrder($sale),
        ]);
    }

    /**
     * Cancelar un item de la orden
     */
    public function cancelItem(Request $request, Sale $sale, SaleItem $item)
    {
        // Verificar que el item pertenece a la venta (usando cast a int por seguridad)
        if ((int) $item->sale_id !== (int) $sale->id) {
            \Illuminate\Support\Facades\Log::warning('SaleItem no pertenece a la venta', [
                'item_sale_id' => $item->sale_id,
                'sale_id' => $sale->id,
            ]);
            return response()->json(['error' => 'El item no pertenece a esta orden'], 403);
        }

        if (!$this->canEditOrder($sale)) {
            return response()->json(['error' => 'Esta orden no puede ser modificada'], 403);
        }

        if ($item->is_cancelled) {
            return response()->json(['error' => 'Este item ya está cancelado'], 400);
        }

        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $success = $this->saleService->cancelSaleItem(
            $item,
            auth()->id(),
            $request->reason
        );

        if (!$success) {
            return response()->json(['error' => 'No se pudo cancelar el item'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item cancelado correctamente',
        ]);
    }

    /**
     * Verificar si una orden puede ser editada
     */
    protected function canEditOrder(Sale $sale): bool
    {
        $kitchenStatus = $sale->kitchenOrderState?->status;
        if ($kitchenStatus && !in_array($kitchenStatus, ['nueva'])) {
            return false;
        }

        if ($sale->status !== 'pendiente') {
            return false;
        }

        return true;
    }

    /**
     * Obtener conteos por tipo de orden
     */
    protected function getOrderCounts(): array
    {
        // Condición base para órdenes activas
        $activeCondition = function ($query) {
            $query->where(function ($q) {
                $q->where('status', 'pendiente')
                    ->orWhereHas('kitchenOrderState', function ($sub) {
                        $sub->whereNotIn('status', ['entregada']);
                    });
            });
        };

        // Total de todas las órdenes activas
        $all = Sale::where($activeCondition)->count();

        // En local (tiene mesa o es dine_in)
        $local = Sale::where($activeCondition)
            ->where(function ($q) {
                $q->whereNotNull('table_id')
                    ->orWhere('delivery_method', 'dine_in');
            })
            ->count();

        // Para llevar (pickup o sin método definido y sin mesa)
        $takeaway = Sale::where($activeCondition)
            ->where(function ($q) {
                $q->where('delivery_method', 'pickup')
                    ->orWhere(function ($sub) {
                        $sub->whereNull('table_id')
                            ->whereNull('delivery_method');
                    });
            })
            ->count();

        // Delivery
        $delivery = Sale::where($activeCondition)
            ->where('delivery_method', 'delivery')
            ->count();

        return [
            'all' => $all,
            'local' => $local,
            'takeaway' => $takeaway,
            'delivery' => $delivery,
        ];
    }

    /**
     * Buscar clientes por nombre o teléfono
     */
    public function searchCustomers(Request $request)
    {
        $search = $request->get('q', '');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $customers = \App\Models\DigitalCustomer::query()
            ->where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->select(['id', 'name', 'phone', 'email', 'orders_count', 'total_spent'])
            ->orderBy('orders_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json($customers);
    }

    /**
     * Asignar cliente a una orden
     */
    public function assignCustomer(Request $request, Sale $sale)
    {
        if (!$this->canEditOrder($sale)) {
            return response()->json(['error' => 'Esta orden no puede ser modificada'], 403);
        }

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:digital_customers,id',
            'customer_name' => 'nullable|string|max:255',
        ]);

        // Si se proporciona customer_id, asociar el cliente digital
        if (!empty($validated['customer_id'])) {
            $customer = \App\Models\DigitalCustomer::find($validated['customer_id']);
            $sale->update([
                'digital_customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
            ]);
        } else {
            // Solo actualizar el nombre (cliente manual)
            $sale->update([
                'digital_customer_id' => null,
                'customer_name' => $validated['customer_name'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado correctamente',
            'customer_name' => $sale->customer_name,
        ]);
    }

    /**
     * Cancelar un pedido completo
     */
    public function cancelOrder(Request $request, Sale $sale)
    {
        // Verificar que el pedido está pendiente
        if ($sale->status !== 'pendiente') {
            return response()->json([
                'success' => false,
                'error' => 'Solo se pueden cancelar pedidos pendientes',
            ], 400);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // Liberar mesa si está asignada
            if ($sale->table_id) {
                $table = \App\Models\Table::find($sale->table_id);
                if ($table) {
                    $table->update([
                        'status' => 'disponible',
                        'current_sale_id' => null,
                    ]);
                }
            }

            // Cancelar todos los items
            $sale->saleItems()->update([
                'cancelled_at' => now(),
                'cancelled_by_user_id' => auth()->id(),
                'cancellation_reason' => $validated['reason'] ?? 'Pedido cancelado',
            ]);

            // Actualizar estado del pedido
            $sale->update([
                'status' => 'cancelada',
                'notes' => ($sale->notes ? $sale->notes . "\n" : '') .
                    'Cancelado: ' . ($validated['reason'] ?? 'Sin motivo especificado'),
            ]);

            // Actualizar estado de cocina si existe
            if ($sale->kitchenOrderState) {
                $sale->kitchenOrderState->update([
                    'status' => 'cancelada',
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();

            \Illuminate\Support\Facades\Log::info('Pedido cancelado', [
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'reason' => $validated['reason'] ?? null,
                'cancelled_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pedido cancelado correctamente',
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error al cancelar pedido: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error al cancelar el pedido',
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de mesas
     */
    protected function getTableStatistics(): array
    {
        $tables = \App\Models\Table::where('is_active', true)->get();

        return [
            'total' => $tables->count(),
            'available' => $tables->where('status', 'disponible')->count(),
            'occupied' => $tables->where('status', 'ocupada')->count(),
            'reserved' => $tables->where('status', 'reservada')->count(),
            'cleaning' => $tables->where('status', 'en_limpieza')->count(),
            'occupancy_rate' => $tables->count() > 0
                ? round(($tables->where('status', 'ocupada')->count() / $tables->count()) * 100)
                : 0,
        ];
    }
}

