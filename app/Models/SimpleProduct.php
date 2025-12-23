<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimpleProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'description',
        'sale_price',
        'cost_per_unit',
        'is_available',
        'allows_variants',
        'category',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'cost_per_unit' => 'decimal:3',
        'is_available' => 'boolean',
        'allows_variants' => 'boolean',
    ];

    // Relación con producto del inventario
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relación con variantes
    public function variants()
    {
        return $this->hasMany(SimpleProductVariant::class);
    }

    // Relación con items de venta
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    // Calcular disponibilidad basada en inventario - CORREGIDO
    public function getAvailableQuantityAttribute()
    {
        // Si no hay producto relacionado, devolver 0
        if (!$this->relationLoaded('product') || !$this->product) {
            return 0;
        }

        // Si no hay stock en el producto base, devolver 0
        $currentStock = floatval($this->product->current_stock);
        if ($currentStock <= 0) {
            return 0;
        }

        // Si cost_per_unit es 0 o negativo, devolver 0
        $costPerUnit = floatval($this->cost_per_unit);
        if ($costPerUnit <= 0) {
            return 0;
        }

        // Calcular cuántas unidades se pueden vender
        return floor($currentStock / $costPerUnit);
    }

    public function getIsInStockAttribute()
    {
        return $this->available_quantity > 0;
    }

    // Scopes para facilitar consultas
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
