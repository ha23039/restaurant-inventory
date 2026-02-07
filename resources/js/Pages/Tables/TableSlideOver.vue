<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import TableChargeSlideOver from './TableChargeSlideOver.vue';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    table: {
        type: Object,
        default: null
    },
    paymentMethods: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'updated']);
const { confirm } = useConfirmDialog();

// State
const pendingSales = ref([]);
const totalPending = ref(0);
const salesCount = ref(0);
const isLoading = ref(false);
const isProcessing = ref(false);
const updatingStatus = ref(null);
const localTableStatus = ref(null);

// Touch gesture state for horizontal swipe-to-close
const touchStart = ref({ x: 0 });
const touchDelta = ref(0);
const isDragging = ref(false);

// Charge slideover state
const showChargeSlideOver = ref(false);
const chargeMode = ref('single'); // 'single' or 'all'
const selectedSale = ref(null);

// Polling
let pollingInterval = null;

// Load pending sales when opened
watch(() => props.show, (newVal) => {
    if (newVal && props.table) {
        localTableStatus.value = props.table.status;
        loadPendingSales();
        startPolling();
    } else {
        resetState();
        stopPolling();
    }
});

// Sync local status when table prop changes
watch(() => props.table?.status, (newStatus) => {
    if (newStatus) {
        localTableStatus.value = newStatus;
    }
});

const loadPendingSales = async () => {
    if (!props.table) return;

    isLoading.value = true;
    try {
        const response = await fetch(route('tables.pending-sales', props.table.id));
        const data = await response.json();

        pendingSales.value = data.pending_sales || [];
        totalPending.value = data.total_pending || 0;
        salesCount.value = data.sales_count || 0;

        // Update local status from response
        if (data.table?.status) {
            localTableStatus.value = data.table.status;
        }
    } catch (error) {
        console.error('Error loading pending sales:', error);
    } finally {
        isLoading.value = false;
    }
};

const startPolling = () => {
    stopPolling();
    pollingInterval = setInterval(loadPendingSales, 10000); // Every 10 seconds
};

const stopPolling = () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
};

const resetState = () => {
    pendingSales.value = [];
    totalPending.value = 0;
    salesCount.value = 0;
    isLoading.value = false;
    isProcessing.value = false;
    updatingStatus.value = null;
    localTableStatus.value = null;
    showChargeSlideOver.value = false;
    selectedSale.value = null;
    // Reset touch state
    touchDelta.value = 0;
    isDragging.value = false;
};

// Computed
const isOccupied = computed(() => {
    return localTableStatus.value === 'ocupada';
});

const currentStatus = computed(() => {
    return localTableStatus.value || props.table?.status;
});

const hasPendingSales = computed(() => {
    return pendingSales.value.length > 0;
});

// Touch gesture handlers for swipe-to-close
const handleTouchStart = (e) => {
    touchStart.value.x = e.touches[0].clientX;
    touchDelta.value = 0;
    isDragging.value = false;
};

const handleTouchMove = (e) => {
    const deltaX = e.touches[0].clientX - touchStart.value.x;

    // Only allow dragging right (positive delta)
    if (deltaX > 20) {
        isDragging.value = true;
        touchDelta.value = Math.min(deltaX, 300);
    }
};

const handleTouchEnd = () => {
    if (isDragging.value && touchDelta.value > 100) {
        handleClose();
    }
    touchDelta.value = 0;
    isDragging.value = false;
};

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
            localTableStatus.value = newStatus;
            emit('updated', { id: props.table.id, status: newStatus });
        },
        onFinish: () => {
            updatingStatus.value = null;
        }
    });
};

const releaseTable = async () => {
    if (!props.table) return;

    // Check if there are pending sales
    if (hasPendingSales.value) {
        const confirmed = await confirm({
            title: 'Hay pedidos pendientes',
            message: `Esta mesa tiene ${salesCount.value} pedido(s) sin cobrar. ¿Deseas liberarla de todos modos?`,
            confirmText: 'Liberar de todos modos',
            type: 'warning'
        });

        if (!confirmed) return;
    }

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
    handleClose();
    router.visit(route('sales.pos', { table_id: props.table.id }));
};

// Charge actions
const openChargeSingle = (sale) => {
    selectedSale.value = sale;
    chargeMode.value = 'single';
    showChargeSlideOver.value = true;
};

const openChargeAll = () => {
    selectedSale.value = null;
    chargeMode.value = 'all';
    showChargeSlideOver.value = true;
};

const handleCharged = (result) => {
    showChargeSlideOver.value = false;

    // Reload pending sales
    loadPendingSales();

    // If table was released, close the slideover
    if (result.table_released) {
        emit('updated', { id: props.table.id, status: 'disponible' });
        handleClose();
    } else {
        emit('updated', { id: props.table.id });
    }
};

// Status helpers
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

// Kitchen status icons are rendered as SVG in template

// Lifecycle
onUnmounted(() => {
    stopPolling();
});
</script>

<template>
    <!-- Slide-over backdrop -->
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
            @touchstart="handleTouchStart"
            @touchmove.passive="handleTouchMove"
            @touchend="handleTouchEnd"
            class="fixed top-0 right-0 h-full w-full md:max-w-2xl bg-white dark:bg-gray-800 shadow-2xl z-50 overflow-y-auto transform-gpu"
            :style="isDragging ? { transform: `translateX(${touchDelta}px)`, transition: 'none' } : {}"
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

                    <div class="flex items-center space-x-2">
                        <!-- Refresh indicator -->
                        <button
                            @click="loadPendingSales"
                            :disabled="isLoading"
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400"
                            title="Actualizar"
                        >
                            <svg :class="['w-5 h-5', isLoading && 'animate-spin']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>

                        <span :class="['px-3 py-1 rounded-full text-sm font-semibold', getStatusColorClass(currentStatus)]">
                            {{ getStatusLabel(currentStatus) }}
                        </span>
                    </div>
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
                            <p class="text-sm text-gray-500 dark:text-gray-400">Pedidos Activos</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ salesCount }}</p>
                        </div>
                    </div>
                </div>

                <!-- Pending Sales Section -->
                <div v-if="hasPendingSales" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Pedidos en esta Mesa
                        </h3>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Total: ${{ totalPending.toFixed(2) }}
                        </span>
                    </div>

                    <!-- Sales List -->
                    <div class="space-y-4">
                        <div
                            v-for="sale in pendingSales"
                            :key="sale.id"
                            class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl overflow-hidden shadow-sm"
                        >
                            <!-- Sale Header -->
                            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ sale.customer_name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            #{{ sale.sale_number }} • {{ sale.created_at }}
                                            <span v-if="sale.customer_phone" class="ml-1">• {{ sale.customer_phone }}</span>
                                        </p>
                                    </div>
                                </div>
                                <span :class="['px-2 py-1 rounded-full text-xs font-medium', getKitchenStatusColor(sale.kitchen_status)]">
                                    {{ getKitchenStatusLabel(sale.kitchen_status) }}
                                </span>
                            </div>

                            <div class="px-4 py-3 divide-y divide-gray-100 dark:divide-gray-600">
                                <div
                                    v-for="item in sale.items"
                                    :key="item.id"
                                    class="py-2"
                                >
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start">
                                            <span class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-600 text-xs font-medium flex items-center justify-center text-gray-700 dark:text-gray-300 mr-3 mt-0.5 flex-shrink-0">
                                                {{ item.quantity }}
                                            </span>
                                            <div>
                                                <span :class="[
                                                    'text-sm',
                                                    item.is_combo ? 'font-medium text-orange-700 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300'
                                                ]">
                                                    {{ item.name }}
                                                    <span v-if="item.is_combo" class="ml-1 px-1.5 py-0.5 text-xs bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-full">
                                                        Combo
                                                    </span>
                                                </span>
                                                <!-- Componentes del combo -->
                                                <div v-if="item.is_combo && item.combo_components" class="mt-1 pl-2 border-l-2 border-orange-200 dark:border-orange-800 space-y-0.5">
                                                    <div 
                                                        v-for="component in item.combo_components" 
                                                        :key="component.id"
                                                        class="text-xs text-gray-500 dark:text-gray-400 flex items-center"
                                                    >
                                                        <span class="w-4 text-center mr-1">{{ component.quantity }}x</span>
                                                        <span>{{ component.name }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white flex-shrink-0">
                                            ${{ item.subtotal.toFixed(2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Sale Footer -->
                            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 flex items-center justify-between">
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Subtotal:</span>
                                    <span class="ml-2 text-lg font-bold text-gray-900 dark:text-white">
                                        ${{ sale.total.toFixed(2) }}
                                    </span>
                                </div>
                                <button
                                    @click="openChargeSingle(sale)"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Cobrar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="space-y-3 pt-4">
                        <!-- Add Another Order Button -->
                        <button
                            @click="goToPOS"
                            class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-xl transition-all flex items-center justify-center shadow-lg"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Añadir Otro Pedido
                        </button>

                        <!-- Charge All Button -->
                        <button
                            v-if="salesCount > 1"
                            @click="openChargeAll"
                            class="w-full px-4 py-4 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold rounded-xl transition-all flex items-center justify-center shadow-lg"
                        >
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Cobrar Todo Junto (${{ totalPending.toFixed(2) }})
                        </button>

                        <!-- Release Table Button -->
                        <button
                            @click="releaseTable"
                            :disabled="isProcessing"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center justify-center"
                        >
                            <svg v-if="isProcessing" class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ isProcessing ? 'Liberando...' : 'Liberar Mesa' }}
                        </button>
                    </div>
                </div>

                <!-- Empty State (no pending sales but occupied) -->
                <div v-else-if="isOccupied && !isLoading" class="text-center py-8">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Sin pedidos pendientes</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                        Esta mesa está ocupada pero no tiene pedidos activos.
                    </p>
                    <div class="flex flex-col gap-3">
                        <button
                            @click="goToPOS"
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg"
                        >
                            Tomar Pedido en POS
                        </button>
                        <button
                            @click="releaseTable"
                            :disabled="isProcessing"
                            class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Liberar Mesa
                        </button>
                    </div>
                </div>

                <!-- Available Table Section -->
                <div v-else-if="!isOccupied && !isLoading" class="space-y-4">
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 text-center">
                        <svg class="w-12 h-12 text-green-600 dark:text-green-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <h3 class="text-lg font-semibold text-green-900 dark:text-green-300 mb-2">Mesa Disponible</h3>
                        <p class="text-sm text-green-700 dark:text-green-400 mb-4">
                            Esta mesa está lista para nuevos clientes
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

                <!-- Loading State -->
                <div v-if="isLoading && !hasPendingSales" class="flex items-center justify-center py-12">
                    <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Charge SlideOver (Nested) -->
    <TableChargeSlideOver
        :show="showChargeSlideOver"
        :sale="selectedSale"
        :sales="pendingSales"
        :table-id="table?.id"
        :mode="chargeMode"
        :payment-methods-from-db="paymentMethods"
        @close="showChargeSlideOver = false"
        @charged="handleCharged"
    />
</template>
