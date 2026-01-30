<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useToast } from 'vue-toastification';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

const props = defineProps({
    combos: Object,
    filters: Object,
});

const toast = useToast();
const { confirm } = useConfirmDialog();

const search = ref(props.filters?.search || '');

const handleSearch = () => {
    router.get(route('combos.index'), {
        search: search.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearSearch = () => {
    search.value = '';
    router.get(route('combos.index'), {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const toggleAvailability = (combo) => {
    router.patch(route('combos.toggle-availability', combo.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(combo.is_available ? 'Combo desactivado' : 'Combo activado');
        },
    });
};

const duplicateCombo = (combo) => {
    router.post(route('combos.duplicate', combo.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Combo duplicado');
        },
    });
};

const deleteCombo = async (combo) => {
    const confirmed = await confirm({
        title: '¿Eliminar combo?',
        message: `¿Estás seguro de eliminar "${combo.name}"? Esta acción no se puede deshacer.`,
        confirmText: 'Eliminar',
        type: 'danger',
    });

    if (!confirmed) return;

    router.delete(route('combos.destroy', combo.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Combo eliminado');
        },
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};
</script>

<template>
    <Head title="Combos" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Combos</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Gestiona tus combos y promociones</p>
                    </div>
                </div>
                <Link
                    :href="route('combos.create')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Combo
                </Link>
            </div>
        </template>

        <div class="py-3 md:py-6 lg:py-12">
            <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
                <!-- Filtros -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <input
                                v-model="search"
                                @keyup.enter="handleSearch"
                                type="text"
                                placeholder="Buscar combos..."
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="handleSearch"
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors"
                            >
                                Buscar
                            </button>
                            <button
                                v-if="search"
                                @click="clearSearch"
                                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                            >
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Lista de Combos -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                    <!-- Grid de combos -->
                    <div v-if="combos.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                        <div
                            v-for="combo in combos.data"
                            :key="combo.id"
                            class="bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden hover:shadow-md transition-shadow"
                        >
                            <!-- Imagen -->
                            <div class="aspect-video bg-gray-200 dark:bg-gray-600 relative">
                                <img
                                    v-if="combo.image_path"
                                    :src="combo.image_path"
                                    :alt="combo.name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>

                                <!-- Badge de estado -->
                                <div class="absolute top-2 right-2">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs font-medium rounded-full',
                                            combo.is_available
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400'
                                                : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400'
                                        ]"
                                    >
                                        {{ combo.is_available ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>

                                <!-- Badge de combo -->
                                <div class="absolute top-2 left-2">
                                    <span class="px-2 py-1 text-xs font-bold bg-purple-600 text-white rounded-full">
                                        COMBO
                                    </span>
                                </div>
                            </div>

                            <!-- Contenido -->
                            <div class="p-4">
                                <h3 class="font-semibold text-lg text-gray-900 dark:text-white mb-1">
                                    {{ combo.name }}
                                </h3>
                                <p v-if="combo.description" class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">
                                    {{ combo.description }}
                                </p>

                                <!-- Info de componentes -->
                                <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mb-3">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        {{ combo.fixed_count }} fijos
                                    </span>
                                    <span v-if="combo.choice_count > 0" class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                        </svg>
                                        {{ combo.choice_count }} a elegir
                                    </span>
                                </div>

                                <!-- Precio -->
                                <div class="flex items-center justify-between">
                                    <span class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                        {{ formatCurrency(combo.base_price) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="px-4 py-3 bg-gray-100 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-600 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="toggleAvailability(combo)"
                                        :class="[
                                            'p-2 rounded-lg transition-colors',
                                            combo.is_available
                                                ? 'text-green-600 hover:bg-green-100 dark:hover:bg-green-900/30'
                                                : 'text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
                                        ]"
                                        :title="combo.is_available ? 'Desactivar' : 'Activar'"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path v-if="combo.is_available" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path v-if="combo.is_available" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="duplicateCombo(combo)"
                                        class="p-2 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                        title="Duplicar"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="route('combos.edit', combo.id)"
                                        class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                        title="Editar"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>
                                    <button
                                        @click="deleteCombo(combo)"
                                        class="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                        title="Eliminar"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estado vacío -->
                    <div v-else class="p-12 text-center">
                        <div class="w-24 h-24 mx-auto mb-6 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            No hay combos
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">
                            Crea tu primer combo para ofrecer promociones a tus clientes
                        </p>
                        <Link
                            :href="route('combos.create')"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Crear Combo
                        </Link>
                    </div>

                    <!-- Paginación -->
                    <div v-if="combos.data.length > 0 && combos.last_page > 1" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Mostrando {{ combos.from }} - {{ combos.to }} de {{ combos.total }}
                            </p>
                            <div class="flex gap-2">
                                <Link
                                    v-for="link in combos.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[
                                        'px-3 py-1 text-sm rounded',
                                        link.active
                                            ? 'bg-purple-600 text-white'
                                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        !link.url && 'opacity-50 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
