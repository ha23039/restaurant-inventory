<?php

namespace App\Http\Controllers;

use App\Http\Resources\CashFlowResource;
use App\Models\CashFlow;
use App\Models\User;
use App\Repositories\CashFlowRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashFlowController extends Controller
{
    public function __construct(
        protected CashFlowRepository $repository
    ) {
    }

    /**
     * Display a listing of cash flow transactions
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'type',
            'category',
            'date_from',
            'date_to',
            'user_id',
            'amount_min',
            'amount_max',
        ]);

        $transactions = $this->repository->getPaginated($filters, 20);

        // Get filter options
        $categories = $this->getCategoryOptions();
        $users = User::select('id', 'name', 'role')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get summary statistics for current filters
        $summary = null;
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $summary = $this->repository->getSummaryByDateRange(
                $filters['date_from'],
                $filters['date_to']
            );
        }

        return Inertia::render('CashFlow/Index', [
            'transactions' => $transactions,
            'filters' => $filters,
            'categories' => $categories,
            'users' => $users,
            'summary' => $summary,
        ]);
    }

    /**
     * Search transactions (API endpoint)
     */
    public function search(Request $request)
    {
        $filters = $request->only([
            'search',
            'type',
            'category',
            'date_from',
            'date_to',
            'user_id',
            'amount_min',
            'amount_max',
        ]);

        $transactions = $this->repository->getPaginated($filters, 20);

        return CashFlowResource::collection($transactions);
    }

    /**
     * Get summary statistics (API endpoint)
     */
    public function getSummary(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $summary = $this->repository->getSummaryByDateRange($dateFrom, $dateTo);

        return response()->json($summary);
    }

    /**
     * Export transactions to CSV
     */
    public function exportCsv(Request $request)
    {
        $filters = $request->only([
            'search',
            'type',
            'category',
            'date_from',
            'date_to',
            'user_id',
            'amount_min',
            'amount_max',
        ]);

        $query = CashFlow::with(['user', 'sale']);

        // Apply filters
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->byDateRange($filters['date_from'], $filters['date_to']);
        }

        if (!empty($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (!empty($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }

        if (!empty($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
        }

        $transactions = $query->orderBy('flow_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Generate CSV
        $filename = 'cash_flow_'.now()->format('Y-m-d_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($file, [
                'ID',
                'Fecha',
                'Tipo',
                'Categoría',
                'Descripción',
                'Monto',
                'Usuario',
                'Notas',
            ]);

            // Data rows
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id,
                    $transaction->flow_date->format('Y-m-d'),
                    $transaction->type === 'entrada' ? 'Ingreso' : 'Egreso',
                    $transaction->category_label,
                    $transaction->description,
                    $transaction->amount,
                    $transaction->user->name ?? 'N/A',
                    $transaction->notes ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export transactions to Excel
     */
    public function exportExcel(Request $request)
    {
        $filters = $request->only([
            'search',
            'type',
            'category',
            'date_from',
            'date_to',
            'user_id',
            'amount_min',
            'amount_max',
        ]);

        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $filename = 'flujo_efectivo_'.now()->format('Y-m-d_His').'.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CashFlowExport($filters, $dateFrom, $dateTo),
            $filename
        );
    }

    /**
     * Export transactions to PDF
     */
    public function exportPdf(Request $request)
    {
        $filters = $request->only([
            'search',
            'type',
            'category',
            'date_from',
            'date_to',
            'user_id',
            'amount_min',
            'amount_max',
        ]);

        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $query = CashFlow::with(['user', 'sale']);

        // Apply filters (same logic as exportCsv)
        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            $query->byDateRange($filters['date_from'], $filters['date_to']);
        }

        if (! empty($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (! empty($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }

        if (! empty($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
        }

        $transactions = $query->orderBy('flow_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate summary
        $summary = null;
        if (! empty($dateFrom) && ! empty($dateTo)) {
            $summary = $this->repository->getSummaryByDateRange($dateFrom, $dateTo);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.cash-flow-pdf', [
            'transactions' => $transactions,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'summary' => $summary,
            'restaurantName' => config('app.name', 'Restaurante'),
        ]);

        $filename = 'flujo_efectivo_'.now()->format('Y-m-d_His').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Get available categories
     */
    protected function getCategoryOptions(): array
    {
        return [
            // Income categories
            ['value' => 'ventas', 'label' => 'Ventas', 'type' => 'entrada'],
            ['value' => 'otros_ingresos', 'label' => 'Otros Ingresos', 'type' => 'entrada'],

            // Expense categories
            ['value' => 'compras', 'label' => 'Compras', 'type' => 'salida'],
            ['value' => 'gastos_operativos', 'label' => 'Gastos Operativos', 'type' => 'salida'],
            ['value' => 'gastos_admin', 'label' => 'Gastos Administrativos', 'type' => 'salida'],
            ['value' => 'mantenimiento', 'label' => 'Mantenimiento', 'type' => 'salida'],
            ['value' => 'marketing', 'label' => 'Marketing', 'type' => 'salida'],
            ['value' => 'devoluciones', 'label' => 'Devoluciones', 'type' => 'salida'],
            ['value' => 'otros', 'label' => 'Otros', 'type' => 'salida'],
        ];
    }
}
