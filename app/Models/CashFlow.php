<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashFlow extends Model
{
    use HasFactory;

    // Especificar el nombre correcto de la tabla (singular)
    protected $table = 'cash_flow';

    protected $fillable = [
        'user_id',
        'sale_id',
        'type',
        'category',
        'amount',
        'description',
        'notes',
        'flow_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'flow_date' => 'date',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // Scopes
    public function scopeEntradas($query)
    {
        return $query->where('type', 'entrada');
    }

    public function scopeSalidas($query)
    {
        return $query->where('type', 'salida');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('flow_date', [$from, $to]);
    }
}
