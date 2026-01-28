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

        <!-- Keyboard Shortcuts Help Modal -->
        <KeyboardShortcutsModal
            :show="shortcutsModalOpen"
            @close="closeShortcutsModal"
        />

        <!-- Global Confirm Dialog (único en toda la app) -->
        <ConfirmDialog />

        <!-- Mobile FAB Navigation -->
        <MobileNavFab @open-expense="openNewExpense" />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useToast } from '@/composables';
import AdminSidebar from '@/Components/AdminSidebar.vue';
import AdminHeader from '@/Components/AdminHeader.vue';
import GlobalSearch from '@/Components/GlobalSearch.vue';
import ExpenseSlideOver from '@/Components/ExpenseSlideOver.vue';
import KeyboardShortcutsModal from '@/Components/KeyboardShortcutsModal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import MobileNavFab from '@/Components/MobileNavFab.vue';

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
const toast = useToast();

// Watch for flash messages
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
        if (flash?.warning) {
            toast.warning(flash.warning);
        }
        if (flash?.info) {
            toast.info(flash.info);
        }
    },
    { deep: true }
);

// Sidebar state
const sidebarCollapsed = ref(false);
const mobileSidebarOpen = ref(false);

// Search state
const searchOpen = ref(false);

// Expense state
const expenseSlideOverOpen = ref(false);

// Shortcuts modal state
const shortcutsModalOpen = ref(false);

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
    // Ignore if user is typing in an input/textarea
    const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName);

    // 1. Ctrl+K - Search
    if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
        event.preventDefault();
        openSearch();
        return;
    }

    // 2. Ctrl+P - Go to POS
    if ((event.metaKey || event.ctrlKey) && event.key === 'p') {
        event.preventDefault();
        router.visit(route('sales.pos'));
        return;
    }

    // 3. Ctrl+E - New Expense
    if ((event.metaKey || event.ctrlKey) && event.key === 'e') {
        event.preventDefault();
        openNewExpense();
        return;
    }

    // 4. Ctrl+B - Toggle Sidebar
    if ((event.metaKey || event.ctrlKey) && event.key === 'b') {
        event.preventDefault();
        toggleSidebar();
        return;
    }

    // 5. Ctrl+D - Toggle Dark Mode
    if ((event.metaKey || event.ctrlKey) && event.key === 'd') {
        event.preventDefault();
        toggleDarkMode();
        return;
    }

    // ? - Show keyboard shortcuts help (only if not typing)
    if (event.key === '?' && !event.ctrlKey && !event.metaKey && !isTyping) {
        event.preventDefault();
        openShortcutsModal();
        return;
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

const openShortcutsModal = () => {
    shortcutsModalOpen.value = true;
};

const closeShortcutsModal = () => {
    shortcutsModalOpen.value = false;
};

const toggleDarkMode = () => {
    // Toggle dark class on html element
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');

    if (isDark) {
        html.classList.remove('dark');
        localStorage.setItem('darkMode', 'false');
        toast.success('Modo claro activado');
    } else {
        html.classList.add('dark');
        localStorage.setItem('darkMode', 'true');
        toast.success('Modo oscuro activado');
    }
};
</script>
