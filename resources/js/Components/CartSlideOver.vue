<script setup>
import { ref, computed } from 'vue';
import SlideOver from './SlideOver.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    cartItems: {
        type: Array,
        default: () => []
    },
    selectedExistingSale: {
        type: Object,
        default: null
    },
    discount: {
        type: Number,
        default: 0
    },
    tax: {
        type: Number,
        default: 0
    },
    paymentMethod: {
        type: String,
        default: 'efectivo'
    },
    cashReceived: {
        type: Number,
        default: 0
    },
    selectedTable: {
        type: [Number, String, null],
        default: null
    },
    availableTables: {
        type: Array,
        default: () => []
    },
    customerName: {
        type: String,
        default: ''
    },
    orderNotes: {
        type: String,
        default: ''
    },
    isFreeSale: {
        type: Boolean,
        default: false
    },
    freeSaleDescription: {
        type: String,
        default: ''
    },
    freeSaleTotal: {
        type: [String, Number],
        default: ''
    },
    processing: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits([
    'close',
    'update:discount',
    'update:tax',
    'update:paymentMethod',
    'update:cashReceived',
    'update:selectedTable',
    'update:customerName',
    'update:orderNotes',
    'increment',
    'decrement',
    'remove',
    'clear',
    'process-sale'
]);

// Estado local
const showCustomerInfo = ref(false);

// Computed
const subtotal = computed(() => {
    return props.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const total = computed(() => {
    let baseTotal = subtotal.value;
    
    if (props.selectedExistingSale) {
        baseTotal += parseFloat(props.selectedExistingSale.total);
    }
    
    if (props.isFreeSale && props.freeSaleTotal) {
        baseTotal += parseFloat(props.freeSaleTotal);
    }
    
    const discountValue = parseFloat(props.discount) || 0;
    const taxValue = parseFloat(props.tax) || 0;
    return baseTotal - discountValue + taxValue;
});

const change = computed(() => {
    if (props.paymentMethod === 'efectivo' || props.paymentMethod === 'mixto') {
        return Math.max(0, parseFloat(props.cashReceived) - total.value);
    }
    return 0;
});

const hasItems = computed(() => {
    return props.cartItems.length > 0 || props.selectedExistingSale || props.isFreeSale;
});

const canComplete = computed(() => {
    if (props.processing) return false;
    if (props.isFreeSale) {
        return props.freeSaleDescription && props.freeSaleTotal && parseFloat(props.freeSaleTotal) > 0;
    }
    return props.cartItems.length > 0 || props.selectedExistingSale;
});

// Methods
const formatPrice = (price) => {
    return parseFloat(price || 0).toFixed(2);
};

const getSaleItemName = (item) => {
    // Para items con variante (ej: "Hamburguesa - Grande")
    if (item.menu_item_variant_id && item.menu_item_variant) {
        const parentName = item.menu_item?.name || 'Producto';
        const variantName = item.menu_item_variant.variant_name || item.menu_item_variant.name;
        return `${parentName} - ${variantName}`;
    }
    
    // Probar también con la estructura "variant" (por compatibilidad)
    if (item.variant) {
        const parentName = item.menu_item?.name || 'Producto';
        const variantName = item.variant.variant_name || item.variant.name;
        return `${parentName} - ${variantName}`;
    }
    
    // Items normales
    return item.menu_item?.name || item.product?.name || item.name || 'Producto';
};

const setQuickAmount = (amount) => {
    emit('update:cashReceived', amount);
};

const paymentMethods = [
    { value: 'efectivo', label: 'Efectivo', icon: 'cash' },
    { value: 'tarjeta', label: 'Tarjeta', icon: 'card' },
    { value: 'transferencia', label: 'Transfer.', icon: 'phone' },
    { value: 'mixto', label: 'Mixto', icon: 'refresh' }
];
</script>

<template>
    <SlideOver
        :show="show"
        @close="$emit('close')"
        title="Carrito de Compras"
        :subtitle="cartItems.length + ' productos'"
        size="full"
    >
        <div class="flex flex-col h-full -mx-4 -mt-2">
            <!-- Items de orden existente -->
            <div v-if="selectedExistingSale" class="mx-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg p-3 mb-4">
                <h4 class="text-sm font-semibold text-orange-900 dark:text-orange-100 mb-2 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Orden #{{ selectedExistingSale.sale_number }}
                </h4>
                <div class="space-y-1 max-h-24 overflow-y-auto">
                    <div
                        v-for="item in selectedExistingSale.sale_items"
                        :key="item.id"
                        class="flex justify-between text-xs text-orange-800 dark:text-orange-200"
                    >
                        <span>{{ item.quantity }}x {{ getSaleItemName(item) }}</span>
                        <span>${{ parseFloat(item.total_price).toFixed(2) }}</span>
                    </div>
                </div>
                <div class="mt-2 pt-2 border-t border-orange-200 dark:border-orange-800 flex justify-between text-sm font-semibold">
                    <span>Subtotal existente:</span>
                    <span>${{ parseFloat(selectedExistingSale.total).toFixed(2) }}</span>
                </div>
            </div>

            <!-- Lista de items del carrito -->
            <div class="flex-1 overflow-y-auto px-4">
                <!-- Empty state -->
                <div v-if="cartItems.length === 0 && !selectedExistingSale && !isFreeSale" class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <div class="font-medium">Carrito vacío</div>
                    <div class="text-sm">Agrega productos del catálogo</div>
                </div>

                <!-- Cart items -->
                <div v-else class="space-y-3">
                    <div
                        v-for="(item, index) in cartItems"
                        :key="index"
                        class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900 dark:text-white text-sm">{{ item.name }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">${{ formatPrice(item.price) }} c/u</p>
                            </div>
                            <span class="font-bold text-gray-900 dark:text-white">
                                ${{ formatPrice(item.price * item.quantity) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <div class="flex items-center space-x-2">
                                <button
                                    @click="$emit('decrement', index)"
                                    class="w-8 h-8 flex items-center justify-center bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-full hover:bg-red-100 dark:hover:bg-red-900"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                </button>
                                <span class="w-8 text-center font-semibold text-gray-900 dark:text-white">{{ item.quantity }}</span>
                                <button
                                    @click="$emit('increment', index)"
                                    class="w-8 h-8 flex items-center justify-center bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-full hover:bg-green-100 dark:hover:bg-green-900"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            </div>
                            <button
                                @click="$emit('remove', index)"
                                class="p-2 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de configuración -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4 px-4 space-y-3">
                <!-- Mesa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Mesa
                    </label>
                    <select
                        :value="selectedTable"
                        @change="$emit('update:selectedTable', $event.target.value ? parseInt($event.target.value) : null)"
                        class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                    >
                        <option :value="null">Para llevar</option>
                        <option v-for="table in availableTables" :key="table.id" :value="table.id">
                            Mesa {{ table.table_number }} {{ table.name ? `- ${table.name}` : '' }}
                        </option>
                    </select>
                </div>

                <!-- Información del Cliente (colapsable) -->
                <div>
                    <button
                        @click="showCustomerInfo = !showCustomerInfo"
                        type="button"
                        class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    >
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Información del Cliente
                            <span v-if="customerName || orderNotes" class="ml-2 w-2 h-2 bg-green-500 rounded-full"></span>
                        </span>
                        <svg
                            class="w-5 h-5 transition-transform"
                            :class="{ 'rotate-180': showCustomerInfo }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div v-show="showCustomerInfo" class="mt-2 space-y-2 pl-1">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Nombre</label>
                            <input
                                :value="customerName"
                                @input="$emit('update:customerName', $event.target.value)"
                                type="text"
                                maxlength="100"
                                placeholder="Ej: Juan Pérez..."
                                class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Notas</label>
                            <textarea
                                :value="orderNotes"
                                @input="$emit('update:orderNotes', $event.target.value)"
                                rows="2"
                                maxlength="500"
                                placeholder="Ej: Sin cebolla, extra picante..."
                                class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Método de pago -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Método de Pago
                    </label>
                    <div class="grid grid-cols-4 gap-1">
                        <button
                            v-for="method in paymentMethods"
                            :key="method.value"
                            @click="$emit('update:paymentMethod', method.value)"
                            :class="[
                                'py-2 px-2 text-xs font-medium rounded-lg border-2 transition-all',
                                paymentMethod === method.value
                                    ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'
                                    : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-300'
                            ]"
                        >
                            {{ method.label }}
                        </button>
                    </div>
                </div>

                <!-- Efectivo recibido -->
                <div v-if="paymentMethod === 'efectivo' || paymentMethod === 'mixto'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Efectivo Recibido
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                        <input
                            type="number"
                            :value="cashReceived"
                            @input="$emit('update:cashReceived', parseFloat($event.target.value) || 0)"
                            class="w-full pl-8 pr-4 py-2 text-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                            placeholder="0.00"
                            step="0.01"
                        />
                    </div>
                    <!-- Botones rápidos -->
                    <div v-if="!cashReceived || cashReceived === 0" class="mt-2 flex flex-wrap gap-1">
                        <button
                            v-for="quickAmount in [5, 10, 15, 20, 50, 100]"
                            :key="quickAmount"
                            @click="setQuickAmount(quickAmount)"
                            type="button"
                            class="px-3 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded-lg transition-colors"
                        >
                            ${{ quickAmount }}
                        </button>
                    </div>
                    <div v-if="change > 0" class="mt-2 p-2 bg-green-100 dark:bg-green-900/30 rounded-lg text-center">
                        <span class="text-green-700 dark:text-green-300 font-bold flex items-center justify-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Cambio: ${{ formatPrice(change) }}
                        </span>
                    </div>
                </div>

                <!-- Descuento/Impuesto Row -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Descuento</label>
                        <div class="relative">
                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500 text-sm">$</span>
                            <input
                                type="number"
                                :value="discount"
                                @input="$emit('update:discount', parseFloat($event.target.value) || 0)"
                                class="w-full pl-6 pr-2 py-1.5 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                                placeholder="0.00"
                                step="0.01"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Impuesto</label>
                        <div class="relative">
                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500 text-sm">$</span>
                            <input
                                type="number"
                                :value="tax"
                                @input="$emit('update:tax', parseFloat($event.target.value) || 0)"
                                class="w-full pl-6 pr-2 py-1.5 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                                placeholder="0.00"
                                step="0.01"
                            />
                        </div>
                    </div>
                </div>

                <!-- Resumen -->
                <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-3 space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                        <span class="text-gray-900 dark:text-white">${{ formatPrice(subtotal) }}</span>
                    </div>
                    <div v-if="discount > 0" class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Descuento:</span>
                        <span class="text-red-600 dark:text-red-400">-${{ formatPrice(discount) }}</span>
                    </div>
                    <div v-if="tax > 0" class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Impuesto:</span>
                        <span class="text-gray-900 dark:text-white">+${{ formatPrice(tax) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold border-t border-gray-300 dark:border-gray-600 pt-2 mt-2">
                        <span class="text-gray-900 dark:text-white">Total:</span>
                        <span class="text-green-600 dark:text-green-400">${{ formatPrice(total) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer con botones -->
        <template #footer>
            <div class="space-y-2">
                <button
                    v-if="hasItems"
                    @click="$emit('clear')"
                    class="w-full py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 flex items-center justify-center"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Limpiar carrito
                </button>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        @click="$emit('process-sale', 'save_pending')"
                        :disabled="!canComplete"
                        class="py-3 bg-orange-500 hover:bg-orange-600 disabled:bg-gray-400 text-white font-bold rounded-lg transition-colors flex items-center justify-center"
                    >
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Pendiente
                    </button>
                    <button
                        @click="$emit('process-sale', 'complete')"
                        :disabled="!canComplete"
                        class="py-3 bg-green-500 hover:bg-green-600 disabled:bg-gray-400 text-white font-bold rounded-lg transition-colors flex items-center justify-center"
                    >
                        <span v-if="processing" class="flex items-center">
                            <svg class="animate-spin w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <template v-else>
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Cobrar
                        </template>
                    </button>
                </div>
            </div>
        </template>
    </SlideOver>
</template>
