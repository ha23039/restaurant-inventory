<?php

namespace App\Services;

use App\Models\Combo;
use App\Repositories\Contracts\ComboRepositoryInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ComboService
{
    public function __construct(
        private ComboRepositoryInterface $comboRepository
    ) {
    }

    /**
     * Obtener combos disponibles
     */
    public function getAvailableCombos(): EloquentCollection
    {
        return $this->comboRepository->getAvailable();
    }

    /**
     * Obtener combos para el menú digital
     */
    public function getCombosForMenu(): Collection
    {
        $combos = $this->comboRepository->getForMenu();

        return $combos->map(function ($combo) {
            return $this->formatComboForMenu($combo);
        });
    }

    /**
     * Obtener combos para el POS
     */
    public function getCombosForPos(): Collection
    {
        $combos = $this->comboRepository->getForPos();

        return $combos->map(function ($combo) {
            return $this->formatComboForPos($combo);
        });
    }

    /**
     * Formatear combo para el POS
     */
    protected function formatComboForPos(Combo $combo): array
    {
        return [
            'id' => $combo->id,
            'name' => $combo->name,
            'description' => $combo->description,
            'image_path' => $combo->image_path,
            'base_price' => $combo->base_price,
            'category' => $combo->category,
            'is_available' => $combo->is_available,
            'components' => $combo->components->map(function ($component) {
                $data = [
                    'id' => $component->id,
                    'component_type' => $component->component_type,
                    'name' => $component->name,
                    'quantity' => $component->quantity,
                    'is_required' => $component->is_required,
                    'sellable_type' => $component->sellable_type,
                    'sellable_id' => $component->sellable_id,
                    'sellable' => $component->sellable ? [
                        'id' => $component->sellable->id,
                        'name' => $component->sellable->name,
                        'image_path' => $component->sellable->image_path ?? null,
                        'has_variants' => $component->sellable->has_variants ?? $component->sellable->allows_variants ?? false,
                        'variants' => ($component->sellable->variants ?? collect())->map(fn($v) => [
                            'id' => $v->id,
                            'variant_name' => $v->variant_name ?? $v->name,
                            'name' => $v->variant_name ?? $v->name,
                            'price' => $v->price ?? null,
                            'is_available' => $v->is_available ?? true,
                        ])->values()->toArray(),
                    ] : null,
                    'options' => [],
                ];

                // Si es choice, agregar opciones
                if ($component->component_type === 'choice') {
                    $data['options'] = $component->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'sellable_type' => $option->sellable_type,
                            'sellable_id' => $option->sellable_id,
                            'price_adjustment' => (float) $option->price_adjustment,
                            'is_default' => $option->is_default,
                            'sellable' => $option->sellable ? [
                                'id' => $option->sellable->id,
                                'name' => $option->sellable->name,
                                'image_path' => $option->sellable->image_path ?? null,
                                'has_variants' => $option->sellable->has_variants ?? $option->sellable->allows_variants ?? false,
                                'allows_variants' => $option->sellable->allows_variants ?? false,
                                'variants' => ($option->sellable->variants ?? collect())->map(fn($v) => [
                                    'id' => $v->id,
                                    'variant_name' => $v->variant_name ?? $v->name,
                                    'name' => $v->variant_name ?? $v->name,
                                    'price' => $v->price ?? null,
                                    'is_available' => $v->is_available ?? true,
                                ])->values()->toArray(),
                            ] : null,
                        ];
                    })->toArray();
                }

                return $data;
            })->toArray(),
        ];
    }

    /**
     * Obtener combo con componentes completos
     */
    public function getComboWithComponents(int $id): ?Combo
    {
        return $this->comboRepository->getWithComponents($id);
    }

    /**
     * Obtener combos paginados con filtros
     */
    public function getPaginatedCombos(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $combos = $this->comboRepository->getPaginated($filters, $perPage);

        // Agregar información calculada
        $combos->getCollection()->transform(function ($combo) {
            $combo->fixed_count = $combo->components->where('component_type', 'fixed')->count();
            $combo->choice_count = $combo->components->where('component_type', 'choice')->count();
            return $combo;
        });

        return $combos;
    }

    /**
     * Crear combo con componentes
     */
    public function createCombo(array $data, ?UploadedFile $image = null): Combo
    {
        $comboData = $this->extractComboData($data);
        $components = $data['components'] ?? [];

        $combo = $this->comboRepository->createWithComponents($comboData, $components);

        // Subir imagen si existe
        if ($image) {
            $this->uploadImage($combo, $image);
        }

        return $combo;
    }

    /**
     * Actualizar combo con componentes
     */
    public function updateCombo(int $id, array $data, ?UploadedFile $image = null, bool $removeImage = false): ?Combo
    {
        $combo = $this->comboRepository->find($id);

        if (!$combo) {
            return null;
        }

        $comboData = $this->extractComboData($data);
        $components = $data['components'] ?? [];

        // Manejar imagen
        if ($removeImage && $combo->image_path) {
            $this->deleteImage($combo);
            $comboData['image_path'] = null;
        }

        $updatedCombo = $this->comboRepository->updateWithComponents($id, $comboData, $components);

        if ($image && $updatedCombo) {
            // Eliminar imagen anterior si existe
            if ($combo->image_path) {
                $this->deleteImage($combo);
            }
            $this->uploadImage($updatedCombo, $image);
        }

        return $updatedCombo;
    }

    /**
     * Eliminar combo
     */
    public function deleteCombo(int $id): bool
    {
        $combo = $this->comboRepository->find($id);

        if (!$combo) {
            return false;
        }

        // Eliminar imagen si existe
        if ($combo->image_path) {
            $this->deleteImage($combo);
        }

        return $this->comboRepository->delete($id);
    }

    /**
     * Duplicar combo
     */
    public function duplicateCombo(int $id): ?Combo
    {
        return $this->comboRepository->duplicate($id);
    }

    /**
     * Toggle disponibilidad
     */
    public function toggleAvailability(int $id): array
    {
        $combo = $this->comboRepository->find($id);

        if (!$combo) {
            return ['success' => false, 'message' => 'Combo no encontrado'];
        }

        $result = $this->comboRepository->toggleAvailability($id);
        $combo->refresh();

        return [
            'success' => $result,
            'is_available' => $combo->is_available,
            'message' => $combo->is_available ? 'Combo activado' : 'Combo desactivado',
        ];
    }

    /**
     * Buscar combos
     */
    public function search(string $query): EloquentCollection
    {
        return $this->comboRepository->search($query);
    }

    /**
     * Verificar si un combo puede ser vendido
     */
    public function canBeSold(int $comboId): array
    {
        $combo = $this->comboRepository->getWithComponents($comboId);

        if (!$combo) {
            return [
                'can_sell' => false,
                'reason' => 'Combo no encontrado',
            ];
        }

        if (!$combo->is_available) {
            return [
                'can_sell' => false,
                'reason' => 'Combo no disponible',
            ];
        }

        if (!$combo->is_fully_available) {
            return [
                'can_sell' => false,
                'reason' => 'Algunos productos del combo no están disponibles',
            ];
        }

        return [
            'can_sell' => true,
            'combo' => $combo,
        ];
    }

    /**
     * Calcular precio final con selecciones
     */
    public function calculateFinalPrice(int $comboId, array $selections = []): float
    {
        $combo = $this->comboRepository->find($comboId);

        if (!$combo) {
            return 0;
        }

        return $combo->calculateFinalPrice($selections);
    }

    /**
     * Formatear combo para respuesta API del menú
     */
    public function formatComboForApi(Combo $combo): array
    {
        $combo->load(['components.options.sellable.variants', 'components.sellable.variants']);

        return [
            'id' => $combo->id,
            'name' => $combo->name,
            'description' => $combo->description,
            'image_path' => $combo->image_path,
            'base_price' => $combo->base_price,
            'is_available' => $combo->is_available,
            'components' => $combo->components->map(function ($component) {
                return [
                    'id' => $component->id,
                    'type' => $component->component_type,
                    'name' => $component->name,
                    'quantity' => $component->quantity,
                    'is_required' => $component->is_required,
                    'product' => $component->isFixed() && $component->sellable ? [
                        'id' => $component->sellable->id,
                        'name' => $component->sellable->name,
                        'type' => $component->sellable_type,
                    ] : null,
                    'options' => $component->isChoice() ? $component->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'product_id' => $option->sellable_id,
                            'product_type' => $option->sellable_type,
                            'product_name' => $option->product_name,
                            'product_image' => $option->product_image,
                            'price_adjustment' => $option->price_adjustment,
                            'formatted_adjustment' => $option->formatted_price_adjustment,
                            'is_default' => $option->is_default,
                            'has_variants' => $option->hasVariants(),
                            'variants' => $option->hasVariants() ? $option->getAvailableVariants()->map(fn($v) => [
                                'id' => $v->id,
                                'name' => $v->variant_name ?? $v->name,
                                'price' => $v->price ?? null,
                            ]) : [],
                        ];
                    }) : [],
                ];
            }),
        ];
    }

    /**
     * Formatear combo para el menú digital
     */
    protected function formatComboForMenu(Combo $combo): array
    {
        return [
            'id' => $combo->id,
            'name' => $combo->name,
            'description' => $combo->description,
            'image_path' => $combo->image_path,
            'base_price' => $combo->base_price,
            'category' => $combo->category,
            'components' => $combo->components->map(function ($component) {
                if ($component->isFixed()) {
                    return [
                        'id' => $component->id,
                        'type' => 'fixed',
                        'name' => $component->name ?? $component->sellable->name ?? null,
                        'quantity' => $component->quantity,
                        'product' => $component->sellable ? [
                            'id' => $component->sellable->id,
                            'name' => $component->sellable->name,
                            'type' => $component->sellable_type,
                            'image_path' => $component->sellable->image_path ?? null,
                        ] : null,
                    ];
                }

                return [
                    'id' => $component->id,
                    'type' => 'choice',
                    'name' => $component->name,
                    'quantity' => $component->quantity,
                    'is_required' => $component->is_required,
                    'options' => $component->options
                        ->filter(fn($opt) => $opt->isReallyAvailable())
                        ->map(function ($option) {
                            $variants = [];
                            if ($option->hasVariants()) {
                                $variants = $option->getAvailableVariants()->map(fn($v) => [
                                    'id' => $v->id,
                                    'name' => $v->variant_name ?? $v->name,
                                    'price' => $v->price ?? null,
                                ])->values()->toArray();
                            }

                            return [
                                'id' => $option->id,
                                'product_id' => $option->sellable_id,
                                'product_type' => $option->sellable_type,
                                'product_name' => $option->product_name,
                                'product_image' => $option->product_image,
                                'price_adjustment' => (float) $option->price_adjustment,
                                'formatted_adjustment' => $option->formatted_price_adjustment,
                                'is_default' => $option->is_default,
                                'has_variants' => $option->hasVariants(),
                                'variants' => $variants,
                            ];
                        })->values(),
                ];
            }),
        ];
    }

    /**
     * Extraer datos del combo de la request
     */
    protected function extractComboData(array $data): array
    {
        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'base_price' => $data['base_price'],
            'category' => $data['category'] ?? 'Combos',
            'is_available' => $data['is_available'] ?? true,
            'show_in_menu' => $data['show_in_menu'] ?? true,
            'show_in_pos' => $data['show_in_pos'] ?? true,
        ];
    }

    /**
     * Subir imagen del combo
     */
    protected function uploadImage(Combo $combo, UploadedFile $image): void
    {
        $path = $image->store('combos', 'public');
        $combo->update(['image_path' => '/storage/' . $path]);
    }

    /**
     * Eliminar imagen del combo
     */
    protected function deleteImage(Combo $combo): void
    {
        if ($combo->image_path) {
            $oldPath = str_replace('/storage/', '', $combo->image_path);
            Storage::disk('public')->delete($oldPath);
        }
    }
}
