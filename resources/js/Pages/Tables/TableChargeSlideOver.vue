<script setup>
import { ref, computed, watch } from 'vue';
import SlideOver from '@/Components/SlideOver.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    sale: {
        type: Object,
        default: null
    },
    // For charging all sales at once
    sales: {
        type: Array,
        default: () => []
    },
    tableId: {
        type: Number,
        default: null
    },
    mode: {
        type: String,
        default: 'single', // 'single' or 'all'
        validator: (value) => ['single', 'all'].includes(value)
    }
});

const emit = defineEmits(['close', 'charged']);

// Form state
const paymentMethod = ref('efectivo');
const discount = ref(0);
const isProcessing = ref(false);
const error = ref('');

// Reset form when opened
watch(() => props.show, (newVal) => {
    if (newVal) {
        paymentMethod.value = 'efectivo';
        discount.value = 0;
        error.value = '';
        isProcessing.value = false;
    }
});

// Computed values
const isSingleMode = computed(() => props.mode === 'single');

const displaySales = computed(() => {
    if (isSingleMode.value && props.sale) {
        return [props.sale];
    }
    return props.sales || [];
});

const subtotal = computed(() => {
    return displaySales.value.reduce((sum, sale) => sum + (sale.subtotal || sale.total || 0), 0);
});

const total = computed(() => {
    return Math.max(0, subtotal.value - (parseFloat(discount.value) || 0));
});

const title = computed(() => {
    if (isSingleMode.value) {
        return 'Cobrar Cuenta';
    }
    return `Cobrar Todo (${displaySales.value.length} pedidos)`;
});

const subtitle = computed(() => {
    if (isSingleMode.value && props.sale) {
        return `Pedido #${props.sale.sale_number}`;
    }
    return 'Cobro conjunto de todas las cuentas';
});

// Payment method options (icons are rendered in template as SVG)
const paymentMethods = [
    { value: 'efectivo', label: 'Efectivo' },
    { value: 'tarjeta', label: 'Tarjeta' },
    { value: 'transferencia', label: 'Transferencia' },
];

// Kitchen status helpers
const getKitchenStatusLabel = (status) => {
    const labels = {
        nueva: 'Nueva',
        preparando: 'Preparando',
        lista: 'Lista',
        entregada: 'Entregada',
        sin_estado: 'Sin estado',
    };
    return labels[status] || status;
};

const getKitchenStatusColor = (status) => {
    const colors = {
        nueva: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        preparando: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        lista: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        entregada: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        sin_estado: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
    };
    return colors[status] || colors.sin_estado;
};

// Actions
const handleClose = () => {
    if (!isProcessing.value) {
        emit('close');
    }
};

const handleCharge = async () => {
    if (isProcessing.value) return;

    isProcessing.value = true;
    error.value = '';

    try {
        let response;
        const data = {
            payment_method: paymentMethod.value,
            discount: parseFloat(discount.value) || 0,
        };

        if (isSingleMode.value && props.sale) {
            // Charge single sale
            response = await fetch(route('sales.charge', props.sale.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                },
                body: JSON.stringify(data),
            });
        } else if (props.tableId) {
            // Charge all sales for table
            response = await fetch(route('tables.charge-all', props.tableId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                },
                body: JSON.stringify(data),
            });
        }

        const result = await response.json();

        if (result.success) {
            emit('charged', result);
            emit('close');
        } else {
            error.value = result.message || 'Error al procesar el cobro';
        }
    } catch (err) {
        console.error('Error charging:', err);
        error.value = 'Error de conexión. Intenta de nuevo.';
    } finally {
        isProcessing.value = false;
    }
};
</script>

<template>
    <SlideOver
        :show="show"
        :title="title"
        :subtitle="subtitle"
        size="md"
        nested
        @close="handleClose"
    >
        <div class="space-y-6">
            <!-- Error message -->
            <div v-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm text-red-700 dark:text-red-300">{{ error }}</span>
                </div>
            </div>

            <!-- Sales summary -->
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                    Resumen de Pedidos
                </h3>

                <div v-for="sale in displaySales" :key="sale.id" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 space-y-3">
                    <!-- Sale header -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ sale.customer_name || 'Cliente' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    #{{ sale.sale_number }} • {{ sale.created_at }}
                                </p>
                            </div>
                        </div>
                        <span :class="['px-2 py-1 rounded-full text-xs font-medium', getKitchenStatusColor(sale.kitchen_status)]">
                            {{ getKitchenStatusLabel(sale.kitchen_status) }}
                        </span>
                    </div>

                    <!-- Items -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3 space-y-2">
                        <div v-for="item in sale.items" :key="item.id" class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ item.quantity }}x {{ item.name }}
                            </span>
                            <span class="text-gray-900 dark:text-white font-medium">
                                ${{ item.subtotal.toFixed(2) }}
                            </span>
                        </div>
                    </div>

                    <!-- Sale total -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex justify-between">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Subtotal</span>
                        <span class="font-bold text-gray-900 dark:text-white">${{ (sale.subtotal || sale.total).toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment method -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wide">
                    Método de Pago
                </label>
                <div class="grid grid-cols-3 gap-3">
                    <button
                        v-for="method in paymentMethods"
                        :key="method.value"
                        @click="paymentMethod = method.value"
                        :class="[
                            'flex flex-col items-center justify-center p-4 rounded-xl border-2 transition-all',
                            paymentMethod === method.value
                                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300'
                                : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 text-gray-700 dark:text-gray-300'
                        ]"
                    >
                        <!-- Efectivo icon -->
                        <svg v-if="method.value === 'efectivo'" class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <!-- Tarjeta icon -->
                        <svg v-else-if="method.value === 'tarjeta'" class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <!-- Transferencia icon -->
                        <svg v-else-if="method.value === 'transferencia'" class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span class="text-sm font-medium">{{ method.label }}</span>
                    </button>
                </div>
            </div>

            <!-- Discount -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">
                    Descuento (opcional)
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">$</span>
                    <input
                        v-model="discount"
                        type="number"
                        min="0"
                        :max="subtotal"
                        step="0.01"
                        class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="0.00"
                    />
                </div>
            </div>

            <!-- Total -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-blue-100">Subtotal</span>
                    <span class="font-medium">${{ subtotal.toFixed(2) }}</span>
                </div>
                <div v-if="discount > 0" class="flex items-center justify-between mb-2">
                    <span class="text-blue-100">Descuento</span>
                    <span class="font-medium">-${{ parseFloat(discount).toFixed(2) }}</span>
                </div>
                <div class="border-t border-blue-400 pt-3 mt-3 flex items-center justify-between">
                    <span class="text-lg font-semibold">Total a Cobrar</span>
                    <span class="text-3xl font-bold">${{ total.toFixed(2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <template #footer>
            <div class="flex items-center justify-end space-x-3">
                <button
                    @click="handleClose"
                    :disabled="isProcessing"
                    class="px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50"
                >
                    Cancelar
                </button>
                <button
                    @click="handleCharge"
                    :disabled="isProcessing || total <= 0"
                    class="px-8 py-3 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                >
                    <svg v-if="isProcessing" class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span v-if="isProcessing">Procesando...</span>
                    <span v-else>Confirmar Cobro ${{ total.toFixed(2) }}</span>
                </button>
            </div>
        </template>
    </SlideOver>
</template>
