<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sale_number',
        'subtotal',
        'tax',
        'discount',
        'total',
        'payment_method',
        'status'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function cashFlow()
    {
        return $this->hasOne(CashFlow::class);
    }

    // ==========================================
    // 🔄 NUEVAS RELACIONES PARA DEVOLUCIONES
    // ==========================================
    
    /**
     * Relación con las devoluciones
     */
    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    /**
     * Obtener devoluciones completadas
     */
    public function completedReturns()
    {
        return $this->hasMany(SaleReturn::class)->where('status', 'completed');
    }

    /**
     * Obtener el total devuelto
     */
    public function getTotalReturnedAttribute()
    {
        return $this->completedReturns()->sum('total_returned') ?? 0;
    }

    /**
     * Verificar si la venta puede tener devoluciones
     */
    public function canHaveReturns()
    {
        return $this->status === 'completada' && $this->total_returned < $this->total;
    }

    /**
     * Obtener el monto disponible para devolver
     */
    public function getAvailableForReturnAttribute()
    {
        return $this->total - $this->total_returned;
    }
}