<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
        <!-- Sidebar -->
        <AdminSidebar
            :is-collapsed="sidebarCollapsed"
            :is-mobile-open="mobileSidebarOpen"
            :low-stock-count="lowStockCount"
            @toggle-collapse="toggleSidebar"
            @close-mobile="closeMobileSidebar"
        />

        <!-- Main Content -->
        <div
            :class="[
                'transition-all duration-300',
                sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'
            ]"
        >
            <!-- Header -->
            <AdminHeader
                :breadcrumbs="breadcrumbs"
                @toggle-mobile-sidebar="toggleMobileSidebar"
                @open-search="openSearch"
                @open-new-expense="openNewExpense"
            />

            <!-- Page Content -->
            <main class="p-4 lg:p-6">
                <!-- Page Header Slot -->
                <div v-if="$slots.header" class="mb-6">
                    <slot name="header"></slot>
                </div>

                <!-- Main Content Slot -->
                <slot></slot>
            </main>
        </div>

        <!-- Global Search Modal -->
        <GlobalSearch
            :is-open="searchOpen"
            @close="closeSearch"
        />

        <!-- Expense SlideOver -->
        <ExpenseSlideOver
            :show="expenseSlideOverOpen"
            :categories="expenseCategories"
            :suppliers="suppliers"
            @close="closeExpenseSlideOver"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';
import AdminHeader from '@/Components/AdminHeader.vue';
import GlobalSearch from '@/Components/GlobalSearch.vue';
import ExpenseSlideOver from '@/Components/ExpenseSlideOver.vue';

const props = defineProps({
    breadcrumbs: {
        type: Array,
        default: () => []
    },
    lowStockCount: {
        type: Number,
        default: 0
    }
});

const page = usePage();

// Sidebar state
const sidebarCollapsed = ref(false);
const mobileSidebarOpen = ref(false);

// Search state
const searchOpen = ref(false);

// Expense state
const expenseSlideOverOpen = ref(false);

// Expense categories and suppliers (pass from backend or fetch)
const expenseCategories = ref([
    { value: 'servicios_publicos', label: 'Servicios Públicos' },
    { value: 'compra_productos_insumos', label: 'Compra de Productos e Insumos' },
    { value: 'arriendo', label: 'Arriendo' },
    { value: 'nomina', label: 'Nómina' },
    { value: 'gastos_admin', label: 'Gastos Administrativos' },
    { value: 'marketing', label: 'Marketing' },
    { value: 'transporte_domicilios', label: 'Transporte, Domicilios y Logística' },
    { value: 'mantenimiento_reparaciones', label: 'Mantenimiento y Reparaciones' },
    { value: 'muebles_equipos', label: 'Muebles, Equipos y Maquinaria' },
    { value: 'otros', label: 'Otros' },
]);

const suppliers = ref(page.props.suppliers || []);

// Load sidebar state from localStorage
onMounted(() => {
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState !== null) {
        sidebarCollapsed.value = savedState === 'true';
    }

    // Add keyboard shortcut for search (Cmd+K / Ctrl+K)
    document.addEventListener('keydown', handleKeyboard);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyboard);
});

const handleKeyboard = (event) => {
    // Cmd+K or Ctrl+K for search
    if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
        event.preventDefault();
        openSearch();
    }
};

const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value);
};

const toggleMobileSidebar = () => {
    mobileSidebarOpen.value = !mobileSidebarOpen.value;
};

const closeMobileSidebar = () => {
    mobileSidebarOpen.value = false;
};

const openSearch = () => {
    searchOpen.value = true;
};

const closeSearch = () => {
    searchOpen.value = false;
};

const openNewExpense = () => {
    expenseSlideOverOpen.value = true;
};

const closeExpenseSlideOver = () => {
    expenseSlideOverOpen.value = false;
};
</script>
