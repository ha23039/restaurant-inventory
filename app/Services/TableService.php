<?php

namespace App\Services;

use App\Models\Table;
use App\Models\Sale;
use App\Repositories\Contracts\TableRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TableService
{
    public function __construct(
        private TableRepositoryInterface $tableRepository
    ) {}

    /**
     * Get all tables with optional filters
     */
    public function getAll(array $filters = []): Collection
    {
        // If status filter is provided
        if (!empty($filters['status'])) {
            return $this->tableRepository->getByStatus($filters['status']);
        }

        // If search query is provided
        if (!empty($filters['search'])) {
            return $this->tableRepository->search($filters['search']);
        }

        // Default: return all active tables
        return $this->tableRepository->getActiveTables();
    }

    /**
     * Get table statistics
     */
    public function getStatistics(): array
    {
        return $this->tableRepository->getStatistics();
    }

    /**
     * Get available tables
     */
    public function getAvailableTables(): Collection
    {
        return $this->tableRepository->getAvailableTables();
    }

    /**
     * Get occupied tables with current sales
     */
    public function getOccupiedTables(): Collection
    {
        return $this->tableRepository->getOccupiedTables();
    }

    /**
     * Create a new table
     */
    public function create(array $data): Table
    {
        return DB::transaction(function () use ($data) {
            $table = Table::create([
                'table_number' => $data['table_number'],
                'name' => $data['name'] ?? null,
                'capacity' => $data['capacity'] ?? 4,
                'status' => 'disponible',
                'notes' => $data['notes'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            Log::info('Mesa creada', [
                'table_id' => $table->id,
                'table_number' => $table->table_number,
                'capacity' => $table->capacity,
            ]);

            return $table;
        });
    }

    /**
     * Update an existing table
     */
    public function update(Table $table, array $data): Table
    {
        return DB::transaction(function () use ($table, $data) {
            $table->update([
                'table_number' => $data['table_number'] ?? $table->table_number,
                'name' => $data['name'] ?? $table->name,
                'capacity' => $data['capacity'] ?? $table->capacity,
                'notes' => $data['notes'] ?? $table->notes,
                'is_active' => $data['is_active'] ?? $table->is_active,
            ]);

            Log::info('Mesa actualizada', [
                'table_id' => $table->id,
                'table_number' => $table->table_number,
            ]);

            return $table->fresh();
        });
    }

    /**
     * Delete a table
     */
    public function delete(Table $table): bool
    {
        return DB::transaction(function () use ($table) {
            // Cannot delete if table is occupied
            if ($table->status === 'ocupada') {
                throw new \Exception('No se puede eliminar una mesa ocupada');
            }

            $tableNumber = $table->table_number;
            $deleted = $table->delete();

            if ($deleted) {
                Log::info('Mesa eliminada', [
                    'table_number' => $tableNumber,
                ]);
            }

            return $deleted;
        });
    }

    /**
     * Change table status
     */
    public function updateStatus(Table $table, string $status): Table
    {
        return DB::transaction(function () use ($table, $status) {
            $this->tableRepository->updateStatus($table->id, $status);

            Log::info('Estado de mesa cambiado', [
                'table_id' => $table->id,
                'table_number' => $table->table_number,
                'old_status' => $table->status,
                'new_status' => $status,
            ]);

            return $table->fresh();
        });
    }

    /**
     * Occupy table with a sale
     */
    public function occupyTable(Table $table, Sale $sale): Table
    {
        return DB::transaction(function () use ($table, $sale) {
            if (!$table->isAvailable()) {
                throw new \Exception('La mesa no está disponible');
            }

            $this->tableRepository->markAsOccupied($table->id, $sale->id);

            // Update sale with table_id
            $sale->update(['table_id' => $table->id]);

            Log::info('Mesa ocupada', [
                'table_id' => $table->id,
                'table_number' => $table->table_number,
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
            ]);

            return $table->fresh();
        });
    }

    /**
     * Release table (mark as available)
     */
    public function releaseTable(Table $table): Table
    {
        return DB::transaction(function () use ($table) {
            $this->tableRepository->markAsAvailable($table->id);

            Log::info('Mesa liberada', [
                'table_id' => $table->id,
                'table_number' => $table->table_number,
            ]);

            return $table->fresh();
        });
    }

    /**
     * Get table with current sale details
     */
    public function getTableWithSale(int $tableId): ?Table
    {
        return $this->tableRepository->getWithCurrentSale($tableId);
    }

    /**
     * Toggle table active status
     */
    public function toggleActive(Table $table): Table
    {
        return DB::transaction(function () use ($table) {
            // Cannot deactivate if occupied
            if ($table->status === 'ocupada') {
                throw new \Exception('No se puede desactivar una mesa ocupada');
            }

            $table->update(['is_active' => !$table->is_active]);

            Log::info('Estado activo de mesa cambiado', [
                'table_id' => $table->id,
                'table_number' => $table->table_number,
                'is_active' => $table->is_active,
            ]);

            return $table->fresh();
        });
    }

    /**
     * Get tables by capacity
     */
    public function getByCapacity(int $minCapacity, ?int $maxCapacity = null): Collection
    {
        return $this->tableRepository->getByCapacity($minCapacity, $maxCapacity);
    }

    /**
     * Get occupancy rate
     */
    public function getOccupancyRate(): float
    {
        return $this->tableRepository->getOccupancyRate();
    }
}
