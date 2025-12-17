<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItemVariant extends Model
{
    protected $fillable = [
        'menu_item_id',
        'variant_name',
        'variant_sku',
        'price',
        'attributes',
        'is_available',
        'display_order',
        'image_path',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    /**
     * Relacion con el menu item padre
     */
    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    /**
     * Recetas (ingredientes) de esta variante
     */
    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * Items de venta que usan esta variante
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Calcular stock disponible basado en ingredientes
     * Similar a la logica de MenuItem pero para variantes
     */
    public function getAvailableQuantityAttribute()
    {
        if (!$this->is_available) {
            return 0;
        }

        $recipes = $this->recipes()->with('product')->get();

        if ($recipes->isEmpty()) {
            return 0;
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

    /**
     * Scope para ordenar por display_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('variant_name', 'asc');
    }
}
