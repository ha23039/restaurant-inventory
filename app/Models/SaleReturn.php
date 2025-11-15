<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleReturn extends Model
{
    protected $fillable = [
        'sale_id',
        'processed_by_user_id',
        'return_number',
        'return_type',
        'reason',
        'notes',
        'subtotal_returned',
        'tax_returned',
        'total_returned',
        'status',
        'refund_method',
        'inventory_restored',
        'cash_flow_adjusted',
        'return_date',
        'processed_at'
    ];

    protected $casts = [
        'subtotal_returned' => 'decimal:2',
        'tax_returned' => 'decimal:2',
        'total_returned' => 'decimal:2',
        'inventory_restored' => 'boolean',
        'cash_flow_adjusted' => 'boolean',
        'return_date' => 'date',
        'processed_at' => 'datetime'
    ];

    /**
     * Relación con la venta original
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Usuario que procesó la devolución
     */
    public function processedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    /**
     * Items de la devolución
     */
    public function returnItems(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    /**
     * Generar número único de devolución
     */
    public static function generateReturnNumber(): string
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return 'RET' . $date . sprintf('%04d', $count);
    }

    /**
     * Verificar si se puede procesar la devolución
     */
    public function canBeProcessed(): bool
    {
        return $this->status === 'pending' && 
               $this->sale->status === 'completada' &&
               $this->returnItems()->count() > 0;
    }

    /**
     * Marcar como completada
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now()
        ]);
    }

    /**
     * Scope para devoluciones del día
     */
    public function scopeToday($query)
    {
        return $query->whereDate('return_date', today());
    }

    /**
     * Scope para devoluciones completadas
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}