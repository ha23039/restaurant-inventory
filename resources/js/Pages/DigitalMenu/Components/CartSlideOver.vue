<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: Boolean,
    cart: Array,
    settings: Object,
});

const emit = defineEmits(['close', 'update-quantity', 'remove-item', 'clear-cart', 'proceed-checkout']);

const cartTotal = computed(() => {
    return props.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const cartItemsCount = computed(() => {
    return props.cart.reduce((sum, item) => sum + item.quantity, 0);
});

const canProceed = computed(() => {
    return props.cart.length > 0 && cartTotal.value >= (props.settings.min_order_amount || 0);
});

const incrementQuantity = (index) => {
    const item = props.cart[index];
    emit('update-quantity', index, item.quantity + 1);
};

const decrementQuantity = (index) => {
    const item = props.cart[index];
    if (item.quantity > 1) {
        emit('update-quantity', index, item.quantity - 1);
    } else {
        emit('remove-item', index);
    }
};

const proceedToCheckout = () => {
    if (canProceed.value) {
        emit('proceed-checkout');
    }
};
</script>

<template>
    <!-- Backdrop -->
    <Transition
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            @click="emit('close')"
            class="fixed inset-0 bg-black/50 dark:bg-black/70 z-40"
        ></div>
    </Transition>

    <!-- SlideOver -->
    <Transition
        enter-active-class="transition-transform duration-300"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition-transform duration-300"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="show"
            class="fixed inset-y-0 right-0 w-full sm:w-96 bg-white dark:bg-gray-800 shadow-xl z-50 flex flex-col"
        >
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tu Pedido</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ cartItemsCount }} item{{ cartItemsCount > 1 ? 's' : '' }}</p>
                </div>
                <button
                    @click="emit('close')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                <div v-if="cart.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="text-gray-600 dark:text-gray-400">Tu carrito esta vacio</p>
                    <button
                        @click="emit('close')"
                        class="mt-4 text-orange-600 dark:text-orange-400 font-medium hover:underline"
                    >
                        Explorar menu
                    </button>
                </div>

                <div
                    v-for="(item, index) in cart"
                    :key="`cart-${index}`"
                    class="flex gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                >
                    <!-- Product Image -->
                    <div class="w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden bg-gray-200 dark:bg-gray-600">
                        <img
                            v-if="item.image_path"
                            :src="item.image_path"
                            :alt="item.name"
                            class="w-full h-full object-cover"
                        >
                        <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-400 to-orange-600">
                            <svg class="w-8 h-8 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white line-clamp-2 mb-1">
                            {{ item.name }}
                        </h3>
                        <p class="text-sm font-semibold text-orange-600 dark:text-orange-400">
                            ${{ parseFloat(item.price).toFixed(2) }}
                        </p>

                        <!-- Quantity Controls -->
                        <div class="flex items-center gap-2 mt-2">
                            <button
                                @click="decrementQuantity(index)"
                                class="w-7 h-7 flex items-center justify-center rounded-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-orange-500 dark:hover:border-orange-500 transition-colors duration-200"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </button>
                            <span class="w-8 text-center text-sm font-medium text-gray-900 dark:text-white">
                                {{ item.quantity }}
                            </span>
                            <button
                                @click="incrementQuantity(index)"
                                class="w-7 h-7 flex items-center justify-center rounded-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-orange-500 dark:hover:border-orange-500 transition-colors duration-200"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </button>
                            <button
                                @click="emit('remove-item', index)"
                                class="ml-auto text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 p-1"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Clear Cart Button -->
                <button
                    v-if="cart.length > 0"
                    @click="emit('clear-cart')"
                    class="w-full text-center text-sm text-red-600 dark:text-red-400 hover:underline py-2"
                >
                    Vaciar carrito
                </button>
            </div>

            <!-- Footer -->
            <div v-if="cart.length > 0" class="border-t border-gray-200 dark:border-gray-700 p-4 space-y-4">
                <!-- Total -->
                <div class="flex items-center justify-between text-lg font-semibold">
                    <span class="text-gray-900 dark:text-white">Total</span>
                    <span class="text-orange-600 dark:text-orange-400">${{ cartTotal.toFixed(2) }}</span>
                </div>

                <!-- Minimum Order Warning -->
                <div v-if="!canProceed && settings.min_order_amount > 0" class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <p class="text-xs text-yellow-800 dark:text-yellow-200">
                        Pedido minimo: ${{ settings.min_order_amount.toFixed(2) }}
                        <br>
                        Te faltan: ${{ (settings.min_order_amount - cartTotal).toFixed(2) }}
                    </p>
                </div>

                <!-- Checkout Button -->
                <button
                    @click="proceedToCheckout"
                    :disabled="!canProceed"
                    class="w-full py-3 px-4 rounded-lg font-semibold transition-all duration-200 active:scale-95"
                    :class="canProceed
                        ? 'bg-orange-600 hover:bg-orange-700 text-white shadow-sm hover:shadow-md'
                        : 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed'
                    "
                >
                    Continuar al Checkout
                </button>
            </div>
        </div>
    </Transition>
</template>
