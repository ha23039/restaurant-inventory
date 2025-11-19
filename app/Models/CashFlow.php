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

    public function scopeIncome($query)
    {
        return $query->where('type', 'entrada');
    }

    public function scopeExpense($query)
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

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('flow_date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('flow_date', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('flow_date', now()->year)
            ->whereMonth('flow_date', now()->month);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('flow_date', now()->year);
    }

    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('description', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getIsIncomeAttribute()
    {
        return $this->type === 'entrada';
    }

    public function getIsExpenseAttribute()
    {
        return $this->type === 'salida';
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getSignedAmountAttribute()
    {
        return $this->is_income ? $this->amount : -$this->amount;
    }

    public function getCategoryLabelAttribute()
    {
        $labels = [
            'ventas' => 'Ventas',
            'compras' => 'Compras',
            'gastos_operativos' => 'Gastos Operativos',
            'gastos_admin' => 'Gastos Administrativos',
            'devoluciones' => 'Devoluciones',
            'mantenimiento' => 'Mantenimiento',
            'marketing' => 'Marketing',
            'otros' => 'Otros',
        ];

        return $labels[$this->category] ?? ucfirst($this->category);
    }
}
