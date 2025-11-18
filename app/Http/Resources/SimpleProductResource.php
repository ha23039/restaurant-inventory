<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SimpleProductResource extends JsonResource
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
            'cost_per_unit' => (float) $this->cost_per_unit,
            'sale_price' => (float) $this->sale_price,
            'profit_margin' => (float) $this->profit_margin,
            'is_available' => (bool) $this->is_available,

            // Computed attributes
            'available_quantity' => $this->when(
                isset($this->available_quantity),
                $this->available_quantity
            ),

            // Relationships
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'current_stock' => (float) $this->product->current_stock,
                    'unit_type' => $this->product->unit_type,
                ];
            }),

            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
