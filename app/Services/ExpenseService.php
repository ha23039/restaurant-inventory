<?php

namespace App\Services;

use App\Models\CashFlow;
use App\Models\Product;
use App\Models\InventoryMovement;
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
            // If it's a product purchase, handle inventory update
            if ($data['category'] === 'compra_productos_insumos' && !empty($data['products'])) {
                $this->updateInventoryFromProducts($data['products'], $data['expense_date'], $data['payment_method'] ?? null);
            }

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

            // If supplier_id is provided, store it in notes
            if (!empty($data['supplier_id'])) {
                $supplierNote = "Proveedor ID: {$data['supplier_id']}";
                $cashFlow->notes = $cashFlow->notes
                    ? $cashFlow->notes . "\n" . $supplierNote
                    : $supplierNote;
                $cashFlow->save();
            }

            // Add payment method to notes
            if (!empty($data['payment_method'])) {
                $paymentNote = "Método de pago: " . ucfirst($data['payment_method']);
                $cashFlow->notes = $cashFlow->notes
                    ? $cashFlow->notes . "\n" . $paymentNote
                    : $paymentNote;
                $cashFlow->save();
            }

            // Add products summary to notes if applicable
            if ($data['category'] === 'compra_productos_insumos' && !empty($data['products'])) {
                $productsSummary = "Productos comprados: " . count($data['products']) . " items";
                $cashFlow->notes = $cashFlow->notes
                    ? $cashFlow->notes . "\n" . $productsSummary
                    : $productsSummary;
                $cashFlow->save();
            }

            return $cashFlow->fresh();
        });
    }

    /**
     * Update inventory from purchased products
     */
    protected function updateInventoryFromProducts(array $products, string $purchaseDate, ?string $paymentMethod = null): void
    {
        foreach ($products as $productData) {
            $product = Product::findOrFail($productData['product_id']);

            // Update stock
            $product->increment('current_stock', $productData['quantity']);

            // Update unit cost (weighted average or just update)
            $product->unit_cost = $productData['cost_price'];
            $product->save();

            // Create inventory movement record
            InventoryMovement::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'movement_type' => 'entrada',
                'quantity' => $productData['quantity'],
                'reason' => 'compra',
                'notes' => "Compra de producto. Precio: $" . number_format($productData['cost_price'], 2) .
                          ($paymentMethod ? " | Método: " . ucfirst($paymentMethod) : ''),
                'movement_date' => $purchaseDate,
            ]);
        }
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
            ['value' => 'servicios_publicos', 'label' => 'Servicios Públicos'],
            ['value' => 'compra_productos_insumos', 'label' => 'Compra de Productos e Insumos'],
            ['value' => 'arriendo', 'label' => 'Arriendo'],
            ['value' => 'nomina', 'label' => 'Nómina'],
            ['value' => 'gastos_admin', 'label' => 'Gastos Administrativos'],
            ['value' => 'marketing', 'label' => 'Marketing'],
            ['value' => 'transporte_domicilios', 'label' => 'Transporte, Domicilios y Logística'],
            ['value' => 'mantenimiento_reparaciones', 'label' => 'Mantenimiento y Reparaciones'],
            ['value' => 'muebles_equipos', 'label' => 'Muebles, Equipos y Maquinaria'],
            ['value' => 'otros', 'label' => 'Otros'],
        ];
    }
}
