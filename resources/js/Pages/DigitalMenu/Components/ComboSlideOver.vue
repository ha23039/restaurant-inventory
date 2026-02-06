<script setup>
import { ref, computed, watch } from 'vue';
import { useToast } from 'vue-toastification';

const props = defineProps({
    show: Boolean,
    combo: Object,
});

const emit = defineEmits(['close', 'add-to-cart']);

const toast = useToast();
const justAdded = ref(false);

// Estado para las selecciones del cliente
// Estructura: { componentId: { optionId, variantId?, variantName? } }
const selections = ref({});

// Inicializar selecciones cuando se abre el slideOver
watch(() => props.show, (newValue) => {
    if (newValue && props.combo) {
        initializeSelections();
    } else {
        selections.value = {};
        justAdded.value = false;
    }
});

const initializeSelections = () => {
    if (!props.combo?.components) return;

    const newSelections = {};
    props.combo.components.forEach(component => {
        if (component.type === 'choice') {
            // Buscar opción por defecto o la primera disponible
            const defaultOption = component.options?.find(opt => opt.is_default)
                || component.options?.[0];

            if (defaultOption) {
                newSelections[component.id] = {
                    optionId: defaultOption.id,
                    optionName: defaultOption.product_name,
                    priceAdjustment: defaultOption.price_adjustment,
                    hasVariants: defaultOption.has_variants,
                    variantId: null,
                    variantName: null,
                };

                // Si tiene variantes, seleccionar la primera DISPONIBLE por defecto
                if (defaultOption.has_variants && defaultOption.variants?.length > 0) {
                    // Buscar primera variante disponible (sin stock definido = ilimitado, stock > 0, o stock 999)
                    const availableVariant = defaultOption.variants.find(
                        v => v.available_quantity === undefined || v.available_quantity === null || v.available_quantity > 0 || v.available_quantity === 999
                    ) || defaultOption.variants[0];
                    
                    newSelections[component.id].variantId = availableVariant.id;
                    newSelections[component.id].variantName = availableVariant.name;
                }
            }
        }
    });
    selections.value = newSelections;
};

// Componentes fijos
const fixedComponents = computed(() => {
    return props.combo?.components?.filter(c => c.type === 'fixed') || [];
});

// Componentes de elección
const choiceComponents = computed(() => {
    return props.combo?.components?.filter(c => c.type === 'choice') || [];
});

// Calcular precio total
const totalPrice = computed(() => {
    if (!props.combo) return 0;

    let price = parseFloat(props.combo.base_price) || 0;

    // Sumar ajustes de precio de las selecciones
    Object.values(selections.value).forEach(selection => {
        if (selection.priceAdjustment) {
            price += parseFloat(selection.priceAdjustment);
        }
    });

    return Math.max(0, price);
});

// Verificar si todas las selecciones requeridas están completas
const isSelectionComplete = computed(() => {
    for (const component of choiceComponents.value) {
        if (component.is_required) {
            const selection = selections.value[component.id];
            if (!selection?.optionId) return false;

            // Si la opción tiene variantes, debe seleccionar una
            if (selection.hasVariants && !selection.variantId) return false;
        }
    }
    return true;
});

// Seleccionar una opción
const selectOption = (componentId, option) => {
    selections.value[componentId] = {
        optionId: option.id,
        optionName: option.product_name,
        priceAdjustment: option.price_adjustment,
        hasVariants: option.has_variants,
        variantId: null,
        variantName: null,
    };

    // Si tiene variantes, seleccionar la primera por defecto
    if (option.has_variants && option.variants?.length > 0) {
        selections.value[componentId].variantId = option.variants[0].id;
        selections.value[componentId].variantName = option.variants[0].name;
    }
};

// Seleccionar una variante
const selectVariant = (componentId, variant) => {
    if (selections.value[componentId]) {
        selections.value[componentId].variantId = variant.id;
        selections.value[componentId].variantName = variant.name;
    }
};

// Agregar al carrito
const addToCart = () => {
    if (!isSelectionComplete.value || justAdded.value) return;

    justAdded.value = true;

    // Construir el item del carrito
    const cartItem = {
        type: 'combo',
        product_type: 'combo',
        id: props.combo.id,
        name: props.combo.name,
        price: totalPrice.value,
        quantity: 1,
        image_path: props.combo.image_path,
        selections: { ...selections.value },
        // Detalle de componentes para mostrar en el carrito
        components_detail: buildComponentsDetail(),
    };

    emit('add-to-cart', cartItem);

    toast.success(`${props.combo.name} agregado al carrito`, {
        timeout: 2000,
    });

    setTimeout(() => {
        justAdded.value = false;
        emit('close');
    }, 400);
};

// Construir detalle de componentes para el carrito
const buildComponentsDetail = () => {
    const details = [];

    // Agregar componentes fijos
    fixedComponents.value.forEach(component => {
        if (component.product) {
            details.push({
                type: 'fixed',
                name: component.product.name,
                quantity: component.quantity,
            });
        }
    });

    // Agregar selecciones
    choiceComponents.value.forEach(component => {
        const selection = selections.value[component.id];
        if (selection) {
            let name = selection.optionName;
            if (selection.variantName) {
                name += ` (${selection.variantName})`;
            }
            details.push({
                type: 'choice',
                componentName: component.name,
                name: name,
                quantity: component.quantity,
            });
        }
    });

    return details;
};

// Touch gesture state
const touchStart = ref({ y: 0 });
const touchDelta = ref(0);
const isDragging = ref(false);
const isAtTop = ref(true);
const contentRef = ref(null);

const handleScroll = () => {
    if (!contentRef.value) return;
    isAtTop.value = contentRef.value.scrollTop <= 0;
};

const handleTouchStart = (e) => {
    touchStart.value.y = e.touches[0].clientY;
    touchDelta.value = 0;
    isDragging.value = false;
};

const handleTouchMove = (e) => {
    const deltaY = e.touches[0].clientY - touchStart.value.y;

    if (!isAtTop.value) return;

    if (deltaY > 5) {
        isDragging.value = true;
        touchDelta.value = Math.min(deltaY * 0.8, 250);
        e.preventDefault();
    }
};

const handleTouchEnd = () => {
    if (isDragging.value && touchDelta.value > 80 && isAtTop.value) {
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
            v-if="show && combo"
            @touchstart="handleTouchStart"
            @touchmove="handleTouchMove"
            @touchend="handleTouchEnd"
            class="fixed inset-x-0 bottom-0 z-50 max-h-[90vh] bg-white dark:bg-gray-800 rounded-t-2xl shadow-xl flex flex-col"
            :style="{
                transform: isDragging ? `translateY(${touchDelta}px)` : '',
                transition: isDragging ? 'none' : 'transform 0.2s ease-out'
            }"
        >
            <!-- Handle Bar -->
            <div class="flex justify-center pt-3 pb-2 cursor-grab">
                <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
            </div>

            <!-- Header con imagen -->
            <div class="px-5 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-start gap-4">
                    <!-- Imagen del combo -->
                    <div class="w-20 h-20 flex-shrink-0 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700">
                        <img
                            v-if="combo.image_path"
                            :src="combo.image_path"
                            :alt="combo.name"
                            class="w-full h-full object-cover"
                        >
                        <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-500 to-purple-700">
                            <svg class="w-8 h-8 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ combo.name }}
                        </h2>
                        <p v-if="combo.description" class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mt-1">
                            {{ combo.description }}
                        </p>
                        <p class="text-lg font-bold text-orange-600 dark:text-orange-400 mt-1">
                            ${{ parseFloat(combo.base_price).toFixed(2) }}
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

            <!-- Contenido scrolleable -->
            <div
                ref="contentRef"
                @scroll="handleScroll"
                class="flex-1 overflow-y-auto overscroll-contain"
            >
                <!-- Componentes Fijos -->
                <div v-if="fixedComponents.length > 0" class="p-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Incluye
                    </h3>
                    <div class="space-y-2">
                        <div
                            v-for="component in fixedComponents"
                            :key="component.id"
                            class="flex items-center gap-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-xl"
                        >
                            <!-- Imagen del producto -->
                            <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                                <img
                                    v-if="component.product?.image_path"
                                    :src="component.product.image_path"
                                    :alt="component.product?.name"
                                    class="w-full h-full object-cover"
                                >
                                <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-400 to-orange-600">
                                    <svg class="w-5 h-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 dark:text-white text-sm">
                                    {{ component.product?.name || component.name }}
                                </p>
                                <p v-if="component.quantity > 1" class="text-xs text-gray-500 dark:text-gray-400">
                                    Cantidad: {{ component.quantity }}
                                </p>
                            </div>

                            <span class="text-xs font-medium text-green-600 dark:text-green-400 px-2 py-1 bg-green-100 dark:bg-green-800/30 rounded-lg">
                                Incluido
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Componentes de Elección -->
                <div v-for="component in choiceComponents" :key="component.id" class="p-4 border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                        </svg>
                        {{ component.name }}
                        <span v-if="component.is_required" class="text-red-500 text-xs">*</span>
                    </h3>

                    <!-- Opciones -->
                    <div class="grid grid-cols-1 gap-2">
                        <button
                            v-for="option in component.options"
                            :key="option.id"
                            @click="selectOption(component.id, option)"
                            class="w-full text-left border-2 rounded-xl p-3 transition-all duration-200"
                            :class="[
                                selections[component.id]?.optionId === option.id
                                    ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20'
                                    : 'border-gray-200 dark:border-gray-600 hover:border-orange-300'
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <!-- Radio button visual -->
                                <div
                                    class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all"
                                    :class="[
                                        selections[component.id]?.optionId === option.id
                                            ? 'border-orange-500 bg-orange-500'
                                            : 'border-gray-300 dark:border-gray-600'
                                    ]"
                                >
                                    <div
                                        v-if="selections[component.id]?.optionId === option.id"
                                        class="w-2 h-2 rounded-full bg-white"
                                    ></div>
                                </div>

                                <!-- Imagen del producto -->
                                <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                                    <img
                                        v-if="option.product_image"
                                        :src="option.product_image"
                                        :alt="option.product_name"
                                        class="w-full h-full object-cover"
                                    >
                                    <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-400 to-orange-600">
                                        <svg class="w-5 h-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 dark:text-white text-sm">
                                        {{ option.product_name }}
                                    </p>
                                    <p v-if="option.has_variants" class="text-xs text-purple-600 dark:text-purple-400">
                                        {{ option.variants?.length }} sabores disponibles
                                    </p>
                                </div>

                                <!-- Precio -->
                                <span
                                    class="text-sm font-semibold flex-shrink-0"
                                    :class="[
                                        option.price_adjustment === 0
                                            ? 'text-green-600 dark:text-green-400'
                                            : option.price_adjustment > 0
                                                ? 'text-orange-600 dark:text-orange-400'
                                                : 'text-blue-600 dark:text-blue-400'
                                    ]"
                                >
                                    {{ option.formatted_adjustment }}
                                </span>
                            </div>

                            <!-- Selector de variantes (si está seleccionada y tiene variantes) -->
                            <Transition
                                enter-active-class="transition-all duration-300"
                                enter-from-class="opacity-0 max-h-0"
                                enter-to-class="opacity-100 max-h-40"
                                leave-active-class="transition-all duration-200"
                                leave-from-class="opacity-100 max-h-40"
                                leave-to-class="opacity-0 max-h-0"
                            >
                                <div
                                    v-if="selections[component.id]?.optionId === option.id && option.has_variants"
                                    class="mt-3 pt-3 border-t border-orange-200 dark:border-orange-800/50 overflow-hidden"
                                    @click.stop
                                >
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Elige tu sabor:</p>
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            v-for="variant in option.variants"
                                            :key="variant.id"
                                            @click="selectVariant(component.id, variant)"
                                            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-all"
                                            :class="[
                                                variant.available_quantity !== undefined && variant.available_quantity !== null && variant.available_quantity <= 0 && variant.available_quantity !== 999
                                                    ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed line-through'
                                                    : selections[component.id]?.variantId === variant.id
                                                        ? 'bg-orange-500 text-white'
                                                        : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 hover:border-orange-400'
                                            ]"
                                            :disabled="variant.available_quantity !== undefined && variant.available_quantity !== null && variant.available_quantity <= 0 && variant.available_quantity !== 999"
                                        >
                                            {{ variant.name }}
                                            <svg 
                                                v-if="variant.available_quantity !== undefined && variant.available_quantity !== null && variant.available_quantity <= 0 && variant.available_quantity !== 999" 
                                                class="w-3 h-3 ml-1 text-red-400 inline"
                                                fill="none" 
                                                stroke="currentColor" 
                                                viewBox="0 0 24 24"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            <span 
                                                v-else-if="variant.available_quantity !== undefined && variant.available_quantity !== null && variant.available_quantity !== 999 && variant.available_quantity <= 3" 
                                                class="ml-1 text-red-500 font-bold"
                                            >({{ variant.available_quantity }})</span>
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer con precio total y botón -->
            <div class="sticky bottom-0 left-0 right-0 p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <!-- Resumen de selecciones -->
                <div v-if="Object.keys(selections).length > 0" class="mb-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Precio base:</span>
                        <span class="text-gray-900 dark:text-white">${{ parseFloat(combo.base_price).toFixed(2) }}</span>
                    </div>
                    <template v-for="(selection, componentId) in selections" :key="componentId">
                        <div v-if="selection.priceAdjustment !== 0" class="flex items-center justify-between text-sm mt-1">
                            <span class="text-gray-600 dark:text-gray-400 truncate pr-2">
                                {{ selection.optionName }}
                                <span v-if="selection.variantName" class="text-xs">({{ selection.variantName }})</span>
                            </span>
                            <span :class="selection.priceAdjustment > 0 ? 'text-orange-600' : 'text-green-600'">
                                {{ selection.priceAdjustment > 0 ? '+' : '' }}${{ Math.abs(selection.priceAdjustment).toFixed(2) }}
                            </span>
                        </div>
                    </template>
                    <div class="flex items-center justify-between text-base font-bold mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                        <span class="text-gray-900 dark:text-white">Total:</span>
                        <span class="text-orange-600 dark:text-orange-400">${{ totalPrice.toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Botón agregar -->
                <button
                    @click="addToCart"
                    :disabled="!isSelectionComplete || justAdded"
                    class="w-full py-4 px-4 rounded-xl font-semibold transition-all relative overflow-hidden"
                    :class="[
                        isSelectionComplete && !justAdded
                            ? 'bg-orange-500 text-white hover:bg-orange-600 active:scale-95 shadow-lg'
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
                        <div v-else-if="isSelectionComplete" class="flex items-center justify-between" key="add">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Agregar combo</span>
                            </div>
                            <span class="text-lg font-bold">${{ totalPrice.toFixed(2) }}</span>
                        </div>
                        <span v-else key="select">Completa tus selecciones</span>
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
