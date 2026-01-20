<template>
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Detalles de Sesión de Caja
                </h2>
                <span :class="[
                    'px-3 py-1 text-xs font-semibold rounded-full',
                    session.status === 'open' ? 'text-green-800 bg-green-100 dark:text-green-300 dark:bg-green-900/50' : 'text-gray-800 bg-gray-100 dark:text-gray-300 dark:bg-gray-700'
                ]">
                    {{ session.status === 'open' ? '● Abierta' : '● Cerrada' }}
                </span>
            </div>
        </template>

        <div class="space-y-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Resumen Principal -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Monto Inicial -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Monto Inicial</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">${{ formatPrice(session.opening_amount) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ formatDateTime(session.opened_at) }}</p>
                        </div>
                    </div>

                    <!-- Monto de Cierre (si está cerrada) -->
                    <div v-if="session.status === 'closed'" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Monto Final</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">${{ formatPrice(session.closing_amount) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ formatDateTime(session.closed_at) }}</p>
                        </div>
                    </div>

                    <!-- Diferencia (si está cerrada) -->
                    <div v-if="session.status === 'closed'" :class="[
                        'overflow-hidden shadow-sm sm:rounded-lg border',
                        toNumber(session.difference) == 0 ? 'bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-800' :
                        toNumber(session.difference) > 0 ? 'bg-yellow-50 dark:bg-yellow-900/30 border-yellow-200 dark:border-yellow-800' :
                        'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800'
                    ]">
                        <div class="p-6">
                            <p class="text-sm font-medium mb-2" :class="[
                                toNumber(session.difference) == 0 ? 'text-green-700 dark:text-green-400' :
                                toNumber(session.difference) > 0 ? 'text-yellow-700 dark:text-yellow-400' :
                                'text-red-700 dark:text-red-400'
                            ]">
                                {{ toNumber(session.difference) == 0 ? '✓ Caja Cuadrada' : toNumber(session.difference) > 0 ? '⚠ Sobrante' : '✗ Faltante' }}
                            </p>
                            <p class="text-3xl font-bold" :class="[
                                toNumber(session.difference) == 0 ? 'text-green-900 dark:text-green-300' :
                                toNumber(session.difference) > 0 ? 'text-yellow-900 dark:text-yellow-300' :
                                'text-red-900 dark:text-red-300'
                            ]">
                                ${{ Math.abs(toNumber(session.difference)).toFixed(2) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Detalles de Sesión -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Información General -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información General</h3>
                            <dl class="space-y-3">
                                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                    <dt class="text-sm text-gray-600 dark:text-gray-400">Cajero:</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ session.user.name }}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                    <dt class="text-sm text-gray-600 dark:text-gray-400">Apertura:</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDateTime(session.opened_at) }}</dd>
                                </div>
                                <div v-if="session.status === 'closed'" class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                    <dt class="text-sm text-gray-600 dark:text-gray-400">Cierre:</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDateTime(session.closed_at) }}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                    <dt class="text-sm text-gray-600 dark:text-gray-400">Duración:</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ duration_hours }} horas</dd>
                                </div>
                                <div class="flex justify-between py-2">
                                    <dt class="text-sm text-gray-600 dark:text-gray-400">Transacciones:</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ transaction_count }}</dd>
                                </div>
                            </dl>

                            <!-- Notas -->
                            <div v-if="session.opening_notes" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notas de Apertura:</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 p-3 rounded">{{ session.opening_notes }}</p>
                            </div>
                            <div v-if="session.closing_notes" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notas de Cierre:</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 p-3 rounded">{{ session.closing_notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Desglose Financiero -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Desglose Financiero</h3>

                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        Efectivo
                                    </span>
                                    <span class="text-lg font-semibold text-green-600 dark:text-green-400">${{ formatPrice(total_cash_sales) }}</span>
                                </div>

                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        Tarjeta
                                    </span>
                                    <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">${{ formatPrice(total_card_sales) }}</span>
                                </div>

                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                        Transferencia
                                    </span>
                                    <span class="text-lg font-semibold text-purple-600 dark:text-purple-400">${{ formatPrice(total_transfer_sales) }}</span>
                                </div>

                                <div class="flex justify-between items-center py-3 pt-4 border-t-2 border-gray-300 dark:border-gray-600">
                                    <span class="text-base font-semibold text-gray-900 dark:text-white">Total Ventas</span>
                                    <span class="text-xl font-bold text-gray-900 dark:text-white">${{ formatPrice(total_all_sales) }}</span>
                                </div>
                            </div>

                            <!-- Resumen de Efectivo (si está cerrada) -->
                            <div v-if="session.status === 'closed'" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Resumen de Efectivo</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Inicial:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">${{ formatPrice(session.opening_amount) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">+ Ventas en Efectivo:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">${{ formatPrice(total_cash_sales) }}</span>
                                    </div>
                                    <div class="flex justify-between font-semibold pt-2 border-t border-gray-200 dark:border-gray-700">
                                        <span class="text-gray-900 dark:text-white">Esperado:</span>
                                        <span class="text-gray-900 dark:text-white">${{ formatPrice(session.expected_amount) }}</span>
                                    </div>
                                    <div class="flex justify-between pt-2">
                                        <span class="text-gray-600 dark:text-gray-400">Contado:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">${{ formatPrice(session.closing_amount) }}</span>
                                    </div>
                                    <div class="flex justify-between font-bold pt-2 border-t-2 border-gray-300 dark:border-gray-600" :class="[
                                        toNumber(session.difference) == 0 ? 'text-green-700 dark:text-green-400' :
                                        toNumber(session.difference) > 0 ? 'text-yellow-700 dark:text-yellow-400' :
                                        'text-red-700 dark:text-red-400'
                                    ]">
                                        <span>Diferencia:</span>
                                        <span>${{ formatPrice(session.difference) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Ventas -->
                <div v-if="sales && sales.length > 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Ventas de esta Sesión ({{ sales.length }})
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            # Venta
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Fecha
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Método
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="sale in sales" :key="sale.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600 dark:text-blue-400">
                                            <Link :href="route('sales.show', sale.id)" class="hover:underline">
                                                {{ sale.sale_number }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatTime(sale.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[
                                                'px-2 py-1 text-xs font-semibold rounded-full',
                                                sale.payment_method === 'efectivo' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' :
                                                sale.payment_method === 'tarjeta' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' :
                                                'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300'
                                            ]">
                                                {{ sale.payment_method }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
                                            ${{ formatPrice(sale.total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="flex justify-between">
                    <Link
                        :href="route('cashregister.index')"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:focus:ring-offset-gray-900"
                    >
                        ← Volver
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    session: Object,
    total_cash_sales: Number,
    total_card_sales: Number,
    total_transfer_sales: Number,
    total_all_sales: Number,
    transaction_count: Number,
    duration_hours: Number,
    sales: Array,
});

const toNumber = (value) => {
    return parseFloat(value) || 0;
};

const formatPrice = (value) => {
    return toNumber(value).toFixed(2);
};

const formatDateTime = (datetime) => {
    if (!datetime) return '';
    return new Date(datetime).toLocaleString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatTime = (datetime) => {
    if (!datetime) return '';
    return new Date(datetime).toLocaleString('es-MX', {
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>
