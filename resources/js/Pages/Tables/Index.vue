<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import TableSlideOver from './TableSlideOver.vue';
import TableFormModal from './TableFormModal.vue';

const props = defineProps({
    tables: Array,
    filters: Object,
    statistics: Object,
});

// State
const selectedTable = ref(null);
const showSlideOver = ref(false);
const showTableFormModal = ref(false);
const editingTable = ref(null);
const localFilters = ref({
    status: props.filters.status || '',
    search: props.filters.search || '',
});

// Open slide-over for table
const openTable = (table) => {
    selectedTable.value = table;
    showSlideOver.value = true;
};

// Close slide-over
const closeSlideOver = () => {
    showSlideOver.value = false;
    selectedTable.value = null;
    // Refresh data
    router.reload({ only: ['tables', 'statistics'] });
};

// Open modal for creating table
const openCreateModal = () => {
    editingTable.value = null;
    showTableFormModal.value = true;
};

// Open modal for editing table
const openEditModal = (table) => {
    editingTable.value = table;
    showTableFormModal.value = true;
};

// Close modal
const closeFormModal = () => {
    showTableFormModal.value = false;
    editingTable.value = null;
    router.reload({ only: ['tables', 'statistics'] });
};

// Apply filters
const applyFilters = () => {
    router.get(route('tables.index'), localFilters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Clear filters
const clearFilters = () => {
    localFilters.value = { status: '', search: '' };
    applyFilters();
};

// Get status badge color
const getStatusColor = (status) => {
    const colors = {
        disponible: 'bg-green-100 border-green-500 text-green-800',
        ocupada: 'bg-red-100 border-red-500 text-red-800',
        reservada: 'bg-yellow-100 border-yellow-500 text-yellow-800',
        en_limpieza: 'bg-blue-100 border-blue-500 text-blue-800',
    };
    return colors[status] || 'bg-gray-100 border-gray-500 text-gray-800';
};

// Get status icon
const getStatusIcon = (status) => {
    const icons = {
        disponible: 'M5 13l4 4L19 7',
        ocupada: 'M6 18L18 6M6 6l12 12',
        reservada: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        en_limpieza: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
    };
    return icons[status] || 'M8 12h.01M12 12h.01M16 12h.01';
};

// Has active filters
const hasActiveFilters = computed(() => {
    return localFilters.value.status || localFilters.value.search;
});
</script>

<template>
    <Head title="Gestión de Mesas" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Gestión de Mesas
                </h2>
                <button
                    @click="showTableFormModal = true"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva Mesa
                </button>
            </div>
        </template>

        <div class="py-3 md:py-6 lg:py-12 px-2 sm:px-6 lg:px-8 space-y-6">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Mesas</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.total }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Disponibles</p>
                            <p class="text-3xl font-bold text-green-600 mt-1">{{ statistics.available }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Ocupadas</p>
                            <p class="text-3xl font-bold text-red-600 mt-1">{{ statistics.occupied }}</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Reservadas</p>
                            <p class="text-3xl font-bold text-yellow-600 mt-1">{{ statistics.reserved }}</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Ocupación</p>
                            <p class="text-3xl font-bold text-purple-600 mt-1">{{ statistics.occupancy_rate }}%</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
                        <input
                            v-model="localFilters.search"
                            type="text"
                            placeholder="Número o nombre..."
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                        <select
                            v-model="localFilters.status"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">Todos</option>
                            <option value="disponible">Disponible</option>
                            <option value="ocupada">Ocupada</option>
                            <option value="reservada">Reservada</option>
                            <option value="en_limpieza">En Limpieza</option>
                        </select>
                    </div>

                    <div class="col-span-2 flex items-end gap-2">
                        <button
                            @click="applyFilters"
                            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
                        >
                            Filtrar
                        </button>
                        <button
                            v-if="hasActiveFilters"
                            @click="clearFilters"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors"
                        >
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tables Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                <div
                    v-for="table in tables"
                    :key="table.id"
                    @click="openTable(table)"
                    :class="[
                        'relative bg-white dark:bg-gray-800 rounded-lg shadow-md border-2 cursor-pointer transition-all hover:shadow-lg hover:scale-105',
                        getStatusColor(table.status)
                    ]"
                >
                    <div class="p-6 text-center">
                        <!-- Table Icon -->
                        <div class="mb-3 flex justify-center">
                            <div class="w-16 h-16 bg-white dark:bg-gray-700 rounded-full flex items-center justify-center shadow-sm">
                                <svg class="w-8 h-8 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Table Number -->
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                            Mesa {{ table.table_number }}
                        </h3>

                        <!-- Table Name -->
                        <p v-if="table.name" class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {{ table.name }}
                        </p>

                        <!-- Capacity -->
                        <div class="flex items-center justify-center text-sm text-gray-600 dark:text-gray-400 mb-3">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                            </svg>
                            {{ table.capacity }} personas
                        </div>

                        <!-- Status Badge -->
                        <div class="flex items-center justify-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getStatusIcon(table.status)" />
                                </svg>
                                {{ table.status_label }}
                            </span>
                        </div>

                        <!-- Sale Info (if occupied) -->
                        <div v-if="table.status === 'ocupada' && table.current_sale" class="mt-3 pt-3 border-t border-red-300">
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                Venta: #{{ table.current_sale.sale_number }}
                            </p>
                        </div>
                    </div>

                    <!-- Inactive indicator -->
                    <div v-if="!table.is_active" class="absolute top-2 right-2">
                        <span class="inline-flex items-center px-2 py-1 bg-gray-500 text-white text-xs font-semibold rounded-full">
                            Inactiva
                        </span>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="tables.length === 0" class="col-span-full">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay mesas</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Comienza creando una nueva mesa
                        </p>
                        <div class="mt-6">
                            <button
                                @click="showTableFormModal = true"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Nueva Mesa
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Slide-Over (Mini POS) -->
        <TableSlideOver
            :show="showSlideOver"
            :table="selectedTable"
            @close="closeSlideOver"
        />

        <!-- Table Form Modal -->
        <TableFormModal
            :show="showTableFormModal"
            :table="editingTable"
            @close="closeFormModal"
        />
    </AdminLayout>
</template>
