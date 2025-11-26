<?php

namespace App\Policies;

use App\Models\SimpleProduct;
use App\Models\User;

class SimpleProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'almacenero']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SimpleProduct $simpleProduct): bool
    {
        return in_array($user->role, ['admin', 'almacenero']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'almacenero']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SimpleProduct $simpleProduct): bool
    {
        return in_array($user->role, ['admin', 'almacenero']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SimpleProduct $simpleProduct): bool
    {
        // Solo admin puede eliminar productos simples
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SimpleProduct $simpleProduct): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SimpleProduct $simpleProduct): bool
    {
        return $user->role === 'admin';
    }
}
