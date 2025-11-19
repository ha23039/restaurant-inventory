<?php

namespace App\Services;

use App\Models\CashFlow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    /**
     * Create a new expense and register it in cash flow
     */
    public function create(array $data): CashFlow
    {
        return DB::transaction(function () use ($data) {
            // Create cash flow entry for the expense
            $cashFlow = CashFlow::create([
                'user_id' => Auth::id(),
                'type' => 'salida',
                'category' => $data['category'],
                'amount' => $data['amount'],
                'description' => $data['description'],
                'notes' => $data['notes'] ?? null,
                'flow_date' => $data['expense_date'],
            ]);

            // If supplier_id is provided, store it in notes or create a separate relation
            if (!empty($data['supplier_id'])) {
                $supplierNote = "Proveedor ID: {$data['supplier_id']}";
                $cashFlow->notes = $cashFlow->notes
                    ? $cashFlow->notes . "\n" . $supplierNote
                    : $supplierNote;
                $cashFlow->save();
            }

            return $cashFlow->fresh();
        });
    }

    /**
     * Update an existing expense and adjust cash flow
     */
    public function update(CashFlow $cashFlow, array $data): CashFlow
    {
        // Verify that this is actually an expense
        if ($cashFlow->type !== 'salida') {
            throw new \Exception('Solo se pueden editar gastos');
        }

        return DB::transaction(function () use ($cashFlow, $data) {
            // Update the cash flow entry
            $cashFlow->update([
                'category' => $data['category'],
                'amount' => $data['amount'],
                'description' => $data['description'],
                'notes' => $data['notes'] ?? null,
                'flow_date' => $data['expense_date'],
            ]);

            // Update supplier info if provided
            if (!empty($data['supplier_id'])) {
                $supplierNote = "Proveedor ID: {$data['supplier_id']}";

                // Remove old supplier note if exists
                $notes = $cashFlow->notes ?? '';
                $notes = preg_replace('/Proveedor ID: \d+\n?/', '', $notes);

                $cashFlow->notes = $notes
                    ? $notes . "\n" . $supplierNote
                    : $supplierNote;
                $cashFlow->save();
            }

            return $cashFlow->fresh();
        });
    }

    /**
     * Delete an expense and remove from cash flow
     */
    public function delete(CashFlow $cashFlow): bool
    {
        // Verify that this is actually an expense
        if ($cashFlow->type !== 'salida') {
            throw new \Exception('Solo se pueden eliminar gastos');
        }

        // Verify that it's not linked to a sale (shouldn't be for expenses, but just in case)
        if ($cashFlow->sale_id) {
            throw new \Exception('No se puede eliminar un gasto vinculado a una venta');
        }

        return DB::transaction(function () use ($cashFlow) {
            return $cashFlow->delete();
        });
    }

    /**
     * Get expense statistics
     */
    public function getStatistics(string $from = null, string $to = null): array
    {
        $query = CashFlow::expense();

        if ($from && $to) {
            $query->byDateRange($from, $to);
        }

        $total = $query->sum('amount');
        $count = $query->count();
        $average = $count > 0 ? $total / $count : 0;

        // Get breakdown by category
        $byCategory = CashFlow::expense()
            ->when($from && $to, fn ($q) => $q->byDateRange($from, $to))
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get()
            ->map(fn ($item) => [
                'category' => $item->category,
                'category_label' => (new CashFlow(['category' => $item->category]))->category_label,
                'total' => (float) $item->total,
                'count' => $item->count,
                'percentage' => $total > 0 ? ($item->total / $total) * 100 : 0,
            ])
            ->toArray();

        return [
            'total' => (float) $total,
            'count' => $count,
            'average' => (float) $average,
            'by_category' => $byCategory,
        ];
    }

    /**
     * Get recent expenses
     */
    public function getRecent(int $limit = 10): array
    {
        return CashFlow::expense()
            ->with('user')
            ->orderBy('flow_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get expense categories with labels
     */
    public function getCategories(): array
    {
        return [
            ['value' => 'compras', 'label' => 'Compras'],
            ['value' => 'gastos_operativos', 'label' => 'Gastos Operativos'],
            ['value' => 'gastos_admin', 'label' => 'Gastos Administrativos'],
            ['value' => 'mantenimiento', 'label' => 'Mantenimiento'],
            ['value' => 'marketing', 'label' => 'Marketing'],
            ['value' => 'otros', 'label' => 'Otros'],
        ];
    }
}
