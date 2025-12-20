<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import SlideOver from './SlideOver.vue';
import ProductSelectorGrid from './ProductSelectorGrid.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    product: {
        type: Object,
        default: null
    },
    products: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close']);

// Form state
const form = ref({
    product_id: '',
    name: '',
    description: '',
    sale_price: '',
    cost_per_unit: '',
    category: '',
    is_available: true,
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

// Watch for product changes (edit mode)
watch(() => props.product, (newProduct) => {
    if (newProduct) {
        form.value = {
            product_id: newProduct.product_id || '',
            name: newProduct.name || '',
            description: newProduct.description || '',
            sale_price: newProduct.sale_price || '',
            cost_per_unit: newProduct.cost_per_unit || '',
            category: newProduct.category || '',
            is_available: newProduct.is_available !== undefined ? newProduct.is_available : true,
        };
        selectedProduct.value = props.products.find(p => p.id === newProduct.product_id);
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

    // Auto-set category based on product
    if (selectedProduct.value && !props.product) {
        form.value.category = selectedProduct.value.category?.name || '';
    }
});

// Reset form when closed
watch(() => props.show, (newVal) => {
    if (!newVal) {
        setTimeout(() => {
            resetForm();
        }, 300);
    } else if (newVal && !props.product) {
        // Modo crear - resetear primero, luego guardar estado inicial
        resetForm();
        setTimeout(() => {
            initialFormState.value = JSON.parse(JSON.stringify(form.value));
        }, 100);
    }
});

const isEditMode = computed(() => props.product !== null);

// Calculate available quantity based on form values
const calculatedAvailability = computed(() => {
    if (!selectedProduct.value || !form.value.cost_per_unit) {
        return 0;
    }
    const currentStock = parseFloat(selectedProduct.value.current_stock || 0);
    const costPerUnit = parseFloat(form.value.cost_per_unit || 0);
    return costPerUnit > 0 ? Math.floor(currentStock / costPerUnit) : 0;
});

const resetForm = () => {
    form.value = {
        product_id: '',
        name: '',
        description: '',
        sale_price: '',
        cost_per_unit: '',
        category: '',
        is_available: true,
    };
    selectedProduct.value = null;
    initialFormState.value = null;
    hasChanges.value = false;
};

const handleClose = () => {
    if (hasChanges.value) {
        if (confirm('¿Deseas descartar los cambios?')) {
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
        alert('Selecciona un producto del inventario');
        return;
    }

    if (!form.value.name || form.value.name.trim() === '') {
        alert('Ingresa el nombre del producto vendible');
        return;
    }

    if (!form.value.sale_price || parseFloat(form.value.sale_price) <= 0) {
        alert('El precio de venta debe ser mayor a 0');
        return;
    }

    if (!form.value.cost_per_unit || parseFloat(form.value.cost_per_unit) <= 0) {
        alert('El costo por unidad debe ser mayor a 0');
        return;
    }

    processing.value = true;

    const url = isEditMode.value
        ? route('simple-products.update', props.product.id)
        : route('simple-products.store');

    const method = isEditMode.value ? 'put' : 'post';

    const data = {
        product_id: parseInt(form.value.product_id),
        name: form.value.name.trim(),
        description: form.value.description?.trim() || null,
        sale_price: parseFloat(form.value.sale_price),
        cost_per_unit: parseFloat(form.value.cost_per_unit),
        category: form.value.category?.trim() || null,
        is_available: form.value.is_available,
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
        :title="isEditMode ? 'Editar Producto Simple' : 'Nuevo Producto Simple'"
        :subtitle="isEditMode ? 'Modifica los detalles del producto vendible' : 'Crea un producto vendible vinculado al inventario'"
        size="lg"
    >
        <div class="space-y-6">
            <!-- Toggle Disponibilidad -->
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="form.is_available ? 'bg-green-100 dark:bg-green-900' : 'bg-gray-200 dark:bg-gray-700'">
                        <svg v-if="form.is_available" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            Disponible en POS
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ form.is_available ? 'Este producto aparece en el punto de venta' : 'Este producto está oculto del POS' }}
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="form.is_available = !form.is_available"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    :class="form.is_available ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                >
                    <span
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                        :class="form.is_available ? 'translate-x-5' : 'translate-x-0'"
                    />
                </button>
            </div>

            <!-- Selección de Producto Base -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Producto del Inventario <span class="text-red-500">*</span>
                    <span v-if="isEditMode" class="text-xs font-normal text-gray-500 ml-2">(puedes cambiarlo)</span>
                </label>
                <ProductSelectorGrid
                    :products="products"
                    v-model="form.product_id"
                    :selected-product="selectedProduct"
                    @select="(p) => selectedProduct = p"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Este es el producto de inventario que se deducirá al vender
                </p>
            </div>

            <!-- Nombre del Producto Vendible -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nombre del Producto Vendible <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                    placeholder="Ej: Coca Cola 355ml, Agua 500ml"
                    required
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    El nombre con el que se mostrará en el punto de venta
                </p>
            </div>

            <!-- Descripción -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Descripción
                </label>
                <textarea
                    v-model="form.description"
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                    placeholder="Descripción opcional del producto..."
                ></textarea>
            </div>

            <!-- Precio de Venta -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Precio de Venta <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">$</span>
                    <input
                        v-model="form.sale_price"
                        type="number"
                        step="0.01"
                        min="0.01"
                        class="w-full pl-8 pr-4 py-2 text-lg border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                        placeholder="0.00"
                        required
                    />
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Precio al que se venderá al cliente
                </p>
            </div>

            <!-- Costo por Unidad (Consumo) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Consumo de Inventario por Unidad <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center space-x-2">
                    <input
                        v-model="form.cost_per_unit"
                        type="number"
                        step="0.001"
                        min="0.001"
                        class="flex-1 px-4 py-2 text-lg border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                        placeholder="0.000"
                        required
                    />
                    <span class="text-sm text-gray-600 dark:text-gray-400 font-medium min-w-[50px]">
                        {{ selectedProduct?.unit_type || product?.product?.unit_type || 'unidad' }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Cantidad de inventario que se deduce por cada unidad vendida
                </p>
            </div>

            <!-- Categoría -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Categoría (opcional)
                </label>
                <input
                    v-model="form.category"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                    placeholder="Ej: Bebidas, Snacks"
                />
            </div>

            <!-- Preview de disponibilidad (si hay suficientes datos) -->
            <div v-if="calculatedAvailability > 0" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-green-900 dark:text-green-100">
                            Unidades disponibles para venta
                        </p>
                        <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                            Con el stock actual ({{ formatQuantity(selectedProduct?.current_stock || product?.product?.current_stock || 0) }} {{ selectedProduct?.unit_type || product?.product?.unit_type }})
                            se pueden vender
                            <strong>{{ calculatedAvailability }} unidades</strong>
                        </p>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                            Cada venta deducirá {{ formatQuantity(form.cost_per_unit) }} {{ selectedProduct?.unit_type || product?.product?.unit_type }} del inventario
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
                            Deducción automática de inventario
                        </p>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            Cada vez que se venda este producto, se deducirá automáticamente la cantidad especificada del inventario base.
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
                    class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 rounded-lg transition-colors flex items-center"
                >
                    <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ processing ? 'Guardando...' : (isEditMode ? 'Actualizar' : 'Crear Producto') }}
                </button>
            </div>
        </template>
    </SlideOver>
</template>
