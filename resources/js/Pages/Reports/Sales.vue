<template>
    <AdminLayout :breadcrumbs="breadcrumbs">
        <Head title="Reportes de Ventas" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Reportes de Ventas
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Analiza el rendimiento de tus productos
                    </p>
                </div>

                <!-- Export Buttons -->
                <div class="flex gap-2">
                    <a
                        :href="exportPdfUrl"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <DocumentArrowDownIcon class="w-5 h-5" />
                        <span class="hidden sm:inline">PDF</span>
                    </a>
                    <a
                        :href="exportExcelUrl"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <TableCellsIcon class="w-5 h-5" />
                        <span class="hidden sm:inline">Excel</span>
                    </a>
                </div>
            </div>

            <!-- Period Filter -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
                    <!-- Period Tabs -->
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Periodo
                        </label>
                        <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 p-1 bg-gray-50 dark:bg-gray-900">
                            <button
                                v-for="tab in periodTabs"
                                :key="tab.value"
                                @click="setPeriod(tab.value)"
                                class="px-4 py-2 text-sm font-medium rounded-md transition-all"
                                :class="[
                                    selectedPeriod === tab.value
                                        ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                                ]"
                            >
                                {{ tab.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Custom Date Range -->
                    <div v-if="selectedPeriod === 'custom'" class="flex flex-col sm:flex-row gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Desde
                            </label>
                            <input
                                type="date"
                                v-model="customStartDate"
                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Hasta
                            </label>
                            <input
                                type="date"
                                v-model="customEndDate"
                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            />
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="applyCustomRange"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                Aplicar
                            </button>
                        </div>
                    </div>

                    <!-- Date Display -->
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <CalendarIcon class="w-4 h-4 inline-block mr-1" />
                        {{ formatDate(filters.start_date) }} - {{ formatDate(filters.end_date) }}
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 lg:p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                            <CurrencyDollarIcon class="w-5 h-5 lg:w-6 lg:h-6 text-green-600 dark:text-green-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs lg:text-sm text-gray-500 dark:text-gray-400 truncate">Ventas Totales</p>
                            <p class="text-lg lg:text-2xl font-bold text-gray-900 dark:text-white truncate">
                                ${{ formatNumber(summary.total_sales) }}
                            </p>
                            <ChangeIndicator :value="summary.sales_change" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 lg:p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <CubeIcon class="w-5 h-5 lg:w-6 lg:h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs lg:text-sm text-gray-500 dark:text-gray-400 truncate">Productos</p>
                            <p class="text-lg lg:text-2xl font-bold text-gray-900 dark:text-white">
                                {{ formatNumber(summary.total_quantity, 0) }}
                            </p>
                            <ChangeIndicator :value="summary.quantity_change" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 lg:p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                            <ShoppingCartIcon class="w-5 h-5 lg:w-6 lg:h-6 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs lg:text-sm text-gray-500 dark:text-gray-400 truncate">Ordenes</p>
                            <p class="text-lg lg:text-2xl font-bold text-gray-900 dark:text-white">
                                {{ formatNumber(summary.total_orders, 0) }}
                            </p>
                            <ChangeIndicator :value="summary.orders_change" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 lg:p-3 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                            <ReceiptPercentIcon class="w-5 h-5 lg:w-6 lg:h-6 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs lg:text-sm text-gray-500 dark:text-gray-400 truncate">Ticket Prom.</p>
                            <p class="text-lg lg:text-2xl font-bold text-gray-900 dark:text-white">
                                ${{ formatNumber(summary.average_ticket) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Products Chart & Table -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <!-- Bar Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Top 10 Productos
                    </h2>
                    <div class="space-y-3">
                        <div
                            v-for="(product, index) in topProducts.slice(0, 10)"
                            :key="index"
                            class="relative"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate pr-2">
                                    {{ index + 1 }}. {{ product.name }}
                                </span>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ product.total_quantity }}
                                    </span>
                                    <ChangeIndicator :value="product.quantity_change" size="sm" />
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5">
                                <div
                                    class="h-2.5 rounded-full transition-all duration-500"
                                    :class="getBarColor(index)"
                                    :style="{ width: getBarWidth(product.total_quantity) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <div v-if="topProducts.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <CubeIcon class="w-12 h-12 mx-auto mb-3 opacity-50" />
                        <p>No hay datos para este periodo</p>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-4 lg:p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Detalle de Ventas
                        </h2>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        #
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Producto
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Cantidad
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Ingresos
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        vs Anterior
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr
                                    v-for="(product, index) in topProducts"
                                    :key="index"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                >
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ index + 1 }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ product.name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-medium text-gray-900 dark:text-white">
                                        {{ formatNumber(product.total_quantity, 0) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">
                                        ${{ formatNumber(product.total_revenue) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <ChangeIndicator :value="product.quantity_change" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="lg:hidden divide-y divide-gray-200 dark:divide-gray-700 max-h-96 overflow-y-auto">
                        <div
                            v-for="(product, index) in topProducts"
                            :key="index"
                            class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 text-xs font-bold text-gray-600 dark:text-gray-300">
                                        {{ index + 1 }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ product.name }}
                                    </span>
                                </div>
                                <ChangeIndicator :value="product.quantity_change" size="sm" />
                            </div>
                            <div class="mt-2 ml-9 flex items-center gap-4 text-sm">
                                <span class="text-gray-500 dark:text-gray-400">
                                    {{ formatNumber(product.total_quantity, 0) }} uds
                                </span>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    ${{ formatNumber(product.total_revenue) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="topProducts.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                        No hay ventas en este periodo
                    </div>
                </div>
            </div>

            <!-- Bottom Products -->
            <div v-if="bottomProducts.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-6">
                <div class="flex items-center gap-2 mb-4">
                    <ExclamationTriangleIcon class="w-5 h-5 text-amber-500" />
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Productos con Menor Venta
                    </h2>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Considera revisar estos productos para optimizar tu menu
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div
                        v-for="(product, index) in bottomProducts"
                        :key="index"
                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                    >
                        <span class="text-sm text-gray-700 dark:text-gray-300 truncate">
                            {{ product.name }}
                        </span>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 flex-shrink-0 ml-2">
                            {{ product.total_quantity }} uds
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    CalendarIcon,
    CurrencyDollarIcon,
    CubeIcon,
    ShoppingCartIcon,
    DocumentArrowDownIcon,
    TableCellsIcon,
    ExclamationTriangleIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
} from '@heroicons/vue/24/outline';
import { ReceiptPercentIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    topProducts: {
        type: Array,
        default: () => [],
    },
    bottomProducts: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        default: () => ({}),
    },
    dailyTrend: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const breadcrumbs = [
    { label: 'Reportes', href: route('reports.sales') },
    { label: 'Ventas' },
];

const periodTabs = [
    { value: 'today', label: 'Hoy' },
    { value: 'week', label: 'Semana' },
    { value: 'month', label: 'Mes' },
    { value: 'custom', label: 'Personalizado' },
];

const selectedPeriod = ref(props.filters.period || 'today');
const customStartDate = ref(props.filters.start_date || '');
const customEndDate = ref(props.filters.end_date || '');

const maxQuantity = computed(() => {
    if (props.topProducts.length === 0) return 1;
    return Math.max(...props.topProducts.map(p => p.total_quantity));
});

const exportPdfUrl = computed(() => {
    const params = new URLSearchParams({
        period: selectedPeriod.value,
        start_date: customStartDate.value,
        end_date: customEndDate.value,
    });
    return route('reports.sales.pdf') + '?' + params.toString();
});

const exportExcelUrl = computed(() => {
    const params = new URLSearchParams({
        period: selectedPeriod.value,
        start_date: customStartDate.value,
        end_date: customEndDate.value,
    });
    return route('reports.sales.excel') + '?' + params.toString();
});

const setPeriod = (period) => {
    selectedPeriod.value = period;
    if (period !== 'custom') {
        router.get(route('reports.sales'), { period }, { preserveState: true });
    }
};

const applyCustomRange = () => {
    if (customStartDate.value && customEndDate.value) {
        router.get(route('reports.sales'), {
            period: 'custom',
            start_date: customStartDate.value,
            end_date: customEndDate.value,
        }, { preserveState: true });
    }
};

const formatNumber = (value, decimals = 2) => {
    if (value === null || value === undefined) return '0';
    return Number(value).toLocaleString('es-MX', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr + 'T00:00:00');
    return date.toLocaleDateString('es-MX', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const getBarWidth = (quantity) => {
    return (quantity / maxQuantity.value) * 100;
};

const getBarColor = (index) => {
    const colors = [
        'bg-blue-500',
        'bg-green-500',
        'bg-purple-500',
        'bg-amber-500',
        'bg-pink-500',
        'bg-cyan-500',
        'bg-indigo-500',
        'bg-rose-500',
        'bg-teal-500',
        'bg-orange-500',
    ];
    return colors[index % colors.length];
};

// Change Indicator Component
const ChangeIndicator = {
    props: {
        value: {
            type: Number,
            default: null,
        },
        size: {
            type: String,
            default: 'md',
        },
    },
    setup(props) {
        const classes = computed(() => {
            const base = props.size === 'sm' ? 'text-xs' : 'text-sm';
            if (props.value === null || props.value === 0) {
                return `${base} text-gray-500 dark:text-gray-400`;
            }
            return props.value > 0
                ? `${base} text-green-600 dark:text-green-400`
                : `${base} text-red-600 dark:text-red-400`;
        });

        const icon = computed(() => {
            if (props.value === null || props.value === 0) return null;
            return props.value > 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon;
        });

        const text = computed(() => {
            if (props.value === null) return '-';
            if (props.value === 0) return '0%';
            const prefix = props.value > 0 ? '+' : '';
            return `${prefix}${props.value}%`;
        });

        return { classes, icon, text };
    },
    template: `
        <span :class="classes" class="inline-flex items-center gap-0.5 font-medium">
            <component :is="icon" v-if="icon" class="w-3.5 h-3.5" />
            {{ text }}
        </span>
    `,
};
</script>
