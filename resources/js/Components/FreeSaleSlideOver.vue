<script setup>
import { ref, watch } from 'vue';
import SlideOver from './SlideOver.vue';
import ConfirmDialog from './ConfirmDialog.vue';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close', 'add']);
const { confirm } = useConfirmDialog();

// Form data
const form = ref({
    name: '',
    price: '',
    category: 'servicio'
});

const hasChanges = ref(false);

// Watch form changes
watch(() => form.value, () => {
    hasChanges.value = true;
}, { deep: true });

// Reset form when closed
watch(() => props.show, (newVal) => {
    if (!newVal) {
        setTimeout(() => {
            resetForm();
        }, 300);
    }
});

const categories = [
    { value: 'servicio', label: 'Servicio' },
    { value: 'evento', label: 'Evento' },
    { value: 'propina', label: 'Propina' },
    { value: 'ajuste', label: 'Ajuste' },
    { value: 'otro', label: 'Otro' }
];

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

const resetForm = () => {
    form.value = {
        name: '',
        price: '',
        category: 'servicio'
    };
    hasChanges.value = false;
};

const handleAdd = () => {
    if (!form.value.name || !form.value.name.trim()) {
        alert('Ingresa un nombre para la venta');
        return;
    }

    if (!form.value.price || parseFloat(form.value.price) <= 0) {
        alert('Ingresa un precio válido');
        return;
    }

    emit('add', {
        type: 'free',
        name: form.value.name.trim(),
        price: parseFloat(form.value.price),
        category: form.value.category,
        quantity: 1,
        subtotal: parseFloat(form.value.price)
    });

    emit('close');
};
</script>

<template>
    <SlideOver
        :show="show"
        :prevent-close="hasChanges"
        @close="handleClose"
        @backdrop-click="handleBackdropClick"
        title="Venta Libre"
        subtitle="Registra una venta sin producto predefinido"
        size="md"
    >
        <div class="space-y-6">
            <!-- Info Box -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                            Venta Flexible
                        </p>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            Úsala para propinas, eventos especiales, servicios adicionales o cualquier venta única que no esté en tu catálogo.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Categoría -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Categoría
                </label>
                <select
                    v-model="form.category"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                >
                    <option
                        v-for="cat in categories"
                        :key="cat.value"
                        :value="cat.value"
                    >
                        {{ cat.label }}
                    </option>
                </select>
            </div>

            <!-- Nombre/Descripción -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nombre/Descripción <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                    placeholder="Ej: Propina mesero Juan, Evento cumpleaños..."
                    maxlength="100"
                    required
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ form.name.length }}/100 caracteres
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
                        min="0"
                        class="w-full pl-10 pr-4 py-2 text-lg border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                        placeholder="0.00"
                        required
                    />
                </div>
            </div>

            <!-- Preview -->
            <div v-if="form.name && form.price" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Vista Previa:</p>
                        <p class="font-medium text-gray-900 dark:text-white mt-1">{{ form.name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ categories.find(c => c.value === form.category)?.label }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            ${{ parseFloat(form.price || 0).toFixed(2) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Ejemplos -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">💡 Ejemplos de uso:</p>
                <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                    <li>• Propina para el mesero ($50)</li>
                    <li>• Evento - Cumpleaños infantil ($5,000)</li>
                    <li>• Servicio - Mesero adicional 4h ($800)</li>
                    <li>• Ajuste - Descuento cortesía (-$100)</li>
                    <li>• Alquiler - Salón privado ($2,500)</li>
                </ul>
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
                    @click="handleAdd"
                    type="button"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                >
                    <svg class="w-5 h-5 inline-block mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Agregar al Carrito
                </button>
            </div>
        </template>
    </SlideOver>

</template>
