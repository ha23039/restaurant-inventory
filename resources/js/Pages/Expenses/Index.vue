<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/Data/DataTable.vue';
import SearchBar from '@/Components/Data/SearchBar.vue';
import FilterDropdown from '@/Components/Data/FilterDropdown.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseCard from '@/Components/Base/BaseCard.vue';
import BaseBadge from '@/Components/Base/BaseBadge.vue';
import ExpenseSlideOver from '@/Components/ExpenseSlideOver.vue';
import { useToast } from '@/composables';

const props = defineProps({
    expenses: Object,
    filters: Object,
    categories: Array,
    suppliers: Array,
    statistics: Object,
});

const toast = useToast();
const showExpenseSlideOver = ref(false);
const editingExpense = ref(null);

// Filtros
const searchQuery = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || null);
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

// Columns para DataTable
const columns = [
    { key: 'flow_date_formatted', label: 'Fecha', sortable: true },
    { key: 'category_label', label: 'Categoría' },
    { key: 'description', label: 'Descripción' },
    { key: 'formatted_amount', label: 'Monto', align: 'right' },
    { key: 'user.name', label: 'Registrado por' },
    { key: 'actions', label: 'Acciones', sortable: false, align: 'center' },
];

const categoryOptions = computed(() => {
    return [
        { value: null, label: 'Todas las categorías' },
        ...props.categories.map(cat => ({
            value: cat.value,
            label: cat.label,
        })),
    ];
});

const handleSearch = () => {
    applyFilters();
};

const handleCategoryChange = () => {
    applyFilters();
};

const handleDateRangeChange = () => {
    applyFilters();
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = null;
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};

const applyFilters = () => {
    router.get(route('expenses.index'), {
        search: searchQuery.value,
        category: selectedCategory.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const openEditSlideOver = (expense) => {
    editingExpense.value = expense;
    showExpenseSlideOver.value = true;
};

const openCreateSlideOver = () => {
    editingExpense.value = null;
    showExpenseSlideOver.value = true;
};

const closeExpenseSlideOver = () => {
    showExpenseSlideOver.value = false;
    setTimeout(() => {
        editingExpense.value = null;
    }, 300);
};

const deleteExpense = (expense) => {
    if (!confirm(`¿Estás seguro de eliminar el gasto "${expense.description}"?`)) {
        return;
    }

    router.delete(route('expenses.destroy', expense.id), {
        onSuccess: () => {
            toast.success('Gasto eliminado exitosamente');
        },
        onError: () => {
            toast.error('Error al eliminar el gasto');
        },
    });
};

const getCategoryVariant = (category) => {
    const variants = {
        compras: 'primary',
        gastos_operativos: 'warning',
        gastos_admin: 'info',
        mantenimiento: 'danger',
        marketing: 'success',
        otros: 'default',
    };
    return variants[category] || 'default';
};
</script>

<template>
    <Head title="Gastos" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    Gestión de Gastos
                </h2>
                <BaseButton variant="primary" @click="openCreateSlideOver">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Gasto
                </BaseButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <BaseCard class="bg-gradient-to-br from-red-500 to-red-600 text-white shadow-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-red-100 text-sm font-medium">Total Gastos</div>
                                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-3xl font-bold">
                                ${{ statistics.total.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                            </div>
                        </div>
                    </BaseCard>

                    <BaseCard class="bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-blue-100 text-sm font-medium">Cantidad</div>
                                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-3xl font-bold">
                                {{ statistics.count }}
                            </div>
                        </div>
                    </BaseCard>

                    <BaseCard class="bg-gradient-to-br from-purple-500 to-purple-600 text-white shadow-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-purple-100 text-sm font-medium">Promedio</div>
                                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-3xl font-bold">
                                ${{ statistics.average.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                            </div>
                        </div>
                    </BaseCard>

                    <BaseCard class="bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-orange-100 text-sm font-medium">Top Categoría</div>
                                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-xl font-bold mb-1">
                                {{ statistics.by_category[0]?.category_label || 'N/A' }}
                            </div>
                            <div class="text-sm text-orange-100">
                                ${{ (statistics.by_category[0]?.total || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                            </div>
                        </div>
                    </BaseCard>
                </div>

                <!-- Filtros -->
                <BaseCard class="bg-white dark:bg-gray-800">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <SearchBar
                                    v-model="searchQuery"
                                    placeholder="Buscar gastos..."
                                    @search="handleSearch"
                                />
                            </div>

                            <div>
                                <FilterDropdown
                                    v-model="selectedCategory"
                                    :options="categoryOptions"
                                    label="Categoría"
                                    @change="handleCategoryChange"
                                />
                            </div>

                            <div class="flex gap-2">
                                <input
                                    v-model="dateFrom"
                                    type="date"
                                    class="block w-1/2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    @change="handleDateRangeChange"
                                />
                                <input
                                    v-model="dateTo"
                                    type="date"
                                    class="block w-1/2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    @change="handleDateRangeChange"
                                />
                            </div>
                        </div>

                        <div v-if="searchQuery || selectedCategory || dateFrom || dateTo" class="mt-4">
                            <BaseButton variant="secondary" size="sm" @click="clearFilters">
                                Limpiar Filtros
                            </BaseButton>
                        </div>
                    </div>
                </BaseCard>

                <!-- Tabla de gastos -->
                <BaseCard class="bg-white dark:bg-gray-800">
                    <DataTable
                        :columns="columns"
                        :data="expenses.data"
                        :hoverable="true"
                    >
                        <template #cell-category_label="{ row }">
                            <BaseBadge :variant="getCategoryVariant(row.category)">
                                {{ row.category_label }}
                            </BaseBadge>
                        </template>

                        <template #cell-formatted_amount="{ row }">
                            <span class="font-semibold text-red-600">
                                -{{ row.formatted_amount }}
                            </span>
                        </template>

                        <template #cell-actions="{ row }">
                            <div class="flex items-center justify-center space-x-2">
                                <Link
                                    :href="route('expenses.show', row.id)"
                                    class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                    title="Ver Detalles"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </Link>
                                <button
                                    @click="openEditSlideOver(row)"
                                    class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                    title="Editar"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    @click="deleteExpense(row)"
                                    class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                    title="Eliminar"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </DataTable>

                    <!-- Paginación -->
                    <div v-if="expenses.data.length > 0" class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <Link
                                    v-if="expenses.prev_page_url"
                                    :href="expenses.prev_page_url"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Anterior
                                </Link>
                                <Link
                                    v-if="expenses.next_page_url"
                                    :href="expenses.next_page_url"
                                    class="relative ml-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Siguiente
                                </Link>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700 dark:text-gray-200">
                                        Mostrando
                                        <span class="font-medium">{{ expenses.from }}</span>
                                        a
                                        <span class="font-medium">{{ expenses.to }}</span>
                                        de
                                        <span class="font-medium">{{ expenses.total }}</span>
                                        gastos
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        <template v-for="(link, index) in expenses.links" :key="index">
                                            <Link
                                                v-if="link.url"
                                                :href="link.url"
                                                v-html="link.label"
                                                class="relative inline-flex items-center px-2 py-2 text-sm font-medium transition-colors border"
                                                :class="[
                                                    link.active
                                                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600 dark:bg-blue-900 dark:text-blue-200'
                                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700',
                                                    index === 0 ? 'rounded-l-md' : '',
                                                    index === expenses.links.length - 1 ? 'rounded-r-md' : '',
                                                ]"
                                            />
                                            <span
                                                v-else
                                                v-html="link.label"
                                                class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300 cursor-default dark:bg-gray-800 dark:border-gray-600 dark:text-gray-600"
                                            />
                                        </template>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </BaseCard>
            </div>
        </div>

        <!-- Expense SlideOver -->
        <ExpenseSlideOver
            :show="showExpenseSlideOver"
            :expense="editingExpense"
            :categories="categories"
            :suppliers="suppliers"
            @close="closeExpenseSlideOver"
        />
    </AdminLayout>
</template>
