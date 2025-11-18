<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 🖨️ CONFIGURACIÓN DE IMPRESORAS TÉRMICAS
    |--------------------------------------------------------------------------
    | Configuración para impresoras térmicas del restaurante
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Impresora de Cocina (58mm)
    |--------------------------------------------------------------------------
    | Para comandas y órdenes de cocina
    */
    'kitchen_printer' => [
        'ip' => env('KITCHEN_PRINTER_IP', '192.168.1.100'),
        'port' => env('KITCHEN_PRINTER_PORT', 9100),
        'width' => 32, // Caracteres por línea en 58mm
        'enabled' => env('KITCHEN_PRINTER_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Impresora de Cliente (80mm)
    |--------------------------------------------------------------------------
    | Para tickets de venta y recibos
    */
    'customer_printer' => [
        'name' => env('CUSTOMER_PRINTER_NAME', 'ThermalPrinter'),
        'ip' => env('CUSTOMER_PRINTER_IP', '192.168.1.101'),
        'port' => env('CUSTOMER_PRINTER_PORT', 9100),
        'width' => 48, // Caracteres por línea en 80mm
        'enabled' => env('CUSTOMER_PRINTER_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Información del Restaurante
    |--------------------------------------------------------------------------
    | Datos que aparecen en los tickets
    */
    'restaurant' => [
        'name' => env('RESTAURANT_NAME', 'Restaurante Demo'),
        'address' => env('RESTAURANT_ADDRESS', 'Calle Principal #123'),
        'phone' => env('RESTAURANT_PHONE', '(555) 123-4567'),
        'email' => env('RESTAURANT_EMAIL', 'info@restaurante.com'),
        'website' => env('RESTAURANT_WEBSITE', 'www.restaurante.com'),
        'tax_id' => env('RESTAURANT_TAX_ID', 'RFC: XAXX010101000'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Tickets
    |--------------------------------------------------------------------------
    */
    'tickets' => [
        'auto_print_kitchen' => env('AUTO_PRINT_KITCHEN', true),
        'auto_print_customer' => env('AUTO_PRINT_CUSTOMER', true),
        'include_qr_code' => env('INCLUDE_QR_CODE', true),
        'qr_size' => env('QR_SIZE', 150),
        'logo_path' => env('RESTAURANT_LOGO_PATH', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Categorías que NO van a Cocina
    |--------------------------------------------------------------------------
    | Items que no necesitan preparación en cocina
    */
    'non_kitchen_categories' => [
        'bebidas',
        'bebidas_frias',
        'postres_frios',
        'extras_frios',
        'condimentos',
        'salsas',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Desarrollo
    |--------------------------------------------------------------------------
    | Para testing sin impresoras físicas
    */
    'development' => [
        'save_to_file' => env('SAVE_TICKETS_TO_FILE', true),
        'file_path' => storage_path('app/tickets/'),
        'simulate_print_delay' => env('SIMULATE_PRINT_DELAY', 2), // segundos
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Prioridades
    |--------------------------------------------------------------------------
    */
    'priorities' => [
        'high_threshold' => 10, // items o más = ALTA
        'medium_threshold' => 5, // items o más = MEDIA
        'rush_hours' => [ // Horas pico (formato 24h)
            'start' => '12:00',
            'end' => '14:00',
        ],
        'rush_hours_evening' => [
            'start' => '19:00',
            'end' => '21:00',
        ],
    ],
];
