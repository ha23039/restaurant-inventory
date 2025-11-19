<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BaseCard from '@/Components/Base/BaseCard.vue';
import ExpenseForm from '@/Components/Financial/ExpenseForm.vue';
import { useForm } from '@/composables';
import { useToast } from '@/composables';

const props = defineProps({
    categories: Array,
    suppliers: Array,
});

const toast = useToast();

const form = useForm({
    amount: '',
    category: '',
    description: '',
    notes: '',
    expense_date: new Date().toISOString().split('T')[0],
    supplier_id: null,
});

const handleSubmit = () => {
    form.post(route('expenses.store'), {
        onSuccess: () => {
            toast.success('Gasto registrado exitosamente');
        },
        onError: (errors) => {
            toast.error('Error al registrar el gasto');
            console.error('Errores:', errors);
        },
    });
};

const handleCancel = () => {
    router.visit(route('expenses.index'));
};
</script>

<template>
    <Head title="Nuevo Gasto" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Registrar Nuevo Gasto
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
                                Registra un nuevo gasto en el sistema. Los campos marcados con * son obligatorios.
                            </p>
                        </div>

                        <ExpenseForm
                            :form="form.data"
                            :categories="categories"
                            :suppliers="suppliers"
                            :processing="form.processing"
                            :errors="form.errors"
                            submit-label="Registrar Gasto"
                            @submit="handleSubmit"
                            @cancel="handleCancel"
                        />
                    </div>
                </BaseCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
