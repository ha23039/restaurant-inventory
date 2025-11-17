<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <!-- Header con búsqueda y filtros -->
        <div class="p-4 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- Búsqueda -->
                <div class="relative flex-1 max-w-md">
                    <input
                        v-model="searchQuery"
                        type="text"
                        :placeholder="searchPlaceholder"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @input="debouncedSearch"
                    />
                    <svg
                        class="absolute left-3 top-2.5 w-5 h-5 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <!-- Acciones adicionales (slot) -->
                <div v-if="$slots.actions" class="flex items-center gap-2">
                    <slot name="actions"></slot>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            @click="column.sortable !== false ? sort(column.key) : null"
                            :class="[
                                'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
                                column.sortable !== false ? 'cursor-pointer hover:bg-gray-100 select-none' : ''
                            ]"
                        >
                            <div class="flex items-center gap-2">
                                <span>{{ column.label }}</span>
                                <span v-if="column.sortable !== false" class="inline-flex flex-col">
                                    <svg
                                        v-if="sortColumn === column.key && sortDirection === 'asc'"
                                        class="w-4 h-4 text-blue-600"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" />
                                    </svg>
                                    <svg
                                        v-else-if="sortColumn === column.key && sortDirection === 'desc'"
                                        class="w-4 h-4 text-blue-600"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" />
                                    </svg>
                                    <svg
                                        v-else
                                        class="w-4 h-4 text-gray-400"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path d="M5 12a1 1 0 102 0V6.414l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L5 6.414V12zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z" />
                                    </svg>
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <slot name="row" :items="sortedAndFilteredData"></slot>
                </tbody>
            </table>
        </div>

        <!-- Mensaje si no hay datos -->
        <div v-if="sortedAndFilteredData.length === 0" class="text-center py-12 text-gray-500">
            <div class="text-4xl mb-2">📋</div>
            <div class="font-medium">{{ emptyMessage }}</div>
            <div v-if="searchQuery" class="text-sm mt-2">Intenta con otros términos de búsqueda</div>
        </div>

        <!-- Footer con paginación y estadísticas -->
        <div v-if="pagination" class="border-t border-gray-200 px-4 py-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- Estadísticas -->
                <div class="text-sm text-gray-700">
                    Mostrando
                    <span class="font-medium">{{ pagination.from || 0 }}</span>
                    a
                    <span class="font-medium">{{ pagination.to || 0 }}</span>
                    de
                    <span class="font-medium">{{ pagination.total }}</span>
                    resultados
                </div>

                <!-- Links de paginación -->
                <div class="flex flex-wrap gap-1">
                    <Link
                        v-for="(link, index) in pagination.links"
                        :key="index"
                        :href="link.url"
                        :class="[
                            'px-3 py-2 rounded text-sm font-medium transition-colors',
                            link.active
                                ? 'bg-blue-600 text-white'
                                : link.url
                                ? 'bg-gray-100 hover:bg-gray-200 text-gray-700'
                                : 'bg-gray-50 text-gray-400 cursor-not-allowed'
                        ]"
                        :preserve-scroll="true"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    columns: {
        type: Array,
        required: true,
        // Ejemplo: [{ key: 'name', label: 'Nombre', sortable: true }, ...]
    },
    data: {
        type: Array,
        required: true,
    },
    pagination: {
        type: Object,
        default: null,
    },
    searchPlaceholder: {
        type: String,
        default: 'Buscar...',
    },
    emptyMessage: {
        type: String,
        default: 'No se encontraron resultados',
    },
    searchableFields: {
        type: Array,
        default: () => [], // Campos en los que buscar, ej: ['name', 'sale_number']
    },
});

// Estado
const searchQuery = ref('');
const sortColumn = ref('');
const sortDirection = ref('asc');

// Búsqueda con debounce
let searchTimeout = null;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        // La búsqueda se aplica automáticamente via computed
    }, 300);
};

// Función de ordenamiento
const sort = (column) => {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
};

// Datos filtrados y ordenados
const sortedAndFilteredData = computed(() => {
    let result = [...props.data];

    // Aplicar búsqueda
    if (searchQuery.value && props.searchableFields.length > 0) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(item => {
            return props.searchableFields.some(field => {
                const value = getNestedValue(item, field);
                return value && value.toString().toLowerCase().includes(query);
            });
        });
    }

    // Aplicar ordenamiento
    if (sortColumn.value) {
        result.sort((a, b) => {
            const aValue = getNestedValue(a, sortColumn.value);
            const bValue = getNestedValue(b, sortColumn.value);

            if (aValue === null || aValue === undefined) return 1;
            if (bValue === null || bValue === undefined) return -1;

            let comparison = 0;
            if (typeof aValue === 'string') {
                comparison = aValue.localeCompare(bValue);
            } else {
                comparison = aValue > bValue ? 1 : aValue < bValue ? -1 : 0;
            }

            return sortDirection.value === 'asc' ? comparison : -comparison;
        });
    }

    return result;
});

// Función auxiliar para obtener valores anidados (ej: 'user.name')
const getNestedValue = (obj, path) => {
    return path.split('.').reduce((current, key) => current?.[key], obj);
};
</script>
