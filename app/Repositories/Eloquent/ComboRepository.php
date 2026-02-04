<?php

namespace App\Repositories\Eloquent;

use App\Models\Combo;
use App\Models\ComboComponent;
use App\Models\ComboComponentOption;
use App\Repositories\Contracts\ComboRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ComboRepository extends BaseRepository implements ComboRepositoryInterface
{
    public function __construct(Combo $model)
    {
        parent::__construct($model);
    }

    public function getAvailable(): Collection
    {
        return $this->model
            ->available()
            ->ordered()
            ->with(['components.sellable', 'components.options.sellable'])
            ->get();
    }

    public function getForMenu(): Collection
    {
        return $this->model
            ->forMenu()
            ->ordered()
            ->with(['components.sellable.variants', 'components.options.sellable.variants', 'components.defaultVariant'])
            ->get()
            ->filter(fn($combo) => $combo->is_fully_available);
    }

    public function getForPos(): Collection
    {
        return $this->model
            ->forPos()
            ->ordered()
            ->with([
                'components.sellable.variants',
                'components.options.sellable.variants',
                'components.defaultVariant'
            ])
            ->get()
            ->filter(fn($combo) => $combo->is_fully_available);
    }

    public function getWithComponents(int $id): ?Combo
    {
        return $this->model
            ->with([
                'components.sellable.variants',
                'components.options.sellable.variants'
            ])
            ->find($id);
    }

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['components.options.sellable', 'components.sellable'])
            ->withCount('components');

        // Filtro de búsqueda
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro por disponibilidad
        if (isset($filters['available'])) {
            $query->where('is_available', filter_var($filters['available'], FILTER_VALIDATE_BOOLEAN));
        }

        // Filtro por categoría
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        return $query->ordered()->paginate($perPage)->withQueryString();
    }

    public function search(string $query): Collection
    {
        return $this->model
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->available()
            ->ordered()
            ->with(['components.sellable', 'components.options.sellable'])
            ->get();
    }

    public function duplicate(int $id): ?Combo
    {
        $original = $this->getWithComponents($id);

        if (!$original) {
            return null;
        }

        return DB::transaction(function () use ($original) {
            // Duplicar combo
            $newCombo = $original->replicate();
            $newCombo->name = $original->name . ' (copia)';
            $newCombo->is_available = false;
            $newCombo->show_in_menu = false;
            $newCombo->show_in_pos = false;
            $newCombo->image_path = null;
            $newCombo->save();

            // Duplicar componentes
            foreach ($original->components as $component) {
                $newComponent = $component->replicate();
                $newComponent->combo_id = $newCombo->id;
                $newComponent->save();

                // Duplicar opciones
                foreach ($component->options as $option) {
                    $newOption = $option->replicate();
                    $newOption->combo_component_id = $newComponent->id;
                    $newOption->save();
                }
            }

            return $newCombo;
        });
    }

    public function toggleAvailability(int $id): bool
    {
        $combo = $this->find($id);

        if (!$combo) {
            return false;
        }

        return $combo->update(['is_available' => !$combo->is_available]);
    }

    public function getByCategory(string $category): Collection
    {
        return $this->model
            ->where('category', $category)
            ->available()
            ->ordered()
            ->with(['components.sellable', 'components.options.sellable'])
            ->get();
    }

    /**
     * Create combo with components
     */
    public function createWithComponents(array $comboData, array $components): Combo
    {
        return DB::transaction(function () use ($comboData, $components) {
            $combo = $this->model->create($comboData);
            $this->saveComponents($combo, $components);
            return $combo->fresh(['components.options.sellable', 'components.sellable']);
        });
    }

    /**
     * Update combo with components
     */
    public function updateWithComponents(int $id, array $comboData, array $components): ?Combo
    {
        $combo = $this->find($id);

        if (!$combo) {
            return null;
        }

        return DB::transaction(function () use ($combo, $comboData, $components) {
            $combo->update($comboData);

            // Eliminar componentes existentes y recrear
            $combo->components()->delete();
            $this->saveComponents($combo, $components);

            return $combo->fresh(['components.options.sellable', 'components.sellable']);
        });
    }

    /**
     * Save components and options
     */
    protected function saveComponents(Combo $combo, array $components): void
    {
        foreach ($components as $index => $componentData) {
            // Convertir strings vacíos a null para campos polimórficos
            $sellableType = !empty($componentData['sellable_type']) ? $componentData['sellable_type'] : null;
            $sellableId = !empty($componentData['sellable_id']) ? (int) $componentData['sellable_id'] : null;

            // Variante por defecto para componentes fijos
            $defaultVariantId = null;
            if ($componentData['component_type'] === 'fixed' && !empty($componentData['default_variant_id'])) {
                $defaultVariantId = (int) $componentData['default_variant_id'];
            }

            $component = ComboComponent::create([
                'combo_id' => $combo->id,
                'component_type' => $componentData['component_type'],
                'name' => !empty($componentData['name']) ? $componentData['name'] : null,
                'quantity' => $componentData['quantity'] ?? 1,
                'is_required' => filter_var($componentData['is_required'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'sellable_type' => $sellableType,
                'sellable_id' => $sellableId,
                'default_variant_id' => $defaultVariantId,
                'sort_order' => $index,
            ]);

            // Guardar opciones para componentes choice
            if ($componentData['component_type'] === 'choice' && !empty($componentData['options'])) {
                foreach ($componentData['options'] as $optIndex => $optionData) {
                    // Validar que la opción tiene los datos requeridos
                    if (empty($optionData['sellable_type']) || empty($optionData['sellable_id'])) {
                        continue;
                    }

                    ComboComponentOption::create([
                        'combo_component_id' => $component->id,
                        'sellable_type' => $optionData['sellable_type'],
                        'sellable_id' => (int) $optionData['sellable_id'],
                        'price_adjustment' => (float) ($optionData['price_adjustment'] ?? 0),
                        'is_default' => filter_var($optionData['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'sort_order' => $optIndex,
                    ]);
                }
            }
        }
    }
}
