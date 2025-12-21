<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Boot method para manejar eventos del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Cuando se elimine la venta (soft o hard), eliminar registros de cocina
        static::deleting(function ($sale) {
            $sale->kitchenOrderState()->delete();
        });
    }

    protected $fillable = [
        'user_id',
        'cash_register_session_id',
        'table_id',
        'sale_number',
        'customer_name',
        'notes',
        'subtotal',
        'tax',
        'discount',
        'total',
        'payment_method',
        'status',
        'is_free_sale',
        'free_sale_description',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'is_free_sale' => 'boolean',
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

    public function cashRegisterSession()
    {
        return $this->belongsTo(CashRegisterSession::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
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

    // ==========================================
    // 🍳 KITCHEN DISPLAY
    // ==========================================

    /**
     * Relación con el estado de cocina
     */
    public function kitchenOrderState()
    {
        return $this->hasOne(KitchenOrderState::class);
    }
}
