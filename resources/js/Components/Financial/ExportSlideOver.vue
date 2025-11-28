<template>
    <SlideOver
        :show="show"
        @close="handleClose"
        title="Exportar Reporte de Flujo de Efectivo"
        subtitle="Selecciona el formato y opciones de exportación"
        size="md"
    >
        <div class="space-y-6">
            <!-- Format Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    Formato de Exportación
                </label>
                <div class="grid grid-cols-3 gap-3">
                    <button
                        v-for="format in formats"
                        :key="format.value"
                        type="button"
                        :class="[
                            'flex flex-col items-center justify-center p-4 rounded-lg border-2 transition-all',
                            selectedFormat === format.value
                                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                        ]"
                        @click="selectedFormat = format.value"
                    >
                        <component :is="format.icon" class="w-8 h-8 mb-2" />
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ format.label }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ format.description }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Export Options -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">
                    Opciones de Exportación
                </h4>

                <!-- Include Filters -->
                <div class="flex items-center mb-4">
                    <input
                        id="include-filters"
                        v-model="includeFilters"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="include-filters" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                        Aplicar filtros activos
                    </label>
                </div>

                <!-- Active Filters Summary -->
                <div v-if="includeFilters && hasActiveFilters" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Filtros que se aplicarán:
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-if="filters.search"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200"
                        >
                            Búsqueda: {{ filters.search }}
                        </span>
                        <span
                            v-if="filters.type"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200"
                        >
                            Tipo: {{ filters.type === 'entrada' ? 'Ingreso' : 'Egreso' }}
                        </span>
                        <span
                            v-if="filters.category"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200"
                        >
                            Categoría: {{ filters.category }}
                        </span>
                        <span
                            v-if="filters.date_from || filters.date_to"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200"
                        >
                            Fecha: {{ filters.date_from }} - {{ filters.date_to }}
                        </span>
                    </div>
                </div>

                <!-- No Filters Warning -->
                <div v-if="!includeFilters" class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 border border-yellow-200 dark:border-yellow-800">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Se exportarán todas las transacciones
                            </p>
                            <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
                                Sin filtros, el archivo puede ser muy grande
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Info -->
            <div v-if="selectedFormat" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-blue-700 dark:text-blue-300">
                        <p class="font-medium">
                            {{ formatInfo.title }}
                        </p>
                        <p class="mt-1">
                            {{ formatInfo.description }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <template #footer>
            <div class="flex items-center justify-end gap-3">
                <button
                    type="button"
                    @click="handleClose"
                    :disabled="exporting"
                    class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    @click="handleExport"
                    :disabled="!selectedFormat || exporting"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center"
                >
                    <svg
                        v-if="!exporting"
                        class="w-4 h-4 mr-2"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                    <svg
                        v-else
                        class="animate-spin w-4 h-4 mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ exporting ? 'Exportando...' : 'Exportar' }}
                </button>
            </div>
        </template>
    </SlideOver>
</template>

<script setup>
import { ref, computed, h } from 'vue';
import SlideOver from '@/Components/SlideOver.vue';

// Icons as functional components
const CsvIcon = () => h('svg', {
    class: 'text-green-600',
    fill: 'none',
    stroke: 'currentColor',
    viewBox: '0 0 24 24'
}, [
    h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
    })
]);

const ExcelIcon = () => h('svg', {
    class: 'text-emerald-600',
    fill: 'none',
    stroke: 'currentColor',
    viewBox: '0 0 24 24'
}, [
    h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'
    })
]);

const PdfIcon = () => h('svg', {
    class: 'text-red-600',
    fill: 'none',
    stroke: 'currentColor',
    viewBox: '0 0 24 24'
}, [
    h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'
    })
]);

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['close', 'export']);

const selectedFormat = ref('excel');
const includeFilters = ref(true);
const exporting = ref(false);

const formats = [
    {
        value: 'csv',
        label: 'CSV',
        description: 'Texto plano',
        icon: CsvIcon
    },
    {
        value: 'excel',
        label: 'Excel',
        description: 'Con formato',
        icon: ExcelIcon
    },
    {
        value: 'pdf',
        label: 'PDF',
        description: 'Imprimible',
        icon: PdfIcon
    }
];

const hasActiveFilters = computed(() => {
    return Object.keys(props.filters).some(key => {
        const value = props.filters[key];
        return value !== null && value !== undefined && value !== '';
    });
});

const formatInfo = computed(() => {
    const info = {
        csv: {
            title: 'CSV (Valores Separados por Comas)',
            description: 'Archivo de texto plano compatible con Excel y otras aplicaciones de hoja de cálculo.'
        },
        excel: {
            title: 'Excel (.xlsx)',
            description: 'Archivo de Excel con formato, colores y auto-filtros aplicados.'
        },
        pdf: {
            title: 'PDF (Documento Portátil)',
            description: 'Documento imprimible con formato profesional y resumen de estadísticas.'
        }
    };

    return info[selectedFormat.value] || {};
});

const handleClose = () => {
    if (!exporting.value) {
        selectedFormat.value = 'excel';
        includeFilters.value = true;
        emit('close');
    }
};

const handleExport = () => {
    exporting.value = true;

    const exportData = {
        format: selectedFormat.value,
        filters: includeFilters.value ? props.filters : {}
    };

    emit('export', exportData);

    // Simulate export delay
    setTimeout(() => {
        exporting.value = false;
        handleClose();
    }, 1000);
};
</script>
