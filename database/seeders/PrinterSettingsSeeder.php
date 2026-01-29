<?php

namespace Database\Seeders;

use App\Models\PrinterSettings;
use Illuminate\Database\Seeder;

class PrinterSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $printers = [
            [
                'type' => 'kitchen',
                'name' => 'Impresora Cocina',
                'connection_type' => 'network',
                'ip_address' => env('KITCHEN_PRINTER_IP', '192.168.1.100'),
                'port' => env('KITCHEN_PRINTER_PORT', 9100),
                'printer_name' => null,
                'width_mm' => '58',
                'is_enabled' => env('KITCHEN_PRINTER_ENABLED', false),
                'auto_print' => env('AUTO_PRINT_KITCHEN', true),
                'test_message' => 'Prueba de impresora de cocina',
            ],
            [
                'type' => 'customer',
                'name' => 'Impresora Cliente',
                'connection_type' => 'network',
                'ip_address' => env('CUSTOMER_PRINTER_IP', '192.168.1.101'),
                'port' => env('CUSTOMER_PRINTER_PORT', 9100),
                'printer_name' => env('CUSTOMER_PRINTER_NAME', 'ThermalPrinter'),
                'width_mm' => '80',
                'is_enabled' => env('CUSTOMER_PRINTER_ENABLED', false),
                'auto_print' => env('AUTO_PRINT_CUSTOMER', true),
                'test_message' => 'Prueba de impresora de cliente',
            ],
        ];

        foreach ($printers as $printer) {
            PrinterSettings::updateOrCreate(
                ['type' => $printer['type']],
                $printer
            );
        }
    }
}
