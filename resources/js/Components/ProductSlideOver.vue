<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import SlideOver from './SlideOver.vue';
import { useToast } from '@/composables';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    product: {
        type: Object,
        default: null
    },
    categories: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close']);

const toast = useToast();

// Form data
const form = ref({
    name: '',
    category_id: '',
    description: '',
    unit_type: '',
    current_stock: 0,
    min_stock: 0,
    max_stock: '',
    unit_cost: 0,
    expiry_date: '',
    is_active: true,
});

const initialFormState = ref(null);
const hasChanges = ref(false);
const isSubmitting = ref(false);

// Watch form changes
watch(() => form.value, () => {
    if (initialFormState.value) {
        hasChanges.value = JSON.stringify(form.value) !== JSON.stringify(initialFormState.value);
    }
}, { deep: true });

// Reset form when closed or load existing product
watch(() => props.show, (newVal) => {
    if (!newVal) {
        setTimeout(() => {
            resetForm();
        }, 300);
    } else if (newVal) {
        if (props.product) {
            loadProductData();
        } else {
            resetForm();
        }
        setTimeout(() => {
            initialFormState.value = JSON.parse(JSON.stringify(form.value));
        }, 100);
    }
});

// Watch product changes
watch(() => props.product, () => {
    if (props.show && props.product) {
        loadProductData();
    }
}, { deep: true });

const loadProductData = () => {
    if (props.product) {
        form.value = {
            name: props.product.name || '',
            category_id: props.product.category_id || '',
            description: props.product.description || '',
            unit_type: props.product.unit_type || '',
            current_stock: props.product.current_stock || 0,
            min_stock: props.product.min_stock || 0,
            max_stock: props.product.max_stock || '',
            unit_cost: props.product.unit_cost || 0,
            expiry_date: props.product.expiry_date || '',
            is_active: props.product.is_active ?? true,
        };
    }
};

const resetForm = () => {
    form.value = {
        name: '',
        category_id: '',
        description: '',
        unit_type: '',
        current_stock: 0,
        min_stock: 0,
        max_stock: '',
        unit_cost: 0,
        expiry_date: '',
        is_active: true,
    };
    initialFormState.value = null;
    hasChanges.value = false;
    isSubmitting.value = false;
};

const isEditMode = computed(() => {
    return props.product !== null;
});

const isStockLow = computed(() => {
    return form.value.current_stock && form.value.min_stock &&
           parseFloat(form.value.current_stock) <= parseFloat(form.value.min_stock);
});

const handleClose = () => {
    if (hasChanges.value && !isSubmitting.value) {
        if (confirm('Aún no has guardado los cambios que realizaste, ¿Estás seguro que deseas salir?')) {
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
    if (!form.value.name) {
        alert('Ingresa el nombre del producto');
        return;
    }

    if (!form.value.category_id) {
        alert('Selecciona una categoría');
        return;
    }

    if (!form.value.unit_type) {
        alert('Selecciona la unidad de medida');
        return;
    }

    if (!form.value.current_stock || parseFloat(form.value.current_stock) < 0) {
        alert('Ingresa un stock actual válido');
        return;
    }

    if (!form.value.min_stock || parseFloat(form.value.min_stock) < 0) {
        alert('Ingresa un stock mínimo válido');
        return;
    }

    if (!form.value.unit_cost || parseFloat(form.value.unit_cost) <= 0) {
        alert('Ingresa un costo unitario válido');
        return;
    }

    isSubmitting.value = true;

    const routeName = isEditMode.value ? 'inventory.products.update' : 'inventory.products.store';
    const routeParams = isEditMode.value ? props.product.id : undefined;
    const method = isEditMode.value ? 'put' : 'post';

    router[method](route(routeName, routeParams), form.value, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(isEditMode.value ? 'Producto actualizado exitosamente' : 'Producto creado exitosamente');
            emit('close');
        },
        onError: (errors) => {
            console.error(errors);
            toast.error('Error al guardar el producto');
            isSubmitting.value = false;
        }
    });
};
</script>

<template>
    <SlideOver
        :show="show"
        :prevent-close="hasChanges && !isSubmitting"
        @close="handleClose"
        @backdrop-click="handleBackdropClick"
        :title="isEditMode ? 'Editar Producto' : 'Nuevo Producto'"
        :subtitle="isEditMode ? 'Modifica la información del producto' : 'Registra un nuevo producto en el inventario'"
        size="md"
    >
        <div class="space-y-6">
            <!-- Nombre y Categoría -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nombre del Producto <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                        placeholder="Ej: Tomate"
                        required
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Categoría <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.category_id"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                        required
                    >
                        <option value="">Seleccionar categoría</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Descripción -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Descripción (Opcional)
                </label>
                <textarea
                    v-model="form.description"
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                    placeholder="Descripción del producto..."
                ></textarea>
            </div>

            <!-- Unidad de medida -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Unidad de Medida <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="form.unit_type"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                    required
                >
                    <option value="">Seleccionar</option>
                    <option value="kg">Kilogramos (kg)</option>
                    <option value="g">Gramos (g)</option>
                    <option value="lt">Litros (lt)</option>
                    <option value="ml">Mililitros (ml)</option>
                    <option value="pcs">Piezas (pcs)</option>
                    <option value="box">Cajas (box)</option>
                    <option value="pack">Paquetes (pack)</option>
                </select>
            </div>

            <!-- Stock Actual y Mínimo -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Stock Actual <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.current_stock"
                        type="number"
                        step="0.001"
                        min="0"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                        required
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Stock Mínimo <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.min_stock"
                        type="number"
                        step="0.001"
                        min="0"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                        required
                    />
                </div>
            </div>

            <!-- Costo Unitario y Stock Máximo -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Costo Unitario <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                        <input
                            v-model="form.unit_cost"
                            type="number"
                            step="0.01"
                            min="0"
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                            required
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Stock Máximo (Opcional)
                    </label>
                    <input
                        v-model="form.max_stock"
                        type="number"
                        step="0.001"
                        min="0"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                    />
                </div>
            </div>

            <!-- Fecha de Vencimiento -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Fecha de Vencimiento (Opcional)
                </label>
                <input
                    v-model="form.expiry_date"
                    type="date"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                />
            </div>

            <!-- Estado Activo (solo edición) -->
            <div v-if="isEditMode" class="flex items-center">
                <input
                    v-model="form.is_active"
                    type="checkbox"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    id="is_active"
                />
                <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Producto activo
                </label>
            </div>

            <!-- Alerta de Stock Bajo -->
            <div v-if="isStockLow" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-yellow-900 dark:text-yellow-100">
                            Alerta de Stock Bajo
                        </p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                            El stock actual ({{ form.current_stock }}) es igual o menor al stock mínimo ({{ form.min_stock }}).
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
                    :disabled="isSubmitting"
                    class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50"
                >
                    Cancelar
                </button>
                <button
                    @click="handleSubmit"
                    type="button"
                    :disabled="isSubmitting"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="isSubmitting">Guardando...</span>
                    <span v-else>{{ isEditMode ? 'Actualizar Producto' : 'Crear Producto' }}</span>
                </button>
            </div>
        </template>
    </SlideOver>
</template>
