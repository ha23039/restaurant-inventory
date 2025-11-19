<template>
    <Head :title="`Devolución #${returnData?.return_number || 'N/A'}`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🔄 Detalle de Devolución #{{ returnData?.return_number || 'N/A' }}
                    <span v-if="returnData?.return_type" class="ml-2 text-sm bg-orange-100 text-orange-800 px-2 py-1 rounded capitalize">
                        {{ returnData.return_type === 'total' ? 'Devolución Total' : 'Devolución Parcial' }}
                    </span>
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('returns.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        ← Volver
                    </Link>
                    <Link 
                        v-if="returnData?.sale"
                        :href="route('sales.show', returnData.sale.id)" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    >
                        🧾 Ver Venta Original
                    </Link>
                    <Link :href="route('returns.create')" class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
                        ➕ Nueva Devolución
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <!-- Información General de la Devolución -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                            <!-- Número de Devolución -->
                            <div class="text-center p-4 bg-orange-50 rounded-lg">
                                <div class="text-2xl font-bold text-orange-600">
                                    #{{ returnData?.return_number || 'N/A' }}
                                </div>
                                <div class="text-sm text-gray-600">Número de Devolución</div>
                            </div>

                            <!-- Venta Original -->
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">
                                    #{{ returnData?.sale?.sale_number || 'N/A' }}
                                </div>
                                <div class="text-sm text-gray-600">Venta Original</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    ${{ formatPrice(returnData?.sale?.total || 0) }}
                                </div>
                            </div>

                            <!-- Total Devuelto -->
                            <div class="text-center p-4 bg-red-50 rounded-lg">
                                <div class="text-3xl font-bold text-red-600">
                                    ${{ formatPrice(returnData?.total_returned) }}
                                </div>
                                <div class="text-sm text-gray-600">Total Devuelto</div>
                            </div>

                            <!-- Estado -->
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <div class="text-2xl font-bold">
                                    <span 
                                        :class="{
                                            'text-green-600': returnData?.status === 'completed',
                                            'text-yellow-600': returnData?.status === 'pending',
                                            'text-red-600': returnData?.status === 'cancelled'
                                        }"
                                    >
                                        {{ getStatusIcon(returnData?.status) }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600 capitalize">{{ getStatusText(returnData?.status) }}</div>
                            </div>

                            <!-- Fecha -->
                            <div class="text-center p-4 bg-purple-50 rounded-lg">
                                <div class="text-lg font-bold text-purple-600">
                                    {{ formatDate(returnData?.return_date) }}
                                </div>
                                <div class="text-sm text-gray-600">Fecha de Devolución</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ formatTime(returnData?.created_at) }}
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6 pt-6 border-t border-gray-200">
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">👤 Procesado por</h4>
                                <p class="text-gray-600">{{ returnData?.processed_by_user?.name || 'Sistema' }}</p>
                                <p class="text-sm text-gray-500">{{ formatTime(returnData?.processed_at) }}</p>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">💰 Método de Reembolso</h4>
                                <div class="flex items-center space-x-2">
                                    <span class="text-2xl">{{ getRefundIcon(returnData?.refund_method) }}</span>
                                    <span class="text-gray-600 capitalize">{{ returnData?.refund_method || 'N/A' }}</span>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">📋 Razón</h4>
                                <p class="text-gray-600">{{ getReasonText(returnData?.reason) }}</p>
                                <div class="text-sm text-gray-500 mt-1">
                                    {{ returnData?.return_type === 'total' ? 'Devolución completa' : 'Devolución parcial' }}
                                </div>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">⚡ Estado del Proceso</h4>
                                <div class="text-sm space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-green-500">{{ returnData?.inventory_restored ? '✅' : '⏳' }}</span>
                                        <span>Inventario {{ returnData?.inventory_restored ? 'restaurado' : 'pendiente' }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-green-500">{{ returnData?.cash_flow_adjusted ? '✅' : '⏳' }}</span>
                                        <span>Flujo de caja {{ returnData?.cash_flow_adjusted ? 'ajustado' : 'pendiente' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div v-if="returnData?.notes" class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="font-semibold text-gray-900 mb-2">📝 Notas</h4>
                            <p class="text-gray-600 bg-gray-50 p-4 rounded-lg">{{ returnData.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Items Devueltos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center justify-between">
                            <span>🛒 Productos Devueltos</span>
                            <span class="text-sm text-gray-500">
                                {{ returnData?.return_items?.length || 0 }} productos devueltos
                            </span>
                        </h3>
                        
                        <!-- Verificar si hay items -->
                        <div v-if="!returnData?.return_items || returnData.return_items.length === 0">
                            <div class="text-center py-12 text-gray-500">
                                <div class="text-4xl mb-4">📦</div>
                                <div class="text-lg font-medium">No se encontraron productos devueltos</div>
                                <div class="text-sm">Esta devolución no tiene productos asociados</div>
                            </div>
                        </div>

                        <!-- Lista de Items Devueltos -->
                        <div v-else class="space-y-4">
                            <div
                                v-for="(item, index) in returnData.return_items"
                                :key="item.id || index"
                                class="border border-orange-200 rounded-lg p-4 bg-orange-50"
                            >
                                <div class="flex items-center justify-between">
                                    <!-- Información del Producto -->
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <!-- Tipo de Producto Icon -->
                                            <div class="flex-shrink-0">
                                                <span 
                                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full text-lg"
                                                    :class="{
                                                        'bg-orange-100 text-orange-600': item.sale_item?.product_type === 'menu',
                                                        'bg-blue-100 text-blue-600': item.sale_item?.product_type === 'simple'
                                                    }"
                                                >
                                                    {{ item.sale_item?.product_type === 'menu' ? '🍽️' : '🥤' }}
                                                </span>
                                            </div>

                                            <!-- Detalles del Producto -->
                                            <div class="flex-1">
                                                <h4 class="text-lg font-semibold text-gray-900">
                                                    {{ getItemName(item) }}
                                                </h4>
                                                
                                                <p class="text-sm text-gray-600" v-if="getItemDescription(item)">
                                                    {{ getItemDescription(item) }}
                                                </p>

                                                <div class="flex items-center space-x-2 mt-1">
                                                    <!-- Badge de Tipo -->
                                                    <span 
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                        :class="{
                                                            'bg-orange-100 text-orange-800': item.sale_item?.product_type === 'menu',
                                                            'bg-blue-100 text-blue-800': item.sale_item?.product_type === 'simple'
                                                        }"
                                                    >
                                                        {{ item.sale_item?.product_type === 'menu' ? 'Platillo del Menú' : 'Producto Individual' }}
                                                    </span>

                                                    <!-- Badge de estado de inventario -->
                                                    <span 
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                        :class="item.inventory_restored ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                                    >
                                                        {{ item.inventory_restored ? '✅ Inventario procesado' : '⏳ Inventario pendiente' }}
                                                    </span>
                                                </div>

                                                <!-- Info de devolución -->
                                                <div class="mt-2 text-sm text-gray-600">
                                                    <div class="flex items-center space-x-4">
                                                        <span>Cantidad original: {{ item.original_quantity }}</span>
                                                        <span class="text-red-600 font-medium">Devuelto: {{ item.quantity_returned }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cantidad y Precios -->
                                    <div class="text-right ml-6">
                                        <div class="text-2xl font-bold text-red-600">
                                            -{{ item.quantity_returned }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            ${{ formatPrice(item.unit_price) }} c/u
                                        </div>
                                        <div class="text-lg font-bold text-red-600 mt-1">
                                            -${{ formatPrice(item.total_price) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen Final -->
                            <div class="mt-8 pt-6 border-t-2 border-red-300">
                                <div class="flex justify-between items-center text-xl font-bold">
                                    <span>Total devuelto de {{ returnData.return_items.length }} productos:</span>
                                    <span class="text-red-600">-${{ formatPrice(returnData?.total_returned) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Línea de Tiempo de la Devolución -->
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">📅 Línea de Tiempo</h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 text-sm">1</span>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Devolución solicitada</div>
                                    <div class="text-sm text-gray-500">{{ formatDateTime(returnData?.created_at) }}</div>
                                </div>
                            </div>

                            <div v-if="returnData?.processed_at" class="flex items-center space-x-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-green-600 text-sm">2</span>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Devolución procesada</div>
                                    <div class="text-sm text-gray-500">{{ formatDateTime(returnData.processed_at) }}</div>
                                    <div class="text-xs text-gray-400">Por: {{ returnData?.processed_by_user?.name || 'Sistema' }}</div>
                                </div>
                            </div>

                            <div v-if="returnData?.inventory_restored" class="flex items-center space-x-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <span class="text-purple-600 text-sm">3</span>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Inventario ajustado</div>
                                    <div class="text-sm text-gray-500">Automáticamente</div>
                                </div>
                            </div>

                            <div v-if="returnData?.cash_flow_adjusted" class="flex items-center space-x-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center">
                                    <span class="text-emerald-600 text-sm">4</span>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Flujo de caja ajustado</div>
                                    <div class="text-sm text-gray-500">Automáticamente</div>
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
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors"
                    >
                        🧾 Ver Venta Original
                    </Link>
                    
                    <Link 
                        :href="route('returns.create')" 
                        class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-3 px-6 rounded-lg transition-colors"
                    >
                        ➕ Nueva Devolución
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

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
    if (item.sale_item?.product_type === 'menu' && item.sale_item?.menu_item) {
        return item.sale_item.menu_item.name;
    } else if (item.sale_item?.product_type === 'simple' && item.sale_item?.simple_product) {
        return item.sale_item.simple_product.name;
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
