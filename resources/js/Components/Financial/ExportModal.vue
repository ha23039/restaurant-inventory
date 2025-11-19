<template>
    <BaseModal
        :show="show"
        :title="title"
        @close="handleClose"
    >
        <div class="space-y-6">
            <!-- Format Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
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
                                ? 'border-blue-500 bg-blue-50'
                                : 'border-gray-200 hover:border-gray-300'
                        ]"
                        @click="selectedFormat = format.value"
                    >
                        <component :is="format.icon" class="w-8 h-8 mb-2" />
                        <span class="text-sm font-medium text-gray-900">
                            {{ format.label }}
                        </span>
                        <span class="text-xs text-gray-500 mt-1">
                            {{ format.description }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Export Options -->
            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-sm font-medium text-gray-900 mb-3">
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
                    <label for="include-filters" class="ml-2 block text-sm text-gray-700">
                        Aplicar filtros activos
                    </label>
                </div>

                <!-- Active Filters Summary -->
                <div v-if="includeFilters && hasActiveFilters" class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs font-medium text-gray-700 mb-2">
                        Filtros que se aplicarán:
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <BaseBadge
                            v-if="filters.search"
                            variant="info"
                            size="sm"
                        >
                            Búsqueda: {{ filters.search }}
                        </BaseBadge>
                        <BaseBadge
                            v-if="filters.type"
                            variant="info"
                            size="sm"
                        >
                            Tipo: {{ filters.type === 'entrada' ? 'Ingreso' : 'Egreso' }}
                        </BaseBadge>
                        <BaseBadge
                            v-if="filters.category"
                            variant="info"
                            size="sm"
                        >
                            Categoría: {{ filters.category }}
                        </BaseBadge>
                        <BaseBadge
                            v-if="filters.date_from || filters.date_to"
                            variant="info"
                            size="sm"
                        >
                            Fecha: {{ filters.date_from }} - {{ filters.date_to }}
                        </BaseBadge>
                    </div>
                </div>

                <!-- No Filters Warning -->
                <div v-if="!includeFilters" class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">
                                Se exportarán todas las transacciones
                            </p>
                            <p class="text-xs text-yellow-700 mt-1">
                                Sin filtros, el archivo puede ser muy grande
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Info -->
            <div v-if="selectedFormat" class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-blue-700">
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

        <!-- Actions -->
        <template #footer>
            <div class="flex items-center justify-end gap-3">
                <BaseButton
                    variant="secondary"
                    size="md"
                    @click="handleClose"
                >
                    Cancelar
                </BaseButton>
                <BaseButton
                    variant="primary"
                    size="md"
                    :loading="exporting"
                    :disabled="!selectedFormat"
                    @click="handleExport"
                >
                    <svg
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
                    Exportar
                </BaseButton>
            </div>
        </template>
    </BaseModal>
</template>

<script setup>
import { ref, computed, h } from 'vue';
import { useToast } from '@/Composables/useToast';
import BaseModal from '@/Components/Base/BaseModal.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseBadge from '@/Components/Base/BaseBadge.vue';

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
    },
    title: {
        type: String,
        default: 'Exportar Reporte'
    }
});

const emit = defineEmits(['close', 'export']);

const toast = useToast();

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
    selectedFormat.value = 'excel';
    includeFilters.value = true;
    emit('close');
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
        toast.success(`Exportando a ${selectedFormat.value.toUpperCase()}...`);
        handleClose();
    }, 500);
};
</script>
