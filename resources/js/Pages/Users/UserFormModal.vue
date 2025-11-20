<template>
    <BaseModal :show="show" max-width="2xl" @close="handleClose">
        <div class="bg-white rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ isEditing ? 'Editar Usuario' : 'Crear Nuevo Usuario' }}
                </h3>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="px-6 py-4">
                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre Completo <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            autocomplete="name"
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            :class="{ 'border-red-500': form.errors.name }"
                            placeholder="Ej: Juan Pérez"
                        />
                        <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Correo Electrónico <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="email"
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            :class="{ 'border-red-500': form.errors.email }"
                            placeholder="usuario@ejemplo.com"
                        />
                        <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Teléfono
                        </label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            autocomplete="tel"
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            :class="{ 'border-red-500': form.errors.phone }"
                            placeholder="(555) 123-4567"
                        />
                        <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">
                            {{ form.errors.phone }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Role -->
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                                Rol <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="role"
                                v-model="form.role"
                                required
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                :class="{ 'border-red-500': form.errors.role }"
                            >
                                <option value="">Seleccionar rol</option>
                                <template v-for="role in roles" :key="role.value">
                                    <option v-if="role && role.label" :value="role.value">
                                        {{ role.label }}
                                    </option>
                                </template>
                            </select>
                            <div v-if="form.errors.role" class="text-red-500 text-sm mt-1">
                                {{ form.errors.role }}
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
                                    Usuario activo
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Password (only for create or when editing to change password) -->
                    <div v-if="!isEditing">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Contraseña <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            :required="!isEditing"
                            autocomplete="new-password"
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            :class="{ 'border-red-500': form.errors.password }"
                            placeholder="Mínimo 8 caracteres"
                        />
                        <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">
                            {{ form.errors.password }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            La contraseña debe tener al menos 8 caracteres
                        </p>
                    </div>

                    <!-- Password confirmation (only for create) -->
                    <div v-if="!isEditing">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirmar Contraseña <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            :required="!isEditing"
                            autocomplete="new-password"
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            placeholder="Confirmar contraseña"
                        />
                    </div>

                    <!-- Info for editing -->
                    <div v-if="isEditing" class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-sm text-blue-700">
                                <p class="font-medium">Nota sobre contraseña</p>
                                <p class="mt-1">
                                    Para cambiar la contraseña de este usuario, usa el botón "Resetear Contraseña" en la tabla de usuarios.
                                </p>
                            </div>
                        </div>
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
                        {{ isEditing ? 'Actualizar' : 'Crear' }} Usuario
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
    user: {
        type: Object,
        default: null
    },
    roles: {
        type: Array,
        required: true
    }
});

const emit = defineEmits(['close']);

const isEditing = computed(() => !!props.user);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    role: '',
    is_active: true,
    password: '',
    password_confirmation: '',
});

// Reset form function - declared before watch to avoid hoisting issues
const resetForm = () => {
    form.reset();
    form.clearErrors();
};

// Watch for user changes to populate form
watch(() => props.user, (newUser) => {
    if (newUser) {
        form.name = newUser.name || '';
        form.email = newUser.email || '';
        form.phone = newUser.phone || '';
        form.role = newUser.role || '';
        form.is_active = newUser.is_active ?? true;
        form.password = '';
        form.password_confirmation = '';
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
        // Update existing user
        form.put(route('users.update', props.user.id), {
            preserveScroll: true,
            onSuccess: () => {
                handleClose();
            },
        });
    } else {
        // Create new user
        form.post(route('users.store'), {
            preserveScroll: true,
            onSuccess: () => {
                handleClose();
            },
        });
    }
};
</script>
