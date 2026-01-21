<template>
    <AdminLayout>
        <template #header>
            <div class="space-y-3 md:space-y-0 md:flex md:items-center md:justify-between">
                <h2 class="text-lg md:text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Flujo de Efectivo
                </h2>
                <div class="flex items-center gap-2 md:gap-3">
                    <BaseButton
                        variant="secondary"
                        size="sm"
                        class="md:hidden"
                        @click="showExportModal = true"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </BaseButton>
                    <BaseButton
                        variant="secondary"
                        size="md"
                        class="hidden md:inline-flex"
                        @click="showExportModal = true"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Exportar Reporte
                    </BaseButton>
                    <Link :href="route('financial.dashboard')">
                        <BaseButton variant="primary" size="sm" class="md:hidden">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </BaseButton>
                        <BaseButton variant="primary" size="md" class="hidden md:inline-flex">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Ver Dashboard
                        </BaseButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-3 md:py-6 px-2 sm:px-6 lg:px-8 space-y-4 md:space-y-6">
            <!-- Summary Cards (if date range selected) -->
            <div v-if="summary" class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6">
                <BaseCard class="bg-gradient-to-br from-blue-500 to-blue-600 text-white">
                    <div class="p-3 md:p-6">
                        <div class="text-blue-100 text-xs md:text-sm font-medium mb-1">
                            Balance
                        </div>
                        <div class="text-lg md:text-3xl font-bold truncate">
                            {{ formatCurrency(summary.balance) }}
                        </div>
                    </div>
                </BaseCard>

                <BaseCard class="bg-gradient-to-br from-green-500 to-green-600 text-white">
                    <div class="p-3 md:p-6">
                        <div class="text-green-100 text-xs md:text-sm font-medium mb-1">
                            Ingresos
                        </div>
                        <div class="text-lg md:text-3xl font-bold mb-1 truncate">
                            {{ formatCurrency(summary.income.total) }}
                        </div>
                        <div class="text-green-100 text-xs hidden md:block">
                            {{ summary.income.count }} transacciones
                        </div>
                    </div>
                </BaseCard>

                <BaseCard class="bg-gradient-to-br from-red-500 to-red-600 text-white">
                    <div class="p-3 md:p-6">
                        <div class="text-red-100 text-xs md:text-sm font-medium mb-1">
                            Egresos
                        </div>
                        <div class="text-lg md:text-3xl font-bold mb-1 truncate">
                            {{ formatCurrency(summary.expenses.total) }}
                        </div>
                        <div class="text-red-100 text-xs hidden md:block">
                            {{ summary.expenses.count }} transacciones
                        </div>
                    </div>
                </BaseCard>

                <BaseCard class="bg-gradient-to-br from-purple-500 to-purple-600 text-white">
                    <div class="p-3 md:p-6">
                        <div class="text-purple-100 text-xs md:text-sm font-medium mb-1">
                            Margen
                        </div>
                        <div class="text-lg md:text-3xl font-bold">
                            {{ summary.profit_margin.toFixed(1) }}%
                        </div>
                    </div>
                </BaseCard>
            </div>

            <!-- Quick Navigation to Cash Register -->
            <BaseCard class="bg-gradient-to-br from-teal-50 to-teal-100 dark:from-teal-900/20 dark:to-teal-800/30 border-2 border-teal-200 dark:border-teal-700">
                <div class="p-4 md:p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-teal-500 dark:bg-teal-600 rounded-full flex items-center justify-center mr-3 md:mr-4 flex-shrink-0">
                            <svg class="w-5 h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base md:text-lg font-semibold text-teal-900 dark:text-teal-100">
                                Caja Registradora
                            </h3>
                            <p class="text-xs md:text-sm text-teal-700 dark:text-teal-300 truncate">
                                Gestión completa de caja
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 md:gap-3">
                        <Link
                            :href="route('cashregister.history')"
                            class="bg-white dark:bg-gray-800 border-2 border-teal-300 dark:border-teal-600 rounded-lg p-3 md:p-4 hover:shadow-md transition-all"
                        >
                            <div class="flex items-center">
                                <svg class="w-4 h-4 md:w-5 md:h-5 text-teal-600 dark:text-teal-400 mr-2 md:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="min-w-0">
                                    <div class="text-sm md:text-base font-medium text-teal-900 dark:text-teal-100">Historial</div>
                                    <div class="text-xs text-teal-700 dark:text-teal-300 hidden md:block">Ver movimientos</div>
                                </div>
                            </div>
                        </Link>
                        <Link
                            :href="route('cashregister.stats')"
                            class="bg-white dark:bg-gray-800 border-2 border-teal-300 dark:border-teal-600 rounded-lg p-3 md:p-4 hover:shadow-md transition-all"
                        >
                            <div class="flex items-center">
                                <svg class="w-4 h-4 md:w-5 md:h-5 text-teal-600 dark:text-teal-400 mr-2 md:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <div class="min-w-0">
                                    <div class="text-sm md:text-base font-medium text-teal-900 dark:text-teal-100">Estadísticas</div>
                                    <div class="text-xs text-teal-700 dark:text-teal-300 hidden md:block">Ver métricas</div>
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

            <!-- Transactions Table (Desktop) -->
            <BaseCard class="bg-white dark:bg-gray-800 hidden lg:block">
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
                </div>
            </BaseCard>

            <!-- Transactions Cards (Mobile) -->
            <div class="lg:hidden space-y-3">
                <!-- Loading State -->
                <div v-if="loading" class="flex justify-center py-12">
                    <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Transaction Cards -->
                <div
                    v-else-if="transactions.data.length > 0"
                    v-for="row in transactions.data"
                    :key="row.id"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4"
                >
                    <!-- Header: Date + Amount -->
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ row.flow_date_formatted }}
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <BaseBadge :variant="row.is_income ? 'success' : 'danger'" size="sm">
                                    {{ row.type === 'entrada' ? 'Ingreso' : 'Egreso' }}
                                </BaseBadge>
                                <BaseBadge :variant="getCategoryVariant(row.category)" size="sm">
                                    {{ row.category_label }}
                                </BaseBadge>
                            </div>
                        </div>
                        <div
                            :class="[
                                'text-lg font-bold',
                                row.is_income ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                            ]"
                        >
                            {{ row.is_income ? '+' : '-' }}{{ formatCurrency(row.amount) }}
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <p class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                            {{ row.description }}
                        </p>
                        <p v-if="row.notes" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ row.notes }}
                        </p>
                    </div>

                    <!-- Footer: User + Sale -->
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                        <div v-if="row.user" class="text-xs text-gray-500 dark:text-gray-400">
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ row.user.name }}</span>
                        </div>
                        <div v-else class="text-xs text-gray-400 dark:text-gray-500">
                            Sin usuario
                        </div>

                        <Link
                            v-if="row.sale"
                            :href="route('sales.show', row.sale.id)"
                            class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium"
                        >
                            Venta #{{ row.sale.sale_number }}
                        </Link>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8 text-center"
                >
                    <svg
                        class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3"
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
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">
                        No se encontraron transacciones
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Intenta ajustar los filtros
                    </p>
                </div>
            </div>

            <!-- Mobile Pagination -->
            <div v-if="transactions.data.length > 0 && !loading" class="lg:hidden bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        {{ transactions.from }}-{{ transactions.to }} de {{ transactions.total }}
                    </p>
                    <div class="flex gap-2">
                        <Link
                            v-if="transactions.prev_page_url"
                            :href="transactions.prev_page_url"
                            class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600"
                        >
                            Anterior
                        </Link>
                        <Link
                            v-if="transactions.next_page_url"
                            :href="transactions.next_page_url"
                            class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
                        >
                            Siguiente
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Desktop Pagination (inside DataTable card) -->
            <BaseCard class="bg-white dark:bg-gray-800 hidden lg:block" v-if="transactions.data.length > 0">
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                    <div class="flex items-center justify-between">
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
                                        v-html="translatePaginationLabel(link.label)"
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
                                        v-html="translatePaginationLabel(link.label)"
                                        class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300 cursor-default dark:bg-gray-800 dark:border-gray-600 dark:text-gray-600"
                                    />
                                </template>
                            </nav>
                        </div>
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

const translatePaginationLabel = (label) => {
    return label
        .replace('&laquo; Previous', '« Anterior')
        .replace('Next &raquo;', 'Siguiente »')
        .replace('&laquo;', '«')
        .replace('&raquo;', '»');
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
