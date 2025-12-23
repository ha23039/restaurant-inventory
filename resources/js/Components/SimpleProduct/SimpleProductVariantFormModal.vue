<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseModal from '../Base/BaseModal.vue';
import { useToast } from 'vue-toastification';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    simpleProduct: {
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
    description: '',
    price: '',
    attributes: [],
    is_available: true,
    recipes: []
});

const submitting = ref(false);
const errors = ref({});

// Variables para el grid de productos
const productSearch = ref('');
const productCategoryFilter = ref('');

// Categorías disponibles para el filtro
const productCategories = computed(() => {
    const cats = new Set();
    props.products.forEach(p => {
        if (p.category?.name) {
            cats.add(p.category.name);
        }
    });
    return Array.from(cats).sort();
});

// Productos filtrados por búsqueda y categoría
const filteredProductsList = computed(() => {
    let result = [...props.products];
    
    // Filtrar por búsqueda
    if (productSearch.value) {
        const searchLower = productSearch.value.toLowerCase();
        result = result.filter(p => 
            p.name.toLowerCase().includes(searchLower) ||
            (p.category?.name || '').toLowerCase().includes(searchLower)
        );
    }
    
    // Filtrar por categoría
    if (productCategoryFilter.value) {
        result = result.filter(p => p.category?.name === productCategoryFilter.value);
    }
    
    return result;
});

// Función para seleccionar un producto del grid
const selectProductForVariant = (product) => {
    // Agregar como receta con cantidad 1
    form.value.recipes = [{
        id: null,
        product_id: product.id,
        quantity_needed: 1,
        unit: product.unit_type
    }];
    // Limpiar búsqueda
    productSearch.value = '';
    productCategoryFilter.value = '';
};

const isEditing = computed(() => !!props.variant);
const modalTitle = computed(() => isEditing.value ? 'Editar Variante' : 'Nueva Variante');

// IMPORTANTE: resetForm debe estar antes de los watchers que la usan con immediate: true
const resetForm = () => {
    form.value = {
        variant_name: '',
        description: '',
        price: '',
        attributes: [],
        is_available: true,
        recipes: []
    };
    errors.value = {};
    // Reset grid de productos
    productSearch.value = '';
    productCategoryFilter.value = '';
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
            description: newVariant.description || '',
            price: newVariant.price || '',
            attributes: attributesArray,
            is_available: newVariant.is_available ?? true,
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
        ? route('simple-products.variants.update', props.variant.id)
        : route('simple-products.variants.store', props.simpleProduct.id);

    const method = isEditing.value ? 'put' : 'post';

    router[method](url, {
        variant_name: form.value.variant_name,
        description: form.value.description,
        price: parseFloat(form.value.price),
        attributes: attributesObject,
        is_available: form.value.is_available,
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
                        placeholder="Ej: Coca Cola 355ml, Sprite 600ml"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': errors.variant_name }"
                    />
                    <p v-if="errors.variant_name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ errors.variant_name }}
                    </p>
                </div>

                <!-- Descripción -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Descripción
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="2"
                        placeholder="Descripción opcional de la variante"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white resize-none"
                    ></textarea>
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
                            class="inline-flex items-center px-3 py-1 text-xs font-medium text-orange-600 hover:text-orange-700 hover:bg-orange-50 dark:text-orange-400 dark:hover:text-orange-300 dark:hover:bg-orange-900/20 rounded-lg transition-colors"
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
                                placeholder="Nombre (ej: Tamaño)"
                                class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white"
                            />
                            <input
                                v-model="attr.value"
                                type="text"
                                placeholder="Valor (ej: 355ml)"
                                class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white"
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
                        Sin atributos. Haz clic en "Agregar Atributo" para definir características como sabor, tamaño, presentación, etc.
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
                                class="w-full pl-7 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': errors.price }"
                            />
                        </div>
                        <p v-if="errors.price" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ errors.price }}
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

                <!-- Sección de Producto de Inventario -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Producto de Inventario
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Selecciona el producto del inventario que representa esta variante
                            </p>
                        </div>
                    </div>

                    <!-- Producto seleccionado -->
                    <div v-if="form.recipes.length > 0 && form.recipes[0].product_id" class="mb-4">
                        <div class="flex items-center justify-between bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-800 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ getSelectedProduct(form.recipes[0].product_id)?.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Stock: {{ formatQuantity(getSelectedProduct(form.recipes[0].product_id)?.current_stock) }} 
                                        {{ getSelectedProduct(form.recipes[0].product_id)?.unit_type }}
                                        <span class="mx-1">•</span>
                                        {{ getSelectedProduct(form.recipes[0].product_id)?.category?.name || 'Sin categoría' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <!-- Cantidad necesaria -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-xs text-gray-600 dark:text-gray-400">Cantidad:</label>
                                    <input
                                        v-model="form.recipes[0].quantity_needed"
                                        type="number"
                                        step="0.001"
                                        min="0.001"
                                        placeholder="1"
                                        class="w-20 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white text-center"
                                    />
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ getSelectedProduct(form.recipes[0].product_id)?.unit_type }}
                                    </span>
                                </div>
                                <!-- Botón cambiar -->
                                <button
                                    type="button"
                                    @click="removeRecipe(0)"
                                    class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                    title="Cambiar producto"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Stock calculado -->
                        <div v-if="form.recipes[0].quantity_needed > 0" class="mt-2 p-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-blue-700 dark:text-blue-300">Unidades disponibles para venta:</span>
                                <span class="font-bold text-blue-600 dark:text-blue-400">{{ calculatedStock }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Selector de producto (cuando no hay producto seleccionado) -->
                    <div v-else>
                        <!-- Búsqueda -->
                        <div class="flex gap-2 mb-3">
                            <div class="relative flex-1">
                                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input
                                    v-model="productSearch"
                                    type="text"
                                    placeholder="Buscar producto..."
                                    class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white"
                                />
                                <button 
                                    v-if="productSearch"
                                    @click="productSearch = ''"
                                    type="button"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <select
                                v-model="productCategoryFilter"
                                class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Todas</option>
                                <option v-for="cat in productCategories" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                        </div>

                        <!-- Grid de productos -->
                        <div class="max-h-[250px] overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div v-if="filteredProductsList.length === 0" class="p-6 text-center text-gray-500">
                                <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="text-sm">No se encontraron productos</p>
                            </div>
                            
                            <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-2">
                                <button
                                    v-for="product in filteredProductsList.slice(0, 12)"
                                    :key="product.id"
                                    type="button"
                                    @click="selectProductForVariant(product)"
                                    class="p-3 text-left border-2 rounded-lg transition-all hover:border-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20"
                                    :class="product.current_stock > 0 
                                        ? 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800' 
                                        : 'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 opacity-60'"
                                >
                                    <p class="font-medium text-sm text-gray-900 dark:text-white truncate">
                                        {{ product.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ product.category?.name || 'Sin categoría' }}
                                    </p>
                                    <div class="mt-1 flex items-center justify-between">
                                        <span 
                                            class="text-xs font-medium"
                                            :class="product.current_stock > 10 ? 'text-green-600' : product.current_stock > 0 ? 'text-yellow-600' : 'text-red-600'"
                                        >
                                            {{ formatQuantity(product.current_stock) }} {{ product.unit_type }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            ${{ parseFloat(product.unit_cost || 0).toFixed(2) }}
                                        </span>
                                    </div>
                                </button>
                            </div>
                            
                            <!-- Más productos -->
                            <div v-if="filteredProductsList.length > 12" class="p-2 text-center border-t border-gray-200 dark:border-gray-700">
                                <p class="text-xs text-gray-500">
                                    Mostrando 12 de {{ filteredProductsList.length }} productos. Usa el buscador para encontrar más.
                                </p>
                            </div>
                        </div>
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
                        class="inline-flex items-center px-6 py-2.5 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition-colors disabled:opacity-50"
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
