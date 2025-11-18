<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashFlowResource extends JsonResource
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
            'type' => $this->type,
            'category' => $this->category,
            'category_label' => $this->category_label,
            'amount' => (float) $this->amount,
            'formatted_amount' => $this->formatted_amount,
            'signed_amount' => $this->signed_amount,
            'is_income' => $this->is_income,
            'is_expense' => $this->is_expense,
            'description' => $this->description,
            'notes' => $this->notes,
            'flow_date' => $this->flow_date->format('Y-m-d'),
            'flow_date_formatted' => $this->flow_date->format('d/m/Y'),

            // Relationships
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'role' => $this->user->role,
                ];
            }),

            'sale' => $this->whenLoaded('sale', function () {
                return $this->sale ? [
                    'id' => $this->sale->id,
                    'sale_number' => $this->sale->sale_number,
                    'total' => (float) $this->sale->total,
                    'payment_method' => $this->sale->payment_method,
                ] : null;
            }),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
