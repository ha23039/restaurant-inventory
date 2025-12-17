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
    }
});

const emit = defineEmits(['close', 'saved']);
const toast = useToast();

const form = ref({
    variant_name: '',
    variant_sku: '',
    price: '',
    masa: 'maiz',
    is_available: true,
    display_order: 0
});

const submitting = ref(false);
const errors = ref({});

const isEditing = computed(() => !!props.variant);
const modalTitle = computed(() => isEditing.value ? 'Editar Variante' : 'Nueva Variante');

// Watch for variant changes to populate form
watch(() => props.variant, (newVariant) => {
    if (newVariant) {
        form.value = {
            variant_name: newVariant.variant_name || '',
            variant_sku: newVariant.variant_sku || '',
            price: newVariant.price || '',
            masa: newVariant.attributes?.masa || 'maiz',
            is_available: newVariant.is_available ?? true,
            display_order: newVariant.display_order || 0
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

const resetForm = () => {
    form.value = {
        variant_name: '',
        variant_sku: '',
        price: '',
        masa: 'maiz',
        is_available: true,
        display_order: 0
    };
    errors.value = {};
};

const handleSubmit = () => {
    if (submitting.value) return;

    submitting.value = true;
    errors.value = {};

    const url = isEditing.value
        ? route('menu.variants.update', props.variant.id)
        : route('menu.variants.store', props.menuItem.id);

    const method = isEditing.value ? 'put' : 'post';

    router[method](url, {
        variant_name: form.value.variant_name,
        variant_sku: form.value.variant_sku,
        price: parseFloat(form.value.price),
        attributes: { masa: form.value.masa },
        is_available: form.value.is_available,
        display_order: parseInt(form.value.display_order) || 0
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

                <!-- Tipo de Masa -->
                <div>
                    <label for="masa" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tipo de Masa <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="masa"
                        v-model="form.masa"
                        required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': errors.masa }"
                    >
                        <option value="maiz">Masa de Maíz</option>
                        <option value="arroz">Masa de Arroz</option>
                    </select>
                    <p v-if="errors.masa" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ errors.masa }}
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

                <!-- Info box -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <div class="text-sm text-blue-800 dark:text-blue-300">
                            <p class="font-semibold mb-1">Gestión de Recetas</p>
                            <p>Después de crear la variante, podrás asignar los ingredientes en la sección de Recetas.</p>
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
