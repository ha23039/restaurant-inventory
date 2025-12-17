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
        'is_service',
        'has_variants',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
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
}
