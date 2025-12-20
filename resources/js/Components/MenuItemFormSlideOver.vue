<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import SlideOver from './SlideOver.vue';
import ConfirmDialog from './ConfirmDialog.vue';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

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
const { confirm } = useConfirmDialog();

// Form state
const form = ref({
    name: '',
    description: '',
    price: '',
    image_path: '',
    is_available: true,
    is_service: false,
    has_variants: false,
});

const initialFormState = ref(null);
const hasChanges = ref(false);
const processing = ref(false);

// Image upload state
const imageMode = ref('url'); // 'url' | 'file'
const imageFile = ref(null);
const imagePreview = ref(null);

// Watch for changes - comparar con estado inicial
watch(() => form.value, () => {
    if (initialFormState.value) {
        hasChanges.value = JSON.stringify(form.value) !== JSON.stringify(initialFormState.value);
    }
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
            has_variants: newItem.has_variants !== undefined ? newItem.has_variants : false,
        };
        // Guardar estado inicial
        initialFormState.value = JSON.parse(JSON.stringify(form.value));
        hasChanges.value = false;
    }
}, { immediate: true });

// Reset form when closed
watch(() => props.show, (newVal) => {
    if (!newVal) {
        setTimeout(() => {
            resetForm();
        }, 300);
    } else if (newVal && !props.menuItem) {
        // Modo crear - resetear primero, luego guardar estado inicial
        resetForm();
        setTimeout(() => {
            initialFormState.value = JSON.parse(JSON.stringify(form.value));
        }, 100);
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
        has_variants: false,
    };
    initialFormState.value = null;
    hasChanges.value = false;
    imageFile.value = null;
    imagePreview.value = null;
    imageMode.value = 'url';
};

const handleImageFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        imageFile.value = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    imageFile.value = null;
    imagePreview.value = null;
    form.value.image_path = '';
};

const handleClose = async () => {
    if (hasChanges.value) {
        const confirmed = await confirm({
            title: '¿Descartar cambios?',
            message: 'Tienes cambios sin guardar. Si sales ahora, se perderán.',
            confirmText: 'Descartar',
            type: 'warning'
        });
        if (confirmed) {
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

    // Prepare data
    let data;
    const useFormData = imageMode.value === 'file' && imageFile.value;

    if (useFormData) {
        data = new FormData();
        data.append('name', form.value.name);
        data.append('description', form.value.description || '');
        data.append('price', parseFloat(form.value.price));
        data.append('is_available', form.value.is_available ? '1' : '0');
        data.append('is_service', form.value.is_service ? '1' : '0');
        data.append('has_variants', form.value.has_variants ? '1' : '0');
        data.append('image', imageFile.value);
        if (isEditMode.value) {
            data.append('_method', 'PUT');
        }
    } else {
        data = {
            ...form.value,
            price: parseFloat(form.value.price),
        };
    }

    router[useFormData ? 'post' : method](url, data, {
        preserveState: true,
        preserveScroll: true,
        forceFormData: useFormData,
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

            <!-- Imagen del Platillo -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Imagen del Platillo
                </label>

                <!-- Tabs para seleccionar modo -->
                <div class="flex space-x-2 mb-3">
                    <button
                        @click.prevent="imageMode = 'url'"
                        type="button"
                        :class="[
                            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                            imageMode === 'url'
                                ? 'bg-orange-600 text-white'
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        URL
                    </button>
                    <button
                        @click.prevent="imageMode = 'file'"
                        type="button"
                        :class="[
                            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                            imageMode === 'file'
                                ? 'bg-orange-600 text-white'
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Subir Archivo
                    </button>
                </div>

                <!-- URL Input -->
                <div v-if="imageMode === 'url'">
                    <input
                        v-model="form.image_path"
                        type="text"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white"
                        placeholder="https://ejemplo.com/imagen.jpg"
                    />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        URL pública de la imagen del platillo
                    </p>
                </div>

                <!-- File Upload -->
                <div v-else class="space-y-3">
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <div v-if="!imagePreview" class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-semibold">Click para subir</span> o arrastra aquí
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, WEBP (MAX. 2MB)</p>
                            </div>

                            <img v-else :src="imagePreview" class="h-32 object-contain rounded-lg" alt="Preview" />

                            <input
                                type="file"
                                class="hidden"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                @change="handleImageFileChange"
                            />
                        </label>
                    </div>

                    <button
                        v-if="imagePreview"
                        @click.prevent="removeImage"
                        type="button"
                        class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                    >
                        Eliminar imagen
                    </button>
                </div>
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

                <!-- Tiene Variantes -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            v-model="form.has_variants"
                            id="has_variants"
                            type="checkbox"
                            class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded dark:border-gray-600"
                        />
                    </div>
                    <div class="ml-3">
                        <label for="has_variants" class="font-medium text-gray-700 dark:text-gray-300">
                            Tiene Variantes
                        </label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Habilita opciones como tipo de masa, relleno, tamaño, etc.
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
