<template>
    <Head title="Gestión de Productos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    📦 Gestión de Productos
                </h2>
                <Link 
                    :href="route('inventory.products.create')"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    + Nuevo Producto
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtros con búsqueda en tiempo real -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Búsqueda en tiempo real -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
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
                                        class="w-full pl-10 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
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
                                <div v-if="form.search && form.search.length >= 2" class="mt-1 text-xs text-gray-500">
                                    💡 Sugerencias: "pan", "carne", "soda", "queso"
                                </div>
                            </div>

                            <!-- Categoría con contador -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                                <select
                                    v-model="form.category_id"
                                    @change="search"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">Todas las categorías</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }} ({{ category.products_count || 0 }})
                                    </option>
                                </select>
                            </div>

                            <!-- Filtros especiales con badges -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Filtros Rápidos</label>
                                <div class="space-y-2">
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            v-model="form.low_stock"
                                            @change="search"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50"
                                        />
                                        <span class="ml-2 text-sm text-gray-600">
                                            ⚠️ Stock bajo
                                            <span v-if="lowStockCount > 0" class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ lowStockCount }}
                                            </span>
                                        </span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            v-model="form.expired"
                                            @change="search"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50"
                                        />
                                        <span class="ml-2 text-sm text-gray-600">
                                            🗓️ Vencidos
                                            <span v-if="expiredCount > 0" class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ expiredCount }}
                                            </span>
                                        </span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            v-model="form.expiring_soon"
                                            @change="search"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-yellow-600 shadow-sm focus:border-yellow-300 focus:ring focus:ring-yellow-200 focus:ring-opacity-50"
                                        />
                                        <span class="ml-2 text-sm text-gray-600">
                                            ⏰ Vencen pronto
                                            <span v-if="expiringSoonCount > 0" class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                {{ expiringSoonCount }}
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Acciones y resultados -->
                            <div class="flex flex-col justify-between">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Acciones</label>
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
                            <span class="text-sm text-gray-600">Filtros activos:</span>
                            <span v-if="form.search" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                🔍 "{{ form.search }}"
                                <button @click="form.search = ''; search()" class="ml-1 text-blue-600 hover:text-blue-800">×</button>
                            </span>
                            <span v-if="form.category_id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                🏷️ {{ getCategoryName(form.category_id) }}
                                <button @click="form.category_id = ''; search()" class="ml-1 text-purple-600 hover:text-purple-800">×</button>
                            </span>
                            <span v-if="form.low_stock" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                ⚠️ Stock bajo
                                <button @click="form.low_stock = false; search()" class="ml-1 text-red-600 hover:text-red-800">×</button>
                            </span>
                            <span v-if="form.expired" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                🗓️ Vencidos
                                <button @click="form.expired = false; search()" class="ml-1 text-red-600 hover:text-red-800">×</button>
                            </span>
                            <span v-if="form.expiring_soon" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                ⏰ Vencen pronto
                                <button @click="form.expiring_soon = false; search()" class="ml-1 text-yellow-600 hover:text-yellow-800">×</button>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tabla de productos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Mensaje de carga -->
                        <div v-if="searching" class="text-center py-8">
                            <div class="inline-flex items-center">
                                <svg class="animate-spin h-5 w-5 text-blue-500 mr-3" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-gray-600">Buscando productos...</span>
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Producto
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Categoría
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Stock Actual
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Stock Mínimo
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Costo Unitario
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Estado
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <!-- Highlight de búsqueda -->
                                                        <span v-html="highlightSearch(product.name)"></span>
                                                    </div>
                                                    <div class="text-sm text-gray-500">
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
                                            <div class="text-sm text-gray-900 font-semibold">{{ formatNumber(product.current_stock) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ formatNumber(product.min_stock) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 font-semibold">${{ formatNumber(product.unit_cost) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                v-if="product.current_stock <= product.min_stock"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                            >
                                                ⚠️ Stock Bajo
                                            </span>
                                            <span
                                                v-else-if="product.current_stock <= product.min_stock * 1.5"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                                            >
                                                ⚡ Stock Medio
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                            >
                                                ✅ Stock OK
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <Link
                                                    :href="route('inventory.products.show', product.id)"
                                                    class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                                    title="Ver detalles"
                                                >
                                                    👁️
                                                </Link>
                                                <Link
                                                    :href="route('inventory.products.edit', product.id)"
                                                    class="text-green-600 hover:text-green-900 transition-colors"
                                                    title="Editar"
                                                >
                                                    ✏️
                                                </Link>
                                                <button
                                                    @click="deleteProduct(product)"
                                                    class="text-red-600 hover:text-red-900 transition-colors"
                                                    title="Eliminar"
                                                >
                                                    🗑️
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Mensaje si no hay resultados -->
                            <div v-if="products.data.length === 0" class="text-center py-12">
                                <div class="text-gray-500 text-lg">
                                    <div class="text-4xl mb-4">📦</div>
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
                                    ➕ Crear primer producto
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
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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

// Eliminar producto
const deleteProduct = (product) => {
    if (confirm(`¿Estás seguro de eliminar "${product.name}"?`)) {
        router.delete(route('inventory.products.destroy', product.id));
    }
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