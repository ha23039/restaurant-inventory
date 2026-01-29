<?php

namespace Database\Seeders;

use App\Models\OrderSettings;
use Illuminate\Database\Seeder;

class OrderSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OrderSettings::create([
            // Numeración de pedidos
            'order_number_prefix' => '',
            'order_number_padding' => 4,
            'order_number_reset' => 'daily',

            // Tipos de servicio habilitados
            'allow_dine_in' => true,
            'allow_takeout' => true,
            'allow_delivery' => false,
            'allow_scheduled' => false,

            // Impuestos
            'tax_included' => true,
            'tax_percentage' => 13.00,
            'show_tax_breakdown' => false,

            // Propinas
            'tip_enabled' => false,
            'tip_suggestions' => [10, 15, 20],
            'tip_mandatory_above' => null,

            // Requisitos de cliente
            'require_customer_name' => false,
            'require_customer_phone' => false,

            // Notas y tiempo
            'allow_notes_per_item' => true,
            'default_prep_time_minutes' => 20,

            // Montos mínimos
            'min_order_amount' => 0,
            'delivery_min_amount' => 0,
            'delivery_fee' => 0,
        ]);
    }
}
