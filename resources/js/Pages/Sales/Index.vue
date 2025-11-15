<template>
    <Head title="Historial de Ventas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    📊 Historial de Ventas
                </h2>
                <div class="flex space-x-2">
                    <Link 
                        :href="route('returns.index')"
                        class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded"
                    >
                        🔄 Devoluciones
                    </Link>
                    <Link 
                        :href="route('sales.pos')"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                    >
                        💰 Ir al POS
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Métricas del día -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <span class="text-green-600 text-lg">💰</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Ventas Brutas</div>
                                    <div class="text-2xl font-bold text-gray-900">${{ formatPrice(metrics.today_sales) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-blue-600 text-lg">🧾</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Transacciones</div>
                                    <div class="text-2xl font-bold text-gray-900">{{ metrics.today_transactions }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                        <span class="text-purple-600 text-lg">📈</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Ticket Promedio</div>
                                    <div class="text-2xl font-bold text-gray-900">
                                        ${{ formatPrice(metrics.today_transactions > 0 ? metrics.today_sales / metrics.today_transactions : 0) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 🔄 MÉTRICA: Devoluciones del día -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 text-lg">↩️</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Devoluciones</div>
                                    <div class="text-2xl font-bold text-red-600">${{ formatPrice(metrics.today_returns || 0) }}</div>
                                    <div class="text-xs text-gray-500">{{ metrics.today_return_count || 0 }} transacciones</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 🔄 NUEVA: Ventas Netas -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center">
                                        <span class="text-emerald-600 text-lg">💵</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Ventas Netas</div>
                                    <div class="text-2xl font-bold text-emerald-600">${{ formatPrice(metrics.net_sales_today || (metrics.today_sales - (metrics.today_returns || 0))) }}</div>
                                    <div class="text-xs text-gray-500">{{ formatPrice(metrics.return_rate_today || 0) }}% tasa devolución</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros MEJORADOS -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                                <input
                                    v-model="form.date_from"
                                    @change="search"
                                    type="date"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                                <input
                                    v-model="form.date_to"
                                    @change="search"
                                    type="date"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select
                                    v-model="form.status"
                                    @change="search"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="completada">Completada</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                            </div>

                            <!-- 🔄 NUEVO FILTRO: Devoluciones -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Devoluciones</label>
                                <select
                                    v-model="form.has_returns"
                                    @change="search"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
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
                                    class="w-full bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de ventas -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ticket
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Fecha
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Items
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total / Neto
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Pago / Estado
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Estado Venta
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cajero
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                #{{ sale.sale_number }}
                                            </div>
                                            <!-- 🔄 MEJORADO: Indicador visual de devoluciones -->
                                            <div v-if="sale.has_returns" class="text-xs text-orange-600 font-medium flex items-center">
                                                🔄 {{ sale.return_percentage }}% devuelto
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ formatDate(sale.created_at) }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ formatTime(sale.created_at) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ sale.sale_items.length }} artículos
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ getTotalQuantity(sale.sale_items) }} unidades
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-green-600">
                                                ${{ formatPrice(sale.total) }}
                                            </div>
                                            <div v-if="sale.discount > 0" class="text-sm text-gray-500">
                                                Desc: ${{ formatPrice(sale.discount) }}
                                            </div>
                                            <!-- 🔄 MEJORADO: Mostrar devoluciones y neto -->
                                            <div v-if="sale.has_returns" class="text-sm text-red-600">
                                                Devuelto: -${{ formatPrice(sale.total_returned) }}
                                            </div>
                                            <div v-if="sale.has_returns" class="text-xs text-blue-600 font-medium border-t pt-1 mt-1">
                                                Neto: ${{ formatPrice(sale.net_total) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col space-y-1">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ getPaymentIcon(sale.payment_method) }} {{ sale.payment_method }}
                                                </span>
                                                <!-- 🔄 MEJORADO: Badge de devoluciones más informativo -->
                                                <span v-if="sale.has_returns" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                    ↩️ ${{ formatPrice(sale.total_returned) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="{
                                                    'bg-green-100 text-green-800': sale.status === 'completada',
                                                    'bg-yellow-100 text-yellow-800': sale.status === 'pendiente',
                                                    'bg-red-100 text-red-800': sale.status === 'cancelada'
                                                }"
                                            >
                                                {{ getStatusIcon(sale.status) }} {{ sale.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ sale.user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex flex-col space-y-1">
                                                <Link
                                                    :href="route('sales.show', sale.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    👁️ Ver Detalle
                                                </Link>
                                                <!-- 🔄 MEJORADO: Enlace a devoluciones más inteligente -->
                                                <Link
                                                    v-if="sale.can_return"
                                                    :href="route('returns.create', { sale_id: sale.id })"
                                                    class="text-orange-600 hover:text-orange-900"
                                                >
                                                    ↩️ Devolver
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
                        <div v-if="sales.data.length === 0" class="text-center py-12 text-gray-500">
                            <div class="text-4xl mb-4">🧾</div>
                            <div>No se encontraron ventas</div>
                            <Link
                                :href="route('sales.pos')"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-700 text-white font-bold rounded"
                            >
                                💰 Realizar primera venta
                            </Link>
                        </div>

                        <!-- Paginación -->
                        <div class="mt-6" v-if="sales.links && sales.links.length > 3">
                            <nav class="flex items-center justify-between">
                                <div class="flex justify-between flex-1 sm:hidden">
                                    <Link
                                        v-if="sales.prev_page_url"
                                        :href="sales.prev_page_url"
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:text-gray-400"
                                    >
                                        Anterior
                                    </Link>
                                    <Link
                                        v-if="sales.next_page_url"
                                        :href="sales.next_page_url"
                                        class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:text-gray-400"
                                    >
                                        Siguiente
                                    </Link>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
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
                                                            ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                                        index === 0 ? 'rounded-l-md' : '',
                                                        index === sales.links.length - 1 ? 'rounded-r-md' : '',
                                                        'border'
                                                    ]"
                                                />
                                                <span
                                                    v-else
                                                    v-html="link.label"
                                                    class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300 cursor-default"
                                                />
                                            </template>
                                        </nav>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- 🔄 MEJORADO: Resumen de devoluciones más detallado -->
                <div v-if="hasReturns || showReturnsSummary" class="mt-6 bg-gradient-to-r from-orange-50 to-red-50 border border-orange-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-orange-900 mb-4 flex items-center">
                        📊 Resumen de Devoluciones
                        <span v-if="activeFilters.has_returns" class="ml-2 text-sm bg-orange-200 text-orange-800 px-2 py-1 rounded">
                            Filtro: {{ getReturnFilterText(activeFilters.has_returns) }}
                        </span>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="text-center bg-white rounded-lg p-4 shadow">
                            <div class="text-2xl font-bold text-orange-700">{{ salesWithReturns }}</div>
                            <div class="text-sm text-orange-600">Ventas con devoluciones</div>
                        </div>
                        <div class="text-center bg-white rounded-lg p-4 shadow">
                            <div class="text-2xl font-bold text-red-700">${{ formatPrice(totalReturned) }}</div>
                            <div class="text-sm text-red-600">Total devuelto</div>
                        </div>
                        <div class="text-center bg-white rounded-lg p-4 shadow">
                            <div class="text-2xl font-bold text-blue-700">{{ returnPercentage }}%</div>
                            <div class="text-sm text-blue-600">Tasa de devolución</div>
                        </div>
                        <div class="text-center bg-white rounded-lg p-4 shadow">
                            <div class="text-2xl font-bold text-emerald-700">${{ formatPrice(netSales) }}</div>
                            <div class="text-sm text-emerald-600">Ventas netas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Props
const props = defineProps({
    sales: Object,
    filters: Object,
    metrics: Object
});

// Form para filtros ACTUALIZADO
const form = reactive({
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    status: props.filters.status || '',
    has_returns: props.filters.has_returns || '' // 🔄 NUEVO FILTRO
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

const getPaymentIcon = (method) => {
    const icons = {
        'efectivo': '💵',
        'tarjeta': '💳',
        'transferencia': '📱',
        'mixto': '🔄'
    };
    return icons[method] || '💰';
};

const getStatusIcon = (status) => {
    const icons = {
        'completada': '✅',
        'pendiente': '⏳',
        'cancelada': '❌'
    };
    return icons[status] || '📋';
};

// 🔄 NUEVA: Función para texto de filtro de devoluciones
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