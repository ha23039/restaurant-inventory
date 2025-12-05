<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseCard from '@/Components/Base/BaseCard.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import { useToast } from '@/composables';

const props = defineProps({
    settings: Object
});

const toast = useToast();

const form = ref({
    restaurant_name: props.settings.restaurant_name || '',
    restaurant_address: props.settings.restaurant_address || '',
    restaurant_phone: props.settings.restaurant_phone || '',
    restaurant_email: props.settings.restaurant_email || '',
    restaurant_tax_id: props.settings.restaurant_tax_id || '',
    primary_color: props.settings.primary_color || '#f97316',
    secondary_color: props.settings.secondary_color || '#ea580c',
    accent_color: props.settings.accent_color || '#fb923c',
    welcome_message: props.settings.welcome_message || '',
    footer_message: props.settings.footer_message || '',
    currency: props.settings.currency || 'MXN',
    timezone: props.settings.timezone || 'America/Mexico_City',
    logo: null,
});

const logoPreview = ref(props.settings.logo_path);
const processing = ref(false);

const handleLogoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.value.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const handleSubmit = () => {
    processing.value = true;

    const formData = new FormData();
    Object.keys(form.value).forEach(key => {
        if (form.value[key] !== null && form.value[key] !== '') {
            formData.append(key, form.value[key]);
        }
    });

    router.post(route('settings.update'), formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Configuración actualizada exitosamente');
            processing.value = false;
        },
        onError: (errors) => {
            toast.error('Error al actualizar configuración');
            console.error(errors);
            processing.value = false;
        },
    });
};
</script>

<template>
    <Head title="Configuración del Negocio" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Configuración del Negocio
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Personaliza la información de tu restaurante
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-6">
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Información General -->
                <BaseCard>
                    <div class="p-6 space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Información General</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Nombre del Restaurante *
                                </label>
                                <input
                                    v-model="form.restaurant_name"
                                    type="text"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    RFC / Tax ID
                                </label>
                                <input
                                    v-model="form.restaurant_tax_id"
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                                />
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Dirección
                                </label>
                                <input
                                    v-model="form.restaurant_address"
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Teléfono
                                </label>
                                <input
                                    v-model="form.restaurant_phone"
                                    type="tel"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Email
                                </label>
                                <input
                                    v-model="form.restaurant_email"
                                    type="email"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                                />
                            </div>
                        </div>
                    </div>
                </BaseCard>

                <!-- Logo -->
                <BaseCard>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Logo del Restaurante</h3>

                        <div class="flex items-center space-x-4">
                            <div v-if="logoPreview" class="flex-shrink-0">
                                <img :src="logoPreview" class="h-20 w-20 object-contain rounded border border-gray-300 dark:border-gray-600" alt="Logo" />
                            </div>

                            <div class="flex-1">
                                <input
                                    type="file"
                                    @change="handleLogoChange"
                                    accept="image/png,image/jpg,image/jpeg,image/svg+xml,image/webp"
                                    class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 dark:file:bg-purple-900 file:text-purple-700 dark:file:text-purple-200 hover:file:bg-purple-100 dark:hover:file:bg-purple-800"
                                />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">PNG, JPG, SVG, WEBP (MAX. 2MB)</p>
                            </div>
                        </div>
                    </div>
                </BaseCard>

                <!-- Colores de Marca -->
                <BaseCard>
                    <div class="p-6 space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Colores de Marca</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Color Primario
                                </label>
                                <div class="flex items-center space-x-2">
                                    <input
                                        v-model="form.primary_color"
                                        type="color"
                                        class="h-10 w-20 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                    <input
                                        v-model="form.primary_color"
                                        type="text"
                                        class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Color Secundario
                                </label>
                                <div class="flex items-center space-x-2">
                                    <input
                                        v-model="form.secondary_color"
                                        type="color"
                                        class="h-10 w-20 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                    <input
                                        v-model="form.secondary_color"
                                        type="text"
                                        class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Color de Acento
                                </label>
                                <div class="flex items-center space-x-2">
                                    <input
                                        v-model="form.accent_color"
                                        type="color"
                                        class="h-10 w-20 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                    <input
                                        v-model="form.accent_color"
                                        type="text"
                                        class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </BaseCard>

                <!-- Botones -->
                <div class="flex justify-end space-x-3">
                    <BaseButton
                        variant="secondary"
                        @click="$inertia.visit(route('dashboard'))"
                        type="button"
                    >
                        Cancelar
                    </BaseButton>

                    <BaseButton
                        variant="primary"
                        type="submit"
                        :disabled="processing"
                    >
                        {{ processing ? 'Guardando...' : 'Guardar Cambios' }}
                    </BaseButton>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
