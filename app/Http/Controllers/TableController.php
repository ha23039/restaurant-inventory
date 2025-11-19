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
    public function updateStatus(Request $request, Table $table): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:disponible,ocupada,reservada,en_limpieza',
        ]);

        try {
            $updatedTable = $this->tableService->updateStatus($table, $validated['status']);

            return response()->json([
                'message' => 'Estado actualizado exitosamente',
                'table' => $updatedTable,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el estado: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Occupy table with a sale
     */
    public function occupyTable(Request $request, Table $table): JsonResponse
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
        ]);

        try {
            $sale = Sale::findOrFail($validated['sale_id']);
            $updatedTable = $this->tableService->occupyTable($table, $sale);

            return response()->json([
                'message' => 'Mesa ocupada exitosamente',
                'table' => $updatedTable,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Release table (mark as available)
     */
    public function releaseTable(Table $table): JsonResponse
    {
        try {
            $updatedTable = $this->tableService->releaseTable($table);

            return response()->json([
                'message' => 'Mesa liberada exitosamente',
                'table' => $updatedTable,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al liberar la mesa: ' . $e->getMessage(),
            ], 400);
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
    public function toggleActive(Table $table): JsonResponse
    {
        try {
            $updatedTable = $this->tableService->toggleActive($table);

            return response()->json([
                'message' => $updatedTable->is_active ? 'Mesa activada' : 'Mesa desactivada',
                'table' => $updatedTable,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
