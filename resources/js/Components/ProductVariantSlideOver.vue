<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    menuItem: {
        type: Object,
        default: null
    },
    cart: {
        type: Array,
        default: () => []
    },
    isSimpleProduct: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close', 'select', 'update-variants']);

// Refs
const slideOverRef = ref(null);
const contentRef = ref(null);

// Responsive detection
const isDesktop = ref(true);
const checkScreenSize = () => {
    isDesktop.value = window.matchMedia('(min-width: 1024px)').matches;
};

onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize);
});

// Selection state: { variantId: { quantity: number, variant: object } }
const selectedVariants = ref({});
const justAdded = ref(false);

// Touch gesture state (mobile only)
const touchStart = ref({ y: 0, x: 0 });
const touchDelta = ref(0);
const isDragging = ref(false);
const isAtTop = ref(true);

// Grouping logic
const groupingAttribute = computed(() => {
    if (!props.menuItem?.variants?.length) return null;

    const firstVariant = props.menuItem.variants[0];
    if (!firstVariant?.attributes) return null;

    const attributeKeys = Object.keys(firstVariant.attributes);

    for (const key of attributeKeys) {
        const uniqueValues = new Set(
            props.menuItem.variants.map(v => v.attributes?.[key]).filter(Boolean)
        );
        if (uniqueValues.size > 1) return key;
    }

    return null;
});

const groupedVariants = computed(() => {
    if (!props.menuItem?.variants?.length) return {};

    const groupKey = groupingAttribute.value;

    if (!groupKey) return { default: props.menuItem.variants };

    const groups = {};
    props.menuItem.variants.forEach(variant => {
        const attrValue = variant.attributes?.[groupKey] || 'otros';
        if (!groups[attrValue]) groups[attrValue] = [];
        groups[attrValue].push(variant);
    });

    return groups;
});

const getGroupDisplayName = (groupKey) => {
    const names = {
        'maiz': 'Masa de Maiz',
        'arroz': 'Masa de Arroz',
        'default': null,
        'otros': 'Otros'
    };
    return names[groupKey] !== undefined ? names[groupKey] : groupKey.charAt(0).toUpperCase() + groupKey.slice(1);
};

const hasVariants = computed(() => props.menuItem?.variants?.length > 0);

// Toggle variant selection
const toggleVariant = (variant) => {
    if (variant.available_quantity <= 0) return;

    if (selectedVariants.value[variant.id]) {
        delete selectedVariants.value[variant.id];
    } else {
        selectedVariants.value[variant.id] = {
            quantity: 1,
            variant: variant
        };
    }
};

// Quantity controls
const incrementQuantity = (variantId) => {
    if (!selectedVariants.value[variantId]) return;

    const variant = selectedVariants.value[variantId].variant;
    const maxQuantity = variant.available_quantity === 999 ? 99 : variant.available_quantity;

    if (selectedVariants.value[variantId].quantity < maxQuantity) {
        selectedVariants.value[variantId].quantity++;
    }
};

const decrementQuantity = (variantId) => {
    if (!selectedVariants.value[variantId]) return;

    if (selectedVariants.value[variantId].quantity > 1) {
        selectedVariants.value[variantId].quantity--;
    }
};

// Computed totals
const totalItems = computed(() => {
    return Object.values(selectedVariants.value).reduce((sum, item) => sum + item.quantity, 0);
});

const totalPrice = computed(() => {
    return Object.values(selectedVariants.value).reduce((sum, item) => {
        return sum + (parseFloat(item.variant.price) * item.quantity);
    }, 0);
});

const hasSelection = computed(() => Object.keys(selectedVariants.value).length > 0);

const addToCart = () => {
    if (!hasSelection.value || justAdded.value) return;

    justAdded.value = true;

    // Determine product type based on isSimpleProduct prop
    const productType = props.isSimpleProduct ? 'simple_variant' : 'variant';
    const productIdField = props.isSimpleProduct ? 'simple_product_id' : 'menu_item_id';

    const variantsToAdd = Object.values(selectedVariants.value).map(({ variant, quantity }) => ({
        type: productType,
        product_type: productType,
        id: variant.id,
        name: `${props.menuItem.name} - ${variant.variant_name}`,
        price: parseFloat(variant.price),
        quantity: quantity,
        variant_id: variant.id,
        [productIdField]: props.menuItem.id,
        available_quantity: variant.available_quantity,
    }));

    emit('update-variants', {
        productId: props.menuItem.id,
        productName: props.menuItem.name,
        variants: variantsToAdd,
        isSimpleProduct: props.isSimpleProduct
    });

    setTimeout(() => {
        selectedVariants.value = {};
        justAdded.value = false;
        emit('close');
    }, 400);
};

// Load cart quantities when opening
const loadCartQuantities = () => {
    if (!props.cart || !props.menuItem) return;

    selectedVariants.value = {};

    props.cart.forEach(cartItem => {
        if (cartItem.variant_id && props.menuItem.variants) {
            const variant = props.menuItem.variants.find(v => v.id === cartItem.variant_id);
            if (variant) {
                selectedVariants.value[variant.id] = {
                    quantity: cartItem.quantity,
                    variant: variant
                };
            }
        }
    });
};

// Watch show state
watch(() => props.show, (newValue, oldValue) => {
    if (newValue && !oldValue) {
        loadCartQuantities();
        isAtTop.value = true;
    } else if (!newValue) {
        selectedVariants.value = {};
        justAdded.value = false;
        touchDelta.value = 0;
        isDragging.value = false;
    }
});

// Scroll detection
const handleScroll = () => {
    if (!contentRef.value) return;
    isAtTop.value = contentRef.value.scrollTop <= 0;
};

// Touch handlers (mobile swipe-to-close)
const handleTouchStart = (e) => {
    if (isDesktop.value) return;
    const touch = e.touches[0];
    touchStart.value = { y: touch.clientY, x: touch.clientX };
    touchDelta.value = 0;
    isDragging.value = false;
};

const handleTouchMove = (e) => {
    if (isDesktop.value) return;

    const deltaY = e.touches[0].clientY - touchStart.value.y;
    const deltaX = Math.abs(e.touches[0].clientX - touchStart.value.x);

    if (!isAtTop.value || deltaX > 20) return;

    if (deltaY > 5) {
        isDragging.value = true;
        touchDelta.value = Math.min(deltaY * 0.8, 250);
        e.preventDefault();
    }
};

const handleTouchEnd = () => {
    if (isDesktop.value) return;

    if (isDragging.value && touchDelta.value > 100 && isAtTop.value) {
        emit('close');
    }
    touchDelta.value = 0;
    isDragging.value = false;
};

const handleClose = () => {
    emit('close');
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
            @click="handleClose"
            class="fixed inset-0 bg-black/50 z-40"
        />
    </Transition>

    <!-- Desktop: Horizontal SlideOver (right side) -->
    <Transition
        v-if="isDesktop"
        enter-active-class="transition-transform duration-300 ease-out"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition-transform duration-200 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="show"
            class="fixed inset-y-0 right-0 z-50 w-full max-w-xl bg-white dark:bg-gray-800 shadow-2xl flex flex-col"
        >
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ menuItem?.name || 'Seleccionar Variantes' }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Selecciona las opciones que deseas
                    </p>
                </div>
                <button
                    @click="handleClose"
                    class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div ref="contentRef" class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- No variants -->
                <div v-if="!hasVariants" class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">No hay variantes disponibles</p>
                </div>

                <!-- Variants grouped -->
                <template v-else v-for="(variants, groupKey) in groupedVariants" :key="groupKey">
                    <div>
                        <h3
                            v-if="getGroupDisplayName(groupKey)"
                            class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center"
                        >
                            <svg class="w-4 h-4 mr-2 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z" />
                            </svg>
                            {{ getGroupDisplayName(groupKey) }}
                        </h3>

                        <div class="grid grid-cols-2 gap-3">
                            <div
                                v-for="variant in variants"
                                :key="variant.id"
                                @click="toggleVariant(variant)"
                                class="border-2 rounded-xl p-4 transition-all duration-200 cursor-pointer"
                                :class="[
                                    selectedVariants[variant.id]
                                        ? 'border-green-500 bg-green-50 dark:bg-green-900/30 shadow-md'
                                        : variant.available_quantity > 0
                                            ? 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 hover:border-blue-500 hover:shadow-lg'
                                            : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 opacity-50 cursor-not-allowed'
                                ]"
                            >
                                <!-- Header -->
                                <div class="flex items-start gap-3 mb-2">
                                    <div
                                        class="w-5 h-5 rounded-md border-2 flex items-center justify-center flex-shrink-0 mt-0.5 transition-all"
                                        :class="[
                                            selectedVariants[variant.id]
                                                ? 'bg-green-500 border-green-500'
                                                : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700'
                                        ]"
                                    >
                                        <svg
                                            v-if="selectedVariants[variant.id]"
                                            class="w-3 h-3 text-white"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-sm text-gray-900 dark:text-white line-clamp-2">
                                            {{ variant.variant_name }}
                                        </h4>
                                        <div class="flex items-center justify-between mt-1">
                                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                                ${{ parseFloat(variant.price).toFixed(2) }}
                                            </span>
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full"
                                                :class="[
                                                    variant.available_quantity > 10
                                                        ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'
                                                        : variant.available_quantity > 0
                                                            ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300'
                                                            : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'
                                                ]"
                                            >
                                                {{ variant.available_quantity > 0 ? variant.available_quantity : 'Agotado' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quantity controls -->
                                <Transition
                                    enter-active-class="transition-all duration-300"
                                    enter-from-class="opacity-0 scale-90"
                                    enter-to-class="opacity-100 scale-100"
                                    leave-active-class="transition-all duration-200"
                                    leave-from-class="opacity-100 scale-100"
                                    leave-to-class="opacity-0 scale-90"
                                >
                                    <div
                                        v-if="selectedVariants[variant.id]"
                                        class="pt-3 mt-2 border-t border-green-200 dark:border-green-700"
                                        @click.stop
                                    >
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Cantidad:</span>
                                            <div class="flex items-center gap-2">
                                                <button
                                                    @click="decrementQuantity(variant.id)"
                                                    :disabled="selectedVariants[variant.id].quantity <= 1"
                                                    class="w-8 h-8 rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 flex items-center justify-center hover:border-blue-500 active:scale-90 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                    </svg>
                                                </button>

                                                <span class="w-8 text-center text-lg font-bold text-gray-900 dark:text-white tabular-nums">
                                                    {{ selectedVariants[variant.id].quantity }}
                                                </span>

                                                <button
                                                    @click="incrementQuantity(variant.id)"
                                                    class="w-8 h-8 rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 flex items-center justify-center hover:border-blue-500 active:scale-90 transition-all"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-800/50">
                <button
                    @click="addToCart"
                    :disabled="!hasSelection || justAdded"
                    class="w-full py-3 px-4 rounded-xl font-semibold transition-all relative overflow-hidden"
                    :class="[
                        hasSelection && !justAdded
                            ? 'bg-blue-600 text-white hover:bg-blue-700 active:scale-98 shadow-lg'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed',
                        justAdded ? 'bg-green-500 scale-95' : ''
                    ]"
                >
                    <Transition
                        enter-active-class="transition-all duration-300"
                        leave-active-class="transition-all duration-200"
                        enter-from-class="opacity-0 -translate-y-2"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 translate-y-2"
                        mode="out-in"
                    >
                        <div v-if="justAdded" class="flex items-center justify-center gap-2" key="added">
                            <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Agregado al carrito</span>
                        </div>
                        <div v-else-if="hasSelection" class="flex items-center justify-between" key="add">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Agregar al carrito</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm opacity-80">{{ totalItems }} {{ totalItems === 1 ? 'item' : 'items' }}</span>
                                <span class="text-lg font-bold">${{ totalPrice.toFixed(2) }}</span>
                            </div>
                        </div>
                        <span v-else key="select">Selecciona las variantes</span>
                    </Transition>
                </button>
            </div>
        </div>
    </Transition>

    <!-- Mobile: Bottom Sheet -->
    <Transition
        v-else
        enter-active-class="transition-transform duration-300 ease-out"
        enter-from-class="translate-y-full"
        enter-to-class="translate-y-0"
        leave-active-class="transition-transform duration-200 ease-in"
        leave-from-class="translate-y-0"
        leave-to-class="translate-y-full"
    >
        <div
            v-if="show"
            ref="slideOverRef"
            @touchstart="handleTouchStart"
            @touchmove="handleTouchMove"
            @touchend="handleTouchEnd"
            class="fixed inset-x-0 bottom-0 z-50 max-h-[85vh] bg-white dark:bg-gray-800 rounded-t-2xl shadow-xl flex flex-col"
            :style="{
                transform: isDragging ? `translateY(${touchDelta}px)` : '',
                transition: isDragging ? 'none' : 'transform 0.2s ease-out'
            }"
        >
            <!-- Handle Bar -->
            <div class="flex justify-center pt-3 pb-2 cursor-grab">
                <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full" />
            </div>

            <!-- Swipe hint -->
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="isDragging && touchDelta > 50" class="absolute inset-x-0 top-8 text-center pointer-events-none">
                    <span class="text-xs text-gray-400 dark:text-gray-500 bg-white/80 dark:bg-gray-800/80 px-3 py-1 rounded-full">
                        {{ touchDelta > 100 ? 'Suelta para cerrar' : 'Arrastra para cerrar' }}
                    </span>
                </div>
            </Transition>

            <!-- Header -->
            <div class="px-5 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-start gap-4">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ menuItem?.name || 'Seleccionar Variantes' }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Selecciona tus opciones
                        </p>
                    </div>
                    <button
                        @click="handleClose"
                        class="flex-shrink-0 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div
                ref="contentRef"
                @scroll="handleScroll"
                class="flex-1 overflow-y-auto p-4 overscroll-contain space-y-4"
            >
                <!-- No variants -->
                <div v-if="!hasVariants" class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No hay variantes disponibles</p>
                </div>

                <!-- Variants grouped -->
                <template v-else v-for="(variants, groupKey) in groupedVariants" :key="groupKey">
                    <div>
                        <h3
                            v-if="getGroupDisplayName(groupKey)"
                            class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3"
                        >
                            {{ getGroupDisplayName(groupKey) }}
                        </h3>

                        <div class="grid grid-cols-2 gap-2">
                            <div
                                v-for="variant in variants"
                                :key="variant.id"
                                @click="toggleVariant(variant)"
                                class="border-2 rounded-xl p-3 transition-all duration-200 cursor-pointer active:scale-95"
                                :class="[
                                    selectedVariants[variant.id]
                                        ? 'border-green-500 bg-green-50 dark:bg-green-900/30 shadow-md'
                                        : variant.available_quantity > 0
                                            ? 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 hover:border-blue-400'
                                            : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 opacity-50 cursor-not-allowed'
                                ]"
                            >
                                <!-- Header -->
                                <div class="flex items-start gap-2 mb-2">
                                    <div
                                        class="w-5 h-5 rounded-md border-2 flex items-center justify-center flex-shrink-0 mt-0.5 transition-all"
                                        :class="[
                                            selectedVariants[variant.id]
                                                ? 'bg-green-500 border-green-500'
                                                : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700'
                                        ]"
                                    >
                                        <svg
                                            v-if="selectedVariants[variant.id]"
                                            class="w-3 h-3 text-white"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-sm text-gray-900 dark:text-white line-clamp-2 leading-snug">
                                            {{ variant.variant_name }}
                                        </h4>
                                        <p class="text-base font-bold text-blue-600 dark:text-blue-400 mt-0.5">
                                            ${{ parseFloat(variant.price).toFixed(2) }}
                                        </p>
                                        <p
                                            v-if="variant.available_quantity <= 0"
                                            class="text-xs text-red-500 font-medium mt-1"
                                        >
                                            Agotado
                                        </p>
                                    </div>
                                </div>

                                <!-- Quantity controls -->
                                <Transition
                                    enter-active-class="transition-all duration-300"
                                    enter-from-class="opacity-0 scale-90"
                                    enter-to-class="opacity-100 scale-100"
                                    leave-active-class="transition-all duration-200"
                                    leave-from-class="opacity-100 scale-100"
                                    leave-to-class="opacity-0 scale-90"
                                >
                                    <div
                                        v-if="selectedVariants[variant.id]"
                                        class="pt-2 border-t border-green-200 dark:border-green-700"
                                        @click.stop
                                    >
                                        <div class="flex items-center justify-between gap-1">
                                            <button
                                                @click="decrementQuantity(variant.id)"
                                                :disabled="selectedVariants[variant.id].quantity <= 1"
                                                class="w-7 h-7 rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 flex items-center justify-center transition-all hover:border-blue-500 active:scale-90 disabled:opacity-30 disabled:cursor-not-allowed"
                                            >
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                </svg>
                                            </button>

                                            <span class="text-lg font-bold text-gray-900 dark:text-white tabular-nums">
                                                {{ selectedVariants[variant.id].quantity }}
                                            </span>

                                            <button
                                                @click="incrementQuantity(variant.id)"
                                                class="w-7 h-7 rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 flex items-center justify-center transition-all hover:border-blue-500 active:scale-90"
                                            >
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="sticky bottom-0 left-0 right-0 p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <button
                    @click="addToCart"
                    :disabled="!hasSelection || justAdded"
                    class="w-full py-4 px-4 rounded-xl font-semibold transition-all relative overflow-hidden"
                    :class="[
                        hasSelection && !justAdded
                            ? 'bg-blue-600 text-white hover:bg-blue-700 active:scale-95 shadow-lg hover:shadow-xl'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed',
                        justAdded ? 'bg-green-500 scale-95' : ''
                    ]"
                >
                    <Transition
                        enter-active-class="transition-all duration-300"
                        leave-active-class="transition-all duration-200"
                        enter-from-class="opacity-0 -translate-y-2"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 translate-y-2"
                        mode="out-in"
                    >
                        <div v-if="justAdded" class="flex items-center justify-center gap-2" key="added">
                            <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Agregado</span>
                        </div>
                        <div v-else-if="hasSelection" class="flex items-center justify-between" key="add">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Agregar al carrito</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm opacity-90">({{ totalItems }} {{ totalItems === 1 ? 'item' : 'items' }})</span>
                                <span class="text-lg font-bold">${{ totalPrice.toFixed(2) }}</span>
                            </div>
                        </div>
                        <span v-else key="select">Selecciona las variantes</span>
                    </Transition>
                </button>
            </div>
        </div>
    </Transition>
</template>
