<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketSettings extends Model
{
    protected $fillable = [
        // Contenido del ticket de cliente
        'show_logo',
        'show_address',
        'show_phone',
        'show_email',
        'show_tax_id',
        'show_website',

        // Código QR
        'show_qr_code',
        'qr_content',
        'qr_custom_url',
        'qr_size',

        // Mensajes personalizados
        'header_message',
        'footer_message',
        'promo_message',

        // Ticket de cocina
        'kitchen_show_customer_name',
        'kitchen_show_table_number',
        'kitchen_show_notes',
        'kitchen_show_order_number',
        'kitchen_font_size',

        // Categorías que NO van a cocina
        'non_kitchen_categories',

        // Configuración de prioridades
        'priority_high_threshold',
        'priority_medium_threshold',
        'rush_hours_start',
        'rush_hours_end',
        'rush_hours_evening_start',
        'rush_hours_evening_end',
    ];

    protected $casts = [
        'show_logo' => 'boolean',
        'show_address' => 'boolean',
        'show_phone' => 'boolean',
        'show_email' => 'boolean',
        'show_tax_id' => 'boolean',
        'show_website' => 'boolean',
        'show_qr_code' => 'boolean',
        'qr_size' => 'integer',
        'kitchen_show_customer_name' => 'boolean',
        'kitchen_show_table_number' => 'boolean',
        'kitchen_show_notes' => 'boolean',
        'kitchen_show_order_number' => 'boolean',
        'non_kitchen_categories' => 'array',
        'priority_high_threshold' => 'integer',
        'priority_medium_threshold' => 'integer',
    ];

    /**
     * Singleton - obtener configuración (con fallback a config/thermal_printer.php)
     */
    public static function get(): array
    {
        try {
            $settings = static::first();

            if ($settings) {
                return [
                    // Ticket de cliente
                    'show_logo' => $settings->show_logo,
                    'show_address' => $settings->show_address,
                    'show_phone' => $settings->show_phone,
                    'show_email' => $settings->show_email,
                    'show_tax_id' => $settings->show_tax_id,
                    'show_website' => $settings->show_website,

                    // QR
                    'show_qr_code' => $settings->show_qr_code,
                    'qr_content' => $settings->qr_content,
                    'qr_custom_url' => $settings->qr_custom_url,
                    'qr_size' => $settings->qr_size,

                    // Mensajes
                    'header_message' => $settings->header_message,
                    'footer_message' => $settings->footer_message,
                    'promo_message' => $settings->promo_message,

                    // Cocina
                    'kitchen_show_customer_name' => $settings->kitchen_show_customer_name,
                    'kitchen_show_table_number' => $settings->kitchen_show_table_number,
                    'kitchen_show_notes' => $settings->kitchen_show_notes,
                    'kitchen_show_order_number' => $settings->kitchen_show_order_number,
                    'kitchen_font_size' => $settings->kitchen_font_size,

                    // Categorías y prioridades
                    'non_kitchen_categories' => $settings->non_kitchen_categories ?? [],
                    'priority_high_threshold' => $settings->priority_high_threshold,
                    'priority_medium_threshold' => $settings->priority_medium_threshold,
                    'rush_hours' => [
                        'start' => $settings->rush_hours_start,
                        'end' => $settings->rush_hours_end,
                    ],
                    'rush_hours_evening' => [
                        'start' => $settings->rush_hours_evening_start,
                        'end' => $settings->rush_hours_evening_end,
                    ],

                    'source' => 'database',
                ];
            }
        } catch (\Exception $e) {
            // Tabla no existe, usar fallback
        }

        // Fallback a config/thermal_printer.php
        return static::getDefaults();
    }

    /**
     * Valores por defecto (desde config o hardcoded)
     */
    public static function getDefaults(): array
    {
        return [
            // Ticket de cliente
            'show_logo' => true,
            'show_address' => true,
            'show_phone' => true,
            'show_email' => false,
            'show_tax_id' => true,
            'show_website' => false,

            // QR
            'show_qr_code' => config('thermal_printer.tickets.include_qr_code', true),
            'qr_content' => 'sale_number',
            'qr_custom_url' => null,
            'qr_size' => config('thermal_printer.tickets.qr_size', 150),

            // Mensajes
            'header_message' => null,
            'footer_message' => '¡Gracias por su compra!',
            'promo_message' => null,

            // Cocina
            'kitchen_show_customer_name' => false,
            'kitchen_show_table_number' => true,
            'kitchen_show_notes' => true,
            'kitchen_show_order_number' => true,
            'kitchen_font_size' => 'large',

            // Categorías que no van a cocina
            'non_kitchen_categories' => config('thermal_printer.non_kitchen_categories', [
                'bebidas',
                'bebidas_frias',
                'postres_frios',
                'extras_frios',
                'condimentos',
                'salsas',
            ]),

            // Prioridades
            'priority_high_threshold' => config('thermal_printer.priorities.high_threshold', 10),
            'priority_medium_threshold' => config('thermal_printer.priorities.medium_threshold', 5),
            'rush_hours' => config('thermal_printer.priorities.rush_hours', [
                'start' => '12:00',
                'end' => '14:00',
            ]),
            'rush_hours_evening' => config('thermal_printer.priorities.rush_hours_evening', [
                'start' => '19:00',
                'end' => '21:00',
            ]),

            'source' => 'config',
        ];
    }

    /**
     * Verificar si una categoría NO va a cocina
     */
    public static function isNonKitchenCategory(string $categorySlug): bool
    {
        $settings = static::get();
        $nonKitchen = $settings['non_kitchen_categories'] ?? [];

        return in_array(strtolower($categorySlug), array_map('strtolower', $nonKitchen));
    }
}
