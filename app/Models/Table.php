<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_number',
        'name',
        'capacity',
        'status',
        'current_sale_id',
        'last_occupied_at',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
        'last_occupied_at' => 'datetime',
    ];

    /**
     * Get the current sale on this table
     */
    public function currentSale()
    {
        return $this->belongsTo(Sale::class, 'current_sale_id');
    }

    /**
     * Get all sales that have been at this table
     */
    public function sales()
    {
        return $this->hasMany(Sale::class, 'table_id');
    }

    /**
     * Scope for active tables
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for available tables
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'disponible')->where('is_active', true);
    }

    /**
     * Scope for occupied tables
     */
    public function scopeOccupied($query)
    {
        return $query->where('status', 'ocupada');
    }

    /**
     * Check if table is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'disponible' && $this->is_active;
    }

    /**
     * Check if table is occupied
     */
    public function isOccupied(): bool
    {
        return $this->status === 'ocupada';
    }

    /**
     * Get status label in Spanish
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'disponible' => 'Disponible',
            'ocupada' => 'Ocupada',
            'reservada' => 'Reservada',
            'en_limpieza' => 'En Limpieza',
            default => $this->status,
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'disponible' => 'green',
            'ocupada' => 'red',
            'reservada' => 'yellow',
            'en_limpieza' => 'blue',
            default => 'gray',
        };
    }
}
