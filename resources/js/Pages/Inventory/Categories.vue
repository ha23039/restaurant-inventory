<template>
    <Head title="Gestión de Categorías" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🏷️ Gestión de Categorías
                </h2>
                <button 
                    @click="openCreateModal"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    + Nueva Categoría
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Grid de categorías -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="category in categories" 
                        :key="category.id"
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4"
                        :style="{ borderLeftColor: category.color }"
                    >
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div 
                                        class="w-6 h-6 rounded-full mr-3"
                                        :style="{ backgroundColor: category.color }"
                                    ></div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ category.name }}</h3>
                                </div>
                                <div class="flex space-x-2">
                                    <button
                                        @click="openEditModal(category)"
                                        class="text-blue-600 hover:text-blue-800"
                                        title="Editar"
                                    >
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteCategory(category)"
                                        class="text-red-600 hover:text-red-800"
                                        title="Eliminar"
                                    >
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4">{{ category.description || 'Sin descripción' }}</p>
                            
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <span>{{ category.products_count }} productos</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ category.color }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje si no hay categorías -->
                    <div v-if="categories.length === 0" class="col-span-full text-center py-12 text-gray-500">
                        🏷️ No hay categorías creadas
                        <br>
                        <button 
                            @click="openCreateModal"
                            class="mt-4 text-blue-600 hover:text-blue-800 underline"
                        >
                            Crear la primera categoría
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para crear/editar categoría -->
        <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ editingCategory ? 'Editar Categoría' : 'Nueva Categoría' }}
                    </h3>
                    
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <!-- Nombre -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre *
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

                        <!-- Color -->
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700 mb-1">
                                Color *
                            </label>
                            <div class="flex items-center space-x-2">
                                <input
                                    id="color"
                                    v-model="form.color"
                                    type="color"
                                    required
                                    class="h-10 w-20 border border-gray-300 rounded cursor-pointer"
                                />
                                <input
                                    v-model="form.color"
                                    type="text"
                                    placeholder="#6366f1"
                                    class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    :class="{ 'border-red-500': form.errors.color }"
                                />
                            </div>
                            <div v-if="form.errors.color" class="text-red-500 text-sm mt-1">
                                {{ form.errors.color }}
                            </div>
                        </div>

                        <!-- Vista previa -->
                        <div class="bg-gray-50 p-3 rounded-md">
                            <p class="text-sm text-gray-600 mb-2">Vista previa:</p>
                            <div class="flex items-center">
                                <div 
                                    class="w-4 h-4 rounded-full mr-2"
                                    :style="{ backgroundColor: form.color }"
                                ></div>
                                <span class="font-medium">{{ form.name || 'Nombre de la categoría' }}</span>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex items-center justify-end space-x-4 pt-4">
                            <button
                                type="button"
                                @click="closeModal"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                            >
                                <span v-if="form.processing">Guardando...</span>
                                <span v-else>{{ editingCategory ? 'Actualizar' : 'Crear' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

// Props
defineProps({
    categories: Array
});

// State
const showModal = ref(false);
const editingCategory = ref(null);

// Form
const form = useForm({
    name: '',
    description: '',
    color: '#6366f1'
});

// Funciones del modal
const openCreateModal = () => {
    editingCategory.value = null;
    form.reset();
    form.color = '#6366f1';
    showModal.value = true;
};

const openEditModal = (category) => {
    editingCategory.value = category;
    form.name = category.name;
    form.description = category.description || '';
    form.color = category.color;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingCategory.value = null;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    if (editingCategory.value) {
        form.put(route('inventory.categories.update', editingCategory.value.id), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('inventory.categories.store'), {
            onSuccess: () => closeModal()
        });
    }
};

const deleteCategory = (category) => {
    if (category.products_count > 0) {
        alert(`No se puede eliminar "${category.name}" porque tiene ${category.products_count} productos asignados.`);
        return;
    }

    if (confirm(`¿Estás seguro de eliminar la categoría "${category.name}"?`)) {
        router.delete(route('inventory.categories.destroy', category.id));
    }
};
</script>
