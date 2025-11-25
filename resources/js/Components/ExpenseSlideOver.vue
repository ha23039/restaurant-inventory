<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import SlideOver from './SlideOver.vue';
import ProductSelectionSlideOver from './ProductSelectionSlideOver.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    categories: {
        type: Array,
        default: () => []
    },
    suppliers: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close']);

// Form data
const form = ref({
    expense_date: new Date().toISOString().split('T')[0],
    category: '',
    description: '',
    amount: '',
    payment_method: 'efectivo',
    supplier_id: null,
    notes: '',
    products: [] // Para productos/insumos
});

const hasChanges = ref(false);
const showProductSelection = ref(false);
const isSubmitting = ref(false);

// Watch form changes
watch(() => form.value, () => {
    hasChanges.value = true;
}, { deep: true });

// Reset form when closed
watch(() => props.show, (newVal) => {
    if (!newVal) {
        setTimeout(() => {
            resetForm();
        }, 300);
    }
});

const isProductCategory = computed(() => {
    return form.value.category === 'compra_productos_insumos';
});

const totalAmount = computed(() => {
    if (isProductCategory.value && form.value.products.length > 0) {
        return form.value.products.reduce((sum, p) => sum + (p.quantity * p.cost_price), 0);
    }
    return parseFloat(form.value.amount) || 0;
});

const paymentMethods = [
    { value: 'efectivo', label: 'Efectivo' },
    { value: 'tarjeta', label: 'Tarjeta' },
    { value: 'transferencia', label: 'Transferencia' },
    { value: 'cheque', label: 'Cheque' },
];

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

const openProductSelection = () => {
    showProductSelection.value = true;
};

const handleProductsSelected = (products) => {
    form.value.products = products;
    showProductSelection.value = false;
    hasChanges.value = true;
};

const removeProduct = (index) => {
    form.value.products.splice(index, 1);
};

const resetForm = () => {
    form.value = {
        expense_date: new Date().toISOString().split('T')[0],
        category: '',
        description: '',
        amount: '',
        payment_method: 'efectivo',
        supplier_id: null,
        notes: '',
        products: []
    };
    hasChanges.value = false;
    isSubmitting.value = false;
};

const handleSubmit = () => {
    if (!form.value.category) {
        alert('Selecciona una categoría');
        return;
    }

    if (!form.value.description) {
        alert('Ingresa una descripción del gasto');
        return;
    }

    if (isProductCategory.value) {
        if (form.value.products.length === 0) {
            alert('Agrega al menos un producto');
            return;
        }
    } else {
        if (!form.value.amount || parseFloat(form.value.amount) <= 0) {
            alert('Ingresa un monto válido');
            return;
        }
    }

    isSubmitting.value = true;

    const data = {
        ...form.value,
        amount: isProductCategory.value ? totalAmount.value : parseFloat(form.value.amount)
    };

    router.post(route('expenses.store'), data, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
        onError: (errors) => {
            console.error(errors);
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
        title="Nuevo Gasto"
        subtitle="Registra un nuevo gasto en el sistema"
        size="md"
    >
        <div class="space-y-6">
            <!-- Fecha -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Fecha del Gasto
                </label>
                <input
                    v-model="form.expense_date"
                    type="date"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                    required
                />
            </div>

            <!-- Categoría -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Categoría <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="form.category"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                    required
                >
                    <option value="">Selecciona una categoría</option>
                    <option
                        v-for="category in categories"
                        :key="category.value"
                        :value="category.value"
                    >
                        {{ category.label }}
                    </option>
                </select>
            </div>

            <!-- Mensaje si es compra de productos -->
            <div v-if="isProductCategory" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                            Actualización automática de inventario
                        </p>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            Los productos que selecciones se agregarán automáticamente a tu inventario al crear el gasto.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Productos (si es compra de productos) -->
            <div v-if="isProductCategory" class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Productos/Insumos <span class="text-red-500">*</span>
                    </label>
                    <button
                        @click="openProductSelection"
                        type="button"
                        class="flex items-center space-x-1 px-3 py-1.5 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Agregar Productos</span>
                    </button>
                </div>

                <!-- Lista de productos seleccionados -->
                <div v-if="form.products.length > 0" class="border border-gray-200 dark:border-gray-700 rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="(product, index) in form.products"
                        :key="index"
                        class="p-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800"
                    >
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ product.name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ product.quantity }} {{ product.unit }} × ${{ product.cost_price.toFixed(2) }} = ${{ (product.quantity * product.cost_price).toFixed(2) }}
                            </p>
                        </div>
                        <button
                            @click="removeProduct(index)"
                            type="button"
                            class="p-1 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div v-else class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No has agregado productos aún
                    </p>
                </div>
            </div>

            <!-- Monto (si NO es compra de productos) -->
            <div v-if="!isProductCategory">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Monto <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                    <input
                        v-model="form.amount"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full pl-8 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                        placeholder="0.00"
                        required
                    />
                </div>
            </div>

            <!-- Descripción -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Descripción <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.description"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                    placeholder="Ej: Pago de luz del mes de enero"
                    required
                />
            </div>

            <!-- Proveedor -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Proveedor (Opcional)
                </label>
                <select
                    v-model="form.supplier_id"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                >
                    <option :value="null">Sin proveedor</option>
                    <option
                        v-for="supplier in suppliers"
                        :key="supplier.id"
                        :value="supplier.id"
                    >
                        {{ supplier.name }}
                    </option>
                </select>
            </div>

            <!-- Método de Pago -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Método de Pago
                </label>
                <select
                    v-model="form.payment_method"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                >
                    <option
                        v-for="method in paymentMethods"
                        :key="method.value"
                        :value="method.value"
                    >
                        {{ method.label }}
                    </option>
                </select>
            </div>

            <!-- Notas -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Notas (Opcional)
                </label>
                <textarea
                    v-model="form.notes"
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                    placeholder="Notas adicionales sobre este gasto..."
                ></textarea>
            </div>

            <!-- Total (si es compra de productos) -->
            <div v-if="isProductCategory && form.products.length > 0" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <span class="text-lg font-semibold text-gray-700 dark:text-gray-300">Total del Gasto</span>
                    <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        ${{ totalAmount.toFixed(2) }}
                    </span>
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
                    <span v-else>Crear Gasto</span>
                </button>
            </div>
        </template>
    </SlideOver>

    <!-- Product Selection SlideOver (Nested) -->
    <ProductSelectionSlideOver
        :show="showProductSelection"
        :selected-products="form.products"
        @close="showProductSelection = false"
        @save="handleProductsSelected"
    />
</template>
