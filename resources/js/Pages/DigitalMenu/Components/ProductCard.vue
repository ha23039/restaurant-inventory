<script setup>
import { computed } from 'vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    type: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['add-to-cart', 'select-variant', 'select-combo']);

const isCombo = computed(() => props.type === 'combo');
const price = computed(() => props.product.price || props.product.sale_price || props.product.base_price);
const hasImage = computed(() => props.product.image_path && props.product.image_path !== '');

// Verificar si tiene elecciones (para combos)
const hasChoices = computed(() => {
    if (!isCombo.value) return false;
    return props.product.components?.some(c => c.type === 'choice') || false;
});

// Contar componentes del combo
const comboComponentsCount = computed(() => {
    if (!isCombo.value) return 0;
    return props.product.components?.length || 0;
});

// Verificar disponibilidad - productos con variantes están disponibles si tienen variantes
const hasVariants = computed(() => {
    if (isCombo.value) return false;
    return (props.product.has_variants && props.product.variants?.length > 0) ||
           (props.product.allows_variants && props.product.variants?.length > 0);
});

const isAvailable = computed(() => {
    if (isCombo.value) return true; // Combos siempre disponibles (ya filtrados en backend)
    if (hasVariants.value) return true; // Productos con variantes siempre están disponibles para ver
    return props.product.available_quantity > 0;
});

const stockStatus = computed(() => {
    if (isCombo.value) return null; // No mostrar stock para combos
    if (hasVariants.value) return null; // No mostrar stock para productos con variantes
    if (!isAvailable.value) return { text: 'Agotado', color: 'red' };
    
    const qty = props.product.available_quantity;
    // Stock muy bajo (1-3): mostrar cantidad exacta en rojo
    if (qty <= 3) return { text: `¡Solo ${qty === 1 ? 'queda 1' : `quedan ${qty}`}!`, color: 'red' };
    // Stock bajo (4-5): mostrar cantidad en amarillo
    if (qty <= 5) return { text: `Quedan ${qty}`, color: 'yellow' };
    
    return null;
});

const handleClick = () => {
    if (!isAvailable.value) return;

    // Si es un combo, siempre abrir el selector
    if (isCombo.value) {
        emit('select-combo', props.product);
        return;
    }

    if (hasVariants.value) {
        emit('select-variant', props.product);
    } else {
        emit('add-to-cart', {
            type: props.type,
            product_type: props.type,
            id: props.product.id,
            name: props.product.name,
            price: price.value,
            quantity: 1,
            image_path: props.product.image_path,
            available_quantity: props.product.available_quantity,
        });
    }
};
</script>

<template>
    <!-- Toda la card es clickeable -->
    <button
        @click="handleClick"
        :disabled="!isAvailable"
        class="w-full text-left bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden border border-gray-100 dark:border-gray-700 active:scale-98"
        :class="isAvailable ? 'cursor-pointer hover:border-brand dark:hover:border-brand' : 'opacity-60 grayscale cursor-not-allowed'"
    >
        <!-- Product Image -->
        <div class="relative aspect-[4/3] overflow-hidden bg-gray-100 dark:bg-gray-700">
            <img
                v-if="hasImage"
                :src="product.image_path"
                :alt="product.name"
                class="w-full h-full object-cover"
                loading="lazy"
            >
            <div
                v-else
                class="w-full h-full flex items-center justify-center product-placeholder-gradient"
            >
                <svg class="w-12 h-12 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>

            <!-- Stock Badge -->
            <div v-if="stockStatus" class="absolute top-2 right-2">
                <span
                    :class="[
                        'inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold',
                        stockStatus.color === 'red' && 'bg-red-500 text-white',
                        stockStatus.color === 'yellow' && 'bg-yellow-500 text-white',
                    ]"
                >
                    {{ stockStatus.text }}
                </span>
            </div>

            <!-- Combo Badge -->
            <div v-if="isCombo" class="absolute top-2 left-2">
                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-purple-600 text-white">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Combo
                </span>
            </div>

            <!-- Variants Badge -->
            <div v-else-if="hasVariants" class="absolute top-2 left-2">
                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-purple-500 text-white">
                    {{ product.variants.length }} opciones
                </span>
            </div>
        </div>

        <!-- Product Info -->
        <div class="p-4">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2 mb-1">
                {{ product.name }}
            </h3>
            
            <p
                v-if="product.description"
                class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-2"
            >
                {{ product.description }}
            </p>

            <div class="flex items-center justify-between">
                <span class="text-lg font-black brand-text">
                    ${{ parseFloat(price).toFixed(2) }}
                </span>
                <span
                    v-if="isAvailable"
                    class="text-xs font-medium text-white px-3 py-1.5 rounded-lg brand-button"
                >
                    {{ isCombo ? 'Personalizar' : hasVariants ? 'Ver opciones' : '+ Agregar' }}
                </span>
            </div>
        </div>
    </button>
</template>

<style scoped>
.active\:scale-98:active {
    transform: scale(0.98);
}

/* Brand colors using CSS custom properties */
.product-placeholder-gradient {
    background: linear-gradient(to bottom right, var(--brand-primary, #f97316), var(--brand-secondary, #ea580c));
}

.brand-text {
    color: var(--brand-primary, #f97316);
}

.dark .brand-text {
    color: var(--brand-accent, #fb923c);
}

.brand-button {
    background-color: var(--brand-primary, #f97316);
}

.hover\:border-brand:hover {
    border-color: var(--brand-accent, #fb923c);
}
</style>
