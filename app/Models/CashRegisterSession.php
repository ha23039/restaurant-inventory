<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegisterSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'opening_amount',
        'closing_amount',
        'expected_amount',
        'difference',
        'status',
        'opened_at',
        'closed_at',
        'opening_notes',
        'closing_notes',
    ];

    protected $casts = [
        'opening_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
        'difference' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected $appends = [
        'total_cash_sales',
        'total_card_sales',
        'total_transfer_sales',
        'total_all_sales',
        'transaction_count',
        'current_duration_in_hours',
    ];

    /**
     * Relación con el usuario (cajero)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con las ventas de esta sesión
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Scope para obtener sesiones abiertas
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope para obtener sesiones cerradas
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope para obtener sesiones de un usuario específico
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para obtener sesiones en un rango de fechas
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('opened_at', [$from, $to]);
    }

    /**
     * Verificar si la sesión está abierta
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Verificar si la sesión está cerrada
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Calcular total de ventas en efectivo de esta sesión
     */
    public function getTotalCashSalesAttribute(): float
    {
        return (float) $this->sales()
            ->where('payment_method', 'efectivo')
            ->where('status', 'completada')
            ->sum('total');
    }

    /**
     * Calcular total de ventas en tarjeta de esta sesión
     */
    public function getTotalCardSalesAttribute(): float
    {
        return (float) $this->sales()
            ->where('payment_method', 'tarjeta')
            ->where('status', 'completada')
            ->sum('total');
    }

    /**
     * Calcular total de ventas en transferencia de esta sesión
     */
    public function getTotalTransferSalesAttribute(): float
    {
        return (float) $this->sales()
            ->where('payment_method', 'transferencia')
            ->where('status', 'completada')
            ->sum('total');
    }

    /**
     * Calcular total de todas las ventas (todos los métodos de pago)
     */
    public function getTotalAllSalesAttribute(): float
    {
        return (float) $this->sales()
            ->where('status', 'completada')
            ->sum('total');
    }

    /**
     * Calcular número de transacciones
     */
    public function getTransactionCountAttribute(): int
    {
        return $this->sales()
            ->where('status', 'completada')
            ->count();
    }

    /**
     * Verificar si hay diferencia (faltante o sobrante)
     */
    public function hasDifference(): bool
    {
        return $this->difference !== null && $this->difference != 0;
    }

    /**
     * Verificar si hay faltante
     */
    public function hasShortage(): bool
    {
        return $this->difference !== null && $this->difference < 0;
    }

    /**
     * Verificar si hay sobrante
     */
    public function hasSurplus(): bool
    {
        return $this->difference !== null && $this->difference > 0;
    }

    /**
     * Obtener label de diferencia
     */
    public function getDifferenceLabelAttribute(): string
    {
        if (!$this->hasDifference()) {
            return 'Sin diferencia';
        }

        if ($this->hasShortage()) {
            return 'Faltante';
        }

        return 'Sobrante';
    }

    /**
     * Calcular duración de la sesión en horas
     */
    public function getDurationInHoursAttribute(): ?float
    {
        if (!$this->closed_at) {
            return null;
        }

        return round($this->opened_at->diffInHours($this->closed_at, true), 2);
    }

    /**
     * Calcular duración actual si está abierta
     */
    public function getCurrentDurationInHoursAttribute(): float
    {
        $endTime = $this->closed_at ?? now();
        return round($this->opened_at->diffInHours($endTime, true), 2);
    }
}
