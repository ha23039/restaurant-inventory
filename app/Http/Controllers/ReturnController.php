<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessReturnRequest;
use App\Models\CashFlow;
use App\Models\InventoryMovement;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReturnController extends Controller
{
    /**
     * 📋 Mostrar lista de devoluciones
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', SaleReturn::class);

        $query = SaleReturn::with([
            'sale:id,sale_number,total,created_at',
            'processedByUser:id,name',
            'returnItems',
        ]);

        // Filtros avanzados
        if ($request->filled('date_from')) {
            $query->whereDate('return_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('return_date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        $returns = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Métricas avanzadas del día
        $todayReturns = SaleReturn::today()->completed()->sum('total_returned');
        $todayCount = SaleReturn::today()->completed()->count();
        $pendingCount = SaleReturn::where('status', 'pending')->count();

        return Inertia::render('Returns/Index', [
            'returns' => $returns,
            'filters' => $request->only(['date_from', 'date_to', 'status', 'reason']),
            'metrics' => [
                'today_returns' => $todayReturns,
                'today_count' => $todayCount,
                'pending_count' => $pendingCount,
            ],
        ]);
    }

    /**
     * Mostrar formulario de nueva devolución (ACTUALIZADO PARA LIVE SEARCH Y SALE_ID)
     */
    public function create(Request $request)
    {
        $this->authorize('processReturn', SaleReturn::class);

        $saleId = $request->get('sale_id');
        $search = $request->get('search');
        $sale = null;
        $searchResults = collect([]);

        // MEJORADO: Si hay sale_id específico (modo directo desde historial)
        if ($saleId) {
            $sale = Sale::with([
                'user:id,name',
                'saleItems.menuItem:id,name,description',
                'saleItems.simpleProduct:id,name,description',
                'completedReturns', // NUEVA: Cargar devoluciones
            ])->findOrFail($saleId);

            // Agregar información completa de devoluciones
            $sale->total_returned = $sale->completedReturns->sum('total_returned');
            $sale->can_return = $sale->total_returned < $sale->total;

            $sale->saleItems->each(function ($item) {
                $totalReturned = SaleReturnItem::where('sale_item_id', $item->id)
                    ->whereHas('saleReturn', function ($query) {
                        $query->where('status', '!=', 'cancelled');
                    })
                    ->sum('quantity_returned');

                $item->quantity_returned = $totalReturned;
                $item->can_return_quantity = $item->quantity - $totalReturned;
                $item->can_return = $item->can_return_quantity > 0;
            });

            // NUEVO: Auto-buscar también para mostrar en resultados si se quiere cambiar
            if (!$search) {
                $search = $sale->sale_number; // Auto-llenar búsqueda con el número de venta
                $searchResults = collect([$sale]); // Mostrar la venta actual en resultados
            }
        }

        // Si hay parámetro de búsqueda (live search)
        if ($search && strlen($search) >= 1 && !$saleId) {
            $searchResults = $this->performSearch($search);

            \Log::info('Live Search - GET request:', [
                'search_term' => $search,
                'found_sales' => $searchResults->count(),
            ]);
        }

        return Inertia::render('Returns/Create', [
            'sale' => $sale,
            'searchResults' => $searchResults,
            'searchTerm' => $search,
            'payment_methods' => PaymentMethod::getActive(),
        ]);
    }

    /**
     * Buscar venta para devolución (MANTENER PARA COMPATIBILIDAD POST)
     */
    public function searchSale(Request $request)
    {
        $this->authorize('processReturn', SaleReturn::class);

        $request->validate([
            'search' => 'required|string|min:1',
        ]);

        $search = $request->search;

        // DEBUG: Log inicial
        \Log::info('INICIO - Búsqueda POST de ventas:', [
            'search_term' => $search,
            'search_length' => strlen($search),
            'is_ajax' => $request->ajax(),
            'wants_json' => $request->wantsJson(),
            'is_inertia' => $request->header('X-Inertia'),
        ]);

        $sales = $this->performSearch($search);

        \Log::info('POST Search:', [
            'search_term' => $search,
            'found_sales' => $sales->count(),
        ]);

        // 🎯 RESPUESTA PROFESIONAL PARA INERTIA
        return Inertia::render('Returns/Create', [
            'searchResults' => $sales,
            'searchTerm' => $search,
            'success' => "Se encontraron {$sales->count()} ventas para '{$search}'",
            'payment_methods' => PaymentMethod::getActive(),
        ]);
    }

    /**
     * MÉTODO CENTRAL: Realizar búsqueda (PROFESIONAL)
     */
    private function performSearch(string $search)
    {
        try {
            $query = Sale::with([
                'user:id,name',
                'saleItems.menuItem:id,name,description',
                'saleItems.simpleProduct:id,name,description',
                'completedReturns',
            ])
                ->where('status', 'completada');

            // Búsqueda inteligente y flexible
            if (is_numeric($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', $search)
                        ->orWhere('sale_number', 'like', "%{$search}")
                        ->orWhere('sale_number', 'like', "%{$search}%")
                        ->orWhereRaw('RIGHT(sale_number, LENGTH(?)) = ?', [$search, $search]);
                });
            } else {
                $query->where('sale_number', 'like', "%{$search}%");
            }

            $sales = $query->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            \Log::info('DEBUG - Consulta SQL ejecutada:', [
                'search_term' => $search,
                'found_sales' => $sales->count(),
                'found_numbers' => $sales->pluck('sale_number')->toArray(),
            ]);

            // Agregar información completa de devoluciones
            $sales->each(function ($sale) {
                $sale->total_returned = $sale->completedReturns->sum('total_returned');
                $sale->can_return = $sale->total_returned < $sale->total;

                // Info detallada de items
                $sale->saleItems->each(function ($item) {
                    $totalReturned = \App\Models\SaleReturnItem::where('sale_item_id', $item->id)
                        ->whereHas('saleReturn', function ($query) {
                            $query->where('status', '!=', 'cancelled');
                        })
                        ->sum('quantity_returned');

                    $item->quantity_returned = $totalReturned;
                    $item->can_return_quantity = $item->quantity - $totalReturned;
                    $item->can_return = $item->can_return_quantity > 0;
                });
            });

            \Log::info('DEBUG - Resultado final:', [
                'sales_count' => $sales->count(),
                'sales_with_return_info' => $sales->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'sale_number' => $sale->sale_number,
                        'total' => $sale->total,
                        'total_returned' => $sale->total_returned,
                        'can_return' => $sale->can_return,
                        'user_name' => $sale->user->name,
                    ];
                })->toArray(),
            ]);

            return $sales;

        } catch (\Exception $e) {
            \Log::error(' ERROR en búsqueda: '.$e->getMessage());

            return collect([]);
        }
    }

    /**
     * 💾 Procesar nueva devolución (IMPLEMENTACIÓN COMPLETA)
     */
    public function store(ProcessReturnRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            // Verificar que la venta existe y está completada CON LOCK
            $sale = Sale::lockForUpdate()->findOrFail($validated['sale_id']);

            if ($sale->status !== 'completada') {
                throw new \Exception('Solo se pueden hacer devoluciones de ventas completadas');
            }

            // Validar que las cantidades sean válidas CON LOCKS
            foreach ($validated['items'] as $itemData) {
                $saleItem = SaleItem::with(['menuItem.recipes.product', 'simpleProduct.product'])
                    ->lockForUpdate()
                    ->findOrFail($itemData['sale_item_id']);

                // Verificar que pertenece a la venta correcta
                if ($saleItem->sale_id !== $sale->id) {
                    throw new \Exception('El item no pertenece a esta venta');
                }

                // Verificar cantidad disponible para devolución
                $totalReturned = SaleReturnItem::where('sale_item_id', $saleItem->id)
                    ->whereHas('saleReturn', function ($query) {
                        $query->where('status', '!=', 'cancelled');
                    })
                    ->sum('quantity_returned');

                $availableToReturn = $saleItem->quantity - $totalReturned;

                if ($itemData['quantity'] > $availableToReturn) {
                    throw new \Exception("Solo se pueden devolver {$availableToReturn} unidades del producto");
                }
            }

            // Calcular totales de la devolución
            $subtotalReturned = 0;
            $totalReturned = 0;

            foreach ($validated['items'] as $itemData) {
                $saleItem = SaleItem::lockForUpdate()->findOrFail($itemData['sale_item_id']);
                $itemTotal = $saleItem->unit_price * $itemData['quantity'];
                $subtotalReturned += $itemTotal;
                $totalReturned += $itemTotal;
            }

            // Determinar tipo de devolución
            $returnType = ($totalReturned >= $sale->total) ? 'total' : 'partial';

            // Crear la devolución
            $saleReturn = SaleReturn::create([
                'sale_id' => $sale->id,
                'processed_by_user_id' => auth()->id(),
                'return_number' => SaleReturn::generateReturnNumber(),
                'return_type' => $returnType,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
                'subtotal_returned' => $subtotalReturned,
                'tax_returned' => 0, // Calcular proporcionalmente si es necesario
                'total_returned' => $totalReturned,
                'status' => 'pending',
                'refund_method' => $validated['refund_method'],
                'return_date' => now()->toDateString(),
            ]);

            // Crear los items de devolución
            foreach ($validated['items'] as $itemData) {
                $saleItem = SaleItem::findOrFail($itemData['sale_item_id']);

                SaleReturnItem::create([
                    'sale_return_id' => $saleReturn->id,
                    'sale_item_id' => $saleItem->id,
                    'quantity_returned' => $itemData['quantity'],
                    'original_quantity' => $saleItem->quantity,
                    'unit_price' => $saleItem->unit_price,
                    'total_price' => $saleItem->unit_price * $itemData['quantity'],
                ]);
            }

            // Auto-procesar la devolución (AUTOMATIZACIÓN COMPLETA)
            $this->processReturn($saleReturn);

            DB::commit();

            return redirect()->route('returns.show', $saleReturn)
                ->with('success', 'Devolución procesada exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error en devolución: '.$e->getMessage());

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 👁️ Mostrar detalle de devolución
     */
    public function show(SaleReturn $return)
    {
        $this->authorize('view', $return);

        $return->load([
            'sale.user:id,name',
            'processedByUser:id,name',
            'returnItems.saleItem.menuItem:id,name,description',
            'returnItems.saleItem.simpleProduct:id,name,description',
        ]);

        return Inertia::render('Returns/Show', [
            'return' => $return,
        ]);
    }

    /**
     * ⚡ CORE: Procesar devolución automáticamente
     */
    private function processReturn(SaleReturn $saleReturn)
    {
        if (!$saleReturn->canBeProcessed()) {
            throw new \Exception('La devolución no se puede procesar');
        }

        try {
            // 1. Restaurar inventario automáticamente
            $this->restoreInventory($saleReturn);

            // 2. Ajustar flujo de caja automáticamente
            $this->adjustCashFlow($saleReturn);

            // 3. Marcar como completada
            $saleReturn->update([
                'inventory_restored' => true,
                'cash_flow_adjusted' => true,
            ]);

            $saleReturn->markAsCompleted();

            \Log::info('Devolución procesada exitosamente', [
                'return_id' => $saleReturn->id,
                'return_number' => $saleReturn->return_number,
                'total' => $saleReturn->total_returned,
            ]);

        } catch (\Exception $e) {
            \Log::error(' Error procesando devolución: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * CORE: Gestión inteligente de inventario (REALISTA PARA RESTAURANTES)
     */
    private function restoreInventory(SaleReturn $saleReturn)
    {
        \Log::info("INICIANDO restauración de inventario para devolución: {$saleReturn->return_number}");

        foreach ($saleReturn->returnItems as $returnItem) {
            $saleItem = $returnItem->saleItem;

            if ($saleItem->product_type === 'menu') {
                // PRODUCTOS PREPARADOS: Solo registro de pérdida operativa
                \Log::info("PRODUCTO PREPARADO detectado: {$this->getItemName($saleItem)}");
                $this->recordOperationalLoss($returnItem);
                \Log::info('Pérdida operativa registrada - NO se restauran ingredientes');

            } elseif ($saleItem->product_type === 'simple') {
                // PRODUCTOS SIMPLES: Restaurar al inventario real
                \Log::info("PRODUCTO SIMPLE detectado: {$this->getItemName($saleItem)}");
                $this->restoreSimpleProductInventory($returnItem);
                \Log::info('Producto simple restaurado al inventario físico');
            }

            $returnItem->markInventoryRestored();
        }

        \Log::info("COMPLETADA restauración de inventario para: {$saleReturn->return_number}");
    }

    /**
     * LÓGICA REALISTA: Registro de pérdida operativa para productos preparados
     *
     * Cuando un cliente devuelve una hamburguesa, pizza, etc., ya preparada:
     * - NO podemos recuperar los ingredientes (pan, carne, condimentos)
     * - Registramos como "pérdida operativa" para contabilidad
     * - Creamos un producto virtual para tracking financiero
     */
    private function recordOperationalLoss(SaleReturnItem $returnItem)
    {
        $saleItem = $returnItem->saleItem;
        $menuItem = $saleItem->menuItem;

        if (!$menuItem) {
            \Log::warning("No se encontró el menú item para el sale_item: {$saleItem->id}");

            return;
        }

        // 🔧 Buscar o crear un producto especial para pérdidas operativas
        $lossProduct = \App\Models\Product::firstOrCreate(
            ['name' => 'Pérdidas Operativas - Productos Preparados'],
            [
                'category_id' => 1,
                'description' => 'Producto virtual para registrar pérdidas operativas de productos preparados devueltos. Los ingredientes no pueden recuperarse.',
                'unit_type' => 'unidad',
                'unit_cost' => 0,
                'current_stock' => 0,
                'min_stock' => 0,
                'max_stock' => 0,
            ]
        );

        // Crear movimiento de inventario como "pérdida operativa"
        InventoryMovement::create([
            'product_id' => $lossProduct->id,
            'user_id' => auth()->id(),
            'movement_type' => 'salida',
            'quantity' => $returnItem->quantity_returned,
            'unit_cost' => $returnItem->unit_price,
            'total_cost' => $returnItem->total_price,
            'reason' => 'perdida_operativa',
            'notes' => "PÉRDIDA OPERATIVA: {$menuItem->name} (Qty: {$returnItem->quantity_returned}) devuelto.
                        Return #{$returnItem->saleReturn->return_number}.
                        IMPORTANTE: Producto ya preparado no puede restaurarse al inventario.
                        Los ingredientes utilizados se consideran pérdida total.",
            'movement_date' => now()->toDateString(),
        ]);

        // Log detallado para auditoria
        \Log::info('PÉRDIDA OPERATIVA registrada:', [
            'producto' => $menuItem->name,
            'cantidad' => $returnItem->quantity_returned,
            'valor_perdido' => $returnItem->total_price,
            'razon' => 'Producto preparado no recuperable',
            'return_number' => $returnItem->saleReturn->return_number,
        ]);
    }

    /**
     * LÓGICA REALISTA: Restaurar inventario de productos simples
     *
     * Cuando un cliente devuelve una soda, agua embotellada, etc.:
     * - SÍ podemos recuperar el producto físico
     * - Lo restauramos al inventario para reventa
     * - Actualizamos el stock disponible
     */
    private function restoreSimpleProductInventory(SaleReturnItem $returnItem)
    {
        $simpleProduct = $returnItem->saleItem->simpleProduct()->with('product')->first();

        if (!$simpleProduct || !$simpleProduct->product) {
            \Log::warning("No se encontró el producto base para: {$returnItem->saleItem->id}");

            return;
        }

        // 🔢 Calcular cantidad exacta a restaurar
        $quantityToRestore = $simpleProduct->cost_per_unit * $returnItem->quantity_returned;

        // Crear movimiento de inventario (entrada)
        InventoryMovement::create([
            'product_id' => $simpleProduct->product_id,
            'user_id' => auth()->id(),
            'movement_type' => 'entrada',
            'quantity' => $quantityToRestore,
            'unit_cost' => $simpleProduct->product->unit_cost,
            'total_cost' => $quantityToRestore * $simpleProduct->product->unit_cost,
            'reason' => 'devolucion_producto_simple',
            'notes' => "DEVOLUCIÓN: {$simpleProduct->name} (Qty: {$returnItem->quantity_returned}) devuelto.
                        Return #{$returnItem->saleReturn->return_number}.
                        Producto físico recuperado y disponible para reventa.",
            'movement_date' => now()->toDateString(),
        ]);

        // Restaurar stock físico del producto
        $simpleProduct->product->increment('current_stock', $quantityToRestore);

        // Log detallado para auditoria
        \Log::info('INVENTARIO RESTAURADO:', [
            'producto' => $simpleProduct->name,
            'cantidad_restaurada' => $quantityToRestore,
            'stock_anterior' => $simpleProduct->product->current_stock - $quantityToRestore,
            'stock_actual' => $simpleProduct->product->current_stock,
            'valor_recuperado' => $quantityToRestore * $simpleProduct->product->unit_cost,
            'return_number' => $returnItem->saleReturn->return_number,
        ]);
    }

    /**
     * CORE: Ajustar flujo de caja automáticamente (USANDO CATEGORÍA DEVOLUCIONES)
     */
    private function adjustCashFlow(SaleReturn $saleReturn)
    {
        // 🔧 DEBUG: Verificar qué categoría estamos usando
        \Log::info('Intentando crear cash flow con categoría: devoluciones');

        try {
            CashFlow::create([
                'user_id' => auth()->id(),
                'sale_id' => $saleReturn->sale_id,
                'type' => 'salida',
                'category' => 'devoluciones', // CAMBIO: usar nueva categoría específica
                'amount' => $saleReturn->total_returned,
                'description' => "Devolución #{$saleReturn->return_number} - {$this->getReasonText($saleReturn->reason)}",
                'flow_date' => $saleReturn->return_date,
            ]);

            \Log::info("Flujo de caja ajustado exitosamente: -{$saleReturn->total_returned}");
        } catch (\Exception $e) {
            \Log::error('❌ ERROR en cash flow: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * 🆔 Helper: Obtener nombre del item
     */
    private function getItemName($saleItem): string
    {
        if ($saleItem->product_type === 'menu' && $saleItem->menuItem) {
            return $saleItem->menuItem->name;
        } elseif ($saleItem->product_type === 'simple' && $saleItem->simpleProduct) {
            return $saleItem->simpleProduct->name;
        }

        return 'Producto desconocido';
    }

    /**
     * Obtener métricas avanzadas de devoluciones (MEJORADO CON PÉRDIDAS OPERATIVAS)
     */
    public function getMetrics(Request $request)
    {
        $this->authorize('viewReports', SaleReturn::class);

        $startDate = $request->get('start_date', today());
        $endDate = $request->get('end_date', today());

        // Métricas básicas
        $totalReturns = SaleReturn::whereBetween('return_date', [$startDate, $endDate])
            ->completed()
            ->sum('total_returned');

        $returnCount = SaleReturn::whereBetween('return_date', [$startDate, $endDate])
            ->completed()
            ->count();

        // Métricas de pérdidas operativas (productos preparados)
        $operationalLosses = InventoryMovement::whereBetween('movement_date', [$startDate, $endDate])
            ->where('reason', 'perdida_operativa')
            ->sum('total_cost');

        $operationalLossCount = InventoryMovement::whereBetween('movement_date', [$startDate, $endDate])
            ->where('reason', 'perdida_operativa')
            ->sum('quantity');

        // Métricas de productos restaurados (productos simples)
        $restoredValue = InventoryMovement::whereBetween('movement_date', [$startDate, $endDate])
            ->where('reason', 'devolucion_producto_simple')
            ->sum('total_cost');

        $restoredCount = InventoryMovement::whereBetween('movement_date', [$startDate, $endDate])
            ->where('reason', 'devolucion_producto_simple')
            ->sum('quantity');

        $metrics = [
            // Métricas básicas
            'total_returns' => $totalReturns,
            'return_count' => $returnCount,
            'pending_returns' => SaleReturn::where('status', 'pending')->count(),
            'return_rate' => $this->calculateReturnRate($startDate, $endDate),

            // NUEVAS: Métricas de pérdidas operativas
            'operational_losses' => $operationalLosses,
            'operational_loss_count' => $operationalLossCount,
            'operational_loss_percentage' => $totalReturns > 0 ? round(($operationalLosses / $totalReturns) * 100, 2) : 0,

            // NUEVAS: Métricas de productos restaurados
            'restored_value' => $restoredValue,
            'restored_count' => $restoredCount,
            'restored_percentage' => $totalReturns > 0 ? round(($restoredValue / $totalReturns) * 100, 2) : 0,

            // Razones más comunes
            'top_reasons' => SaleReturn::whereBetween('return_date', [$startDate, $endDate])
                ->completed()
                ->select('reason', DB::raw('COUNT(*) as count'))
                ->groupBy('reason')
                ->orderBy('count', 'desc')
                ->get(),

            // NUEVO: Desglose por tipo de producto
            'breakdown_by_type' => $this->getReturnBreakdownByType($startDate, $endDate),
        ];

        return response()->json($metrics);
    }

    /**
     * NUEVO: Desglose de devoluciones por tipo de producto
     */
    private function getReturnBreakdownByType($startDate, $endDate): array
    {
        // Obtener devoluciones del período
        $returnItems = SaleReturnItem::whereHas('saleReturn', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('return_date', [$startDate, $endDate])
                ->where('status', 'completed');
        })->with(['saleItem.menuItem', 'saleItem.simpleProduct'])->get();

        $menuReturns = 0;
        $menuValue = 0;
        $simpleReturns = 0;
        $simpleValue = 0;

        foreach ($returnItems as $item) {
            if ($item->saleItem->product_type === 'menu') {
                $menuReturns += $item->quantity_returned;
                $menuValue += $item->total_price;
            } elseif ($item->saleItem->product_type === 'simple') {
                $simpleReturns += $item->quantity_returned;
                $simpleValue += $item->total_price;
            }
        }

        return [
            'prepared_products' => [
                'count' => $menuReturns,
                'value' => $menuValue,
                'type' => 'Productos Preparados (Pérdida Total)',
                'icon' => '🍔',
            ],
            'simple_products' => [
                'count' => $simpleReturns,
                'value' => $simpleValue,
                'type' => 'Productos Simples (Recuperables)',
                'icon' => '🥤',
            ],
        ];
    }

    /**
     * 📈 NUEVO: Reporte de pérdidas operativas
     */
    public function getOperationalLossReport(Request $request)
    {
        $this->authorize('viewOperationalLosses', SaleReturn::class);

        $startDate = $request->get('start_date', today()->subDays(30));
        $endDate = $request->get('end_date', today());

        // Pérdidas por producto preparado
        $lossesByProduct = DB::table('inventory_movements')
            ->join('sale_return_items', function ($join) {
                $join->on('inventory_movements.notes', 'like', DB::raw("CONCAT('%Return #', (SELECT return_number FROM sale_returns WHERE id = sale_return_items.sale_return_id), '%')"));
            })
            ->join('sale_items', 'sale_return_items.sale_item_id', '=', 'sale_items.id')
            ->join('menu_items', 'sale_items.menu_item_id', '=', 'menu_items.id')
            ->where('inventory_movements.reason', 'perdida_operativa')
            ->whereBetween('inventory_movements.movement_date', [$startDate, $endDate])
            ->select(
                'menu_items.name',
                DB::raw('SUM(inventory_movements.quantity) as total_quantity'),
                DB::raw('SUM(inventory_movements.total_cost) as total_loss')
            )
            ->groupBy('menu_items.name')
            ->orderBy('total_loss', 'desc')
            ->get();

        // Resumen general
        $summary = [
            'total_loss_value' => $lossesByProduct->sum('total_loss'),
            'total_units_lost' => $lossesByProduct->sum('total_quantity'),
            'most_returned_product' => $lossesByProduct->first()?->name ?? 'N/A',
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ];

        return response()->json([
            'summary' => $summary,
            'losses_by_product' => $lossesByProduct,
            'period_days' => now()->parse($startDate)->diffInDays($endDate) + 1,
        ]);
    }

    /**
     * 📈 Calcular tasa de devolución
     */
    private function calculateReturnRate($startDate, $endDate): float
    {
        $totalSales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completada')
            ->sum('total');

        $totalReturns = SaleReturn::whereBetween('return_date', [$startDate, $endDate])
            ->completed()
            ->sum('total_returned');

        return $totalSales > 0 ? round(($totalReturns / $totalSales) * 100, 2) : 0;
    }

    /**
     * Helper: Obtener texto de razón
     */
    private function getReasonText($reason): string
    {
        $reasons = [
            'defective' => 'Producto defectuoso',
            'wrong_order' => 'Orden incorrecta',
            'customer_request' => 'Solicitud del cliente',
            'error' => 'Error del sistema',
            'other' => 'Otra razón',
        ];

        return $reasons[$reason] ?? 'Razón desconocida';
    }
}
