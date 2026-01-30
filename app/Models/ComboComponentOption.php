<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComboComponentOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'combo_component_id',
        'sellable_type',
        'sellable_id',
        'price_adjustment',
        'is_default',
        'is_available',
        'sort_order',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'is_default' => 'boolean',
        'is_available' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Componente al que pertenece
     */
    public function comboComponent()
    {
        return $this->belongsTo(ComboComponent::class);
    }

    /**
     * Producto asociado (MenuItem o SimpleProduct)
     * Relación polimórfica
     */
    public function sellable()
    {
        return $this->morphTo();
    }

    /**
     * Obtener el nombre del producto
     */
    public function getProductNameAttribute(): ?string
    {
        return $this->sellable->name ?? null;
    }

    /**
     * Obtener la imagen del producto
     */
    public function getProductImageAttribute(): ?string
    {
        return $this->sellable->image_path ?? null;
    }

    /**
     * Verificar si el producto tiene variantes
     */
    public function hasVariants(): bool
    {
        if ($this->sellable_type === 'simple_product' && $this->sellable) {
            return $this->sellable->allows_variants && $this->sellable->variants()->count() > 0;
        }

        if ($this->sellable_type === 'menu_item' && $this->sellable) {
            return $this->sellable->has_variants && $this->sellable->variants()->count() > 0;
        }

        return false;
    }

    /**
     * Obtener las variantes disponibles del producto
     */
    public function getAvailableVariants()
    {
        if (!$this->hasVariants()) {
            return collect();
        }

        if ($this->sellable_type === 'simple_product') {
            return $this->sellable->variants()
                ->where('is_available', true)
                ->get();
        }

        if ($this->sellable_type === 'menu_item') {
            return $this->sellable->variants()
                ->where('is_available', true)
                ->get();
        }

        return collect();
    }

    /**
     * Verificar si esta opción está realmente disponible
     * (producto existe, está activo, y tiene stock)
     */
    public function isReallyAvailable(): bool
    {
        if (!$this->is_available || !$this->sellable) {
            return false;
        }

        // Para productos con variantes, verificar si al menos una variante está disponible
        if ($this->hasVariants()) {
            return $this->getAvailableVariants()->isNotEmpty();
        }

        // Para productos simples sin variantes
        if ($this->sellable_type === 'simple_product') {
            return $this->sellable->is_available && $this->sellable->available_quantity > 0;
        }

        // Para menu items sin variantes
        if ($this->sellable_type === 'menu_item') {
            return $this->sellable->is_available && $this->sellable->available_quantity > 0;
        }

        return false;
    }

    /**
     * Formatear el ajuste de precio para mostrar
     */
    public function getFormattedPriceAdjustmentAttribute(): string
    {
        $adjustment = (float) $this->price_adjustment;

        if ($adjustment === 0.0) {
            return 'Incluido';
        }

        $formatted = '$' . number_format(abs($adjustment), 2);

        return $adjustment > 0 ? "+{$formatted}" : "-{$formatted}";
    }
}
