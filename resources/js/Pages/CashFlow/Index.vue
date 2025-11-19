<template>
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
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
            <BaseCard class="bg-white">
                <div class="p-6">
                    <DataTable
                        :columns="columns"
                        :data="transactions.data"
                        :loading="loading"
                    >
                        <template #cell-flow_date="{ row }">
                            <div class="text-sm text-gray-900">
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
                                <div class="text-sm font-medium text-gray-900">
                                    {{ row.description }}
                                </div>
                                <div v-if="row.notes" class="text-xs text-gray-500 mt-1">
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
                            <div v-if="row.user" class="text-sm text-gray-900">
                                <div class="font-medium">{{ row.user.name }}</div>
                                <div class="text-xs text-gray-500">{{ row.user.role }}</div>
                            </div>
                            <div v-else class="text-sm text-gray-400">
                                N/A
                            </div>
                        </template>

                        <template #cell-sale="{ row }">
                            <div v-if="row.sale">
                                <Link
                                    :href="route('sales.show', row.sale.id)"
                                    class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                                >
                                    #{{ row.sale.sale_number }}
                                </Link>
                                <div class="text-xs text-gray-500">
                                    {{ row.sale.payment_method }}
                                </div>
                            </div>
                            <div v-else class="text-sm text-gray-400">
                                -
                            </div>
                        </template>
                    </DataTable>

                    <!-- Pagination -->
                    <div v-if="transactions.data.length > 0" class="mt-6">
                        <Pagination
                            :links="transactions.links"
                            :from="transactions.from"
                            :to="transactions.to"
                            :total="transactions.total"
                        />
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="transactions.data.length === 0 && !loading"
                        class="py-12 text-center"
                    >
                        <svg
                            class="w-16 h-16 mx-auto text-gray-300 mb-4"
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
                        <h3 class="text-lg font-medium text-gray-900 mb-1">
                            No se encontraron transacciones
                        </h3>
                        <p class="text-gray-500">
                            Intenta ajustar los filtros de búsqueda
                        </p>
                    </div>
                </div>
            </BaseCard>
        </div>

        <!-- Export Modal -->
        <ExportModal
            :show="showExportModal"
            :filters="filters"
            title="Exportar Reporte de Flujo de Efectivo"
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
import Pagination from '@/Components/Data/Pagination.vue';
import TransactionFilters from '@/Components/Financial/TransactionFilters.vue';
import ExportModal from '@/Components/Financial/ExportModal.vue';

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
