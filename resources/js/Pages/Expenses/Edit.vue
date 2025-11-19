<script setup>
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseCard from '@/Components/Base/BaseCard.vue';
import ExpenseForm from '@/Components/Financial/ExpenseForm.vue';
import { useForm } from '@/composables';
import { useToast } from '@/composables';

const props = defineProps({
    expense: Object,
    categories: Array,
    suppliers: Array,
});

const toast = useToast();

// Extraer supplier_id de las notas si existe
const extractSupplierId = (notes) => {
    if (!notes) return null;
    const match = notes.match(/Proveedor ID: (\d+)/);
    return match ? parseInt(match[1]) : null;
};

const form = useForm({
    amount: props.expense.amount,
    category: props.expense.category,
    description: props.expense.description,
    notes: props.expense.notes ? props.expense.notes.replace(/Proveedor ID: \d+\n?/, '').trim() : '',
    expense_date: props.expense.flow_date,
    supplier_id: extractSupplierId(props.expense.notes),
});

const handleSubmit = () => {
    form.put(route('expenses.update', props.expense.id), {
        onSuccess: () => {
            toast.success('Gasto actualizado exitosamente');
        },
        onError: (errors) => {
            toast.error('Error al actualizar el gasto');
            console.error('Errores:', errors);
        },
    });
};

const handleCancel = () => {
    router.visit(route('expenses.index'));
};
</script>

<template>
    <Head title="Editar Gasto" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Gasto
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <BaseCard class="bg-white">
                    <div class="p-6">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900">
                                Información del Gasto
                            </h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Modifica la información del gasto. Los campos marcados con * son obligatorios.
                            </p>
                            <div class="mt-2 text-xs text-gray-500">
                                Registrado por: {{ expense.user.name }} el {{ new Date(expense.created_at).toLocaleDateString('es-MX') }}
                            </div>
                        </div>

                        <ExpenseForm
                            :form="form.data"
                            :categories="categories"
                            :suppliers="suppliers"
                            :processing="form.processing"
                            :errors="form.errors"
                            submit-label="Actualizar Gasto"
                            @submit="handleSubmit"
                            @cancel="handleCancel"
                        />
                    </div>
                </BaseCard>
            </div>
        </div>
    </AdminLayout>
</template>
