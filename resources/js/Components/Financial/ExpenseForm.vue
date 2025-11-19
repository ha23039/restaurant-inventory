<script setup>
import { computed } from 'vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseTextarea from '@/Components/Base/BaseTextarea.vue';
import FormGroup from '@/Components/Forms/FormGroup.vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    suppliers: {
        type: Array,
        default: () => [],
    },
    processing: {
        type: Boolean,
        default: false,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
    submitLabel: {
        type: String,
        default: 'Guardar Gasto',
    },
});

const emit = defineEmits(['submit', 'cancel']);

const categoryOptions = computed(() => {
    return props.categories.map(cat => ({
        value: cat.value,
        label: cat.label,
    }));
});

const supplierOptions = computed(() => {
    return [
        { value: null, label: 'Sin proveedor' },
        ...props.suppliers.map(supplier => ({
            value: supplier.id,
            label: supplier.name,
        })),
    ];
});

const handleSubmit = () => {
    emit('submit');
};

const handleCancel = () => {
    emit('cancel');
};
</script>

<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Monto y Fecha -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <FormInput
                v-model="form.amount"
                type="number"
                step="0.01"
                min="0.01"
                label="Monto *"
                placeholder="0.00"
                :error="errors.amount"
                required
            >
                <template #icon>
                    <span class="text-gray-500">$</span>
                </template>
            </FormInput>

            <FormInput
                v-model="form.expense_date"
                type="date"
                label="Fecha del Gasto *"
                :error="errors.expense_date"
                :max="new Date().toISOString().split('T')[0]"
                required
            />
        </div>

        <!-- Categoría -->
        <FormSelect
            v-model="form.category"
            :options="categoryOptions"
            label="Categoría *"
            placeholder="Selecciona una categoría"
            :error="errors.category"
            required
        />

        <!-- Descripción -->
        <FormInput
            v-model="form.description"
            type="text"
            label="Descripción *"
            placeholder="Ej: Pago de luz del mes de noviembre"
            :error="errors.description"
            required
        />

        <!-- Proveedor (opcional) -->
        <FormSelect
            v-model="form.supplier_id"
            :options="supplierOptions"
            label="Proveedor (opcional)"
            placeholder="Selecciona un proveedor"
            :error="errors.supplier_id"
        />

        <!-- Notas adicionales -->
        <FormGroup
            label="Notas Adicionales"
            :error="errors.notes"
        >
            <BaseTextarea
                v-model="form.notes"
                :rows="4"
                placeholder="Información adicional sobre este gasto..."
                :maxlength="1000"
                show-count
                :error="errors.notes"
            />
        </FormGroup>

        <!-- Resumen -->
        <div v-if="form.amount && form.category" class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-900 mb-2">Resumen</h3>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Categoría:</span>
                    <span class="font-medium">
                        {{ categories.find(c => c.value === form.category)?.label || '-' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Monto:</span>
                    <span class="font-semibold text-red-600">
                        -${{ parseFloat(form.amount || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Fecha:</span>
                    <span class="font-medium">
                        {{ form.expense_date || '-' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Error general -->
        <div v-if="errors.error" class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-800">{{ errors.error }}</p>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
            <BaseButton
                type="button"
                variant="secondary"
                @click="handleCancel"
                :disabled="processing"
            >
                Cancelar
            </BaseButton>

            <BaseButton
                type="submit"
                variant="primary"
                :loading="processing"
                :disabled="processing"
            >
                {{ submitLabel }}
            </BaseButton>
        </div>
    </form>
</template>
