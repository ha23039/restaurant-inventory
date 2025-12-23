<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseModal from './Base/BaseModal.vue';
import { useToast } from 'vue-toastification';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    menuItem: {
        type: Object,
        default: null
    },
    variant: {
        type: Object,
        default: null
    },
    products: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'saved']);
const toast = useToast();

const form = ref({
    variant_name: '',
    variant_sku: '',
    price: '',
    attributes: [],  // Array dinámico de {key, value}
    is_available: true,
    display_order: 0,
    recipes: []  // Array de ingredientes {product_id, quantity_needed, unit}
});

const submitting = ref(false);
const errors = ref({});

const isEditing = computed(() => !!props.variant);
const modalTitle = computed(() => isEditing.value ? 'Editar Variante' : 'Nueva Variante');

// IMPORTANTE: resetForm debe estar antes de los watchers que la usan con immediate: true
const resetForm = () => {
    form.value = {
        variant_name: '',
        variant_sku: '',
        price: '',
        attributes: [],  // Array dinámico de {key, value}
        is_available: true,
        display_order: 0,
        recipes: []  // Array de ingredientes
    };
    errors.value = {};
};

watch(() => props.variant, (newVariant) => {
    if (newVariant) {
        // Convertir attributes object a array de {key, value}
        const attributesArray = [];
        if (newVariant.attributes && typeof newVariant.attributes === 'object') {
            Object.entries(newVariant.attributes).forEach(([key, value]) => {
                attributesArray.push({ key, value });
            });
        }

        // Convertir recetas existentes al formato del formulario
        const recipesArray = [];
        if (newVariant.recipes && Array.isArray(newVariant.recipes)) {
            newVariant.recipes.forEach(recipe => {
                recipesArray.push({
                    id: recipe.id,
                    product_id: recipe.product_id,
                    quantity_needed: recipe.quantity_needed,
                    unit: recipe.unit
                });
            });
        }

        form.value = {
            variant_name: newVariant.variant_name || '',
            variant_sku: newVariant.variant_sku || '',
            price: newVariant.price || '',
            attributes: attributesArray,
            is_available: newVariant.is_available ?? true,
            display_order: newVariant.display_order || 0,
            recipes: recipesArray
        };
    } else {
        resetForm();
    }
}, { immediate: true });

// Watch for show prop to reset form when modal opens
watch(() => props.show, (newVal) => {
    if (newVal && !props.variant) {
        resetForm();
    }
    errors.value = {};
});

// resetForm movida arriba, antes de los watchers

const handleSubmit = () => {
    if (submitting.value) return;

    submitting.value = true;
    errors.value = {};

    // Convertir array de atributos a objeto
    const attributesObject = {};
    form.value.attributes.forEach(attr => {
        if (attr.key && attr.key.trim()) {
            attributesObject[attr.key.trim().toLowerCase()] = attr.value.trim();
        }
    });

    // Preparar recetas para enviar (solo las válidas)
    const validRecipes = form.value.recipes
        .filter(r => r.product_id && r.quantity_needed > 0 && r.unit)
        .map(r => ({
            id: r.id || null,
            product_id: parseInt(r.product_id),
            quantity_needed: parseFloat(r.quantity_needed),
            unit: r.unit
        }));

    const url = isEditing.value
        ? route('carta.variants.update', props.variant.id)
        : route('carta.variants.store', props.menuItem.id);

    const method = isEditing.value ? 'put' : 'post';

    router[method](url, {
        variant_name: form.value.variant_name,
        variant_sku: form.value.variant_sku,
        price: parseFloat(form.value.price),
        attributes: attributesObject,
        is_available: form.value.is_available,
        display_order: parseInt(form.value.display_order) || 0,
        recipes: validRecipes
    }, {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            emit('close');
        },
        onError: (err) => {
            errors.value = err;
            toast.error('Por favor corrige los errores del formulario');
        },
        onFinish: () => {
            submitting.value = false;
        }
    });
};

// Funciones para manejar atributos dinámicos
const addAttribute = () => {
    form.value.attributes.push({ key: '', value: '' });
};

const removeAttribute = (index) => {
    form.value.attributes.splice(index, 1);
};

// Funciones para manejar ingredientes/recetas
const addRecipe = () => {
    form.value.recipes.push({
        id: null,
        product_id: '',
        quantity_needed: '',
        unit: ''
    });
};

const removeRecipe = (index) => {
    form.value.recipes.splice(index, 1);
};

// Auto-seleccionar unidad cuando se selecciona un producto
const onProductSelect = (index) => {
    const recipe = form.value.recipes[index];
    if (recipe.product_id) {
        const product = props.products.find(p => p.id === parseInt(recipe.product_id));
        if (product && !recipe.unit) {
            recipe.unit = product.unit_type;
        }
    }
};

// Obtener productos disponibles (excluyendo los ya agregados)
const getAvailableProducts = (currentIndex) => {
    const usedProductIds = form.value.recipes
        .filter((_, idx) => idx !== currentIndex)
        .map(r => parseInt(r.product_id))
        .filter(id => !isNaN(id));

    return props.products.filter(p => !usedProductIds.includes(p.id));
};

// Obtener producto seleccionado en una receta
const getSelectedProduct = (productId) => {
    return props.products.find(p => p.id === parseInt(productId));
};

// Opciones de unidades
const unitOptions = [
    { value: 'kg', label: 'Kilogramos (kg)' },
    { value: 'g', label: 'Gramos (g)' },
    { value: 'lt', label: 'Litros (L)' },
    { value: 'ml', label: 'Mililitros (ml)' },
    { value: 'pcs', label: 'Piezas (pzas)' },
];

// Formatear cantidad
const formatQuantity = (quantity) => {
    if (!quantity) return '0';
    return parseFloat(quantity).toFixed(3).replace(/\.?0+$/, '');
};

// Calcular stock disponible basado en recetas actuales
const calculatedStock = computed(() => {
    if (form.value.recipes.length === 0) return 999;

    let minAvailable = Infinity;

    for (const recipe of form.value.recipes) {
        if (!recipe.product_id || !recipe.quantity_needed) continue;

        const product = props.products.find(p => p.id === parseInt(recipe.product_id));
        if (!product) continue;

        const availableFromThis = Math.floor(product.current_stock / parseFloat(recipe.quantity_needed));
        minAvailable = Math.min(minAvailable, availableFromThis);
    }

    return minAvailable === Infinity ? 999 : minAvailable;
});

const handleClose = () => {
    if (!submitting.value) {
        emit('close');
    }
};
</script>

<template>
    <BaseModal
        :show="show"
        @close="handleClose"
        max-width="2xl"
    >
        <div class="p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                {{ modalTitle }}
            </h2>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Nombre de la variante -->
                <div>
                    <label for="variant_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nombre de la Variante <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="variant_name"
                        v-model="form.variant_name"
                        type="text"
                        required
                        placeholder="Ej: Frijol con Queso"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': errors.variant_name }"
                    />
                    <p v-if="errors.variant_name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ errors.variant_name }}
                    </p>
                </div>

                <!-- Atributos Dinámicos -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Atributos
                        </label>
                        <button
                            type="button"
                            @click="addAttribute"
                            class="inline-flex items-center px-3 py-1 text-xs font-medium text-purple-600 hover:text-purple-700 hover:bg-purple-50 dark:text-purple-400 dark:hover:text-purple-300 dark:hover:bg-purple-900/20 rounded-lg transition-colors"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Agregar Atributo
                        </button>
                    </div>
                    
                    <!-- Lista de atributos -->
                    <div v-if="form.attributes.length > 0" class="space-y-3">
                        <div 
                            v-for="(attr, index) in form.attributes" 
                            :key="index"
                            class="flex items-center gap-3"
                        >
                            <input
                                v-model="attr.key"
                                type="text"
                                placeholder="Nombre (ej: Masa)"
                                class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"
                            />
                            <input
                                v-model="attr.value"
                                type="text"
                                placeholder="Valor (ej: Maíz)"
                                class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"
                            />
                            <button
                                type="button"
                                @click="removeAttribute(index)"
                                class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                title="Eliminar atributo"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Empty state -->
                    <p v-else class="text-sm text-gray-400 dark:text-gray-500 text-center py-4">
                        Sin atributos. Haz clic en "Agregar Atributo" para definir características como tipo de masa, relleno, tamaño, etc.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Precio -->
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Precio <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400">$</span>
                            </div>
                            <input
                                id="price"
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                min="0.01"
                                required
                                placeholder="0.00"
                                class="w-full pl-7 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': errors.price }"
                            />
                        </div>
                        <p v-if="errors.price" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ errors.price }}
                        </p>
                    </div>

                    <!-- SKU -->
                    <div>
                        <label for="variant_sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            SKU (Opcional)
                        </label>
                        <input
                            id="variant_sku"
                            v-model="form.variant_sku"
                            type="text"
                            placeholder="Ej: PUP-MAIZ-FQ"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"
                            :class="{ 'border-red-500': errors.variant_sku }"
                        />
                        <p v-if="errors.variant_sku" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ errors.variant_sku }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Orden de visualización -->
                    <div>
                        <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Orden de Visualización
                        </label>
                        <input
                            id="display_order"
                            v-model="form.display_order"
                            type="number"
                            min="0"
                            placeholder="0"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"
                            :class="{ 'border-red-500': errors.display_order }"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Orden en que aparece en el POS (menor = primero)
                        </p>
                        <p v-if="errors.display_order" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ errors.display_order }}
                        </p>
                    </div>

                    <!-- Disponibilidad -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Disponibilidad
                        </label>
                        <div class="flex items-center h-10">
                            <button
                                type="button"
                                @click="form.is_available = !form.is_available"
                                class="relative inline-flex items-center"
                            >
                                <div
                                    class="w-11 h-6 rounded-full transition-colors"
                                    :class="form.is_available ? 'bg-green-600' : 'bg-gray-300 dark:bg-gray-600'"
                                >
                                    <div
                                        class="absolute top-0.5 left-0.5 bg-white w-5 h-5 rounded-full transition-transform"
                                        :class="form.is_available ? 'transform translate-x-5' : ''"
                                    ></div>
                                </div>
                                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ form.is_available ? 'Disponible' : 'No disponible' }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sección de Ingredientes -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ingredientes (Receta)
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Define los ingredientes necesarios para preparar esta variante
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="addRecipe"
                            :disabled="products.length === 0"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-600 hover:text-green-700 hover:bg-green-50 dark:text-green-400 dark:hover:text-green-300 dark:hover:bg-green-900/20 rounded-lg transition-colors border border-green-300 dark:border-green-600 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Agregar Ingrediente
                        </button>
                    </div>

                    <!-- Lista de ingredientes -->
                    <div v-if="form.recipes.length > 0" class="space-y-3">
                        <div
                            v-for="(recipe, index) in form.recipes"
                            :key="index"
                            class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3"
                        >
                            <div class="grid grid-cols-12 gap-3 items-start">
                                <!-- Producto -->
                                <div class="col-span-5">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                        Ingrediente
                                    </label>
                                    <select
                                        v-model="recipe.product_id"
                                        @change="onProductSelect(index)"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Seleccionar...</option>
                                        <optgroup
                                            v-for="category in [...new Set(getAvailableProducts(index).map(p => p.category?.name || 'Sin categoría'))]"
                                            :key="category"
                                            :label="category"
                                        >
                                            <option
                                                v-for="product in getAvailableProducts(index).filter(p => (p.category?.name || 'Sin categoría') === category)"
                                                :key="product.id"
                                                :value="product.id"
                                            >
                                                {{ product.name }} ({{ formatQuantity(product.current_stock) }} {{ product.unit_type }})
                                            </option>
                                        </optgroup>
                                    </select>
                                </div>

                                <!-- Cantidad -->
                                <div class="col-span-3">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                        Cantidad
                                    </label>
                                    <input
                                        v-model="recipe.quantity_needed"
                                        type="number"
                                        step="0.001"
                                        min="0.001"
                                        placeholder="0.000"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>

                                <!-- Unidad -->
                                <div class="col-span-3">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                        Unidad
                                    </label>
                                    <select
                                        v-model="recipe.unit"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">...</option>
                                        <option v-for="u in unitOptions" :key="u.value" :value="u.value">
                                            {{ u.value }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Eliminar -->
                                <div class="col-span-1 flex items-end justify-center">
                                    <button
                                        type="button"
                                        @click="removeRecipe(index)"
                                        class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                        title="Eliminar ingrediente"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Info del producto seleccionado -->
                            <div v-if="recipe.product_id && getSelectedProduct(recipe.product_id)" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Stock actual: {{ formatQuantity(getSelectedProduct(recipe.product_id).current_stock) }} {{ getSelectedProduct(recipe.product_id).unit_type }}
                                <span v-if="recipe.quantity_needed" class="ml-2 text-green-600 dark:text-green-400">
                                    → {{ Math.floor(getSelectedProduct(recipe.product_id).current_stock / parseFloat(recipe.quantity_needed || 1)) }} porciones posibles
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-else class="text-center py-6">
                        <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mb-2">
                            Sin ingredientes definidos
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            Las variantes sin ingredientes mostrarán "Stock: 999" en el POS
                        </p>
                    </div>

                    <!-- Stock calculado -->
                    <div v-if="form.recipes.length > 0" class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-green-800 dark:text-green-300">
                                Stock disponible calculado:
                            </span>
                            <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                {{ calculatedStock }} unidades
                            </span>
                        </div>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                            Basado en el ingrediente con menor disponibilidad
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        @click="handleClose"
                        :disabled="submitting"
                        class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="inline-flex items-center px-6 py-2.5 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors disabled:opacity-50"
                    >
                        <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isEditing ? 'Actualizar Variante' : 'Crear Variante' }}
                    </button>
                </div>
            </form>
        </div>
    </BaseModal>
</template>
