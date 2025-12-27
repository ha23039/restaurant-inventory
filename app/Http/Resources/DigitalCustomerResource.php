<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DigitalCustomerResource extends JsonResource
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
            'phone' => $this->phone,
            'country_code' => $this->country_code,
            'full_phone' => $this->full_phone,
            'email' => $this->email,
            'is_verified' => $this->is_verified,
            'is_active' => $this->is_active,
            'verified_label' => $this->is_verified ? 'Verificado' : 'No verificado',
            'status_label' => $this->is_active ? 'Activo' : 'Inactivo',
            'orders_count' => $this->orders_count ?? 0,
            'total_spent' => $this->total_spent ?? 0,
            'total_spent_formatted' => '$' . number_format($this->total_spent ?? 0, 2),
            'last_order_at' => $this->last_order_at?->toISOString(),
            'last_order_at_formatted' => $this->last_order_at?->format('d/m/Y H:i'),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'created_at_formatted' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->toISOString(),
            'can_edit' => $this->canEdit($request),
            'can_delete' => $this->canDelete($request),
        ];
    }

    /**
     * Check if current user can edit this customer
     */
    private function canEdit(Request $request): bool
    {
        $currentUser = $request->user();

        // Only admins can edit customers
        return $currentUser && $currentUser->role === 'admin';
    }

    /**
     * Check if current user can delete this customer
     */
    private function canDelete(Request $request): bool
    {
        $currentUser = $request->user();

        // Only admins can delete customers
        // Don't allow deletion if customer has orders
        if ($currentUser && $currentUser->role === 'admin') {
            return $this->orders_count === 0;
        }

        return false;
    }
}
