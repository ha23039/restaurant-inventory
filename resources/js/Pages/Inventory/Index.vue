<template>
    <Head title="Dashboard de Inventario" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center">
                <component :is="icons.inventory" class="w-6 h-6 text-gray-800 dark:text-gray-200 mr-2" />
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Dashboard de Inventario
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Métricas principales -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
                    <!-- Total productos -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white overflow-hidden shadow-lg sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                                        <component :is="icons.product" class="w-5 h-5" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-blue-100">Total Productos</div>
                                    <div class="text-2xl font-bold">{{ metrics.total_products }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total categorías -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white overflow-hidden shadow-lg sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                                        <component :is="icons.category" class="w-5 h-5" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-purple-100">Categorías</div>
                                    <div class="text-2xl font-bold">{{ metrics.total_categories }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock bajo -->
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white overflow-hidden shadow-lg sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                                        <component :is="icons.warning" class="w-5 h-5" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-orange-100">Stock Bajo</div>
                                    <div class="text-2xl font-bold">{{ metrics.low_stock_products }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Productos vencidos -->
                    <div class="bg-gradient-to-br from-red-500 to-red-600 text-white overflow-hidden shadow-lg sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                                        <component :is="icons.calendar" class="w-5 h-5" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-red-100">Vencidos</div>
                                    <div class="text-2xl font-bold">{{ metrics.expired_products }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Por vencer pronto -->
                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white overflow-hidden shadow-lg sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                                        <component :is="icons.pending" class="w-5 h-5" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-yellow-100">Vencen Pronto</div>
                                    <div class="text-2xl font-bold">{{ metrics.expiring_soon_products }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
                    <Link
                        :href="route('inventory.products.index')"
                        class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-4 px-6 rounded-lg text-center transition-colors"
                    >
                        <component :is="icons.list" class="w-8 h-8 mx-auto mb-2" />
                        <div>Ver Productos</div>
                    </Link>

                    <Link
                        :href="route('inventory.products.create')"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg text-center transition-colors"
                    >
                        <component :is="icons.add" class="w-8 h-8 mx-auto mb-2" />
                        <div>Nuevo Producto</div>
                    </Link>

                    <Link
                        :href="route('inventory.categories.index')"
                        class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-4 px-6 rounded-lg text-center transition-colors"
                    >
                        <component :is="icons.category" class="w-8 h-8 mx-auto mb-2" />
                        <div>Gestionar Categorías</div>
                    </Link>

                    <Link
                        :href="route('inventory.products.index', { low_stock: 1 })"
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-lg text-center transition-colors"
                    >
                        <component :is="icons.alert" class="w-8 h-8 mx-auto mb-2" />
                        <div>Ver Alertas</div>
                    </Link>

                    <Link
                        :href="route('inventory.movements.index')"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-lg text-center transition-colors"
                    >
                        <component :is="icons.list" class="w-8 h-8 mx-auto mb-2" />
                        <div>Ver Movimientos</div>
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Alertas de productos -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <component :is="icons.alert" class="w-5 h-5 text-gray-900 dark:text-gray-100 mr-2" />
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Productos con Alertas</h3>
                                </div>
                                <Link
                                    :href="route('inventory.products.index', { low_stock: 1 })"
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm"
                                >
                                    Ver todos
                                </Link>
                            </div>

                            <div v-if="alert_products.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-8 flex flex-col items-center">
                                <component :is="icons.success" class="w-8 h-8 text-green-500 dark:text-green-400 mb-2" />
                                No hay productos con alertas
                            </div>

                            <div v-else class="space-y-3">
                                <div
                                    v-for="product in alert_products"
                                    :key="product.id"
                                    class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg"
                                >
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ product.name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ product.category.name }} • Stock: {{ product.current_stock }} {{ product.unit_type }}
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span
                                            v-if="product.current_stock <= product.min_stock"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200"
                                        >
                                            Stock Bajo
                                        </span>
                                        <span
                                            v-else-if="isExpired(product.expiry_date)"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200"
                                        >
                                            Vencido
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200"
                                        >
                                            Vence Pronto
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Productos por categoría -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <component :is="icons.pie" class="w-5 h-5 text-gray-900 dark:text-gray-100 mr-2" />
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Productos por Categoría</h3>
                                </div>
                                <Link
                                    :href="route('inventory.categories.index')"
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm"
                                >
                                    Gestionar
                                </Link>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="category in products_by_category"
                                    :key="category.id"
                                    class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg"
                                >
                                    <div class="flex items-center">
                                        <div
                                            class="w-4 h-4 rounded-full mr-3"
                                            :style="{ backgroundColor: category.color }"
                                        ></div>
                                        <div class="font-medium text-gray-900 dark:text-white">{{ category.name }}</div>
                                    </div>
                                    <div class="text-lg font-bold text-gray-600 dark:text-gray-400">
                                        {{ category.products_count }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimos movimientos -->
                <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <component :is="icons.list" class="w-5 h-5 text-gray-900 dark:text-gray-100 mr-2" />
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Últimos Movimientos</h3>
                            </div>
                            <Link
                                :href="route('inventory.movements.index')"
                                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm"
                            >
                                Ver historial completo
                            </Link>
                        </div>

                        <div v-if="recent_movements.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-8 flex flex-col items-center">
                            <component :is="icons.list" class="w-8 h-8 text-gray-400 dark:text-gray-600 mb-2" />
                            No hay movimientos registrados
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Producto
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Tipo
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Cantidad
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Usuario
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Fecha
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="movement in recent_movements" :key="movement.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ movement.product.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="{
                                                    'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200': movement.movement_type === 'entrada',
                                                    'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200': movement.movement_type === 'salida',
                                                    'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200': movement.movement_type === 'ajuste'
                                                }"
                                            >
                                                {{ movement.movement_type.charAt(0).toUpperCase() + movement.movement_type.slice(1) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            {{ movement.quantity }} {{ movement.product.unit_type }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ movement.user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(movement.movement_date) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useIcons } from '@/composables/useIcons';

const { icons } = useIcons();

// Props
defineProps({
    metrics: Object,
    alert_products: Array,
    recent_movements: Array,
    products_by_category: Array,
});

// Funciones auxiliares
const isExpired = (date) => {
    if (!date) return false;
    return new Date(date) < new Date();
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>
