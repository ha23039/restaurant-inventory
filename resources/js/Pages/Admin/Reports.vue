<template>
    <Head title="Reportes" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-800 dark:text-gray-200 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Reportes y Análisis
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-3 md:py-6 lg:py-12">
            <div class="max-w-full mx-auto px-2 sm:px-6 lg:px-8">
                <!-- Grid de reportes -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Reporte de Ventas -->
                    <Link
                        :href="route('sales.index')"
                        class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 border-2 border-blue-200 dark:border-blue-700 rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer"
                    >
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-blue-500 dark:bg-blue-600 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-4 text-lg font-semibold text-blue-900 dark:text-blue-100">Ventas</h3>
                        </div>
                        <p class="text-sm text-blue-700 dark:text-blue-300">Historial completo de ventas, análisis por período, productos más vendidos</p>
                    </Link>

                    <!-- Reporte de Inventario -->
                    <Link
                        :href="route('inventory.products.index')"
                        class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900 dark:to-purple-800 border-2 border-purple-200 dark:border-purple-700 rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer"
                    >
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-purple-500 dark:bg-purple-600 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-4 text-lg font-semibold text-purple-900 dark:text-purple-100">Inventario</h3>
                        </div>
                        <p class="text-sm text-purple-700 dark:text-purple-300">Stock actual, productos críticos, movimientos de inventario</p>
                    </Link>

                    <!-- Reporte de Flujo de Caja -->
                    <Link
                        :href="route('cashflow.index')"
                        class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900 dark:to-green-800 border-2 border-green-200 dark:border-green-700 rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer"
                    >
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-green-500 dark:bg-green-600 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <h3 class="ml-4 text-lg font-semibold text-green-900 dark:text-green-100">Flujo de Caja</h3>
                        </div>
                        <p class="text-sm text-green-700 dark:text-green-300">Ingresos, gastos, balance general, proyecciones</p>
                    </Link>

                    <!-- Reporte de Devoluciones -->
                    <Link
                        :href="route('returns.index')"
                        class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900 dark:to-orange-800 border-2 border-orange-200 dark:border-orange-700 rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer"
                    >
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-orange-500 dark:bg-orange-600 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <h3 class="ml-4 text-lg font-semibold text-orange-900 dark:text-orange-100">Devoluciones</h3>
                        </div>
                        <p class="text-sm text-orange-700 dark:text-orange-300">Análisis de devoluciones, razones, impacto en ventas</p>
                    </Link>

                    <!-- Reporte de Caja Registradora -->
                    <div class="bg-gradient-to-br from-teal-50 to-teal-100 dark:from-teal-900 dark:to-teal-800 border-2 border-teal-200 dark:border-teal-700 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-teal-500 dark:bg-teal-600 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                    <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <h3 class="ml-4 text-lg font-semibold text-teal-900 dark:text-teal-100">Caja Registradora</h3>
                        </div>
                        <p class="text-sm text-teal-700 dark:text-teal-300 mb-3">Sesiones de caja, estadísticas, análisis de cajeros</p>
                        <div class="flex gap-2">
                            <Link
                                :href="route('cashregister.history')"
                                class="flex-1 px-3 py-2 text-xs font-medium text-teal-700 dark:text-teal-200 bg-teal-100 dark:bg-teal-800 rounded hover:bg-teal-200 dark:hover:bg-teal-700 transition-colors text-center"
                            >
                                Ver Historial
                            </Link>
                            <Link
                                :href="route('cashregister.stats')"
                                class="flex-1 px-3 py-2 text-xs font-medium text-teal-700 dark:text-teal-200 bg-teal-100 dark:bg-teal-800 rounded hover:bg-teal-200 dark:hover:bg-teal-700 transition-colors text-center"
                            >
                                Ver Estadísticas
                            </Link>
                        </div>
                    </div>

                    <!-- Productos Simples -->
                    <Link
                        :href="route('simple-products.index')"
                        class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900 dark:to-indigo-800 border-2 border-indigo-200 dark:border-indigo-700 rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer"
                    >
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-indigo-500 dark:bg-indigo-600 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-4 text-lg font-semibold text-indigo-900 dark:text-indigo-100">Productos Simples</h3>
                        </div>
                        <p class="text-sm text-indigo-700 dark:text-indigo-300">Gestión de productos individuales, stock y precios de venta</p>
                    </Link>

                    <!-- Gestión de Usuarios -->
                    <Link
                        :href="route('users.index')"
                        class="bg-gradient-to-br from-pink-50 to-pink-100 dark:from-pink-900 dark:to-pink-800 border-2 border-pink-200 dark:border-pink-700 rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer"
                    >
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-pink-500 dark:bg-pink-600 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-4 text-lg font-semibold text-pink-900 dark:text-pink-100">Gestión de Usuarios</h3>
                        </div>
                        <p class="text-sm text-pink-700 dark:text-pink-300">Administración de empleados, roles y permisos del sistema</p>
                    </Link>
                </div>

                <!-- Reportes Consolidados -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Reportes Consolidados</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Genera reportes ejecutivos con datos consolidados del negocio</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Reporte Ejecutivo Mensual -->
                            <button
                                @click="openConsolidatedReport('executive')"
                                class="flex flex-col items-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/30 border-2 border-blue-200 dark:border-blue-700 rounded-lg hover:shadow-md transition-all"
                            >
                                <div class="w-10 h-10 bg-blue-500 dark:bg-blue-600 rounded-full flex items-center justify-center mb-2">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-blue-900 dark:text-blue-100 text-center">Reporte Ejecutivo</span>
                            </button>

                            <!-- Reporte Financiero -->
                            <button
                                @click="openConsolidatedReport('financial')"
                                class="flex flex-col items-center p-4 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/30 border-2 border-green-200 dark:border-green-700 rounded-lg hover:shadow-md transition-all"
                            >
                                <div class="w-10 h-10 bg-green-500 dark:bg-green-600 rounded-full flex items-center justify-center mb-2">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-green-900 dark:text-green-100 text-center">Estado Financiero</span>
                            </button>

                            <!-- Reporte de Rentabilidad -->
                            <button
                                @click="openConsolidatedReport('profitability')"
                                class="flex flex-col items-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/30 border-2 border-purple-200 dark:border-purple-700 rounded-lg hover:shadow-md transition-all"
                            >
                                <div class="w-10 h-10 bg-purple-500 dark:bg-purple-600 rounded-full flex items-center justify-center mb-2">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-purple-900 dark:text-purple-100 text-center">Rentabilidad</span>
                            </button>

                            <!-- Reporte de Inventario -->
                            <button
                                @click="openConsolidatedReport('inventory')"
                                class="flex flex-col items-center p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/30 border-2 border-yellow-200 dark:border-yellow-700 rounded-lg hover:shadow-md transition-all"
                            >
                                <div class="w-10 h-10 bg-yellow-500 dark:bg-yellow-600 rounded-full flex items-center justify-center mb-2">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-yellow-900 dark:text-yellow-100 text-center">Inventario Valorizado</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-600 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                Haz clic en cualquier tarjeta para ver el reporte detallado. Los reportes consolidados se pueden exportar a PDF o Excel.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Consolidated Report SlideOver -->
        <ConsolidatedReportSlideOver
            :show="showConsolidatedReport"
            :report-type="selectedReportType"
            @close="closeConsolidatedReport"
        />
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConsolidatedReportSlideOver from '@/Components/Reports/ConsolidatedReportSlideOver.vue';

const showConsolidatedReport = ref(false);
const selectedReportType = ref('');

const openConsolidatedReport = (reportType) => {
    selectedReportType.value = reportType;
    showConsolidatedReport.value = true;
};

const closeConsolidatedReport = () => {
    showConsolidatedReport.value = false;
    selectedReportType.value = '';
};
</script>
