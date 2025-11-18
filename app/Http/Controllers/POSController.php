<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\MenuItemResource;
use App\Http\Resources\SimpleProductResource;
use App\Repositories\Contracts\SimpleProductRepositoryInterface;
use App\Services\MenuItemService;
use App\Services\SaleService;
use Inertia\Inertia;

class POSController extends Controller
{
    public function __construct(
        private MenuItemService $menuItemService,
        private SimpleProductRepositoryInterface $simpleProductRepository,
        private SaleService $saleService
    ) {}

    public function index()
    {
        $this->authorize('processSale', \App\Models\Sale::class);

        // Obtener platillos del menú con disponibilidad calculada
        $menuItems = $this->menuItemService->getAvailableMenuItems();

        // Obtener productos simples con disponibilidad
        $simpleProducts = $this->simpleProductRepository->getAvailableProducts();

        // Calcular disponibilidad para cada producto simple
        $simpleProducts->each(function ($item) {
            if ($item->product) {
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

        return Inertia::render('Sales/POS', [
            'menu_items' => MenuItemResource::collection($menuItems),
            'simple_products' => SimpleProductResource::collection($simpleProducts),
        ]);
    }

    public function store(StoreSaleRequest $request)
    {
        try {
            $sale = $this->saleService->processSale($request->validated(), auth()->id());

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Venta procesada exitosamente');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
