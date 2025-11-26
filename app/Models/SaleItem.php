<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'menu_item_id',
        'simple_product_id',
        'free_sale_name',
        'free_sale_price',
        'quantity',
        'unit_price',
        'total_price',
        'product_type',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // Relaciones
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function simpleProduct()
    {
        return $this->belongsTo(SimpleProduct::class);
    }

    // Accessor para obtener el producto (sea del menú o simple)
    public function getProductAttribute()
    {
        if ($this->product_type === 'menu') {
            return $this->menuItem;
        } else {
            return $this->simpleProduct;
        }
    }

    // Accessor para obtener el nombre del producto
    public function getProductNameAttribute()
    {
        if ($this->product_type === 'free') {
            return $this->free_sale_name;
        } elseif ($this->product_type === 'menu') {
            return $this->menuItem?->name;
        } else {
            return $this->simpleProduct?->name;
        }
    }
}
