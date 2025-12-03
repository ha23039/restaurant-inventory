<template>
    <Head title="Historial de Ventas" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <component :is="icons.chart" class="w-6 h-6 text-gray-800 dark:text-white mr-2" />
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                        Historial de Ventas
                    </h2>
                </div>
                <div class="flex space-x-2">
                    <Link
                        :href="route('returns.index')"
                        class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
                    >
                        <component :is="icons.returns" class="w-4 h-4 mr-2" />
                        Devoluciones
                    </Link>
                    <Link
                        :href="route('sales.pos')"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
                    >
                        <component :is="icons.pos" class="w-4 h-4 mr-2" />
                        Ir al POS
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Métricas del día -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                        <component :is="icons.sales" class="w-5 h-5 text-green-600 dark:text-green-400" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Ventas Brutas</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">${{ formatPrice(metrics.today_sales) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                        <component :is="icons.receipt" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Transacciones</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ metrics.today_transactions }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                                        <component :is="icons.chart" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Ticket Promedio</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                        ${{ formatPrice(metrics.today_transactions > 0 ? metrics.today_sales / metrics.today_transactions : 0) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MÉTRICA: Devoluciones del día -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                        <component :is="icons.refund" class="w-5 h-5 text-red-600 dark:text-red-400" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Devoluciones</div>
                                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">${{ formatPrice(metrics.today_returns || 0) }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ metrics.today_return_count || 0 }} transacciones</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ventas Netas -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900 rounded-full flex items-center justify-center">
                                        <component :is="icons.cash" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Ventas Netas</div>
                                    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">${{ formatPrice(metrics.net_sales_today || (metrics.today_sales - (metrics.today_returns || 0))) }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ formatPrice(metrics.return_rate_today || 0) }}% tasa devolución</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros MEJORADOS -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Desde</label>
                                <input
                                    v-model="form.date_from"
                                    @change="search"
                                    type="date"
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Hasta</label>
                                <input
                                    v-model="form.date_to"
                                    @change="search"
                                    type="date"
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                                <select
                                    v-model="form.status"
                                    @change="search"
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="completada">Completada</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                            </div>

                            <!-- 🔄 NUEVO FILTRO: Devoluciones -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Devoluciones</label>
                                <select
                                    v-model="form.has_returns"
                                    @change="search"
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">Todas las ventas</option>
                                    <option value="with_returns">Con devoluciones</option>
                                    <option value="without_returns">Sin devoluciones</option>
                                    <option value="partial_returns">Devoluciones parciales</option>
                                    <option value="full_returns">Devoluciones totales</option>
                                </select>
                            </div>

                            <div class="flex items-end">
                                <button
                                    @click="clearFilters"
                                    class="w-full bg-gray-500 hover:bg-gray-700 dark:bg-gray-600 dark:hover:bg-gray-500 text-white font-bold py-2 px-4 rounded"
                                >
                                    Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de ventas -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Ticket
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Fecha
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Items
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Total / Neto
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Pago / Estado
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Estado Venta
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Mesa
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Cajero
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                #{{ sale.sale_number }}
                                            </div>
                                            <!-- Indicador visual de devoluciones -->
                                            <div v-if="sale.has_returns" class="text-xs text-orange-600 dark:text-orange-400 font-medium flex items-center">
                                                <component :is="icons.returns" class="w-3 h-3 mr-1" />
                                                {{ sale.return_percentage }}% devuelto
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                {{ formatDate(sale.created_at) }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ formatTime(sale.created_at) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                {{ sale.sale_items.length }} artículos
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ getTotalQuantity(sale.sale_items) }} unidades
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-green-600 dark:text-green-400">
                                                ${{ formatPrice(sale.total) }}
                                            </div>
                                            <div v-if="sale.discount > 0" class="text-sm text-gray-500 dark:text-gray-400">
                                                Desc: ${{ formatPrice(sale.discount) }}
                                            </div>
                                            <!-- 🔄 MEJORADO: Mostrar devoluciones y neto -->
                                            <div v-if="sale.has_returns" class="text-sm text-red-600 dark:text-red-400">
                                                Devuelto: -${{ formatPrice(sale.total_returned) }}
                                            </div>
                                            <div v-if="sale.has_returns" class="text-xs text-blue-600 dark:text-blue-400 font-medium border-t border-gray-200 dark:border-gray-600 pt-1 mt-1">
                                                Neto: ${{ formatPrice(sale.net_total) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col space-y-1">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 gap-1">
                                                    <component :is="getPaymentIcon(sale.payment_method)" class="w-3 h-3" />
                                                    {{ sale.payment_method }}
                                                </span>
                                                <!-- Badge de devoluciones más informativo -->
                                                <span v-if="sale.has_returns" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-300 gap-1">
                                                    <component :is="icons.refund" class="w-3 h-3" />
                                                    ${{ formatPrice(sale.total_returned) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium gap-1"
                                                :class="{
                                                    'bg-green-100 text-green-800': sale.status === 'completada',
                                                    'bg-yellow-100 text-yellow-800': sale.status === 'pendiente',
                                                    'bg-red-100 text-red-800': sale.status === 'cancelada'
                                                }"
                                            >
                                                <component :is="getStatusIcon(sale.status, true)" class="w-4 h-4" />
                                                {{ sale.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <div v-if="sale.table" class="flex items-center">
                                                <svg class="w-4 h-4 mr-1 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                <span class="font-medium">{{ sale.table.table_number }}</span>
                                                <span v-if="sale.table.name" class="text-gray-500 dark:text-gray-400 ml-1 text-xs">{{ sale.table.name }}</span>
                                            </div>
                                            <div v-else class="text-gray-400 text-xs">Para llevar</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ sale.user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex flex-col space-y-1">
                                                <Link
                                                    :href="route('sales.show', sale.id)"
                                                    class="text-indigo-600 hover:text-indigo-900 inline-flex items-center"
                                                >
                                                    <component :is="icons.view" class="w-4 h-4 mr-1" />
                                                    Ver Detalle
                                                </Link>
                                                <!-- Enlace a devoluciones -->
                                                <Link
                                                    v-if="sale.can_return"
                                                    :href="route('returns.create', { sale_id: sale.id })"
                                                    class="text-orange-600 hover:text-orange-900 inline-flex items-center"
                                                >
                                                    <component :is="icons.refund" class="w-4 h-4 mr-1" />
                                                    Devolver
                                                </Link>
                                                <span v-else-if="sale.status === 'completada' && sale.has_returns" class="text-gray-400 text-xs">
                                                    {{ sale.total_returned >= sale.total ? 'Totalmente devuelto' : 'Parcialmente devuelto' }}
                                                </span>
                                                <span v-else-if="sale.status !== 'completada'" class="text-gray-400 text-xs">
                                                    Solo ventas completadas
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mensaje si no hay ventas -->
                        <div v-if="sales.data.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
                            <component :is="icons.receipt" class="w-16 h-16 mx-auto mb-4 text-gray-400 dark:text-gray-600" />
                            <div>No se encontraron ventas</div>
                            <Link
                                :href="route('sales.pos')"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-700 text-white font-bold rounded gap-2"
                            >
                                <component :is="icons.pos" class="w-5 h-5" />
                                Realizar primera venta
                            </Link>
                        </div>

                        <!-- Paginación -->
                        <div class="mt-6" v-if="sales.links && sales.links.length > 3">
                            <nav class="flex items-center justify-between">
                                <div class="flex justify-between flex-1 sm:hidden">
                                    <Link
                                        v-if="sales.prev_page_url"
                                        :href="sales.prev_page_url"
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:text-gray-400"
                                    >
                                        Anterior
                                    </Link>
                                    <Link
                                        v-if="sales.next_page_url"
                                        :href="sales.next_page_url"
                                        class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:text-gray-400"
                                    >
                                        Siguiente
                                    </Link>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            Mostrando
                                            <span class="font-medium">{{ sales.from }}</span>
                                            a
                                            <span class="font-medium">{{ sales.to }}</span>
                                            de
                                            <span class="font-medium">{{ sales.total }}</span>
                                            ventas
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                            <template v-for="(link, index) in sales.links" :key="index">
                                                <Link
                                                    v-if="link.url"
                                                    :href="link.url"
                                                    v-html="link.label"
                                                    class="relative inline-flex items-center px-2 py-2 text-sm font-medium transition-colors"
                                                    :class="[
                                                        link.active
                                                            ? 'z-10 bg-indigo-50 dark:bg-indigo-900 border-indigo-500 text-indigo-600 dark:text-indigo-300'
                                                            : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                                                        index === 0 ? 'rounded-l-md' : '',
                                                        index === sales.links.length - 1 ? 'rounded-r-md' : '',
                                                        'border'
                                                    ]"
                                                />
                                                <span
                                                    v-else
                                                    v-html="link.label"
                                                    class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-300 dark:text-gray-600 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 cursor-default"
                                                />
                                            </template>
                                        </nav>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Resumen de devoluciones más detallado -->
                <div v-if="hasReturns || showReturnsSummary" class="mt-6 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 border border-orange-200 dark:border-orange-700 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-orange-900 dark:text-orange-300 mb-4 flex items-center">
                        <component :is="icons.chart" class="w-5 h-5 mr-2" />
                        Resumen de Devoluciones
                        <span v-if="activeFilters.has_returns" class="ml-2 text-sm bg-orange-200 dark:bg-orange-800 text-orange-800 dark:text-orange-200 px-2 py-1 rounded">
                            Filtro: {{ getReturnFilterText(activeFilters.has_returns) }}
                        </span>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="text-center bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                            <div class="text-2xl font-bold text-orange-700 dark:text-orange-400">{{ salesWithReturns }}</div>
                            <div class="text-sm text-orange-600 dark:text-orange-400">Ventas con devoluciones</div>
                        </div>
                        <div class="text-center bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                            <div class="text-2xl font-bold text-red-700 dark:text-red-400">${{ formatPrice(totalReturned) }}</div>
                            <div class="text-sm text-red-600 dark:text-red-400">Total devuelto</div>
                        </div>
                        <div class="text-center bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                            <div class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ returnPercentage }}%</div>
                            <div class="text-sm text-blue-600 dark:text-blue-400">Tasa de devolución</div>
                        </div>
                        <div class="text-center bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                            <div class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">${{ formatPrice(netSales) }}</div>
                            <div class="text-sm text-emerald-600 dark:text-emerald-400">Ventas netas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useIcons } from '@/composables/useIcons';

// Props
const props = defineProps({
    sales: Object,
    filters: Object,
    metrics: Object
});

// Icons
const { icons, getPaymentIcon, getStatusIcon } = useIcons();

// Form para filtros ACTUALIZADO
const form = reactive({
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    status: props.filters.status || '',
    has_returns: props.filters.has_returns || ''
});

// 🔄 NUEVO: Computed para filtros activos
const activeFilters = computed(() => props.filters);

// 🔄 MEJORADOS: Computed para análisis de devoluciones
const hasReturns = computed(() => {
    return props.sales.data.some(sale => sale.has_returns);
});

const showReturnsSummary = computed(() => {
    return hasReturns.value || activeFilters.value.has_returns;
});

const salesWithReturns = computed(() => {
    return props.sales.data.filter(sale => sale.has_returns).length;
});

const totalReturned = computed(() => {
    return props.sales.data.reduce((sum, sale) => sum + (sale.total_returned || 0), 0);
});

const totalSales = computed(() => {
    return props.sales.data.reduce((sum, sale) => sum + sale.total, 0);
});

const netSales = computed(() => {
    return totalSales.value - totalReturned.value;
});

const returnPercentage = computed(() => {
    if (totalSales.value === 0 || totalReturned.value === 0) return '0.0';
    const percentage = (totalReturned.value / totalSales.value) * 100;
    return isNaN(percentage) ? '0.0' : percentage.toFixed(1);
});

// Funciones
const search = () => {
    router.get(route('sales.index'), form, {
        preserveState: true,
        replace: true
    });
};

const clearFilters = () => {
    form.date_from = '';
    form.date_to = '';
    form.status = '';
    form.has_returns = ''; // 🔄 NUEVO
    search();
};

const formatPrice = (price) => {
    return parseFloat(price || 0).toFixed(2);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getTotalQuantity = (saleItems) => {
    return saleItems.reduce((total, item) => total + item.quantity, 0);
};

const getReturnFilterText = (filterValue) => {
    const texts = {
        'with_returns': 'Con devoluciones',
        'without_returns': 'Sin devoluciones',
        'partial_returns': 'Devoluciones parciales',
        'full_returns': 'Devoluciones totales'
    };
    return texts[filterValue] || 'Todas';
};
</script>
