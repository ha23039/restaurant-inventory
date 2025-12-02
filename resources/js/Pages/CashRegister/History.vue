<template>
    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-white">
                Historial de Sesiones de Caja
            </h2>
        </template>

        <div class="space-y-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filtros -->
                <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filtros</h3>
                        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                                <select
                                    v-model="localFilters.status"
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Todos</option>
                                    <option value="open">Abiertas</option>
                                    <option value="closed">Cerradas</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Desde</label>
                                <input
                                    v-model="localFilters.date_from"
                                    type="date"
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Hasta</label>
                                <input
                                    v-model="localFilters.date_to"
                                    type="date"
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>

                            <div class="flex items-end">
                                <button
                                    type="submit"
                                    class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    Filtrar
                                </button>
                            </div>
                        </form>

                        <!-- Active Filters -->
                        <div v-if="hasActiveFilters" class="mt-4 flex flex-wrap gap-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Filtros activos:</span>
                            <span v-if="filters.status" class="px-2 py-1 text-xs font-semibold text-blue-800 dark:text-blue-200 bg-blue-100 dark:bg-blue-900 rounded-full">
                                Estado: {{ filters.status }}
                                <button @click="removeFilter('status')" class="ml-1 text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100">×</button>
                            </span>
                            <span v-if="filters.date_from" class="px-2 py-1 text-xs font-semibold text-blue-800 dark:text-blue-200 bg-blue-100 dark:bg-blue-900 rounded-full">
                                Desde: {{ filters.date_from }}
                                <button @click="removeFilter('date_from')" class="ml-1 text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100">×</button>
                            </span>
                            <span v-if="filters.date_to" class="px-2 py-1 text-xs font-semibold text-blue-800 dark:text-blue-200 bg-blue-100 dark:bg-blue-900 rounded-full">
                                Hasta: {{ filters.date_to }}
                                <button @click="removeFilter('date_to')" class="ml-1 text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100">×</button>
                            </span>
                            <button @click="clearFilters" class="px-2 py-1 text-xs font-semibold text-red-700 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                Limpiar todo
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Sesiones -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="sessions.data.length === 0" class="text-center space-y-6">
                            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay sesiones</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No se encontraron sesiones con los filtros aplicados</p>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Cajero
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Apertura
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Cierre
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Duración
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Ventas
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Diferencia
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Estado
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="session in sessions.data" :key="session.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ session.user.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDateTime(session.opened_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ session.closed_at ? formatDateTime(session.closed_at) : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ getDuration(session) }} hrs
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
                                            ${{ formatPrice(session.total_all_sales || 0) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                            <span v-if="session.status === 'closed'" :class="[
                                                'font-semibold',
                                                toNumber(session.difference) == 0 ? 'text-green-600 dark:text-green-400' :
                                                toNumber(session.difference) > 0 ? 'text-yellow-600 dark:text-yellow-400' :
                                                'text-red-600 dark:text-red-400'
                                            ]">
                                                {{ toNumber(session.difference) > 0 ? '+' : '' }}${{ formatPrice(session.difference) }}
                                            </span>
                                            <span v-else class="text-gray-400 dark:text-gray-500">-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span :class="[
                                                'px-2 py-1 text-xs font-semibold rounded-full',
                                                session.status === 'open' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
                                            ]">
                                                {{ session.status === 'open' ? 'Abierta' : 'Cerrada' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link
                                                :href="route('cashregister.show', session.id)"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"
                                            >
                                                Ver detalles
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div v-if="sessions.data.length > 0" class="mt-6 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 sm:px-6">
                            <div class="flex flex-1 justify-between sm:hidden">
                                <Link
                                    v-if="sessions.prev_page_url"
                                    :href="sessions.prev_page_url"
                                    class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600"
                                >
                                    Anterior
                                </Link>
                                <Link
                                    v-if="sessions.next_page_url"
                                    :href="sessions.next_page_url"
                                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600"
                                >
                                    Siguiente
                                </Link>
                            </div>
                            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        Mostrando
                                        <span class="font-medium">{{ sessions.from || 0 }}</span>
                                        a
                                        <span class="font-medium">{{ sessions.to || 0 }}</span>
                                        de
                                        <span class="font-medium">{{ sessions.total || 0 }}</span>
                                        resultados
                                    </p>
                                </div>
                                <div>
                                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                                        <Link
                                            v-for="link in (sessions.links || [])"
                                            :key="link.label"
                                            :href="link.url"
                                            :class="[
                                                'relative inline-flex items-center px-4 py-2 text-sm font-medium',
                                                link.active
                                                    ? 'z-10 bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600'
                                                    : 'text-gray-900 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:z-20 focus:outline-offset-0',
                                                !link.url && 'pointer-events-none opacity-50'
                                            ]"
                                            v-html="link.label"
                                        ></Link>
                                    </nav>
                                </div>
                            </div>
                        </div>
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
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    sessions: Object,
    filters: Object,
});

const localFilters = ref({
    status: props.filters.status || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

const hasActiveFilters = computed(() => {
    return props.filters.status || props.filters.date_from || props.filters.date_to;
});

const applyFilters = () => {
    router.get(route('cashregister.history'), localFilters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const removeFilter = (filterName) => {
    localFilters.value[filterName] = '';
    applyFilters();
};

const clearFilters = () => {
    localFilters.value = {
        status: '',
        date_from: '',
        date_to: '',
    };
    applyFilters();
};

const toNumber = (value) => {
    if (value === null || value === undefined || value === '') {
        return 0;
    }
    return parseFloat(value) || 0;
};

const formatPrice = (value) => {
    return toNumber(value).toFixed(2);
};

const formatDateTime = (datetime) => {
    if (!datetime) {
        return '-';
    }
    try {
        return new Date(datetime).toLocaleString('es-MX', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return '-';
    }
};

const getDuration = (session) => {
    if (!session || !session.opened_at) {
        return '0.0';
    }

    try {
        if (!session.closed_at) {
            const start = new Date(session.opened_at);
            const now = new Date();
            const hours = Math.abs(now - start) / 36e5;
            return hours.toFixed(1);
        }

        const start = new Date(session.opened_at);
        const end = new Date(session.closed_at);
        const hours = Math.abs(end - start) / 36e5;
        return hours.toFixed(1);
    } catch (e) {
        return '0.0';
    }
};
</script>
