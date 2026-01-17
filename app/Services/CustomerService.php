<?php

namespace App\Services;

use App\Models\DigitalCustomer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService
{
    /**
     * Get paginated customers with filters
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DigitalCustomer::query();

        // Filter by verified status
        if (isset($filters['is_verified'])) {
            $query->where('is_verified', (bool) $filters['is_verified']);
        }

        // Filter by active status
        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        // Search by name, phone, or email
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('phone', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Create a new customer
     */
    public function create(array $data): DigitalCustomer
    {
        return DB::transaction(function () use ($data) {
            $customer = DigitalCustomer::create([
                'restaurant_id' => 1,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'country_code' => $data['country_code'] ?? '+52',
                'email' => $data['email'] ?? null,
                'is_verified' => $data['is_verified'] ?? false,
                'is_active' => $data['is_active'] ?? true,
                'notes' => $data['notes'] ?? null,
            ]);

            Log::info('Cliente digital creado', [
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->full_phone,
            ]);

            return $customer;
        });
    }

    /**
     * Update an existing customer
     */
    public function update(DigitalCustomer $customer, array $data): DigitalCustomer
    {
        return DB::transaction(function () use ($customer, $data) {
            $updateData = [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'country_code' => $data['country_code'] ?? $customer->country_code,
                'email' => $data['email'] ?? null,
                'is_verified' => $data['is_verified'] ?? $customer->is_verified,
                'is_active' => $data['is_active'] ?? $customer->is_active,
                'notes' => $data['notes'] ?? null,
            ];

            $customer->update($updateData);

            Log::info('Cliente digital actualizado', [
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->full_phone,
            ]);

            return $customer->fresh();
        });
    }

    /**
     * Delete a customer
     */
    public function delete(DigitalCustomer $customer): bool
    {
        return DB::transaction(function () use ($customer) {
            $customerId = $customer->id;
            $customerName = $customer->name;

            $deleted = $customer->delete();

            if ($deleted) {
                Log::info('Cliente digital eliminado', [
                    'customer_id' => $customerId,
                    'name' => $customerName,
                ]);
            }

            return $deleted;
        });
    }

    /**
     * Toggle customer active status
     */
    public function toggleStatus(DigitalCustomer $customer): DigitalCustomer
    {
        return DB::transaction(function () use ($customer) {
            $customer->is_active = !$customer->is_active;
            $customer->save();

            Log::info('Estado de cliente cambiado', [
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'is_active' => $customer->is_active,
            ]);

            return $customer->fresh();
        });
    }

    /**
     * Toggle customer verified status
     */
    public function toggleVerified(DigitalCustomer $customer): DigitalCustomer
    {
        return DB::transaction(function () use ($customer) {
            $customer->is_verified = !$customer->is_verified;

            // Clear verification code if marking as verified
            if ($customer->is_verified) {
                $customer->verification_code = null;
                $customer->code_expires_at = null;
            }

            $customer->save();

            Log::info('Estado de verificación de cliente cambiado', [
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'is_verified' => $customer->is_verified,
            ]);

            return $customer->fresh();
        });
    }

    /**
     * Get customer statistics
     */
    public function getStatistics(): array
    {
        $incompleteCount = DigitalCustomer::where(function ($q) {
            $q->whereNull('name')
              ->orWhere('name', '')
              ->orWhere('name', 'like', 'Cliente%');
        })->where('orders_count', 0)->count();

        return [
            'total' => DigitalCustomer::count(),
            'verified' => DigitalCustomer::where('is_verified', true)->count(),
            'unverified' => DigitalCustomer::where('is_verified', false)->count(),
            'active' => DigitalCustomer::where('is_active', true)->count(),
            'incomplete' => $incompleteCount,
            'total_orders' => DigitalCustomer::sum('orders_count'),
            'total_revenue' => DigitalCustomer::sum('total_spent'),
        ];
    }

    /**
     * Delete incomplete customers (no name and no orders)
     */
    public function deleteIncompleteCustomers(): int
    {
        return DB::transaction(function () {
            $incompleteCustomers = DigitalCustomer::where(function ($q) {
                $q->whereNull('name')
                  ->orWhere('name', '')
                  ->orWhere('name', 'like', 'Cliente%');
            })
            ->where('orders_count', 0)
            ->doesntHave('sales') // Double-check with real relationship
            ->get();

            $count = $incompleteCustomers->count();

            if ($count > 0) {
                foreach ($incompleteCustomers as $customer) {
                    $customer->delete();
                }

                Log::info('Clientes incompletos eliminados en masa', [
                    'count' => $count,
                ]);
            }

            return $count;
        });
    }
}
