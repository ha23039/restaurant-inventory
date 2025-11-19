<template>
    <Head :title="`Producto: ${product.name}`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link 
                        :href="route('inventory.products.index')"
                        class="text-gray-500 hover:text-gray-700"
                    >
                        ← Volver a Productos
                    </Link>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        👁️ {{ product.name }}
                    </h2>
                </div>
                <div class="flex space-x-2">
                    <Link
                        :href="route('inventory.products.edit', product.id)"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    >
                        ✏️ Editar
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Información principal -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Información General</h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                                        <dd class="text-sm text-gray-900">{{ product.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Categoría</dt>
                                        <dd class="text-sm text-gray-900">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :style="{ backgroundColor: product.category.color + '20', color: product.category.color }"
                                            >
                                                {{ product.category.name }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div v-if="product.description">
                                        <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                                        <dd class="text-sm text-gray-900">{{ product.description }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Unidad de Medida</dt>
                                        <dd class="text-sm text-gray-900">{{ product.unit_type }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                        <dd class="text-sm text-gray-900">
                                            <span 
                                                :class="product.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            >
                                                {{ product.is_active ? '✅ Activo' : '❌ Inactivo' }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Stock y Costos</h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Stock Actual</dt>
                                        <dd class="text-sm text-gray-900 font-bold">
                                            {{ product.current_stock }} {{ product.unit_type }}
                                            <span
                                                v-if="product.current_stock <= product.min_stock"
                                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                            >
                                                ⚠️ Stock Bajo
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Stock Mínimo</dt>
                                        <dd class="text-sm text-gray-900">{{ product.min_stock }} {{ product.unit_type }}</dd>
                                    </div>
                                    <div v-if="product.max_stock">
                                        <dt class="text-sm font-medium text-gray-500">Stock Máximo</dt>
                                        <dd class="text-sm text-gray-900">{{ product.max_stock }} {{ product.unit_type }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Costo Unitario</dt>
                                        <dd class="text-sm text-gray-900 font-bold">${{ product.unit_cost }}</dd>
                                    </div>
                                    <div v-if="product.expiry_date">
                                        <dt class="text-sm font-medium text-gray-500">Fecha de Vencimiento</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ formatDate(product.expiry_date) }}
                                            <span
                                                v-if="isExpired(product.expiry_date)"
                                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                            >
                                                🗓️ Vencido
                                            </span>
                                            <span
                                                v-else-if="isExpiringSoon(product.expiry_date)"
                                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                                            >
                                                ⏰ Vence Pronto
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimos movimientos -->
                <div v-if="product.inventory_movements && product.inventory_movements.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Últimos Movimientos</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="movement in product.inventory_movements.slice(0, 10)" :key="movement.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(movement.movement_date) }}
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
                                            {{ movement.quantity }} {{ product.unit_type }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ movement.user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ movement.reason }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recetas donde se usa -->
                <div v-if="product.recipes && product.recipes.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">🍔 Usado en Recetas</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="recipe in product.recipes" :key="recipe.id" class="border rounded-lg p-4">
                                <h4 class="font-medium text-gray-900">{{ recipe.menu_item.name }}</h4>
                                <p class="text-sm text-gray-500 mt-1">
                                    Cantidad: {{ recipe.quantity_needed }} {{ recipe.unit }}
                                </p>
                                <p class="text-sm text-gray-500">
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
