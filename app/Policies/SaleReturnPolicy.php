<?php

namespace App\Policies;

use App\Models\SaleReturn;
use App\Models\User;

class SaleReturnPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'cajero']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SaleReturn $saleReturn): bool
    {
        return in_array($user->role, ['admin', 'cajero']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'cajero']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SaleReturn $saleReturn): bool
    {
        // Solo admin puede modificar devoluciones
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SaleReturn $saleReturn): bool
    {
        // Solo admin puede eliminar devoluciones
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SaleReturn $saleReturn): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SaleReturn $saleReturn): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can process returns.
     */
    public function processReturn(User $user): bool
    {
        return in_array($user->role, ['admin', 'cajero']);
    }

    /**
     * Determine whether the user can view operational losses.
     */
    public function viewOperationalLosses(User $user): bool
    {
        return $user->role === 'admin';
    }
}
