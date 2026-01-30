<?php

namespace App\Repositories\Contracts;

use App\Models\Combo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ComboRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get available combos
     */
    public function getAvailable(): Collection;

    /**
     * Get combos for digital menu
     */
    public function getForMenu(): Collection;

    /**
     * Get combos for POS
     */
    public function getForPos(): Collection;

    /**
     * Get combos with components loaded
     */
    public function getWithComponents(int $id): ?Combo;

    /**
     * Get paginated combos with filters
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Search combos by name or description
     */
    public function search(string $query): Collection;

    /**
     * Duplicate a combo
     */
    public function duplicate(int $id): ?Combo;

    /**
     * Toggle availability
     */
    public function toggleAvailability(int $id): bool;

    /**
     * Get combos by category
     */
    public function getByCategory(string $category): Collection;
}
