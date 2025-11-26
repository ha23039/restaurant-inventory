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
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_service' => 'boolean',
    ];

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
