<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    table: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'updated']);

// State
const currentSale = ref(null);
const cartItems = ref([]);
const isProcessing = ref(false);
const updatingStatus = ref(null); // Track which status button is loading
const localTableStatus = ref(null); // Local status for instant UI feedback

// Load table details when opened
watch(() => props.show, (newVal) => {
    if (newVal && props.table) {
        loadTableDetails();
        localTableStatus.value = props.table.status;
    } else {
        resetState();
    }
});

// Sync local status when table prop changes
watch(() => props.table?.status, (newStatus) => {
    if (newStatus) {
        localTableStatus.value = newStatus;
    }
});

const loadTableDetails = () => {
    // If table is occupied, load current sale
    if (props.table?.status === 'ocupada' && props.table?.current_sale) {
        currentSale.value = props.table.current_sale;
        // Load sale items into cart
        cartItems.value = props.table.current_sale.sale_items?.map(item => ({
            id: item.id,
            name: item.product_type === 'menu' ? item.menu_item?.name : item.simple_product?.name,
            quantity: item.quantity,
            unit_price: parseFloat(item.unit_price),
            subtotal: parseFloat(item.subtotal)
        })) || [];
    } else {
        currentSale.value = null;
        cartItems.value = [];
    }
};

const resetState = () => {
    currentSale.value = null;
    cartItems.value = [];
    isProcessing.value = false;
    updatingStatus.value = null;
    localTableStatus.value = null;
};

// Computed
const cartTotal = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + item.subtotal, 0);
});

const isOccupied = computed(() => {
    return localTableStatus.value === 'ocupada';
});

const currentStatus = computed(() => {
    return localTableStatus.value || props.table?.status;
});

// Methods
const handleClose = () => {
    emit('close');
};

const updateStatus = async (newStatus) => {
    if (!props.table || updatingStatus.value) return;

    updatingStatus.value = newStatus;

    router.post(route('tables.update-status', props.table.id), {
        status: newStatus
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Update local status immediately for instant feedback
            localTableStatus.value = newStatus;
            // Emit event so parent can refresh if needed
            emit('updated', { id: props.table.id, status: newStatus });
        },
        onFinish: () => {
            updatingStatus.value = null;
        }
    });
};

const releaseTable = () => {
    if (!props.table) return;

    if (!confirm('¿Estás seguro de liberar esta mesa?')) return;

    isProcessing.value = true;

    router.post(route('tables.release', props.table.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            handleClose();
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const goToPOS = () => {
    // Close slideover and navigate to POS with table pre-selected
    handleClose();
    router.visit(route('sales.pos', { table_id: props.table.id }));
};

// Status color helper
const getStatusColorClass = (status) => {
    const colors = {
        disponible: 'text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900/30',
        ocupada: 'text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30',
        reservada: 'text-yellow-700 dark:text-yellow-300 bg-yellow-100 dark:bg-yellow-900/30',
        en_limpieza: 'text-blue-700 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/30',
    };
    return colors[status] || 'text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800';
};

const getStatusLabel = (status) => {
    const labels = {
        disponible: 'Disponible',
        ocupada: 'Ocupada',
        reservada: 'Reservada',
        en_limpieza: 'En Limpieza',
    };
    return labels[status] || status;
};
</script>

<template>
    <!-- Slide-over backdrop (SIN backdrop-blur para mejor rendimiento) -->
    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            @click="handleClose"
            class="fixed inset-0 bg-gray-900/50 z-40"
        ></div>
    </Transition>

    <!-- Slide-over panel -->
    <Transition
        enter-active-class="transition-transform duration-200 ease-out"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition-transform duration-200 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="show && table"
            class="fixed top-0 right-0 h-full w-full md:max-w-2xl bg-white dark:bg-gray-800 shadow-2xl z-50 overflow-y-auto transform-gpu"
        >
            <!-- Header -->
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <button
                            @click="handleClose"
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                Mesa {{ table.table_number }}
                            </h2>
                            <p v-if="table.name" class="text-sm text-gray-500 dark:text-gray-400">
                                {{ table.name }}
                            </p>
                        </div>
                    </div>

                    <span :class="['px-3 py-1 rounded-full text-sm font-semibold', getStatusColorClass(currentStatus)]">
                        {{ getStatusLabel(currentStatus) }}
                    </span>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6">
                <!-- Table Info -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Capacidad</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ table.capacity }} personas</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Estado</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ getStatusLabel(currentStatus) }}</p>
                        </div>
                    </div>

                    <div v-if="table.notes" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Notas</p>
                        <p class="text-sm text-gray-900 dark:text-white mt-1">{{ table.notes }}</p>
                    </div>
                </div>

                <!-- Current Sale Section (if occupied) -->
                <div v-if="isOccupied && currentSale" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Venta Actual</h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            #{{ currentSale.sale_number }}
                        </span>
                    </div>

                    <!-- Cart Items -->
                    <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg divide-y divide-gray-200 dark:divide-gray-600">
                        <div
                            v-for="item in cartItems"
                            :key="item.id"
                            class="p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ item.name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ item.quantity }} x ${{ item.unit_price.toFixed(2) }}
                                    </p>
                                </div>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                    ${{ item.subtotal.toFixed(2) }}
                                </p>
                            </div>
                        </div>

                        <div v-if="cartItems.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                            No hay productos en esta venta
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-semibold text-gray-700 dark:text-gray-300">Total</span>
                            <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                ${{ cartTotal.toFixed(2) }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions for occupied table -->
                    <div class="space-y-2">
                        <button
                            @click="releaseTable"
                            :disabled="isProcessing"
                            class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg disabled:opacity-50 flex items-center justify-center"
                        >
                            <svg v-if="isProcessing" class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ isProcessing ? 'Liberando...' : 'Liberar Mesa' }}
                        </button>

                        <p class="text-xs text-center text-gray-500 dark:text-gray-400">
                            Nota: Para procesar el pago de esta venta, ve al módulo de POS o Ventas
                        </p>
                    </div>
                </div>

                <!-- Available Table Section -->
                <div v-else class="space-y-4">
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 text-center">
                        <svg class="w-12 h-12 text-green-600 dark:text-green-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <h3 class="text-lg font-semibold text-green-900 dark:text-green-300 mb-2">Mesa Disponible</h3>
                        <p class="text-sm text-green-700 dark:text-green-400 mb-4">
                            Esta mesa está disponible para nuevos clientes
                        </p>

                        <button
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg"
                            @click="goToPOS"
                        >
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tomar Pedido en POS
                        </button>
                    </div>

                    <!-- Quick Status Changes -->
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Cambiar Estado</p>

                        <div class="grid grid-cols-2 gap-2">
                            <!-- Reservar -->
                            <button
                                v-if="currentStatus !== 'reservada'"
                                @click="updateStatus('reservada')"
                                :disabled="updatingStatus !== null"
                                class="px-4 py-2 bg-yellow-100 dark:bg-yellow-900/30 hover:bg-yellow-200 dark:hover:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300 font-medium rounded-lg disabled:opacity-50 flex items-center justify-center"
                            >
                                <svg v-if="updatingStatus === 'reservada'" class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Reservar
                            </button>

                            <!-- En Limpieza -->
                            <button
                                v-if="currentStatus !== 'en_limpieza'"
                                @click="updateStatus('en_limpieza')"
                                :disabled="updatingStatus !== null"
                                class="px-4 py-2 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 text-blue-800 dark:text-blue-300 font-medium rounded-lg disabled:opacity-50 flex items-center justify-center"
                            >
                                <svg v-if="updatingStatus === 'en_limpieza'" class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                En Limpieza
                            </button>

                            <!-- Disponible -->
                            <button
                                v-if="currentStatus !== 'disponible'"
                                @click="updateStatus('disponible')"
                                :disabled="updatingStatus !== null"
                                class="px-4 py-2 bg-green-100 dark:bg-green-900/30 hover:bg-green-200 dark:hover:bg-green-900/50 text-green-800 dark:text-green-300 font-medium rounded-lg disabled:opacity-50 flex items-center justify-center"
                            >
                                <svg v-if="updatingStatus === 'disponible'" class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Disponible
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-sm text-gray-600 dark:text-gray-400">
                    <p class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        Tip: Haz clic en las mesas del grid para gestionar pedidos rápidamente
                    </p>
                </div>
            </div>
        </div>
    </Transition>
</template>
