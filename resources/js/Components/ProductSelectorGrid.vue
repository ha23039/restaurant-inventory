<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    },
    modelValue: {
        type: [Number, String],
        default: null
    },
    selectedProduct: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['update:modelValue', 'select']);

const searchQuery = ref('');
const selectedCategory = ref('');

// Get unique categories
const categories = computed(() => {
    const cats = [...new Set(props.products.map(p => p.category?.name || 'Sin categoría'))];
    return cats.sort();
});

// Límite de productos para mejor rendimiento
const PRODUCT_LIMIT = 30;

// Filter products (sin límite para conteo)
const allFilteredProducts = computed(() => {
    let result = props.products;

    // Filter by search
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(p =>
            p.name.toLowerCase().includes(query) ||
            (p.category?.name || '').toLowerCase().includes(query)
        );
    }

    // Filter by category
    if (selectedCategory.value) {
        result = result.filter(p =>
            (p.category?.name || 'Sin categoría') === selectedCategory.value
        );
    }

    return result;
});

// Productos limitados para renderizar
const filteredProducts = computed(() => {
    return allFilteredProducts.value.slice(0, PRODUCT_LIMIT);
});

// Hay más productos?
const hasMoreProducts = computed(() => {
    return allFilteredProducts.value.length > PRODUCT_LIMIT;
});

const totalFilteredCount = computed(() => {
    return allFilteredProducts.value.length;
});

const selectProduct = (product) => {
    emit('update:modelValue', product.id);
    emit('select', product);
};

const isSelected = (product) => {
    return props.modelValue == product.id;
};

const formatStock = (stock) => {
    return parseFloat(stock || 0).toFixed(3).replace(/\.?0+$/, '');
};

const getStockColor = (product) => {
    const stock = parseFloat(product.current_stock || 0);
    const minStock = parseFloat(product.min_stock || 0);

    if (stock <= 0) return 'text-red-600 dark:text-red-400';
    if (stock <= minStock) return 'text-orange-600 dark:text-orange-400';
    return 'text-green-600 dark:text-green-400';
};

const clearSearch = () => {
    searchQuery.value = '';
    selectedCategory.value = '';
};
</script>

<template>
    <div class="space-y-4">
        <!-- Search and Filter Bar -->
        <div class="flex flex-col sm:flex-row gap-3">
            <!-- Search Input -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar producto..."
                    class="w-full pl-10 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white text-sm"
                />
                <button
                    v-if="searchQuery"
                    @click="searchQuery = ''"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Category Filter -->
            <select
                v-model="selectedCategory"
                class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white text-sm min-w-[160px]"
            >
                <option value="">Todas las categorías</option>
                <option v-for="cat in categories" :key="cat" :value="cat">
                    {{ cat }}
                </option>
            </select>
        </div>

        <!-- Results Count -->
        <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
            <div class="flex items-center gap-2">
                <span>{{ totalFilteredCount }} productos</span>
                <span v-if="hasMoreProducts" class="text-xs text-blue-600 dark:text-blue-400">
                    (mostrando {{ filteredProducts.length }} - usa el buscador)
                </span>
            </div>
            <button
                v-if="searchQuery || selectedCategory"
                @click="clearSearch"
                class="text-blue-600 hover:text-blue-700 dark:text-blue-400 flex items-center gap-1"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Limpiar filtros
            </button>
        </div>

        <!-- Products Grid -->
        <div class="max-h-[400px] overflow-y-auto overscroll-contain scrollbar-hide border border-gray-200 dark:border-gray-700 rounded-lg">
            <div v-if="filteredProducts.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="font-medium">No se encontraron productos</p>
                <p class="text-sm mt-1">Intenta con otros términos de búsqueda</p>
            </div>

            <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-2">
                <button
                    v-for="product in filteredProducts"
                    :key="product.id"
                    @click="selectProduct(product)"
                    type="button"
                    class="relative p-3 rounded-lg border-2 text-left transition-all hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="[
                        isSelected(product)
                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 ring-2 ring-blue-200 dark:ring-blue-800'
                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600'
                    ]"
                >
                    <!-- Selected Badge -->
                    <div
                        v-if="isSelected(product)"
                        class="absolute top-1 right-1 w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center"
                    >
                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </div>

                    <!-- Product Name -->
                    <p class="font-medium text-sm text-gray-900 dark:text-white truncate pr-6">
                        {{ product.name }}
                    </p>

                    <!-- Category -->
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                        {{ product.category?.name || 'Sin categoría' }}
                    </p>

                    <!-- Stock Info -->
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs font-medium" :class="getStockColor(product)">
                            {{ formatStock(product.current_stock) }} {{ product.unit_type }}
                        </span>
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            ${{ parseFloat(product.unit_cost || 0).toFixed(2) }}
                        </span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Selected Product Preview -->
        <div
            v-if="selectedProduct"
            class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4"
        >
            <div class="flex items-start space-x-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                        Producto seleccionado
                    </p>
                    <p class="text-sm text-blue-700 dark:text-blue-300 font-semibold mt-1">
                        {{ selectedProduct.name }}
                    </p>
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                        Stock: {{ formatStock(selectedProduct.current_stock) }} {{ selectedProduct.unit_type }}
                        <span v-if="selectedProduct.category" class="ml-2">
                            | {{ selectedProduct.category.name }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
