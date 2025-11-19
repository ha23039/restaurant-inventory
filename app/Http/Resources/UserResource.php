<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'role_label' => $this->getRoleLabel(),
            'is_active' => $this->is_active,
            'status_label' => $this->is_active ? 'Activo' : 'Inactivo',
            'created_at' => $this->created_at?->toISOString(),
            'created_at_formatted' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->toISOString(),
            'can_edit' => $this->canEdit($request),
            'can_delete' => $this->canDelete($request),
        ];
    }

    /**
     * Get human-readable role label
     */
    private function getRoleLabel(): string
    {
        return match($this->role) {
            'admin' => 'Administrador',
            'chef' => 'Chef',
            'almacenero' => 'Almacenero',
            'cajero' => 'Cajero',
            default => ucfirst($this->role),
        };
    }

    /**
     * Check if current user can edit this user
     */
    private function canEdit(Request $request): bool
    {
        $currentUser = $request->user();

        // Admins can edit anyone
        if ($currentUser->role === 'admin') {
            return true;
        }

        // Users can't edit others
        return false;
    }

    /**
     * Check if current user can delete this user
     */
    private function canDelete(Request $request): bool
    {
        $currentUser = $request->user();

        // Can't delete yourself
        if ($this->id === $currentUser->id) {
            return false;
        }

        // Only admins can delete
        return $currentUser->role === 'admin';
    }
}
