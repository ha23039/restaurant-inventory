<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MenuItemFormSlideOver from '@/Components/MenuItemFormSlideOver.vue';
import ExportMenuSlideOver from '@/Components/Menu/ExportMenuSlideOver.vue';
import { useToast } from 'vue-toastification';

const props = defineProps({
    menuItems: Object,
    filters: Object,
});

const toast = useToast();

// Estado
const showFormSlideOver = ref(false);
const showExportSlideOver = ref(false);
const editingItem = ref(null);
const searchTerm = ref(props.filters?.search || '');
const filterAvailable = ref(props.filters?.is_available || '');
const filterService = ref(props.filters?.is_service || '');

// Métodos
const openCreateForm = () => {
    editingItem.value = null;
    showFormSlideOver.value = true;
};

const openEditForm = (item) => {
    editingItem.value = item;
    showFormSlideOver.value = true;
};

const closeForm = () => {
    showFormSlideOver.value = false;
    editingItem.value = null;
};

const applyFilters = () => {
    router.get(route('menu.items'), {
        search: searchTerm.value,
        is_available: filterAvailable.value,
        is_service: filterService.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchTerm.value = '';
    filterAvailable.value = '';
    filterService.value = '';
    router.get(route('menu.items'));
};

const toggleAvailability = (item) => {
    router.patch(route('menu.items.toggle-availability', item.id), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Disponibilidad actualizada');
        },
    });
};

const deleteItem = (item) => {
    if (confirm(`¿Estás seguro de eliminar "${item.name}"?`)) {
        router.delete(route('menu.items.destroy', item.id), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Platillo eliminado');
            },
            onError: (errors) => {
                toast.error(errors.error || 'Error al eliminar platillo');
            },
        });
    }
};

const formatPrice = (price) => {
    return parseFloat(price).toFixed(2);
};
</script>

<template>
    <Head title="Platillos del Menú" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Platillos del Menú
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Gestiona los platillos y servicios del restaurante
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <button
                        @click="showExportSlideOver = true"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                        </svg>
                        Exportar Menú
                    </button>

                    <button
                        @click="openCreateForm"
                        class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Platillo
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Filtros y búsqueda -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Búsqueda -->
                        <div class="md:col-span-2">
                            <input
                                v-model="searchTerm"
                                @keyup.enter="applyFilters"
                                type="text"
                                placeholder="Buscar platillo..."
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white"
                            />
                        </div>

                        <!-- Filtro disponibilidad -->
                        <div>
                            <select
                                v-model="filterAvailable"
                                @change="applyFilters"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Todos</option>
                                <option value="true">Disponibles</option>
                                <option value="false">No Disponibles</option>
                            </select>
                        </div>

                        <!-- Filtro tipo -->
                        <div>
                            <select
                                v-model="filterService"
                                @change="applyFilters"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Todos</option>
                                <option value="false">Platillos</option>
                                <option value="true">Servicios</option>
                            </select>
                        </div>
                    </div>

                    <!-- Botón limpiar filtros -->
                    <div v-if="searchTerm || filterAvailable || filterService" class="mt-3">
                        <button
                            @click="clearFilters"
                            class="text-sm text-orange-600 hover:text-orange-700 font-medium"
                        >
                            Limpiar filtros
                        </button>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Platillo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Precio
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Recetas
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Disponibilidad
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="item in menuItems.data" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ item.name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ item.description || 'Sin descripción' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                        ${{ formatPrice(item.price) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        v-if="item.is_service"
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200"
                                    >
                                        Servicio
                                    </span>
                                    <span
                                        v-else
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200"
                                    >
                                        Platillo
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ item.recipes_count }} ingrediente(s)
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button
                                        @click="toggleAvailability(item)"
                                        class="relative inline-flex items-center"
                                    >
                                        <div
                                            class="w-11 h-6 rounded-full transition-colors"
                                            :class="item.is_available ? 'bg-green-600' : 'bg-gray-300 dark:bg-gray-600'"
                                        >
                                            <div
                                                class="absolute top-0.5 left-0.5 bg-white w-5 h-5 rounded-full transition-transform"
                                                :class="item.is_available ? 'transform translate-x-5' : ''"
                                            ></div>
                                        </div>
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            {{ item.is_available ? 'Disponible' : 'No disponible' }}
                                        </span>
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-3">
                                        <button
                                            @click="openEditForm(item)"
                                            class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                            title="Editar"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="deleteItem(item)"
                                            class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                            title="Eliminar"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="menuItems.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="text-gray-400 dark:text-gray-500">
                                        <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-sm">No hay platillos registrados</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Paginación -->
                    <div v-if="menuItems.data.length > 0" class="bg-gray-50 dark:bg-gray-900 px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                Mostrando {{ menuItems.from }} a {{ menuItems.to }} de {{ menuItems.total }} platillos
                            </div>
                            <div class="flex space-x-2">
                                <a
                                    v-for="link in menuItems.links"
                                    :key="link.label"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-3 py-1 text-sm rounded border',
                                        link.active
                                            ? 'bg-orange-600 text-white border-orange-600'
                                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                                    ]"
                                ></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form SlideOver -->
        <MenuItemFormSlideOver
            :show="showFormSlideOver"
            :menuItem="editingItem"
            @close="closeForm"
        />

        <!-- Export SlideOver -->
        <ExportMenuSlideOver
            :show="showExportSlideOver"
            @close="showExportSlideOver = false"
        />
    </AdminLayout>
</template>
