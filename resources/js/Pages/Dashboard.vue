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

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                <Link :href="route('menu.index')" class="block">
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