<template>
    <Head title="Nueva Devolución" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight flex items-center gap-2">
                    <component :is="icons.returns" class="w-6 h-6" />
                    Nueva Devolución
                </h2>
                <Link
                    :href="route('returns.index')"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                >
                    ← Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Búsqueda de Venta con Live Search -->
                <div v-if="!selectedSale" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                            <component :is="icons.search" class="w-5 h-5" />
                            Buscar Venta
                        </h3>

                        <div class="mb-6">
                            <div class="relative">
                                <input
                                    v-model="searchTerm"
                                    @input="onSearchInput"
                                    type="text"
                                    placeholder="Escribe para buscar: 1, 2, 202506110001..."
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm pr-10"
                                    :class="{ 'opacity-50': searching }"
                                />
                                <!-- Indicador de carga -->
                                <div v-if="searching" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <svg class="animate-spin h-5 w-5 text-blue-500 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
                                <component :is="icons.lightbulb" class="w-3 h-3" />
                                Búsqueda automática - Los resultados aparecen mientras escribes
                            </p>
                        </div>

                        <!-- Resultados de búsqueda en tiempo real -->
                        <div v-if="searchResults.length > 0" class="space-y-4">
                            <h4 class="font-medium text-gray-900 dark:text-white flex items-center gap-2">
                                <component :is="icons.receipt" class="w-4 h-4" />
                                {{ searchResults.length }} {{ searchResults.length === 1 ? 'venta encontrada' : 'ventas encontradas' }}
                                <span v-if="searchTerm" class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                                    para "{{ searchTerm }}"
                                </span>
                            </h4>

                            <div
                                v-for="sale in searchResults"
                                :key="sale.id"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md hover:border-blue-300 dark:hover:border-blue-600 transition-all bg-white dark:bg-gray-700"
                            >
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-4 mb-2">
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                Ticket #{{ sale.sale_number }}
                                            </h4>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                                ✅ {{ sale.status }}
                                            </span>
                                            <span v-if="sale.total_returned > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 gap-1">
                                                <component :is="icons.returns" class="w-3 h-3" />
                                                Con devoluciones previas
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600 dark:text-gray-300 mb-3">
                                            <div>
                                                <strong>Fecha:</strong> {{ formatDate(sale.created_at) }}
                                            </div>
                                            <div>
                                                <strong>Cajero:</strong> {{ sale.user?.name }}
                                            </div>
                                            <div>
                                                <strong>Items:</strong> {{ sale.sale_items?.length || 0 }} productos
                                            </div>
                                        </div>

                                        <!-- Items disponibles para devolución -->
                                        <div class="flex flex-wrap gap-2">
                                            <span
                                                v-for="item in sale.sale_items?.slice(0, 3)"
                                                :key="item.id"
                                                class="inline-flex items-center px-2 py-1 rounded text-xs"
                                                :class="item.can_return ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' : 'bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400'"
                                            >
                                                {{ getItemName(item) }}
                                                <span class="ml-1">
                                                    ({{ item.can_return_quantity || item.quantity }}/{{ item.quantity }})
                                                </span>
                                            </span>
                                            <span v-if="sale.sale_items?.length > 3" class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300">
                                                +{{ sale.sale_items.length - 3 }} más...
                                            </span>
                                        </div>
                                    </div>

                                    <div class="text-right ml-6">
                                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                            ${{ formatPrice(sale.total) }}
                                        </div>
                                        <div v-if="sale.total_returned > 0" class="text-sm text-red-500 dark:text-red-400">
                                            Devuelto: -${{ formatPrice(sale.total_returned) }}
                                        </div>
                                        <div class="text-xs text-blue-600 dark:text-blue-400 font-medium mt-1">
                                            Disponible: ${{ formatPrice(sale.total - (sale.total_returned || 0)) }}
                                        </div>
                                        <div class="mt-2 px-3 py-1 rounded-full text-xs font-medium"
                                             :class="sale.can_return ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400'">
                                            {{ sale.can_return ? '✅ Puede devolver' : '⚠️ Totalmente devuelto' }}
                                        </div>

                                        <!-- Botón para seleccionar y procesar devolución -->
                                        <button
                                            v-if="sale.can_return"
                                            @click="selectSale(sale)"
                                            class="mt-3 w-full inline-flex items-center justify-center gap-1 bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded transition-colors"
                                        >
                                            <component :is="icons.returns" class="w-4 h-4" />
                                            Procesar Devolución
                                        </button>
                                        <button
                                            v-else
                                            disabled
                                            class="mt-3 w-full inline-flex items-center justify-center gap-1 bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 font-bold py-2 px-4 rounded cursor-not-allowed"
                                        >
                                            <component :is="icons.close" class="w-4 h-4" />
                                            No disponible
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mensaje cuando no hay resultados pero sí hay búsqueda -->
                        <div v-else-if="searchTerm && !searching" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <component :is="icons.search" class="w-12 h-12 mx-auto mb-2 text-gray-400 dark:text-gray-500" />
                            <div class="text-lg">No se encontraron ventas</div>
                            <div class="text-sm mt-1">para "{{ searchTerm }}"</div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                Intenta con: ID de venta, número de ticket, o últimos dígitos
                            </div>
                        </div>

                        <!-- Estado inicial -->
                        <div v-else-if="!searchTerm" class="text-center py-12 text-gray-500 dark:text-gray-400">
                            <component :is="icons.receipt" class="w-16 h-16 mx-auto mb-4 text-gray-400 dark:text-gray-500" />
                            <div class="text-lg font-medium">Buscar venta para devolver</div>
                            <div class="text-sm mt-2">Comienza escribiendo el número de ticket o ID</div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 text-xs">
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                    <strong>Por ID:</strong><br>
                                    Ej: 1, 2, 13, 14
                                </div>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                    <strong>Por últimos dígitos:</strong><br>
                                    Ej: 0001, 0002
                                </div>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                    <strong>Por número completo:</strong><br>
                                    Ej: 202506110001
                                </div>
                            </div>
                        </div>

                        <!-- Estado de carga -->
                        <div v-else-if="searching" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <div class="text-3xl mb-2">⏳</div>
                            <div>Buscando ventas...</div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Devolución Completo -->
                <div v-if="selectedSale" class="space-y-6">
                    <!-- Información de la Venta Seleccionada -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                        <component :is="icons.receipt" class="w-5 h-5" />
                                        Venta Seleccionada: #{{ selectedSale.sale_number }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ formatDate(selectedSale.created_at) }} - Cajero: {{ selectedSale.user.name }}
                                    </p>
                                </div>
                                <button
                                    @click="clearSelection"
                                    class="inline-flex items-center gap-1 text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm transition-colors"
                                >
                                    <component :is="icons.close" class="w-4 h-4" />
                                    Cambiar venta
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div class="text-center p-4 bg-green-50 dark:bg-green-900/30 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                        ${{ formatPrice(selectedSale.total) }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Original</div>
                                </div>

                                <div class="text-center p-4 bg-red-50 dark:bg-red-900/30 rounded-lg">
                                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                                        ${{ formatPrice(selectedSale.total_returned || 0) }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Ya Devuelto</div>
                                </div>

                                <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                        ${{ formatPrice(selectedSale.total - (selectedSale.total_returned || 0)) }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Disponible para Devolver</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de Devolución -->
                    <form @submit.prevent="submitReturn" class="space-y-6">
                        <!-- Selección de Items -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                                    <component :is="icons.pos" class="w-5 h-5" />
                                    Seleccionar Items a Devolver
                                </h3>

                                <div class="space-y-4">
                                    <div
                                        v-for="item in selectedSale.sale_items"
                                        :key="item.id"
                                        class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                                        :class="{'border-orange-300 dark:border-orange-600 bg-orange-50 dark:bg-orange-900/20': form.items.some(i => i.sale_item_id === item.id)}"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-4">
                                                <input
                                                    type="checkbox"
                                                    :id="`item-${item.id}`"
                                                    :checked="form.items.some(i => i.sale_item_id === item.id)"
                                                    @change="toggleItem(item)"
                                                    :disabled="!item.can_return"
                                                    class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 dark:border-gray-600 rounded"
                                                />
                                                <label :for="`item-${item.id}`" class="cursor-pointer">
                                                    <div class="font-medium text-gray-900 dark:text-white">{{ getItemName(item) }}</div>
                                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                                        ${{ formatPrice(item.unit_price) }} × {{ item.quantity }} = ${{ formatPrice(item.total_price) }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-500">
                                                        {{ item.can_return_quantity || item.quantity }} disponibles para devolver
                                                    </div>
                                                </label>
                                            </div>

                                            <!-- Control de cantidad -->
                                            <div v-if="form.items.some(i => i.sale_item_id === item.id)" class="flex items-center space-x-2">
                                                <label class="text-sm text-gray-600 dark:text-gray-400">Cantidad:</label>
                                                <input
                                                    type="number"
                                                    :min="1"
                                                    :max="item.can_return_quantity || item.quantity"
                                                    v-model="getFormItem(item.id).quantity"
                                                    class="w-20 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-orange-500 focus:ring-orange-500 rounded-md text-center"
                                                />
                                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                                    de {{ item.can_return_quantity || item.quantity }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="form.items.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <component :is="icons.package" class="w-12 h-12 mx-auto mb-2 text-gray-400 dark:text-gray-500" />
                                    <div>Selecciona los productos que deseas devolver</div>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Devolución -->
                        <div v-if="form.items.length > 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                                    <component :is="icons.document" class="w-5 h-5" />
                                    Información de la Devolución
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Razón de la devolución *</label>
                                        <select
                                            v-model="form.reason"
                                            required
                                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-orange-500 focus:ring-orange-500 rounded-md"
                                        >
                                            <option value="">Seleccionar razón</option>
                                            <option value="defective">Producto defectuoso</option>
                                            <option value="wrong_order">Orden incorrecta</option>
                                            <option value="customer_request">Solicitud del cliente</option>
                                            <option value="error">Error del sistema</option>
                                            <option value="other">Otra razón</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Método de reembolso *</label>
                                        <select
                                            v-model="form.refund_method"
                                            required
                                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-orange-500 focus:ring-orange-500 rounded-md"
                                        >
                                            <option value="">Seleccionar método</option>
                                            <option value="efectivo">Efectivo</option>
                                            <option value="tarjeta">Tarjeta</option>
                                            <option value="transferencia">Transferencia</option>
                                            <option value="credito">Crédito a cuenta</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notas adicionales</label>
                                    <textarea
                                        v-model="form.notes"
                                        rows="3"
                                        placeholder="Descripción adicional de la devolución..."
                                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 focus:border-orange-500 focus:ring-orange-500 rounded-md"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen y Confirmación -->
                        <div v-if="form.items.length > 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                                    <component :is="icons.cash" class="w-5 h-5" />
                                    Resumen de Devolución
                                </h3>

                                <div class="space-y-4 mb-6">
                                    <div
                                        v-for="item in form.items"
                                        :key="item.sale_item_id"
                                        class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700"
                                    >
                                        <div>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ getItemNameById(item.sale_item_id) }}</span>
                                            <span class="text-gray-500 dark:text-gray-400 ml-2">× {{ item.quantity }}</span>
                                        </div>
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            ${{ formatPrice(getItemPriceById(item.sale_item_id) * item.quantity) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="border-t border-gray-300 dark:border-gray-600 pt-4">
                                    <div class="flex justify-between items-center text-xl font-bold text-gray-900 dark:text-white">
                                        <span>Total a devolver:</span>
                                        <span class="text-red-600 dark:text-red-400">${{ formatPrice(getTotalToReturn()) }}</span>
                                    </div>
                                </div>

                                <div class="mt-6 flex space-x-4">
                                    <button
                                        type="button"
                                        @click="clearSelection"
                                        class="flex-1 inline-flex items-center justify-center gap-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition-colors"
                                    >
                                        <component :is="icons.close" class="w-5 h-5" />
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="processing || !form.reason || !form.refund_method"
                                        class="flex-1 inline-flex items-center justify-center gap-1 bg-orange-500 hover:bg-orange-700 text-white font-bold py-3 px-6 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <template v-if="processing">
                                            <span>⏳</span>
                                            Procesando...
                                        </template>
                                        <template v-else>
                                            <span>✅</span>
                                            Confirmar Devolución
                                        </template>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useIcons } from '@/composables/useIcons';

// Icons
const { icons } = useIcons();

// Props que vienen del controlador
const props = defineProps({
    searchResults: {
        type: Array,
        default: () => []
    },
    searchTerm: {
        type: String,
        default: ''
    },
    sale: {
        type: Object,
        default: null
    }
});

// Estado reactivo
const searchTerm = ref(props.searchTerm || '');
const selectedSale = ref(props.sale || null);
const searching = ref(false);
const searchTimeout = ref(null);
const processing = ref(false);

// Computed para los resultados
const searchResults = computed(() => props.searchResults || []);

// 🔄 NUEVO: Formulario de devolución
const form = useForm({
    sale_id: null,
    reason: '',
    notes: '',
    refund_method: '',
    items: []
});

// 🔄 BÚSQUEDA EN TIEMPO REAL (igual que antes)
const onSearchInput = () => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }

    if (!searchTerm.value || searchTerm.value.length < 1) {
        router.get(route('returns.create'), {}, {
            preserveState: true,
            preserveScroll: true
        });
        return;
    }

    searching.value = true;

    searchTimeout.value = setTimeout(() => {
        performSearch();
    }, 300);
};

const performSearch = () => {
    console.log('🔍 Búsqueda en vivo:', searchTerm.value);

    router.get(route('returns.create'), 
        { search: searchTerm.value },
        {
            preserveState: true,
            preserveScroll: true,
            onStart: () => {
                searching.value = true;
            },
            onFinish: () => {
                searching.value = false;
            }
        }
    );
};

// 🔄 NUEVAS: Funciones del formulario
const selectSale = (sale) => {
    selectedSale.value = sale;
    form.sale_id = sale.id;
    form.items = [];
    console.log('✅ Venta seleccionada:', sale);
};

const clearSelection = () => {
    selectedSale.value = null;
    searchTerm.value = '';
    form.reset();
    router.get(route('returns.create'), {}, {
        preserveState: true,
        preserveScroll: true
    });
};

// 🔄 NUEVA: Verificar si es un combo
const isComboItem = (item) => {
    // Un combo típicamente tiene múltiples ingredientes en su nombre
    // o contiene palabras clave como "combo", "con", "+"
    const name = getItemName(item).toLowerCase();
    return name.includes('combo') || 
           name.includes(' + ') || 
           name.includes(' con ') ||
           (item.menu_item && item.menu_item.recipes && item.menu_item.recipes.length > 3);
};

const toggleItem = (item) => {
    const index = form.items.findIndex(i => i.sale_item_id === item.id);
    
    if (index >= 0) {
        form.items.splice(index, 1);
    } else if (item.can_return) {
        // 🔄 NUEVO: Para combos, forzar devolución completa
        const quantity = isComboItem(item) ? item.quantity : 1;
        
        form.items.push({
            sale_item_id: item.id,
            quantity: quantity
        });
    }
};

const getFormItem = (itemId) => {
    return form.items.find(i => i.sale_item_id === itemId);
};

const getItemName = (item) => {
    if (item.menu_item) {
        return item.menu_item.name;
    } else if (item.simple_product) {
        return item.simple_product.name;
    }
    return 'Producto desconocido';
};

const getItemNameById = (itemId) => {
    const item = selectedSale.value.sale_items.find(i => i.id === itemId);
    return getItemName(item);
};

const getItemPriceById = (itemId) => {
    const item = selectedSale.value.sale_items.find(i => i.id === itemId);
    return item.unit_price;
};

const getTotalToReturn = () => {
    return form.items.reduce((total, formItem) => {
        const item = selectedSale.value.sale_items.find(i => i.id === formItem.sale_item_id);
        return total + (item.unit_price * formItem.quantity);
    }, 0);
};

const submitReturn = () => {
    if (!form.reason || !form.refund_method || form.items.length === 0) {
        alert('Por favor completa todos los campos requeridos');
        return;
    }

    // 🔍 DEBUG: Verificar datos antes de enviar
    console.log('🔍 DEBUG - Datos del formulario:', {
        sale_id: form.sale_id,
        reason: form.reason,
        refund_method: form.refund_method,
        items: form.items,
        notes: form.notes
    });

    processing.value = true;
    
    form.post(route('returns.store'), {
        onSuccess: (response) => {
            console.log('✅ Devolución procesada exitosamente');
            console.log('📄 Response:', response);
        },
        onError: (errors) => {
            console.error('❌ Error al procesar devolución:', errors);
            alert('Error: ' + Object.values(errors).flat().join(', '));
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        }
    });
};

const formatPrice = (price) => {
    return parseFloat(price || 0).toFixed(2);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// 🔄 NUEVO: Auto-buscar cuando viene con sale_id
onMounted(() => {
    if (props.sale && props.sale.id) {
        // Si viene con venta pre-seleccionada, auto-buscar su número
        searchTerm.value = props.sale.sale_number || props.sale.id.toString();
        selectedSale.value = props.sale;
        form.sale_id = props.sale.id;
    } else if (props.searchTerm) {
        searchTerm.value = props.searchTerm;
    }
});

// Watch para cambios en la prop sale
watch(() => props.sale, (newSale) => {
    if (newSale) {
        selectedSale.value = newSale;
        form.sale_id = newSale.id;
        searchTerm.value = newSale.sale_number || newSale.id.toString();
    }
}, { immediate: true });
</script>
