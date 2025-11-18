<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
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
            'price' => (float) $this->price,
            'image_path' => $this->image_path,
            'is_available' => (bool) $this->is_available,

            // Computed attributes
            'available_quantity' => $this->when(
                isset($this->available_quantity),
                $this->available_quantity
            ),
            'is_in_stock' => $this->when(
                isset($this->is_in_stock),
                $this->is_in_stock
            ),

            // Relationships
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),

            'recipes' => $this->whenLoaded('recipes', function () {
                return $this->recipes->map(function ($recipe) {
                    return [
                        'id' => $recipe->id,
                        'product_id' => $recipe->product_id,
                        'product_name' => $recipe->product->name ?? null,
                        'quantity_needed' => (float) $recipe->quantity_needed,
                        'unit' => $recipe->unit,
                    ];
                });
            }),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
