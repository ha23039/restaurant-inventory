<?php

namespace App\Repositories\Eloquent;

use App\Models\Table;
use App\Repositories\Contracts\TableRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TableRepository extends BaseRepository implements TableRepositoryInterface
{
    public function __construct(Table $model)
    {
        parent::__construct($model);
    }

    public function getActiveTables(): Collection
    {
        return $this->model
            ->with(['currentSale'])
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get();
    }

    public function getAvailableTables(): Collection
    {
        return $this->model
            ->where('status', 'disponible')
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get();
    }

    public function getOccupiedTables(): Collection
    {
        return $this->model
            ->where('status', 'ocupada')
            ->with(['currentSale.user', 'currentSale.saleItems'])
            ->orderBy('table_number')
            ->get();
    }

    public function getByTableNumber(string $tableNumber): ?Table
    {
        return $this->model
            ->where('table_number', $tableNumber)
            ->first();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model
            ->with(['currentSale'])
            ->where('status', $status)
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get();
    }

    public function getStatistics(): array
    {
        $total = $this->model->where('is_active', true)->count();
        $available = $this->model->where('status', 'disponible')->where('is_active', true)->count();
        $occupied = $this->model->where('status', 'ocupada')->count();
        $reserved = $this->model->where('status', 'reservada')->count();
        $cleaning = $this->model->where('status', 'en_limpieza')->count();

        return [
            'total' => $total,
            'available' => $available,
            'occupied' => $occupied,
            'reserved' => $reserved,
            'cleaning' => $cleaning,
            'occupancy_rate' => $total > 0 ? round(($occupied / $total) * 100, 2) : 0,
        ];
    }

    public function markAsOccupied(int $tableId, int $saleId): bool
    {
        $table = $this->find($tableId);

        if (!$table) {
            return false;
        }

        return $table->update([
            'status' => 'ocupada',
            'current_sale_id' => $saleId,
            'last_occupied_at' => now(),
        ]);
    }

    public function markAsAvailable(int $tableId): bool
    {
        $table = $this->find($tableId);

        if (!$table) {
            return false;
        }

        return $table->update([
            'status' => 'disponible',
            'current_sale_id' => null,
        ]);
    }

    public function updateStatus(int $tableId, string $status): bool
    {
        $table = $this->find($tableId);

        if (!$table) {
            return false;
        }

        // If changing to disponible, clear current sale
        if ($status === 'disponible') {
            return $table->update([
                'status' => $status,
                'current_sale_id' => null,
            ]);
        }

        return $table->update(['status' => $status]);
    }

    public function getWithCurrentSale(int $tableId): ?Table
    {
        return $this->model
            ->with(['currentSale.user', 'currentSale.saleItems.menuItem', 'currentSale.saleItems.simpleProduct'])
            ->find($tableId);
    }

    public function search(string $query): Collection
    {
        return $this->model
            ->with(['currentSale'])
            ->where(function ($q) use ($query) {
                $q->where('table_number', 'like', "%{$query}%")
                  ->orWhere('name', 'like', "%{$query}%");
            })
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get();
    }

    public function getByCapacity(int $minCapacity, ?int $maxCapacity = null): Collection
    {
        $query = $this->model
            ->where('capacity', '>=', $minCapacity)
            ->where('is_active', true);

        if ($maxCapacity) {
            $query->where('capacity', '<=', $maxCapacity);
        }

        return $query->orderBy('table_number')->get();
    }

    public function getOccupancyRate(): float
    {
        $total = $this->model->where('is_active', true)->count();

        if ($total === 0) {
            return 0.0;
        }

        $occupied = $this->model->where('status', 'ocupada')->count();

        return round(($occupied / $total) * 100, 2);
    }
}
