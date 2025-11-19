<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Abrir Caja
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Header con icono -->
                        <div class="mb-6 flex items-center space-x-3">
                            <div class="rounded-full bg-green-100 p-3">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Iniciar Sesión de Caja</h3>
                                <p class="text-sm text-gray-500">Registra el efectivo inicial para comenzar tu turno</p>
                            </div>
                        </div>

                        <!-- Formulario -->
                        <form @submit.prevent="submitForm">
                            <!-- Monto Inicial -->
                            <div class="mb-6">
                                <label for="opening_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                    Monto Inicial en Efectivo <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-3 text-gray-500 text-lg">$</span>
                                    <input
                                        id="opening_amount"
                                        v-model="form.opening_amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full pl-8 pr-4 py-3 text-lg border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
                                        placeholder="0.00"
                                        autofocus
                                    />
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Cuenta el efectivo físico en la caja y registra el monto exacto
                                </p>
                                <InputError :message="errors.opening_amount" class="mt-2" />
                            </div>

                            <!-- Notas de Apertura -->
                            <div class="mb-6">
                                <label for="opening_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Notas (Opcional)
                                </label>
                                <textarea
                                    id="opening_notes"
                                    v-model="form.opening_notes"
                                    rows="3"
                                    maxlength="1000"
                                    class="w-full border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
                                    placeholder="Ej: Recibí caja del turno anterior, billetes de baja denominación, etc."
                                ></textarea>
                                <div class="mt-1 flex justify-between">
                                    <p class="text-xs text-gray-500">Opcional: Registra cualquier observación relevante</p>
                                    <span class="text-xs text-gray-400">{{ form.opening_notes?.length || 0 }}/1000</span>
                                </div>
                                <InputError :message="errors.opening_notes" class="mt-2" />
                            </div>

                            <!-- Información Importante -->
                            <div class="mb-6 rounded-lg bg-blue-50 p-4 border border-blue-200">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-blue-600 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="text-sm text-blue-800">
                                        <p class="font-semibold mb-1">Importante:</p>
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>Verifica que el monto registrado coincida con el efectivo físico</li>
                                            <li>Una vez abierta la caja, podrás procesar ventas</li>
                                            <li>Al cerrar, se calculará automáticamente si hay diferencias</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="flex items-center justify-end space-x-3">
                                <Link
                                    :href="route('dashboard')"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                >
                                    Cancelar
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="processing"
                                    class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="!processing" class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                        </svg>
                                        Abrir Caja
                                    </span>
                                    <span v-else>Abriendo...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    errors: Object,
});

const form = ref({
    opening_amount: '',
    opening_notes: '',
});

const processing = ref(false);

const submitForm = () => {
    processing.value = true;

    router.post(route('cashregister.store'), form.value, {
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>
