<script setup>
import { ref, computed, watch } from 'vue';
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
const deletingVariantId = ref(null);

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

// Cargar productos disponibles para las recetas
const loadProducts = async () => {
    try {
        const response = await fetch(route('api.products.index'));
        const data = await response.json();
        products.value = data;
    } catch (error) {
        console.error('Error loading products:', error);
    }
};

// Watch for show prop changes to load variants and products
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
    toast.success(editingVariant.value ? 'Variante actualizada' : 'Variante creada');
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

    deletingVariantId.value = variant.id;

    router.delete(route('simple-products.variants.destroy', variant.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Variante eliminada');
            loadVariants();
            emit('updated');
        },
        onError: (errors) => {
            toast.error(errors.error || 'Error al eliminar variante');
        },
        onFinish: () => {
            deletingVariantId.value = null;
        }
    });
};

const toggleAvailability = async (variant) => {
    router.patch(route('simple-products.variants.toggle-availability', variant.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            loadVariants();
            toast.success('Disponibilidad actualizada');
            emit('updated');
        },
        onError: (errors) => {
            toast.error(errors.error || 'Error al actualizar disponibilidad');
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
        <div class="space-y-6">
            <!-- Loading state -->
            <div v-if="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-orange-600 mb-4"></div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Cargando variantes...</p>
            </div>

            <!-- Empty state -->
            <div v-else-if="variants.length === 0" class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="text-gray-500 dark:text-gray-400 mb-2">No hay variantes registradas</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">Crea la primera variante para este producto</p>
                <button
                    @click="openCreateForm"
                    class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Nueva Variante
                </button>
            </div>

            <!-- Variants list -->
            <div v-else class="space-y-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Variantes ({{ variants.length }})
                    </h3>
                </div>
                
                <div class="space-y-3">
                    <div
                        v-for="variant in variants"
                        :key="variant.id"
                        class="p-4 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">
                                    {{ variant.variant_name }}
                                </h4>
                                <!-- Atributos dinámicos -->
                                <div v-if="variant.attributes && Object.keys(variant.attributes).length > 0" class="flex flex-wrap gap-2 mb-2">
                                    <span 
                                        v-for="(value, key) in variant.attributes" 
                                        :key="key"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300"
                                    >
                                        {{ key }}: {{ value }}
                                    </span>
                                </div>
                                <div v-if="variant.description" class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                    {{ variant.description }}
                                </div>
                                <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span>Stock: {{ variant.available_quantity || 0 }}</span>
                                    <span v-if="variant.recipes?.length > 0">•</span>
                                    <span v-if="variant.recipes?.length > 0">{{ variant.recipes.length }} ingrediente(s)</span>
                                </div>
                            </div>
                            <span class="text-xl font-bold text-orange-600 dark:text-orange-400">
                                ${{ parseFloat(variant.price).toFixed(2) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-600">
                            <button
                                @click="toggleAvailability(variant)"
                                class="relative inline-flex items-center"
                            >
                                <div
                                    class="w-11 h-6 rounded-full transition-colors"
                                    :class="variant.is_available ? 'bg-green-600' : 'bg-gray-300 dark:bg-gray-600'"
                                >
                                    <div
                                        class="absolute top-0.5 left-0.5 bg-white w-5 h-5 rounded-full transition-transform"
                                        :class="variant.is_available ? 'transform translate-x-5' : ''"
                                    ></div>
                                </div>
                                <span class="ml-2 text-xs text-gray-700 dark:text-gray-300">
                                    {{ variant.is_available ? 'Disponible' : 'No disponible' }}
                                </span>
                            </button>
                            <div class="flex items-center space-x-1">
                                <button
                                    @click="openEditForm(variant)"
                                    class="p-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                    title="Editar"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    @click="deleteVariant(variant)"
                                    :disabled="deletingVariantId === variant.id"
                                    class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/20 rounded-lg transition-colors disabled:opacity-50"
                                    title="Eliminar"
                                >
                                    <svg v-if="deletingVariantId === variant.id" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex items-center justify-between">
                <button
                    @click="openCreateForm"
                    type="button"
                    class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva Variante
                </button>
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

    <!-- Variant Form Modal -->
    <SimpleProductVariantFormModal
        :show="showVariantForm"
        :simpleProduct="simpleProduct"
        :variant="editingVariant"
        :products="products"
        @close="closeVariantForm"
        @saved="handleVariantSaved"
    />
</template>
