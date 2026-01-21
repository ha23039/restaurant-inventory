<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RecipeFormSlideOver from '@/Components/RecipeFormSlideOver.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { useToast } from 'vue-toastification';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

const props = defineProps({
    menuItems: Object,
    products: Array,
    filters: Object,
});

const toast = useToast();
const { confirm } = useConfirmDialog();

// Estado
const expandedItems = ref(new Set());
const showFormSlideOver = ref(false);
const selectedMenuItem = ref(null);
const editingRecipe = ref(null);
const searchTerm = ref(props.filters?.search || '');
const filterOnlyDishes = ref(props.filters?.only_dishes === 'true');
const filterHasRecipes = ref(props.filters?.has_recipes || '');

// Métodos
const toggleExpand = (itemId) => {
    if (expandedItems.value.has(itemId)) {
        expandedItems.value.delete(itemId);
    } else {
        expandedItems.value.add(itemId);
    }
};

const isExpanded = (itemId) => {
    return expandedItems.value.has(itemId);
};

const openAddRecipeForm = (menuItem) => {
    selectedMenuItem.value = menuItem;
    editingRecipe.value = null;
    showFormSlideOver.value = true;
};

const openEditRecipeForm = (menuItem, recipe) => {
    selectedMenuItem.value = menuItem;
    editingRecipe.value = recipe;
    showFormSlideOver.value = true;
};

const closeForm = () => {
    showFormSlideOver.value = false;
    selectedMenuItem.value = null;
    editingRecipe.value = null;
};

const applyFilters = () => {
    router.get(route('carta.recipes'), {
        search: searchTerm.value,
        only_dishes: filterOnlyDishes.value || undefined,
        has_recipes: filterHasRecipes.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchTerm.value = '';
    filterOnlyDishes.value = false;
    filterHasRecipes.value = '';
    router.get(route('carta.recipes'));
};

const deleteRecipe = async (recipe, menuItemName) => {
    const confirmed = await confirm({
        title: '¿Eliminar ingrediente?',
        message: `¿Eliminar "${recipe.product.name}" de la receta de "${menuItemName}"?`,
        confirmText: 'Eliminar',
        type: 'danger'
    });

    if (confirmed) {
        router.delete(route('carta.recipes.destroy', recipe.id), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Ingrediente eliminado');
            },
        });
    }
};

const getUnitLabel = (unit) => {
    const labels = {
        'kg': 'kg',
        'lt': 'L',
        'pcs': 'pzas',
        'g': 'g',
        'ml': 'ml',
    };
    return labels[unit] || unit;
};

const formatQuantity = (quantity) => {
    return parseFloat(quantity).toFixed(3).replace(/\.?0+$/, '');
};
</script>

<template>
    <Head title="Recetas" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Recetas de Platillos
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Asigna ingredientes a cada platillo del menú
                        </p>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-3 md:py-6 lg:py-12">
            <div class="max-w-full mx-auto px-2 sm:px-6 lg:px-8">
                <!-- Filtros y búsqueda -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Búsqueda -->
                        <div class="md:col-span-2">
                            <input
                                v-model="searchTerm"
                                @keyup.enter="applyFilters"
                                type="text"
                                placeholder="Buscar platillo..."
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"
                            />
                        </div>

                        <!-- Filtro con/sin recetas -->
                        <div>
                            <select
                                v-model="filterHasRecipes"
                                @change="applyFilters"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Todos</option>
                                <option value="true">Con Receta</option>
                                <option value="false">Sin Receta</option>
                            </select>
                        </div>
                    </div>

                    <!-- Checkbox excluir servicios -->
                    <div class="mt-3 flex items-center">
                        <input
                            v-model="filterOnlyDishes"
                            @change="applyFilters"
                            id="only_dishes"
                            type="checkbox"
                            class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                        />
                        <label for="only_dishes" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Solo platillos (excluir servicios)
                        </label>
                    </div>

                    <!-- Botón limpiar filtros -->
                    <div v-if="searchTerm || filterOnlyDishes || filterHasRecipes" class="mt-3">
                        <button
                            @click="clearFilters"
                            class="text-sm text-purple-600 hover:text-purple-700 font-medium"
                        >
                            Limpiar filtros
                        </button>
                    </div>
                </div>

                <!-- Lista de platillos con accordion -->
                <div class="space-y-4">
                    <div
                        v-for="item in menuItems.data"
                        :key="item.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden"
                    >
                        <!-- Header del accordion -->
                        <div
                            @click="toggleExpand(item.id)"
                            class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            <div class="flex items-center space-x-4 flex-1">
                                <!-- Icono -->
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 rounded-lg flex items-center justify-center"
                                        :class="item.is_service ? 'bg-purple-100 dark:bg-purple-900' : 'bg-orange-100 dark:bg-orange-900'"
                                    >
                                        <span class="text-2xl">{{ item.is_service ? '⚙️' : '🍽️' }}</span>
                                    </div>
                                </div>

                                <!-- Info del platillo -->
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ item.name }}
                                        </h3>
                                        <span
                                            v-if="item.is_service"
                                            class="px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200"
                                        >
                                            Servicio
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ item.description || 'Sin descripción' }}
                                    </p>
                                    <div class="flex items-center space-x-4 mt-2">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            ${{ parseFloat(item.price).toFixed(2) }}
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ item.recipes_count }} ingrediente(s)
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones y chevron -->
                            <div class="flex items-center space-x-3">
                                <button
                                    @click.stop="openAddRecipeForm(item)"
                                    class="px-3 py-1.5 text-sm font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 border border-purple-600 dark:border-purple-400 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors"
                                >
                                    + Ingrediente
                                </button>
                                <svg
                                    class="w-5 h-5 text-gray-400 transition-transform"
                                    :class="{ 'transform rotate-180': isExpanded(item.id) }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <!-- Contenido expandible (recetas) -->
                        <div
                            v-show="isExpanded(item.id)"
                            class="border-t border-gray-200 dark:border-gray-700"
                        >
                            <!-- Lista de ingredientes -->
                            <div v-if="item.recipes && item.recipes.length > 0" class="p-4">
                                <div class="space-y-2">
                                    <div
                                        v-for="recipe in item.recipes"
                                        :key="recipe.id"
                                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-lg"
                                    >
                                        <div class="flex items-center space-x-3 flex-1">
                                            <!-- Icono de categoría -->
                                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                                <span class="text-blue-600 dark:text-blue-400 text-sm">📦</span>
                                            </div>

                                            <!-- Info del ingrediente -->
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ recipe.product.name }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ recipe.product.category?.name || 'Sin categoría' }}
                                                </div>
                                            </div>

                                            <!-- Cantidad -->
                                            <div class="text-right">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                                    {{ formatQuantity(recipe.quantity_needed) }} {{ getUnitLabel(recipe.unit) }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    Stock: {{ formatQuantity(recipe.product.current_stock) }} {{ getUnitLabel(recipe.product.unit_type) }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Botones de acción -->
                                        <div class="flex items-center space-x-2 ml-4">
                                            <button
                                                @click="openEditRecipeForm(item, recipe)"
                                                class="text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button
                                                @click="deleteRecipe(recipe, item.name)"
                                                class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div v-else class="p-8 text-center">
                                <div class="text-gray-400 dark:text-gray-500">
                                    <svg class="mx-auto h-12 w-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-sm">No hay ingredientes asignados</p>
                                    <button
                                        @click="openAddRecipeForm(item)"
                                        class="mt-3 text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 font-medium"
                                    >
                                        Agregar primer ingrediente
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state general -->
                    <div v-if="menuItems.data.length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
                        <div class="text-gray-400 dark:text-gray-500">
                            <svg class="mx-auto h-16 w-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-lg font-medium mb-2">No hay platillos para mostrar</p>
                            <p class="text-sm">Primero debes crear platillos en la sección de Platillos del Menú</p>
                        </div>
                    </div>
                </div>

                <!-- Paginación -->
                <div v-if="menuItems.data.length > 0" class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm px-6 py-3">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Mostrando {{ menuItems.from }} a {{ menuItems.to }} de {{ menuItems.total }} platillos
                        </div>
                        <div class="flex space-x-2">
                            <a
                                v-for="link in menuItems.links"
                                :key="link.label"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-1 text-sm rounded border',
                                    link.active
                                        ? 'bg-purple-600 text-white border-purple-600'
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                                ]"
                            ></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form SlideOver -->
        <RecipeFormSlideOver
            :show="showFormSlideOver"
            :menuItem="selectedMenuItem"
            :recipe="editingRecipe"
            :products="products"
            @close="closeForm"
        />

    </AdminLayout>
</template>
