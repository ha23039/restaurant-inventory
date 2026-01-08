<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
});

const emit = defineEmits(['close']);

const loading = ref(false);
const authenticated = ref(false);
const customer = ref(null);
const orders = ref([]);
const statusFilter = ref('all');

// Touch gesture state
const touchStart = ref({ y: 0 });
const touchDelta = ref(0);
const isDragging = ref(false);

// Status options
const statusOptions = [
    { value: 'all', label: 'Todos' },
    { value: 'pendiente', label: 'Pendiente' },
    { value: 'en_preparacion', label: 'En preparación' },
    { value: 'lista', label: 'Lista' },
    { value: 'completada', label: 'Completada' },
    { value: 'cancelada', label: 'Cancelada' },
];

// Filtered orders
const filteredOrders = computed(() => {
    if (statusFilter.value === 'all') {
        return orders.value;
    }
    return orders.value.filter(order => order.status === statusFilter.value);
});

// Load orders when slideOver opens
watch(() => props.show, async (newVal) => {
    if (newVal) {
        await loadOrders();
    }
});

const loadOrders = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/digital-menu/orders/my-orders', {
            params: { status: statusFilter.value }
        });

        if (response.data.authenticated) {
            authenticated.value = true;
            customer.value = response.data.customer;
            orders.value = response.data.orders;
        } else {
            authenticated.value = false;
        }
    } catch (error) {
        console.error('Error loading orders:', error);
        authenticated.value = false;
    } finally {
        loading.value = false;
    }
};

const viewOrder = (order) => {
    window.open(order.tracking_url, '_blank');
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'pendiente': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 border-yellow-300 dark:border-yellow-700',
        'en_preparacion': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-300 dark:border-blue-700',
        'lista': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border-green-300 dark:border-green-700',
        'completada': 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600',
        'cancelada': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border-red-300 dark:border-red-700',
    };
    return classes[status] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300';
};

const getStatusLabel = (status) => {
    const labels = {
        'pendiente': 'Pendiente',
        'en_preparacion': 'En preparación',
        'lista': 'Lista',
        'completada': 'Completada',
        'cancelada': 'Cancelada',
    };
    return labels[status] || status;
};

const getDeliveryMethodLabel = (method) => {
    const labels = {
        'pickup': 'Para llevar',
        'delivery': 'Delivery',
        'dine_in': 'Comer aquí',
    };
    return labels[method] || method;
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Touch handlers for swipe-to-close
const handleTouchStart = (e) => {
    touchStart.value.y = e.touches[0].clientY;
    touchDelta.value = 0;
    isDragging.value = false;
};

const handleTouchMove = (e) => {
    const deltaY = e.touches[0].clientY - touchStart.value.y;
    if (deltaY > 5) {
        isDragging.value = true;
        touchDelta.value = Math.min(deltaY * 0.8, 250);
    }
};

const handleTouchEnd = () => {
    if (isDragging.value && touchDelta.value > 80) {
        emit('close');
    }
    touchDelta.value = 0;
    isDragging.value = false;
};
</script>

<template>
    <!-- Backdrop -->
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
            @click="emit('close')"
            class="fixed inset-0 bg-black/60 z-50"
        ></div>
    </Transition>

    <!-- SlideOver -->
    <Transition
        enter-active-class="transition-transform duration-300"
        enter-from-class="translate-y-full"
        enter-to-class="translate-y-0"
        leave-active-class="transition-transform duration-200"
        leave-from-class="translate-y-0"
        leave-to-class="translate-y-full"
    >
        <div
            v-if="show"
            @touchstart="handleTouchStart"
            @touchmove.passive="handleTouchMove"
            @touchend="handleTouchEnd"
            class="fixed inset-x-0 bottom-0 z-50 max-h-[90vh] bg-white dark:bg-gray-800 rounded-t-2xl shadow-xl flex flex-col"
            :style="{
                transform: isDragging ? `translateY(${touchDelta}px)` : '',
                transition: isDragging ? 'none' : 'transform 0.2s ease-out'
            }"
        >
            <!-- Handle Bar -->
            <div class="flex justify-center pt-3 pb-2 cursor-grab">
                <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
            </div>

            <!-- Header -->
            <div class="px-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            Mis Pedidos
                        </h2>
                        <p v-if="customer" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ customer.name }}
                        </p>
                    </div>
                    <button
                        @click="emit('close')"
                        class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Status Filter -->
                <div class="mt-4 flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                    <button
                        v-for="option in statusOptions"
                        :key="option.value"
                        @click="statusFilter = option.value; loadOrders()"
                        class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium transition-all"
                        :class="statusFilter === option.value
                            ? 'bg-orange-500 text-white shadow-lg'
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                    >
                        {{ option.label }}
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <!-- Loading State -->
                <div v-if="loading" class="flex items-center justify-center py-12">
                    <svg class="animate-spin h-8 w-8 text-orange-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Not Authenticated -->
                <div v-else-if="!authenticated" class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                        Inicia sesión para ver tus pedidos
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Crea un pedido para ver tu historial
                    </p>
                </div>

                <!-- Empty State -->
                <div v-else-if="filteredOrders.length === 0" class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                        No hay pedidos
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Aún no has realizado ningún pedido
                    </p>
                </div>

                <!-- Orders List -->
                <div v-else class="space-y-4">
                    <div
                        v-for="order in filteredOrders"
                        :key="order.id"
                        @click="viewOrder(order)"
                        class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-orange-500 dark:hover:border-orange-400 cursor-pointer transition-all hover:shadow-md active:scale-98"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                    #{{ order.sale_number }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ formatDate(order.created_at) }}
                                </p>
                            </div>
                            <span
                                class="flex-shrink-0 px-3 py-1 rounded-full text-xs font-semibold border"
                                :class="getStatusBadgeClass(order.status)"
                            >
                                {{ getStatusLabel(order.status) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>{{ order.items_count }} artículo{{ order.items_count > 1 ? 's' : '' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="font-bold text-orange-600 dark:text-orange-400">${{ Number(order.total).toFixed(2) }}</span>
                            </div>
                            <div class="col-span-2 flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{{ getDeliveryMethodLabel(order.delivery_method) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.active\:scale-98:active {
    transform: scale(0.98);
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
