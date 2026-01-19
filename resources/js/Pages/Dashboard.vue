<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useIcons } from '@/composables/useIcons';
import SalesChart from '@/Components/Charts/SalesChart.vue';
import TopProductsChart from '@/Components/Charts/TopProductsChart.vue';
import PaymentMethodsChart from '@/Components/Charts/PaymentMethodsChart.vue';

const props = defineProps({
    metrics: Object,
    chartData: Object,
});

const page = usePage();
const { icons } = useIcons();

// Función para verificar permisos de rol
const canAccess = (allowedRoles) => {
    const userRole = page.props.auth.user.role;
    return allowedRoles.includes(userRole);
};

// Formatear moneda
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(amount);
};
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard - {{ $page.props.auth.user.name }} ({{ $page.props.auth.user.role }})
            </h2>
        </template>

        <div class="py-6 lg:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Atajos Rápidos (Móvil/Tablet) -->
                <div class="mb-6 lg:hidden">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">
                        Acceso Rápido
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <!-- POS -->
                        <Link
                            v-if="canAccess(['admin', 'cajero'])"
                            :href="route('sales.pos')"
                            class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl text-white shadow-lg shadow-green-500/30 active:scale-95 transition-transform"
                        >
                            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm font-semibold">POS</span>
                        </Link>

                        <!-- Cocina -->
                        <Link
                            v-if="canAccess(['admin', 'chef', 'cajero'])"
                            :href="route('kitchen.display')"
                            class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl text-white shadow-lg shadow-orange-500/30 active:scale-95 transition-transform"
                        >
                            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                            </svg>
                            <span class="text-sm font-semibold">Cocina</span>
                        </Link>

                        <!-- Mesas -->
                        <Link
                            v-if="canAccess(['admin', 'cajero'])"
                            :href="route('tables.index')"
                            class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl text-white shadow-lg shadow-blue-500/30 active:scale-95 transition-transform"
                        >
                            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                            </svg>
                            <span class="text-sm font-semibold">Mesas</span>
                        </Link>

                        <!-- Inventario -->
                        <Link
                            v-if="canAccess(['admin', 'almacenero'])"
                            :href="route('inventory.products.index')"
                            class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl text-white shadow-lg shadow-purple-500/30 active:scale-95 transition-transform"
                        >
                            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span class="text-sm font-semibold">Inventario</span>
                        </Link>

                        <!-- Gastos -->
                        <Link
                            v-if="canAccess(['admin'])"
                            :href="route('expenses.index')"
                            class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-red-500 to-pink-500 rounded-2xl text-white shadow-lg shadow-red-500/30 active:scale-95 transition-transform"
                        >
                            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-semibold">Gastos</span>
                        </Link>

                        <!-- Ventas -->
                        <Link
                            v-if="canAccess(['admin', 'cajero'])"
                            :href="route('sales.index')"
                            class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-2xl text-white shadow-lg shadow-emerald-500/30 active:scale-95 transition-transform"
                        >
                            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span class="text-sm font-semibold">Ventas</span>
                        </Link>

                        <!-- Carta/Menú -->
                        <Link
                            v-if="canAccess(['admin', 'chef'])"
                            :href="route('carta.items')"
                            class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl text-white shadow-lg shadow-amber-500/30 active:scale-95 transition-transform"
                        >
                            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span class="text-sm font-semibold">Carta</span>
                        </Link>

                        <!-- Reportes -->
                        <Link
                            v-if="canAccess(['admin'])"
                            :href="route('admin.reports')"
                            class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-indigo-500 to-violet-500 rounded-2xl text-white shadow-lg shadow-indigo-500/30 active:scale-95 transition-transform"
                        >
                            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span class="text-sm font-semibold">Reportes</span>
                        </Link>
                    </div>
                </div>

                <!-- Métricas principales -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- Stock Bajo (Admin + Almacenero) -->
                    <div v-if="canAccess(['admin', 'almacenero'])" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-300">Productos con Stock Bajo</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ metrics.low_stock_products }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ventas del Día (Admin + Cajero) -->
                    <div v-if="canAccess(['admin', 'cajero'])" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-300">Ventas del Día</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(metrics.today_sales) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transacciones del Día (Admin + Cajero) -->
                    <div v-if="canAccess(['admin', 'cajero'])" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-300">Transacciones del Día</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ metrics.today_transactions }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Flujo de Caja (Solo Admin) -->
                    <div v-if="canAccess(['admin'])" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-300">Saldo del Día</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(metrics.today_cash_flow) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficas de Análisis (Admin + Cajero) -->
                <div v-if="canAccess(['admin', 'cajero'])" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Gráfica de Ventas Semanales -->
                    <SalesChart
                        :data="chartData.weekSales"
                        :labels="chartData.weekLabels"
                    />

                    <!-- Gráfica de Productos Más Vendidos -->
                    <TopProductsChart
                        :products="chartData.topProducts"
                    />

                    <!-- Gráfica de Métodos de Pago -->
                    <PaymentMethodsChart
                        :data="chartData.paymentMethods"
                    />
                </div>

                <!-- Panel de acceso rápido -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-white">
                        <h3 class="text-lg font-semibold mb-4">¡Bienvenido al Sistema de Gestión de Restaurante!</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Acceso rápido según rol -->
                            <div v-if="canAccess(['admin', 'almacenero'])" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <Link :href="route('inventory.products.index')" class="block">
                                    <div class="flex items-center">
                                        <component :is="icons.product" class="w-5 h-5 text-blue-600 mr-2" />
                                        <h4 class="font-medium text-blue-600">Gestionar Productos</h4>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Ver y editar inventario de productos</p>
                                </Link>
                            </div>
                            <div v-if="canAccess(['admin', 'almacenero'])" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <Link :href="route('inventory.products.index')" class="block">
                                    <div class="flex items-center">
                                        <component :is="icons.list" class="w-5 h-5 text-blue-600 mr-2" />
                                        <h4 class="font-medium text-blue-600">Ver Todos los Productos</h4>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Lista completa del inventario</p>
                                </Link>
                            </div>
                            <div v-if="canAccess(['admin', 'cajero'])" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <Link :href="route('sales.index')" class="block">
                                    <div class="flex items-center">
                                        <component :is="icons.pos" class="w-5 h-5 text-green-600 mr-2" />
                                        <h4 class="font-medium text-green-600">Punto de Venta</h4>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Procesar ventas y generar tickets</p>
                                </Link>
                            </div>

                            <div v-if="canAccess(['admin', 'chef'])" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <Link :href="route('carta.index')" class="block">
                                    <div class="flex items-center">
                                        <component :is="icons.menu" class="w-5 h-5 text-purple-600 mr-2" />
                                        <h4 class="font-medium text-purple-600">Gestionar Recetas</h4>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Configurar ingredientes por platillo</p>
                                </Link>
                            </div>

                            <div v-if="canAccess(['admin'])" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <Link :href="route('admin.reports')" class="block">
                                    <div class="flex items-center">
                                        <component :is="icons.reports" class="w-5 h-5 text-red-600 mr-2" />
                                        <h4 class="font-medium text-red-600">Reportes</h4>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Ver análisis y reportes detallados</p>
                                </Link>
                            </div>

                            <div v-if="canAccess(['admin'])" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <Link :href="route('cashflow.index')" class="block">
                                    <div class="flex items-center">
                                        <component :is="icons.cashflow" class="w-5 h-5 text-yellow-600 mr-2" />
                                        <h4 class="font-medium text-yellow-600">Flujo de Caja</h4>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Controlar ingresos y gastos</p>
                                </Link>
                            </div>
                            <div v-if="canAccess(['admin', 'cajero'])" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <Link :href="route('returns.index')" class="block">
                                    <div class="flex items-center">
                                        <component :is="icons.returns" class="w-5 h-5 text-orange-600 mr-2" />
                                        <h4 class="font-medium text-orange-600">Gestionar Devoluciones</h4>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Procesar devoluciones y reembolsos</p>
                                </Link>
                            </div>

                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <Link :href="route('profile.edit')" class="block">
                                    <div class="flex items-center">
                                        <component :is="icons.settings" class="w-5 h-5 text-gray-600 mr-2" />
                                        <h4 class="font-medium text-gray-600 dark:text-gray-300">Mi Perfil</h4>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Actualizar información personal</p>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>