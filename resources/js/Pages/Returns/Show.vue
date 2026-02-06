<template>
    <Head :title="`Devolución #${returnData?.return_number || 'N/A'}`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight flex items-center gap-2">
                    <component :is="icons.returns" class="w-6 h-6" />
                    Detalle de Devolución #{{ returnData?.return_number || 'N/A' }}
                    <span v-if="returnData?.return_type" class="ml-2 text-sm bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 px-2 py-1 rounded capitalize">
                        {{ returnData.return_type === 'total' ? 'Devolución Total' : 'Devolución Parcial' }}
                    </span>
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('returns.index')" class="inline-flex items-center gap-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        ← Volver
                    </Link>
                    <Link
                        v-if="returnData?.sale"
                        :href="route('sales.show', returnData.sale.id)"
                        class="inline-flex items-center gap-1 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    >
                        <component :is="icons.receipt" class="w-4 h-4" />
                        Ver Venta Original
                    </Link>
                    <Link :href="route('returns.create')" class="inline-flex items-center gap-1 bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
                        <component :is="icons.add" class="w-4 h-4" />
                        Nueva Devolución
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-3 md:py-6 lg:py-12">
            <div class="max-w-6xl mx-auto px-2 sm:px-6 lg:px-8">
                <!-- Información General de la Devolución -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                            <!-- Número de Devolución -->
                            <div class="text-center p-4 bg-orange-50 dark:bg-orange-900/30 rounded-lg">
                                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                    #{{ returnData?.return_number || 'N/A' }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Número de Devolución</div>
                            </div>

                            <!-- Venta Original -->
                            <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    #{{ returnData?.sale?.sale_number || 'N/A' }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Venta Original</div>
                                <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                    ${{ formatPrice(returnData?.sale?.total || 0) }}
                                </div>
                            </div>

                            <!-- Total Devuelto -->
                            <div class="text-center p-4 bg-red-50 dark:bg-red-900/30 rounded-lg">
                                <div class="text-3xl font-bold text-red-600 dark:text-red-400">
                                    ${{ formatPrice(returnData?.total_returned) }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Total Devuelto</div>
                            </div>

                            <!-- Estado -->
                            <div class="text-center p-4 bg-green-50 dark:bg-green-900/30 rounded-lg">
                                <div class="text-2xl font-bold">
                                    <span
                                        :class="{
                                            'text-green-600 dark:text-green-400': returnData?.status === 'completed',
                                            'text-yellow-600 dark:text-yellow-400': returnData?.status === 'pending',
                                            'text-red-600 dark:text-red-400': returnData?.status === 'cancelled'
                                        }"
                                    >
                                        {{ getStatusIcon(returnData?.status) }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 capitalize">{{ getStatusText(returnData?.status) }}</div>
                            </div>

                            <!-- Fecha -->
                            <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
                                <div class="text-lg font-bold text-purple-600 dark:text-purple-400">
                                    {{ formatDate(returnData?.return_date) }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Fecha de Devolución</div>
                                <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                    {{ formatTime(returnData?.created_at) }}
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-1">
                                    <component :is="icons.profile" class="w-4 h-4" />
                                    Procesado por
                                </h4>
                                <p class="text-gray-600 dark:text-gray-300">{{ returnData?.processed_by_user?.name || 'Sistema' }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatTime(returnData?.processed_at) }}</p>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-1">
                                    <component :is="icons.cash" class="w-4 h-4" />
                                    Método de Reembolso
                                </h4>
                                <div class="flex items-center space-x-2">
                                    <span class="text-2xl">{{ getRefundIcon(returnData?.refund_method) }}</span>
                                    <span class="text-gray-600 dark:text-gray-300 capitalize">{{ returnData?.refund_method || 'N/A' }}</span>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-1">
                                    <component :is="icons.receipt" class="w-4 h-4" />
                                    Razón
                                </h4>
                                <p class="text-gray-600 dark:text-gray-300">{{ getReasonText(returnData?.reason) }}</p>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ returnData?.return_type === 'total' ? 'Devolución completa' : 'Devolución parcial' }}
                                </div>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-1">
                                    <component :is="icons.chart" class="w-4 h-4" />
                                    Estado del Proceso
                                </h4>
                                <div class="text-sm space-y-1 text-gray-600 dark:text-gray-300">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-green-500 dark:text-green-400">{{ returnData?.inventory_restored ? '✅' : '⏳' }}</span>
                                        <span>Inventario {{ returnData?.inventory_restored ? 'restaurado' : 'pendiente' }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-green-500 dark:text-green-400">{{ returnData?.cash_flow_adjusted ? '✅' : '⏳' }}</span>
                                        <span>Flujo de caja {{ returnData?.cash_flow_adjusted ? 'ajustado' : 'pendiente' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div v-if="returnData?.notes" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-1">
                                <component :is="icons.document" class="w-4 h-4" />
                                Notas
                            </h4>
                            <p class="text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">{{ returnData.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Items Devueltos -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <component :is="icons.pos" class="w-5 h-5" />
                                Productos Devueltos
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ returnData?.return_items?.length || 0 }} productos devueltos
                            </span>
                        </h3>

                        <!-- Verificar si hay items -->
                        <div v-if="!returnData?.return_items || returnData.return_items.length === 0">
                            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                                <component :is="icons.package" class="w-16 h-16 mx-auto mb-4 text-gray-400 dark:text-gray-500" />
                                <div class="text-lg font-medium">No se encontraron productos devueltos</div>
                                <div class="text-sm">Esta devolución no tiene productos asociados</div>
                            </div>
                        </div>

                        <!-- Lista de Items Devueltos -->
                        <div v-else class="space-y-4">
                            <div
                                v-for="(item, index) in returnData.return_items"
                                :key="item.id || index"
                                class="border border-orange-200 dark:border-orange-800 rounded-lg p-4 bg-orange-50 dark:bg-orange-900/20"
                            >
                                <div class="flex items-center justify-between">
                                    <!-- Información del Producto -->
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <!-- Tipo de Producto Icon -->
                                            <div class="flex-shrink-0">
                                                <span
                                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full"
                                                    :class="{
                                                        'bg-orange-100 dark:bg-orange-900/40 text-orange-600 dark:text-orange-400': item.sale_item?.product_type === 'menu',
                                                        'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400': item.sale_item?.product_type === 'simple'
                                                    }"
                                                >
                                                    <component
                                                        :is="item.sale_item?.product_type === 'menu' ? icons.menu : icons.product"
                                                        class="w-5 h-5"
                                                    />
                                                </span>
                                            </div>

                                            <!-- Detalles del Producto -->
                                            <div class="flex-1">
                                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                    {{ getItemName(item) }}
                                                </h4>

                                                <p class="text-sm text-gray-600 dark:text-gray-400" v-if="getItemDescription(item)">
                                                    {{ getItemDescription(item) }}
                                                </p>

                                                <div class="flex items-center space-x-2 mt-1">
                                                    <!-- Badge de Tipo -->
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                        :class="{
                                                            'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200': item.sale_item?.product_type === 'menu',
                                                            'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200': item.sale_item?.product_type === 'simple'
                                                        }"
                                                    >
                                                        {{ item.sale_item?.product_type === 'menu' ? 'Platillo del Menú' : 'Producto Individual' }}
                                                    </span>

                                                    <!-- Badge de estado de inventario -->
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                        :class="item.inventory_restored ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200'"
                                                    >
                                                        {{ item.inventory_restored ? '✅ Inventario procesado' : '⏳ Inventario pendiente' }}
                                                    </span>
                                                </div>

                                                <!-- Info de devolución -->
                                                <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                    <div class="flex items-center space-x-4">
                                                        <span>Cantidad original: {{ item.original_quantity }}</span>
                                                        <span class="text-red-600 dark:text-red-400 font-medium">Devuelto: {{ item.quantity_returned }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cantidad y Precios -->
                                    <div class="text-right ml-6">
                                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                                            -{{ item.quantity_returned }}
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            ${{ formatPrice(item.unit_price) }} c/u
                                        </div>
                                        <div class="text-lg font-bold text-red-600 dark:text-red-400 mt-1">
                                            -${{ formatPrice(item.total_price) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen Final -->
                            <div class="mt-8 pt-6 border-t-2 border-red-300 dark:border-red-700">
                                <div class="flex justify-between items-center text-xl font-bold text-gray-900 dark:text-white">
                                    <span>Total devuelto de {{ returnData.return_items.length }} productos:</span>
                                    <span class="text-red-600 dark:text-red-400">-${{ formatPrice(returnData?.total_returned) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Línea de Tiempo de la Devolución -->
                <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                            <component :is="icons.calendar" class="w-5 h-5" />
                            Línea de Tiempo
                        </h3>

                        <div class="space-y-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/40 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 dark:text-blue-400 text-sm">1</span>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Devolución solicitada</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ formatDateTime(returnData?.created_at) }}</div>
                                </div>
                            </div>

                            <div v-if="returnData?.processed_at" class="flex items-center space-x-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-green-100 dark:bg-green-900/40 rounded-full flex items-center justify-center">
                                    <span class="text-green-600 dark:text-green-400 text-sm">2</span>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Devolución procesada</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ formatDateTime(returnData.processed_at) }}</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">Por: {{ returnData?.processed_by_user?.name || 'Sistema' }}</div>
                                </div>
                            </div>

                            <div v-if="returnData?.inventory_restored" class="flex items-center space-x-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-purple-100 dark:bg-purple-900/40 rounded-full flex items-center justify-center">
                                    <span class="text-purple-600 dark:text-purple-400 text-sm">3</span>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Inventario ajustado</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Automáticamente</div>
                                </div>
                            </div>

                            <div v-if="returnData?.cash_flow_adjusted" class="flex items-center space-x-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-emerald-100 dark:bg-emerald-900/40 rounded-full flex items-center justify-center">
                                    <span class="text-emerald-600 dark:text-emerald-400 text-sm">4</span>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Flujo de caja ajustado</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Automáticamente</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="mt-6 flex justify-center space-x-4">
                    <Link
                        :href="route('returns.index')"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition-colors"
                    >
                        ← Volver a Devoluciones
                    </Link>

                    <Link
                        v-if="returnData?.sale"
                        :href="route('sales.show', returnData.sale.id)"
                        class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors"
                    >
                        <component :is="icons.receipt" class="w-5 h-5" />
                        Ver Venta Original
                    </Link>

                    <Link
                        :href="route('returns.create')"
                        class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-700 text-white font-bold py-3 px-6 rounded-lg transition-colors"
                    >
                        <component :is="icons.add" class="w-5 h-5" />
                        Nueva Devolución
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useIcons } from '@/composables/useIcons';

// Icons
const { icons } = useIcons();

// Props
const props = defineProps({
    return: {
        type: Object,
        required: true
    }
});

// Alias para evitar confusión con la palabra reservada 'return'
const returnData = props.return;

// Funciones auxiliares
const formatPrice = (price) => {
    return parseFloat(price || 0).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return 'Fecha no disponible';
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatTime = (date) => {
    if (!date) return 'Hora no disponible';
    return new Date(date).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatDateTime = (date) => {
    if (!date) return 'Fecha no disponible';
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getItemName = (item) => {
    const saleItem = item.sale_item;
    if (!saleItem) return 'Producto no identificado';
    
    if (saleItem.product_type === 'variant' && saleItem.menu_item_variant) {
        const parentName = saleItem.menu_item_variant.menu_item?.name || '';
        const variantName = saleItem.menu_item_variant.variant_name || '';
        return parentName && variantName ? `${parentName} - ${variantName}` : (variantName || parentName);
    }
    if (saleItem.product_type === 'simple_variant' && saleItem.simple_product_variant) {
        const parentName = saleItem.simple_product_variant.simple_product?.name || '';
        const variantName = saleItem.simple_product_variant.variant_name || '';
        return parentName && variantName ? `${parentName} - ${variantName}` : (variantName || parentName);
    }
    if (saleItem.product_type === 'menu' && saleItem.menu_item) {
        return saleItem.menu_item.name;
    }
    if (saleItem.product_type === 'simple' && saleItem.simple_product) {
        return saleItem.simple_product.name;
    }
    if (saleItem.product_type === 'combo' && saleItem.combo) {
        return saleItem.combo.name;
    }
    if (saleItem.product_type === 'free' && saleItem.free_sale_name) {
        return saleItem.free_sale_name;
    }
    return 'Producto no identificado';
};

const getItemDescription = (item) => {
    if (item.sale_item?.product_type === 'menu' && item.sale_item?.menu_item) {
        return item.sale_item.menu_item.description;
    } else if (item.sale_item?.product_type === 'simple' && item.sale_item?.simple_product) {
        return item.sale_item.simple_product.description;
    }
    return null;
};

const getStatusIcon = (status) => {
    const icons = {
        'completed': '✅',
        'pending': '⏳',
        'cancelled': '❌'
    };
    return icons[status] || '📋';
};

const getStatusText = (status) => {
    const statuses = {
        'completed': 'Completada',
        'pending': 'Pendiente',
        'cancelled': 'Cancelada'
    };
    return statuses[status] || status;
};

const getRefundIcon = (method) => {
    const icons = {
        'efectivo': '💵',
        'tarjeta': '💳',
        'transferencia': '📱',
        'credito': '🏪'
    };
    return icons[method] || '💰';
};

const getReasonText = (reason) => {
    const reasons = {
        'defective': 'Producto defectuoso',
        'wrong_order': 'Orden incorrecta',
        'customer_request': 'Solicitud del cliente',
        'error': 'Error del sistema',
        'other': 'Otra razón'
    };
    return reasons[reason] || reason;
};

// Debug
console.log('🔍 Return Show - Data:', returnData);
</script>
