<template>
    <Head title="Gestión de Menú" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-800 dark:text-gray-200 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Gestión de Menú
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Grid de opciones del menú -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Platillos del Menú -->
                    <Link
                        :href="route('menu.items')"
                        class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/30 border-2 border-blue-200 dark:border-blue-700 rounded-lg p-6 hover:shadow-lg dark:hover:shadow-blue-900/20 transition-all transform hover:-translate-y-1"
                    >
                        <div class="flex items-center mb-4">
                            <div class="w-16 h-16 bg-blue-500 dark:bg-blue-600 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-blue-900 dark:text-blue-100 mb-2">Platillos del Menú</h3>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mb-4">Gestiona los platillos que se venden en el restaurante</p>
                        <div class="flex items-center text-blue-600 dark:text-blue-400 font-medium">
                            <span>Ver platillos</span>
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </Link>

                    <!-- Recetas -->
                    <Link
                        :href="route('menu.recipes')"
                        class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/30 border-2 border-purple-200 dark:border-purple-700 rounded-lg p-6 hover:shadow-lg dark:hover:shadow-purple-900/20 transition-all transform hover:-translate-y-1"
                    >
                        <div class="flex items-center mb-4">
                            <div class="w-16 h-16 bg-purple-500 dark:bg-purple-600 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"></path>
                                    <path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"></path>
                                    <path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-purple-900 dark:text-purple-100 mb-2">Recetas</h3>
                        <p class="text-sm text-purple-700 dark:text-purple-300 mb-4">Define ingredientes y cantidades para cada platillo</p>
                        <div class="flex items-center text-purple-600 dark:text-purple-400 font-medium">
                            <span>Ver recetas</span>
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </Link>

                    <!-- Productos Simples -->
                    <Link
                        :href="route('simple-products.index')"
                        class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/30 border-2 border-green-200 dark:border-green-700 rounded-lg p-6 hover:shadow-lg dark:hover:shadow-green-900/20 transition-all transform hover:-translate-y-1"
                    >
                        <div class="flex items-center mb-4">
                            <div class="w-16 h-16 bg-green-500 dark:bg-green-600 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-green-900 dark:text-green-100 mb-2">Productos Simples</h3>
                        <p class="text-sm text-green-700 dark:text-green-300 mb-4">Productos individuales sin receta (bebidas, extras)</p>
                        <div class="flex items-center text-green-600 dark:text-green-400 font-medium">
                            <span>Ver productos</span>
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </Link>
                </div>

                <!-- Info Section -->
                <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Gestión de Menú del Restaurante</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-200 mb-2">Platillos del Menú</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Son los platillos preparados que se venden en el restaurante. Cada platillo tiene una receta asociada que define qué ingredientes se necesitan y en qué cantidades.
                            </p>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-200 mb-2">Recetas</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Define la lista de materiales (BOM) para cada platillo. Cuando se vende un platillo, el sistema deduce automáticamente los ingredientes del inventario según la receta.
                            </p>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-200 mb-2">Productos Simples</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Productos individuales vendibles vinculados directamente al inventario. Ej: bebidas embotelladas, snacks. Se deduce 1:1 del stock base.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Platillos</div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats?.total_items || 0 }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ stats?.with_recipes || 0 }} con receta</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"></path>
                                        <path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"></path>
                                        <path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Recetas Definidas</div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats?.total_recipes || 0 }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ stats?.total_simple_products || 0 }} productos simples</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Disponibles</div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats?.available_items || 0 }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ stats?.total_items ? ((stats.available_items / stats.total_items) * 100).toFixed(0) : 0 }}% del total</div>
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

const props = defineProps({
    stats: Object,
});
</script>
