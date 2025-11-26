<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Http\Resources\CashFlowResource;
use App\Models\CashFlow;
use App\Models\Supplier;
use App\Services\ExpenseService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseService $expenseService
    ) {
    }

    /**
     * Display a listing of expenses
     */
    public function index(Request $request)
    {
        $query = CashFlow::expense()->with(['user']);

        // Apply filters
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Order by date desc
        $query->orderBy('flow_date', 'desc')
            ->orderBy('created_at', 'desc');

        $expenses = $query->paginate(20)->withQueryString();

        return Inertia::render('Expenses/Index', [
            'expenses' => CashFlowResource::collection($expenses),
            'filters' => $request->only(['date_from', 'date_to', 'category', 'search']),
            'categories' => $this->expenseService->getCategories(),
            'statistics' => $this->expenseService->getStatistics(
                $request->date_from,
                $request->date_to
            ),
        ]);
    }

    /**
     * Show the form for creating a new expense
     */
    public function create()
    {
        // Redirect to index where the SlideOver will handle creation
        return redirect()->route('expenses.index');
    }

    /**
     * Store a newly created expense
     */
    public function store(ExpenseRequest $request)
    {
        try {
            $expense = $this->expenseService->create($request->validated());

            return redirect()
                ->route('expenses.index')
                ->with('success', 'Gasto registrado exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al registrar el gasto: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified expense
     */
    public function show(CashFlow $expense)
    {
        // Verify it's an expense
        if ($expense->type !== 'salida') {
            abort(404);
        }

        $expense->load(['user']);

        return Inertia::render('Expenses/Show', [
            'expense' => new CashFlowResource($expense),
        ]);
    }

    /**
     * Show the form for editing the specified expense
     */
    public function edit(CashFlow $expense)
    {
        // Verify it's an expense
        if ($expense->type !== 'salida') {
            abort(404);
        }

        return Inertia::render('Expenses/Edit', [
            'expense' => new CashFlowResource($expense),
            'categories' => $this->expenseService->getCategories(),
            'suppliers' => Supplier::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified expense
     */
    public function update(ExpenseRequest $request, CashFlow $expense)
    {
        // Verify it's an expense
        if ($expense->type !== 'salida') {
            abort(404);
        }

        try {
            $this->expenseService->update($expense, $request->validated());

            return redirect()
                ->route('expenses.index')
                ->with('success', 'Gasto actualizado exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al actualizar el gasto: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified expense
     */
    public function destroy(CashFlow $expense)
    {
        // Verify it's an expense
        if ($expense->type !== 'salida') {
            abort(404);
        }

        try {
            $this->expenseService->delete($expense);

            return redirect()
                ->route('expenses.index')
                ->with('success', 'Gasto eliminado exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Error al eliminar el gasto: ' . $e->getMessage()]);
        }
    }
}
