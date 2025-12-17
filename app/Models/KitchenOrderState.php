<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitchenOrderState extends Model
{
    protected $fillable = [
        'sale_id',
        'status',
        'started_at',
        'completed_at',
        'assigned_to_user_id',
        'priority',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'priority' => 'integer',
    ];

    /**
     * Relación con Sale
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relación con User (Chef asignado)
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * Obtener tiempo transcurrido en minutos
     */
    public function getElapsedMinutesAttribute(): int
    {
        return (int) $this->created_at->diffInMinutes(now());
    }

    /**
     * Obtener color basado en tiempo transcurrido
     */
    public function getColorAttribute(): string
    {
        $minutes = $this->elapsed_minutes;

        if ($minutes < 5) {
            return 'green';
        } elseif ($minutes < 10) {
            return 'yellow';
        } elseif ($minutes < 15) {
            return 'orange';
        } else {
            return 'red';
        }
    }

    /**
     * Scopes para búsquedas comunes
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['entregada']);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOrderByPriority($query)
    {
        return $query->orderByDesc('priority')->orderBy('created_at');
    }
}
