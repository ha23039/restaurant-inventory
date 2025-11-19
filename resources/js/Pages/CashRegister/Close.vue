<template>
    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Cerrar Caja
            </h2>
        </template>

        <div class="space-y-6">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-6">
                <!-- Resumen de la Sesión -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen de Turno</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <!-- Monto Inicial -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-600 mb-1">Monto Inicial</p>
                                <p class="text-2xl font-bold text-gray-900">${{ formatPrice(session.opening_amount) }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Apertura: {{ formatDateTime(session.opened_at) }}
                                </p>
                            </div>

                            <!-- Duración -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-600 mb-1">Duración del Turno</p>
                                <p class="text-2xl font-bold text-gray-900">{{ session.duration_hours }} hrs</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ session.transaction_count }} transacciones procesadas
                                </p>
                            </div>
                        </div>

                        <!-- Desglose de Ventas por Método de Pago -->
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="font-semibold text-gray-900 mb-3">Ventas por Método de Pago</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        Efectivo
                                    </span>
                                    <span class="text-lg font-semibold text-green-600">${{ formatPrice(session.total_cash_sales) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        Tarjeta
                                    </span>
                                    <span class="text-lg font-semibold text-blue-600">${{ formatPrice(session.total_card_sales) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                        Transferencia
                                    </span>
                                    <span class="text-lg font-semibold text-purple-600">${{ formatPrice(session.total_transfer_sales) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 pt-4 border-t-2 border-gray-300">
                                    <span class="text-base font-semibold text-gray-900">Total Ventas</span>
                                    <span class="text-xl font-bold text-gray-900">${{ formatPrice(session.total_all_sales) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Efectivo Esperado -->
                        <div class="mt-6 bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-blue-900">Efectivo Esperado en Caja</p>
                                    <p class="text-xs text-blue-700 mt-1">Inicial + Ventas en Efectivo</p>
                                </div>
                                <p class="text-2xl font-bold text-blue-900">${{ formatPrice(session.expected_closing) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Cierre -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Conteo de Efectivo</h3>

                        <form @submit.prevent="submitForm">
                            <!-- Monto de Cierre -->
                            <div class="mb-6">
                                <label for="closing_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                    Efectivo Contado en Caja <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-3 text-gray-500 text-lg">$</span>
                                    <input
                                        id="closing_amount"
                                        v-model="form.closing_amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full pl-8 pr-4 py-3 text-lg border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="0.00"
                                        autofocus
                                        @input="calculateDifference"
                                    />
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Cuenta todo el efectivo físico en la caja y registra el monto exacto
                                </p>
                                <InputError :message="errors.closing_amount" class="mt-2" />
                            </div>

                            <!-- Preview de Diferencia -->
                            <div v-if="form.closing_amount" class="mb-6">
                                <div
                                    :class="[
                                        'rounded-lg p-4 border-2',
                                        difference === 0 ? 'bg-green-50 border-green-300' :
                                        difference > 0 ? 'bg-yellow-50 border-yellow-300' :
                                        'bg-red-50 border-red-300'
                                    ]"
                                >
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-semibold" :class="[
                                                difference === 0 ? 'text-green-800' :
                                                difference > 0 ? 'text-yellow-800' :
                                                'text-red-800'
                                            ]">
                                                {{ differenceLabel }}
                                            </p>
                                            <p class="text-sm mt-1" :class="[
                                                difference === 0 ? 'text-green-700' :
                                                difference > 0 ? 'text-yellow-700' :
                                                'text-red-700'
                                            ]">
                                                {{ differenceDescription }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-3xl font-bold" :class="[
                                                difference === 0 ? 'text-green-800' :
                                                difference > 0 ? 'text-yellow-800' :
                                                'text-red-800'
                                            ]">
                                                ${{ Math.abs(difference).toFixed(2) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notas de Cierre -->
                            <div class="mb-6">
                                <label for="closing_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Notas de Cierre {{ difference !== 0 ? '(Recomendado explicar diferencia)' : '(Opcional)' }}
                                </label>
                                <textarea
                                    id="closing_notes"
                                    v-model="form.closing_notes"
                                    rows="3"
                                    maxlength="1000"
                                    class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                    :placeholder="difference !== 0
                                        ? 'Explica la razón de la diferencia (ej: error en cambio, billete roto, etc.)'
                                        : 'Opcional: Cualquier observación relevante del turno'"
                                ></textarea>
                                <div class="mt-1 flex justify-between">
                                    <p class="text-xs text-gray-500">
                                        {{ difference !== 0 ? 'Es importante explicar cualquier diferencia' : 'Opcional' }}
                                    </p>
                                    <span class="text-xs text-gray-400">{{ form.closing_notes?.length || 0 }}/1000</span>
                                </div>
                                <InputError :message="errors.closing_notes" class="mt-2" />
                            </div>

                            <!-- Advertencia si hay faltante grande -->
                            <div v-if="difference < -50" class="mb-6 rounded-lg bg-red-50 p-4 border border-red-200">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-red-600 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="text-sm text-red-800">
                                        <p class="font-semibold mb-1">Faltante Significativo Detectado</p>
                                        <p>Se recomienda revisar el conteo antes de cerrar la caja. Verifica billetes, monedas y que no haya errores.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="flex items-center justify-end space-x-3">
                                <Link
                                    :href="route('cashregister.index')"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                >
                                    Cancelar
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="processing || !form.closing_amount"
                                    class="px-6 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="!processing" class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        Cerrar Caja
                                    </span>
                                    <span v-else>Cerrando...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    session: Object,
    errors: Object,
});

const form = ref({
    session_id: props.session.id,
    closing_amount: '',
    closing_notes: '',
});

const processing = ref(false);
const difference = ref(0);

const calculateDifference = () => {
    const closingAmount = parseFloat(form.value.closing_amount) || 0;
    const expectedAmount = props.session.expected_closing;
    difference.value = closingAmount - expectedAmount;
};

const differenceLabel = computed(() => {
    if (difference.value === 0) return '✓ Caja Cuadrada';
    if (difference.value > 0) return '⚠ Sobrante';
    return '✗ Faltante';
});

const differenceDescription = computed(() => {
    if (difference.value === 0) return 'El efectivo contado coincide perfectamente con lo esperado';
    if (difference.value > 0) return 'Hay más efectivo del esperado';
    return 'Hay menos efectivo del esperado';
});

const formatPrice = (value) => {
    return parseFloat(value || 0).toFixed(2);
};

const formatDateTime = (datetime) => {
    return new Date(datetime).toLocaleString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const submitForm = () => {
    processing.value = true;

    router.post(route('cashregister.close'), form.value, {
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>
