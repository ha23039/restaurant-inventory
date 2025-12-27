<template>
    <BaseModal :show="show" max-width="2xl" @close="handleClose">
        <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ isEditing ? 'Editar Cliente' : 'Crear Nuevo Cliente' }}
                </h3>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="px-6 py-4">
                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nombre Completo <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            autocomplete="name"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            :class="{ 'border-red-500': form.errors.name }"
                            placeholder="Ej: Juan Pérez"
                        />
                        <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Country Code -->
                        <div>
                            <label for="country_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Código <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="country_code"
                                v-model="form.country_code"
                                required
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                :class="{ 'border-red-500': form.errors.country_code }"
                            >
                                <option value="+52">+52 (México)</option>
                                <option value="+503">+503 (El Salvador)</option>
                                <option value="+56">+56 (Chile)</option>
                                <option value="+1">+1 (USA/Canada)</option>
                                <option value="+34">+34 (España)</option>
                                <option value="+54">+54 (Argentina)</option>
                                <option value="+55">+55 (Brasil)</option>
                            </select>
                            <div v-if="form.errors.country_code" class="text-red-500 text-sm mt-1">
                                {{ form.errors.country_code }}
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="md:col-span-2">
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Teléfono <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                required
                                autocomplete="tel"
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                :class="{ 'border-red-500': form.errors.phone }"
                                placeholder="1234567890"
                            />
                            <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">
                                {{ form.errors.phone }}
                            </div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Correo Electrónico
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            :class="{ 'border-red-500': form.errors.email }"
                            placeholder="cliente@ejemplo.com (opcional)"
                        />
                        <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Verified Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Verificación
                            </label>
                            <div class="flex items-center h-10">
                                <input
                                    id="is_verified"
                                    v-model="form.is_verified"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded"
                                />
                                <label for="is_verified" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Cliente verificado
                                </label>
                            </div>
                        </div>

                        <!-- Active Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Estado
                            </label>
                            <div class="flex items-center h-10">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded"
                                />
                                <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Cliente activo
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Notas
                        </label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            :class="{ 'border-red-500': form.errors.notes }"
                            placeholder="Notas adicionales sobre el cliente (opcional)"
                        ></textarea>
                        <div v-if="form.errors.notes" class="text-red-500 text-sm mt-1">
                            {{ form.errors.notes }}
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-600">
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
                        {{ isEditing ? 'Actualizar' : 'Crear' }} Cliente
                    </BaseButton>
                </div>
            </form>
        </div>
    </BaseModal>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/Base/BaseModal.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    customer: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close']);

const isEditing = computed(() => !!props.customer);

const form = useForm({
    name: '',
    phone: '',
    country_code: '+52',
    email: '',
    is_verified: false,
    is_active: true,
    notes: '',
});

// Reset form function - declared before watch to avoid hoisting issues
const resetForm = () => {
    form.reset();
    form.clearErrors();
};

// Watch for customer changes to populate form
watch(() => props.customer, (newCustomer) => {
    if (newCustomer) {
        form.name = newCustomer.name || '';
        form.phone = newCustomer.phone || '';
        form.country_code = newCustomer.country_code || '+52';
        form.email = newCustomer.email || '';
        form.is_verified = newCustomer.is_verified ?? false;
        form.is_active = newCustomer.is_active ?? true;
        form.notes = newCustomer.notes || '';
    } else {
        resetForm();
    }
}, { immediate: true });

const handleClose = () => {
    if (!form.processing) {
        resetForm();
        emit('close');
    }
};

const handleSubmit = () => {
    if (isEditing.value) {
        // Update existing customer
        form.put(route('customers.update', props.customer.id), {
            preserveScroll: true,
            onSuccess: () => {
                handleClose();
            },
        });
    } else {
        // Create new customer
        form.post(route('customers.store'), {
            preserveScroll: true,
            onSuccess: () => {
                handleClose();
            },
        });
    }
};
</script>
