<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn; // 🔄 NUEVA IMPORTACIÓN
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Sale::class);

        // 🔄 ACTUALIZADO: Incluir devoluciones, mesa, variantes y combos en la consulta
        $query = Sale::with([
            'user',
            'table',
            'saleItems.menuItem',
            'saleItems.simpleProduct',
            'saleItems.menuItemVariant',  // 🆕 Variantes
            'saleItems.combo',  // 🆕 Combos
            'completedReturns',  // 🔄 NUEVA RELACIÓN
        ]);

        // Filtros existentes
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 🔄 NUEVO FILTRO: Ventas con devoluciones
        if ($request->filled('has_returns')) {
            if ($request->has_returns === 'with_returns') {
                $query->whereHas('completedReturns');
            } elseif ($request->has_returns === 'without_returns') {
                $query->whereDoesntHave('completedReturns');
            } elseif ($request->has_returns === 'partial_returns') {
                // Ventas con devoluciones parciales (devuelto < total)
                $query->whereHas('completedReturns')
                    ->whereRaw('(SELECT COALESCE(SUM(total_returned), 0) FROM sale_returns WHERE sale_returns.sale_id = sales.id AND sale_returns.status = "completed") < sales.total');
            } elseif ($request->has_returns === 'full_returns') {
                // Ventas con devoluciones totales (devuelto >= total)
                $query->whereHas('completedReturns')
                    ->whereRaw('(SELECT COALESCE(SUM(total_returned), 0) FROM sale_returns WHERE sale_returns.sale_id = sales.id AND sale_returns.status = "completed") >= sales.total');
            }
        }

        $sales = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // 🔄 MEJORADO: Calcular información de devoluciones para cada venta
        $sales->getCollection()->transform(function ($sale) {
            $sale->total_returned = $sale->completedReturns->sum('total_returned');
            $sale->has_returns = $sale->total_returned > 0;
            $sale->net_total = $sale->total - $sale->total_returned;
            $sale->return_percentage = $sale->total > 0 ? round(($sale->total_returned / $sale->total) * 100, 1) : 0;
            $sale->can_return = ($sale->status === 'completada') && ($sale->total_returned < $sale->total);

            return $sale;
        });

        // 📊 Métricas mejoradas del día
        $today = today();

        // Ventas del día
        $todaySales = Sale::whereDate('created_at', $today)
            ->where('status', 'completada')
            ->sum('total');

        $todayTransactions = Sale::whereDate('created_at', $today)
            ->where('status', 'completada')
            ->count();

        // 🔄 MEJORADO: Métricas de devoluciones del día
        $todayReturns = SaleReturn::whereDate('return_date', $today)
            ->where('status', 'completed')
            ->sum('total_returned');

        $todayReturnCount = SaleReturn::whereDate('return_date', $today)
            ->where('status', 'completed')
            ->count();

        // 🔄 NUEVA: Métricas adicionales
        $salesWithReturnsToday = Sale::whereDate('created_at', $today)
            ->where('status', 'completada')
            ->whereHas('completedReturns')
            ->count();

        $netSalesToday = $todaySales - $todayReturns;
        $returnRateToday = $todaySales > 0 ? round(($todayReturns / $todaySales) * 100, 2) : 0;

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'filters' => $request->only(['date_from', 'date_to', 'status', 'has_returns']), // 🔄 AGREGADO has_returns
            'metrics' => [
                'today_sales' => $todaySales,
                'today_transactions' => $todayTransactions,
                'today_returns' => $todayReturns, // 🔄 EXISTENTE
                'today_return_count' => $todayReturnCount, // 🔄 NUEVO
                'sales_with_returns_today' => $salesWithReturnsToday, // 🔄 NUEVO
                'net_sales_today' => $netSalesToday, // 🔄 NUEVO
                'return_rate_today' => $returnRateToday, // 🔄 NUEVO
            ],
        ]);
    }

    public function show(Sale $sale)
    {
        $this->authorize('view', $sale);

        // DEBUG: Log la venta que se está mostrando
        \Log::info('🧪 SaleController@show - Venta solicitada:', ['sale_id' => $sale->id]);

        // 🔄 ACTUALIZADO: Cargar todas las relaciones necesarias incluyendo devoluciones y mesa
        $sale = Sale::with([
            'user:id,name,email',
            'table',
            'saleItems' => function ($query) {
                $query->with([
                    'menuItem:id,name,description,price',
                    'menuItem.recipes' => function ($recipeQuery) {
                        $recipeQuery->with('product:id,name,unit_type');
                    },
                    'simpleProduct:id,name,description,sale_price,category',
                    'simpleProductVariant:id,simple_product_id,variant_name,price',  // 🆕 Variantes de producto simple
                    'simpleProductVariant.simpleProduct:id,name',  // 🆕 Producto padre de la variante
                    'menuItemVariant:id,menu_item_id,variant_name,price,attributes',  // 🆕 Variantes
                    'menuItemVariant.menuItem:id,name',  // 🆕 Platillo padre de la variante
                    'combo:id,name,description,base_price',  // 🆕 Combos
                ]);
            },
            'completedReturns.returnItems.saleItem', // 🔄 NUEVA: Cargar devoluciones completas
        ])->findOrFail($sale->id);

        // TRANSFORMAR DATOS: Asegurar estructura consistente
        $transformedSale = [
            'id' => $sale->id,
            'sale_number' => $sale->sale_number,
            'customer_name' => $sale->customer_name,
            'notes' => $sale->notes,
            'subtotal' => floatval($sale->subtotal),
            'discount' => floatval($sale->discount),
            'tax' => floatval($sale->tax),
            'total' => floatval($sale->total),
            'payment_method' => $sale->payment_method,
            'status' => $sale->status,
            'created_at' => $sale->created_at->toISOString(),
            'updated_at' => $sale->updated_at->toISOString(),

            // 🔄 MEJORADO: Información de devoluciones
            'total_returned' => $sale->completedReturns->sum('total_returned'),
            'has_returns' => $sale->completedReturns->count() > 0,
            'net_total' => $sale->total - $sale->completedReturns->sum('total_returned'),
            'can_return' => ($sale->status === 'completada') && ($sale->completedReturns->sum('total_returned') < $sale->total),

            // Usuario
            'user' => [
                'id' => $sale->user->id,
                'name' => $sale->user->name,
                'email' => $sale->user->email,
            ],

            // Mesa (si está asignada)
            'table' => $sale->table ? [
                'id' => $sale->table->id,
                'table_number' => $sale->table->table_number,
                'name' => $sale->table->name,
                'capacity' => $sale->table->capacity,
                'status' => $sale->table->status,
            ] : null,

            // Items transformados
            'sale_items' => $sale->saleItems->map(function ($item) use ($sale) {
                $baseItem = [
                    'id' => $item->id,
                    'quantity' => intval($item->quantity),
                    'unit_price' => floatval($item->unit_price),
                    'total_price' => floatval($item->total_price ?? ($item->quantity * $item->unit_price)),
                    'product_type' => $item->product_type,
                ];

                // 🔄 NUEVO: Agregar información de devoluciones por item
                $quantityReturned = DB::table('sale_return_items')
                    ->join('sale_returns', 'sale_return_items.sale_return_id', '=', 'sale_returns.id')
                    ->where('sale_return_items.sale_item_id', $item->id)
                    ->where('sale_returns.status', '!=', 'cancelled')
                    ->sum('sale_return_items.quantity_returned');

                $baseItem['quantity_returned'] = intval($quantityReturned);
                $baseItem['can_return_quantity'] = $item->quantity - $quantityReturned;
                $baseItem['can_return'] = ($baseItem['can_return_quantity'] > 0) && ($sale->status === 'completada');

                // Agregar datos específicos según el tipo
                if ($item->product_type === 'free') {
                    // Venta libre - usar campos free_sale_*
                    $baseItem['free_sale'] = [
                        'name' => $item->free_sale_name,
                        'price' => floatval($item->free_sale_price),
                    ];
                    $baseItem['product_name'] = $item->free_sale_name;
                } elseif ($item->product_type === 'menu' && $item->menuItem) {
                    $baseItem['menu_item'] = [
                        'id' => $item->menuItem->id,
                        'name' => $item->menuItem->name,
                        'description' => $item->menuItem->description,
                        'price' => floatval($item->menuItem->price),
                        'recipes' => $item->menuItem->recipes->map(function ($recipe) {
                            return [
                                'id' => $recipe->id,
                                'quantity_needed' => floatval($recipe->quantity_needed),
                                'product' => $recipe->product ? [
                                    'id' => $recipe->product->id,
                                    'name' => $recipe->product->name,
                                    'unit_type' => $recipe->product->unit_type,
                                ] : null,
                            ];
                        })->toArray(),
                    ];
                    $baseItem['product_name'] = $item->menuItem->name;
                } elseif ($item->product_type === 'simple' && $item->simpleProduct) {
                    $baseItem['simple_product'] = [
                        'id' => $item->simpleProduct->id,
                        'name' => $item->simpleProduct->name,
                        'description' => $item->simpleProduct->description,
                        'sale_price' => floatval($item->simpleProduct->sale_price),
                        'category' => $item->simpleProduct->category,
                    ];
                    $baseItem['product_name'] = $item->simpleProduct->name;
                } elseif ($item->product_type === 'variant' && $item->menuItemVariant) {
                    // 🆕 Variantes de platillo
                    $baseItem['menu_item_variant'] = [
                        'id' => $item->menuItemVariant->id,
                        'variant_name' => $item->menuItemVariant->variant_name,
                        'price' => floatval($item->menuItemVariant->price),
                        'attributes' => $item->menuItemVariant->attributes,
                        'menu_item' => $item->menuItemVariant->menuItem ? [
                            'id' => $item->menuItemVariant->menuItem->id,
                            'name' => $item->menuItemVariant->menuItem->name,
                        ] : null,
                    ];
                    $parentName = $item->menuItemVariant->menuItem ? $item->menuItemVariant->menuItem->name : '';
                    $baseItem['product_name'] = $parentName . ' - ' . $item->menuItemVariant->variant_name;
                } elseif ($item->product_type === 'simple_variant' && $item->simpleProductVariant) {
                    // 🆕 Variantes de producto simple (bebidas, etc)
                    $baseItem['simple_product_variant'] = [
                        'id' => $item->simpleProductVariant->id,
                        'variant_name' => $item->simpleProductVariant->variant_name,
                        'price' => floatval($item->simpleProductVariant->price),
                        'simple_product' => $item->simpleProductVariant->simpleProduct ? [
                            'id' => $item->simpleProductVariant->simpleProduct->id,
                            'name' => $item->simpleProductVariant->simpleProduct->name,
                        ] : null,
                    ];
                    $parentName = $item->simpleProductVariant->simpleProduct ? $item->simpleProductVariant->simpleProduct->name : '';
                    $baseItem['product_name'] = $parentName . ' - ' . $item->simpleProductVariant->variant_name;
                } elseif ($item->product_type === 'combo' && $item->combo) {
                    // 🆕 Combos
                    $comboSelections = is_string($item->combo_selections)
                        ? json_decode($item->combo_selections, true)
                        : $item->combo_selections;

                    $baseItem['combo'] = [
                        'id' => $item->combo->id,
                        'name' => $item->combo->name,
                        'description' => $item->combo->description,
                        'base_price' => floatval($item->combo->base_price),
                    ];
                    $baseItem['combo_selections'] = $comboSelections;
                    $baseItem['components_detail'] = $comboSelections['components_detail'] ?? [];
                    $baseItem['product_name'] = $item->combo->name;
                }

                return $baseItem;
            })->toArray(),

            // 🔄 MEJORADO: Información detallada de devoluciones
            'returns' => $sale->completedReturns->map(function ($return) {
                return [
                    'id' => $return->id,
                    'return_number' => $return->return_number,
                    'total_returned' => floatval($return->total_returned),
                    'reason' => $return->reason,
                    'return_date' => $return->return_date,
                    'items_count' => $return->returnItems->count(),
                    'return_type' => $return->return_type,
                    'refund_method' => $return->refund_method,
                ];
            })->toArray(),
        ];

        // DEBUG: Log los datos transformados
        \Log::info('🧪 SaleController@show - Datos transformados:', [
            'sale_id' => $transformedSale['id'],
            'sale_number' => $transformedSale['sale_number'],
            'items_count' => count($transformedSale['sale_items']),
            'user_name' => $transformedSale['user']['name'],
            'total_returned' => $transformedSale['total_returned'], // 🔄 NUEVO DEBUG
            'net_total' => $transformedSale['net_total'], // 🔄 NUEVO DEBUG
            'can_return' => $transformedSale['can_return'], // 🔄 NUEVO DEBUG
        ]);

        return Inertia::render('Sales/SaleDetail', [
            'sale' => $transformedSale,
        ]);
    }

    /**
     * 🔄 Función para refrescar el stock disponible
     * Útil para actualizar disponibilidad en tiempo real
     */
    public function refreshStock()
    {
        try {
            // Actualizar disponibilidad de productos del menú
            $menuItems = \App\Models\MenuItem::with(['recipes.product'])->get();
            foreach ($menuItems as $item) {
                $availableQuantity = $this->calculateAvailableQuantity($item);
                // Aquí podrías guardar en caché o retornar para el frontend
            }

            // Actualizar disponibilidad de productos simples
            $simpleProducts = \App\Models\SimpleProduct::with('product')->get();
            foreach ($simpleProducts as $product) {
                if ($product->product) {
                    $currentStock = floatval($product->product->current_stock);
                    $costPerUnit = floatval($product->cost_per_unit);
                    $available = $costPerUnit > 0 ? floor($currentStock / $costPerUnit) : 0;
                    // Aquí podrías guardar en caché o retornar para el frontend
                }
            }

            return response()->json(['success' => true, 'message' => 'Stock actualizado']);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar stock: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al actualizar stock'], 500);
        }
    }

    /**
     * 🧮 Calcular cantidad disponible para un platillo del menú
     */
    private function calculateAvailableQuantity(\App\Models\MenuItem $menuItem)
    {
        if ($menuItem->recipes->isEmpty()) {
            return 999; // Si no tiene receta, asumimos disponible
        }

        $minQuantity = PHP_INT_MAX;

        foreach ($menuItem->recipes as $recipe) {
            $product = $recipe->product;
            $neededQuantity = $recipe->quantity_needed;

            if ($neededQuantity > 0) {
                $possibleQuantity = floor($product->current_stock / $neededQuantity);
                $minQuantity = min($minQuantity, $possibleQuantity);
            }
        }

        return max(0, $minQuantity);
    }

    /**
     * 📊 Obtener métricas de ventas para dashboard (MEJORADO)
     */
    public function getMetrics(Request $request)
    {
        $this->authorize('viewReports', Sale::class);

        $startDate = $request->get('start_date', today());
        $endDate = $request->get('end_date', today());

        // 🔄 MEJORADO: Incluir métricas completas de devoluciones
        $totalSales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completada')
            ->sum('total');

        $totalReturns = SaleReturn::whereBetween('return_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('total_returned');

        $metrics = [
            'total_sales' => $totalSales,
            'total_transactions' => Sale::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completada')
                ->count(),
            'average_ticket' => Sale::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completada')
                ->avg('total'),

            // 🔄 MEJORADO: Métricas de devoluciones
            'total_returns' => $totalReturns,
            'net_sales' => $totalSales - $totalReturns,
            'return_count' => SaleReturn::whereBetween('return_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->count(),
            'return_rate' => $this->calculateReturnRate($startDate, $endDate),
            'sales_with_returns' => Sale::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completada')
                ->whereHas('completedReturns')
                ->count(),

            'top_selling_items' => SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereBetween('sales.created_at', [$startDate, $endDate])
                ->where('sales.status', 'completada')
                ->select(
                    'sale_items.menu_item_id',
                    'sale_items.simple_product_id',
                    DB::raw('SUM(sale_items.quantity) as total_quantity')
                )
                ->groupBy('sale_items.menu_item_id', 'sale_items.simple_product_id')
                ->orderBy('total_quantity', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json($metrics);
    }

    /**
     * 🔄 MEJORADO: Calcular tasa de devolución
     */
    private function calculateReturnRate($startDate, $endDate): float
    {
        $totalSales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completada')
            ->sum('total');

        $totalReturns = SaleReturn::whereBetween('return_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('total_returned');

        return $totalSales > 0 ? round(($totalReturns / $totalSales) * 100, 2) : 0;
    }

    /**
     * Eliminar o cancelar una venta (solo admin)
     */
    public function destroy(Sale $sale)
    {
        $this->authorize('delete', $sale);

        // Verificar si tiene devoluciones
        if ($sale->completedReturns()->count() > 0) {
            return back()->with('error', 'No se puede eliminar una venta que tiene devoluciones asociadas');
        }

        DB::beginTransaction();
        try {
            // Si la venta está pendiente, eliminar físicamente
            if ($sale->status === 'pendiente') {
                // Eliminar items de la venta
                $sale->saleItems()->delete();
                // Eliminar la venta
                $sale->delete();

                DB::commit();
                return redirect()->route('sales.index')
                    ->with('success', 'Venta eliminada exitosamente');
            }

            // Si la venta está completada, marcar como cancelada y revertir flujo de caja
            if ($sale->status === 'completada') {
                // Buscar y eliminar el registro de flujo de caja asociado
                \App\Models\CashFlow::where('sale_id', $sale->id)
                    ->where('category', 'ventas')
                    ->delete();

                // Cambiar status a cancelada
                $sale->update(['status' => 'cancelada']);

                DB::commit();
                return redirect()->route('sales.index')
                    ->with('success', 'Venta cancelada exitosamente. El flujo de caja ha sido ajustado.');
            }

            // Si ya está cancelada
            if ($sale->status === 'cancelada') {
                return back()->with('error', 'Esta venta ya está cancelada');
            }

            DB::commit();
            return back()->with('error', 'No se puede procesar esta venta');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar/cancelar venta: ' . $e->getMessage());
            return back()->with('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }

    /**
     * 🔄 MEJORADO: Obtener ventas que pueden tener devoluciones
     */
    public function getReturnableSales(Request $request)
    {
        $search = $request->get('search', '');

        $query = Sale::with([
            'user:id,name',
            'saleItems.menuItem:id,name',
            'saleItems.simpleProduct:id,name',
            'completedReturns',
        ])
            ->where('status', 'completada');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                    ->orWhere('id', $search)
                    ->orWhereRaw('SUBSTRING(sale_number, -4) = ?', [$search]);
            });
        }

        $sales = $query->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // 🔄 MEJORADO: Agregar información completa de devoluciones
        $sales->each(function ($sale) {
            $sale->total_returned = $sale->completedReturns->sum('total_returned');
            $sale->can_return = $sale->total_returned < $sale->total;
            $sale->net_total = $sale->total - $sale->total_returned;
            $sale->return_percentage = $sale->total > 0 ? round(($sale->total_returned / $sale->total) * 100, 1) : 0;

            // Agregar info de items que pueden devolverse
            $sale->saleItems->each(function ($item) {
                $quantityReturned = DB::table('sale_return_items')
                    ->join('sale_returns', 'sale_return_items.sale_return_id', '=', 'sale_returns.id')
                    ->where('sale_return_items.sale_item_id', $item->id)
                    ->where('sale_returns.status', '!=', 'cancelled')
                    ->sum('sale_return_items.quantity_returned');

                $item->quantity_returned = $quantityReturned;
                $item->can_return_quantity = $item->quantity - $quantityReturned;
                $item->can_return = $item->can_return_quantity > 0;
            });
        });

        return response()->json(['sales' => $sales]);
    }
}
