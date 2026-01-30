<script setup>
import { ref, computed, watch } from 'vue';
import { useToast } from 'vue-toastification';

const props = defineProps({
    show: Boolean,
    combo: Object,
});

const emit = defineEmits(['close', 'add-to-cart']);

const toast = useToast();

// Estado para las selecciones del usuario
// Estructura: { componentId: { optionId, variantId?, variantName?, productName } }
const selections = ref({});

// Inicializar selecciones cuando se abre el slideOver
watch(() => props.show, (newValue) => {
    if (newValue && props.combo) {
        initializeSelections();
    } else {
        selections.value = {};
    }
});

const initializeSelections = () => {
    if (!props.combo?.components) return;

    const newSelections = {};
    props.combo.components.forEach(component => {
        if (component.component_type === 'choice') {
            // Buscar opción por defecto o la primera disponible
            const defaultOption = component.options?.find(opt => opt.is_default)
                || component.options?.[0];

            if (defaultOption) {
                newSelections[component.id] = {
                    optionId: defaultOption.id,
                    productName: defaultOption.sellable?.name || 'Producto',
                    priceAdjustment: defaultOption.price_adjustment || 0,
                    hasVariants: defaultOption.sellable?.has_variants || defaultOption.sellable?.allows_variants || false,
                    variants: defaultOption.sellable?.variants || [],
                    variantId: null,
                    variantName: null,
                };

                // Si tiene variantes, seleccionar la primera por defecto
                const variants = defaultOption.sellable?.variants || [];
                if (variants.length > 0) {
                    const firstAvailable = variants.find(v => v.is_available) || variants[0];
                    if (firstAvailable) {
                        newSelections[component.id].variantId = firstAvailable.id;
                        newSelections[component.id].variantName = firstAvailable.variant_name || firstAvailable.name;
                    }
                }
            }
        }
    });
    selections.value = newSelections;
};

// Componentes fijos
const fixedComponents = computed(() => {
    return props.combo?.components?.filter(c => c.component_type === 'fixed') || [];
});

// Componentes de elección
const choiceComponents = computed(() => {
    return props.combo?.components?.filter(c => c.component_type === 'choice') || [];
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
            if (selection.hasVariants && selection.variants?.length > 0 && !selection.variantId) return false;
        }
    }
    return true;
});

// Seleccionar una opción
const selectOption = (componentId, option) => {
    const variants = option.sellable?.variants || [];
    const hasVariants = variants.length > 0;

    selections.value[componentId] = {
        optionId: option.id,
        productName: option.sellable?.name || 'Producto',
        priceAdjustment: option.price_adjustment || 0,
        hasVariants: hasVariants,
        variants: variants,
        variantId: null,
        variantName: null,
    };

    // Si tiene variantes, seleccionar la primera por defecto
    if (hasVariants) {
        const firstAvailable = variants.find(v => v.is_available) || variants[0];
        if (firstAvailable) {
            selections.value[componentId].variantId = firstAvailable.id;
            selections.value[componentId].variantName = firstAvailable.variant_name || firstAvailable.name;
        }
    }
};

// Seleccionar una variante
const selectVariant = (componentId, variant) => {
    if (selections.value[componentId]) {
        selections.value[componentId].variantId = variant.id;
        selections.value[componentId].variantName = variant.variant_name || variant.name;
    }
};

// Agregar al carrito
const addToCart = () => {
    if (!isSelectionComplete.value) return;

    // Construir el item del carrito
    const cartItem = {
        id: props.combo.id,
        name: props.combo.name,
        price: totalPrice.value,
        quantity: 1,
        product_type: 'combo',
        available_quantity: 999, // Los combos no tienen límite de stock individual
        selections: { ...selections.value },
        components_detail: buildComponentsDetail(),
    };

    emit('add-to-cart', cartItem);
    emit('close');
};

// Construir detalle de componentes para el carrito
const buildComponentsDetail = () => {
    const details = [];

    // Agregar componentes fijos
    fixedComponents.value.forEach(component => {
        if (component.sellable) {
            details.push({
                type: 'fixed',
                name: component.sellable.name,
                quantity: component.quantity,
            });
        }
    });

    // Agregar selecciones
    choiceComponents.value.forEach(component => {
        const selection = selections.value[component.id];
        if (selection) {
            let name = selection.productName;
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

// Formatear precio con signo
const formatPriceAdjustment = (adjustment) => {
    const value = parseFloat(adjustment) || 0;
    if (value === 0) return 'Incluido';
    if (value > 0) return `+$${value.toFixed(2)}`;
    return `-$${Math.abs(value).toFixed(2)}`;
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

    <!-- SlideOver (desde la derecha para desktop) -->
    <Transition
        enter-active-class="transition-transform duration-300"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition-transform duration-200"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="show && combo"
            class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white dark:bg-gray-800 shadow-xl flex flex-col"
        >
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-purple-600 to-purple-700">
                <div class="flex items-center gap-3">
                    <!-- Icono de combo -->
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white">
                            {{ combo.name }}
                        </h2>
                        <p class="text-sm text-purple-200">
                            Precio base: ${{ parseFloat(combo.base_price).toFixed(2) }}
                        </p>
                    </div>
                </div>
                <button
                    @click="emit('close')"
                    class="p-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Descripción del combo -->
            <div v-if="combo.description" class="px-4 py-3 bg-purple-50 dark:bg-purple-900/20 border-b border-purple-100 dark:border-purple-800">
                <p class="text-sm text-purple-700 dark:text-purple-300">
                    {{ combo.description }}
                </p>
            </div>

            <!-- Contenido scrolleable -->
            <div class="flex-1 overflow-y-auto">
                <!-- Componentes Fijos -->
                <div v-if="fixedComponents.length > 0" class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Incluye
                    </h3>
                    <div class="space-y-2">
                        <div
                            v-for="component in fixedComponents"
                            :key="component.id"
                            class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                                    <img
                                        v-if="component.sellable?.image_path"
                                        :src="component.sellable.image_path"
                                        :alt="component.sellable?.name"
                                        class="w-full h-full object-cover"
                                    >
                                    <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-400 to-orange-600">
                                        <svg class="w-5 h-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ component.sellable?.name || component.name }}
                                    </p>
                                    <p v-if="component.quantity > 1" class="text-xs text-gray-500 dark:text-gray-400">
                                        Cantidad: {{ component.quantity }}
                                    </p>
                                </div>
                            </div>
                            <span class="text-xs font-medium text-green-600 dark:text-green-400 px-2 py-1 bg-green-100 dark:bg-green-800/30 rounded-lg">
                                Incluido
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Componentes de Elección -->
                <div v-for="component in choiceComponents" :key="component.id" class="p-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                        </svg>
                        {{ component.name }}
                        <span v-if="component.is_required" class="text-red-500 text-xs">*</span>
                    </h3>

                    <!-- Opciones -->
                    <div class="space-y-2">
                        <button
                            v-for="option in component.options"
                            :key="option.id"
                            @click="selectOption(component.id, option)"
                            class="w-full text-left border-2 rounded-lg p-3 transition-all duration-200"
                            :class="[
                                selections[component.id]?.optionId === option.id
                                    ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20'
                                    : 'border-gray-200 dark:border-gray-600 hover:border-purple-300 dark:hover:border-purple-600'
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <!-- Radio button visual -->
                                <div
                                    class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all"
                                    :class="[
                                        selections[component.id]?.optionId === option.id
                                            ? 'border-purple-500 bg-purple-500'
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
                                        v-if="option.sellable?.image_path"
                                        :src="option.sellable.image_path"
                                        :alt="option.sellable?.name"
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
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ option.sellable?.name || 'Producto' }}
                                    </p>
                                    <p v-if="option.sellable?.variants?.length > 0" class="text-xs text-purple-600 dark:text-purple-400">
                                        {{ option.sellable.variants.length }} opciones
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
                                    {{ formatPriceAdjustment(option.price_adjustment) }}
                                </span>
                            </div>

                            <!-- Selector de variantes -->
                            <Transition
                                enter-active-class="transition-all duration-300"
                                enter-from-class="opacity-0 max-h-0"
                                enter-to-class="opacity-100 max-h-40"
                                leave-active-class="transition-all duration-200"
                                leave-from-class="opacity-100 max-h-40"
                                leave-to-class="opacity-0 max-h-0"
                            >
                                <div
                                    v-if="selections[component.id]?.optionId === option.id && option.sellable?.variants?.length > 0"
                                    class="mt-3 pt-3 border-t border-purple-200 dark:border-purple-800/50 overflow-hidden"
                                    @click.stop
                                >
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Selecciona una opcion:</p>
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            v-for="variant in option.sellable.variants"
                                            :key="variant.id"
                                            @click="selectVariant(component.id, variant)"
                                            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-all"
                                            :class="[
                                                selections[component.id]?.variantId === variant.id
                                                    ? 'bg-purple-500 text-white'
                                                    : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 hover:border-purple-400'
                                            ]"
                                        >
                                            {{ variant.variant_name || variant.name }}
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </button>
                    </div>
                </div>

                <!-- Mensaje si no hay componentes -->
                <div v-if="fixedComponents.length === 0 && choiceComponents.length === 0" class="p-8 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">Este combo no tiene componentes configurados</p>
                </div>
            </div>

            <!-- Footer con precio total y botón -->
            <div class="sticky bottom-0 left-0 right-0 p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <!-- Resumen de precio -->
                <div class="mb-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Precio base:</span>
                        <span class="text-gray-900 dark:text-white">${{ parseFloat(combo.base_price).toFixed(2) }}</span>
                    </div>
                    <template v-for="(selection, componentId) in selections" :key="componentId">
                        <div v-if="selection.priceAdjustment !== 0" class="flex items-center justify-between text-sm mt-1">
                            <span class="text-gray-600 dark:text-gray-400 truncate pr-2">
                                {{ selection.productName }}
                                <span v-if="selection.variantName" class="text-xs">({{ selection.variantName }})</span>
                            </span>
                            <span :class="selection.priceAdjustment > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400'">
                                {{ selection.priceAdjustment > 0 ? '+' : '' }}${{ selection.priceAdjustment.toFixed(2) }}
                            </span>
                        </div>
                    </template>
                    <div class="flex items-center justify-between text-base font-bold mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                        <span class="text-gray-900 dark:text-white">Total:</span>
                        <span class="text-purple-600 dark:text-purple-400">${{ totalPrice.toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="grid grid-cols-2 gap-3">
                    <button
                        @click="emit('close')"
                        class="py-3 px-4 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-semibold hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="addToCart"
                        :disabled="!isSelectionComplete"
                        class="py-3 px-4 rounded-lg font-semibold transition-all flex items-center justify-center gap-2"
                        :class="[
                            isSelectionComplete
                                ? 'bg-purple-500 text-white hover:bg-purple-600 shadow-lg'
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Agregar
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
