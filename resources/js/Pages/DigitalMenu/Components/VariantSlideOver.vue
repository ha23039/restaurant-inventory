<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useToast } from 'vue-toastification';

const props = defineProps({
    show: Boolean,
    menuItem: Object, // Para platillos (compatibilidad)
    product: Object, // Para productos genéricos (nuevo)
    cart: Array, // Para pre-cargar cantidades del carrito
});

const emit = defineEmits(['close', 'select', 'update-variants']);

// Usar menuItem o product
const activeProduct = computed(() => props.product || props.menuItem);

const toast = useToast();
const slideOverRef = ref(null);
const contentRef = ref(null); // Referencia al contenedor scrolleable
const justAdded = ref(false);

// Cambio: ahora almacenamos múltiples variantes seleccionadas con sus cantidades
// Estructura: { variantId: { quantity: number, variant: object } }
const selectedVariants = ref({});

// Touch gesture state
const touchStart = ref({ y: 0, x: 0 });
const touchDelta = ref(0);
const isDragging = ref(false);
const isAtTop = ref(true); // Indica si el scroll está en el tope

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

// Toggle de selección de variante (checkbox)
const toggleVariant = (variant) => {
    // No permitir seleccionar si está agotado
    if (variant.available_quantity <= 0 && variant.available_quantity !== 999) return;

    if (selectedVariants.value[variant.id]) {
        // Si ya está seleccionada, quitarla
        delete selectedVariants.value[variant.id];
    } else {
        // Si no está seleccionada, agregarla con cantidad 1
        selectedVariants.value[variant.id] = {
            quantity: 1,
            variant: variant
        };
    }
};

// Incrementar cantidad de una variante específica
const incrementQuantity = (variantId) => {
    if (!selectedVariants.value[variantId]) return;

    const variant = selectedVariants.value[variantId].variant;
    const maxQuantity = variant.available_quantity === 999 ? 99 : variant.available_quantity;

    if (selectedVariants.value[variantId].quantity < maxQuantity) {
        selectedVariants.value[variantId].quantity++;
    }
};

// Decrementar cantidad de una variante específica
const decrementQuantity = (variantId) => {
    if (!selectedVariants.value[variantId]) return;

    if (selectedVariants.value[variantId].quantity > 1) {
        selectedVariants.value[variantId].quantity--;
    }
};

// Calcular totales
const totalItems = computed(() => {
    return Object.values(selectedVariants.value).reduce((sum, item) => sum + item.quantity, 0);
});

const totalPrice = computed(() => {
    return Object.values(selectedVariants.value).reduce((sum, item) => {
        return sum + (parseFloat(item.variant.price) * item.quantity);
    }, 0);
});

const hasSelection = computed(() => {
    return Object.keys(selectedVariants.value).length > 0;
});

// Agregar/Actualizar variantes en el carrito (MODO EDITAR)
const addToCart = () => {
    if (!hasSelection.value || justAdded.value) return;

    // Activar animación de "añadido"
    justAdded.value = true;

    // Preparar array de variantes para reemplazar
    const variantsToUpdate = Object.values(selectedVariants.value).map(({ variant, quantity }) => ({
        type: 'variant',
        product_type: getProductType.value === 'menu' ? 'variant' : 'simple_variant',
        id: variant.id,
        name: `${activeProduct.value.name} - ${variant.variant_name}`,
        price: parseFloat(variant.price),
        quantity: quantity,
        variant_id: variant.id,
        image_path: activeProduct.value.image_path,
        available_quantity: variant.available_quantity,
    }));

    // Emitir evento de actualización con el producto base y las variantes
    emit('update-variants', {
        productId: activeProduct.value.id,
        productName: activeProduct.value.name,
        variants: variantsToUpdate
    });

    // Toast de éxito
    const itemCount = totalItems.value;
    const itemText = itemCount === 1 ? 'item' : 'items';
    toast.success(`${itemCount} ${itemText} actualizados en el carrito`, {
        timeout: 2000,
    });

    // Resetear selecciones y cerrar
    setTimeout(() => {
        selectedVariants.value = {};
        justAdded.value = false;
        emit('close');
    }, 400);
};

// Cargar cantidades del carrito al abrir el slideOver (PERSISTENCIA)
const loadCartQuantities = () => {
    if (!props.cart || !activeProduct.value) return;

    // Resetear selecciones
    selectedVariants.value = {};

    // Buscar items del carrito que correspondan a variantes de este producto
    props.cart.forEach(cartItem => {
        // Verificar si es una variante del producto actual
        if (cartItem.variant_id && activeProduct.value.variants) {
            const variant = activeProduct.value.variants.find(v => v.id === cartItem.variant_id);

            if (variant) {
                // Pre-cargar la cantidad del carrito
                selectedVariants.value[variant.id] = {
                    quantity: cartItem.quantity,
                    variant: variant
                };
            }
        }
    });
};

// Resetear/cargar selecciones cuando cambia el estado del slideOver
watch(() => props.show, (newValue, oldValue) => {
    if (newValue && !oldValue) {
        // Se acaba de abrir - cargar cantidades del carrito
        loadCartQuantities();
    } else if (!newValue) {
        // Se cerró - resetear
        selectedVariants.value = {};
        justAdded.value = false;
    }
});

// Detectar si el scroll está en el tope
const handleScroll = () => {
    if (!contentRef.value) return;
    isAtTop.value = contentRef.value.scrollTop <= 0;
};

// Touch handlers for swipe-to-close (solo cuando está en el tope)
const handleTouchStart = (e) => {
    const touch = e.touches[0];
    touchStart.value = { y: touch.clientY, x: touch.clientX };
    touchDelta.value = 0;
    isDragging.value = false;
};

const handleTouchMove = (e) => {
    const deltaY = e.touches[0].clientY - touchStart.value.y;
    const deltaX = Math.abs(e.touches[0].clientX - touchStart.value.x);

    // Solo permitir swipe hacia abajo si:
    // 1. El scroll está en el tope
    // 2. El movimiento es principalmente vertical (no horizontal)
    // 3. El movimiento es hacia abajo (deltaY > 0)
    if (!isAtTop.value || deltaX > 20) return;

    if (deltaY > 5) {
        isDragging.value = true;
        // Apply resistance (0.8) para rubber-band effect
        touchDelta.value = Math.min(deltaY * 0.8, 250);

        // Prevenir pull-to-refresh del navegador
        e.preventDefault();
    }
};

const handleTouchEnd = () => {
    // Close si se arrastró más de 80px y estaba en el tope
    if (isDragging.value && touchDelta.value > 80 && isAtTop.value) {
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
            @touchmove="handleTouchMove"
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
                            Selecciona tus opciones
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

            <!-- Variants List (Grouped) - Grid Compacto 2 Columnas -->
            <div
                ref="contentRef"
                @scroll="handleScroll"
                class="flex-1 overflow-y-auto p-4 overscroll-contain space-y-4"
            >
                <template v-for="(variants, groupKey) in groupedVariants" :key="groupKey">
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
                                        : (variant.available_quantity > 0 || variant.available_quantity === 999)
                                            ? 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 hover:border-orange-400'
                                            : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 opacity-50 cursor-not-allowed'
                                ]"
                            >
                                <!-- Header: Checkbox + Info -->
                                <div class="flex items-start gap-2 mb-2">
                                    <div class="w-5 h-5 rounded-md border-2 flex items-center justify-center flex-shrink-0 mt-0.5 transition-all"
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
                                        <p class="text-base font-bold text-orange-600 dark:text-orange-400 mt-0.5">
                                            ${{ parseFloat(variant.price).toFixed(2) }}
                                        </p>
                                        <!-- Stock status -->
                                        <p
                                            v-if="variant.available_quantity <= 0 && variant.available_quantity !== 999"
                                            class="text-xs text-red-500 font-bold mt-1 animate-pulse flex items-center gap-1"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Agotado
                                        </p>
                                        <p
                                            v-else-if="variant.available_quantity !== 999 && variant.available_quantity <= 3"
                                            class="text-xs text-red-500 font-semibold mt-1 flex items-center gap-1"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                            </svg>
                                            ¡Solo {{ variant.available_quantity === 1 ? 'queda 1' : `quedan ${variant.available_quantity}` }}!
                                        </p>
                                        <p
                                            v-else-if="variant.available_quantity !== 999 && variant.available_quantity <= 5"
                                            class="text-xs text-yellow-600 dark:text-yellow-400 font-medium mt-1 flex items-center gap-1"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            Quedan {{ variant.available_quantity }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Contador de cantidad (solo si está seleccionada) -->
                                <Transition
                                    enter-active-class="transition-all duration-300"
                                    enter-from-class="opacity-0 scale-90"
                                    enter-to-class="opacity-100 scale-100"
                                    leave-active-class="transition-all duration-200"
                                    leave-from-class="opacity-100 scale-100"
                                    leave-to-class="opacity-0 scale-90"
                                >
                                    <div v-if="selectedVariants[variant.id]" class="pt-2 border-t border-green-200 dark:border-green-700" @click.stop>
                                        <div class="flex items-center justify-between gap-1">
                                            <button
                                                @click="decrementQuantity(variant.id)"
                                                :disabled="selectedVariants[variant.id].quantity <= 1"
                                                class="w-7 h-7 rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 flex items-center justify-center transition-all hover:border-orange-500 active:scale-90 disabled:opacity-30 disabled:cursor-not-allowed"
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
                                                class="w-7 h-7 rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 flex items-center justify-center transition-all hover:border-orange-500 active:scale-90"
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

            <!-- Botón Footer -->
            <div class="sticky bottom-0 left-0 right-0 p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <!-- Botón Agregar al Carrito con Resumen -->
                <button
                    @click="addToCart"
                    :disabled="!hasSelection || justAdded"
                    class="w-full py-4 px-4 rounded-xl font-semibold transition-all relative overflow-hidden"
                    :class="[
                        hasSelection && !justAdded
                            ? 'bg-orange-500 text-white hover:bg-orange-600 active:scale-95 shadow-lg hover:shadow-xl'
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
                            <span>¡Agregado!</span>
                        </div>
                        <div v-else-if="hasSelection" class="flex items-center justify-between" key="add">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Agregar al carrito</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm opacity-90">
                                    ({{ totalItems }} {{ totalItems === 1 ? 'item' : 'items' }})
                                </span>
                                <span class="text-lg font-bold">
                                    ${{ totalPrice.toFixed(2) }}
                                </span>
                            </div>
                        </div>
                        <span v-else key="select">Selecciona tus opciones</span>
                    </Transition>
                </button>
            </div>

            <!-- Swipe hint -->
            <div v-if="isDragging" class="absolute inset-x-0 top-8 text-center">
                <span class="text-xs text-gray-400">Suelta para cerrar</span>
            </div>
        </div>
    </Transition>
</template>
