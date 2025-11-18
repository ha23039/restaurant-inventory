<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleReturnItem extends Model
{
    protected $fillable = [
        'sale_return_id',
        'sale_item_id',
        'quantity_returned',
        'original_quantity',
        'unit_price',
        'total_price',
        'inventory_restored',
    ];

    protected $casts = [
        'quantity_returned' => 'integer',
        'original_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'inventory_restored' => 'boolean',
    ];

    /**
     * Relación con la devolución
     */
    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class);
    }

    /**
     * Relación con el item original vendido
     */
    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    /**
     * Verificar si se puede devolver más cantidad
     */
    public function canReturnMore(): bool
    {
        $totalReturned = self::where('sale_item_id', $this->sale_item_id)
            ->whereHas('saleReturn', function ($query) {
                $query->where('status', '!=', 'cancelled');
            })
            ->sum('quantity_returned');

        return $totalReturned < $this->original_quantity;
    }

    /**
     * Marcar como inventario restaurado
     */
    public function markInventoryRestored(): void
    {
        $this->update(['inventory_restored' => true]);
    }
}
