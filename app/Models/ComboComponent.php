<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComboComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'combo_id',
        'component_type',
        'name',
        'quantity',
        'is_required',
        'sellable_type',
        'sellable_id',
        'default_variant_id',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_required' => 'boolean',
        'default_variant_id' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Combo al que pertenece
     */
    public function combo()
    {
        return $this->belongsTo(Combo::class);
    }

    /**
     * Opciones disponibles (solo para type = 'choice')
     */
    public function options()
    {
        return $this->hasMany(ComboComponentOption::class)->orderBy('sort_order');
    }

    /**
     * Opciones disponibles
     */
    public function availableOptions()
    {
        return $this->options()->where('is_available', true);
    }

    /**
     * Producto asociado (solo para type = 'fixed')
     * Relación polimórfica a MenuItem o SimpleProduct
     */
    public function sellable()
    {
        return $this->morphTo();
    }

    /**
     * Verificar si es componente fijo
     */
    public function isFixed(): bool
    {
        return $this->component_type === 'fixed';
    }

    /**
     * Verificar si es componente de elección
     */
    public function isChoice(): bool
    {
        return $this->component_type === 'choice';
    }

    /**
     * Relación con la variante por defecto (si aplica)
     */
    public function defaultVariant()
    {
        // La variante puede ser MenuItemVariant o SimpleProductVariant
        // según el sellable_type
        if ($this->sellable_type === 'menu_item' && $this->default_variant_id) {
            return $this->belongsTo(MenuItemVariant::class, 'default_variant_id');
        }

        if ($this->sellable_type === 'simple_product' && $this->default_variant_id) {
            return $this->belongsTo(SimpleProductVariant::class, 'default_variant_id');
        }

        return null;
    }

    /**
     * Verificar si tiene variante por defecto configurada
     */
    public function hasDefaultVariant(): bool
    {
        return $this->isFixed() && !empty($this->default_variant_id);
    }

    /**
     * Obtener el nombre del producto (para componentes fijos)
     */
    public function getProductNameAttribute(): ?string
    {
        if (!$this->isFixed() || !$this->sellable) {
            return null;
        }

        $name = $this->sellable->name ?? null;

        // Si tiene variante por defecto, incluir el nombre de la variante
        if ($this->default_variant_id) {
            $variantName = $this->getDefaultVariantName();
            if ($variantName) {
                $name .= ' - ' . $variantName;
            }
        }

        return $name;
    }

    /**
     * Obtener nombre de la variante por defecto
     */
    public function getDefaultVariantName(): ?string
    {
        if (!$this->default_variant_id) {
            return null;
        }

        if ($this->sellable_type === 'menu_item') {
            $variant = MenuItemVariant::find($this->default_variant_id);
            return $variant?->variant_name;
        }

        if ($this->sellable_type === 'simple_product') {
            $variant = SimpleProductVariant::find($this->default_variant_id);
            return $variant?->variant_name ?? $variant?->name;
        }

        return null;
    }

    /**
     * Verificar si el producto asociado está disponible (para fijos)
     */
    public function isProductAvailable(): bool
    {
        if (!$this->isFixed()) {
            return true;
        }

        if (!$this->sellable) {
            return false;
        }

        // Verificar disponibilidad según el tipo
        if ($this->sellable_type === 'simple_product') {
            return $this->sellable->is_available && $this->sellable->available_quantity > 0;
        }

        if ($this->sellable_type === 'menu_item') {
            return $this->sellable->is_available && $this->sellable->available_quantity > 0;
        }

        return false;
    }

    /**
     * Obtener la opción por defecto (para choices)
     */
    public function getDefaultOption(): ?ComboComponentOption
    {
        return $this->options()->where('is_default', true)->first()
            ?? $this->availableOptions()->first();
    }
}
