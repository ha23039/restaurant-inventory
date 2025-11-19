<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/Data/DataTable.vue';
import Pagination from '@/Components/Data/Pagination.vue';
import SearchBar from '@/Components/Data/SearchBar.vue';
import FilterDropdown from '@/Components/Data/FilterDropdown.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseCard from '@/Components/Base/BaseCard.vue';
import BaseBadge from '@/Components/Base/BaseBadge.vue';
import { useToast } from '@/composables';

const props = defineProps({
    expenses: Object,
    filters: Object,
    categories: Array,
    statistics: Object,
});

const toast = useToast();

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
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestión de Gastos
                </h2>
                <Link :href="route('expenses.create')">
                    <BaseButton variant="primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Gasto
                    </BaseButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <BaseCard class="bg-white">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500 mb-1">Total Gastos</div>
                            <div class="text-2xl font-bold text-gray-900">
                                ${{ statistics.total.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                            </div>
                        </div>
                    </BaseCard>

                    <BaseCard class="bg-white">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500 mb-1">Cantidad</div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ statistics.count }}
                            </div>
                        </div>
                    </BaseCard>

                    <BaseCard class="bg-white">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500 mb-1">Promedio</div>
                            <div class="text-2xl font-bold text-gray-900">
                                ${{ statistics.average.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                            </div>
                        </div>
                    </BaseCard>

                    <BaseCard class="bg-white">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500 mb-1">Top Categoría</div>
                            <div class="text-lg font-bold text-gray-900">
                                {{ statistics.by_category[0]?.category_label || 'N/A' }}
                            </div>
                            <div class="text-sm text-gray-500">
                                ${{ (statistics.by_category[0]?.total || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                            </div>
                        </div>
                    </BaseCard>
                </div>

                <!-- Filtros -->
                <BaseCard class="bg-white">
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
                                    class="block w-1/2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    @change="handleDateRangeChange"
                                />
                                <input
                                    v-model="dateTo"
                                    type="date"
                                    class="block w-1/2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                <BaseCard class="bg-white">
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
                            <div class="flex items-center justify-center gap-2">
                                <Link :href="route('expenses.edit', row.id)">
                                    <BaseButton variant="secondary" size="sm">
                                        Editar
                                    </BaseButton>
                                </Link>
                                <BaseButton
                                    variant="danger"
                                    size="sm"
                                    @click="deleteExpense(row)"
                                >
                                    Eliminar
                                </BaseButton>
                            </div>
                        </template>
                    </DataTable>

                    <div class="border-t border-gray-200">
                        <Pagination
                            :current-page="expenses.current_page"
                            :total-pages="expenses.last_page"
                            :total-items="expenses.total"
                            :per-page="expenses.per_page"
                            @page-change="(page) => router.get(route('expenses.index', { ...filters, page }))"
                        />
                    </div>
                </BaseCard>
            </div>
        </div>
    </AdminLayout>
</template>
