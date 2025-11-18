<template>
    <TransitionRoot :show="isOpen" as="template">
        <Dialog @close="close" class="relative z-50">
            <!-- Backdrop -->
            <TransitionChild
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
            </TransitionChild>

            <!-- Modal -->
            <div class="fixed inset-0 overflow-y-auto p-4 sm:p-6 md:p-20">
                <TransitionChild
                    enter="ease-out duration-300"
                    enter-from="opacity-0 scale-95"
                    enter-to="opacity-100 scale-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100 scale-100"
                    leave-to="opacity-0 scale-95"
                >
                    <DialogPanel class="mx-auto max-w-2xl transform overflow-hidden rounded-xl bg-white dark:bg-gray-900 shadow-2xl ring-1 ring-black ring-opacity-5 transition-all">
                        <!-- Search Input -->
                        <div class="relative">
                            <svg
                                class="pointer-events-none absolute left-4 top-3.5 h-5 w-5 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <input
                                ref="searchInput"
                                v-model="query"
                                type="text"
                                placeholder="Buscar ventas, productos, menú..."
                                class="h-12 w-full border-0 bg-transparent pl-11 pr-4 text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-0 sm:text-sm"
                                @keydown.esc="close"
                            />
                        </div>

                        <!-- Results -->
                        <div v-if="query" class="max-h-96 scroll-py-2 overflow-y-auto border-t border-gray-200 dark:border-gray-800">
                            <!-- No results -->
                            <div v-if="filteredResults.length === 0" class="px-6 py-14 text-center sm:px-14">
                                <svg
                                    class="mx-auto h-6 w-6 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                                    No encontramos resultados para "{{ query }}"
                                </p>
                            </div>

                            <!-- Results list -->
                            <ul v-else class="divide-y divide-gray-200 dark:divide-gray-800">
                                <li
                                    v-for="result in filteredResults"
                                    :key="result.id"
                                    class="group flex cursor-pointer items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800"
                                    @click="navigate(result.href)"
                                >
                                    <div :class="[
                                        'flex h-10 w-10 flex-none items-center justify-center rounded-lg',
                                        getCategoryColor(result.category)
                                    ]">
                                        <component :is="getCategoryIcon(result.category)" class="h-5 w-5" />
                                    </div>
                                    <div class="ml-4 flex-auto">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ result.title }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ result.description }}
                                        </p>
                                    </div>
                                    <div class="ml-4 flex-none text-xs text-gray-400">
                                        {{ result.badge }}
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- Quick actions (when no query) -->
                        <div v-else class="border-t border-gray-200 dark:border-gray-800 px-6 py-14 text-center sm:px-14">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Acciones Rápidas</h3>
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    v-for="action in quickActions"
                                    :key="action.name"
                                    @click="navigate(action.href)"
                                    class="flex flex-col items-center justify-center p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                                >
                                    <component :is="action.icon" class="w-6 h-6 text-gray-600 dark:text-gray-400 mb-2" />
                                    <span class="text-xs font-medium text-gray-900 dark:text-white">{{ action.name }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex flex-wrap items-center bg-gray-50 dark:bg-gray-800/50 px-4 py-2.5 text-xs text-gray-700 dark:text-gray-300">
                            <kbd class="mx-1 flex h-5 w-5 items-center justify-center rounded border border-gray-400 font-semibold sm:mx-2 bg-white dark:bg-gray-900">↑</kbd>
                            <kbd class="mx-1 flex h-5 w-5 items-center justify-center rounded border border-gray-400 font-semibold sm:mx-2 bg-white dark:bg-gray-900">↓</kbd>
                            <span class="sm:hidden">para navegar</span>
                            <span class="hidden sm:inline">para navegar,</span>
                            <kbd class="mx-1 flex h-5 w-5 items-center justify-center rounded border border-gray-400 font-semibold sm:mx-2 bg-white dark:bg-gray-900">↵</kbd>
                            <span class="hidden sm:inline">para seleccionar,</span>
                            <kbd class="mx-1 flex h-5 w-5 items-center justify-center rounded border border-gray-400 font-semibold sm:mx-2 bg-white dark:bg-gray-900">esc</kbd>
                            <span>para cerrar</span>
                        </div>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, computed, watch, nextTick, h } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue';

const props = defineProps({
    isOpen: Boolean
});

const emit = defineEmits(['close']);

const searchInput = ref(null);
const query = ref('');

// Mock data - replace with real API calls
const allResults = [
    {
        id: 1,
        category: 'sales',
        title: 'Venta #20251117001',
        description: 'Total: $1,250.00 - Completada',
        badge: 'Hoy',
        href: '/sales/1'
    },
    {
        id: 2,
        category: 'inventory',
        title: 'Coca Cola 355ml',
        description: 'Stock: 45 unidades',
        badge: 'Bebida',
        href: '/inventory/products/2'
    },
    {
        id: 3,
        category: 'menu',
        title: 'Hamburguesa Clásica',
        description: 'Precio: $85.00',
        badge: 'Platillo',
        href: '/menu/items/1'
    }
];

const filteredResults = computed(() => {
    if (!query.value) return [];

    const searchTerm = query.value.toLowerCase();
    return allResults.filter(item =>
        item.title.toLowerCase().includes(searchTerm) ||
        item.description.toLowerCase().includes(searchTerm)
    );
});

const quickActions = [
    {
        name: 'Nueva Venta',
        href: '/sales/pos',
        icon: h('svg', { fill: 'currentColor', viewBox: '0 0 20 20' }, [
            h('path', { d: 'M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z' })
        ])
    },
    {
        name: 'Agregar Producto',
        href: '/inventory/products/create',
        icon: h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 4v16m8-8H4' })
        ])
    },
    {
        name: 'Ver Reportes',
        href: '/admin/reports',
        icon: h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' })
        ])
    },
    {
        name: 'Devoluciones',
        href: '/returns',
        icon: h('svg', { fill: 'currentColor', viewBox: '0 0 20 20' }, [
            h('path', { 'fill-rule': 'evenodd', d: 'M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z', 'clip-rule': 'evenodd' })
        ])
    }
];

const getCategoryColor = (category) => {
    const colors = {
        sales: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
        inventory: 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
        menu: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
    };
    return colors[category] || 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400';
};

const getCategoryIcon = (category) => {
    const icons = {
        sales: h('svg', { fill: 'currentColor', viewBox: '0 0 20 20' }, [
            h('path', { d: 'M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z' }),
            h('path', { 'fill-rule': 'evenodd', d: 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z', 'clip-rule': 'evenodd' })
        ]),
        inventory: h('svg', { fill: 'currentColor', viewBox: '0 0 20 20' }, [
            h('path', { d: 'M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' })
        ]),
        menu: h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' })
        ])
    };
    return icons[category] || icons.menu;
};

const navigate = (href) => {
    router.visit(href);
    close();
};

const close = () => {
    query.value = '';
    emit('close');
};

watch(() => props.isOpen, (newValue) => {
    if (newValue) {
        nextTick(() => {
            searchInput.value?.focus();
        });
    }
});
</script>
