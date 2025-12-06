<template>
    <SlideOver
        :show="show"
        @close="handleClose"
        :title="reportTitle"
        :subtitle="reportSubtitle"
        size="md"
    >
        <div class="space-y-6">
            <!-- Date Range Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    Periodo del Reporte
                </label>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="date-from" class="block text-xs text-gray-600 dark:text-gray-400 mb-1">
                            Fecha Inicial
                        </label>
                        <input
                            id="date-from"
                            v-model="dateFrom"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                    </div>
                    <div>
                        <label for="date-to" class="block text-xs text-gray-600 dark:text-gray-400 mb-1">
                            Fecha Final
                        </label>
                        <input
                            id="date-to"
                            v-model="dateTo"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                    </div>
                </div>
            </div>

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

            <!-- Export Options based on Report Type -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">
                    Opciones de Exportación
                </h4>

                <!-- Executive Report Options -->
                <div v-if="reportType === 'executive'" class="space-y-3">
                    <div class="flex items-center">
                        <input
                            id="include-sales"
                            v-model="options.includeSales"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="include-sales" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Incluir resumen de ventas
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input
                            id="include-cashflow"
                            v-model="options.includeCashflow"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="include-cashflow" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Incluir flujo de efectivo
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input
                            id="include-inventory"
                            v-model="options.includeInventory"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="include-inventory" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Incluir estado de inventario
                        </label>
                    </div>
                </div>

                <!-- Financial Report Options -->
                <div v-else-if="reportType === 'financial'" class="space-y-3">
                    <div class="flex items-center">
                        <input
                            id="include-income"
                            v-model="options.includeIncome"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="include-income" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Incluir estado de ingresos
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input
                            id="include-expenses"
                            v-model="options.includeExpenses"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="include-expenses" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Incluir desglose de gastos
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input
                            id="include-balance"
                            v-model="options.includeBalance"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="include-balance" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Incluir balance general
                        </label>
                    </div>
                </div>

                <!-- Profitability Report Options -->
                <div v-else-if="reportType === 'profitability'" class="space-y-3">
                    <div class="flex items-center">
                        <input
                            id="include-margins"
                            v-model="options.includeMargins"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="include-margins" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Incluir análisis de márgenes
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input
                            id="include-products"
                            v-model="options.includeProducts"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="include-products" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Incluir rentabilidad por producto
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input
                            id="include-categories"
                            v-model="options.includeCategories"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="include-categories" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Incluir análisis por categoría
                        </label>
                    </div>
                </div>

                <!-- Inventory Report Options -->
                <div v-else-if="reportType === 'inventory'" class="space-y-3">
                    <div class="flex items-center">
                        <input
                            id="include-values"
                            v-model="options.includeValues"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="include-values" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Incluir valorización de inventario
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input
                            id="include-movements"
                            v-model="options.includeMovements"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="include-movements" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Incluir movimientos de inventario
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input
                            id="include-alerts"
                            v-model="options.includeAlerts"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="include-alerts" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Incluir alertas de stock bajo
                        </label>
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
                    {{ exporting ? 'Exportando...' : 'Exportar Reporte' }}
                </button>
            </div>
        </template>
    </SlideOver>
</template>

<script setup>
import { ref, computed, h } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import SlideOver from '@/Components/SlideOver.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

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
    reportType: {
        type: String,
        default: 'executive'
    }
});

const emit = defineEmits(['close']);

const today = new Date().toISOString().split('T')[0];
const firstDayOfMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];

const dateFrom = ref(firstDayOfMonth);
const dateTo = ref(today);
const selectedFormat = ref('excel');
const exporting = ref(false);
const options = ref({
    includeSales: true,
    includeCashflow: true,
    includeInventory: true,
    includeIncome: true,
    includeExpenses: true,
    includeBalance: true,
    includeMargins: true,
    includeProducts: true,
    includeCategories: true,
    includeValues: true,
    includeMovements: true,
    includeAlerts: true,
});

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

const reportTitles = {
    executive: 'Reporte Ejecutivo',
    financial: 'Estado Financiero',
    profitability: 'Análisis de Rentabilidad',
    inventory: 'Inventario Valorizado'
};

const reportSubtitles = {
    executive: 'Resumen completo del desempeño del negocio',
    financial: 'Estado de ingresos, gastos y balance general',
    profitability: 'Análisis de márgenes y rentabilidad por producto',
    inventory: 'Valorización y movimientos de inventario'
};

const reportTitle = computed(() => reportTitles[props.reportType] || 'Reporte Consolidado');
const reportSubtitle = computed(() => reportSubtitles[props.reportType] || 'Selecciona opciones de exportación');

const formatInfo = computed(() => {
    const info = {
        csv: {
            title: 'CSV (Valores Separados por Comas)',
            description: 'Archivo de texto plano compatible con Excel y otras aplicaciones de hoja de cálculo.'
        },
        excel: {
            title: 'Excel (.xlsx)',
            description: 'Archivo de Excel con formato, colores, gráficos y auto-filtros aplicados.'
        },
        pdf: {
            title: 'PDF (Documento Portátil)',
            description: 'Documento imprimible con formato profesional, gráficos y resumen ejecutivo.'
        }
    };

    return info[selectedFormat.value] || {};
});

const handleClose = () => {
    if (!exporting.value) {
        selectedFormat.value = 'excel';
        dateFrom.value = firstDayOfMonth;
        dateTo.value = today;
        emit('close');
    }
};

const handleExport = async () => {
    exporting.value = true;

    const routeMap = {
        executive: 'consolidated-reports.executive.export',
        financial: 'consolidated-reports.financial.export',
        profitability: 'consolidated-reports.profitability.export',
        inventory: 'consolidated-reports.inventory.export',
    };

    const routeName = routeMap[props.reportType];

    if (!routeName) {
        toast.error('Tipo de reporte no válido');
        exporting.value = false;
        return;
    }

    try {
        toast.info(`Generando ${reportTitle.value}...`);

        const response = await axios.post(route(routeName), {
            format: selectedFormat.value,
            dateFrom: dateFrom.value,
            dateTo: dateTo.value,
            options: options.value,
        }, {
            responseType: 'blob',
        });

        const contentDisposition = response.headers['content-disposition'];
        let filename = `reporte_${props.reportType}_${dateFrom.value}_${dateTo.value}.${selectedFormat.value}`;

        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i);
            if (filenameMatch) {
                filename = filenameMatch[1];
            }
        }

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        toast.success(`${reportTitle.value} generado exitosamente`);
        handleClose();
    } catch (error) {
        console.error('Error exporting report:', error);
        toast.error('Error al generar el reporte. Por favor intenta de nuevo.');
    } finally {
        exporting.value = false;
    }
};
</script>
