<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrinterSettings extends Model
{
    protected $fillable = [
        'type',
        'name',
        'connection_type',
        'ip_address',
        'port',
        'printer_name',
        'width_mm',
        'is_enabled',
        'auto_print',
        'test_message',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'auto_print' => 'boolean',
        'port' => 'integer',
    ];

    /**
     * Obtener configuración de impresora de cocina
     * Con fallback a .env si no existe en BD
     */
    public static function getKitchenPrinter(): array
    {
        try {
            $printer = static::where('type', 'kitchen')->first();

            if ($printer && $printer->is_enabled) {
                return [
                    'enabled' => true,
                    'ip' => $printer->ip_address,
                    'port' => $printer->port ?? 9100,
                    'width' => $printer->width_mm == '58' ? 32 : 48,
                    'width_mm' => $printer->width_mm,
                    'auto_print' => $printer->auto_print,
                    'connection_type' => $printer->connection_type,
                    'printer_name' => $printer->printer_name,
                    'source' => 'database',
                ];
            }
        } catch (\Exception $e) {
            // Tabla no existe o error de conexión, usar fallback
        }

        // Fallback a .env
        return [
            'enabled' => config('thermal_printer.kitchen_printer.enabled', false),
            'ip' => config('thermal_printer.kitchen_printer.ip'),
            'port' => config('thermal_printer.kitchen_printer.port', 9100),
            'width' => config('thermal_printer.kitchen_printer.width', 32),
            'width_mm' => '58',
            'auto_print' => config('thermal_printer.tickets.auto_print_kitchen', true),
            'connection_type' => 'network',
            'printer_name' => null,
            'source' => 'env',
        ];
    }

    /**
     * Obtener configuración de impresora de cliente
     * Con fallback a .env si no existe en BD
     */
    public static function getCustomerPrinter(): array
    {
        try {
            $printer = static::where('type', 'customer')->first();

            if ($printer && $printer->is_enabled) {
                return [
                    'enabled' => true,
                    'ip' => $printer->ip_address,
                    'port' => $printer->port ?? 9100,
                    'width' => $printer->width_mm == '58' ? 32 : 48,
                    'width_mm' => $printer->width_mm,
                    'auto_print' => $printer->auto_print,
                    'connection_type' => $printer->connection_type,
                    'printer_name' => $printer->printer_name,
                    'source' => 'database',
                ];
            }
        } catch (\Exception $e) {
            // Tabla no existe o error de conexión, usar fallback
        }

        // Fallback a .env
        return [
            'enabled' => config('thermal_printer.customer_printer.enabled', false),
            'ip' => config('thermal_printer.customer_printer.ip'),
            'port' => config('thermal_printer.customer_printer.port', 9100),
            'width' => config('thermal_printer.customer_printer.width', 48),
            'width_mm' => '80',
            'auto_print' => config('thermal_printer.tickets.auto_print_customer', true),
            'connection_type' => 'network',
            'printer_name' => config('thermal_printer.customer_printer.name'),
            'source' => 'env',
        ];
    }

    /**
     * Obtener configuración de impresora por tipo
     */
    public static function getPrinter(string $type): array
    {
        return $type === 'kitchen'
            ? static::getKitchenPrinter()
            : static::getCustomerPrinter();
    }

    /**
     * Verificar si la impresión automática está habilitada
     */
    public static function isAutoPrintEnabled(string $type): bool
    {
        $config = static::getPrinter($type);
        return $config['enabled'] && $config['auto_print'];
    }

    /**
     * Obtener ancho de caracteres por línea
     */
    public function getCharactersPerLine(): int
    {
        return $this->width_mm == '58' ? 32 : 48;
    }
}
