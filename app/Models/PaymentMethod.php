<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'label',
        'icon',
        'is_active',
        'requires_reference',
        'requires_amount_input',
        'commission_percent',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_reference' => 'boolean',
        'requires_amount_input' => 'boolean',
        'commission_percent' => 'decimal:2',
    ];

    /**
     * Scope para obtener solo métodos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Obtener todos los métodos de pago activos y ordenados
     * Con fallback a valores por defecto si la tabla está vacía o no existe
     */
    public static function getActive()
    {
        try {
            $methods = static::active()->ordered()->get();

            if (!$methods->isEmpty()) {
                return $methods;
            }
        } catch (\Exception $e) {
            // Tabla no existe o error de conexión, usar fallback
        }

        // Fallback si no hay métodos configurados o tabla no existe
        return collect([
            (object) ['name' => 'efectivo', 'label' => 'Efectivo', 'icon' => 'cash', 'requires_reference' => false, 'requires_amount_input' => true],
            (object) ['name' => 'tarjeta', 'label' => 'Tarjeta', 'icon' => 'credit-card', 'requires_reference' => false, 'requires_amount_input' => false],
            (object) ['name' => 'transferencia', 'label' => 'Transferencia', 'icon' => 'bank', 'requires_reference' => true, 'requires_amount_input' => false],
            (object) ['name' => 'mixto', 'label' => 'Pago Mixto', 'icon' => 'mix', 'requires_reference' => false, 'requires_amount_input' => true],
        ]);
    }

    /**
     * Obtener el label de un método de pago por su nombre
     */
    public static function getLabelByName(string $name): string
    {
        try {
            $method = static::where('name', $name)->first();

            if ($method) {
                return $method->label;
            }
        } catch (\Exception $e) {
            // Tabla no existe, usar fallback
        }

        // Fallback
        $fallbacks = [
            'efectivo' => 'Efectivo',
            'tarjeta' => 'Tarjeta',
            'transferencia' => 'Transferencia',
            'mixto' => 'Pago Mixto',
        ];

        return $fallbacks[$name] ?? ucfirst($name);
    }
}
