<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import SlideOver from './SlideOver.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    selectedProducts: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'save']);

// State
const products = ref([]);
const searchQuery = ref('');
const selectedProduct = ref(null);
const quantity = ref('');
const costPrice = ref('');
const isLoading = ref(false);
const tempSelectedProducts = ref([]);

// Load products when opened
watch(() => props.show, (newVal) => {
    if (newVal) {
        loadProducts();
        tempSelectedProducts.value = [...props.selectedProducts];
    }
});

const loadProducts = async () => {
    isLoading.value = true;
    try {
        // Usando Inertia's router para hacer una petición
        const response = await fetch(route('api.products.index'));
        const data = await response.json();
        products.value = data;
    } catch (error) {
        console.error('Error loading products:', error);
    } finally {
        isLoading.value = false;
    }
};

const filteredProducts = computed(() => {
    if (!searchQuery.value) return products.value;

    const query = searchQuery.value.toLowerCase();
    return products.value.filter(p =>
        p.name.toLowerCase().includes(query) ||
        (p.code && p.code.toLowerCase().includes(query))
    );
});

// Computed Set para búsqueda O(1) ultra rápida
const selectedProductIds = computed(() => {
    return new Set(tempSelectedProducts.value.map(p => p.product_id));
});

const isProductSelected = (productId) => {
    return selectedProductIds.value.has(productId); // O(1) - SÚPER RÁPIDO
};

const handleProductClick = (product) => {
    if (isProductSelected(product.id)) {
        // Si ya está seleccionado, lo removemos
        tempSelectedProducts.value = tempSelectedProducts.value.filter(p => p.product_id !== product.id);
        if (selectedProduct.value?.id === product.id) {
            selectedProduct.value = null;
            quantity.value = '';
            costPrice.value = '';
        }
    } else {
        // Seleccionamos el producto para editar
        selectedProduct.value = product;
        quantity.value = '';
        costPrice.value = product.unit_cost || '';
    }
};

const addProduct = () => {
    if (!selectedProduct.value) {
        alert('Selecciona un producto');
        return;
    }

    if (!quantity.value || parseFloat(quantity.value) <= 0) {
        alert('Ingresa una cantidad válida');
        return;
    }

    if (!costPrice.value || parseFloat(costPrice.value) <= 0) {
        alert('Ingresa un precio de costo válido');
        return;
    }

    const productData = {
        product_id: selectedProduct.value.id,
        name: selectedProduct.value.name,
        unit: selectedProduct.value.unit_type,
        quantity: parseFloat(quantity.value),
        cost_price: parseFloat(costPrice.value)
    };

    // Verificar si ya existe
    const existingIndex = tempSelectedProducts.value.findIndex(p => p.product_id === selectedProduct.value.id);

    if (existingIndex >= 0) {
        // Actualizar existente
        tempSelectedProducts.value[existingIndex] = productData;
    } else {
        // Agregar nuevo
        tempSelectedProducts.value.push(productData);
    }

    // Limpiar selección
    selectedProduct.value = null;
    quantity.value = '';
    costPrice.value = '';
    searchQuery.value = '';
};

const removeProduct = (productId) => {
    tempSelectedProducts.value = tempSelectedProducts.value.filter(p => p.product_id !== productId);
};

const handleSave = () => {
    if (tempSelectedProducts.value.length === 0) {
        alert('Agrega al menos un producto');
        return;
    }
    emit('save', tempSelectedProducts.value);
};

const handleClose = () => {
    if (tempSelectedProducts.value.length > 0 && JSON.stringify(tempSelectedProducts.value) !== JSON.stringify(props.selectedProducts)) {
        if (confirm('¿Deseas guardar los cambios antes de cerrar?')) {
            handleSave();
        } else {
            emit('close');
        }
    } else {
        emit('close');
    }
};

const totalAmount = computed(() => {
    return tempSelectedProducts.value.reduce((sum, p) => sum + (p.quantity * p.cost_price), 0);
});
</script>

<template>
    <SlideOver
        :show="show"
        @close="handleClose"
        title="Seleccionar Productos"
        subtitle="Agrega productos e insumos a la compra"
        size="lg"
    >
        <div class="space-y-6">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Buscar Producto
                </label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                        placeholder="Buscar por nombre o código..."
                    />
                </div>
            </div>

            <!-- Product Grid -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    Productos Disponibles
                </label>

                <div v-if="isLoading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="text-sm text-gray-500 mt-2">Cargando productos...</p>
                </div>

                <div v-else-if="filteredProducts.length === 0" class="text-center py-8">
                    <p class="text-gray-500">No se encontraron productos</p>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-80 overflow-y-auto">
                    <button
                        v-for="product in filteredProducts"
                        :key="product.id"
                        @click="handleProductClick(product)"
                        type="button"
                        :class="[
                            'text-left p-4 border-2 rounded-lg transition-all',
                            selectedProduct?.id === product.id
                                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                : isProductSelected(product.id)
                                ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                        ]"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">{{ product.name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Stock: {{ product.current_stock }} {{ product.unit_type }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Costo: ${{ parseFloat(product.unit_cost || 0).toFixed(2) }}
                                </p>
                            </div>
                            <div v-if="isProductSelected(product.id)" class="flex-shrink-0">
                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Product Details (when selected) -->
            <div v-if="selectedProduct" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100">
                        {{ selectedProduct.name }}
                    </h3>
                    <button
                        @click="selectedProduct = null; quantity = ''; costPrice = ''"
                        type="button"
                        class="p-1 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">
                            Cantidad <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="quantity"
                            type="number"
                            step="0.001"
                            min="0"
                            class="w-full px-3 py-2 border border-blue-300 dark:border-blue-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                            :placeholder="'En ' + selectedProduct.unit_type"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">
                            Precio de Costo <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                            <input
                                v-model="costPrice"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full pl-8 pr-3 py-2 border border-blue-300 dark:border-blue-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                                placeholder="0.00"
                            />
                        </div>
                    </div>
                </div>

                <button
                    @click="addProduct"
                    type="button"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                >
                    Agregar Producto
                </button>
            </div>

            <!-- Selected Products Summary -->
            <div v-if="tempSelectedProducts.length > 0">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    Productos Seleccionados ({{ tempSelectedProducts.length }})
                </label>

                <div class="border border-gray-200 dark:border-gray-700 rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="product in tempSelectedProducts"
                        :key="product.product_id"
                        class="p-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800"
                    >
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ product.name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ product.quantity }} {{ product.unit }} × ${{ product.cost_price.toFixed(2) }}
                            </p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                ${{ (product.quantity * product.cost_price).toFixed(2) }}
                            </span>
                            <button
                                @click="removeProduct(product.product_id)"
                                type="button"
                                class="p-1 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div class="mt-4 bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-base font-semibold text-gray-700 dark:text-gray-300">Total</span>
                        <span class="text-xl font-bold text-blue-600 dark:text-blue-400">
                            ${{ totalAmount.toFixed(2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
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
                    @click="handleSave"
                    type="button"
                    :disabled="tempSelectedProducts.length === 0"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Guardar ({{ tempSelectedProducts.length }})
                </button>
            </div>
        </template>
    </SlideOver>
</template>
