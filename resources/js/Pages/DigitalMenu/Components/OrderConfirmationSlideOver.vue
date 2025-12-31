<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    sale: Object,
    whatsappUrl: String,
    trackingUrl: String,
    settings: Object,
});

const emit = defineEmits(['close', 'makeAnotherOrder']);

// Touch gesture state
const touchStart = ref({ y: 0 });
const touchDelta = ref(0);
const isDragging = ref(false);

// Format estimated time
const estimatedTimeFormatted = computed(() => {
    if (!props.sale?.estimated_ready_at) return 'En breve';

    const date = new Date(props.sale.estimated_ready_at);
    return date.toLocaleTimeString('es-MX', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
});

// Handle actions
const openWhatsApp = () => {
    if (props.whatsappUrl) {
        window.open(props.whatsappUrl, '_blank');
    }
};

const viewOrder = () => {
    if (props.trackingUrl) {
        window.open(props.trackingUrl, '_blank');
    }
};

const makeAnotherOrder = () => {
    emit('makeAnotherOrder');
    emit('close');
};

// Touch handlers for swipe-to-close
const handleTouchStart = (e) => {
    touchStart.value.y = e.touches[0].clientY;
    touchDelta.value = 0;
    isDragging.value = false;
};

const handleTouchMove = (e) => {
    const deltaY = e.touches[0].clientY - touchStart.value.y;
    if (deltaY > 5) {
        isDragging.value = true;
        touchDelta.value = Math.min(deltaY * 0.8, 250);
    }
};

const handleTouchEnd = () => {
    if (isDragging.value && touchDelta.value > 80) {
        makeAnotherOrder();
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
            @click="makeAnotherOrder"
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
            class="fixed inset-x-0 bottom-0 z-50 max-h-[85vh] bg-white dark:bg-gray-800 rounded-t-2xl shadow-xl flex flex-col"
            :style="{
                transform: isDragging ? `translateY(${touchDelta}px)` : '',
                transition: isDragging ? 'none' : 'transform 0.2s ease-out'
            }"
        >
            <!-- Handle Bar -->
            <div class="flex justify-center pt-3 pb-2 cursor-grab">
                <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
            </div>

            <!-- Success Icon & Message -->
            <div class="px-6 pt-6 pb-4 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    Pedido Confirmado
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Tu pedido ha sido recibido y está siendo preparado
                </p>
            </div>

            <!-- Order Details -->
            <div class="flex-1 overflow-y-auto px-6 pb-6 space-y-4">
                <!-- Order Number Card -->
                <div class="p-4 bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/30 dark:to-red-900/30 rounded-2xl border-2 border-orange-400 dark:border-orange-600">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1 text-center">
                        Número de Pedido
                    </p>
                    <p class="text-3xl font-bold text-orange-600 dark:text-orange-400 text-center tracking-wide">
                        #{{ sale?.sale_number }}
                    </p>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Total -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 mb-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs font-medium">Total</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            ${{ sale?.total?.toFixed(2) }}
                        </p>
                    </div>

                    <!-- Estimated Time -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 mb-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs font-medium">Listo aprox.</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ estimatedTimeFormatted }}
                        </p>
                    </div>
                </div>

                <!-- Restaurant Info -->
                <div class="p-4 bg-blue-50 dark:bg-blue-900/30 rounded-xl border border-blue-200 dark:border-blue-800">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-300">
                                {{ settings?.restaurant_name || 'Restaurante' }}
                            </p>
                            <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">
                                Te notificaremos cuando tu pedido esté listo. Puedes contactarnos por WhatsApp para cualquier consulta.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-6 space-y-3 bg-gray-50 dark:bg-gray-800/50">
                <!-- WhatsApp Button -->
                <button
                    @click="openWhatsApp"
                    class="w-full py-4 px-6 rounded-2xl font-bold text-white bg-green-600 hover:bg-green-700 transition-all duration-200 active:scale-98 flex items-center justify-center gap-3 shadow-lg"
                >
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Contactar por WhatsApp
                </button>

                <!-- View Order Button -->
                <button
                    v-if="trackingUrl"
                    @click="viewOrder"
                    class="w-full py-3 px-6 rounded-xl font-semibold text-orange-600 dark:text-orange-400 bg-orange-100 dark:bg-orange-900/30 hover:bg-orange-200 dark:hover:bg-orange-900/50 transition-all duration-200 active:scale-98 flex items-center justify-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Ver mi pedido
                </button>

                <!-- Make Another Order Button -->
                <button
                    @click="makeAnotherOrder"
                    class="w-full py-3 px-6 rounded-xl font-semibold text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200 active:scale-98 flex items-center justify-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Hacer otro pedido
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
