<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\CashFlow;
use App\Models\Product;
use App\Models\MenuItem;
use App\Models\SimpleProduct;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsolidatedReportController extends Controller
{
    public function exportExecutiveReport(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,excel,pdf',
            'dateFrom' => 'required|date',
            'dateTo' => 'required|date|after_or_equal:dateFrom',
            'options' => 'array',
        ]);

        $format = $request->input('format');
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $options = $request->input('options', []);

        $data = $this->getExecutiveReportData($dateFrom, $dateTo, $options);

        switch ($format) {
            case 'csv':
                return $this->exportExecutiveCsv($data, $dateFrom, $dateTo);
            case 'excel':
                return $this->exportExecutiveExcel($data, $dateFrom, $dateTo);
            case 'pdf':
                return $this->exportExecutivePdf($data, $dateFrom, $dateTo);
        }
    }

    public function exportFinancialReport(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,excel,pdf',
            'dateFrom' => 'required|date',
            'dateTo' => 'required|date|after_or_equal:dateFrom',
            'options' => 'array',
        ]);

        $format = $request->input('format');
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $options = $request->input('options', []);

        $data = $this->getFinancialReportData($dateFrom, $dateTo, $options);

        switch ($format) {
            case 'csv':
                return $this->exportFinancialCsv($data, $dateFrom, $dateTo);
            case 'excel':
                return $this->exportFinancialExcel($data, $dateFrom, $dateTo);
            case 'pdf':
                return $this->exportFinancialPdf($data, $dateFrom, $dateTo);
        }
    }

    public function exportProfitabilityReport(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,excel,pdf',
            'dateFrom' => 'required|date',
            'dateTo' => 'required|date|after_or_equal:dateFrom',
            'options' => 'array',
        ]);

        $format = $request->input('format');
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $options = $request->input('options', []);

        $data = $this->getProfitabilityReportData($dateFrom, $dateTo, $options);

        switch ($format) {
            case 'csv':
                return $this->exportProfitabilityCsv($data, $dateFrom, $dateTo);
            case 'excel':
                return $this->exportProfitabilityExcel($data, $dateFrom, $dateTo);
            case 'pdf':
                return $this->exportProfitabilityPdf($data, $dateFrom, $dateTo);
        }
    }

    public function exportInventoryReport(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,excel,pdf',
            'dateFrom' => 'required|date',
            'dateTo' => 'required|date|after_or_equal:dateFrom',
            'options' => 'array',
        ]);

        $format = $request->input('format');
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $options = $request->input('options', []);

        $data = $this->getInventoryReportData($dateFrom, $dateTo, $options);

        switch ($format) {
            case 'csv':
                return $this->exportInventoryCsv($data, $dateFrom, $dateTo);
            case 'excel':
                return $this->exportInventoryExcel($data, $dateFrom, $dateTo);
            case 'pdf':
                return $this->exportInventoryPdf($data, $dateFrom, $dateTo);
        }
    }

    private function getExecutiveReportData($dateFrom, $dateTo, $options)
    {
        $data = [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
        ];

        if ($options['includeSales'] ?? true) {
            $sales = Sale::whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59'])
                ->where('status', 'completed')
                ->get();

            $data['sales'] = [
                'total_sales' => $sales->count(),
                'total_revenue' => $sales->sum('total_amount'),
                'average_ticket' => $sales->avg('total_amount'),
                'total_discount' => $sales->sum('discount_amount'),
            ];
        }

        if ($options['includeCashflow'] ?? true) {
            $cashflow = CashFlow::whereBetween('flow_date', [$dateFrom, $dateTo])->get();

            $income = $cashflow->where('type', 'entrada');
            $expenses = $cashflow->where('type', 'salida');

            $data['cashflow'] = [
                'total_income' => $income->sum('amount'),
                'total_expenses' => $expenses->sum('amount'),
                'net_cashflow' => $income->sum('amount') - $expenses->sum('amount'),
                'income_by_category' => $income->groupBy('category')->map(fn($items) => $items->sum('amount')),
                'expenses_by_category' => $expenses->groupBy('category')->map(fn($items) => $items->sum('amount')),
            ];
        }

        if ($options['includeInventory'] ?? true) {
            $products = Product::with('category')->get();
            $lowStock = $products->filter(fn($p) => $p->current_stock <= $p->min_stock);

            $data['inventory'] = [
                'total_products' => $products->count(),
                'low_stock_count' => $lowStock->count(),
                'total_inventory_value' => $products->sum(fn($p) => $p->current_stock * $p->unit_cost),
                'out_of_stock' => $products->where('current_stock', 0)->count(),
            ];
        }

        return $data;
    }

    private function getFinancialReportData($dateFrom, $dateTo, $options)
    {
        $data = [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
        ];

        $cashflow = CashFlow::whereBetween('flow_date', [$dateFrom, $dateTo])->get();

        if ($options['includeIncome'] ?? true) {
            $income = $cashflow->where('type', 'entrada');
            $data['income'] = [
                'total' => $income->sum('amount'),
                'count' => $income->count(),
                'by_category' => $income->groupBy('category')->map(function ($items, $category) {
                    return [
                        'category' => $category,
                        'amount' => $items->sum('amount'),
                        'count' => $items->count(),
                    ];
                })->values(),
            ];
        }

        if ($options['includeExpenses'] ?? true) {
            $expenses = $cashflow->where('type', 'salida');
            $data['expenses'] = [
                'total' => $expenses->sum('amount'),
                'count' => $expenses->count(),
                'by_category' => $expenses->groupBy('category')->map(function ($items, $category) {
                    return [
                        'category' => $category,
                        'amount' => $items->sum('amount'),
                        'count' => $items->count(),
                    ];
                })->values(),
            ];
        }

        if ($options['includeBalance'] ?? true) {
            $income = $cashflow->where('type', 'entrada')->sum('amount');
            $expenses = $cashflow->where('type', 'salida')->sum('amount');

            $data['balance'] = [
                'income' => $income,
                'expenses' => $expenses,
                'net' => $income - $expenses,
                'profit_margin' => $income > 0 ? (($income - $expenses) / $income) * 100 : 0,
            ];
        }

        return $data;
    }

    private function getProfitabilityReportData($dateFrom, $dateTo, $options)
    {
        $data = [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
        ];

        $sales = Sale::whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59'])
            ->where('status', 'completed')
            ->with(['saleItems.menuItem.recipes.product', 'saleItems.simpleProduct.product'])
            ->get();

        $totalRevenue = $sales->sum('total_amount');
        $totalCost = 0;

        foreach ($sales as $sale) {
            foreach ($sale->saleItems as $item) {
                if ($item->product_type === 'menu' && $item->menuItem) {
                    foreach ($item->menuItem->recipes as $recipe) {
                        if ($recipe->product) {
                            $totalCost += $recipe->product->unit_cost * $recipe->quantity_needed * $item->quantity;
                        }
                    }
                } elseif ($item->product_type === 'simple' && $item->simpleProduct && $item->simpleProduct->product) {
                    $totalCost += $item->simpleProduct->product->unit_cost * $item->simpleProduct->cost_per_unit * $item->quantity;
                }
            }
        }

        $data['overview'] = [
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'gross_profit' => $totalRevenue - $totalCost,
            'profit_margin' => $totalRevenue > 0 ? (($totalRevenue - $totalCost) / $totalRevenue) * 100 : 0,
        ];

        if ($options['includeProducts'] ?? true) {
            $productSales = [];

            foreach ($sales as $sale) {
                foreach ($sale->saleItems as $item) {
                    $key = $item->product_type === 'menu' ? 'menu_'.$item->menu_item_id : 'simple_'.$item->simple_product_id;

                    if (!isset($productSales[$key])) {
                        $productSales[$key] = [
                            'name' => $item->product_type === 'menu'
                                ? ($item->menuItem->name ?? 'N/A')
                                : ($item->simpleProduct->name ?? 'N/A'),
                            'type' => $item->product_type,
                            'quantity_sold' => 0,
                            'revenue' => 0,
                            'cost' => 0,
                        ];
                    }

                    $productSales[$key]['quantity_sold'] += $item->quantity;
                    $productSales[$key]['revenue'] += $item->subtotal;

                    if ($item->product_type === 'menu' && $item->menuItem) {
                        foreach ($item->menuItem->recipes as $recipe) {
                            if ($recipe->product) {
                                $productSales[$key]['cost'] += $recipe->product->unit_cost * $recipe->quantity_needed * $item->quantity;
                            }
                        }
                    } elseif ($item->product_type === 'simple' && $item->simpleProduct && $item->simpleProduct->product) {
                        $productSales[$key]['cost'] += $item->simpleProduct->product->unit_cost * $item->simpleProduct->cost_per_unit * $item->quantity;
                    }
                }
            }

            $data['products'] = collect($productSales)->map(function ($product) {
                $product['profit'] = $product['revenue'] - $product['cost'];
                $product['margin'] = $product['revenue'] > 0 ? (($product['revenue'] - $product['cost']) / $product['revenue']) * 100 : 0;
                return $product;
            })->sortByDesc('profit')->values()->all();
        }

        return $data;
    }

    private function getInventoryReportData($dateFrom, $dateTo, $options)
    {
        $data = [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
        ];

        $products = Product::with('category')->get();

        if ($options['includeValues'] ?? true) {
            $data['valuation'] = [
                'total_products' => $products->count(),
                'total_value' => $products->sum(fn($p) => $p->current_stock * $p->unit_cost),
                'by_category' => $products->groupBy('category.name')->map(function ($items) {
                    return [
                        'count' => $items->count(),
                        'value' => $items->sum(fn($p) => $p->current_stock * $p->unit_cost),
                    ];
                }),
            ];
        }

        if ($options['includeMovements'] ?? true) {
            $movements = InventoryMovement::whereBetween('movement_date', [$dateFrom, $dateTo])
                ->with('product')
                ->get();

            $data['movements'] = [
                'total_movements' => $movements->count(),
                'by_type' => $movements->groupBy('movement_type')->map(fn($items) => $items->count()),
                'by_reason' => $movements->groupBy('reason')->map(fn($items) => $items->count()),
            ];
        }

        if ($options['includeAlerts'] ?? true) {
            $lowStock = $products->filter(fn($p) => $p->current_stock <= $p->min_stock);
            $outOfStock = $products->where('current_stock', 0);

            $data['alerts'] = [
                'low_stock_count' => $lowStock->count(),
                'out_of_stock_count' => $outOfStock->count(),
                'low_stock_products' => $lowStock->map(fn($p) => [
                    'name' => $p->name,
                    'current_stock' => $p->current_stock,
                    'min_stock' => $p->min_stock,
                    'unit_type' => $p->unit_type,
                ])->values(),
            ];
        }

        return $data;
    }

    private function exportExecutiveCsv($data, $dateFrom, $dateTo)
    {
        $filename = 'reporte_ejecutivo_'.$dateFrom.'_'.$dateTo.'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['REPORTE EJECUTIVO']);
            fputcsv($file, ['Periodo', $data['period']['from'].' - '.$data['period']['to']]);
            fputcsv($file, []);

            if (isset($data['sales'])) {
                fputcsv($file, ['VENTAS']);
                fputcsv($file, ['Total de Ventas', $data['sales']['total_sales']]);
                fputcsv($file, ['Ingresos Totales', '$'.number_format($data['sales']['total_revenue'], 2)]);
                fputcsv($file, ['Ticket Promedio', '$'.number_format($data['sales']['average_ticket'], 2)]);
                fputcsv($file, ['Descuentos Totales', '$'.number_format($data['sales']['total_discount'], 2)]);
                fputcsv($file, []);
            }

            if (isset($data['cashflow'])) {
                fputcsv($file, ['FLUJO DE EFECTIVO']);
                fputcsv($file, ['Ingresos Totales', '$'.number_format($data['cashflow']['total_income'], 2)]);
                fputcsv($file, ['Egresos Totales', '$'.number_format($data['cashflow']['total_expenses'], 2)]);
                fputcsv($file, ['Flujo Neto', '$'.number_format($data['cashflow']['net_cashflow'], 2)]);
                fputcsv($file, []);
            }

            if (isset($data['inventory'])) {
                fputcsv($file, ['INVENTARIO']);
                fputcsv($file, ['Total de Productos', $data['inventory']['total_products']]);
                fputcsv($file, ['Productos con Stock Bajo', $data['inventory']['low_stock_count']]);
                fputcsv($file, ['Valor Total del Inventario', '$'.number_format($data['inventory']['total_inventory_value'], 2)]);
                fputcsv($file, ['Productos Agotados', $data['inventory']['out_of_stock']]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportExecutiveExcel($data, $dateFrom, $dateTo)
    {
        return $this->exportExecutiveCsv($data, $dateFrom, $dateTo);
    }

    private function exportExecutivePdf($data, $dateFrom, $dateTo)
    {
        $pdf = Pdf::loadView('reports.executive', compact('data'));
        return $pdf->download('reporte_ejecutivo_'.$dateFrom.'_'.$dateTo.'.pdf');
    }

    private function exportFinancialCsv($data, $dateFrom, $dateTo)
    {
        $filename = 'estado_financiero_'.$dateFrom.'_'.$dateTo.'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['ESTADO FINANCIERO']);
            fputcsv($file, ['Periodo', $data['period']['from'].' - '.$data['period']['to']]);
            fputcsv($file, []);

            if (isset($data['income'])) {
                fputcsv($file, ['INGRESOS']);
                fputcsv($file, ['Total', '$'.number_format($data['income']['total'], 2)]);
                fputcsv($file, ['Número de Transacciones', $data['income']['count']]);
                fputcsv($file, []);
                fputcsv($file, ['Categoría', 'Monto', 'Transacciones']);
                foreach ($data['income']['by_category'] as $category) {
                    fputcsv($file, [
                        $category['category'],
                        '$'.number_format($category['amount'], 2),
                        $category['count'],
                    ]);
                }
                fputcsv($file, []);
            }

            if (isset($data['expenses'])) {
                fputcsv($file, ['EGRESOS']);
                fputcsv($file, ['Total', '$'.number_format($data['expenses']['total'], 2)]);
                fputcsv($file, ['Número de Transacciones', $data['expenses']['count']]);
                fputcsv($file, []);
                fputcsv($file, ['Categoría', 'Monto', 'Transacciones']);
                foreach ($data['expenses']['by_category'] as $category) {
                    fputcsv($file, [
                        $category['category'],
                        '$'.number_format($category['amount'], 2),
                        $category['count'],
                    ]);
                }
                fputcsv($file, []);
            }

            if (isset($data['balance'])) {
                fputcsv($file, ['BALANCE GENERAL']);
                fputcsv($file, ['Ingresos', '$'.number_format($data['balance']['income'], 2)]);
                fputcsv($file, ['Egresos', '$'.number_format($data['balance']['expenses'], 2)]);
                fputcsv($file, ['Balance Neto', '$'.number_format($data['balance']['net'], 2)]);
                fputcsv($file, ['Margen de Utilidad', number_format($data['balance']['profit_margin'], 2).'%']);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportFinancialExcel($data, $dateFrom, $dateTo)
    {
        return $this->exportFinancialCsv($data, $dateFrom, $dateTo);
    }

    private function exportFinancialPdf($data, $dateFrom, $dateTo)
    {
        $pdf = Pdf::loadView('reports.financial', compact('data'));
        return $pdf->download('estado_financiero_'.$dateFrom.'_'.$dateTo.'.pdf');
    }

    private function exportProfitabilityCsv($data, $dateFrom, $dateTo)
    {
        $filename = 'analisis_rentabilidad_'.$dateFrom.'_'.$dateTo.'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['ANÁLISIS DE RENTABILIDAD']);
            fputcsv($file, ['Periodo', $data['period']['from'].' - '.$data['period']['to']]);
            fputcsv($file, []);

            fputcsv($file, ['RESUMEN GENERAL']);
            fputcsv($file, ['Ingresos Totales', '$'.number_format($data['overview']['total_revenue'], 2)]);
            fputcsv($file, ['Costo Total', '$'.number_format($data['overview']['total_cost'], 2)]);
            fputcsv($file, ['Utilidad Bruta', '$'.number_format($data['overview']['gross_profit'], 2)]);
            fputcsv($file, ['Margen de Utilidad', number_format($data['overview']['profit_margin'], 2).'%']);
            fputcsv($file, []);

            if (isset($data['products'])) {
                fputcsv($file, ['RENTABILIDAD POR PRODUCTO']);
                fputcsv($file, ['Producto', 'Tipo', 'Cantidad', 'Ingresos', 'Costo', 'Utilidad', 'Margen %']);
                foreach ($data['products'] as $product) {
                    fputcsv($file, [
                        $product['name'],
                        $product['type'] === 'menu' ? 'Platillo' : 'Producto Simple',
                        $product['quantity_sold'],
                        '$'.number_format($product['revenue'], 2),
                        '$'.number_format($product['cost'], 2),
                        '$'.number_format($product['profit'], 2),
                        number_format($product['margin'], 2).'%',
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportProfitabilityExcel($data, $dateFrom, $dateTo)
    {
        return $this->exportProfitabilityCsv($data, $dateFrom, $dateTo);
    }

    private function exportProfitabilityPdf($data, $dateFrom, $dateTo)
    {
        $pdf = Pdf::loadView('reports.profitability', compact('data'));
        return $pdf->download('analisis_rentabilidad_'.$dateFrom.'_'.$dateTo.'.pdf');
    }

    private function exportInventoryCsv($data, $dateFrom, $dateTo)
    {
        $filename = 'inventario_valorizado_'.$dateFrom.'_'.$dateTo.'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['INVENTARIO VALORIZADO']);
            fputcsv($file, ['Periodo', $data['period']['from'].' - '.$data['period']['to']]);
            fputcsv($file, []);

            if (isset($data['valuation'])) {
                fputcsv($file, ['VALORIZACIÓN']);
                fputcsv($file, ['Total de Productos', $data['valuation']['total_products']]);
                fputcsv($file, ['Valor Total', '$'.number_format($data['valuation']['total_value'], 2)]);
                fputcsv($file, []);
            }

            if (isset($data['movements'])) {
                fputcsv($file, ['MOVIMIENTOS']);
                fputcsv($file, ['Total de Movimientos', $data['movements']['total_movements']]);
                fputcsv($file, []);
            }

            if (isset($data['alerts'])) {
                fputcsv($file, ['ALERTAS']);
                fputcsv($file, ['Productos con Stock Bajo', $data['alerts']['low_stock_count']]);
                fputcsv($file, ['Productos Agotados', $data['alerts']['out_of_stock_count']]);
                fputcsv($file, []);
                if (!empty($data['alerts']['low_stock_products'])) {
                    fputcsv($file, ['Producto', 'Stock Actual', 'Stock Mínimo', 'Unidad']);
                    foreach ($data['alerts']['low_stock_products'] as $product) {
                        fputcsv($file, [
                            $product['name'],
                            $product['current_stock'],
                            $product['min_stock'],
                            $product['unit_type'],
                        ]);
                    }
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportInventoryExcel($data, $dateFrom, $dateTo)
    {
        return $this->exportInventoryCsv($data, $dateFrom, $dateTo);
    }

    private function exportInventoryPdf($data, $dateFrom, $dateTo)
    {
        $pdf = Pdf::loadView('reports.inventory', compact('data'));
        return $pdf->download('inventario_valorizado_'.$dateFrom.'_'.$dateTo.'.pdf');
    }
}
