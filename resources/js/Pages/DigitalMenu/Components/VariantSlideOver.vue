<script setup>
import { ref } from 'vue';

const props = defineProps({
    show: Boolean,
    menuItem: Object,
});

const emit = defineEmits(['close', 'select']);

// Track selected for visual feedback
const selectedId = ref(null);

const handleSelectVariant = (variant) => {
    if (variant.available_quantity <= 0) return;
    
    selectedId.value = variant.id;
    
    emit('select', {
        type: 'variant',
        id: variant.id,
        name: `${props.menuItem.name} - ${variant.variant_name}`,
        price: parseFloat(variant.price),
        quantity: 1,
        variant_id: variant.id,
        image_path: props.menuItem.image_path,
        available_quantity: variant.available_quantity,
    });
    
    // Reset selection and close after animation
    setTimeout(() => {
        selectedId.value = null;
        emit('close');
    }, 300);
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
        enter-from-class="translate-y-full"
        enter-to-class="translate-y-0"
        leave-active-class="transition-transform duration-300"
        leave-from-class="translate-y-0"
        leave-to-class="translate-y-full"
    >
        <div
            v-if="show"
            class="fixed inset-x-0 bottom-0 z-50 max-h-[85vh] bg-white dark:bg-gray-800 rounded-t-2xl shadow-xl flex flex-col"
        >
            <!-- Handle Bar -->
            <div class="flex justify-center pt-3 pb-2">
                <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
            </div>

            <!-- Header -->
            <div class="px-5 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-start gap-4">
                    <!-- Product Image -->
                    <div class="w-20 h-20 flex-shrink-0 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700">
                        <img
                            v-if="menuItem?.image_path"
                            :src="menuItem.image_path"
                            :alt="menuItem?.name"
                            class="w-full h-full object-cover"
                        >
                        <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-400 to-orange-600">
                            <svg class="w-8 h-8 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white line-clamp-2">
                            {{ menuItem?.name }}
                        </h2>
                        <p v-if="menuItem?.description" class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mt-1">
                            {{ menuItem.description }}
                        </p>
                    </div>

                    <!-- Close Button -->
                    <button
                        @click="emit('close')"
                        class="flex-shrink-0 p-2 -mt-1 -mr-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Variants List -->
            <div class="flex-1 overflow-y-auto p-5">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    Selecciona una opción:
                </p>
                
                <div class="space-y-3">
                    <button
                        v-for="variant in menuItem?.variants"
                        :key="variant.id"
                        @click="handleSelectVariant(variant)"
                        :disabled="variant.available_quantity <= 0"
                        class="w-full p-4 rounded-xl border-2 text-left transition-all duration-200"
                        :class="[
                            selectedId === variant.id
                                ? 'border-green-500 bg-green-50 dark:bg-green-900/30 scale-98'
                                : variant.available_quantity > 0
                                    ? 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 hover:border-orange-500 dark:hover:border-orange-400 active:scale-98'
                                    : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 opacity-50 cursor-not-allowed'
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-900 dark:text-white">
                                    {{ variant.variant_name }}
                                </h4>
                                <p v-if="variant.description" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ variant.description }}
                                </p>
                            </div>
                            
                            <div class="flex items-center gap-3 ml-3">
                                <!-- Stock indicator -->
                                <span 
                                    v-if="variant.available_quantity > 0"
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ variant.available_quantity }} disp.
                                </span>
                                <span 
                                    v-else
                                    class="text-xs text-red-500 dark:text-red-400 font-medium"
                                >
                                    Agotado
                                </span>

                                <!-- Price -->
                                <span class="text-lg font-bold text-orange-600 dark:text-orange-400">
                                    ${{ parseFloat(variant.price).toFixed(2) }}
                                </span>

                                <!-- Checkmark when selected -->
                                <svg 
                                    v-if="selectedId === variant.id" 
                                    class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0" 
                                    fill="currentColor" 
                                    viewBox="0 0 20 20"
                                >
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.scale-98 {
    transform: scale(0.98);
}
</style>
