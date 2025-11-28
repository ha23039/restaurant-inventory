<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseCard from '@/Components/Base/BaseCard.vue';
import BaseBadge from '@/Components/Base/BaseBadge.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import { useToast } from '@/composables';

const props = defineProps({
    expense: Object,
});

const toast = useToast();

const deleteExpense = () => {
    if (!confirm('¿Estás seguro de eliminar este gasto? Esta acción no se puede deshacer.')) {
        return;
    }

    router.delete(route('expenses.destroy', props.expense.id), {
        onSuccess: () => {
            toast.success('Gasto eliminado exitosamente');
            router.visit(route('expenses.index'));
        },
        onError: () => {
            toast.error('Error al eliminar el gasto');
        },
    });
};

const getCategoryVariant = (category) => {
    const variants = {
        compras: 'primary',
        compra_productos_insumos: 'success',
        gastos_operativos: 'warning',
        gastos_admin: 'info',
        mantenimiento: 'danger',
        marketing: 'success',
        otros: 'default',
    };
    return variants[category] || 'default';
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2,
    }).format(value);
};

// Extraer productos de las notas si existen
const extractProducts = (notes) => {
    if (!notes) return [];

    const lines = notes.split('\n');
    const products = [];
    let inProductsSection = false;

    for (const line of lines) {
        if (line.includes('Productos comprados:')) {
            inProductsSection = true;
            continue;
        }

        if (inProductsSection && line.trim().startsWith('-')) {
            products.push(line.trim().substring(1).trim());
        }
    }

    return products;
};

const extractPaymentMethod = (notes) => {
    if (!notes) return null;
    const match = notes.match(/Método de pago: (\w+)/);
    return match ? match[1] : null;
};
</script>

<template>
    <Head title="Detalles del Gasto" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detalles del Gasto
                </h2>
                <div class="flex items-center gap-3">
                    <Link :href="route('expenses.index')">
                        <BaseButton variant="secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Volver
                        </BaseButton>
                    </Link>
                    <Link :href="route('expenses.edit', expense.id)">
                        <BaseButton variant="primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Editar
                        </BaseButton>
                    </Link>
                    <BaseButton variant="danger" @click="deleteExpense">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Eliminar
                    </BaseButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Información Principal -->
                <BaseCard class="bg-white">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-6">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">
                                    {{ expense.description }}
                                </h3>
                                <div class="flex items-center gap-3">
                                    <BaseBadge :variant="getCategoryVariant(expense.category)" size="lg">
                                        {{ expense.category_label }}
                                    </BaseBadge>
                                    <span class="text-sm text-gray-500">
                                        {{ expense.flow_date_formatted }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500 mb-1">Monto Total</div>
                                <div class="text-4xl font-bold text-red-600">
                                    {{ formatCurrency(expense.amount) }}
                                </div>
                            </div>
                        </div>

                        <!-- Detalles en Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-200 pt-6">
                            <!-- Usuario que registró -->
                            <div v-if="expense.user">
                                <dt class="text-sm font-medium text-gray-500 mb-1">
                                    Registrado por
                                </dt>
                                <dd class="text-base text-gray-900">
                                    {{ expense.user.name }}
                                    <span class="text-xs text-gray-500 ml-2">({{ expense.user.role }})</span>
                                </dd>
                            </div>

                            <!-- Fecha de registro -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">
                                    Fecha de Registro
                                </dt>
                                <dd class="text-base text-gray-900">
                                    {{ new Date(expense.created_at).toLocaleDateString('es-MX', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    }) }}
                                </dd>
                            </div>

                            <!-- Método de pago -->
                            <div v-if="extractPaymentMethod(expense.notes)">
                                <dt class="text-sm font-medium text-gray-500 mb-1">
                                    Método de Pago
                                </dt>
                                <dd class="text-base text-gray-900 capitalize">
                                    {{ extractPaymentMethod(expense.notes) }}
                                </dd>
                            </div>

                            <!-- ID del gasto -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">
                                    ID del Gasto
                                </dt>
                                <dd class="text-base text-gray-900 font-mono">
                                    #{{ expense.id }}
                                </dd>
                            </div>
                        </div>
                    </div>
                </BaseCard>

                <!-- Productos (si es compra de productos) -->
                <BaseCard v-if="expense.category === 'compra_productos_insumos' && extractProducts(expense.notes).length > 0" class="bg-white">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Productos Comprados
                        </h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <ul class="space-y-2">
                                <li
                                    v-for="(product, index) in extractProducts(expense.notes)"
                                    :key="index"
                                    class="flex items-center text-sm text-gray-700"
                                >
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    {{ product }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </BaseCard>

                <!-- Notas -->
                <BaseCard v-if="expense.notes" class="bg-white">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Notas Adicionales
                        </h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ expense.notes }}</p>
                        </div>
                    </div>
                </BaseCard>

                <!-- Historial de Modificaciones -->
                <BaseCard v-if="expense.updated_at !== expense.created_at" class="bg-white">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Historial de Modificaciones
                        </h3>
                        <div class="text-sm text-gray-600">
                            <p>
                                <span class="font-medium">Última actualización:</span>
                                {{ new Date(expense.updated_at).toLocaleDateString('es-MX', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }) }}
                            </p>
                        </div>
                    </div>
                </BaseCard>
            </div>
        </div>
    </AdminLayout>
</template>
