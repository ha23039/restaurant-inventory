<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import OrderDetailSlideOver from '@/Components/OrderDetailSlideOver.vue';

const props = defineProps({
    orders: Object,
    counts: Object,
    filters: Object,
});

const toast = useToast();

// Tabs
const activeTab = ref(props.filters?.type || 'all');
const tabs = computed(() => [
    { key: 'all', label: 'Todos', count: props.counts?.all || 0, icon: 'clipboard-list' },
    { key: 'local', label: 'En Local', count: props.counts?.local || 0, icon: 'home' },
    { key: 'takeaway', label: 'Para Llevar', count: props.counts?.takeaway || 0, icon: 'shopping-bag' },
    { key: 'delivery', label: 'Delivery', count: props.counts?.delivery || 0, icon: 'truck' },
]);

// SlideOver
const showSlideOver = ref(false);
const selectedOrder = ref(null);
const loadingOrder = ref(false);

// Auto-refresh cada 30 segundos
let refreshInterval = null;

onMounted(() => {
    refreshInterval = setInterval(() => {
        router.reload({ only: ['orders', 'counts'], preserveScroll: true });
    }, 30000);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});

// Cambiar tab (con transición rápida)
const changeTab = (key) => {
    if (activeTab.value === key) return;
    activeTab.value = key;
    router.get(route('orders.index'), {
        type: key,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['orders', 'counts'],
    });
};

// Abrir detalle de orden
const openOrderDetail = async (order) => {
    selectedOrder.value = order;
    showSlideOver.value = true;
    loadingOrder.value = true;

    try {
        const response = await fetch(route('orders.show', order.id));
        const data = await response.json();
        selectedOrder.value = {
            ...order,
            ...data.sale,
            active_subtotal: data.active_subtotal,
            cancelled_total: data.cancelled_total,
            can_edit: data.can_edit,
        };
    } catch (error) {
        console.error('Error loading order:', error);
        toast.error('Error al cargar el pedido');
    } finally {
        loadingOrder.value = false;
    }
};

// Evento cuando se cancela un item
const handleItemCancelled = () => {
    toast.success('Item cancelado correctamente');
    router.reload({ only: ['orders', 'counts'] });
    if (selectedOrder.value) {
        openOrderDetail(selectedOrder.value);
    }
};

// Evento cuando se actualiza el cliente
const handleCustomerUpdated = () => {
    toast.success('Cliente actualizado correctamente');
    router.reload({ only: ['orders'] });
    if (selectedOrder.value) {
        openOrderDetail(selectedOrder.value);
    }
};

// Helper para status de cocina con iconos
const getKitchenStatus = (status) => {
    const statuses = {
        nueva: { 
            bg: 'bg-blue-100 dark:bg-blue-900/30', 
            text: 'text-blue-700 dark:text-blue-300',
            label: 'Nueva',
            dot: 'bg-blue-500'
        },
        en_preparacion: { 
            bg: 'bg-amber-100 dark:bg-amber-900/30', 
            text: 'text-amber-700 dark:text-amber-300',
            label: 'En preparación',
            dot: 'bg-amber-500 animate-pulse'
        },
        lista: { 
            bg: 'bg-green-100 dark:bg-green-900/30', 
            text: 'text-green-700 dark:text-green-300',
            label: 'Lista',
            dot: 'bg-green-500'
        },
        entregada: { 
            bg: 'bg-gray-100 dark:bg-gray-700', 
            text: 'text-gray-700 dark:text-gray-300',
            label: 'Entregada',
            dot: 'bg-gray-400'
        },
    };
    return statuses[status] || statuses.nueva;
};

// Helper para tipo de orden con iconos
const getOrderTypeIcon = (order) => {
    if (order.delivery_method === 'delivery') return 'truck';
    if (order.delivery_method === 'pickup') return 'shopping-bag';
    if (order.table_id || order.delivery_method === 'dine_in') return 'home';
    return 'clipboard-list';
};

const getDeliveryLabel = (method, table) => {
    if (table) return `Mesa ${table.table_number}`;
    const labels = { pickup: 'Para Llevar', dine_in: 'En Local', delivery: 'Delivery' };
    return labels[method] || 'Pedido';
};

// Formatear hora relativa
const formatTimeAgo = (date) => {
    const now = new Date();
    const orderDate = new Date(date);
    const diffMs = now - orderDate;
    const diffMins = Math.floor(diffMs / 60000);
    
    if (diffMins < 1) return 'Ahora';
    if (diffMins < 60) return `${diffMins} min`;
    
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours}h`;
    
    return orderDate.toLocaleDateString('es-MX', { day: 'numeric', month: 'short' });
};

// Formatear precio
const formatPrice = (price) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(price);
};

// Iconos SVG
const icons = {
    'clipboard-list': 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
    'home': 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    'shopping-bag': 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
    'truck': 'M8 17a2 2 0 002-2H6a2 2 0 002 2zm10 0a2 2 0 002-2h-4a2 2 0 002 2zm2-4V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0H4m16 0l2 4H2l2-4',
    'phone': 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
    'user': 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    'plus': 'M12 4v16m8-8H4',
    'eye': 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
};
</script>

<template>
    <Head title="Pedidos" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 sm:mb-8">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 sm:p-3 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-lg">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons['clipboard-list']" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                                Pedidos
                            </h1>
                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                                {{ orders.total }} pedidos activos
                            </p>
                        </div>
                    </div>
                    <a
                        :href="route('sales.pos')"
                        class="inline-flex items-center justify-center gap-2 px-4 sm:px-5 py-2.5 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white font-semibold rounded-xl shadow-lg shadow-orange-500/25 transition-all duration-200 hover:shadow-xl hover:shadow-orange-500/30"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons.plus" />
                        </svg>
                        <span class="hidden xs:inline">Nuevo Pedido</span>
                        <span class="xs:hidden">Nuevo</span>
                    </a>
                </div>

                <!-- Tabs con Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="changeTab(tab.key)"
                        :class="[
                            'relative p-3 sm:p-4 rounded-xl transition-all duration-200 text-left group',
                            activeTab === tab.key
                                ? 'bg-gradient-to-br from-orange-500 to-red-600 text-white shadow-lg shadow-orange-500/25'
                                : 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 border border-gray-200 dark:border-gray-700'
                        ]"
                    >
                        <div class="flex items-center justify-between mb-1 sm:mb-2">
                            <svg 
                                :class="[
                                    'w-5 h-5 sm:w-6 sm:h-6',
                                    activeTab === tab.key ? 'text-white/80' : 'text-gray-400 dark:text-gray-500'
                                ]" 
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons[tab.icon]" />
                            </svg>
                            <span 
                                :class="[
                                    'text-xl sm:text-2xl font-bold',
                                    activeTab === tab.key ? 'text-white' : 'text-gray-900 dark:text-white'
                                ]"
                            >
                                {{ tab.count }}
                            </span>
                        </div>
                        <p 
                            :class="[
                                'text-xs sm:text-sm font-medium truncate',
                                activeTab === tab.key ? 'text-white/90' : 'text-gray-600 dark:text-gray-400'
                            ]"
                        >
                            {{ tab.label }}
                        </p>
                        <!-- Indicator bar -->
                        <div 
                            v-if="activeTab === tab.key"
                            class="absolute bottom-0 left-3 right-3 sm:left-4 sm:right-4 h-1 bg-white/30 rounded-full"
                        ></div>
                    </button>
                </div>

                <!-- Orders Grid -->
                <div v-if="orders.data.length === 0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons['clipboard-list']" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No hay pedidos</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                        No hay pedidos activos en esta categoría. Los nuevos pedidos aparecerán aquí automáticamente.
                    </p>
                </div>

                <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="order in orders.data"
                        :key="order.id"
                        @click="openOrderDetail(order)"
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-lg border border-gray-100 dark:border-gray-700 hover:border-orange-200 dark:hover:border-orange-800 transition-all duration-200 cursor-pointer overflow-hidden group"
                    >
                        <!-- Card Header -->
                        <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div :class="[
                                        'w-10 h-10 rounded-xl flex items-center justify-center',
                                        order.source === 'digital_menu' 
                                            ? 'bg-purple-100 dark:bg-purple-900/30' 
                                            : 'bg-orange-100 dark:bg-orange-900/30'
                                    ]">
                                        <svg :class="[
                                            'w-5 h-5',
                                            order.source === 'digital_menu' 
                                                ? 'text-purple-600 dark:text-purple-400' 
                                                : 'text-orange-600 dark:text-orange-400'
                                        ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons[getOrderTypeIcon(order)]" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                                            #{{ order.sale_number }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ getDeliveryLabel(order.delivery_method, order.table) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ formatPrice(order.total) }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ formatTimeAgo(order.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-4">
                            <!-- Cliente -->
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons.user" />
                                </svg>
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ order.customer_name || 'Cliente' }}
                                </span>
                                <span 
                                    v-if="order.source === 'digital_menu'"
                                    class="ml-auto text-xs px-2 py-0.5 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full"
                                >
                                    Menú Digital
                                </span>
                            </div>

                            <!-- Status Badge -->
                            <div class="flex items-center justify-between">
                                <div :class="[
                                    'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium',
                                    getKitchenStatus(order.kitchen_order_state?.status).bg,
                                    getKitchenStatus(order.kitchen_order_state?.status).text
                                ]">
                                    <span :class="[
                                        'w-2 h-2 rounded-full',
                                        getKitchenStatus(order.kitchen_order_state?.status).dot
                                    ]"></span>
                                    {{ getKitchenStatus(order.kitchen_order_state?.status).label }}
                                </div>
                                <!-- Visible en móvil, hover en desktop -->
                                <button class="p-2 text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors sm:opacity-0 sm:group-hover:opacity-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons.eye" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="orders.links && orders.links.length > 3" class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 order-2 sm:order-1">
                        Mostrando {{ orders.from }}-{{ orders.to }} de {{ orders.total }}
                    </p>
                    <div class="flex gap-1.5 sm:gap-2 order-1 sm:order-2 flex-wrap justify-center">
                        <template v-for="link in orders.links" :key="link.label">
                            <button
                                v-if="link.url"
                                @click="router.get(link.url, {}, { preserveState: true })"
                                :class="[
                                    link.active
                                        ? 'bg-orange-600 text-white'
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700',
                                    'px-2.5 sm:px-3 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-colors'
                                ]"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- SlideOver -->
        <OrderDetailSlideOver
            :show="showSlideOver"
            :order="selectedOrder"
            :loading="loadingOrder"
            @close="showSlideOver = false"
            @item-cancelled="handleItemCancelled"
            @customer-updated="handleCustomerUpdated"
        />
    </AdminLayout>
</template>
