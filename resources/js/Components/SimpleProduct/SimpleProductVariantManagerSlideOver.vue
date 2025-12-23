<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import SlideOver from '../SlideOver.vue';
import SimpleProductVariantFormModal from './SimpleProductVariantFormModal.vue';
import { useToast } from 'vue-toastification';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    simpleProduct: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'updated']);
const toast = useToast();
const { confirm } = useConfirmDialog();

const variants = ref([]);
const products = ref([]);
const loading = ref(false);
const showVariantForm = ref(false);
const editingVariant = ref(null);

const loadVariants = async () => {
    if (!props.simpleProduct?.id) return;

    loading.value = true;
    try {
        const response = await fetch(route('simple-products.show', props.simpleProduct.id));
        const data = await response.json();
        variants.value = data.variants || [];
    } catch (error) {
        console.error('Error loading variants:', error);
        toast.error('Error al cargar las variantes');
    } finally {
        loading.value = false;
    }
};

const loadProducts = async () => {
    try {
        const response = await fetch(route('api.products.index'));
        const data = await response.json();
        products.value = data;
    } catch (error) {
        console.error('Error loading products:', error);
    }
};

watch(() => props.show, (newVal) => {
    if (newVal && props.simpleProduct?.id) {
        loadVariants();
        loadProducts();
    }
});

const handleClose = () => {
    emit('close');
};

const openCreateForm = () => {
    editingVariant.value = null;
    showVariantForm.value = true;
};

const openEditForm = (variant) => {
    editingVariant.value = variant;
    showVariantForm.value = true;
};

const closeVariantForm = () => {
    showVariantForm.value = false;
    editingVariant.value = null;
};

const handleVariantSaved = () => {
    closeVariantForm();
    loadVariants();
    emit('updated');
};

const deleteVariant = async (variant) => {
    const confirmed = await confirm({
        title: '¿Eliminar variante?',
        message: `Se eliminará la variante "${variant.variant_name}". Esta acción no se puede deshacer.`,
        confirmText: 'Eliminar',
        type: 'danger'
    });

    if (!confirmed) return;

    router.delete(route('simple-products.variants.destroy', variant.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Variante eliminada');
            loadVariants();
            emit('updated');
        },
        onError: () => {
            toast.error('Error al eliminar la variante');
        }
    });
};

const toggleAvailability = (variant) => {
    router.patch(route('simple-products.variants.toggle-availability', variant.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            loadVariants();
            emit('updated');
        },
        onError: () => {
            toast.error('Error al actualizar disponibilidad');
        }
    });
};

const getAttributesText = (attributes) => {
    if (!attributes || typeof attributes !== 'object') return '';
    return Object.entries(attributes)
        .map(([key, value]) => `${key}: ${value}`)
        .join(', ');
};
</script>

<template>
    <SlideOver
        :show="show"
        @close="handleClose"
        :title="`Variantes de ${simpleProduct?.name || 'Producto'}`"
        subtitle="Gestión de variantes del producto"
        size="md"
    >
        <div class="flex flex-col h-full">

            <!-- Content -->
            <div class="flex-1 overflow-y-auto px-6 py-4">
                <!-- Loading State -->
                <div v-if="loading" class="flex items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-600"></div>
                </div>

                <!-- Empty State -->
                <div v-else-if="variants.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Sin variantes</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Agrega variantes para productos con diferentes sabores, tamaños, etc.
                    </p>
                    <div class="mt-6">
                        <button
                            @click="openCreateForm"
                            type="button"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                        >
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Crear Primera Variante
                        </button>
                    </div>
                </div>

                <!-- Variants List -->
                <div v-else class="space-y-3">
                    <div
                        v-for="variant in variants"
                        :key="variant.id"
                        class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-orange-300 dark:hover:border-orange-600 transition-colors"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ variant.variant_name }}
                                    </h4>
                                    <span
                                        class="px-2 py-0.5 text-xs font-medium rounded-full"
                                        :class="variant.is_available
                                            ? 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-200'
                                            : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200'"
                                    >
                                        {{ variant.is_available ? 'Disponible' : 'No disponible' }}
                                    </span>
                                </div>

                                <div class="mt-2 text-sm space-y-1">
                                    <p class="text-gray-600 dark:text-gray-400">
                                        Precio: <span class="font-medium text-gray-900 dark:text-white">${{ parseFloat(variant.price).toFixed(2) }}</span>
                                    </p>
                                    <p v-if="variant.description" class="text-gray-500 dark:text-gray-400 text-xs">
                                        {{ variant.description }}
                                    </p>
                                    <p v-if="getAttributesText(variant.attributes)" class="text-gray-500 dark:text-gray-400 text-xs">
                                        {{ getAttributesText(variant.attributes) }}
                                    </p>
                                    <p class="text-gray-600 dark:text-gray-400">
                                        Stock disponible:
                                        <span class="font-medium" :class="variant.available_quantity > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                            {{ variant.available_quantity || 0 }}
                                        </span>
                                    </p>
                                    <p v-if="variant.recipes?.length > 0" class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ variant.recipes.length }} ingrediente(s) configurado(s)
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 ml-4">
                                <button
                                    @click="toggleAvailability(variant)"
                                    type="button"
                                    :title="variant.is_available ? 'Deshabilitar' : 'Habilitar'"
                                    class="p-2 text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <svg v-if="variant.is_available" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                                <button
                                    @click="openEditForm(variant)"
                                    type="button"
                                    title="Editar"
                                    class="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    @click="deleteVariant(variant)"
                                    type="button"
                                    title="Eliminar"
                                    class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <button
                    v-if="variants.length > 0"
                    @click="openCreateForm"
                    type="button"
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                >
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Agregar Nueva Variante
                </button>
            </div>
        </div>

        <!-- Variant Form Modal -->
        <SimpleProductVariantFormModal
            :show="showVariantForm"
            :simple-product="simpleProduct"
            :variant="editingVariant"
            :products="products"
            @close="closeVariantForm"
            @saved="handleVariantSaved"
        />
    </SlideOver>
</template>
