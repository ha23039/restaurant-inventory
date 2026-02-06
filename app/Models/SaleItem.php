<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'menu_item_id',
        'menu_item_variant_id',
        'simple_product_id',
        'simple_product_variant_id',
        'combo_id',
        'combo_selections',
        'free_sale_name',
        'free_sale_price',
        'quantity',
        'unit_price',
        'total_price',
        'product_type',
        'cancelled_at',
        'cancelled_by_user_id',
        'cancellation_reason',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'combo_selections' => 'array',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Atributos a incluir en la serialización JSON
     */
    protected $appends = ['is_cancelled'];

    /**
     * Scope para items activos (no cancelados)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('cancelled_at');
    }

    /**
     * Scope para items cancelados
     */
    public function scopeCancelled($query)
    {
        return $query->whereNotNull('cancelled_at');
    }

    /**
     * Usuario que canceló el item
     */
    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    /**
     * Verificar si el item está cancelado
     */
    public function getIsCancelledAttribute(): bool
    {
        return $this->cancelled_at !== null;
    }

    // Relaciones
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    /**
     * Relacion con variante de menu item
     */
    public function menuItemVariant()
    {
        return $this->belongsTo(MenuItemVariant::class);
    }

    public function simpleProduct()
    {
        return $this->belongsTo(SimpleProduct::class);
    }

    public function simpleProductVariant()
    {
        return $this->belongsTo(SimpleProductVariant::class);
    }

    public function combo()
    {
        return $this->belongsTo(Combo::class);
    }

    // Accessor para obtener el producto (sea del menú, variante, simple o combo)
    public function getProductAttribute()
    {
        return match ($this->product_type) {
            'variant' => $this->menuItemVariant,
            'simple_variant' => $this->simpleProductVariant,
            'menu' => $this->menuItem,
            'simple' => $this->simpleProduct,
            'combo' => $this->combo,
            default => $this->simpleProduct,
        };
    }

    // Accessor para obtener el nombre del producto
    public function getProductNameAttribute()
    {
        return match ($this->product_type) {
            'free' => $this->free_sale_name,
            'variant' => $this->menuItemVariant?->variant_name,
            'simple_variant' => $this->simpleProductVariant?->variant_name,
            'menu' => $this->menuItem?->name,
            'simple' => $this->simpleProduct?->name,
            'combo' => $this->combo?->name,
            default => $this->simpleProduct?->name,
        };
    }

    /**
     * Obtener detalles de los componentes del combo (para display)
     */
    public function getComboComponentsDetailAttribute(): array
    {
        if ($this->product_type !== 'combo' || !$this->combo_selections) {
            return [];
        }

        return $this->combo_selections['components_detail'] ?? [];
    }
}
