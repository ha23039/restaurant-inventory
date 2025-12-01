<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'unit_type' => $this->unit_type,
            'current_stock' => (float) $this->current_stock,
            'min_stock' => (float) $this->min_stock,
            'max_stock' => $this->max_stock ? (float) $this->max_stock : null,
            'unit_cost' => (float) $this->unit_cost,
            'expiry_date' => $this->expiry_date?->format('Y-m-d'),
            'is_active' => (bool) $this->is_active,
            'is_low_stock' => $this->current_stock <= $this->min_stock,
            'is_expiring_soon' => $this->expiry_date && $this->expiry_date->lte(now()->addDays(7)),

            // Relationships
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'color' => $this->category->color,
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
