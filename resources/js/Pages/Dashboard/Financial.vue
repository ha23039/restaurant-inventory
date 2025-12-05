<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseCard from '@/Components/Base/BaseCard.vue';
import BaseBadge from '@/Components/Base/BaseBadge.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import TrendChart from '@/Components/Charts/TrendChart.vue';
import CategoryPieChart from '@/Components/Charts/CategoryPieChart.vue';
import PaymentMethodChart from '@/Components/Charts/PaymentMethodChart.vue';

const props = defineProps({
    filters: Object,
    kpis: Object,
    trends: Object,
    categoryChart: Object,
    paymentMethodChart: Object,
    topExpenses: Array,
    recentTransactions: Array,
    summary: Object,
});

// Local state for filters
const dateFrom = ref(props.filters.date_from);
const dateTo = ref(props.filters.date_to);
const selectedPeriod = ref(props.filters.period);

const periodOptions = [
    { value: 'day', label: 'Diario' },
    { value: 'week', label: 'Semanal' },
    { value: 'month', label: 'Mensual' },
    { value: 'year', label: 'Anual' },
];

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2,
    }).format(value);
};

const handleDateRangeChange = () => {
    router.visit(route('financial.dashboard'), {
        data: {
            date_from: dateFrom.value,
            date_to: dateTo.value,
            period: selectedPeriod.value,
        },
        preserveState: true,
        preserveScroll: true,
    });
};

const handlePeriodChange = () => {
    router.visit(route('financial.dashboard'), {
        data: {
            date_from: dateFrom.value,
            date_to: dateTo.value,
            period: selectedPeriod.value,
        },
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

    dateFrom.value = firstDay.toISOString().split('T')[0];
    dateTo.value = today.toISOString().split('T')[0];
    selectedPeriod.value = 'day';

    handleDateRangeChange();
};
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Dashboard Financiero
                </h2>
                <div class="flex items-center gap-3">
                    <FormSelect
                        v-model="selectedPeriod"
                        :options="periodOptions"
                        class="w-40"
                        @change="handlePeriodChange"
                    />
                </div>
            </div>
        </template>

        <div class="py-6 space-y-6">
            <!-- Date Range Filter -->
            <BaseCard class="bg-white dark:bg-gray-800">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                        Filtrar por Rango de Fechas
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <FormInput
                            v-model="dateFrom"
                            type="date"
                            label="Fecha Inicio"
                            @change="handleDateRangeChange"
                        />
                        <FormInput
                            v-model="dateTo"
                            type="date"
                            label="Fecha Fin"
                            @change="handleDateRangeChange"
                        />
                        <div class="flex items-end">
                            <BaseButton
                                variant="secondary"
                                size="md"
                                class="w-full"
                                @click="resetFilters"
                            >
                                Limpiar Filtros
                            </BaseButton>
                        </div>
                    </div>
                </div>
            </BaseCard>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Balance -->
                <BaseCard class="bg-gradient-to-br from-blue-500 to-blue-600 text-white">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-blue-100 text-sm font-medium">
                                Balance Total
                            </div>
                            <div
                                class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold mb-1">
                            {{ formatCurrency(kpis.balance.current) }}
                        </div>
                        <div class="flex items-center gap-2">
                            <BaseBadge
                                :variant="kpis.balance.trend === 'up' ? 'success' : 'danger'"
                                size="sm"
                            >
                                <span class="flex items-center gap-1">
                                    <svg
                                        v-if="kpis.balance.trend === 'up'"
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 10l7-7m0 0l7 7m-7-7v18"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 14l-7 7m0 0l-7-7m7 7V3"
                                        />
                                    </svg>
                                    {{ Math.abs(kpis.balance.change).toFixed(1) }}%
                                </span>
                            </BaseBadge>
                            <span class="text-blue-100 text-xs">vs período anterior</span>
                        </div>
                    </div>
                </BaseCard>

                <!-- Income -->
                <BaseCard class="bg-gradient-to-br from-green-500 to-green-600 text-white">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-green-100 text-sm font-medium">
                                Ingresos Totales
                            </div>
                            <div
                                class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                                    />
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold mb-1">
                            {{ formatCurrency(kpis.income.current) }}
                        </div>
                        <div class="flex items-center gap-2">
                            <BaseBadge
                                :variant="kpis.income.trend === 'up' ? 'success' : 'danger'"
                                size="sm"
                            >
                                <span class="flex items-center gap-1">
                                    <svg
                                        v-if="kpis.income.trend === 'up'"
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 10l7-7m0 0l7 7m-7-7v18"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 14l-7 7m0 0l-7-7m7 7V3"
                                        />
                                    </svg>
                                    {{ Math.abs(kpis.income.change).toFixed(1) }}%
                                </span>
                            </BaseBadge>
                            <span class="text-green-100 text-xs">
                                {{ kpis.income.count }} transacciones
                            </span>
                        </div>
                    </div>
                </BaseCard>

                <!-- Expenses -->
                <BaseCard class="bg-gradient-to-br from-red-500 to-red-600 text-white">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-red-100 text-sm font-medium">
                                Gastos Totales
                            </div>
                            <div
                                class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"
                                    />
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold mb-1">
                            {{ formatCurrency(kpis.expenses.current) }}
                        </div>
                        <div class="flex items-center gap-2">
                            <BaseBadge
                                :variant="kpis.expenses.trend === 'down' ? 'success' : 'danger'"
                                size="sm"
                            >
                                <span class="flex items-center gap-1">
                                    <svg
                                        v-if="kpis.expenses.trend === 'up'"
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 10l7-7m0 0l7 7m-7-7v18"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 14l-7 7m0 0l-7-7m7 7V3"
                                        />
                                    </svg>
                                    {{ Math.abs(kpis.expenses.change).toFixed(1) }}%
                                </span>
                            </BaseBadge>
                            <span class="text-red-100 text-xs">
                                {{ kpis.expenses.count }} transacciones
                            </span>
                        </div>
                    </div>
                </BaseCard>

                <!-- Profit Margin -->
                <BaseCard class="bg-gradient-to-br from-purple-500 to-purple-600 text-white">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-purple-100 text-sm font-medium">
                                Margen de Ganancia
                            </div>
                            <div
                                class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center"
                            >
                                <svg
                                    class="w-5 h-5"
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
                            </div>
                        </div>
                        <div class="text-3xl font-bold mb-1">
                            {{ kpis.profit_margin.current.toFixed(1) }}%
                        </div>
                        <div class="flex items-center gap-2">
                            <BaseBadge
                                :variant="kpis.profit_margin.trend === 'up' ? 'success' : 'danger'"
                                size="sm"
                            >
                                <span class="flex items-center gap-1">
                                    <svg
                                        v-if="kpis.profit_margin.trend === 'up'"
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 10l7-7m0 0l7 7m-7-7v18"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 14l-7 7m0 0l-7-7m7 7V3"
                                        />
                                    </svg>
                                    {{ Math.abs(kpis.profit_margin.change).toFixed(1) }}pp
                                </span>
                            </BaseBadge>
                            <span class="text-purple-100 text-xs">puntos porcentuales</span>
                        </div>
                    </div>
                </BaseCard>
            </div>

            <!-- Charts Row 1: Trend Chart -->
            <BaseCard class="bg-white dark:bg-gray-800">
                <div class="p-6">
                    <TrendChart :data="trends" :height="350" />
                </div>
            </BaseCard>

            <!-- Charts Row 2: Pie Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <BaseCard class="bg-white dark:bg-gray-800">
                    <div class="p-6">
                        <CategoryPieChart
                            v-if="categoryChart.labels.length > 0"
                            :data="categoryChart"
                            :height="350"
                        />
                        <div
                            v-else
                            class="h-[350px] flex items-center justify-center text-gray-400 dark:text-gray-500"
                        >
                            No hay datos de gastos en este período
                        </div>
                    </div>
                </BaseCard>

                <BaseCard class="bg-white dark:bg-gray-800">
                    <div class="p-6">
                        <PaymentMethodChart
                            v-if="paymentMethodChart.labels.length > 0"
                            :data="paymentMethodChart"
                            :height="350"
                        />
                        <div
                            v-else
                            class="h-[350px] flex items-center justify-center text-gray-400 dark:text-gray-500"
                        >
                            No hay datos de métodos de pago en este período
                        </div>
                    </div>
                </BaseCard>
            </div>

            <!-- Top Expenses & Recent Transactions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Expenses -->
                <BaseCard class="bg-white dark:bg-gray-800">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Top 5 Categorías de Gastos
                        </h3>
                        <div v-if="topExpenses.length > 0" class="space-y-3">
                            <div
                                v-for="(expense, index) in topExpenses"
                                :key="expense.category"
                                class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-semibold text-sm"
                                    >
                                        {{ index + 1 }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ expense.label }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        {{ formatCurrency(expense.total) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="py-12 text-center text-gray-400 dark:text-gray-500"
                        >
                            No hay gastos registrados
                        </div>
                    </div>
                </BaseCard>

                <!-- Recent Transactions -->
                <BaseCard class="bg-white dark:bg-gray-800">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Transacciones Recientes
                        </h3>
                        <div v-if="recentTransactions.length > 0" class="space-y-3">
                            <div
                                v-for="transaction in recentTransactions"
                                :key="transaction.id"
                                class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        :class="[
                                            'w-10 h-10 rounded-full flex items-center justify-center',
                                            transaction.is_income
                                                ? 'bg-green-100 text-green-600'
                                                : 'bg-red-100 text-red-600'
                                        ]"
                                    >
                                        <svg
                                            v-if="transaction.is_income"
                                            class="w-5 h-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 10l7-7m0 0l7 7m-7-7v18"
                                            />
                                        </svg>
                                        <svg
                                            v-else
                                            class="w-5 h-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 14l-7 7m0 0l-7-7m7 7V3"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ transaction.description }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-300">
                                            {{ transaction.flow_date_formatted }} •
                                            {{ transaction.category_label }}
                                        </div>
                                    </div>
                                </div>
                                <div
                                    :class="[
                                        'font-semibold',
                                        transaction.is_income ? 'text-green-600' : 'text-red-600'
                                    ]"
                                >
                                    {{ transaction.is_income ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="py-12 text-center text-gray-400 dark:text-gray-500"
                        >
                            No hay transacciones recientes
                        </div>
                    </div>
                </BaseCard>
            </div>
        </div>
    </AdminLayout>
</template>
