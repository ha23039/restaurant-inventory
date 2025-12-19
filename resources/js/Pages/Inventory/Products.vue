<template>
    <Head title="Gestión de Productos" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    Gestión de Productos
                </h2>
                <button
                    @click="openCreateSlideOver"
                    class="inline-flex items-center bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Producto
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtros con búsqueda en tiempo real -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Búsqueda en tiempo real -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input
                                        v-model="form.search"
                                        @input="searchProducts"
                                        type="text"
                                        placeholder="Busca por nombre... ej: hambu"
                                        class="w-full pl-10 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    />
                                    <!-- Indicador de búsqueda -->
                                    <div v-if="searching" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <!-- Sugerencias de búsqueda -->
                                <div v-if="form.search && form.search.length >= 2" class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                                    Sugerencias: "pan", "carne", "soda", "queso"
                                </div>
                            </div>

                            <!-- Categoría con contador -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoría</label>
                                <select
                                    v-model="form.category_id"
                                    @change="search"
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">Todas las categorías</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }} ({{ category.products_count || 0 }})
                                    </option>
                                </select>
                            </div>

                            <!-- Filtros especiales con badges -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filtros Rápidos</label>
                                <div class="space-y-2">
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            v-model="form.low_stock"
                                            @change="search"
                                            type="checkbox"
                                            class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50"
                                        />
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">
                                            Stock bajo
                                            <span v-if="lowStockCount > 0" class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                                {{ lowStockCount }}
                                            </span>
                                        </span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            v-model="form.expired"
                                            @change="search"
                                            type="checkbox"
                                            class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50"
                                        />
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">
                                            Vencidos
                                            <span v-if="expiredCount > 0" class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                                {{ expiredCount }}
                                            </span>
                                        </span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            v-model="form.expiring_soon"
                                            @change="search"
                                            type="checkbox"
                                            class="rounded border-gray-300 dark:border-gray-600 text-yellow-600 shadow-sm focus:border-yellow-300 focus:ring focus:ring-yellow-200 focus:ring-opacity-50"
                                        />
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">
                                            Vencen pronto
                                            <span v-if="expiringSoonCount > 0" class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                                                {{ expiringSoonCount }}
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Acciones y resultados -->
                            <div class="flex flex-col justify-between">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Acciones</label>
                                    <button
                                        @click="clearFilters"
                                        class="w-full bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors"
                                    >
                                        🗑️ Limpiar Filtros
                                    </button>
                                </div>
                                <!-- Contador de resultados -->
                                <div class="mt-2 text-xs text-gray-500 text-center">
                                    {{ products.total }} resultados
                                </div>
                            </div>
                        </div>

                        <!-- Filtros activos -->
                        <div v-if="hasActiveFilters" class="mt-4 flex flex-wrap gap-2">
                            <span class="text-sm text-gray-600 dark:text-gray-300">Filtros activos:</span>
                            <span v-if="form.search" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                 "{{ form.search }}"
                                <button @click="form.search = ''; search()" class="ml-1 text-blue-600 hover:text-blue-800">×</button>
                            </span>
                            <span v-if="form.category_id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200">
                                {{ getCategoryName(form.category_id) }}
                                <button @click="form.category_id = ''; search()" class="ml-1 text-purple-600 hover:text-purple-800">×</button>
                            </span>
                            <span v-if="form.low_stock" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                Stock bajo
                                <button @click="form.low_stock = false; search()" class="ml-1 text-red-600 hover:text-red-800">×</button>
                            </span>
                            <span v-if="form.expired" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                Vencidos
                                <button @click="form.expired = false; search()" class="ml-1 text-red-600 hover:text-red-800">×</button>
                            </span>
                            <span v-if="form.expiring_soon" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                                Vencen pronto
                                <button @click="form.expiring_soon = false; search()" class="ml-1 text-yellow-600 hover:text-yellow-800">×</button>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tabla de productos -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Mensaje de carga -->
                        <div v-if="searching" class="text-center py-8">
                            <div class="inline-flex items-center">
                                <svg class="animate-spin h-5 w-5 text-blue-500 mr-3" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300">Buscando productos...</span>
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Producto
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Categoría
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Stock Actual
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Stock Mínimo
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Costo Unitario
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Estado
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        <!-- Highlight de búsqueda -->
                                                        <span v-html="highlightSearch(product.name)"></span>
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-300">
                                                        {{ product.unit_type }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :style="{ backgroundColor: product.category.color + '20', color: product.category.color }"
                                            >
                                                {{ product.category.name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white font-semibold">{{ formatNumber(product.current_stock) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">{{ formatNumber(product.min_stock) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white font-semibold">${{ formatNumber(product.unit_cost) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                v-if="parseFloat(product.current_stock) <= parseFloat(product.min_stock)"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200"
                                            >
                                                Stock Bajo
                                            </span>
                                            <span
                                                v-else-if="parseFloat(product.current_stock) <= parseFloat(product.min_stock) * 1.5"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200"
                                            >
                                                Stock Medio
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200"
                                            >
                                                Stock OK
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center justify-center space-x-2">
                                                <Link
                                                    :href="route('inventory.products.show', product.id)"
                                                    class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                                    title="Ver Detalles"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </Link>
                                                <button
                                                    @click="openEditSlideOver(product)"
                                                    class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                                    title="Editar"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button
                                                    @click="deleteProduct(product)"
                                                    class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                    title="Eliminar"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Mensaje si no hay resultados -->
                            <div v-if="products.data.length === 0" class="text-center py-12">
                                <div class="text-gray-500 text-lg">
                                    <div class="text-4xl mb-4"></div>
                                    <div v-if="hasActiveFilters">
                                        No se encontraron productos con los filtros aplicados
                                    </div>
                                    <div v-else>
                                        No hay productos registrados
                                    </div>
                                </div>
                                <Link
                                    v-if="!hasActiveFilters"
                                    :href="route('inventory.products.create')"
                                    class="mt-4 inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-700 text-white font-bold rounded"
                                >
                                    Crear primer producto
                                </Link>
                            </div>
                        </div>

                        <!-- Paginación -->
                        <div class="mt-6" v-if="products.links && products.links.length > 3 && !searching">
                            <nav class="flex items-center justify-between">
                                <div class="flex justify-between flex-1 sm:hidden">
                                    <Link
                                        v-if="products.prev_page_url"
                                        :href="products.prev_page_url"
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:text-gray-400"
                                    >
                                        Anterior
                                    </Link>
                                    <Link
                                        v-if="products.next_page_url"
                                        :href="products.next_page_url"
                                        class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:text-gray-400"
                                    >
                                        Siguiente
                                    </Link>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Mostrando
                                            <span class="font-medium">{{ products.from }}</span>
                                            a
                                            <span class="font-medium">{{ products.to }}</span>
                                            de
                                            <span class="font-medium">{{ products.total }}</span>
                                            resultados
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                            <template v-for="(link, index) in products.links" :key="index">
                                                <Link
                                                    v-if="link.url"
                                                    :href="link.url"
                                                    v-html="link.label"
                                                    class="relative inline-flex items-center px-2 py-2 text-sm font-medium transition-colors"
                                                    :class="[
                                                        link.active
                                                            ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                                        index === 0 ? 'rounded-l-md' : '',
                                                        index === products.links.length - 1 ? 'rounded-r-md' : '',
                                                        'border'
                                                    ]"
                                                />
                                                <span
                                                    v-else
                                                    v-html="link.label"
                                                    class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300 cursor-default"
                                                />
                                            </template>
                                        </nav>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product SlideOver -->
        <ProductSlideOver
            :show="showProductSlideOver"
            :product="editingProduct"
            :categories="categories"
            @close="closeProductSlideOver"
        />
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ProductSlideOver from '@/Components/ProductSlideOver.vue';
import { debounce } from 'lodash-es';

// Props
const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object
});

// State
const searching = ref(false);
const lowStockCount = ref(0);
const expiredCount = ref(0);
const expiringSoonCount = ref(0);
const showProductSlideOver = ref(false);
const editingProduct = ref(null);

// Reactive form para filtros
const form = reactive({
    search: props.filters.search || '',
    category_id: props.filters.category_id || '',
    low_stock: props.filters.low_stock || false,
    expired: props.filters.expired || false,
    expiring_soon: props.filters.expiring_soon || false
});

// Computed
const hasActiveFilters = computed(() => {
    return form.search || form.category_id || form.low_stock || form.expired || form.expiring_soon;
});

// Función de búsqueda con debounce para búsqueda en tiempo real
const searchProducts = debounce(() => {
    search();
}, 300); // 300ms de delay

// Función de búsqueda principal
const search = () => {
    searching.value = true;
    router.get(route('inventory.products.index'), form, {
        preserveState: true,
        replace: true,
        onFinish: () => {
            searching.value = false;
        }
    });
};

// Limpiar filtros
const clearFilters = () => {
    form.search = '';
    form.category_id = '';
    form.low_stock = false;
    form.expired = false;
    form.expiring_soon = false;
    search();
};

// SlideOver functions
const openEditSlideOver = (product) => {
    editingProduct.value = product;
    showProductSlideOver.value = true;
};

const openCreateSlideOver = () => {
    editingProduct.value = null;
    showProductSlideOver.value = true;
};

const closeProductSlideOver = () => {
    showProductSlideOver.value = false;
    setTimeout(() => {
        editingProduct.value = null;
    }, 300);
};

// Eliminar producto
const deleteProduct = (product) => {
    if (confirm(`¿Estás seguro de eliminar "${product.name}"?`)) {
        router.delete(route('inventory.products.destroy', product.id));
    }
    closeDropdown();
};

// Formatear números - eliminar decimales innecesarios
const formatNumber = (number) => {
    if (number === null || number === undefined) return '0';
    
    const num = parseFloat(number);
    
    // Si es un número entero, mostrarlo sin decimales
    if (num % 1 === 0) {
        return num.toString();
    }
    
    // Si tiene decimales, mostrar máximo 2 decimales y eliminar ceros finales
    return num.toFixed(2).replace(/\.?0+$/, '');
};

// Highlight de texto en búsqueda
const highlightSearch = (text) => {
    if (!form.search || form.search.length < 2) return text;
    
    const regex = new RegExp(`(${form.search})`, 'gi');
    return text.replace(regex, '<mark class="bg-yellow-200 px-1 rounded">$1</mark>');
};

// Obtener nombre de categoría
const getCategoryName = (categoryId) => {
    const category = props.categories.find(c => c.id == categoryId);
    return category ? category.name : '';
};

// Cargar contadores al montar
onMounted(async () => {
    try {
        const response = await fetch(route('inventory.alerts'));
        const data = await response.json();
        lowStockCount.value = data.low_stock.length;
        expiredCount.value = data.expired.length;
        expiringSoonCount.value = data.expiring_soon.length;
    } catch (error) {
        console.log('Error cargando alertas:', error);
    }
});
</script>
