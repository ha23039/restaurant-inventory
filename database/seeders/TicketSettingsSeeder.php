<?php

namespace Database\Seeders;

use App\Models\TicketSettings;
use Illuminate\Database\Seeder;

class TicketSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TicketSettings::updateOrCreate(
            ['id' => 1],
            [
                // Contenido del ticket de cliente
                'show_logo' => true,
                'show_address' => true,
                'show_phone' => true,
                'show_email' => false,
                'show_tax_id' => true,
                'show_website' => false,

                // Código QR
                'show_qr_code' => env('INCLUDE_QR_CODE', true),
                'qr_content' => 'sale_number',
                'qr_custom_url' => null,
                'qr_size' => env('QR_SIZE', 150),

                // Mensajes personalizados
                'header_message' => null,
                'footer_message' => '¡Gracias por su compra!',
                'promo_message' => null,

                // Ticket de cocina
                'kitchen_show_customer_name' => false,
                'kitchen_show_table_number' => true,
                'kitchen_show_notes' => true,
                'kitchen_show_order_number' => true,
                'kitchen_font_size' => 'large',

                // Categorías que NO van a cocina
                'non_kitchen_categories' => [
                    'bebidas',
                    'bebidas_frias',
                    'postres_frios',
                    'extras_frios',
                    'condimentos',
                    'salsas',
                ],

                // Configuración de prioridades
                'priority_high_threshold' => 10,
                'priority_medium_threshold' => 5,
                'rush_hours_start' => '12:00',
                'rush_hours_end' => '14:00',
                'rush_hours_evening_start' => '19:00',
                'rush_hours_evening_end' => '21:00',
            ]
        );
    }
}
