<script setup>
import { ref, computed } from 'vue';
import SlideOver from './SlideOver.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    menuItem: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'select']);

// Track selected variants for visual feedback
const selectedVariants = ref(new Set());

const handleSelectVariant = (variant) => {
    // Add visual feedback
    selectedVariants.value.add(variant.id);

    // Emit selection
    emit('select', {
        id: variant.id,
        name: variant.variant_name,
        price: parseFloat(variant.price),
        available_quantity: variant.available_quantity || 0,
        product_type: 'variant',
        menu_item_id: props.menuItem?.id,
    });

    // Remove feedback after animation
    setTimeout(() => {
        selectedVariants.value.delete(variant.id);
    }, 600);
};

const handleClose = () => {
    emit('close');
};

// Group variants by masa type
const groupedVariants = computed(() => {
    if (!props.menuItem?.variants) return { maiz: [], arroz: [] };

    const groups = {
        maiz: [],
        arroz: [],
    };

    props.menuItem.variants.forEach(variant => {
        const masa = variant.attributes?.masa || 'maiz';
        if (groups[masa]) {
            groups[masa].push(variant);
        }
    });

    return groups;
});

const hasVariants = computed(() => {
    return (groupedVariants.value.maiz.length + groupedVariants.value.arroz.length) > 0;
});
</script>

<template>
    <SlideOver
        :show="show"
        @close="handleClose"
        :title="menuItem?.name || 'Seleccionar Variante'"
        :subtitle="menuItem?.description || 'Selecciona tu masa y relleno favorito'"
        size="lg"
    >
        <div class="space-y-6">
            <!-- No variants message -->
            <div v-if="!hasVariants" class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="text-gray-500 dark:text-gray-400">No hay variantes disponibles</p>
            </div>

            <!-- Variants Grid -->
            <div v-else class="space-y-6">
                <!-- Masa de Maiz -->
                <div v-if="groupedVariants.maiz.length > 0">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-yellow-600 dark:text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z" />
                        </svg>
                        Masa de Maiz
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button
                            v-for="variant in groupedVariants.maiz"
                            :key="variant.id"
                            @click="handleSelectVariant(variant)"
                            :disabled="variant.available_quantity <= 0"
                            :class="[
                                'group relative p-4 rounded-xl border-2 text-left transition-all duration-200',
                                selectedVariants.has(variant.id)
                                    ? 'border-green-500 bg-green-50 dark:bg-green-900/30 scale-95'
                                    : variant.available_quantity > 0
                                    ? 'border-gray-300 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400 hover:shadow-lg hover:scale-105 bg-white dark:bg-gray-700'
                                    : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 opacity-60 cursor-not-allowed'
                            ]"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    {{ variant.variant_name.split(' - ')[1] || variant.variant_name }}
                                </h4>
                                <span v-if="selectedVariants.has(variant.id)" class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    ${{ parseFloat(variant.price).toFixed(2) }}
                                </span>
                                <span :class="[
                                    'text-xs font-medium px-2 py-1 rounded-full',
                                    variant.available_quantity > 10
                                        ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'
                                        : variant.available_quantity > 0
                                        ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300'
                                        : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'
                                ]">
                                    {{ variant.available_quantity > 0 ? `Stock: ${variant.available_quantity}` : 'Agotado' }}
                                </span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Masa de Arroz -->
                <div v-if="groupedVariants.arroz.length > 0">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-amber-600 dark:text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z" />
                        </svg>
                        Masa de Arroz
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button
                            v-for="variant in groupedVariants.arroz"
                            :key="variant.id"
                            @click="handleSelectVariant(variant)"
                            :disabled="variant.available_quantity <= 0"
                            :class="[
                                'group relative p-4 rounded-xl border-2 text-left transition-all duration-200',
                                selectedVariants.has(variant.id)
                                    ? 'border-green-500 bg-green-50 dark:bg-green-900/30 scale-95'
                                    : variant.available_quantity > 0
                                    ? 'border-gray-300 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400 hover:shadow-lg hover:scale-105 bg-white dark:bg-gray-700'
                                    : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 opacity-60 cursor-not-allowed'
                            ]"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    {{ variant.variant_name.split(' - ')[1] || variant.variant_name }}
                                </h4>
                                <span v-if="selectedVariants.has(variant.id)" class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    ${{ parseFloat(variant.price).toFixed(2) }}
                                </span>
                                <span :class="[
                                    'text-xs font-medium px-2 py-1 rounded-full',
                                    variant.available_quantity > 10
                                        ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'
                                        : variant.available_quantity > 0
                                        ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300'
                                        : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'
                                ]">
                                    {{ variant.available_quantity > 0 ? `Stock: ${variant.available_quantity}` : 'Agotado' }}
                                </span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Info tip -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm text-blue-800 dark:text-blue-300">
                            Haz clic en una variante para agregarla al carrito. Puedes seleccionar multiples variantes.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex items-center justify-end">
                <button
                    @click="handleClose"
                    type="button"
                    class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                    Cerrar
                </button>
            </div>
        </template>
    </SlideOver>
</template>
