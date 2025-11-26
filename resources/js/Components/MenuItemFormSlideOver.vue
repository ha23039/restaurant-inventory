<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
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

const emit = defineEmits(['close']);

// Form state
const form = ref({
    name: '',
    description: '',
    price: '',
    image_path: '',
    is_available: true,
    is_service: false,
});

const hasChanges = ref(false);
const processing = ref(false);

// Watch for changes
watch(() => form.value, () => {
    hasChanges.value = true;
}, { deep: true });

// Watch for menuItem changes (edit mode)
watch(() => props.menuItem, (newItem) => {
    if (newItem) {
        form.value = {
            name: newItem.name || '',
            description: newItem.description || '',
            price: newItem.price || '',
            image_path: newItem.image_path || '',
            is_available: newItem.is_available !== undefined ? newItem.is_available : true,
            is_service: newItem.is_service !== undefined ? newItem.is_service : false,
        };
        hasChanges.value = false;
    }
}, { immediate: true });

// Reset form when closed
watch(() => props.show, (newVal) => {
    if (!newVal) {
        setTimeout(() => {
            resetForm();
        }, 300);
    }
});

const isEditMode = computed(() => props.menuItem !== null);

const resetForm = () => {
    form.value = {
        name: '',
        description: '',
        price: '',
        image_path: '',
        is_available: true,
        is_service: false,
    };
    hasChanges.value = false;
};

const handleClose = () => {
    if (hasChanges.value) {
        if (confirm('¿Deseas descartar los cambios?')) {
            emit('close');
        }
    } else {
        emit('close');
    }
};

const handleBackdropClick = () => {
    handleClose();
};

const handleSubmit = () => {
    // Validación básica
    if (!form.value.name || !form.value.name.trim()) {
        alert('El nombre del platillo es requerido');
        return;
    }

    if (!form.value.price || parseFloat(form.value.price) <= 0) {
        alert('El precio debe ser mayor a 0');
        return;
    }

    processing.value = true;

    const url = isEditMode.value
        ? route('menu.items.update', props.menuItem.id)
        : route('menu.items.store');

    const method = isEditMode.value ? 'put' : 'post';

    router[method](url, {
        ...form.value,
        price: parseFloat(form.value.price),
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            resetForm();
        },
        onError: (errors) => {
            console.error('Errores de validación:', errors);
            alert('Error al guardar: ' + (errors.message || 'Verifica los datos'));
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<template>
    <SlideOver
        :show="show"
        :prevent-close="hasChanges"
        @close="handleClose"
        @backdrop-click="handleBackdropClick"
        :title="isEditMode ? 'Editar Platillo' : 'Nuevo Platillo'"
        subtitle="Completa la información del platillo del menú"
        size="md"
    >
        <div class="space-y-6">
            <!-- Nombre -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nombre del Platillo <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white"
                    placeholder="Ej: Hamburguesa Clásica"
                    maxlength="255"
                    required
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ form.name.length }}/255 caracteres
                </p>
            </div>

            <!-- Descripción -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Descripción
                </label>
                <textarea
                    v-model="form.description"
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white"
                    placeholder="Describe el platillo..."
                    maxlength="500"
                ></textarea>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ form.description.length }}/500 caracteres
                </p>
            </div>

            <!-- Precio -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Precio <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-lg font-semibold">$</span>
                    <input
                        v-model="form.price"
                        type="number"
                        step="0.01"
                        min="0.01"
                        class="w-full pl-10 pr-4 py-2 text-lg border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white"
                        placeholder="0.00"
                        required
                    />
                </div>
            </div>

            <!-- URL de Imagen (opcional) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    URL de Imagen
                </label>
                <input
                    v-model="form.image_path"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white"
                    placeholder="https://ejemplo.com/imagen.jpg"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Opcional: URL pública de la imagen del platillo
                </p>
            </div>

            <!-- Toggles -->
            <div class="space-y-4">
                <!-- Es Servicio -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            v-model="form.is_service"
                            id="is_service"
                            type="checkbox"
                            class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                        />
                    </div>
                    <div class="ml-3">
                        <label for="is_service" class="font-medium text-gray-700 dark:text-gray-300">
                            Es un Servicio
                        </label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Los servicios no afectan el inventario (Ej: Mesero adicional, Decoración)
                        </p>
                    </div>
                </div>

                <!-- Está Disponible -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            v-model="form.is_available"
                            id="is_available"
                            type="checkbox"
                            class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                        />
                    </div>
                    <div class="ml-3">
                        <label for="is_available" class="font-medium text-gray-700 dark:text-gray-300">
                            Disponible para la venta
                        </label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Si está desactivado, no aparecerá en el POS
                        </p>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                            Próximo paso: Recetas
                        </p>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            Después de crear el platillo, podrás asignarle ingredientes desde la sección de Recetas.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer con botones -->
        <template #footer>
            <div class="flex items-center justify-end space-x-3">
                <button
                    @click="handleClose"
                    type="button"
                    class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                    Cancelar
                </button>
                <button
                    @click="handleSubmit"
                    :disabled="processing"
                    type="button"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 disabled:bg-gray-400 rounded-lg transition-colors flex items-center"
                >
                    <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ processing ? 'Guardando...' : (isEditMode ? 'Actualizar' : 'Crear Platillo') }}
                </button>
            </div>
        </template>
    </SlideOver>
</template>
