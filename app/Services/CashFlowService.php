<?php

namespace App\Services;

use App\Models\Sale;
use App\Repositories\Contracts\CashFlowRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CashFlowService
{
    public function __construct(
        private CashFlowRepositoryInterface $cashFlowRepository
    ) {}

    /**
     * Registrar ingreso por venta
     */
    public function recordSaleIncome(Sale $sale): void
    {
        $this->cashFlowRepository->create([
            'user_id' => $sale->user_id,
            'sale_id' => $sale->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => $sale->total,
            'description' => "Venta #{$sale->sale_number}",
            'notes' => "Pago: {$sale->payment_method}",
            'flow_date' => now()->toDateString(),
        ]);

        Log::info('Flujo de efectivo registrado', [
            'sale_id' => $sale->id,
            'amount' => $sale->total,
            'type' => 'entrada',
        ]);
    }

    /**
     * Registrar devolución (salida de efectivo)
     */
    public function recordReturn(int $userId, string $returnNumber, float $amount): void
    {
        $this->cashFlowRepository->create([
            'user_id' => $userId,
            'sale_id' => null,
            'type' => 'salida',
            'category' => 'devoluciones',
            'amount' => $amount,
            'description' => "Devolución #{$returnNumber}",
            'flow_date' => now()->toDateString(),
        ]);

        Log::info('Devolución registrada en flujo de efectivo', [
            'return_number' => $returnNumber,
            'amount' => $amount,
        ]);
    }

    /**
     * Registrar gasto
     */
    public function recordExpense(int $userId, string $category, float $amount, string $description): void
    {
        $this->cashFlowRepository->create([
            'user_id' => $userId,
            'sale_id' => null,
            'type' => 'salida',
            'category' => $category,
            'amount' => $amount,
            'description' => $description,
            'flow_date' => now()->toDateString(),
        ]);

        Log::info('Gasto registrado', [
            'category' => $category,
            'amount' => $amount,
        ]);
    }

    /**
     * Obtener balance por rango de fechas
     */
    public function getBalance(Carbon $startDate, Carbon $endDate): array
    {
        $income = $this->cashFlowRepository->getTotalIncomeByDateRange($startDate, $endDate);
        $expenses = $this->cashFlowRepository->getTotalExpensesByDateRange($startDate, $endDate);
        $balance = $income - $expenses;

        return [
            'income' => $income,
            'expenses' => $expenses,
            'balance' => $balance,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ];
    }

    /**
     * Obtener flujo de efectivo por categoría
     */
    public function getFlowByCategory(string $category, Carbon $startDate, Carbon $endDate)
    {
        return $this->cashFlowRepository->getByCategoryAndDateRange($category, $startDate, $endDate);
    }

    /**
     * Obtener resumen financiero
     */
    public function getFinancialSummary(Carbon $startDate, Carbon $endDate): array
    {
        $balance = $this->getBalance($startDate, $endDate);

        $byCategory = collect(['ventas', 'compras', 'gastos_operativos', 'gastos_admin', 'devoluciones', 'otros'])
            ->mapWithKeys(function ($category) use ($startDate, $endDate) {
                $entries = $this->cashFlowRepository->getByCategoryAndDateRange($category, $startDate, $endDate);

                return [$category => [
                    'total' => $entries->sum('amount'),
                    'count' => $entries->count(),
                ]];
            });

        return [
            'balance' => $balance,
            'by_category' => $byCategory,
        ];
    }
}
