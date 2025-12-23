<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimpleProductVariantRecipe extends Model
{
    protected $fillable = [
        'simple_product_variant_id',
        'product_id',
        'quantity_needed',
        'unit',
        'restaurant_id',
    ];

    protected $casts = [
        'quantity_needed' => 'decimal:3',
    ];

    /**
     * Relación con la variante del producto simple
     */
    public function simpleProductVariant()
    {
        return $this->belongsTo(SimpleProductVariant::class);
    }

    /**
     * Relación con el producto (ingrediente) del inventario
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
