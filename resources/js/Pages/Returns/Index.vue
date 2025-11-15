<template>
    <Head title="Devoluciones" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🔄 Sistema de Devoluciones
                </h2>
                <div class="flex space-x-2">
                    <Link 
                        :href="route('sales.index')"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    >
                        📊 Historial de Ventas
                    </Link>
                    <Link 
                        :href="route('returns.create')"
                        class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded"
                    >
                        ➕ Nueva Devolución
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Métricas del día -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 text-lg">💰</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Devoluciones Hoy</div>
                                    <div class="text-2xl font-bold text-red-600">${{ formatPrice(metrics.today_returns || 0) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-blue-600 text-lg">📊</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Cantidad Hoy</div>
                                    <div class="text-2xl font-bold text-blue-600">{{ metrics.today_count || 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                        <span class="text-orange-600 text-lg">⏳</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Pendientes</div>
                                    <div class="text-2xl font-bold text-orange-600">{{ metrics.pending_count || 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <span class="text-green-600 text-lg">✅</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Total Devoluciones</div>
                                    <div class="text-2xl font-bold text-green-600">{{ returns.total || 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                                <input
                                    v-model="form.date_from"
                                    @change="search"
                                    type="date"
                                    class="w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                                <input
                                    v-model="form.date_to"
                                    @change="search"
                                    type="date"
                                    class="w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select
                                    v-model="form.status"
                                    @change="search"
                                    class="w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm"
                                >
                                    <option value="">Todos los estados</option>
                                    <option value="pending">Pendiente</option>
                                    <option value="completed">Completada</option>
                                    <option value="cancelled">Cancelada</option>
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

                <!-- Lista de devoluciones -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="returns.data && returns.data.length > 0">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Devolución
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Venta Original
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Fecha
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Monto
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Razón
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Estado
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Procesado por
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Acciones
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="returnItem in returns.data" :key="returnItem.id" class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    #{{ returnItem.return_number }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ returnItem.return_type === 'total' ? 'Total' : 'Parcial' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    #{{ returnItem.sale?.sale_number || 'N/A' }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    ${{ formatPrice(returnItem.sale?.total || 0) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ formatDate(returnItem.return_date) }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ formatTime(returnItem.created_at) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-bold text-red-600">
                                                    -${{ formatPrice(returnItem.total_returned) }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ returnItem.return_items?.length || 0 }} items
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ getReasonText(returnItem.reason) }}
                                                </div>
                                                <div class="text-sm text-gray-500 capitalize">
                                                    {{ returnItem.refund_method }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span 
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    :class="{
                                                        'bg-green-100 text-green-800': returnItem.status === 'completed',
                                                        'bg-yellow-100 text-yellow-800': returnItem.status === 'pending',
                                                        'bg-red-100 text-red-800': returnItem.status === 'cancelled'
                                                    }"
                                                >
                                                    {{ getStatusIcon(returnItem.status) }} {{ getStatusText(returnItem.status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ returnItem.processed_by_user?.name || 'Sistema' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <Link
                                                    :href="route('returns.show', returnItem.id)"
                                                    class="text-orange-600 hover:text-orange-900"
                                                >
                                                    👁️ Ver Detalle
                                                </Link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            <div class="mt-6" v-if="returns.links && returns.links.length > 3">
                                <nav class="flex items-center justify-between">
                                    <div class="flex justify-between flex-1 sm:hidden">
                                        <Link
                                            v-if="returns.prev_page_url"
                                            :href="returns.prev_page_url"
                                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:text-gray-400"
                                        >
                                            Anterior
                                        </Link>
                                        <Link
                                            v-if="returns.next_page_url"
                                            :href="returns.next_page_url"
                                            class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:text-gray-400"
                                        >
                                            Siguiente
                                        </Link>
                                    </div>
                                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm text-gray-700">
                                                Mostrando
                                                <span class="font-medium">{{ returns.from }}</span>
                                                a
                                                <span class="font-medium">{{ returns.to }}</span>
                                                de
                                                <span class="font-medium">{{ returns.total }}</span>
                                                devoluciones
                                            </p>
                                        </div>
                                        <div>
                                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                                <template v-for="(link, index) in returns.links" :key="index">
                                                    <Link
                                                        v-if="link.url"
                                                        :href="link.url"
                                                        v-html="link.label"
                                                        class="relative inline-flex items-center px-2 py-2 text-sm font-medium transition-colors"
                                                        :class="[
                                                            link.active
                                                                ? 'z-10 bg-orange-50 border-orange-500 text-orange-600'
                                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                                            index === 0 ? 'rounded-l-md' : '',
                                                            index === returns.links.length - 1 ? 'rounded-r-md' : '',
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
                        
                        <!-- Estado vacío -->
                        <div v-else class="text-center py-12 text-gray-500">
                            <div class="text-4xl mb-4">🔄</div>
                            <div class="text-lg font-medium">No hay devoluciones registradas</div>
                            <div class="text-sm mt-2">El sistema está listo para procesar devoluciones</div>
                            <Link 
                                :href="route('returns.create')" 
                                class="mt-4 inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-700 text-white font-bold rounded"
                            >
                                ➕ Procesar Primera Devolución
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Props del controlador
const props = defineProps({
    returns: Object,
    filters: Object,
    metrics: Object
});

// Form para filtros
const form = reactive({
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    status: props.filters?.status || ''
});

// Funciones
const search = () => {
    router.get(route('returns.index'), form, {
        preserveState: true,
        replace: true
    });
};

const clearFilters = () => {
    form.date_from = '';
    form.date_to = '';
    form.status = '';
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

const getReasonText = (reason) => {
    const reasons = {
        'defective': 'Producto defectuoso',
        'wrong_order': 'Orden incorrecta',
        'customer_request': 'Solicitud del cliente',
        'error': 'Error del sistema',
        'other': 'Otra razón'
    };
    return reasons[reason] || reason;
};

const getStatusText = (status) => {
    const statuses = {
        'pending': 'Pendiente',
        'completed': 'Completada',
        'cancelled': 'Cancelada'
    };
    return statuses[status] || status;
};

const getStatusIcon = (status) => {
    const icons = {
        'pending': '⏳',
        'completed': '✅',
        'cancelled': '❌'
    };
    return icons[status] || '📋';
};

// Debug
console.log('🔍 Returns Index - Props:', props);
</script>