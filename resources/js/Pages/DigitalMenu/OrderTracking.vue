<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import DigitalMenuLayout from '@/Layouts/DigitalMenuLayout.vue';

const props = defineProps({
    sale: Object,
});

// Estados dinámicos basados en lo que viene de cocina
const currentKitchenStatus = ref(props.sale.kitchen_status);
const currentStatusLabel = ref(props.sale.status_label);
const currentStatusColor = ref(props.sale.status_color);
const elapsedMinutes = ref(props.sale.elapsed_minutes);
let refreshInterval = null;

// Timeline de estados (del backend)
const statuses = [
    { key: 'nueva', label: 'Pedido Recibido', icon: 'check', color: 'blue' },
    { key: 'preparando', label: 'En Preparación', icon: 'fire', color: 'yellow' },
    { key: 'lista', label: 'Lista para Recoger', icon: 'check-circle', color: 'green' },
    { key: 'entregada', label: 'Entregada', icon: 'star', color: 'gray' },
];

const currentStepIndex = computed(() => {
    return statuses.findIndex(s => s.key === currentKitchenStatus.value);
});

const deliveryMethodLabel = computed(() => {
    const labels = {
        'pickup': 'Para llevar',
        'delivery': 'Delivery',
        'dine_in': 'Comer aquí',
    };
    return labels[props.sale.delivery_method] || props.sale.delivery_method;
});

const isStatusActive = (index) => {
    return index <= currentStepIndex.value;
};

const isStatusCurrent = (index) => {
    return index === currentStepIndex.value;
};

const getBadgeColor = (color) => {
    const colors = {
        'blue': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-300 dark:border-blue-700',
        'yellow': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 border-yellow-300 dark:border-yellow-700',
        'green': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border-green-300 dark:border-green-700',
        'gray': 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600',
        'red': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border-red-300 dark:border-red-700',
    };
    return colors[color] || colors['blue'];
};

const refreshStatus = async () => {
    try {
        const response = await fetch(route('digital-menu.order.status', props.sale.sale_number));
        const data = await response.json();

        // Actualizar con datos del backend
        currentKitchenStatus.value = data.kitchen_status;
        currentStatusLabel.value = data.status_label;
        currentStatusColor.value = data.status_color;
        elapsedMinutes.value = data.elapsed_minutes;

        // Stop refreshing if order is completed or cancelled
        if (data.kitchen_status === 'entregada' || data.kitchen_status === 'cancelada') {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }
    } catch (error) {
        console.error('Error refreshing status:', error);
    }
};

onMounted(() => {
    // Refresh status every 15 seconds for live updates
    if (currentKitchenStatus.value !== 'entregada' && currentKitchenStatus.value !== 'cancelada') {
        refreshInterval = setInterval(refreshStatus, 15000);
    }
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<template>
    <DigitalMenuLayout>
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="mb-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 dark:bg-green-900/20 rounded-full mb-4">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    Pedido #{{ sale.sale_number }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mb-3">
                    {{ deliveryMethodLabel }}
                    <span v-if="sale.table_number">- Mesa {{ sale.table_number }}</span>
                </p>

                <!-- Current Status Badge -->
                <div class="inline-flex items-center gap-2">
                    <span
                        class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold border-2"
                        :class="getBadgeColor(currentStatusColor)"
                    >
                        <span class="w-2 h-2 rounded-full mr-2" :class="`bg-${currentStatusColor}-500`"></span>
                        {{ currentStatusLabel }}
                    </span>
                    <span
                        v-if="elapsedMinutes !== null"
                        class="text-sm text-gray-500 dark:text-gray-400"
                    >
                        · {{ elapsedMinutes }} min
                    </span>
                </div>
            </div>

            <!-- Status Timeline -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Estado de tu pedido</h2>

                <div class="relative">
                    <!-- Progress Line -->
                    <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                    <div
                        class="absolute left-6 top-0 w-0.5 bg-orange-500 dark:bg-orange-400 transition-all duration-500"
                        :style="{ height: `${(currentStepIndex / (statuses.length - 1)) * 100}%` }"
                    ></div>

                    <!-- Steps -->
                    <div class="space-y-8">
                        <div
                            v-for="(status, index) in statuses"
                            :key="status.key"
                            class="relative flex items-start"
                        >
                            <!-- Icon -->
                            <div
                                class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300 z-10"
                                :class="isStatusActive(index)
                                    ? 'bg-orange-500 dark:bg-orange-600 text-white'
                                    : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500'
                                "
                            >
                                <svg v-if="status.icon === 'check'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <svg v-else-if="status.icon === 'fire'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                </svg>
                                <svg v-else-if="status.icon === 'check-circle'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </div>

                            <!-- Content -->
                            <div class="ml-4 flex-1">
                                <h3
                                    class="text-base font-semibold transition-colors duration-300"
                                    :class="isStatusActive(index)
                                        ? 'text-gray-900 dark:text-white'
                                        : 'text-gray-400 dark:text-gray-600'
                                    "
                                >
                                    {{ status.label }}
                                </h3>
                                <p
                                    v-if="isStatusCurrent(index)"
                                    class="text-sm text-orange-600 dark:text-orange-400 mt-1 flex items-center"
                                >
                                    <span class="inline-block w-2 h-2 bg-orange-500 rounded-full mr-2 animate-pulse"></span>
                                    En proceso...
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Detalles del pedido</h2>

                <div class="space-y-3">
                    <div
                        v-for="(item, index) in sale.items"
                        :key="index"
                        class="flex items-center justify-between py-2"
                    >
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ item.quantity }}x {{ item.name }}
                            </p>
                        </div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            ${{ parseFloat(item.total_price).toFixed(2) }}
                        </p>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4">
                    <div class="flex items-center justify-between text-lg font-bold">
                        <span class="text-gray-900 dark:text-white">Total</span>
                        <span class="text-orange-600 dark:text-orange-400">${{ parseFloat(sale.total).toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Customer Notes -->
            <div v-if="sale.customer_notes" class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-6 mb-6">
                <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-2">Notas especiales</h3>
                <p class="text-sm text-blue-800 dark:text-blue-300">{{ sale.customer_notes }}</p>
            </div>

            <!-- Estimated Time -->
            <div v-if="sale.estimated_ready_at && currentStatus !== 'completada'" class="text-center p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Tiempo estimado de preparacion
                </p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                    {{ new Date(sale.estimated_ready_at).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) }}
                </p>
            </div>

            <!-- Back to Menu -->
            <div class="mt-8 text-center">
                <a
                    :href="route('digital-menu.index')"
                    class="inline-flex items-center text-orange-600 dark:text-orange-400 hover:underline"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver al menu
                </a>
            </div>
        </div>
    </DigitalMenuLayout>
</template>
