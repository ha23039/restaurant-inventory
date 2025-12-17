<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_item_id',
        'menu_item_variant_id',
        'product_id',
        'quantity_needed',
        'unit',
    ];

    protected $casts = [
        'quantity_needed' => 'decimal:3',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    /**
     * Relacion con variante de menu item (si aplica)
     */
    public function menuItemVariant()
    {
        return $this->belongsTo(MenuItemVariant::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // MÉTODO CLAVE: Calcular costo de ingredientes
    public function calculateCost()
    {
        return $this->quantity_needed * $this->product->unit_cost;
    }
}
