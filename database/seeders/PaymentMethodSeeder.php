<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'name' => 'efectivo',
                'label' => 'Efectivo',
                'icon' => 'cash',
                'is_active' => true,
                'requires_reference' => false,
                'requires_amount_input' => true,
                'commission_percent' => 0,
                'sort_order' => 1,
            ],
            [
                'name' => 'tarjeta',
                'label' => 'Tarjeta',
                'icon' => 'credit-card',
                'is_active' => true,
                'requires_reference' => false,
                'requires_amount_input' => false,
                'commission_percent' => 0,
                'sort_order' => 2,
            ],
            [
                'name' => 'transferencia',
                'label' => 'Transferencia',
                'icon' => 'bank',
                'is_active' => true,
                'requires_reference' => true,
                'requires_amount_input' => false,
                'commission_percent' => 0,
                'sort_order' => 3,
            ],
            [
                'name' => 'mixto',
                'label' => 'Pago Mixto',
                'icon' => 'mix',
                'is_active' => true,
                'requires_reference' => false,
                'requires_amount_input' => true,
                'commission_percent' => 0,
                'sort_order' => 4,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['name' => $method['name']],
                $method
            );
        }
    }
}
