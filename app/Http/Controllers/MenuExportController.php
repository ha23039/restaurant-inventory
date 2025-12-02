<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\BusinessSettings;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class MenuExportController extends Controller
{
    public function exportPdf(Request $request)
    {
        $settings = BusinessSettings::get();

        // Apply filters
        $query = MenuItem::query();

        // Si se especificaron IDs de platillos, solo usar esos
        if ($request->has('items') && !empty($request->items)) {
            $itemIds = explode(',', $request->items);
            $query->whereIn('id', $itemIds);
        } else {
            // Aplicar filtros generales
            $query->when($request->only_available, fn($q) => $q->where('is_available', true))
                  ->when($request->only_platillos, fn($q) => $q->where('is_service', false));
        }

        $menuItems = $query->orderBy('name')->get();

        // Options from SlideOver - asegurar que se lean correctamente
        $options = [
            'include_images' => $request->input('include_images') === '1',
            'include_prices' => $request->input('include_prices') === '1',
            'include_descriptions' => $request->input('include_descriptions') === '1',
        ];

        $pdf = PDF::loadView('exports.menu-pdf', [
            'menuItems' => $menuItems,
            'settings' => $settings,
            'options' => $options,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('menu-' . now()->format('Y-m-d') . '.pdf');
    }
}
