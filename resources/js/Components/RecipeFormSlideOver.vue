<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import SlideOver from './SlideOver.vue';
import ConfirmDialog from './ConfirmDialog.vue';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    menuItem: {
        type: Object,
        default: null
    },
    recipe: {
        type: Object,
        default: null
    },
    products: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close']);
const { confirm } = useConfirmDialog();

// Form state
const form = ref({
    product_id: '',
    quantity_needed: '',
    unit: '',
});

const initialFormState = ref(null);
const hasChanges = ref(false);
const processing = ref(false);
const selectedProduct = ref(null);

// Watch for changes - comparar con estado inicial
watch(() => form.value, () => {
    if (initialFormState.value) {
        hasChanges.value = JSON.stringify(form.value) !== JSON.stringify(initialFormState.value);
    }
}, { deep: true });

// Watch for recipe changes (edit mode)
watch(() => props.recipe, (newRecipe) => {
    if (newRecipe) {
        form.value = {
            product_id: newRecipe.product_id || '',
            quantity_needed: newRecipe.quantity_needed || '',
            unit: newRecipe.unit || '',
        };
        selectedProduct.value = props.products.find(p => p.id === newRecipe.product_id);
        // Guardar estado inicial
        initialFormState.value = JSON.parse(JSON.stringify(form.value));
        hasChanges.value = false;
    } else if (props.show) {
        // Modo crear - reset form
        resetForm();
    }
}, { immediate: true });

// Watch product selection
watch(() => form.value.product_id, (newProductId) => {
    selectedProduct.value = props.products.find(p => p.id === parseInt(newProductId));

    // Auto-set unit based on product
    if (selectedProduct.value && !props.recipe) {
        form.value.unit = selectedProduct.value.unit_type;
    }
});

// Reset form when closed
watch(() => props.show, (newVal) => {
    if (!newVal) {
        setTimeout(() => {
            resetForm();
        }, 300);
    } else if (newVal && !props.recipe) {
        // Modo crear - resetear primero, luego guardar estado inicial
        resetForm();
        setTimeout(() => {
            initialFormState.value = JSON.parse(JSON.stringify(form.value));
        }, 100);
    }
});

const isEditMode = computed(() => props.recipe !== null);

const resetForm = () => {
    form.value = {
        product_id: '',
        quantity_needed: '',
        unit: '',
    };
    selectedProduct.value = null;
    initialFormState.value = null;
    hasChanges.value = false;
};

const handleClose = async () => {
    if (hasChanges.value) {
        const confirmed = await confirm({
            title: '¿Descartar cambios?',
            message: 'Tienes cambios sin guardar. Si sales ahora, se perderán.',
            confirmText: 'Descartar',
            type: 'warning'
        });
        if (confirmed) {
            emit('close');
        }
    } else {
        emit('close');
    }
};

const handleBackdropClick = () => {
    handleClose();
};

const handleSubmit = () => {
    // Validación básica
    if (!form.value.product_id) {
        alert('Selecciona un ingrediente');
        return;
    }

    if (!form.value.quantity_needed || parseFloat(form.value.quantity_needed) <= 0) {
        alert('La cantidad debe ser mayor a 0');
        return;
    }

    if (!form.value.unit) {
        alert('Selecciona una unidad de medida');
        return;
    }

    processing.value = true;

    const url = isEditMode.value
        ? route('menu.recipes.update', props.recipe.id)
        : route('menu.recipes.store');

    const method = isEditMode.value ? 'put' : 'post';

    const data = isEditMode.value
        ? {
            quantity_needed: parseFloat(form.value.quantity_needed),
            unit: form.value.unit,
        }
        : {
            menu_item_id: props.menuItem.id,
            product_id: parseInt(form.value.product_id),
            quantity_needed: parseFloat(form.value.quantity_needed),
            unit: form.value.unit,
        };

    router[method](url, data, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            resetForm();
        },
        onError: (errors) => {
            console.error('Errores de validación:', errors);
            alert('Error al guardar: ' + (errors.error || errors.message || 'Verifica los datos'));
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

// Filtrar productos que ya están en la receta (solo en modo crear)
const availableProducts = computed(() => {
    if (isEditMode.value || !props.menuItem) {
        return props.products;
    }

    const existingProductIds = props.menuItem.recipes?.map(r => r.product_id) || [];
    return props.products.filter(p => !existingProductIds.includes(p.id));
});

const getUnitOptions = () => {
    return [
        { value: 'kg', label: 'Kilogramos (kg)' },
        { value: 'g', label: 'Gramos (g)' },
        { value: 'lt', label: 'Litros (L)' },
        { value: 'ml', label: 'Mililitros (ml)' },
        { value: 'pcs', label: 'Piezas (pzas)' },
    ];
};

const formatQuantity = (quantity) => {
    return parseFloat(quantity).toFixed(3).replace(/\.?0+$/, '');
};
</script>

<template>
    <SlideOver
        :show="show"
        :prevent-close="hasChanges"
        @close="handleClose"
        @backdrop-click="handleBackdropClick"
        :title="isEditMode ? 'Editar Ingrediente' : `Agregar Ingrediente a ${menuItem?.name || ''}`"
        :subtitle="isEditMode ? 'Modifica la cantidad necesaria' : 'Selecciona un ingrediente del inventario'"
        size="md"
    >
        <div class="space-y-6">
            <!-- Selección de Producto (solo en modo crear) -->
            <div v-if="!isEditMode">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Ingrediente <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="form.product_id"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-800 dark:text-white"
                    required
                >
                    <option value="">Selecciona un ingrediente...</option>
                    <optgroup
                        v-for="category in [...new Set(availableProducts.map(p => p.category?.name || 'Sin categoría'))]"
                        :key="category"
                        :label="category"
                    >
                        <option
                            v-for="product in availableProducts.filter(p => (p.category?.name || 'Sin categoría') === category)"
                            :key="product.id"
                            :value="product.id"
                        >
                            {{ product.name }} (Stock: {{ formatQuantity(product.current_stock) }} {{ product.unit_type }})
                        </option>
                    </optgroup>
                </select>
            </div>

            <!-- Info del producto seleccionado (modo crear) -->
            <div v-if="selectedProduct && !isEditMode" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="text-blue-600 dark:text-blue-400 text-lg">📦</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                            {{ selectedProduct.name }}
                        </p>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            Stock actual: {{ formatQuantity(selectedProduct.current_stock) }} {{ selectedProduct.unit_type }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Info del producto en modo edición -->
            <div v-if="isEditMode && recipe" class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="text-purple-600 dark:text-purple-400 text-lg">📦</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-purple-900 dark:text-purple-100">
                            {{ recipe.product?.name }}
                        </p>
                        <p class="text-sm text-purple-700 dark:text-purple-300 mt-1">
                            Stock actual: {{ formatQuantity(recipe.product?.current_stock || 0) }} {{ recipe.product?.unit_type }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Cantidad Necesaria -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Cantidad Necesaria <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.quantity_needed"
                    type="number"
                    step="0.001"
                    min="0.001"
                    class="w-full px-4 py-2 text-lg border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-800 dark:text-white"
                    placeholder="0.000"
                    required
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Cantidad necesaria para preparar 1 porción de este platillo
                </p>
            </div>

            <!-- Unidad de Medida -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Unidad de Medida <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="form.unit"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-800 dark:text-white"
                    required
                >
                    <option value="">Selecciona una unidad...</option>
                    <option
                        v-for="unit in getUnitOptions()"
                        :key="unit.value"
                        :value="unit.value"
                    >
                        {{ unit.label }}
                    </option>
                </select>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Debe coincidir con la unidad del producto en inventario
                </p>
            </div>

            <!-- Preview de consumo (si hay suficientes datos) -->
            <div v-if="selectedProduct && form.quantity_needed && form.unit" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-green-900 dark:text-green-100">
                            Porciones posibles
                        </p>
                        <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                            Con el stock actual ({{ formatQuantity(selectedProduct.current_stock) }} {{ selectedProduct.unit_type }})
                            se pueden preparar aproximadamente
                            <strong>{{ Math.floor(selectedProduct.current_stock / parseFloat(form.quantity_needed || 1)) }} porciones</strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                            Deducción automática
                        </p>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            Cada vez que se venda este platillo, se deducirá automáticamente la cantidad especificada del inventario.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer con botones -->
        <template #footer>
            <div class="flex items-center justify-end space-x-3">
                <button
                    @click="handleClose"
                    type="button"
                    class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                    Cancelar
                </button>
                <button
                    @click="handleSubmit"
                    :disabled="processing"
                    type="button"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 rounded-lg transition-colors flex items-center"
                >
                    <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ processing ? 'Guardando...' : (isEditMode ? 'Actualizar' : 'Agregar Ingrediente') }}
                </button>
            </div>
        </template>
    </SlideOver>

</template>
