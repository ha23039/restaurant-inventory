<template>
    <Head title="Dashboard de Inventario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📦 Dashboard de Inventario
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Métricas principales -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
                    <!-- Total productos -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-blue-600 text-lg">📦</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Total Productos</div>
                                    <div class="text-2xl font-bold text-gray-900">{{ metrics.total_products }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total categorías -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                        <span class="text-purple-600 text-lg">🏷️</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Categorías</div>
                                    <div class="text-2xl font-bold text-gray-900">{{ metrics.total_categories }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock bajo -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 text-lg">⚠️</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Stock Bajo</div>
                                    <div class="text-2xl font-bold text-red-600">{{ metrics.low_stock_products }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Productos vencidos -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 text-lg">🗓️</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Vencidos</div>
                                    <div class="text-2xl font-bold text-red-600">{{ metrics.expired_products }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Por vencer pronto -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <span class="text-yellow-600 text-lg">⏰</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Vencen Pronto</div>
                                    <div class="text-2xl font-bold text-yellow-600">{{ metrics.expiring_soon_products }}</div>
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
                        <div class="text-2xl mb-2">📋</div>
                        <div>Ver Productos</div>
                    </Link>

                    <Link 
                        :href="route('inventory.products.create')"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg text-center transition-colors"
                    >
                        <div class="text-2xl mb-2">➕</div>
                        <div>Nuevo Producto</div>
                    </Link>

                    <Link 
                        :href="route('inventory.categories.index')"
                        class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-4 px-6 rounded-lg text-center transition-colors"
                    >
                        <div class="text-2xl mb-2">🏷️</div>
                        <div>Gestionar Categorías</div>
                    </Link>

                    <Link 
                        :href="route('inventory.products.index', { low_stock: 1 })"
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-lg text-center transition-colors"
                    >
                        <div class="text-2xl mb-2">⚠️</div>
                        <div>Ver Alertas</div>
                    </Link>

                    <Link 
                        :href="route('inventory.movements.index')"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-lg text-center transition-colors"
                    >
                        <div class="text-2xl mb-2">📋</div>
                        <div>Ver Movimientos</div>
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Alertas de productos -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">🚨 Productos con Alertas</h3>
                                <Link 
                                    :href="route('inventory.products.index', { low_stock: 1 })"
                                    class="text-blue-600 hover:text-blue-800 text-sm"
                                >
                                    Ver todos
                                </Link>
                            </div>
                            
                            <div v-if="alert_products.length === 0" class="text-gray-500 text-center py-8">
                                ✅ No hay productos con alertas
                            </div>
                            
                            <div v-else class="space-y-3">
                                <div 
                                    v-for="product in alert_products" 
                                    :key="product.id"
                                    class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
                                >
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">{{ product.name }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ product.category.name }} • Stock: {{ product.current_stock }} {{ product.unit_type }}
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span
                                            v-if="product.current_stock <= product.min_stock"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                        >
                                            Stock Bajo
                                        </span>
                                        <span
                                            v-else-if="isExpired(product.expiry_date)"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                        >
                                            Vencido
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                                        >
                                            Vence Pronto
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Productos por categoría -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">📊 Productos por Categoría</h3>
                                <Link 
                                    :href="route('inventory.categories.index')"
                                    class="text-blue-600 hover:text-blue-800 text-sm"
                                >
                                    Gestionar
                                </Link>
                            </div>
                            
                            <div class="space-y-3">
                                <div 
                                    v-for="category in products_by_category" 
                                    :key="category.id"
                                    class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
                                >
                                    <div class="flex items-center">
                                        <div 
                                            class="w-4 h-4 rounded-full mr-3"
                                            :style="{ backgroundColor: category.color }"
                                        ></div>
                                        <div class="font-medium text-gray-900">{{ category.name }}</div>
                                    </div>
                                    <div class="text-lg font-bold text-gray-600">
                                        {{ category.products_count }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimos movimientos -->
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">📋 Últimos Movimientos</h3>
                            <Link 
                                :href="route('inventory.movements.index')"
                                class="text-blue-600 hover:text-blue-800 text-sm"
                            >
                                Ver historial completo
                            </Link>
                        </div>
                        
                        <div v-if="recent_movements.length === 0" class="text-gray-500 text-center py-8">
                            📝 No hay movimientos registrados
                        </div>
                        
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Producto
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tipo
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cantidad
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Usuario
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Fecha
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="movement in recent_movements" :key="movement.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ movement.product.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="{
                                                    'bg-green-100 text-green-800': movement.movement_type === 'entrada',
                                                    'bg-red-100 text-red-800': movement.movement_type === 'salida',
                                                    'bg-blue-100 text-blue-800': movement.movement_type === 'ajuste'
                                                }"
                                            >
                                                {{ movement.movement_type.charAt(0).toUpperCase() + movement.movement_type.slice(1) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ movement.quantity }} {{ movement.product.unit_type }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ movement.user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
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
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

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