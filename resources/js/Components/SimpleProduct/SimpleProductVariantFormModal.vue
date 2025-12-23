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

const isEditing = computed(() => !!props.variant);
const modalTitle = computed(() => isEditing.value ? 'Editar Variante' : 'Nueva Variante');

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
};

watch(() => props.variant, (newVariant) => {
    if (newVariant) {
        const attributesArray = [];
        if (newVariant.attributes && typeof newVariant.attributes === 'object') {
            Object.entries(newVariant.attributes).forEach(([key, value]) => {
                attributesArray.push({ key, value });
            });
        }

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

watch(() => props.show, (newVal) => {
    if (!newVal) {
        setTimeout(resetForm, 300);
    }
});

const addAttribute = () => {
    form.value.attributes.push({ key: '', value: '' });
};

const removeAttribute = (index) => {
    form.value.attributes.splice(index, 1);
};

const addRecipe = () => {
    form.value.recipes.push({
        product_id: null,
        quantity_needed: '',
        unit: 'pcs'
    });
};

const removeRecipe = (index) => {
    form.value.recipes.splice(index, 1);
};

const getProductName = (productId) => {
    const product = props.products.find(p => p.id === parseInt(productId));
    return product ? product.name : 'Producto desconocido';
};

const handleSubmit = () => {
    errors.value = {};
    submitting.value = true;

    const attributesObject = {};
    form.value.attributes.forEach(attr => {
        if (attr.key && attr.value) {
            attributesObject[attr.key] = attr.value;
        }
    });

    const data = {
        variant_name: form.value.variant_name,
        description: form.value.description,
        price: parseFloat(form.value.price),
        attributes: attributesObject,
        is_available: form.value.is_available,
        recipes: form.value.recipes.filter(r => r.product_id && r.quantity_needed)
    };

    const url = isEditing.value
        ? route('simple-products.variants.update', props.variant.id)
        : route('simple-products.variants.store', props.simpleProduct.id);

    const method = isEditing.value ? 'put' : 'post';

    router[method](url, data, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(isEditing.value ? 'Variante actualizada' : 'Variante creada');
            emit('saved');
            emit('close');
            resetForm();
        },
        onError: (serverErrors) => {
            errors.value = serverErrors;
            toast.error('Error al guardar la variante');
        },
        onFinish: () => {
            submitting.value = false;
        }
    });
};
</script>

<template>
    <BaseModal :show="show" @close="emit('close')" max-width="3xl">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                {{ modalTitle }}
            </h3>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Nombre de Variante -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Nombre de Variante *
                    </label>
                    <input
                        v-model="form.variant_name"
                        type="text"
                        required
                        placeholder="Ej: Coca Cola, Pepsi, Sprite"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-orange-500"
                    />
                    <p v-if="errors.variant_name" class="text-red-500 text-xs mt-1">{{ errors.variant_name }}</p>
                </div>

                <!-- Descripción -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Descripción
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="2"
                        placeholder="Descripción opcional"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-orange-500"
                    ></textarea>
                </div>

                <!-- Precio -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Precio *
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                        <input
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            min="0.01"
                            required
                            placeholder="0.00"
                            class="w-full pl-8 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-orange-500"
                        />
                    </div>
                    <p v-if="errors.price" class="text-red-500 text-xs mt-1">{{ errors.price }}</p>
                </div>

                <!-- Disponibilidad -->
                <div class="flex items-center gap-3">
                    <input
                        v-model="form.is_available"
                        type="checkbox"
                        id="is_available"
                        class="w-4 h-4 text-orange-600 rounded focus:ring-2 focus:ring-orange-500"
                    />
                    <label for="is_available" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Variante disponible para venta
                    </label>
                </div>

                <!-- Atributos -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Atributos (Opcionales)
                        </label>
                        <button
                            type="button"
                            @click="addAttribute"
                            class="text-xs text-orange-600 hover:text-orange-700 font-medium"
                        >
                            + Agregar Atributo
                        </button>
                    </div>
                    <div v-for="(attr, index) in form.attributes" :key="index" class="flex gap-2 mb-2">
                        <input
                            v-model="attr.key"
                            type="text"
                            placeholder="Nombre (ej: Tamaño)"
                            class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm"
                        />
                        <input
                            v-model="attr.value"
                            type="text"
                            placeholder="Valor (ej: 355ml)"
                            class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm"
                        />
                        <button
                            type="button"
                            @click="removeAttribute(index)"
                            class="px-3 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Recetas (Ingredientes) -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Ingredientes (Receta)
                        </label>
                        <button
                            type="button"
                            @click="addRecipe"
                            class="text-xs text-orange-600 hover:text-orange-700 font-medium"
                        >
                            + Agregar Ingrediente
                        </button>
                    </div>
                    <div v-if="form.recipes.length === 0" class="text-sm text-gray-500 dark:text-gray-400 text-center py-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        Sin ingredientes. El stock será ilimitado.
                    </div>
                    <div v-for="(recipe, index) in form.recipes" :key="index" class="grid grid-cols-12 gap-2 mb-2">
                        <div class="col-span-6">
                            <select
                                v-model="recipe.product_id"
                                required
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm"
                            >
                                <option :value="null">Seleccionar ingrediente</option>
                                <option v-for="product in products" :key="product.id" :value="product.id">
                                    {{ product.name }} ({{ product.current_stock }} {{ product.unit_type }})
                                </option>
                            </select>
                        </div>
                        <div class="col-span-3">
                            <input
                                v-model="recipe.quantity_needed"
                                type="number"
                                step="0.001"
                                min="0.001"
                                required
                                placeholder="Cantidad"
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm"
                            />
                        </div>
                        <div class="col-span-2">
                            <select
                                v-model="recipe.unit"
                                required
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm"
                            >
                                <option value="kg">kg</option>
                                <option value="g">g</option>
                                <option value="lt">lt</option>
                                <option value="ml">ml</option>
                                <option value="pcs">pcs</option>
                            </select>
                        </div>
                        <div class="col-span-1">
                            <button
                                type="button"
                                @click="removeRecipe(index)"
                                class="w-full h-full flex items-center justify-center text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        @click="emit('close')"
                        class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="px-6 py-2 text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ submitting ? 'Guardando...' : (isEditing ? 'Actualizar' : 'Crear') }}
                    </button>
                </div>
            </form>
        </div>
    </BaseModal>
</template>
