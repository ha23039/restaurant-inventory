<template>
    <Head :title="product ? 'Editar Producto' : 'Nuevo Producto'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link 
                    :href="route('inventory.products.index')"
                    class="text-gray-500 hover:text-gray-700"
                >
                    ← Volver a Productos
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ product ? '✏️ Editar Producto' : '➕ Nuevo Producto' }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Información básica -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre del Producto *
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    :class="{ 'border-red-500': form.errors.name }"
                                />
                                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Categoría -->
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Categoría *
                                </label>
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    required
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    :class="{ 'border-red-500': form.errors.category_id }"
                                >
                                    <option value="">Seleccionar categoría</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.category_id }}
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                :class="{ 'border-red-500': form.errors.description }"
                            ></textarea>
                            <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">
                                {{ form.errors.description }}
                            </div>
                        </div>

                        <!-- Unidad y Stock -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Tipo de unidad -->
                            <div>
                                <label for="unit_type" class="block text-sm font-medium text-gray-700 mb-1">
                                    Unidad de Medida *
                                </label>
                                <select
                                    id="unit_type"
                                    v-model="form.unit_type"
                                    required
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    :class="{ 'border-red-500': form.errors.unit_type }"
                                >
                                    <option value="">Seleccionar</option>
                                    <option value="kg">Kilogramos (kg)</option>
                                    <option value="g">Gramos (g)</option>
                                    <option value="lt">Litros (lt)</option>
                                    <option value="ml">Mililitros (ml)</option>
                                    <option value="pcs">Piezas (pcs)</option>
                                    <option value="box">Cajas (box)</option>
                                    <option value="pack">Paquetes (pack)</option>
                                </select>
                                <div v-if="form.errors.unit_type" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.unit_type }}
                                </div>
                            </div>

                            <!-- Stock actual -->
                            <div>
                                <label for="current_stock" class="block text-sm font-medium text-gray-700 mb-1">
                                    Stock Actual *
                                </label>
                                <input
                                    id="current_stock"
                                    v-model="form.current_stock"
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    required
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    :class="{ 'border-red-500': form.errors.current_stock }"
                                />
                                <div v-if="form.errors.current_stock" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.current_stock }}
                                </div>
                            </div>

                            <!-- Stock mínimo -->
                            <div>
                                <label for="min_stock" class="block text-sm font-medium text-gray-700 mb-1">
                                    Stock Mínimo *
                                </label>
                                <input
                                    id="min_stock"
                                    v-model="form.min_stock"
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    required
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    :class="{ 'border-red-500': form.errors.min_stock }"
                                />
                                <div v-if="form.errors.min_stock" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.min_stock }}
                                </div>
                            </div>
                        </div>

                        <!-- Costo y fecha -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Costo unitario -->
                            <div>
                                <label for="unit_cost" class="block text-sm font-medium text-gray-700 mb-1">
                                    Costo Unitario *
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input
                                        id="unit_cost"
                                        v-model="form.unit_cost"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full pl-7 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': form.errors.unit_cost }"
                                    />
                                </div>
                                <div v-if="form.errors.unit_cost" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.unit_cost }}
                                </div>
                            </div>

                            <!-- Stock máximo -->
                            <div>
                                <label for="max_stock" class="block text-sm font-medium text-gray-700 mb-1">
                                    Stock Máximo
                                </label>
                                <input
                                    id="max_stock"
                                    v-model="form.max_stock"
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    :class="{ 'border-red-500': form.errors.max_stock }"
                                />
                                <div v-if="form.errors.max_stock" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.max_stock }}
                                </div>
                            </div>

                            <!-- Fecha de vencimiento -->
                            <div>
                                <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha de Vencimiento
                                </label>
                                <input
                                    id="expiry_date"
                                    v-model="form.expiry_date"
                                    type="date"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    :class="{ 'border-red-500': form.errors.expiry_date }"
                                />
                                <div v-if="form.errors.expiry_date" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.expiry_date }}
                                </div>
                            </div>
                        </div>

                        <!-- Estado activo (solo para editar) -->
                        <div v-if="product" class="flex items-center">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            />
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                Producto activo
                            </label>
                        </div>

                        <!-- Alerta de stock -->
                        <div v-if="form.current_stock && form.min_stock && parseFloat(form.current_stock) <= parseFloat(form.min_stock)" 
                             class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">
                                        ⚠️ Alerta de Stock Bajo
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        El stock actual ({{ form.current_stock }}) es igual o menor al stock mínimo ({{ form.min_stock }}).
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                            <Link 
                                :href="route('inventory.products.index')"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                            >
                                <span v-if="form.processing">Guardando...</span>
                                <span v-else>{{ product ? 'Actualizar' : 'Crear' }} Producto</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Props
const props = defineProps({
    categories: Array,
    product: Object
});

// Función para formatear fecha
const formatDateForInput = (dateString) => {
    if (!dateString) return '';
    return dateString.split('T')[0]; // Convierte "2025-06-14T00:00:00.000000Z" a "2025-06-14"
};

// Form
const form = useForm({
    name: props.product?.name || '',
    category_id: props.product?.category_id || '',
    description: props.product?.description || '',
    unit_type: props.product?.unit_type || '',
    current_stock: props.product?.current_stock || 0,
    min_stock: props.product?.min_stock || 0,
    max_stock: props.product?.max_stock || '',
    unit_cost: props.product?.unit_cost || 0,
    expiry_date: formatDateForInput(props.product?.expiry_date) || '',
    is_active: props.product?.is_active ?? true,
});

// Submit
const submit = () => {
    if (props.product) {
        form.put(route('inventory.products.update', props.product.id));
    } else {
        form.post(route('inventory.products.store'));
    }
};
</script>