<template>
    <Head :title="`Producto: ${product.name}`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link
                        :href="route('inventory.products.index')"
                        class="inline-flex items-center text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver a Productos
                    </Link>
                </div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ product.name }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Información principal -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información General</h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ product.name }}</dd>
                                    </div>
                                    <div v-if="product.category">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Categoría</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :style="{ backgroundColor: product.category.color + '20', color: product.category.color }"
                                            >
                                                {{ product.category.name }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div v-if="product.description">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Descripción</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-300">{{ product.description }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Unidad de Medida</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ product.unit_type }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">
                                            <span
                                                :class="product.is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            >
                                                {{ product.is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Stock y Costos</h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Stock Actual</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white font-bold">
                                            {{ product.current_stock }} {{ product.unit_type }}
                                            <span
                                                v-if="product.current_stock <= product.min_stock"
                                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200"
                                            >
                                                Stock Bajo
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Stock Mínimo</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-300">{{ product.min_stock }} {{ product.unit_type }}</dd>
                                    </div>
                                    <div v-if="product.max_stock">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Stock Máximo</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-300">{{ product.max_stock }} {{ product.unit_type }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Costo Unitario</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white font-bold">${{ product.unit_cost }}</dd>
                                    </div>
                                    <div v-if="product.expiry_date">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Vencimiento</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-300">
                                            {{ formatDate(product.expiry_date) }}
                                            <span
                                                v-if="isExpired(product.expiry_date)"
                                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200"
                                            >
                                                Vencido
                                            </span>
                                            <span
                                                v-else-if="isExpiringSoon(product.expiry_date)"
                                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200"
                                            >
                                                Vence Pronto
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimos movimientos -->
                <div v-if="product.inventory_movements && product.inventory_movements.length > 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Últimos Movimientos</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Usuario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Motivo</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="movement in product.inventory_movements.slice(0, 10)" :key="movement.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ formatDate(movement.movement_date) }}
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
                                            {{ movement.quantity }} {{ product.unit_type }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ movement.user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ movement.reason }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recetas donde se usa -->
                <div v-if="product.recipes && product.recipes.length > 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <component :is="icons.menu" class="w-5 h-5" />
                            Usado en Recetas
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="recipe in product.recipes" :key="recipe.id" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ recipe.menu_item.name }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    Cantidad: {{ recipe.quantity_needed }} {{ recipe.unit }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Precio: ${{ recipe.menu_item.price }}
                                </p>
                            </div>
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

// Icons
const { icons } = useIcons();

// Props
defineProps({
    product: Object
});

// Funciones auxiliares
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const isExpired = (date) => {
    if (!date) return false;
    return new Date(date) < new Date();
};

const isExpiringSoon = (date, days = 7) => {
    if (!date) return false;
    const expiryDate = new Date(date);
    const today = new Date();
    const diffTime = expiryDate - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays <= days && diffDays > 0;
};
</script>
