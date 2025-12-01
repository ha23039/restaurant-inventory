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
        $menuItems = MenuItem::query()
            ->when($request->only_available, fn($q) => $q->where('is_available', true))
            ->when($request->only_platillos, fn($q) => $q->where('is_service', false))
            ->orderBy('name')
            ->get();

        // Options from SlideOver
        $options = [
            'include_images' => $request->boolean('include_images', true),
            'include_prices' => $request->boolean('include_prices', true),
            'include_descriptions' => $request->boolean('include_descriptions', true),
        ];

        $pdf = PDF::loadView('exports.menu-pdf', [
            'menuItems' => $menuItems,
            'settings' => $settings,
            'options' => $options,
            'generatedAt' => now(),
        ]);

        return $pdf->download('menu-' . now()->format('Y-m-d') . '.pdf');
    }
}
