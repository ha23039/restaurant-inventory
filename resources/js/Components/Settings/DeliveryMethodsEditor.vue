<script setup>
import { ref, watch, computed } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    allowPickup: Boolean,
    allowDelivery: Boolean,
    allowDineIn: Boolean,
    deliveryFee: {
        type: [Number, String],
        default: 0,
    },
    minOrderAmount: {
        type: [Number, String],
        default: 0,
    },
});

const emit = defineEmits([
    'update:allowPickup',
    'update:allowDelivery',
    'update:allowDineIn',
    'update:deliveryFee',
    'update:minOrderAmount',
]);

const pickup = ref(props.allowPickup);
const delivery = ref(props.allowDelivery);
const dineIn = ref(props.allowDineIn);
const fee = ref(props.deliveryFee);
const minOrder = ref(props.minOrderAmount);

watch(pickup, (value) => emit('update:allowPickup', value));
watch(delivery, (value) => emit('update:allowDelivery', value));
watch(dineIn, (value) => emit('update:allowDineIn', value));
watch(fee, (value) => emit('update:deliveryFee', value));
watch(minOrder, (value) => emit('update:minOrderAmount', value));

const hasAtLeastOneMethod = computed(() => pickup.value || delivery.value || dineIn.value);
</script>

<template>
    <div class="space-y-6">
        <!-- Methods Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Pickup -->
            <div class="relative">
                <label
                    class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition-all duration-200"
                    :class="pickup
                        ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20'
                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600'"
                >
                    <input
                        v-model="pickup"
                        type="checkbox"
                        class="mt-1 w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500 dark:focus:ring-orange-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                    >
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span class="font-semibold text-gray-900 dark:text-white">Para llevar</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            Cliente recoge su pedido en el restaurante
                        </p>
                        <div class="mt-2 text-xs font-medium text-orange-600 dark:text-orange-400">
                            Sin cargo adicional
                        </div>
                    </div>
                </label>
            </div>

            <!-- Delivery -->
            <div class="relative">
                <label
                    class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition-all duration-200"
                    :class="delivery
                        ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20'
                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600'"
                >
                    <input
                        v-model="delivery"
                        type="checkbox"
                        class="mt-1 w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500 dark:focus:ring-orange-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                    >
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span class="font-semibold text-gray-900 dark:text-white">Delivery</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            Entrega a domicilio del cliente
                        </p>
                        <div v-if="delivery" class="mt-2 text-xs font-medium text-orange-600 dark:text-orange-400">
                            Cargo: ${{ parseFloat(fee).toFixed(2) }}
                        </div>
                    </div>
                </label>
            </div>

            <!-- Dine In -->
            <div class="relative">
                <label
                    class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition-all duration-200"
                    :class="dineIn
                        ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20'
                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600'"
                >
                    <input
                        v-model="dineIn"
                        type="checkbox"
                        class="mt-1 w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500 dark:focus:ring-orange-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                    >
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span class="font-semibold text-gray-900 dark:text-white">Comer aquí</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            Cliente come en el restaurante (mesa)
                        </p>
                        <div class="mt-2 text-xs font-medium text-orange-600 dark:text-orange-400">
                            Sin cargo adicional
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Warning if no methods selected -->
        <div v-if="!hasAtLeastOneMethod" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200">
                        Advertencia: No hay métodos de entrega habilitados
                    </h4>
                    <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
                        Debes habilitar al menos un método de entrega para que los clientes puedan realizar pedidos.
                    </p>
                </div>
            </div>
        </div>

        <!-- Additional Settings -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <!-- Delivery Fee -->
            <div v-if="delivery">
                <InputLabel for="delivery_fee" value="Costo de delivery" />
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                    </div>
                    <TextInput
                        id="delivery_fee"
                        v-model="fee"
                        type="number"
                        step="0.01"
                        min="0"
                        class="pl-7"
                        placeholder="0.00"
                    />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Costo adicional por entrega a domicilio
                </p>
            </div>

            <!-- Minimum Order -->
            <div>
                <InputLabel for="min_order" value="Pedido mínimo" />
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                    </div>
                    <TextInput
                        id="min_order"
                        v-model="minOrder"
                        type="number"
                        step="0.01"
                        min="0"
                        class="pl-7"
                        placeholder="0.00"
                    />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Monto mínimo requerido para realizar un pedido (0 = sin mínimo)
                </p>
            </div>
        </div>
    </div>
</template>
