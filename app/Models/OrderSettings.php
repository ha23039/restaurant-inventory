<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderSettings extends Model
{
    protected $fillable = [
        // Numeración
        'order_number_prefix',
        'order_number_padding',
        'order_number_reset',

        // Tipos de servicio
        'allow_dine_in',
        'allow_takeout',
        'allow_delivery',
        'allow_scheduled',

        // Impuestos
        'tax_included',
        'tax_percentage',
        'show_tax_breakdown',

        // Propinas
        'tip_enabled',
        'tip_suggestions',
        'tip_mandatory_above',

        // Requisitos de cliente
        'require_customer_name',
        'require_customer_phone',

        // Notas y tiempo
        'allow_notes_per_item',
        'default_prep_time_minutes',

        // Montos
        'min_order_amount',
        'delivery_min_amount',
        'delivery_fee',
    ];

    protected $casts = [
        'allow_dine_in' => 'boolean',
        'allow_takeout' => 'boolean',
        'allow_delivery' => 'boolean',
        'allow_scheduled' => 'boolean',
        'tax_included' => 'boolean',
        'tax_percentage' => 'decimal:2',
        'show_tax_breakdown' => 'boolean',
        'tip_enabled' => 'boolean',
        'tip_suggestions' => 'array',
        'require_customer_name' => 'boolean',
        'require_customer_phone' => 'boolean',
        'allow_notes_per_item' => 'boolean',
        'min_order_amount' => 'decimal:2',
        'delivery_min_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
    ];

    /**
     * Singleton - obtener configuración (con fallback a valores por defecto)
     */
    public static function get(): array
    {
        try {
            $settings = static::first();

            if ($settings) {
                return [
                    // Numeración
                    'order_number_prefix' => $settings->order_number_prefix,
                    'order_number_padding' => $settings->order_number_padding,
                    'order_number_reset' => $settings->order_number_reset,

                    // Tipos de servicio
                    'allow_dine_in' => $settings->allow_dine_in,
                    'allow_takeout' => $settings->allow_takeout,
                    'allow_delivery' => $settings->allow_delivery,
                    'allow_scheduled' => $settings->allow_scheduled,

                    // Impuestos
                    'tax_included' => $settings->tax_included,
                    'tax_percentage' => $settings->tax_percentage,
                    'show_tax_breakdown' => $settings->show_tax_breakdown,

                    // Propinas
                    'tip_enabled' => $settings->tip_enabled,
                    'tip_suggestions' => $settings->tip_suggestions ?? [10, 15, 20],
                    'tip_mandatory_above' => $settings->tip_mandatory_above,

                    // Requisitos
                    'require_customer_name' => $settings->require_customer_name,
                    'require_customer_phone' => $settings->require_customer_phone,

                    // Notas y tiempo
                    'allow_notes_per_item' => $settings->allow_notes_per_item,
                    'default_prep_time_minutes' => $settings->default_prep_time_minutes,

                    // Montos
                    'min_order_amount' => $settings->min_order_amount,
                    'delivery_min_amount' => $settings->delivery_min_amount,
                    'delivery_fee' => $settings->delivery_fee,

                    'source' => 'database',
                ];
            }
        } catch (\Exception $e) {
            // Tabla no existe, usar fallback
        }

        return static::getDefaults();
    }

    /**
     * Valores por defecto
     */
    public static function getDefaults(): array
    {
        return [
            // Numeración
            'order_number_prefix' => '',
            'order_number_padding' => 4,
            'order_number_reset' => 'daily',

            // Tipos de servicio
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

            // Requisitos
            'require_customer_name' => false,
            'require_customer_phone' => false,

            // Notas y tiempo
            'allow_notes_per_item' => true,
            'default_prep_time_minutes' => 20,

            // Montos
            'min_order_amount' => 0,
            'delivery_min_amount' => 0,
            'delivery_fee' => 0,

            'source' => 'defaults',
        ];
    }

    /**
     * Obtener tipos de servicio habilitados
     */
    public static function getEnabledServiceTypes(): array
    {
        $settings = static::get();
        $types = [];

        if ($settings['allow_dine_in']) {
            $types[] = ['value' => 'dine_in', 'label' => 'Comer aquí'];
        }
        if ($settings['allow_takeout']) {
            $types[] = ['value' => 'takeout', 'label' => 'Para llevar'];
        }
        if ($settings['allow_delivery']) {
            $types[] = ['value' => 'delivery', 'label' => 'Delivery', 'fee' => $settings['delivery_fee']];
        }

        return $types;
    }

    /**
     * Calcular impuesto si aplica
     */
    public static function calculateTax(float $subtotal): float
    {
        $settings = static::get();

        if ($settings['tax_included']) {
            return 0; // Impuesto ya incluido en precios
        }

        return $subtotal * ($settings['tax_percentage'] / 100);
    }
}
