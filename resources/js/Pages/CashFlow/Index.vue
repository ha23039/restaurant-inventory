<template>
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Flujo de Efectivo
                </h2>
                <div class="flex items-center gap-3">
                    <BaseButton
                        variant="secondary"
                        size="md"
                        @click="showExportModal = true"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        Exportar Reporte
                    </BaseButton>
                    <Link :href="route('financial.dashboard')">
                        <BaseButton variant="primary" size="md">
                            <svg
                                class="w-4 h-4 mr-2"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                />
                            </svg>
                            Ver Dashboard
                        </BaseButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6 space-y-6">
            <!-- Summary Cards (if date range selected) -->
            <div v-if="summary" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <BaseCard class="bg-gradient-to-br from-blue-500 to-blue-600 text-white">
                    <div class="p-6">
                        <div class="text-blue-100 text-sm font-medium mb-1">
                            Balance
                        </div>
                        <div class="text-3xl font-bold">
                            {{ formatCurrency(summary.balance) }}
                        </div>
                    </div>
                </BaseCard>

                <BaseCard class="bg-gradient-to-br from-green-500 to-green-600 text-white">
                    <div class="p-6">
                        <div class="text-green-100 text-sm font-medium mb-1">
                            Ingresos
                        </div>
                        <div class="text-3xl font-bold mb-1">
                            {{ formatCurrency(summary.income.total) }}
                        </div>
                        <div class="text-green-100 text-xs">
                            {{ summary.income.count }} transacciones
                        </div>
                    </div>
                </BaseCard>

                <BaseCard class="bg-gradient-to-br from-red-500 to-red-600 text-white">
                    <div class="p-6">
                        <div class="text-red-100 text-sm font-medium mb-1">
                            Egresos
                        </div>
                        <div class="text-3xl font-bold mb-1">
                            {{ formatCurrency(summary.expenses.total) }}
                        </div>
                        <div class="text-red-100 text-xs">
                            {{ summary.expenses.count }} transacciones
                        </div>
                    </div>
                </BaseCard>

                <BaseCard class="bg-gradient-to-br from-purple-500 to-purple-600 text-white">
                    <div class="p-6">
                        <div class="text-purple-100 text-sm font-medium mb-1">
                            Margen
                        </div>
                        <div class="text-3xl font-bold">
                            {{ summary.profit_margin.toFixed(1) }}%
                        </div>
                    </div>
                </BaseCard>
            </div>

            <!-- Quick Navigation to Cash Register -->
            <BaseCard class="bg-gradient-to-br from-teal-50 to-teal-100 dark:from-teal-900/20 dark:to-teal-800/30 border-2 border-teal-200 dark:border-teal-700">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-teal-500 dark:bg-teal-600 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-teal-900 dark:text-teal-100">
                                    Caja Registradora
                                </h3>
                                <p class="text-sm text-teal-700 dark:text-teal-300">
                                    Accede a la gestión completa de la caja registradora
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            :href="route('cashregister.history')"
                            class="flex-1 bg-white dark:bg-gray-800 border-2 border-teal-300 dark:border-teal-600 rounded-lg p-4 hover:shadow-md transition-all transform hover:-translate-y-0.5"
                        >
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-teal-600 dark:text-teal-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <div class="font-medium text-teal-900 dark:text-teal-100">Historial</div>
                                    <div class="text-xs text-teal-700 dark:text-teal-300">Ver movimientos</div>
                                </div>
                            </div>
                        </Link>
                        <Link
                            :href="route('cashregister.stats')"
                            class="flex-1 bg-white dark:bg-gray-800 border-2 border-teal-300 dark:border-teal-600 rounded-lg p-4 hover:shadow-md transition-all transform hover:-translate-y-0.5"
                        >
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-teal-600 dark:text-teal-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <div>
                                    <div class="font-medium text-teal-900 dark:text-teal-100">Estadísticas</div>
                                    <div class="text-xs text-teal-700 dark:text-teal-300">Ver métricas</div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </BaseCard>

            <!-- Filters -->
            <TransactionFilters
                :filters="filters"
                :categories="categories"
                :users="users"
                :expanded-by-default="false"
                @apply="handleFiltersApply"
                @clear="handleFiltersClear"
            />

            <!-- Transactions Table -->
            <BaseCard class="bg-white dark:bg-gray-800">
                <div class="p-6">
                    <DataTable
                        :columns="columns"
                        :data="transactions.data"
                        :loading="loading"
                    >
                        <template #cell-flow_date="{ row }">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                {{ row.flow_date_formatted }}
                            </div>
                        </template>

                        <template #cell-type="{ row }">
                            <BaseBadge :variant="row.is_income ? 'success' : 'danger'" size="sm">
                                {{ row.type === 'entrada' ? 'Ingreso' : 'Egreso' }}
                            </BaseBadge>
                        </template>

                        <template #cell-category_label="{ row }">
                            <BaseBadge
                                :variant="getCategoryVariant(row.category)"
                                size="sm"
                            >
                                {{ row.category_label }}
                            </BaseBadge>
                        </template>

                        <template #cell-description="{ row }">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ row.description }}
                                </div>
                                <div v-if="row.notes" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ row.notes }}
                                </div>
                            </div>
                        </template>

                        <template #cell-amount="{ row }">
                            <div
                                :class="[
                                    'text-sm font-semibold',
                                    row.is_income ? 'text-green-600' : 'text-red-600'
                                ]"
                            >
                                {{ row.is_income ? '+' : '-' }}{{ formatCurrency(row.amount) }}
                            </div>
                        </template>

                        <template #cell-user="{ row }">
                            <div v-if="row.user" class="text-sm text-gray-900 dark:text-gray-100">
                                <div class="font-medium">{{ row.user.name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ row.user.role }}</div>
                            </div>
                            <div v-else class="text-sm text-gray-400 dark:text-gray-500">
                                N/A
                            </div>
                        </template>

                        <template #cell-sale="{ row }">
                            <div v-if="row.sale">
                                <Link
                                    :href="route('sales.show', row.sale.id)"
                                    class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium"
                                >
                                    #{{ row.sale.sale_number }}
                                </Link>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ row.sale.payment_method }}
                                </div>
                            </div>
                            <div v-else class="text-sm text-gray-400 dark:text-gray-500">
                                -
                            </div>
                        </template>
                    </DataTable>

                    <!-- Pagination -->
                    <div v-if="transactions.data.length > 0" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <Link
                                    v-if="transactions.prev_page_url"
                                    :href="transactions.prev_page_url"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                                >
                                    Anterior
                                </Link>
                                <Link
                                    v-if="transactions.next_page_url"
                                    :href="transactions.next_page_url"
                                    class="relative ml-3 inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                                >
                                    Siguiente
                                </Link>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        Mostrando
                                        <span class="font-medium">{{ transactions.from }}</span>
                                        a
                                        <span class="font-medium">{{ transactions.to }}</span>
                                        de
                                        <span class="font-medium">{{ transactions.total }}</span>
                                        transacciones
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        <template v-for="(link, index) in transactions.links" :key="index">
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
                                                    index === transactions.links.length - 1 ? 'rounded-r-md' : '',
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

                    <!-- Empty State -->
                    <div
                        v-if="transactions.data.length === 0 && !loading"
                        class="py-12 text-center"
                    >
                        <svg
                            class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                            />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">
                            No se encontraron transacciones
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            Intenta ajustar los filtros de búsqueda
                        </p>
                    </div>
                </div>
            </BaseCard>
        </div>

        <!-- Export SlideOver -->
        <ExportSlideOver
            :show="showExportModal"
            :filters="filters"
            @close="showExportModal = false"
            @export="handleExport"
        />
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseCard from '@/Components/Base/BaseCard.vue';
import BaseBadge from '@/Components/Base/BaseBadge.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import DataTable from '@/Components/Data/DataTable.vue';
import TransactionFilters from '@/Components/Financial/TransactionFilters.vue';
import ExportSlideOver from '@/Components/Financial/ExportSlideOver.vue';

const props = defineProps({
    transactions: Object,
    filters: Object,
    categories: Array,
    users: Array,
    summary: Object,
});

const toast = useToast();
const loading = ref(false);
const showExportModal = ref(false);

const columns = [
    { key: 'flow_date', label: 'Fecha', sortable: true },
    { key: 'type', label: 'Tipo', sortable: true },
    { key: 'category_label', label: 'Categoría', sortable: true },
    { key: 'description', label: 'Descripción' },
    { key: 'amount', label: 'Monto', sortable: true },
    { key: 'user', label: 'Usuario' },
    { key: 'sale', label: 'Venta' },
];

const getCategoryVariant = (category) => {
    const variants = {
        ventas: 'success',
        compras: 'info',
        gastos_operativos: 'warning',
        gastos_admin: 'danger',
        mantenimiento: 'secondary',
        marketing: 'info',
        devoluciones: 'danger',
        otros: 'secondary',
    };

    return variants[category] || 'secondary';
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2,
    }).format(value);
};

const handleFiltersApply = (filters) => {
    loading.value = true;

    router.visit(route('cashflow.index'), {
        data: filters,
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            loading.value = false;
        },
    });
};

const handleFiltersClear = () => {
    loading.value = true;

    router.visit(route('cashflow.index'), {
        preserveState: false,
        preserveScroll: false,
        onFinish: () => {
            loading.value = false;
            toast.info('Filtros limpiados');
        },
    });
};

const handleExport = (exportData) => {
    const { format, filters: exportFilters } = exportData;

    const routeMap = {
        csv: 'cashflow.export-csv',
        excel: 'cashflow.export-excel',
        pdf: 'cashflow.export-pdf'
    };

    const routeName = routeMap[format];
    if (!routeName) {
        toast.error('Formato de exportación no válido');
        return;
    }

    const queryParams = new URLSearchParams(exportFilters).toString();
    const url = route(routeName) + (queryParams ? `?${queryParams}` : '');

    window.location.href = url;
};
</script>
