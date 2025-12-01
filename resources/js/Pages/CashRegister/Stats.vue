<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    stats: Object,
    date_from: String,
    date_to: String,
});

const localDateFrom = ref(props.date_from);
const localDateTo = ref(props.date_to);

const applyFilters = () => {
    router.get(route('cashregister.stats'), {
        date_from: localDateFrom.value,
        date_to: localDateTo.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    localDateFrom.value = new Date(new Date().setDate(1)).toISOString().split('T')[0]; // Primer día del mes
    localDateTo.value = new Date().toISOString().split('T')[0]; // Hoy
    applyFilters();
};

const formatPrice = (value) => {
    return parseFloat(value || 0).toFixed(2);
};

const formatPercent = (value) => {
    return parseFloat(value || 0).toFixed(1);
};

// Calcular estadísticas derivadas
const averagePerSession = computed(() => {
    if (!props.stats?.total_sessions || props.stats.total_sessions === 0) return 0;
    return props.stats.total_sales / props.stats.total_sessions;
});

const averageDuration = computed(() => {
    if (!props.stats?.total_sessions || props.stats.total_sessions === 0) return 0;
    return props.stats.total_hours / props.stats.total_sessions;
});

const mostProductiveDay = computed(() => {
    if (!props.stats?.daily_totals || props.stats.daily_totals.length === 0) return null;
    return props.stats.daily_totals.reduce((max, day) =>
        day.total > max.total ? day : max
    , props.stats.daily_totals[0]);
});
</script>

<template>
    <Head title="Estadísticas de Caja" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Estadísticas de Caja Registradora
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Filtros de Fecha -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Período de Análisis
                    </h3>
                    <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Desde
                            </label>
                            <input
                                v-model="localDateFrom"
                                type="date"
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Hasta
                            </label>
                            <input
                                v-model="localDateTo"
                                type="date"
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>

                        <div class="flex items-end gap-2">
                            <button
                                type="submit"
                                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                            >
                                Aplicar
                            </button>
                            <button
                                type="button"
                                @click="clearFilters"
                                class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors"
                            >
                                Limpiar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Métricas Principales -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Sesiones -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm opacity-90">Total Sesiones</p>
                                <p class="text-3xl font-bold mt-2">{{ stats?.total_sessions || 0 }}</p>
                            </div>
                            <svg class="w-12 h-12 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <p class="text-xs mt-3 opacity-75">
                            {{ stats?.total_hours?.toFixed(1) || 0 }} horas totales
                        </p>
                    </div>

                    <!-- Ventas Totales -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm opacity-90">Ventas Totales</p>
                                <p class="text-3xl font-bold mt-2">${{ formatPrice(stats?.total_sales) }}</p>
                            </div>
                            <svg class="w-12 h-12 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-xs mt-3 opacity-75">
                            {{ stats?.total_transactions || 0 }} transacciones
                        </p>
                    </div>

                    <!-- Promedio por Sesión -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm opacity-90">Promedio/Sesión</p>
                                <p class="text-3xl font-bold mt-2">${{ formatPrice(averagePerSession) }}</p>
                            </div>
                            <svg class="w-12 h-12 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <p class="text-xs mt-3 opacity-75">
                            Duración promedio: {{ averageDuration.toFixed(1) }} hrs
                        </p>
                    </div>

                    <!-- Diferencias -->
                    <div :class="[
                        'rounded-lg shadow-lg p-6 text-white',
                        (stats?.total_differences || 0) >= 0
                            ? 'bg-gradient-to-br from-yellow-500 to-orange-500'
                            : 'bg-gradient-to-br from-red-500 to-red-600'
                    ]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm opacity-90">Diferencias</p>
                                <p class="text-3xl font-bold mt-2">
                                    {{ (stats?.total_differences || 0) >= 0 ? '+' : '' }}${{ formatPrice(stats?.total_differences) }}
                                </p>
                            </div>
                            <svg class="w-12 h-12 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-xs mt-3 opacity-75">
                            {{ stats?.sessions_with_differences || 0 }} sesiones con diferencias
                        </p>
                    </div>
                </div>

                <!-- Desglose por Método de Pago -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                        Desglose por Método de Pago
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="border-l-4 border-green-500 pl-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Efectivo</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                ${{ formatPrice(stats?.payment_methods?.cash || 0) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ formatPercent(stats?.payment_methods?.cash_percentage || 0) }}% del total
                            </p>
                        </div>

                        <div class="border-l-4 border-blue-500 pl-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Tarjeta</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                ${{ formatPrice(stats?.payment_methods?.card || 0) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ formatPercent(stats?.payment_methods?.card_percentage || 0) }}% del total
                            </p>
                        </div>

                        <div class="border-l-4 border-purple-500 pl-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Transferencia</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                ${{ formatPrice(stats?.payment_methods?.transfer || 0) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ formatPercent(stats?.payment_methods?.transfer_percentage || 0) }}% del total
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Ventas por Día -->
                <div v-if="stats?.daily_totals && stats.daily_totals.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                        Ventas por Día
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="day in stats.daily_totals"
                            :key="day.date"
                            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                        >
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                        <span class="text-blue-600 dark:text-blue-400 font-bold">
                                            {{ new Date(day.date).getDate() }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ new Date(day.date).toLocaleDateString('es-ES', { weekday: 'long', month: 'long', day: 'numeric' }) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ day.sessions }} sesión(es) • {{ day.transactions }} venta(s)
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                    ${{ formatPrice(day.total) }}
                                </p>
                                <div v-if="day.difference != 0" class="text-xs mt-1" :class="day.difference > 0 ? 'text-yellow-600' : 'text-red-600'">
                                    {{ day.difference > 0 ? '+' : '' }}${{ formatPrice(day.difference) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Día Más Productivo -->
                    <div v-if="mostProductiveDay" class="mt-6 bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Día más productivo: {{ new Date(mostProductiveDay.date).toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' }) }}
                                </p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    ${{ formatPrice(mostProductiveDay.total) }} en {{ mostProductiveDay.transactions }} venta(s)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sesiones Recientes -->
                <div v-if="stats?.recent_sessions && stats.recent_sessions.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Últimas Sesiones
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Fecha
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Duración
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Ventas
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Diferencia
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Estado
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="session in stats.recent_sessions" :key="session.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ new Date(session.opened_at).toLocaleDateString('es-ES') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ session.duration_hours || 0 }} hrs
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900 dark:text-white">
                                        ${{ formatPrice(session.total_sales) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                        <span v-if="session.status === 'closed'" :class="[
                                            'font-semibold',
                                            session.difference == 0 ? 'text-green-600' :
                                            session.difference > 0 ? 'text-yellow-600' :
                                            'text-red-600'
                                        ]">
                                            {{ session.difference > 0 ? '+' : '' }}${{ formatPrice(session.difference) }}
                                        </span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span :class="[
                                            'px-2 py-1 text-xs font-semibold rounded-full',
                                            session.status === 'open' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                        ]">
                                            {{ session.status === 'open' ? 'Abierta' : 'Cerrada' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
