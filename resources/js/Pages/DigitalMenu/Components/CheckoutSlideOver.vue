<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    cart: Array,
    cartTotal: Number,
    settings: Object,
});

const emit = defineEmits(['close', 'confirm']);

const customerName = ref('');
const customerPhone = ref('');
const customerNotes = ref('');
const deliveryMethod = ref('pickup');

// Touch gesture state
const touchStart = ref({ y: 0 });
const touchDelta = ref(0);
const isDragging = ref(false);

const isFormValid = computed(() => {
    return customerName.value.trim().length >= 2 && customerPhone.value.trim().length >= 8;
});

const deliveryMethods = computed(() => {
    const methods = props.settings?.delivery_methods || [];
    return methods.length > 0 ? methods : [
        { id: 'pickup', label: 'Para llevar', fee: 0 },
        { id: 'dine_in', label: 'Comer aquí', fee: 0 },
    ];
});

const handleConfirm = () => {
    if (!isFormValid.value) return;

    emit('confirm', {
        customerName: customerName.value.trim(),
        customerPhone: customerPhone.value.trim(),
        customerNotes: customerNotes.value.trim(),
        deliveryMethod: deliveryMethod.value,
    });
};

// Touch handlers for swipe-to-close
const handleTouchStart = (e) => {
    touchStart.value.y = e.touches[0].clientY;
    touchDelta.value = 0;
    isDragging.value = false;
};

const handleTouchMove = (e) => {
    const deltaY = e.touches[0].clientY - touchStart.value.y;
    
    // Start dragging with minimal threshold for natural feel
    if (deltaY > 5) {
        isDragging.value = true;
        // Apply resistance for rubber-band effect
        touchDelta.value = Math.min(deltaY * 0.8, 250);
    }
};

const handleTouchEnd = () => {
    // Close if dragged more than 80px (easier to trigger)
    if (isDragging.value && touchDelta.value > 80) {
        emit('close');
    }
    touchDelta.value = 0;
    isDragging.value = false;
};
</script>

<template>
    <!-- Backdrop -->
    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            @click="emit('close')"
            class="fixed inset-0 bg-black/60 z-50"
        ></div>
    </Transition>

    <!-- SlideOver -->
    <Transition
        enter-active-class="transition-transform duration-300"
        enter-from-class="translate-y-full"
        enter-to-class="translate-y-0"
        leave-active-class="transition-transform duration-200"
        leave-from-class="translate-y-0"
        leave-to-class="translate-y-full"
    >
        <div
            v-if="show"
            @touchstart="handleTouchStart"
            @touchmove.passive="handleTouchMove"
            @touchend="handleTouchEnd"
            class="fixed inset-x-0 bottom-0 z-50 max-h-[90vh] bg-white dark:bg-gray-800 rounded-t-2xl shadow-xl flex flex-col"
            :style="{ 
                transform: isDragging ? `translateY(${touchDelta}px)` : '', 
                transition: isDragging ? 'none' : 'transform 0.2s ease-out' 
            }"
        >
            <!-- Handle Bar (draggable) -->
            <div class="flex justify-center pt-3 pb-2 cursor-grab">
                <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
            </div>

            <!-- Header -->
            <div class="px-5 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Finalizar Pedido</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Total: <span class="font-bold text-orange-600 dark:text-orange-400">${{ cartTotal.toFixed(2) }}</span>
                        </p>
                    </div>
                    <button
                        @click="emit('close')"
                        class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Form -->
            <div class="flex-1 overflow-y-auto p-5 space-y-5">
                <!-- Nombre -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tu nombre *
                    </label>
                    <input
                        v-model="customerName"
                        type="text"
                        placeholder="¿Cómo te llamas?"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                    />
                </div>

                <!-- Teléfono -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Teléfono *
                    </label>
                    <input
                        v-model="customerPhone"
                        type="tel"
                        placeholder="Para confirmar tu pedido"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                    />
                </div>

                <!-- Método de entrega -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        ¿Cómo lo quieres?
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            v-for="method in deliveryMethods"
                            :key="method.id"
                            @click="deliveryMethod = method.id"
                            class="p-3 rounded-xl border-2 text-center transition-all"
                            :class="deliveryMethod === method.id
                                ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300'
                                : 'border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-orange-300'
                            "
                        >
                            <span class="text-lg mb-1 block">
                                {{ method.id === 'pickup' ? '🛍️' : method.id === 'dine_in' ? '🍽️' : '🛵' }}
                            </span>
                            <span class="text-sm font-medium">{{ method.label }}</span>
                        </button>
                    </div>
                </div>

                <!-- Notas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Comentarios (opcional)
                    </label>
                    <textarea
                        v-model="customerNotes"
                        rows="2"
                        placeholder="¿Alguna indicación especial?"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"
                    ></textarea>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-5 bg-gray-50 dark:bg-gray-800/50">
                <button
                    @click="handleConfirm"
                    :disabled="!isFormValid"
                    class="w-full py-4 rounded-2xl font-bold text-lg transition-all duration-200 flex items-center justify-center gap-2"
                    :class="isFormValid
                        ? 'bg-green-600 hover:bg-green-700 text-white shadow-lg active:scale-98'
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                    "
                >
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Enviar por WhatsApp
                </button>
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-3">
                    Te contactaremos para confirmar tu pedido
                </p>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.active\:scale-98:active {
    transform: scale(0.98);
}
</style>
