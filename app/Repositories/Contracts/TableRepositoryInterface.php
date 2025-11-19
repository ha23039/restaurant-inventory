<?php

namespace App\Repositories\Contracts;

use App\Models\Table;
use Illuminate\Database\Eloquent\Collection;

interface TableRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active tables
     */
    public function getActiveTables(): Collection;

    /**
     * Get available tables only
     */
    public function getAvailableTables(): Collection;

    /**
     * Get occupied tables with current sale information
     */
    public function getOccupiedTables(): Collection;

    /**
     * Get table by table number
     */
    public function getByTableNumber(string $tableNumber): ?Table;

    /**
     * Get tables by status
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get table statistics (available, occupied, reserved, cleaning)
     */
    public function getStatistics(): array;

    /**
     * Mark table as occupied with sale
     */
    public function markAsOccupied(int $tableId, int $saleId): bool;

    /**
     * Mark table as available (clear current sale)
     */
    public function markAsAvailable(int $tableId): bool;

    /**
     * Update table status
     */
    public function updateStatus(int $tableId, string $status): bool;

    /**
     * Get table with current sale details
     */
    public function getWithCurrentSale(int $tableId): ?Table;

    /**
     * Search tables by number or name
     */
    public function search(string $query): Collection;

    /**
     * Get tables by capacity
     */
    public function getByCapacity(int $minCapacity, ?int $maxCapacity = null): Collection;

    /**
     * Get table occupancy rate (percentage)
     */
    public function getOccupancyRate(): float;
}
