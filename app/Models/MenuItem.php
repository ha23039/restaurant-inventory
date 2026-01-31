<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image_path',
        'is_available',
        'show_in_digital_menu',
        'available_in_combos',
        'is_service',
        'has_variants',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'show_in_digital_menu' => 'boolean',
        'available_in_combos' => 'boolean',
        'is_service' => 'boolean',
        'has_variants' => 'boolean',
    ];

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Variantes de este menu item
     */
    public function variants()
    {
        return $this->hasMany(MenuItemVariant::class);
    }

    /**
     * Scope para items con variantes
     */
    public function scopeWithVariants($query)
    {
        return $query->where('has_variants', true);
    }

    /**
     * Scope para items sin variantes
     */
    public function scopeWithoutVariants($query)
    {
        return $query->where('has_variants', false);
    }

    /**
     * Scope para items visibles en menú digital
     */
    public function scopeForDigitalMenu($query)
    {
        return $query->where('is_available', true)
                     ->where('show_in_digital_menu', true);
    }

    /**
     * Scope para items disponibles en combos
     */
    public function scopeAvailableForCombos($query)
    {
        return $query->where('is_available', true)
                     ->where('available_in_combos', true);
    }

    /**
     * Calcular stock disponible basado en ingredientes
     */
    public function getAvailableQuantityAttribute()
    {
        if (!$this->is_available) {
            return 0;
        }

        // Si tiene variantes, considerar disponible si al menos una variante tiene stock
        if ($this->has_variants) {
            $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->get();
            if ($variants->isEmpty()) {
                return 999; // Sin variantes configuradas = siempre disponible
            }
            // Retornar el máximo de las variantes disponibles
            return $variants->max(fn($v) => $v->available_quantity) ?? 0;
        }

        // Para items sin variantes, calcular basado en recetas
        $recipes = $this->relationLoaded('recipes') ? $this->recipes : $this->recipes()->with('product')->get();

        if ($recipes->isEmpty()) {
            return 999; // Sin recetas = siempre disponible
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
}
