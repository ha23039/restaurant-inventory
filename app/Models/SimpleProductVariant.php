<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimpleProductVariant extends Model
{
    protected $fillable = [
        'simple_product_id',
        'variant_name',
        'description',
        'price',
        'attributes',
        'image_path',
        'is_available',
        'restaurant_id',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    /**
     * Relación con el producto simple padre
     */
    public function simpleProduct()
    {
        return $this->belongsTo(SimpleProduct::class);
    }

    /**
     * Recetas (ingredientes) de esta variante
     */
    public function recipes()
    {
        return $this->hasMany(SimpleProductVariantRecipe::class);
    }

    /**
     * Items de venta que usan esta variante
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'simple_product_variant_id');
    }

    /**
     * Calcular stock disponible basado en ingredientes
     * Similar a la lógica de MenuItem pero para variantes de productos simples
     */
    public function getAvailableQuantityAttribute()
    {
        if (!$this->is_available) {
            return 0;
        }

        $recipes = $this->recipes()->with('product')->get();

        // Si no tiene recetas, se considera siempre disponible
        if ($recipes->isEmpty()) {
            return 999;
        }

        $availableQuantities = [];

        foreach ($recipes as $recipe) {
            if (!$recipe->product) {
                return 0;
            }

            $currentStock = $recipe->product->current_stock;
            $neededPerUnit = $recipe->quantity_needed;

            if ($neededPerUnit <= 0) {
                continue;
            }

            $availableQuantities[] = floor($currentStock / $neededPerUnit);
        }

        return empty($availableQuantities) ? 0 : min($availableQuantities);
    }

    /**
     * Scope para variantes disponibles
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}
