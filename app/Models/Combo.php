<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Combo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'base_price',
        'category',
        'is_available',
        'show_in_menu',
        'show_in_pos',
        'sort_order',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_available' => 'boolean',
        'show_in_menu' => 'boolean',
        'show_in_pos' => 'boolean',
    ];

    /**
     * Componentes del combo
     */
    public function components()
    {
        return $this->hasMany(ComboComponent::class)->orderBy('sort_order');
    }

    /**
     * Componentes fijos (siempre incluidos)
     */
    public function fixedComponents()
    {
        return $this->components()->where('component_type', 'fixed');
    }

    /**
     * Componentes de elección (cliente elige)
     */
    public function choiceComponents()
    {
        return $this->components()->where('component_type', 'choice');
    }

    /**
     * Scope para combos disponibles
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope para menú digital
     */
    public function scopeForMenu($query)
    {
        return $query->available()->where('show_in_menu', true);
    }

    /**
     * Scope para POS
     */
    public function scopeForPos($query)
    {
        return $query->available()->where('show_in_pos', true);
    }

    /**
     * Scope ordenado
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Calcular precio final con ajustes
     *
     * @param array $selectedOptions Array de [component_id => option_id]
     * @return float
     */
    public function calculateFinalPrice(array $selectedOptions = []): float
    {
        $price = (float) $this->base_price;

        foreach ($selectedOptions as $componentId => $optionId) {
            $option = ComboComponentOption::find($optionId);
            if ($option && $option->comboComponent->combo_id === $this->id) {
                $price += (float) $option->price_adjustment;
            }
        }

        return max(0, $price);
    }

    /**
     * Verificar si el combo está completamente disponible
     * (todos los componentes fijos y al menos una opción por choice están disponibles)
     */
    public function getIsFullyAvailableAttribute(): bool
    {
        if (!$this->is_available) {
            return false;
        }

        // Verificar componentes fijos
        foreach ($this->fixedComponents as $component) {
            if (!$component->isProductAvailable()) {
                return false;
            }
        }

        // Verificar que cada choice tenga al menos una opción disponible
        foreach ($this->choiceComponents as $component) {
            $hasAvailableOption = $component->options()
                ->where('is_available', true)
                ->exists();

            if ($component->is_required && !$hasAvailableOption) {
                return false;
            }
        }

        return true;
    }
}
