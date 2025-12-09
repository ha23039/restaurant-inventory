<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import SlideOver from './SlideOver.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    supplier: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close']);

const form = ref({
    name: '',
    contact_person: '',
    phone: '',
    email: '',
    address: '',
    is_active: true
});

const initialFormState = ref(null);
const hasChanges = ref(false);
const isSubmitting = ref(false);

// Watch form changes
watch(() => form.value, () => {
    if (initialFormState.value) {
        hasChanges.value = JSON.stringify(form.value) !== JSON.stringify(initialFormState.value);
    }
}, { deep: true });

// Reset form when closed or load existing supplier
watch(() => props.show, (newVal) => {
    if (!newVal) {
        setTimeout(() => {
            resetForm();
        }, 300);
    } else if (newVal) {
        if (props.supplier) {
            loadSupplierData();
        } else {
            resetForm();
        }
        setTimeout(() => {
            initialFormState.value = JSON.parse(JSON.stringify(form.value));
        }, 100);
    }
});

// Watch supplier changes
watch(() => props.supplier, () => {
    if (props.show && props.supplier) {
        loadSupplierData();
    }
}, { deep: true });

const loadSupplierData = () => {
    if (props.supplier) {
        form.value = {
            name: props.supplier.name || '',
            contact_person: props.supplier.contact_person || '',
            phone: props.supplier.phone || '',
            email: props.supplier.email || '',
            address: props.supplier.address || '',
            is_active: props.supplier.is_active ?? true
        };
    }
};

const resetForm = () => {
    form.value = {
        name: '',
        contact_person: '',
        phone: '',
        email: '',
        address: '',
        is_active: true
    };
    initialFormState.value = null;
    hasChanges.value = false;
    isSubmitting.value = false;
};

const handleClose = () => {
    if (hasChanges.value && !isSubmitting.value) {
        if (confirm('Aún no has guardado los cambios que realizaste, ¿Estás seguro que deseas salir?')) {
            emit('close');
        }
    } else {
        emit('close');
    }
};

const isEditMode = props.supplier !== null;

const handleSubmit = () => {
    if (!form.value.name) {
        alert('El nombre del proveedor es requerido');
        return;
    }

    if (!form.value.phone) {
        alert('El teléfono es requerido');
        return;
    }

    isSubmitting.value = true;

    const routeName = props.supplier ? 'suppliers.update' : 'suppliers.store';
    const routeParams = props.supplier ? props.supplier.id : undefined;
    const method = props.supplier ? 'put' : 'post';

    router[method](route(routeName, routeParams), form.value, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
        onError: (errors) => {
            console.error(errors);
            isSubmitting.value = false;
        }
    });
};
</script>

<template>
    <SlideOver
        :show="show"
        :prevent-close="hasChanges && !isSubmitting"
        @close="handleClose"
        @backdrop-click="handleClose"
        :title="supplier ? 'Editar Proveedor' : 'Nuevo Proveedor'"
        :subtitle="supplier ? 'Modifica la información del proveedor' : 'Registra un nuevo proveedor en el sistema'"
        size="md"
    >
        <div class="space-y-6">
            <!-- Nombre -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nombre del Proveedor <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                    placeholder="Ej: Distribuidora Alimenticia S.A."
                    required
                />
            </div>

            <!-- Persona de Contacto -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Persona de Contacto
                </label>
                <input
                    v-model="form.contact_person"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                    placeholder="Ej: Juan Pérez"
                />
            </div>

            <!-- Teléfono -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Teléfono <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.phone"
                    type="tel"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                    placeholder="Ej: (503) 1234-5678"
                    required
                />
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email
                </label>
                <input
                    v-model="form.email"
                    type="email"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                    placeholder="Ej: contacto@proveedor.com"
                />
            </div>

            <!-- Dirección -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Dirección
                </label>
                <textarea
                    v-model="form.address"
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                    placeholder="Dirección completa del proveedor..."
                ></textarea>
            </div>

            <!-- Estado Activo -->
            <div>
                <label class="flex items-center">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-800"
                    />
                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Proveedor activo
                    </span>
                </label>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Los proveedores inactivos no aparecerán en las listas de selección
                </p>
            </div>
        </div>

        <!-- Footer con botones -->
        <template #footer>
            <div class="flex items-center justify-end space-x-3">
                <button
                    @click="handleClose"
                    type="button"
                    :disabled="isSubmitting"
                    class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50"
                >
                    Cancelar
                </button>
                <button
                    @click="handleSubmit"
                    type="button"
                    :disabled="isSubmitting"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="isSubmitting">Guardando...</span>
                    <span v-else>{{ supplier ? 'Actualizar Proveedor' : 'Crear Proveedor' }}</span>
                </button>
            </div>
        </template>
    </SlideOver>
</template>
