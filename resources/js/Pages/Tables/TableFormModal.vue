<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/Base/BaseModal.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    table: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close']);

const isEditing = ref(false);

const form = useForm({
    table_number: '',
    name: '',
    capacity: 4,
    notes: '',
    is_active: true,
});

// Watch for table changes to populate form
watch(() => props.table, (newTable) => {
    if (newTable) {
        isEditing.value = true;
        form.table_number = newTable.table_number || '';
        form.name = newTable.name || '';
        form.capacity = newTable.capacity || 4;
        form.notes = newTable.notes || '';
        form.is_active = newTable.is_active ?? true;
    } else {
        isEditing.value = false;
        resetForm();
    }
}, { immediate: true });

const resetForm = () => {
    form.reset();
    form.clearErrors();
};

const handleClose = () => {
    if (!form.processing) {
        resetForm();
        emit('close');
    }
};

const handleSubmit = () => {
    if (isEditing.value) {
        // Update existing table
        form.put(route('tables.update', props.table.id), {
            preserveScroll: true,
            onSuccess: () => {
                handleClose();
            },
        });
    } else {
        // Create new table
        form.post(route('tables.store'), {
            preserveScroll: true,
            onSuccess: () => {
                handleClose();
            },
        });
    }
};
</script>

<template>
    <BaseModal :show="show" max-width="2xl" @close="handleClose">
        <div class="bg-white rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ isEditing ? 'Editar Mesa' : 'Crear Nueva Mesa' }}
                </h3>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="px-6 py-4">
                <div class="space-y-6">
                    <!-- Table Number -->
                    <div>
                        <label for="table_number" class="block text-sm font-medium text-gray-700 mb-1">
                            Número de Mesa <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="table_number"
                            v-model="form.table_number"
                            type="text"
                            required
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            :class="{ 'border-red-500': form.errors.table_number }"
                            placeholder="Ej: 1, A1, VIP-1"
                        />
                        <div v-if="form.errors.table_number" class="text-red-500 text-sm mt-1">
                            {{ form.errors.table_number }}
                        </div>
                    </div>

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre (Opcional)
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            :class="{ 'border-red-500': form.errors.name }"
                            placeholder="Ej: Mesa Principal, Mesa Terraza"
                        />
                        <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <!-- Capacity -->
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">
                            Capacidad <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                id="capacity"
                                v-model="form.capacity"
                                type="number"
                                min="1"
                                max="50"
                                required
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                :class="{ 'border-red-500': form.errors.capacity }"
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Número de personas que puede acomodar la mesa</p>
                        <div v-if="form.errors.capacity" class="text-red-500 text-sm mt-1">
                            {{ form.errors.capacity }}
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Notas
                        </label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            :class="{ 'border-red-500': form.errors.notes }"
                            placeholder="Notas adicionales sobre la mesa..."
                        ></textarea>
                        <div v-if="form.errors.notes" class="text-red-500 text-sm mt-1">
                            {{ form.errors.notes }}
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Estado
                        </label>
                        <div class="flex items-center h-10">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                Mesa activa
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Las mesas inactivas no aparecerán en el sistema</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                    <BaseButton
                        type="button"
                        variant="secondary"
                        size="md"
                        @click="handleClose"
                        :disabled="form.processing"
                    >
                        Cancelar
                    </BaseButton>
                    <BaseButton
                        type="submit"
                        variant="primary"
                        size="md"
                        :loading="form.processing"
                        :disabled="form.processing"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                        {{ isEditing ? 'Actualizar' : 'Crear' }} Mesa
                    </BaseButton>
                </div>
            </form>
        </div>
    </BaseModal>
</template>
