<template>
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Gestión de Caja
                </h2>
                <div v-if="currentSession" class="flex items-center space-x-2">
                    <span class="px-3 py-1 text-xs font-semibold text-green-800 bg-green-100 dark:bg-green-900 rounded-full">
                        ● Caja Abierta
                    </span>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <div class="mx-auto max-w-7xl">
                <!-- Sin Sesión Abierta -->
                <div v-if="!currentSession">
                    <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                        <div class="p-12 text-center">
                            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                                <svg class="h-10 w-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">No hay sesión de caja abierta</h3>
                            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                                Debes abrir una caja antes de poder procesar ventas
                            </p>
                            <Link
                                :href="route('cashregister.create')"
                                class="inline-flex items-center px-6 py-3 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                                Abrir Caja
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Con Sesión Abierta -->
                <div v-else class="space-y-6">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Monto Inicial -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-md p-3">
                                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Monto Inicial</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">${{ formatPrice(currentSession.opening_amount) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ventas en Efectivo -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-md p-3">
                                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ventas Efectivo</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">${{ formatPrice(currentSession.total_cash_sales) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Ventas -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900 rounded-md p-3">
                                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Ventas</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">${{ formatPrice(currentSession.total_all_sales) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transacciones -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-yellow-100 dark:bg-yellow-900 rounded-md p-3">
                                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Transacciones</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ currentSession.transaction_count }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles de Sesión -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Información de Sesión -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información de Sesión</h3>
                                <dl class="space-y-3">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Cajero:</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ currentSession.user.name }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Apertura:</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDateTime(currentSession.opened_at) }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Duración:</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ currentSession.duration_hours }} horas</dd>
                                    </div>
                                    <div v-if="currentSession.opening_notes" class="pt-3 border-t border-gray-200 dark:border-gray-700">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400 mb-1">Notas de Apertura:</dt>
                                        <dd class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 p-2 rounded">{{ currentSession.opening_notes }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Desglose por Método de Pago -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Desglose por Método de Pago</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Efectivo</span>
                                        <span class="text-sm font-semibold text-green-600 dark:text-green-400">${{ formatPrice(currentSession.total_cash_sales) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Tarjeta</span>
                                        <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">${{ formatPrice(currentSession.total_card_sales) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Transferencia</span>
                                        <span class="text-sm font-semibold text-purple-600 dark:text-purple-400">${{ formatPrice(currentSession.total_transfer_sales) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center pt-2 border-t-2 border-gray-300 dark:border-gray-600">
                                        <span class="text-base font-semibold text-gray-900 dark:text-white">Total</span>
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">${{ formatPrice(currentSession.total_all_sales) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Acciones</h3>
                            <div class="flex flex-wrap gap-3">
                                <Link
                                    :href="route('sales.pos')"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Ir al POS
                                </Link>

                                <Link
                                    :href="route('sales.index')"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    Ver Ventas
                                </Link>

                                <!-- Botones solo para Admin -->
                                <template v-if="isAdmin()">
                                    <Link
                                        :href="route('cashregister.history')"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Historial
                                    </Link>

                                    <Link
                                        :href="route('cashregister.stats')"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        Estadísticas
                                    </Link>
                                </template>

                                <Link
                                    :href="route('cashregister.close.form')"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Cerrar Caja
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    currentSession: Object,
});

const page = usePage();

const isAdmin = () => {
    return page.props.auth.user.role === 'admin';
};

const formatPrice = (value) => {
    return parseFloat(value || 0).toFixed(2);
};

const formatDateTime = (datetime) => {
    return new Date(datetime).toLocaleString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>
