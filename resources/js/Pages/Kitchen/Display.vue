<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-200">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <!-- Back Button + Title -->
                    <div class="flex items-center space-x-4">
                        <Link
                            :href="route('dashboard')"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition-colors duration-200"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Panel
                        </Link>

                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                            <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path>
                            </svg>
                            <span>COCINA</span>
                        </h1>

                        <!-- Order Count Badge -->
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                            {{ orders.length }} {{ orders.length === 1 ? 'orden' : 'órdenes' }}
                        </span>
                    </div>

                    <!-- Clock + Dark Mode Toggle -->
                    <div class="flex items-center space-x-6">
                        <!-- Live Clock -->
                        <div class="text-2xl font-mono font-bold text-gray-900 dark:text-white">
                            {{ currentTime }}
                        </div>

                        <!-- Dark Mode Toggle -->
                        <button
                            @click="toggleDarkMode"
                            class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors duration-200"
                            aria-label="Toggle dark mode"
                        >
                            <svg v-if="isDarkMode" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path>
                            </svg>
                            <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Empty State -->
            <div v-if="orders.length === 0" class="text-center py-20">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gray-100 dark:bg-gray-800 mb-6">
                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    No hay órdenes pendientes
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Las nuevas órdenes aparecerán aquí automáticamente
                </p>
            </div>

            <!-- Orders Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div
                    v-for="order in orders"
                    :key="order.id"
                    class="bg-white dark:bg-gray-900 rounded-lg shadow-lg border-l-4 overflow-hidden transition-all duration-200 hover:shadow-xl"
                    :class="getColorClasses(order.color)"
                >
                    <!-- Card Header -->
                    <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ order.sale_number }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ order.table_name }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold" :class="getTextColorClass(order.color)">
                                    {{ order.elapsed_minutes }}m
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-500">
                                    {{ order.created_at }}
                                </div>
                            </div>
                        </div>

                        <!-- Customer Name -->
                        <div v-if="order.customer_name" class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                            {{ order.customer_name }}
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-950 space-y-2">
                        <div
                            v-for="(item, index) in order.items"
                            :key="index"
                            class="flex items-start space-x-2 text-sm"
                        >
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold text-xs flex-shrink-0">
                                {{ item.quantity }}
                            </span>
                            <span class="text-gray-900 dark:text-white font-medium">
                                {{ item.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="order.notes" class="px-4 py-3 bg-yellow-50 dark:bg-yellow-900/10 border-t border-yellow-100 dark:border-yellow-900/20">
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-xs text-yellow-800 dark:text-yellow-400">
                                {{ order.notes }}
                            </p>
                        </div>
                    </div>

                    <!-- Status Actions -->
                    <div class="p-4 border-t border-gray-200 dark:border-gray-800">
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                @click="updateStatus(order.id, 'preparando')"
                                :disabled="updatingOrderId === order.id || order.status === 'preparando' || order.status === 'lista'"
                                class="px-3 py-2 text-xs font-semibold rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                                :class="order.status === 'nueva'
                                    ? 'bg-blue-600 hover:bg-blue-700 text-white'
                                    : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'"
                            >
                                <svg v-if="updatingOrderId === order.id" class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else-if="order.status === 'preparando'" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span v-if="order.status === 'preparando'">En proceso</span>
                                <span v-else>Iniciar</span>
                            </button>

                            <button
                                @click="updateStatus(order.id, 'lista')"
                                :disabled="updatingOrderId === order.id || order.status === 'nueva' || order.status === 'lista'"
                                class="px-3 py-2 text-xs font-semibold rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                                :class="order.status === 'preparando'
                                    ? 'bg-green-600 hover:bg-green-700 text-white'
                                    : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'"
                            >
                                <svg v-if="updatingOrderId === order.id" class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else-if="order.status === 'lista'" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span v-if="order.status === 'lista'">Lista</span>
                                <span v-else>Listo</span>
                            </button>

                            <button
                                @click="updateStatus(order.id, 'entregada')"
                                :disabled="updatingOrderId === order.id || order.status !== 'lista'"
                                class="px-3 py-2 text-xs font-semibold rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                                :class="order.status === 'lista'
                                    ? 'bg-purple-600 hover:bg-purple-700 text-white'
                                    : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'"
                            >
                                <svg v-if="updatingOrderId === order.id" class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Entregar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Audio Element for Alerts -->
        <audio ref="alertSound" preload="auto">
            <source src="/sounds/new-order.mp3" type="audio/mpeg">
        </audio>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

// State
const orders = ref([]);
const currentTime = ref('');
const isDarkMode = ref(false);
const alertSound = ref(null);
const updatingOrderId = ref(null);
let pollingInterval = null;
let clockInterval = null;
let previousOrderIds = [];
let isFirstLoad = true;

// Dark Mode
const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('darkMode', 'true');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('darkMode', 'false');
    }
};

// Initialize dark mode from localStorage
const initDarkMode = () => {
    const savedMode = localStorage.getItem('darkMode');
    if (savedMode === 'true' || (!savedMode && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    }
};

// Update Clock
const updateClock = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('es-MX', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });
};

// Fetch Orders (Polling)
const fetchOrders = async () => {
    try {
        const response = await axios.get(route('kitchen.api.orders'));
        const newOrders = response.data;

        // Check for new orders (compare IDs)
        const newOrderIds = newOrders.map(order => order.id);
        const hasNewOrders = newOrderIds.some(id => !previousOrderIds.includes(id));

        // Play sound if there are new orders (skip only on very first load)
        if (hasNewOrders && !isFirstLoad) {
            playAlert();
        }

        // Mark that first load is complete
        if (isFirstLoad) {
            isFirstLoad = false;
        }

        orders.value = newOrders;
        previousOrderIds = newOrderIds;
    } catch (error) {
        console.error('Error fetching orders:', error);
    }
};

// Update Order Status
const updateStatus = async (orderId, newStatus) => {
    // Set loading state
    updatingOrderId.value = orderId;

    try {
        await axios.patch(route('kitchen.api.update-status', orderId), {
            status: newStatus
        });

        // Refresh orders immediately
        await fetchOrders();
    } catch (error) {
        console.error('Error updating status:', error);
        alert('Error al actualizar el estado de la orden');
    } finally {
        // Clear loading state
        updatingOrderId.value = null;
    }
};

// Play Alert Sound
const playAlert = () => {
    if (alertSound.value) {
        alertSound.value.play().catch(err => {
            console.error('Error playing sound:', err);
        });
    }
};

// Color Utility Functions
const getColorClasses = (color) => {
    const classes = {
        green: 'border-l-green-500 dark:border-l-green-400',
        yellow: 'border-l-yellow-500 dark:border-l-yellow-400',
        orange: 'border-l-orange-500 dark:border-l-orange-400',
        red: 'border-l-red-500 dark:border-l-red-400'
    };
    return classes[color] || classes.green;
};

const getTextColorClass = (color) => {
    const classes = {
        green: 'text-green-600 dark:text-green-400',
        yellow: 'text-yellow-600 dark:text-yellow-400',
        orange: 'text-orange-600 dark:text-orange-400',
        red: 'text-red-600 dark:text-red-400'
    };
    return classes[color] || classes.green;
};

// Lifecycle
onMounted(() => {
    initDarkMode();
    updateClock();
    fetchOrders();

    // Start polling every 5 seconds
    pollingInterval = setInterval(fetchOrders, 5000);

    // Update clock every second
    clockInterval = setInterval(updateClock, 1000);
});

onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
    if (clockInterval) {
        clearInterval(clockInterval);
    }
});
</script>
