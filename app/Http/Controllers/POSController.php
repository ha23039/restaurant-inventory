<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\MenuItemResource;
use App\Http\Resources\SimpleProductResource;
use App\Models\PaymentMethod;
use App\Repositories\Contracts\SimpleProductRepositoryInterface;
use App\Services\MenuItemService;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class POSController extends Controller
{
    public function __construct(
        private MenuItemService $menuItemService,
        private SimpleProductRepositoryInterface $simpleProductRepository,
        private SaleService $saleService
    ) {
    }

    public function index()
    {
        $this->authorize('processSale', \App\Models\Sale::class);

        // Obtener platillos del menú con disponibilidad calculada
        $menuItems = $this->menuItemService->getAvailableMenuItems();

        // Cargar variantes para items que las tengan
        $menuItems->load([
            'variants' => function ($query) {
                $query->where('is_available', true)
                    ->ordered()
                    ->with(['recipes.product']);
            }
        ]);

        // Calcular disponibilidad de variantes
        $menuItems->each(function ($menuItem) {
            if ($menuItem->has_variants && $menuItem->variants) {
                foreach ($menuItem->variants as $variant) {
                    $variant->available_quantity = $variant->available_quantity;
                }
            }
        });

        // Obtener productos simples con disponibilidad
        $simpleProducts = $this->simpleProductRepository->getAvailableProducts();

        // Calcular disponibilidad para cada producto simple
        $simpleProducts->each(function ($item) {
            if ($item->allows_variants) {
                // Para productos con variantes, verificar si tiene variantes disponibles
                $hasAvailableVariants = false;
                $totalVariantStock = 0;

                foreach ($item->variants as $variant) {
                    if ($variant->is_available) {
                        $hasAvailableVariants = true;
                        $variantStock = $variant->available_quantity;
                        $totalVariantStock += $variantStock;
                    }
                }

                // Un producto con variantes está "en stock" si tiene variantes disponibles
                $item->available_quantity = $totalVariantStock;
                $item->is_in_stock = $hasAvailableVariants;
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
        });

        // Obtener ventas pendientes (órdenes activas)
        $pendingSales = $this->saleService->getPendingSales();

        // Obtener mesas disponibles para asignación
        $availableTables = \App\Models\Table::active()
            ->orderBy('table_number')
            ->get(['id', 'table_number', 'name', 'capacity', 'status', 'current_sale_id']);

        return Inertia::render('Sales/POS', [
            'menu_items' => MenuItemResource::collection($menuItems),
            'simple_products' => SimpleProductResource::collection($simpleProducts),
            'pending_sales' => $pendingSales,
            'available_tables' => $availableTables,
            'payment_methods' => PaymentMethod::getActive(),
        ]);
    }

    public function store(StoreSaleRequest $request)
    {
        try {
            $sale = $this->saleService->processSale($request->validated(), auth()->id());

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Venta procesada exitosamente');

        } catch (\Exception $e) {
            \Log::error('Error en POSController@store: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->validated(),
            ]);

            return back()->withErrors(['error' => $e->getMessage()])->with('error', $e->getMessage());
        }
    }

    public function completePendingSale(Request $request, \App\Models\Sale $sale)
    {
        try {
            $validated = $request->validate([
                'payment_method' => 'nullable|in:efectivo,tarjeta,transferencia',
            ]);

            // Verificar que la venta esté pendiente
            if ($sale->status !== 'pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta orden ya fue procesada'
                ], 400);
            }

            // Guardar info de origen antes de modificar
            $wasDigitalOrder = $sale->source === 'digital_menu';

            // Completar la venta usando el servicio (adopta al cajero actual)
            $completedSale = $this->saleService->completePendingSale(
                $sale,
                $validated['payment_method'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => "Orden #{$sale->sale_number} completada exitosamente",
                'sale' => $completedSale,
                'adopted_from_digital' => $wasDigitalOrder,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
