<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-950 transition-colors duration-200">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm sticky top-0 z-10">
            <div class="max-w-full mx-auto px-2 sm:px-4 lg:px-6 py-2 md:py-3">
                <!-- Top Row -->
                <div class="flex items-center justify-between">
                    <!-- Left: Back + Title -->
                    <div class="flex items-center space-x-2 md:space-x-4">
                        <Link
                            :href="route('dashboard')"
                            class="inline-flex items-center justify-center w-10 h-10 md:px-4 md:py-2 md:w-auto bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            <span class="hidden md:inline ml-2">Panel</span>
                        </Link>

                        <div class="flex items-center space-x-2">
                            <svg class="w-6 h-6 md:w-8 md:h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path>
                            </svg>
                            <h1 class="text-lg md:text-2xl font-bold text-gray-900 dark:text-white">COCINA</h1>
                        </div>
                    </div>

                    <!-- Right: Clock + Controls -->
                    <div class="flex items-center space-x-2 md:space-x-4">
                        <!-- Live Clock -->
                        <div class="text-lg md:text-2xl font-mono font-bold text-gray-900 dark:text-white">
                            {{ currentTime }}
                        </div>

                        <!-- Sound Toggle -->
                        <button
                            @click="toggleSound"
                            class="p-2 rounded-lg transition-colors"
                            :class="soundEnabled
                                ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400'
                                : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600'"
                            :title="soundEnabled ? 'Sonido activado' : 'Sonido silenciado'"
                        >
                            <svg v-if="soundEnabled" class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                            </svg>
                            <svg v-else class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                            </svg>
                        </button>

                        <!-- Fullscreen Toggle -->
                        <button
                            @click="toggleFullscreen"
                            class="hidden md:block p-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors"
                            title="Pantalla completa"
                        >
                            <svg v-if="!isFullscreen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                            </svg>
                            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <!-- Dark Mode Toggle -->
                        <button
                            @click="toggleDarkMode"
                            class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors"
                        >
                            <svg v-if="isDarkMode" class="w-5 h-5 md:w-6 md:h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5 md:w-6 md:h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Filters Row -->
                <div class="mt-3 flex items-center justify-between">
                    <!-- Status Filters -->
                    <div class="flex items-center space-x-1 md:space-x-2 overflow-x-auto pb-1">
                        <button
                            v-for="filter in filters"
                            :key="filter.value"
                            @click="activeFilter = filter.value"
                            class="flex items-center space-x-1 px-3 py-1.5 md:px-4 md:py-2 rounded-full text-xs md:text-sm font-semibold whitespace-nowrap transition-all"
                            :class="activeFilter === filter.value
                                ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900'
                                : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'"
                        >
                            <span>{{ filter.label }}</span>
                            <span
                                v-if="filter.count > 0"
                                class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-bold"
                                :class="activeFilter === filter.value
                                    ? 'bg-white/20 dark:bg-gray-900/20'
                                    : filter.color"
                            >
                                {{ filter.count }}
                            </span>
                        </button>
                    </div>

                    <!-- Total Orders -->
                    <div class="hidden sm:flex items-center text-sm text-gray-500 dark:text-gray-400">
                        <span class="font-medium">{{ orders.length }}</span>
                        <span class="ml-1">{{ orders.length === 1 ? 'orden total' : 'órdenes totales' }}</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-full mx-auto px-2 sm:px-4 lg:px-6 py-4 md:py-6">
            <!-- Empty State -->
            <div v-if="filteredOrders.length === 0" class="text-center py-16 md:py-20">
                <div class="inline-flex items-center justify-center w-20 h-20 md:w-24 md:h-24 rounded-full bg-gray-200 dark:bg-gray-800 mb-4 md:mb-6">
                    <svg class="w-10 h-10 md:w-12 md:h-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-lg md:text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    {{ activeFilter === 'all' ? 'No hay órdenes pendientes' : `No hay órdenes ${getFilterLabel(activeFilter).toLowerCase()}` }}
                </h3>
                <p class="text-sm md:text-base text-gray-600 dark:text-gray-400">
                    Las nuevas órdenes aparecerán aquí automáticamente
                </p>
            </div>

            <!-- Orders Grid -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 md:gap-4">
                <div
                    v-for="order in filteredOrders"
                    :key="order.id"
                    class="bg-white dark:bg-gray-900 rounded-xl shadow-lg border-l-4 overflow-hidden transition-all duration-200 hover:shadow-xl"
                    :class="[
                        getColorClasses(order.color),
                        order.color === 'red' ? 'animate-pulse-subtle' : ''
                    ]"
                >
                    <!-- Card Header -->
                    <div class="p-3 md:p-4 border-b border-gray-200 dark:border-gray-800">
                        <div class="flex items-start justify-between mb-1">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h3 class="text-base md:text-lg font-bold text-gray-900 dark:text-white truncate">
                                        {{ order.sale_number }}
                                    </h3>
                                    <!-- Status Badge -->
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                        :class="getStatusBadgeClass(order.status)"
                                    >
                                        {{ getStatusLabel(order.status) }}
                                    </span>
                                    <!-- Source Badge -->
                                    <span
                                        v-if="order.source === 'digital_menu'"
                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300"
                                        title="Menu Digital"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </span>
                                    <span
                                        v-else-if="order.source === 'waiter'"
                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300"
                                        title="Mesero"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                    {{ order.table_name }}
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0 ml-2">
                                <div class="text-xl md:text-2xl font-bold tabular-nums" :class="getTextColorClass(order.color)">
                                    {{ order.elapsed_minutes }}<span class="text-sm">m</span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-500">
                                    {{ order.created_at }}
                                </div>
                            </div>
                        </div>

                        <!-- Customer Name -->
                        <div v-if="order.customer_name" class="flex items-center text-sm text-gray-600 dark:text-gray-400 mt-1">
                            <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="truncate">{{ order.customer_name }}</span>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="p-3 md:p-4 bg-gray-50 dark:bg-gray-950 space-y-2">
                        <div
                            v-for="(item, index) in order.items"
                            :key="index"
                            class="flex items-start space-x-2"
                        >
                            <span class="inline-flex items-center justify-center min-w-[2rem] h-8 md:min-w-[2.25rem] md:h-9 px-2 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 font-bold text-sm md:text-base flex-shrink-0">
                                {{ item.quantity }}x
                            </span>
                            <span class="text-sm md:text-base text-gray-900 dark:text-white font-medium leading-tight pt-1">
                                {{ item.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="order.notes" class="px-3 md:px-4 py-2 md:py-3 bg-yellow-50 dark:bg-yellow-900/20 border-t border-yellow-200 dark:border-yellow-900/30">
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-xs md:text-sm text-yellow-800 dark:text-yellow-300 font-medium">
                                {{ order.notes }}
                            </p>
                        </div>
                    </div>

                    <!-- Status Actions -->
                    <div class="p-2 md:p-3 border-t border-gray-200 dark:border-gray-800">
                        <div class="grid grid-cols-3 gap-1.5 md:gap-2">
                            <button
                                @click="updateStatus(order.id, 'preparando')"
                                :disabled="updatingOrderId === order.id || order.status === 'preparando' || order.status === 'lista'"
                                class="px-2 py-2.5 md:py-3 text-xs md:text-sm font-semibold rounded-lg transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center active:scale-95"
                                :class="order.status === 'nueva'
                                    ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-md'
                                    : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-500'"
                            >
                                <svg v-if="updatingOrderId === order.id" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span v-else>Iniciar</span>
                            </button>

                            <button
                                @click="updateStatus(order.id, 'lista')"
                                :disabled="updatingOrderId === order.id || order.status === 'nueva' || order.status === 'lista'"
                                class="px-2 py-2.5 md:py-3 text-xs md:text-sm font-semibold rounded-lg transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center active:scale-95"
                                :class="order.status === 'preparando'
                                    ? 'bg-green-600 hover:bg-green-700 text-white shadow-md'
                                    : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-500'"
                            >
                                <svg v-if="updatingOrderId === order.id" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span v-else>Listo</span>
                            </button>

                            <button
                                @click="updateStatus(order.id, 'entregada')"
                                :disabled="updatingOrderId === order.id || order.status !== 'lista'"
                                class="px-2 py-2.5 md:py-3 text-xs md:text-sm font-semibold rounded-lg transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center active:scale-95"
                                :class="order.status === 'lista'
                                    ? 'bg-purple-600 hover:bg-purple-700 text-white shadow-md'
                                    : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-500'"
                            >
                                <svg v-if="updatingOrderId === order.id" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span v-else>Entregar</span>
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

// State
const orders = ref([]);
const currentTime = ref('');
const isDarkMode = ref(false);
const alertSound = ref(null);
const updatingOrderId = ref(null);
const soundEnabled = ref(true);
const isFullscreen = ref(false);
const activeFilter = ref('all');

let pollingInterval = null;
let clockInterval = null;
let previousOrderIds = [];
let isFirstLoad = true;

// Filters configuration
const filters = computed(() => [
    { value: 'all', label: 'Todas', count: orders.value.length, color: 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' },
    { value: 'nueva', label: 'Nuevas', count: orders.value.filter(o => o.status === 'nueva').length, color: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' },
    { value: 'preparando', label: 'Preparando', count: orders.value.filter(o => o.status === 'preparando').length, color: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300' },
    { value: 'lista', label: 'Listas', count: orders.value.filter(o => o.status === 'lista').length, color: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' },
]);

// Filtered orders
const filteredOrders = computed(() => {
    if (activeFilter.value === 'all') return orders.value;
    return orders.value.filter(o => o.status === activeFilter.value);
});

// Sound Toggle
const toggleSound = () => {
    soundEnabled.value = !soundEnabled.value;
    localStorage.setItem('kitchenSoundEnabled', soundEnabled.value.toString());
};

// Fullscreen Toggle
const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        isFullscreen.value = true;
    } else {
        document.exitFullscreen();
        isFullscreen.value = false;
    }
};

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

// Initialize settings from localStorage
const initSettings = () => {
    const savedMode = localStorage.getItem('darkMode');
    if (savedMode === 'true' || (!savedMode && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    }

    const savedSound = localStorage.getItem('kitchenSoundEnabled');
    if (savedSound !== null) {
        soundEnabled.value = savedSound === 'true';
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

        // Play sound if enabled and there are new orders
        if (hasNewOrders && !isFirstLoad && soundEnabled.value) {
            playAlert();
        }

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
    updatingOrderId.value = orderId;

    try {
        await axios.patch(route('kitchen.api.update-status', orderId), {
            status: newStatus
        });
        await fetchOrders();
    } catch (error) {
        console.error('Error updating status:', error);
        alert('Error al actualizar el estado de la orden');
    } finally {
        updatingOrderId.value = null;
    }
};

// Play Alert Sound
const playAlert = () => {
    if (alertSound.value && soundEnabled.value) {
        alertSound.value.play().catch(err => {
            console.error('Error playing sound:', err);
        });
    }
};

// Status helpers
const getStatusLabel = (status) => {
    const labels = {
        'nueva': 'Nueva',
        'preparando': 'Preparando',
        'lista': 'Lista'
    };
    return labels[status] || status;
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'nueva': 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300',
        'preparando': 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300',
        'lista': 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300'
    };
    return classes[status] || 'bg-gray-100 text-gray-600';
};

const getFilterLabel = (filter) => {
    const f = filters.value.find(f => f.value === filter);
    return f ? f.label : filter;
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

// Fullscreen change listener
const handleFullscreenChange = () => {
    isFullscreen.value = !!document.fullscreenElement;
};

// Lifecycle
onMounted(() => {
    initSettings();
    updateClock();
    fetchOrders();

    pollingInterval = setInterval(fetchOrders, 5000);
    clockInterval = setInterval(updateClock, 1000);

    document.addEventListener('fullscreenchange', handleFullscreenChange);
});

onUnmounted(() => {
    if (pollingInterval) clearInterval(pollingInterval);
    if (clockInterval) clearInterval(clockInterval);
    document.removeEventListener('fullscreenchange', handleFullscreenChange);
});
</script>

<style scoped>
@keyframes pulse-subtle {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.85; }
}
.animate-pulse-subtle {
    animation: pulse-subtle 2s ease-in-out infinite;
}
</style>
