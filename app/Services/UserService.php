<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    /**
     * Get paginated users with filters
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query();

        // Filter by role
        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        // Filter by status
        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        // Search by name or email
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Create a new user
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            Log::info('Usuario creado', [
                'user_id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ]);

            return $user;
        });
    }

    /**
     * Update an existing user
     */
    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? $user->is_active,
            ];

            // Only update password if provided
            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $user->update($updateData);

            Log::info('Usuario actualizado', [
                'user_id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ]);

            return $user->fresh();
        });
    }

    /**
     * Delete a user
     */
    public function delete(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            $userId = $user->id;
            $userName = $user->name;

            $deleted = $user->delete();

            if ($deleted) {
                Log::info('Usuario eliminado', [
                    'user_id' => $userId,
                    'name' => $userName,
                ]);
            }

            return $deleted;
        });
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user): User
    {
        return DB::transaction(function () use ($user) {
            $user->is_active = !$user->is_active;
            $user->save();

            Log::info('Estado de usuario cambiado', [
                'user_id' => $user->id,
                'name' => $user->name,
                'is_active' => $user->is_active,
            ]);

            return $user->fresh();
        });
    }

    /**
     * Reset user password
     */
    public function resetPassword(User $user, string $newPassword): User
    {
        return DB::transaction(function () use ($user, $newPassword) {
            $user->password = Hash::make($newPassword);
            $user->save();

            Log::info('Contraseña reseteada', [
                'user_id' => $user->id,
                'name' => $user->name,
            ]);

            return $user->fresh();
        });
    }

    /**
     * Get user statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'by_role' => [
                'admin' => User::where('role', 'admin')->count(),
                'chef' => User::where('role', 'chef')->count(),
                'almacenero' => User::where('role', 'almacenero')->count(),
                'cajero' => User::where('role', 'cajero')->count(),
            ],
        ];
    }

    /**
     * Get available roles
     */
    public function getAvailableRoles(): array
    {
        return [
            ['value' => 'admin', 'label' => 'Administrador'],
            ['value' => 'chef', 'label' => 'Chef'],
            ['value' => 'almacenero', 'label' => 'Almacenero'],
            ['value' => 'cajero', 'label' => 'Cajero'],
        ];
    }
}
