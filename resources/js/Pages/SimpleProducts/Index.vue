<script setup>
import { ref, computed } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SimpleProductFormSlideOver from '@/Components/SimpleProductFormSlideOver.vue';
import SimpleProductVariantManagerSlideOver from '@/Components/SimpleProduct/SimpleProductVariantManagerSlideOver.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { useToast } from 'vue-toastification';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

const props = defineProps({
    simpleProducts: Object,
    products: Array,
    filters: Object,
});

const toast = useToast();
const { confirm } = useConfirmDialog();

// SlideOver state
const showFormSlideOver = ref(false);
const editingProduct = ref(null);

// Variant Manager state
const showVariantManager = ref(false);
const managingProductVariants = ref(null);

// Search and filters
const search = ref(props.filters.search || '');
const categoryFilter = ref(props.filters.category || '');

// Get unique categories from products
const categories = computed(() => {
    const cats = new Set(props.products.map(p => p.category?.name).filter(Boolean));
    return Array.from(cats).sort();
});

const openCreateForm = () => {
    editingProduct.value = null;
    showFormSlideOver.value = true;
};

const openEditForm = (product) => {
    editingProduct.value = product;
    showFormSlideOver.value = true;
};

const closeForm = () => {
    showFormSlideOver.value = false;
    editingProduct.value = null;
};

const openVariantManager = (product) => {
    managingProductVariants.value = product;
    showVariantManager.value = true;
};

const closeVariantManager = () => {
    showVariantManager.value = false;
    managingProductVariants.value = null;
};

const handleVariantsUpdated = () => {
    // Reload the page data to reflect variant changes
    router.reload({ only: ['simpleProducts'] });
};

const handleSearch = () => {
    router.get(route('simple-products.index'), {
        search: search.value,
        category: categoryFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    categoryFilter.value = '';
    router.get(route('simple-products.index'), {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const deleteProduct = async (product) => {
    const confirmed = await confirm({
        title: '¿Eliminar producto?',
        message: `¿Eliminar "${product.name}"? Esta acción no se puede deshacer.`,
        confirmText: 'Eliminar',
        type: 'danger'
    });

    if (!confirmed) return;

    router.delete(route('simple-products.destroy', product.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Producto eliminado exitosamente');
        },
        onError: (errors) => {
            toast.error(errors.error || 'Error al eliminar el producto');
        },
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(value);
};

const formatQuantity = (quantity) => {
    return parseFloat(quantity).toFixed(3).replace(/\.?0+$/, '');
};

const getStockBadgeClass = (product) => {
    // Productos con variantes tienen estado especial
    if (product.allows_variants) {
        return 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400';
    }
    if (!product.is_in_stock) {
        return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400';
    }
    if (product.available_quantity < 10) {
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400';
    }
    return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400';
};
</script>

<template>
    <Head title="Productos Simples" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-800 dark:text-gray-200 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path>
                    </svg>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Productos Simples
                    </h2>
                </div>
                <button
                    @click="openCreateForm"
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors shadow-sm"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Nuevo Producto
                </button>
            </div>
        </template>

        <div class="py-3 md:py-6 lg:py-12">
            <div class="max-w-full mx-auto px-2 sm:px-6 lg:px-8">

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Buscar
                            </label>
                            <input
                                v-model="search"
                                @keyup.enter="handleSearch"
                                type="text"
                                placeholder="Nombre o descripción..."
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            />
                        </div>

                        <!-- Category Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Categoría
                            </label>
                            <select
                                v-model="categoryFilter"
                                @change="handleSearch"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Todas las categorías</option>
                                <option v-for="category in categories" :key="category" :value="category">
                                    {{ category }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Filter Actions -->
                    <div class="flex items-center justify-between mt-4">
                        <button
                            @click="handleSearch"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                        >
                            Aplicar Filtros
                        </button>
                        <button
                            v-if="search || categoryFilter"
                            @click="clearFilters"
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
                        >
                            Limpiar filtros
                        </button>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Producto
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Producto Base
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Precio Venta
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Consume
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Disponible
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr
                                    v-for="product in simpleProducts.data"
                                    :key="product.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <!-- Product Name -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                                                <img
                                                    v-if="product.image_path"
                                                    :src="product.image_path"
                                                    :alt="product.name"
                                                    class="w-full h-full object-cover"
                                                />
                                                <div v-else class="w-full h-full flex items-center justify-center bg-blue-100 dark:bg-blue-900">
                                                    <span class="text-blue-600 dark:text-blue-400 text-lg">📦</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ product.name }}
                                                </div>
                                                <div v-if="product.description" class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">
                                                    {{ product.description }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Base Product -->
                                    <td class="px-6 py-4">
                                        <div v-if="product.product" class="text-sm">
                                            <div class="font-medium text-gray-900 dark:text-white">
                                                {{ product.product.name }}
                                            </div>
                                            <div class="text-gray-500 dark:text-gray-400">
                                                Stock: {{ formatQuantity(product.product.current_stock) }} {{ product.product.unit_type }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Sale Price -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ formatCurrency(product.sale_price) }}
                                        </div>
                                    </td>

                                    <!-- Cost Per Unit (Consumption) -->
                                    <td class="px-6 py-4">
                                        <div v-if="product.allows_variants" class="text-sm text-gray-500 dark:text-gray-400 italic">
                                            Por variante
                                        </div>
                                        <div v-else class="text-sm text-gray-900 dark:text-white">
                                            {{ formatQuantity(product.cost_per_unit) }} {{ product.product?.unit_type }}
                                        </div>
                                        <div v-if="!product.allows_variants" class="text-xs text-gray-500 dark:text-gray-400">
                                            por unidad vendida
                                        </div>
                                    </td>

                                    <!-- Available Quantity -->
                                    <td class="px-6 py-4">
                                        <div v-if="product.allows_variants" class="text-sm text-purple-600 dark:text-purple-400 font-medium">
                                            Por variante
                                        </div>
                                        <div v-else class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ product.available_quantity }} unidades
                                        </div>
                                    </td>

                                    <!-- Stock Status -->
                                    <td class="px-6 py-4">
                                        <span :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            getStockBadgeClass(product)
                                        ]">
                                            <span v-if="product.allows_variants">
                                                Por variante
                                            </span>
                                            <span v-else-if="product.is_in_stock">
                                                ✓ En Stock
                                            </span>
                                            <span v-else>
                                                ✗ Sin Stock
                                            </span>
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <button
                                                v-if="product.allows_variants"
                                                @click="openVariantManager(product)"
                                                class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300"
                                                title="Gestionar Variantes"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                                </svg>
                                            </button>
                                            <button
                                                @click="openEditForm(product)"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                                title="Editar"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button
                                                @click="deleteProduct(product)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                title="Eliminar"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Empty State -->
                                <tr v-if="simpleProducts.data.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                            <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">
                                                No se encontraron productos
                                            </p>
                                            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">
                                                Comienza agregando tu primer producto simple
                                            </p>
                                            <button
                                                @click="openCreateForm"
                                                class="mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                                            >
                                                Agregar Producto
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="simpleProducts.data.length > 0" class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <a
                                    v-if="simpleProducts.prev_page_url"
                                    :href="simpleProducts.prev_page_url"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    Anterior
                                </a>
                                <a
                                    v-if="simpleProducts.next_page_url"
                                    :href="simpleProducts.next_page_url"
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    Siguiente
                                </a>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        Mostrando
                                        <span class="font-medium">{{ simpleProducts.from }}</span>
                                        a
                                        <span class="font-medium">{{ simpleProducts.to }}</span>
                                        de
                                        <span class="font-medium">{{ simpleProducts.total }}</span>
                                        resultados
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                        <a
                                            v-for="link in simpleProducts.links"
                                            :key="link.label"
                                            :href="link.url"
                                            v-html="link.label"
                                            :class="[
                                                'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                                link.active
                                                    ? 'z-10 bg-blue-600 border-blue-600 text-white'
                                                    : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                                                !link.url ? 'cursor-not-allowed opacity-50' : ''
                                            ]"
                                        ></a>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form SlideOver -->
        <SimpleProductFormSlideOver
            :show="showFormSlideOver"
            :product="editingProduct"
            :products="products"
            @close="closeForm"
        />

        <!-- Variant Manager SlideOver -->
        <SimpleProductVariantManagerSlideOver
            :show="showVariantManager"
            :simple-product="managingProductVariants"
            @close="closeVariantManager"
            @updated="handleVariantsUpdated"
        />

    </AdminLayout>
</template>
