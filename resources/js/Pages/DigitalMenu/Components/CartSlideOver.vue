<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    cart: Array,
    settings: Object,
});

const emit = defineEmits(['close', 'update-quantity', 'remove-item', 'clear-cart', 'proceed-checkout']);

const cartTotal = computed(() => props.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0));
const cartItemsCount = computed(() => props.cart.reduce((sum, item) => sum + item.quantity, 0));

const minOrderAmount = computed(() => parseFloat(props.settings?.min_order_amount || 0));
const canProceed = computed(() => props.cart.length > 0 && cartTotal.value >= minOrderAmount.value);
const remaining = computed(() => Math.max(0, minOrderAmount.value - cartTotal.value));

// Touch gesture state for horizontal swipe
const touchStart = ref({ x: 0 });
const touchDelta = ref(0);
const isDragging = ref(false);

const handleTouchStart = (e) => {
    touchStart.value.x = e.touches[0].clientX;
    touchDelta.value = 0;
    isDragging.value = false;
};

const handleTouchMove = (e) => {
    const deltaX = e.touches[0].clientX - touchStart.value.x;
    
    // Only allow dragging right (positive delta)
    if (deltaX > 20) {
        isDragging.value = true;
        touchDelta.value = Math.min(deltaX, 300);
    }
};

const handleTouchEnd = () => {
    if (isDragging.value && touchDelta.value > 100) {
        emit('close');
    }
    touchDelta.value = 0;
    isDragging.value = false;
};

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
</script>

<template>
    <!-- Backdrop -->
    <Transition
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            @click="emit('close')"
            class="fixed inset-0 bg-black/60 z-40"
        ></div>
    </Transition>

    <!-- SlideOver -->
    <Transition
        enter-active-class="transition-transform duration-300 ease-out"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition-transform duration-200 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="show"
            @touchstart="handleTouchStart"
            @touchmove.passive="handleTouchMove"
            @touchend="handleTouchEnd"
            class="fixed inset-y-0 right-0 w-full sm:w-[420px] bg-white dark:bg-gray-900 shadow-2xl z-50 flex flex-col"
            :style="isDragging ? { transform: `translateX(${touchDelta}px)`, transition: 'none' } : {}"
        >
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 bg-gradient-to-r from-orange-500 to-red-500">
                <div>
                    <h2 class="text-xl font-bold text-white">Tu Pedido</h2>
                    <p class="text-sm text-white/80">{{ cartItemsCount }} producto{{ cartItemsCount !== 1 ? 's' : '' }}</p>
                </div>
                <button
                    @click="emit('close')"
                    class="p-2 text-white/80 hover:text-white hover:bg-white/20 rounded-xl transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto">
                <!-- Empty State -->
                <div v-if="cart.length === 0" class="flex flex-col items-center justify-center h-full py-12 px-6">
                    <div class="w-24 h-24 mb-6 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Tu carrito está vacío</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">Agrega productos deliciosos para comenzar tu pedido</p>
                    <button
                        @click="emit('close')"
                        class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl transition-colors"
                    >
                        Explorar menú
                    </button>
                </div>

                <!-- Items List -->
                <div v-else class="p-4 space-y-3">
                    <div
                        v-for="(item, index) in cart"
                        :key="`cart-${index}`"
                        class="flex gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-2xl"
                    >
                        <!-- Product Image -->
                        <div class="w-20 h-20 flex-shrink-0 rounded-xl overflow-hidden bg-gray-200 dark:bg-gray-700">
                            <img
                                v-if="item.image_path"
                                :src="item.image_path"
                                :alt="item.name"
                                class="w-full h-full object-cover"
                            >
                            <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-400 to-red-500">
                                <svg class="w-8 h-8 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-2">
                                    {{ item.name }}
                                </h3>
                                <button
                                    @click="emit('remove-item', index)"
                                    class="flex-shrink-0 p-1 text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="flex items-center justify-between">
                                <!-- Quantity Controls -->
                                <div class="flex items-center gap-1 bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600">
                                    <button
                                        @click="decrementQuantity(index)"
                                        class="w-8 h-8 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-orange-500 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                        </svg>
                                    </button>
                                    <span class="w-8 text-center text-sm font-bold text-gray-900 dark:text-white">
                                        {{ item.quantity }}
                                    </span>
                                    <button
                                        @click="incrementQuantity(index)"
                                        class="w-8 h-8 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-orange-500 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Price -->
                                <p class="text-base font-bold text-orange-600 dark:text-orange-400">
                                    ${{ (parseFloat(item.price) * item.quantity).toFixed(2) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Clear Cart -->
                    <button
                        @click="emit('clear-cart')"
                        class="w-full text-center text-sm text-gray-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 py-2 transition-colors"
                    >
                        Vaciar carrito
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div v-if="cart.length > 0" class="border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 p-5 space-y-4">
                <!-- Minimum Order Warning -->
                <div v-if="!canProceed && minOrderAmount > 0" class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="text-sm text-amber-800 dark:text-amber-200">
                            <p class="font-medium">Pedido mínimo: ${{ minOrderAmount.toFixed(2) }}</p>
                            <p class="text-xs opacity-80">Te faltan ${{ remaining.toFixed(2) }} más</p>
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Total</span>
                    <span class="text-2xl font-black bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent">
                        ${{ cartTotal.toFixed(2) }}
                    </span>
                </div>

                <!-- Checkout Button -->
                <button
                    @click="emit('proceed-checkout')"
                    :disabled="!canProceed"
                    class="w-full py-4 rounded-2xl font-bold text-lg transition-all duration-200 active:scale-98"
                    :class="canProceed
                        ? 'bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white shadow-lg shadow-orange-500/30'
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                    "
                >
                    <span v-if="canProceed">Continuar al checkout →</span>
                    <span v-else>Agrega más productos</span>
                </button>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.active\:scale-98:active {
    transform: scale(0.98);
}
</style>
