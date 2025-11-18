<script setup>
import { computed, ref } from 'vue';
import LoadingSpinner from '@/Components/Feedback/LoadingSpinner.vue';
import EmptyState from '@/Components/Feedback/EmptyState.vue';

const props = defineProps({
    columns: {
        type: Array,
        required: true,
        validator: (value) => {
            return value.every(col => 'key' in col && 'label' in col);
        },
    },
    data: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    sortable: {
        type: Boolean,
        default: true,
    },
    hoverable: {
        type: Boolean,
        default: true,
    },
    striped: {
        type: Boolean,
        default: false,
    },
    emptyMessage: {
        type: String,
        default: 'No hay datos disponibles',
    },
    rowKey: {
        type: String,
        default: 'id',
    },
});

const emit = defineEmits(['row-click', 'sort']);

const sortKey = ref('');
const sortOrder = ref('asc');

const sortedData = computed(() => {
    if (!props.sortable || !sortKey.value) {
        return props.data;
    }

    return [...props.data].sort((a, b) => {
        const aVal = getNestedValue(a, sortKey.value);
        const bVal = getNestedValue(b, sortKey.value);

        if (aVal === bVal) return 0;

        const comparison = aVal > bVal ? 1 : -1;
        return sortOrder.value === 'asc' ? comparison : -comparison;
    });
});

const getNestedValue = (obj, path) => {
    return path.split('.').reduce((value, key) => value?.[key], obj);
};

const handleSort = (column) => {
    if (!props.sortable || column.sortable === false) {
        return;
    }

    if (sortKey.value === column.key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = column.key;
        sortOrder.value = 'asc';
    }

    emit('sort', { key: sortKey.value, order: sortOrder.value });
};

const handleRowClick = (row, index) => {
    emit('row-click', { row, index });
};

const getSortIcon = (column) => {
    if (!props.sortable || column.sortable === false) {
        return null;
    }

    if (sortKey.value !== column.key) {
        return 'neutral';
    }

    return sortOrder.value;
};

const tableRowClasses = (index) => {
    const classes = [];

    if (props.hoverable) {
        classes.push('hover:bg-gray-50 cursor-pointer');
    }

    if (props.striped && index % 2 === 1) {
        classes.push('bg-gray-50');
    }

    return classes.join(' ');
};
</script>

<template>
    <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <!-- Table Header -->
            <thead class="bg-gray-50">
                <tr>
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        :class="[
                            column.sortable !== false && sortable ? 'cursor-pointer select-none' : '',
                            column.align === 'center' ? 'text-center' : '',
                            column.align === 'right' ? 'text-right' : '',
                        ]"
                        @click="handleSort(column)"
                    >
                        <div class="flex items-center gap-2">
                            <span>{{ column.label }}</span>

                            <!-- Sort Icons -->
                            <span
                                v-if="sortable && column.sortable !== false"
                                class="flex flex-col"
                            >
                                <svg
                                    v-if="getSortIcon(column) === 'neutral'"
                                    class="w-4 h-4 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"
                                    />
                                </svg>
                                <svg
                                    v-else-if="getSortIcon(column) === 'asc'"
                                    class="w-4 h-4 text-blue-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 15l7-7 7 7"
                                    />
                                </svg>
                                <svg
                                    v-else-if="getSortIcon(column) === 'desc'"
                                    class="w-4 h-4 text-blue-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>
                            </span>
                        </div>
                    </th>
                </tr>
            </thead>

            <!-- Table Body -->
            <tbody class="bg-white divide-y divide-gray-200">
                <!-- Loading State -->
                <tr v-if="loading">
                    <td :colspan="columns.length" class="px-6 py-12 text-center">
                        <LoadingSpinner size="lg" />
                    </td>
                </tr>

                <!-- Empty State -->
                <tr v-else-if="sortedData.length === 0">
                    <td :colspan="columns.length" class="px-6 py-12">
                        <EmptyState :title="emptyMessage" />
                    </td>
                </tr>

                <!-- Data Rows -->
                <tr
                    v-else
                    v-for="(row, index) in sortedData"
                    :key="row[rowKey] || index"
                    :class="tableRowClasses(index)"
                    @click="handleRowClick(row, index)"
                >
                    <td
                        v-for="column in columns"
                        :key="column.key"
                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                        :class="[
                            column.align === 'center' ? 'text-center' : '',
                            column.align === 'right' ? 'text-right' : '',
                        ]"
                    >
                        <!-- Custom Slot for Column -->
                        <slot
                            :name="`cell-${column.key}`"
                            :row="row"
                            :column="column"
                            :value="getNestedValue(row, column.key)"
                        >
                            <!-- Default Cell Content -->
                            {{ getNestedValue(row, column.key) }}
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
