<template>
    <Head title="Pérdidas Operativas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    📉 Pérdidas Operativas - Productos Preparados
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('returns.index')" class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
                        🔄 Devoluciones
                    </Link>
                    <Link :href="route('inventory.products.index')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        📦 Inventario
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Métricas de Pérdidas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 text-lg">💸</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Pérdidas Hoy</div>
                                    <div class="text-2xl font-bold text-red-600">${{ formatPrice(metrics.today_losses || 0) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                        <span class="text-orange-600 text-lg">🍔</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Productos Perdidos</div>
                                    <div class="text-2xl font-bold text-orange-600">{{ metrics.today_units || 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                        <span class="text-purple-600 text-lg">📊</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Pérdidas Este Mes</div>
                                    <div class="text-2xl font-bold text-purple-600">${{ formatPrice(metrics.month_losses || 0) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-blue-600 text-lg">📈</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">% de Ventas</div>
                                    <div class="text-2xl font-bold text-blue-600">{{ metrics.loss_percentage || 0 }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                                <input
                                    v-model="form.date_from"
                                    @change="loadData"
                                    type="date"
                                    class="w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                                <input
                                    v-model="form.date_to"
                                    @change="loadData"
                                    type="date"
                                    class="w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm"
                                />
                            </div>

                            <div class="flex items-end">
                                <button
                                    @click="clearFilters"
                                    class="w-full bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Pérdidas Operativas -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">📋 Registro de Pérdidas Operativas</h3>
                        
                        <div v-if="losses.length > 0">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Fecha
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Producto Devuelto
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Cantidad
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Valor Perdido
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Devolución
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Usuario
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="loss in losses" :key="loss.id" class="hover:bg-red-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ formatDate(loss.movement_date) }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ formatTime(loss.created_at) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ extractProductName(loss.notes) }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    🍔 Producto preparado
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-bold text-red-600">
                                                    {{ formatNumber(loss.quantity) }} unidades
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-bold text-red-600">
                                                    -${{ formatPrice(loss.total_cost) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-blue-600">
                                                    {{ extractReturnNumber(loss.notes) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ loss.user?.name || 'Sistema' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Resumen -->
                            <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h4 class="font-medium text-red-900">Resumen del Período</h4>
                                        <p class="text-sm text-red-700">
                                            {{ form.date_from }} a {{ form.date_to }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-red-600">
                                            -${{ formatPrice(totalLosses) }}
                                        </div>
                                        <div class="text-sm text-red-600">
                                            {{ totalUnits }} productos perdidos
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="text-center py-12 text-gray-500">
                            <div class="text-4xl mb-4">✅</div>
                            <div class="text-lg font-medium">Sin pérdidas operativas</div>
                            <div class="text-sm mt-2">No hay productos preparados devueltos en este período</div>
                        </div>
                    </div>
                </div>

                <!-- Productos Más Devueltos -->
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">🏆 Productos Más Devueltos</h3>
                        
                        <div v-if="topProducts.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div 
                                v-for="(product, index) in topProducts.slice(0, 3)" 
                                :key="index"
                                class="bg-red-50 border border-red-200 rounded-lg p-4"
                            >
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="text-sm font-medium text-red-900">
                                            #{{ index + 1 }} {{ product.name }}
                                        </div>
                                        <div class="text-xs text-red-600">
                                            {{ product.total_quantity }} unidades devueltas
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-red-600">
                                            -${{ formatPrice(product.total_loss) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="text-center py-8 text-gray-500">
                            <div class="text-3xl mb-2">🎯</div>
                            <div>No hay suficientes datos para mostrar productos más devueltos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// State
const losses = ref([]);
const metrics = ref({});
const topProducts = ref([]);
const loading = ref(false);

// Form para filtros
const form = reactive({
    date_from: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0], // 30 días atrás
    date_to: new Date().toISOString().split('T')[0] // Hoy
});

// Computed
const totalLosses = computed(() => {
    return losses.value.reduce((sum, loss) => sum + parseFloat(loss.total_cost || 0), 0);
});

const totalUnits = computed(() => {
    return losses.value.reduce((sum, loss) => sum + parseFloat(loss.quantity || 0), 0);
});

// Methods
const loadData = async () => {
    loading.value = true;
    
    try {
        // Cargar pérdidas operativas
        const lossResponse = await fetch(`/api/operational-losses?start_date=${form.date_from}&end_date=${form.date_to}`);
        const lossData = await lossResponse.json();
        
        losses.value = lossData.losses || [];
        topProducts.value = lossData.losses_by_product || [];
        
        // Cargar métricas
        const metricsResponse = await fetch(`/api/returns/metrics?start_date=${form.date_from}&end_date=${form.date_to}`);
        const metricsData = await metricsResponse.json();
        
        metrics.value = metricsData;
        
    } catch (error) {
        console.error('Error cargando datos de pérdidas:', error);
    } finally {
        loading.value = false;
    }
};

const clearFilters = () => {
    form.date_from = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    form.date_to = new Date().toISOString().split('T')[0];
    loadData();
};

// Utility functions
const formatPrice = (price) => {
    return parseFloat(price || 0).toFixed(2);
};

const formatNumber = (number) => {
    return parseFloat(number || 0).toString();
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const extractProductName = (notes) => {
    // Extraer nombre del producto de las notas
    const match = notes.match(/PÉRDIDA OPERATIVA:\s*([^(]+)/);
    return match ? match[1].trim() : 'Producto no identificado';
};

const extractReturnNumber = (notes) => {
    // Extraer número de devolución
    const match = notes.match(/Return #(\w+)/);
    return match ? `#${match[1]}` : 'N/A';
};

// Load data on mount
onMounted(() => {
    loadData();
});
</script>