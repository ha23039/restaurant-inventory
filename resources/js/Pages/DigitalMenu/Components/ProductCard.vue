<script setup>
import { computed } from 'vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    type: {
        type: String,
        required: true, // 'menu' or 'simple'
    },
});

const emit = defineEmits(['add-to-cart', 'select-variant']);

const price = computed(() => {
    return props.type === 'menu' ? props.product.price : props.product.sale_price;
});

const hasImage = computed(() => {
    return props.product.image_path && props.product.image_path !== '';
});

const isAvailable = computed(() => {
    return props.product.available_quantity > 0;
});

const handleAddToCart = () => {
    if (!isAvailable.value) return;

    if (props.product.has_variants) {
        emit('select-variant', props.product);
    } else {
        emit('add-to-cart', {
            type: props.type,
            id: props.product.id,
            name: props.product.name,
            price: price.value,
            quantity: 1,
            image_path: props.product.image_path,
        });
    }
};
</script>

<template>
    <div
        class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden border border-gray-200 dark:border-gray-700"
        :class="{ 'opacity-60': !isAvailable }"
    >
        <!-- Product Image -->
        <div class="aspect-w-4 aspect-h-3 bg-gray-100 dark:bg-gray-700 overflow-hidden">
            <img
                v-if="hasImage"
                :src="product.image_path"
                :alt="product.name"
                class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-200"
            >
            <div
                v-else
                class="w-full h-48 flex items-center justify-center bg-gradient-to-br from-orange-400 to-orange-600"
            >
                <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>

            <!-- Stock Badge -->
            <div class="absolute top-2 right-2">
                <span
                    v-if="!isAvailable"
                    class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-red-500 text-white"
                >
                    Agotado
                </span>
                <span
                    v-else-if="product.available_quantity <= 5"
                    class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-yellow-500 text-white"
                >
                    Pocas unidades
                </span>
            </div>

            <!-- Variants Badge -->
            <div v-if="product.has_variants" class="absolute top-2 left-2">
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-500 text-white">
                    {{ product.variants.length }} variantes
                </span>
            </div>
        </div>

        <!-- Product Info -->
        <div class="p-4">
            <div class="flex items-start justify-between mb-2">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white line-clamp-2 flex-1">
                    {{ product.name }}
                </h3>
            </div>

            <p
                v-if="product.description"
                class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-3"
            >
                {{ product.description }}
            </p>

            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                        ${{ parseFloat(price).toFixed(2) }}
                    </p>
                    <p v-if="isAvailable" class="text-xs text-gray-500 dark:text-gray-400">
                        {{ product.available_quantity }} disponibles
                    </p>
                </div>

                <button
                    @click="handleAddToCart"
                    :disabled="!isAvailable"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
                    :class="isAvailable
                        ? 'bg-orange-600 hover:bg-orange-700 text-white shadow-sm hover:shadow-md active:scale-95'
                        : 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed'
                    "
                >
                    <svg v-if="product.has_variants" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <svg v-else class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ product.has_variants ? 'Ver' : 'Agregar' }}
                </button>
            </div>
        </div>
    </div>
</template>
