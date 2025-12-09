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

const emit = defineEmits(['close']);

// State
const currentSale = ref(null);
const cartItems = ref([]);
const isProcessing = ref(false);

// Load table details when opened
watch(() => props.show, (newVal) => {
    if (newVal && props.table) {
        loadTableDetails();
    } else {
        resetState();
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
};

// Computed
const cartTotal = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + item.subtotal, 0);
});

const isOccupied = computed(() => {
    return props.table?.status === 'ocupada';
});

// Methods
const handleClose = () => {
    emit('close');
};

const updateStatus = (newStatus) => {
    if (!props.table) return;

    router.post(route('tables.update-status', props.table.id), {
        status: newStatus
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Reload will be handled by parent
        }
    });
};

const releaseTable = () => {
    if (!props.table) return;

    if (!confirm('¿Estás seguro de liberar esta mesa?')) return;

    router.post(route('tables.release', props.table.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            handleClose();
        }
    });
};

// Status color helper
const getStatusColorClass = (status) => {
    const colors = {
        disponible: 'text-green-700 bg-green-100',
        ocupada: 'text-red-700 bg-red-100',
        reservada: 'text-yellow-700 bg-yellow-100',
        en_limpieza: 'text-blue-700 bg-blue-100',
    };
    return colors[status] || 'text-gray-700 bg-gray-100';
};
</script>

<template>
    <!-- Slide-over backdrop -->
    <Transition
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            @click="handleClose"
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40"
        ></div>
    </Transition>

    <!-- Slide-over panel -->
    <Transition
        enter-active-class="transition-transform duration-300"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition-transform duration-300"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="show && table"
            class="fixed top-0 right-0 h-full w-full md:w-2/3 lg:w-1/2 bg-white dark:bg-gray-800 shadow-2xl z-50 overflow-y-auto"
        >
            <!-- Header -->
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <button
                            @click="handleClose"
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
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

                    <span :class="['px-3 py-1 rounded-full text-sm font-semibold', getStatusColorClass(table.status)]">
                        {{ table.status_label }}
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
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ table.status_label }}</p>
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
                            class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50"
                        >
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Liberar Mesa
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
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
                            @click="router.visit(route('sales.pos', { table_id: table.id }))"
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
                            <button
                                v-if="table.status !== 'reservada'"
                                @click="updateStatus('reservada')"
                                class="px-4 py-2 bg-yellow-100 dark:bg-yellow-900/30 hover:bg-yellow-200 dark:hover:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300 font-medium rounded-lg transition-colors"
                            >
                                Reservar
                            </button>

                            <button
                                v-if="table.status !== 'en_limpieza'"
                                @click="updateStatus('en_limpieza')"
                                class="px-4 py-2 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 text-blue-800 dark:text-blue-300 font-medium rounded-lg transition-colors"
                            >
                                En Limpieza
                            </button>

                            <button
                                v-if="table.status !== 'disponible'"
                                @click="updateStatus('disponible')"
                                class="px-4 py-2 bg-green-100 dark:bg-green-900/30 hover:bg-green-200 dark:hover:bg-green-900/50 text-green-800 dark:text-green-300 font-medium rounded-lg transition-colors"
                            >
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
                        Tip: Haz clic en las mesas del grid para gestionar  pedidos rápidamente
                    </p>
                </div>
            </div>
        </div>
    </Transition>
</template>
