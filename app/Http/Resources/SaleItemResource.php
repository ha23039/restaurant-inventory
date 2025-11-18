<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
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
            'product_type' => $this->product_type,
            'quantity' => (int) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'total_price' => (float) $this->total_price,

            // Product details (polymorphic)
            'product_name' => $this->product_type === 'menu'
                ? $this->menuItem?->name
                : $this->simpleProduct?->name,

            'menu_item' => $this->whenLoaded('menuItem', function () {
                return $this->menuItem ? new MenuItemResource($this->menuItem) : null;
            }),

            'simple_product' => $this->whenLoaded('simpleProduct', function () {
                return $this->simpleProduct ? new SimpleProductResource($this->simpleProduct) : null;
            }),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
