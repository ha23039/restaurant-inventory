<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReturnSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener ventas completadas para crear devoluciones de prueba
        $sales = Sale::where('status', 'completada')
                    ->with('saleItems')
                    ->take(3) // Solo 3 ventas para no saturar
                    ->get();

        if ($sales->isEmpty()) {
            $this->command->info("⚠️  No hay ventas completadas para crear devoluciones de prueba");
            return;
        }

        $user = User::first();
        $returnNumber = 1;

        foreach ($sales as $sale) {
            // Crear diferentes tipos de devoluciones
            $isPartialReturn = $returnNumber % 2 === 1; // Alternar entre parcial y total
            
            if ($isPartialReturn) {
                // Devolución parcial: solo algunos items
                $itemsToReturn = $sale->saleItems->take(1); // Solo el primer item
                $quantityPercent = 0.5; // 50% de la cantidad
            } else {
                // Devolución total: todos los items
                $itemsToReturn = $sale->saleItems;
                $quantityPercent = 1.0; // 100% de la cantidad
            }

            // Calcular total de la devolución
            $totalReturned = 0;
            $returnItems = [];

            foreach ($itemsToReturn as $saleItem) {
                $quantityToReturn = max(1, floor($saleItem->quantity * $quantityPercent));
                $itemTotal = $saleItem->unit_price * $quantityToReturn;
                $totalReturned += $itemTotal;

                $returnItems[] = [
                    'sale_item_id' => $saleItem->id,
                    'quantity_returned' => $quantityToReturn,
                    'original_quantity' => $saleItem->quantity,
                    'unit_price' => $saleItem->unit_price,
                    'total_price' => $itemTotal,
                    'inventory_restored' => true
                ];
            }

            // Crear la devolución
            $return = SaleReturn::create([
                'sale_id' => $sale->id,
                'processed_by_user_id' => $user->id,
                'return_number' => 'RET' . now()->format('Ymd') . sprintf('%04d', $returnNumber++),
                'return_type' => $isPartialReturn ? 'partial' : 'total',
                'reason' => $this->getRandomReason(),
                'notes' => $this->getRandomNotes(),
                'subtotal_returned' => $totalReturned,
                'tax_returned' => 0,
                'total_returned' => $totalReturned,
                'status' => 'completed',
                'refund_method' => $this->getRandomRefundMethod(),
                'inventory_restored' => true,
                'cash_flow_adjusted' => true,
                'return_date' => now()->subDays(rand(0, 7))->toDateString(),
                'processed_at' => now()->subDays(rand(0, 7))
            ]);

            // Crear los items de devolución
            foreach ($returnItems as $itemData) {
                $itemData['sale_return_id'] = $return->id;
                SaleReturnItem::create($itemData);
            }

            $this->command->info("✅ Devolución creada: {$return->return_number} - \${$totalReturned}");
        }

        $totalReturns = SaleReturn::count();
        $this->command->info("✅ Se crearon {$totalReturns} devoluciones de prueba");
    }

    private function getRandomReason(): string
    {
        $reasons = ['defective', 'wrong_order', 'customer_request', 'error', 'other'];
        return $reasons[array_rand($reasons)];
    }

    private function getRandomRefundMethod(): string
    {
        $methods = ['efectivo', 'tarjeta', 'transferencia', 'credito'];
        return $methods[array_rand($methods)];
    }

    private function getRandomNotes(): string
    {
        $notes = [
            'Cliente insatisfecho con la calidad del producto',
            'Error en la preparación del pedido',
            'Producto no correspondía a lo solicitado',
            'Demora excesiva en la entrega',
            'Solicitud del cliente por cambio de opinión',
            'Producto defectuoso encontrado después de la venta',
            'Error del sistema al procesar la orden',
            'Cliente alérgico a uno de los ingredientes',
            'Temperatura del producto no era la adecuada',
            'Devolución autorizada por el gerente'
        ];
        
        return $notes[array_rand($notes)];
    }
}