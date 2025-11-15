<template>
    <div class="ticket-manager">
        <!-- Botones de Impresión Rápida -->
        <div class="flex space-x-3 mb-4" v-if="sale">
            <button
                @click="printKitchen"
                :disabled="printing.kitchen || !hasKitchenItems"
                class="flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 disabled:bg-gray-400 text-white font-bold rounded transition-colors"
            >
                <span v-if="printing.kitchen" class="animate-spin mr-2">🔄</span>
                <span v-else class="mr-2">🍳</span>
                {{ printing.kitchen ? 'Enviando...' : 'Comanda a Cocina' }}
            </button>

            <button
                @click="printCustomer"
                :disabled="printing.customer"
                class="flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-bold rounded transition-colors"
            >
                <span v-if="printing.customer" class="animate-spin mr-2">🔄</span>
                <span v-else class="mr-2">🧾</span>
                {{ printing.customer ? 'Imprimiendo...' : 'Ticket Cliente' }}
            </button>

            <button
                @click="showReprintModal = true"
                class="flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-bold rounded transition-colors"
            >
                <span class="mr-2">🔄</span>
                Reimprimir
            </button>
        </div>

        <!-- Configuración de Impresión Automática -->
        <div class="bg-gray-50 rounded-lg p-4 mb-4" v-if="showAutoSettings">
            <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                ⚙️ Configuración de Impresión Automática
            </h4>
            <div class="grid grid-cols-2 gap-4">
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        v-model="autoSettings.printKitchen"
                        class="mr-2 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                    />
                    <span class="text-sm">Auto-enviar comanda a cocina</span>
                </label>
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        v-model="autoSettings.printCustomer"
                        class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="text-sm">Auto-imprimir ticket cliente</span>
                </label>
            </div>
        </div>

        <!-- Estado de Impresoras -->
        <div class="bg-white rounded-lg border p-4 mb-4" v-if="showPrinterStatus">
            <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                🖨️ Estado de Impresoras
            </h4>
            <div class="grid grid-cols-2 gap-4">
                <div class="flex items-center">
                    <div 
                        class="w-3 h-3 rounded-full mr-2"
                        :class="printerStatus.kitchen ? 'bg-green-500' : 'bg-red-500'"
                    ></div>
                    <span class="text-sm">Cocina ({{ printerStatus.kitchen ? 'Conectada' : 'Sin conexión' }})</span>
                </div>
                <div class="flex items-center">
                    <div 
                        class="w-3 h-3 rounded-full mr-2"
                        :class="printerStatus.customer ? 'bg-green-500' : 'bg-red-500'"
                    ></div>
                    <span class="text-sm">Cliente ({{ printerStatus.customer ? 'Conectada' : 'Sin conexión' }})</span>
                </div>
            </div>
        </div>

        <!-- Historial de Impresiones -->
        <div class="bg-white rounded-lg border p-4" v-if="showPrintHistory && printHistory.length > 0">
            <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                📋 Últimas Impresiones
            </h4>
            <div class="space-y-2">
                <div 
                    v-for="(print, index) in printHistory" 
                    :key="index"
                    class="flex items-center justify-between text-sm p-2 bg-gray-50 rounded"
                >
                    <div class="flex items-center">
                        <span class="mr-2">{{ print.type === 'kitchen' ? '🍳' : '🧾' }}</span>
                        <span>{{ print.type === 'kitchen' ? 'Comanda' : 'Ticket' }}</span>
                    </div>
                    <div class="flex items-center">
                        <span 
                            class="mr-2 text-xs px-2 py-1 rounded"
                            :class="print.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                        >
                            {{ print.success ? '✅' : '❌' }}
                        </span>
                        <span class="text-gray-500">{{ print.time }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Reimpresión -->
        <div v-if="showReprintModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-96">
                <h3 class="text-lg font-semibold mb-4">🔄 Reimprimir Tickets</h3>
                
                <div class="space-y-3 mb-6">
                    <label class="flex items-center">
                        <input
                            type="radio"
                            v-model="reprintType"
                            value="kitchen"
                            class="mr-2"
                        />
                        <span>🍳 Solo comanda de cocina</span>
                    </label>
                    <label class="flex items-center">
                        <input
                            type="radio"
                            v-model="reprintType"
                            value="customer"
                            class="mr-2"
                        />
                        <span>🧾 Solo ticket de cliente</span>
                    </label>
                    <label class="flex items-center">
                        <input
                            type="radio"
                            v-model="reprintType"
                            value="both"
                            class="mr-2"
                        />
                        <span>📋 Ambos tickets</span>
                    </label>
                </div>

                <div class="flex space-x-3">
                    <button
                        @click="executeReprint"
                        :disabled="reprinting || !reprintType"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-bold py-2 px-4 rounded"
                    >
                        {{ reprinting ? 'Reimprimiendo...' : 'Reimprimir' }}
                    </button>
                    <button
                        @click="showReprintModal = false"
                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

        <!-- Notificaciones -->
        <div v-if="notification" class="fixed top-4 right-4 z-50">
            <div 
                class="px-6 py-4 rounded-lg shadow-lg transition-all duration-300"
                :class="{
                    'bg-green-100 border border-green-400 text-green-700': notification.type === 'success',
                    'bg-red-100 border border-red-400 text-red-700': notification.type === 'error',
                    'bg-blue-100 border border-blue-400 text-blue-700': notification.type === 'info'
                }"
            >
                <div class="flex items-center">
                    <span class="mr-2">
                        {{ notification.type === 'success' ? '✅' : notification.type === 'error' ? '❌' : 'ℹ️' }}
                    </span>
                    {{ notification.message }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';

// Props
const props = defineProps({
    sale: Object,
    showAutoSettings: {
        type: Boolean,
        default: true
    },
    showPrinterStatus: {
        type: Boolean,
        default: true
    },
    showPrintHistory: {
        type: Boolean,
        default: true
    }
});

// Reactive data
const printing = ref({
    kitchen: false,
    customer: false
});

const autoSettings = ref({
    printKitchen: true,
    printCustomer: true
});

const printerStatus = ref({
    kitchen: true,
    customer: true
});

const printHistory = ref([]);
const notification = ref(null);
const showReprintModal = ref(false);
const reprintType = ref('both');
const reprinting = ref(false);

// Computed
const hasKitchenItems = computed(() => {
    if (!props.sale || !props.sale.sale_items) return false;
    
    // Categorías que NO van a cocina
    const nonKitchenCategories = ['bebidas', 'bebidas_frias', 'postres_frios'];
    
    return props.sale.sale_items.some(item => {
        return !nonKitchenCategories.includes(item.category_slug);
    });
});

// Methods
const printKitchen = async () => {
    if (!props.sale) return;
    
    printing.value.kitchen = true;
    
    try {
        const response = await fetch(`/tickets/kitchen/${props.sale.id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Comanda enviada a cocina exitosamente', 'success');
            addToPrintHistory('kitchen', true);
        } else {
            showNotification(result.message || 'Error al enviar comanda', 'error');
            addToPrintHistory('kitchen', false);
        }
        
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión al enviar comanda', 'error');
        addToPrintHistory('kitchen', false);
    }
    
    printing.value.kitchen = false;
};

const printCustomer = async () => {
    if (!props.sale) return;
    
    printing.value.customer = true;
    
    try {
        const response = await fetch(`/tickets/customer/${props.sale.id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Ticket impreso exitosamente', 'success');
            addToPrintHistory('customer', true);
        } else {
            showNotification(result.message || 'Error al imprimir ticket', 'error');
            addToPrintHistory('customer', false);
        }
        
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión al imprimir ticket', 'error');
        addToPrintHistory('customer', false);
    }
    
    printing.value.customer = false;
};

const executeReprint = async () => {
    if (!props.sale || !reprintType.value) return;
    
    reprinting.value = true;
    
    try {
        const response = await fetch(`/tickets/reprint/${props.sale.id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                type: reprintType.value
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Reimpresión exitosa', 'success');
            
            // Agregar al historial según el tipo
            if (reprintType.value === 'kitchen' || reprintType.value === 'both') {
                addToPrintHistory('kitchen', true);
            }
            if (reprintType.value === 'customer' || reprintType.value === 'both') {
                addToPrintHistory('customer', true);
            }
        } else {
            showNotification(result.message || 'Error en reimpresión', 'error');
        }
        
        showReprintModal.value = false;
        reprintType.value = 'both';
        
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión en reimpresión', 'error');
    }
    
    reprinting.value = false;
};

const autoprint = async () => {
    if (!props.sale) return;
    
    try {
        const response = await fetch(`/tickets/autoprint/${props.sale.id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                print_kitchen: autoSettings.value.printKitchen,
                print_customer: autoSettings.value.printCustomer
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Impresión automática completada', 'success');
            
            // Agregar al historial
            if (result.results.kitchen) {
                addToPrintHistory('kitchen', result.results.kitchen.success);
            }
            if (result.results.customer) {
                addToPrintHistory('customer', result.results.customer.success);
            }
        } else {
            showNotification(result.message || 'Error en impresión automática', 'error');
        }
        
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error en impresión automática', 'error');
    }
};

const showNotification = (message, type = 'info') => {
    notification.value = { message, type };
    
    setTimeout(() => {
        notification.value = null;
    }, 5000);
};

const addToPrintHistory = (type, success) => {
    const now = new Date();
    const time = now.toLocaleTimeString('es-ES', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
    
    printHistory.value.unshift({ type, success, time });
    
    // Mantener solo los últimos 5
    if (printHistory.value.length > 5) {
        printHistory.value = printHistory.value.slice(0, 5);
    }
};

// Exposer métodos para uso externo
defineExpose({
    printKitchen,
    printCustomer,
    autoprint
});

// Lifecycle
onMounted(() => {
    // Cargar configuración guardada
    const savedSettings = localStorage.getItem('autoprint_settings');
    if (savedSettings) {
        autoSettings.value = JSON.parse(savedSettings);
    }
    
    // Verificar estado de impresoras (opcional)
    checkPrinterStatus();
});

// Watch autoSettings para guardar cambios
import { watch } from 'vue';
watch(autoSettings, (newSettings) => {
    localStorage.setItem('autoprint_settings', JSON.stringify(newSettings));
}, { deep: true });

const checkPrinterStatus = async () => {
    try {
        const response = await fetch('/tickets/stats');
        const result = await response.json();
        
        if (result.success) {
            printerStatus.value = {
                kitchen: result.data.printers.kitchen_enabled,
                customer: result.data.printers.customer_enabled
            };
        }
    } catch (error) {
        console.warn('No se pudo verificar estado de impresoras:', error);
    }
};
</script>

<style scoped>
.ticket-manager {
    width: 100%;
}

/* Animaciones personalizadas */
@keyframes pulse-success {
    0%, 100% { background-color: #d1fae5; } /* Tailwind bg-green-100 */
    50% { background-color: #bbf7d0; }      /* Tailwind bg-green-200 */
}

@keyframes pulse-error {
    0%, 100% { background-color: #fee2e2; } /* Tailwind bg-red-100 */
    50% { background-color: #fecaca; }      /* Tailwind bg-red-200 */
}

.pulse-success {
    animation: pulse-success 2s ease-in-out;
}

.pulse-error {
    animation: pulse-error 2s ease-in-out;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .ticket-manager .flex {
        flex-direction: column;
        gap: 0.5rem;
    }

    .ticket-manager .grid-cols-2 {
        grid-template-columns: 1fr;
    }
}
</style>