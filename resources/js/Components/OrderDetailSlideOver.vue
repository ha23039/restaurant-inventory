<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import SlideOver from './SlideOver.vue';

const props = defineProps({
    show: Boolean,
    order: Object,
    loading: Boolean,
});

const emit = defineEmits(['close', 'itemCancelled', 'customerUpdated']);

const toast = useToast();

// Estado local
const cancellingItem = ref(null);
const cancelReason = ref('');
const showCancelModal = ref(false);
const itemToCancel = ref(null);

// Customer search state
const showCustomerSearch = ref(false);
const customerSearch = ref('');
const searchResults = ref([]);
const searchLoading = ref(false);
const assigningCustomer = ref(false);
let searchTimeout = null;

// Items activos y cancelados
const activeItems = computed(() => {
    return props.order?.sale_items?.filter(item => !item.is_cancelled) || [];
});

const cancelledItems = computed(() => {
    return props.order?.sale_items?.filter(item => item.is_cancelled) || [];
});

// Título dinámico
const slideTitle = computed(() => {
    return props.order ? `Pedido #${props.order.sale_number}` : 'Cargando...';
});

const slideSubtitle = computed(() => {
    return props.order ? formatDateTime(props.order.created_at) : '';
});

// Watch for search input with debounce
watch(customerSearch, (newVal) => {
    if (searchTimeout) clearTimeout(searchTimeout);

    if (newVal.length < 2) {
        searchResults.value = [];
        return;
    }

    searchTimeout = setTimeout(() => {
        searchCustomers(newVal);
    }, 300);
});

// Search customers API
const searchCustomers = async (query) => {
    searchLoading.value = true;
    try {
        const response = await axios.get(route('orders.customers.search'), {
            params: { q: query }
        });
        searchResults.value = response.data;
    } catch (error) {
        console.error('Error searching customers:', error);
        toast.error('Error al buscar clientes');
        searchResults.value = [];
    } finally {
        searchLoading.value = false;
    }
};

// Assign customer to order
const assignCustomer = async (customer) => {
    if (!props.order) return;

    assigningCustomer.value = true;
    try {
        await axios.post(route('orders.assign-customer', { sale: props.order.id }), {
            customer_id: customer.id,
        });
        showCustomerSearch.value = false;
        customerSearch.value = '';
        searchResults.value = [];
        emit('customerUpdated');
    } catch (error) {
        console.error('Error assigning customer:', error);
        toast.error(error.response?.data?.error || 'Error al asignar cliente');
    } finally {
        assigningCustomer.value = false;
    }
};

// Abrir modal de cancelación
const openCancelModal = (item) => {
    itemToCancel.value = item;
    cancelReason.value = '';
    showCancelModal.value = true;
};

// Cancelar item
const cancelItem = async () => {
    if (!itemToCancel.value || !props.order) return;

    cancellingItem.value = itemToCancel.value.id;

    try {
        await axios.post(route('orders.cancel-item', {
            sale: props.order.id,
            item: itemToCancel.value.id,
        }), {
            reason: cancelReason.value,
        });

        showCancelModal.value = false;
        itemToCancel.value = null;
        emit('itemCancelled');
    } catch (error) {
        console.error('Error cancelling item:', error);
        toast.error(error.response?.data?.error || 'Error al cancelar el item');
    } finally {
        cancellingItem.value = null;
    }
};

// Helpers
const getProductName = (item) => {
    if (!item) return '';
    if (item.product_type === 'free') return item.free_sale_name;
    if (item.product_type === 'variant') return item.menu_item_variant?.variant_name || item.menu_item?.name;
    if (item.product_type === 'simple_variant') return item.simple_product_variant?.variant_name || item.simple_product?.name;
    if (item.product_type === 'menu') return item.menu_item?.name;
    if (item.product_type === 'simple') return item.simple_product?.name;
    if (item.product_type === 'combo') return item.combo?.name;
    return 'Producto';
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(price);
};

const getKitchenStatusLabel = (status) => {
    const labels = {
        nueva: 'Nueva',
        en_preparacion: 'En preparación',
        lista: 'Lista',
        entregada: 'Entregada',
    };
    return labels[status] || status || '-';
};

const getKitchenStatusColor = (status) => {
    const colors = {
        nueva: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        en_preparacion: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        lista: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        entregada: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    };
    return colors[status] || colors.nueva;
};

const getDeliveryLabel = (method) => {
    const labels = {
        pickup: 'Para Llevar',
        dine_in: 'En Local',
        delivery: 'Delivery',
    };
    return labels[method] || method || '-';
};

const formatDateTime = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleString('es-MX', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <SlideOver
        :show="show"
        @close="$emit('close')"
        :title="slideTitle"
        :subtitle="slideSubtitle"
        size="md"
    >
        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-600"></div>
        </div>

        <!-- Content -->
        <div v-else-if="order" class="space-y-6">
            <!-- Customer Section -->
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Cliente</span>
                    <button
                        v-if="order.can_edit"
                        @click="showCustomerSearch = !showCustomerSearch"
                        class="text-xs font-medium text-orange-600 dark:text-orange-400 hover:underline"
                    >
                        {{ showCustomerSearch ? 'Cerrar' : 'Buscar/Cambiar' }}
                    </button>
                </div>

                <!-- Current Customer -->
                <div v-if="!showCustomerSearch" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center text-white font-semibold">
                        {{ (order.customer_name || 'C')[0].toUpperCase() }}
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ order.customer_name || 'Sin nombre' }}
                        </p>
                        <p v-if="order.customer_phone" class="text-sm text-gray-500 dark:text-gray-400">
                            {{ order.customer_phone }}
                        </p>
                    </div>
                </div>

                <!-- Customer Search -->
                <div v-else class="space-y-3">
                    <div class="relative">
                        <input
                            v-model="customerSearch"
                            type="text"
                            placeholder="Buscar por nombre o teléfono..."
                            class="w-full px-4 py-2.5 pl-10 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        />
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Loading -->
                    <div v-if="searchLoading" class="flex items-center justify-center py-4">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-orange-600"></div>
                    </div>

                    <!-- Results -->
                    <div v-else-if="searchResults.length > 0" class="max-h-48 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-600 divide-y divide-gray-100 dark:divide-gray-700">
                        <button
                            v-for="customer in searchResults"
                            :key="customer.id"
                            @click="assignCustomer(customer)"
                            :disabled="assigningCustomer"
                            class="w-full px-4 py-3 text-left hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors disabled:opacity-50 flex items-center gap-3"
                        >
                            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-sm font-medium text-gray-600 dark:text-gray-300">
                                {{ (customer.name || 'C')[0].toUpperCase() }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 dark:text-white truncate">{{ customer.name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ customer.phone }}</p>
                            </div>
                            <span v-if="customer.orders_count > 0" class="text-xs font-medium text-orange-600 dark:text-orange-400 bg-orange-100 dark:bg-orange-900/30 px-2 py-0.5 rounded-full">
                                {{ customer.orders_count }} pedidos
                            </span>
                        </button>
                    </div>

                    <!-- No results -->
                    <div v-else-if="customerSearch.length >= 2 && !searchLoading" class="text-center py-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No se encontraron clientes</p>
                    </div>
                </div>
            </div>

            <!-- Order Info -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Estado Cocina</p>
                    <span :class="[
                        'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
                        getKitchenStatusColor(order.kitchen_order_state?.status)
                    ]">
                        {{ getKitchenStatusLabel(order.kitchen_order_state?.status) }}
                    </span>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Tipo</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ order.table ? `Mesa ${order.table.table_number}` : getDeliveryLabel(order.delivery_method) }}
                    </p>
                </div>
            </div>

            <!-- Items -->
            <div>
                <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-4">
                    Items del pedido ({{ activeItems.length }})
                </h3>

                <!-- Items Activos -->
                <div class="space-y-2">
                    <div
                        v-for="item in activeItems"
                        :key="item.id"
                        class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700"
                    >
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="flex-shrink-0 w-6 h-6 bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-full flex items-center justify-center text-xs font-bold">
                                    {{ item.quantity }}
                                </span>
                                <span class="font-medium text-gray-900 dark:text-white truncate">
                                    {{ getProductName(item) }}
                                </span>
                            </div>
                            <!-- Componentes de combo -->
                            <div v-if="item.combo_selections?.components_detail" class="mt-1.5 ml-8 space-y-0.5">
                                <p
                                    v-for="comp in item.combo_selections.components_detail"
                                    :key="comp.componentName"
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    └ {{ comp.componentName }}: {{ comp.name }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ formatPrice(item.total_price) }}
                            </span>
                            <!-- Botón cancelar -->
                            <button
                                v-if="order.can_edit"
                                @click="openCancelModal(item)"
                                :disabled="cancellingItem === item.id"
                                class="p-1.5 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors disabled:opacity-50"
                                title="Cancelar item"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Items Cancelados -->
                <div v-if="cancelledItems.length > 0" class="mt-6">
                    <h4 class="text-xs font-medium text-red-500 dark:text-red-400 uppercase tracking-wide mb-3">
                        Items cancelados ({{ cancelledItems.length }})
                    </h4>
                    <div class="space-y-2">
                        <div
                            v-for="item in cancelledItems"
                            :key="item.id"
                            class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/10 rounded-xl border border-red-100 dark:border-red-900/30 opacity-60"
                        >
                            <div class="flex-1">
                                <p class="text-sm text-gray-500 dark:text-gray-400 line-through">
                                    {{ item.quantity }}x {{ getProductName(item) }}
                                </p>
                                <p v-if="item.cancellation_reason" class="text-xs text-red-500 dark:text-red-400 mt-0.5">
                                    {{ item.cancellation_reason }}
                                </p>
                            </div>
                            <span class="text-sm text-gray-400 line-through">
                                {{ formatPrice(item.total_price) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer: Totales -->
        <template #footer v-if="order && !loading">
            <div class="space-y-3">
                <!-- Totals -->
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                        <span class="text-gray-900 dark:text-white">{{ formatPrice(order.active_subtotal || order.subtotal) }}</span>
                    </div>
                    <div v-if="order.cancelled_total > 0" class="flex justify-between text-sm">
                        <span class="text-red-500 dark:text-red-400">Cancelado</span>
                        <span class="text-red-500 dark:text-red-400 line-through">{{ formatPrice(order.cancelled_total) }}</span>
                    </div>
                    <div v-if="order.discount > 0" class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Descuento</span>
                        <span class="text-green-600 dark:text-green-400">-{{ formatPrice(order.discount) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200 dark:border-gray-700">
                        <span class="text-gray-900 dark:text-white">Total</span>
                        <span class="text-orange-600 dark:text-orange-400">{{ formatPrice(order.total) }}</span>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="flex gap-3">
                    <button class="flex-1 py-3 px-4 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors font-medium flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Imprimir
                    </button>
                    <button class="flex-1 py-3 px-4 bg-gradient-to-r from-orange-500 to-red-600 text-white rounded-xl hover:from-orange-600 hover:to-red-700 transition-all font-medium flex items-center justify-center gap-2 shadow-lg shadow-orange-500/25">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Cobrar
                    </button>
                </div>
            </div>
        </template>
    </SlideOver>

    <!-- Modal de Cancelación -->
    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="showCancelModal" class="fixed inset-0 bg-black/60 z-[80] flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-sm w-full p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                            ¿Cancelar item?
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ itemToCancel?.quantity }}x {{ getProductName(itemToCancel) }}
                        </p>
                    </div>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3">
                    El inventario será restaurado automáticamente.
                </p>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Razón (opcional)
                    </label>
                    <input
                        v-model="cancelReason"
                        type="text"
                        placeholder="Ej: Cliente cambió de opinión"
                        class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    />
                </div>

                <div class="flex gap-3">
                    <button
                        @click="showCancelModal = false"
                        class="flex-1 py-2.5 px-4 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors font-medium"
                    >
                        No, mantener
                    </button>
                    <button
                        @click="cancelItem"
                        :disabled="cancellingItem"
                        class="flex-1 py-2.5 px-4 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium disabled:opacity-50 flex items-center justify-center gap-2"
                    >
                        <svg v-if="cancellingItem" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ cancellingItem ? 'Cancelando...' : 'Sí, cancelar' }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
