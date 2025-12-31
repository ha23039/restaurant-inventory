<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: Boolean,
    menuItem: Object, // Para platillos (compatibilidad)
    product: Object, // Para productos genéricos (nuevo)
});

const emit = defineEmits(['close', 'select']);

// Usar menuItem o product
const activeProduct = computed(() => props.product || props.menuItem);

const selectedId = ref(null);
const slideOverRef = ref(null);

// Touch gesture state
const touchStart = ref({ y: 0, x: 0 });
const touchDelta = ref(0);
const isDragging = ref(false);

// Detect grouping attribute
const groupingAttribute = computed(() => {
    if (!activeProduct.value?.variants?.length) return null;
    
    const firstVariant = activeProduct.value.variants[0];
    if (!firstVariant?.attributes) return null;
    
    const attributeKeys = Object.keys(firstVariant.attributes);
    
    for (const key of attributeKeys) {
        const uniqueValues = new Set(
            activeProduct.value.variants.map(v => v.attributes?.[key]).filter(Boolean)
        );
        if (uniqueValues.size > 1) return key;
    }
    
    return null;
});

// Group variants by attribute
const groupedVariants = computed(() => {
    if (!activeProduct.value?.variants?.length) return {};
    
    const groupKey = groupingAttribute.value;
    
    if (!groupKey) return { default: activeProduct.value.variants };
    
    const groups = {};
    activeProduct.value.variants.forEach(variant => {
        const attrValue = variant.attributes?.[groupKey] || 'otros';
        if (!groups[attrValue]) groups[attrValue] = [];
        groups[attrValue].push(variant);
    });
    
    return groups;
});

const getGroupDisplayName = (groupKey) => {
    const names = {
        'maiz': 'Masa de Maíz',
        'arroz': 'Masa de Arroz',
        'default': null,
        'otros': 'Otros'
    };
    return names[groupKey] !== undefined ? names[groupKey] : groupKey.charAt(0).toUpperCase() + groupKey.slice(1);
};

// Determinar el tipo de producto
const getProductType = computed(() => {
    return activeProduct.value?.product_type || (props.menuItem ? 'menu' : 'simple');
});

const handleSelectVariant = (variant) => {
    // Para productos simples, available_quantity puede ser 999 (sin límite) o mayor a 0
    if (variant.available_quantity <= 0 && variant.available_quantity !== 999) return;

    // Solo marcar como seleccionada, no cerrar
    selectedId.value = variant.id;
};

const addToCart = () => {
    if (!selectedId.value) return;

    const variant = activeProduct.value.variants.find(v => v.id === selectedId.value);
    if (!variant) return;

    emit('select', {
        type: 'variant',
        product_type: getProductType.value === 'menu' ? 'variant' : 'simple_variant',
        id: variant.id,
        name: `${activeProduct.value.name} - ${variant.variant_name}`,
        price: parseFloat(variant.price),
        quantity: 1,
        variant_id: variant.id,
        image_path: activeProduct.value.image_path,
        available_quantity: variant.available_quantity,
    });

    // Resetear selección y cerrar
    selectedId.value = null;
    emit('close');
};

// Touch handlers for swipe-to-close
const handleTouchStart = (e) => {
    touchStart.value = { y: e.touches[0].clientY, x: e.touches[0].clientX };
    touchDelta.value = 0;
    isDragging.value = false;
};

const handleTouchMove = (e) => {
    const deltaY = e.touches[0].clientY - touchStart.value.y;
    
    // Start dragging with minimal threshold for natural feel
    if (deltaY > 5) {
        isDragging.value = true;
        // Apply resistance (0.8) for rubber-band effect
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

// Prevent pull-to-refresh when touching the slideover
const preventPullToRefresh = (e) => {
    if (props.show) {
        const scrollTop = slideOverRef.value?.scrollTop || 0;
        if (scrollTop <= 0) {
            // At top, might trigger pull-to-refresh
        }
    }
};

onMounted(() => {
    document.addEventListener('touchmove', preventPullToRefresh, { passive: false });
});

onUnmounted(() => {
    document.removeEventListener('touchmove', preventPullToRefresh);
});
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
            class="fixed inset-0 bg-black/50 z-40"
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
            ref="slideOverRef"
            @touchstart="handleTouchStart"
            @touchmove.passive="handleTouchMove"
            @touchend="handleTouchEnd"
            class="fixed inset-x-0 bottom-0 z-50 max-h-[85vh] bg-white dark:bg-gray-800 rounded-t-2xl shadow-xl flex flex-col"
            :style="{ 
                transform: isDragging ? `translateY(${touchDelta}px)` : '', 
                transition: isDragging ? 'none' : 'transform 0.2s ease-out' 
            }"
        >
            <!-- Handle Bar (draggable indicator) -->
            <div class="flex justify-center pt-3 pb-2 cursor-grab">
                <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
            </div>

            <!-- Header -->
            <div class="px-5 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-start gap-4">
                    <div class="w-16 h-16 flex-shrink-0 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700">
                        <img
                            v-if="activeProduct?.image_path"
                            :src="activeProduct.image_path"
                            :alt="activeProduct?.name"
                            class="w-full h-full object-cover"
                        >
                        <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-400 to-orange-600">
                            <svg class="w-6 h-6 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ activeProduct?.name }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Selecciona una opción
                        </p>
                    </div>

                    <button
                        @click="emit('close')"
                        class="flex-shrink-0 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Variants List (Grouped) -->
            <div class="flex-1 overflow-y-auto p-4 overscroll-contain">
                <template v-for="(variants, groupKey) in groupedVariants" :key="groupKey">
                    <h3 
                        v-if="getGroupDisplayName(groupKey)"
                        class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 mt-3 first:mt-0"
                    >
                        {{ getGroupDisplayName(groupKey) }}
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <button
                            v-for="variant in variants"
                            :key="variant.id"
                            @click="handleSelectVariant(variant)"
                            :disabled="variant.available_quantity <= 0 && variant.available_quantity !== 999"
                            class="p-3 rounded-xl border-2 text-left transition-all duration-150"
                            :class="[
                                selectedId === variant.id
                                    ? 'border-green-500 bg-green-50 dark:bg-green-900/30'
                                    : (variant.available_quantity > 0 || variant.available_quantity === 999)
                                        ? 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 hover:border-orange-500 dark:hover:border-orange-400 active:scale-95'
                                        : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 opacity-50 cursor-not-allowed'
                            ]"
                        >
                            <div class="flex flex-col">
                                <span class="font-semibold text-sm text-gray-900 dark:text-white line-clamp-1">
                                    {{ variant.variant_name }}
                                </span>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-base font-bold text-orange-600 dark:text-orange-400">
                                        ${{ parseFloat(variant.price).toFixed(2) }}
                                    </span>
                                    <svg 
                                        v-if="selectedId === variant.id" 
                                        class="w-5 h-5 text-green-600 dark:text-green-400" 
                                        fill="currentColor" 
                                        viewBox="0 0 20 20"
                                    >
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span 
                                        v-else-if="variant.available_quantity <= 0 && variant.available_quantity !== 999"
                                        class="text-xs text-red-500"
                                    >
                                        Agotado
                                    </span>
                                </div>
                            </div>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Botón Agregar al Carrito -->
            <div class="sticky bottom-0 left-0 right-0 p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <button
                    @click="addToCart"
                    :disabled="!selectedId"
                    class="w-full py-3 px-4 rounded-xl font-semibold transition-all"
                    :class="selectedId
                        ? 'bg-orange-500 text-white hover:bg-orange-600 active:scale-95'
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'"
                >
                    <span v-if="selectedId" class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Agregar al carrito
                    </span>
                    <span v-else>Selecciona una opción</span>
                </button>
            </div>

            <!-- Swipe hint -->
            <div v-if="isDragging" class="absolute inset-x-0 top-8 text-center">
                <span class="text-xs text-gray-400">Suelta para cerrar</span>
            </div>
        </div>
    </Transition>
</template>
