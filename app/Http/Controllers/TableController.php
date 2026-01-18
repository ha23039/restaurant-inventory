<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Sale;
use App\Services\TableService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class TableController extends Controller
{
    public function __construct(
        protected TableService $tableService
    ) {}

    /**
     * Display a listing of tables
     */
    public function index(Request $request): Response
    {
        $tables = $this->tableService->getAll(
            $request->only(['status', 'search'])
        );

        return Inertia::render('Tables/Index', [
            'tables' => $tables,
            'filters' => $request->only(['status', 'search']),
            'statistics' => $this->tableService->getStatistics(),
        ]);
    }

    /**
     * Store a newly created table
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'table_number' => 'required|string|max:20|unique:tables,table_number',
            'name' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1|max:50',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        try {
            $this->tableService->create($validated);

            return redirect()
                ->route('tables.index')
                ->with('success', 'Mesa creada exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al crear la mesa: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified table
     */
    public function update(Request $request, Table $table): RedirectResponse
    {
        $validated = $request->validate([
            'table_number' => 'required|string|max:20|unique:tables,table_number,' . $table->id,
            'name' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1|max:50',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        try {
            $this->tableService->update($table, $validated);

            return redirect()
                ->route('tables.index')
                ->with('success', 'Mesa actualizada exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Error al actualizar la mesa: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified table
     */
    public function destroy(Table $table): RedirectResponse
    {
        try {
            $this->tableService->delete($table);

            return redirect()
                ->route('tables.index')
                ->with('success', 'Mesa eliminada exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update table status
     */
    public function updateStatus(Request $request, Table $table): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:disponible,ocupada,reservada,en_limpieza',
        ]);

        try {
            $this->tableService->updateStatus($table, $validated['status']);

            return back()->with('success', 'Estado actualizado exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el estado: ' . $e->getMessage()]);
        }
    }

    /**
     * Occupy table with a sale
     */
    public function occupyTable(Request $request, Table $table): RedirectResponse
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
        ]);

        try {
            $sale = Sale::findOrFail($validated['sale_id']);
            $this->tableService->occupyTable($table, $sale);

            return back()->with('success', 'Mesa ocupada exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Release table (mark as available)
     */
    public function releaseTable(Table $table): RedirectResponse
    {
        try {
            $this->tableService->releaseTable($table);

            return back()->with('success', 'Mesa liberada exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al liberar la mesa: ' . $e->getMessage()]);
        }
    }

    /**
     * Get table with current sale details (for slide-over)
     */
    public function show(Table $table): JsonResponse
    {
        $tableWithSale = $this->tableService->getTableWithSale($table->id);

        return response()->json([
            'table' => $tableWithSale,
        ]);
    }

    /**
     * Toggle table active status
     */
    public function toggleActive(Table $table): RedirectResponse
    {
        try {
            $updatedTable = $this->tableService->toggleActive($table);

            $message = $updatedTable->is_active ? 'Mesa activada exitosamente' : 'Mesa desactivada exitosamente';
            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get all pending sales for a table (API for polling)
     */
    public function getPendingSales(Table $table): JsonResponse
    {
        $pendingSales = Sale::where('table_id', $table->id)
            ->whereIn('status', ['pendiente', 'en_preparacion'])
            ->with([
                'saleItems.menuItem',
                'saleItems.menuItemVariant.menuItem',
                'saleItems.simpleProduct',
                'saleItems.simpleProductVariant.simpleProduct',
                'digitalCustomer',
                'kitchenOrderState',
            ])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'status' => $sale->status,
                    'source' => $sale->source,
                    'customer_name' => $sale->customer_name ?: ($sale->digitalCustomer?->name ?: 'Cliente'),
                    'customer_phone' => $sale->customer_phone ?: $sale->digitalCustomer?->full_phone,
                    'subtotal' => (float) $sale->subtotal,
                    'discount' => (float) $sale->discount,
                    'total' => (float) $sale->total,
                    'created_at' => $sale->created_at->format('H:i'),
                    'kitchen_status' => $sale->kitchenOrderState?->status ?? 'sin_estado',
                    'kitchen_color' => $this->getKitchenStatusColor($sale->kitchenOrderState?->status),
                    'items' => $sale->saleItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $this->getSaleItemName($item),
                            'quantity' => $item->quantity,
                            'unit_price' => (float) $item->unit_price,
                            'subtotal' => (float) ($item->total_price ?? $item->subtotal ?? $item->unit_price * $item->quantity),
                        ];
                    }),
                ];
            });

        $totalPending = $pendingSales->sum('total');

        return response()->json([
            'table' => [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'name' => $table->name,
                'capacity' => $table->capacity,
                'status' => $table->status,
            ],
            'pending_sales' => $pendingSales,
            'total_pending' => $totalPending,
            'sales_count' => $pendingSales->count(),
        ]);
    }

    /**
     * Charge (complete) a sale from table view
     */
    public function chargeSale(Request $request, Sale $sale): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:efectivo,tarjeta,transferencia',
            'discount' => 'nullable|numeric|min:0',
        ]);

        try {
            \DB::beginTransaction();

            $discount = $validated['discount'] ?? 0;
            $newTotal = $sale->subtotal - $discount;

            // Update sale
            $sale->update([
                'status' => 'completada',
                'payment_method' => $validated['payment_method'],
                'discount' => $discount,
                'total' => $newTotal,
            ]);

            // Update kitchen order state if exists
            if ($sale->kitchenOrderState) {
                $sale->kitchenOrderState->update([
                    'status' => 'entregada',
                ]);
            }

            // Create cash flow entry
            \App\Models\CashFlow::create([
                'sale_id' => $sale->id,
                'user_id' => auth()->id(),
                'type' => 'entrada',
                'category' => 'ventas',
                'amount' => $newTotal,
                'description' => "Cobro de venta #{$sale->sale_number}",
                'payment_method' => $validated['payment_method'],
                'flow_date' => now(),
            ]);

            // Check if table should be released
            $tableId = $sale->table_id;
            if ($tableId) {
                $remainingPendingSales = Sale::where('table_id', $tableId)
                    ->whereIn('status', ['pendiente', 'en_preparacion'])
                    ->count();

                if ($remainingPendingSales === 0) {
                    $table = Table::find($tableId);
                    if ($table) {
                        $table->update([
                            'status' => 'disponible',
                            'current_sale_id' => null,
                        ]);
                    }
                }
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta cobrada exitosamente',
                'sale' => $sale->fresh(),
                'table_released' => isset($remainingPendingSales) && $remainingPendingSales === 0,
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cobrar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Charge all pending sales for a table at once
     */
    public function chargeAllSales(Request $request, Table $table): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:efectivo,tarjeta,transferencia',
            'discount' => 'nullable|numeric|min:0',
        ]);

        try {
            \DB::beginTransaction();

            $pendingSales = Sale::where('table_id', $table->id)
                ->whereIn('status', ['pendiente', 'en_preparacion'])
                ->get();

            if ($pendingSales->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay ventas pendientes en esta mesa',
                ], 422);
            }

            $totalAmount = $pendingSales->sum('total');
            $discount = $validated['discount'] ?? 0;
            $finalTotal = $totalAmount - $discount;

            // Update all sales
            foreach ($pendingSales as $sale) {
                $sale->update([
                    'status' => 'completada',
                    'payment_method' => $validated['payment_method'],
                ]);

                // Update kitchen order state if exists
                if ($sale->kitchenOrderState) {
                    $sale->kitchenOrderState->update([
                        'status' => 'entregada',
                    ]);
                }
            }

            // Create single cash flow entry for all sales
            \App\Models\CashFlow::create([
                'user_id' => auth()->id(),
                'type' => 'entrada',
                'category' => 'ventas',
                'amount' => $finalTotal,
                'description' => "Cobro conjunto mesa {$table->table_number} ({$pendingSales->count()} ventas)",
                'payment_method' => $validated['payment_method'],
                'flow_date' => now(),
            ]);

            // Release table
            $table->update([
                'status' => 'disponible',
                'current_sale_id' => null,
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Se cobraron {$pendingSales->count()} ventas por \${$finalTotal}",
                'total_charged' => $finalTotal,
                'sales_count' => $pendingSales->count(),
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cobrar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get kitchen status color
     */
    private function getKitchenStatusColor(?string $status): string
    {
        return match ($status) {
            'nueva' => 'blue',
            'preparando' => 'yellow',
            'lista' => 'green',
            'entregada' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get sale item name
     */
    private function getSaleItemName($item): string
    {
        if ($item->product_type === 'variant' && $item->menuItemVariant) {
            $parentName = $item->menuItemVariant->menuItem->name ?? '';
            $variantName = $item->menuItemVariant->variant_name;
            return $parentName ? "{$parentName} - {$variantName}" : $variantName;
        } elseif ($item->product_type === 'simple_variant' && $item->simpleProductVariant) {
            $parentName = $item->simpleProductVariant->simpleProduct->name ?? '';
            $variantName = $item->simpleProductVariant->variant_name;
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
}
