<template>
    <!-- Mobile backdrop -->
    <Transition
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isMobileOpen"
            @click="closeMobile"
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 lg:hidden"
        ></div>
    </Transition>

    <!-- Sidebar -->
    <aside
        :class="[
            'fixed top-0 left-0 h-full bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 transition-all duration-300 z-50',
            isCollapsed ? 'w-20' : 'w-64',
            isMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]"
    >
        <!-- Logo & Toggle -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-800">
            <Link v-show="!isCollapsed" :href="route('dashboard')" class="flex items-center space-x-2 transition-opacity duration-200" :class="{ 'opacity-0': isCollapsed }">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                </svg>
                <span class="text-xl font-bold text-gray-900 dark:text-white">RestaurantPOS</span>
            </Link>

            <button
                @click="toggleCollapse"
                class="hidden lg:flex p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            >
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="isCollapsed ? 'M13 5l7 7-7 7M5 5l7 7-7 7' : 'M11 19l-7-7 7-7m8 14l-7-7 7-7'"></path>
                </svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">
            <!-- Dashboard -->
            <NavItem
                :href="route('dashboard')"
                :active="route().current('dashboard')"
                :collapsed="isCollapsed"
                icon="dashboard"
            >
                Dashboard
            </NavItem>

            <!-- Ventas (Admin + Cajero) -->
            <template v-if="canAccess(['admin', 'cajero'])">
                <NavItem
                    :href="route('sales.pos')"
                    :active="route().current('sales.pos')"
                    :collapsed="isCollapsed"
                    icon="pos"
                    :badge="5"
                >
                    Punto de Venta
                </NavItem>

                <NavItem
                    :href="route('sales.index')"
                    :active="route().current('sales.*')"
                    :collapsed="isCollapsed"
                    icon="sales"
                >
                    Ventas
                </NavItem>

                <NavItem
                    :href="route('returns.index')"
                    :active="route().current('returns.*')"
                    :collapsed="isCollapsed"
                    icon="returns"
                >
                    Devoluciones
                </NavItem>

                <NavItem
                    :href="route('cashregister.index')"
                    :active="route().current('cashregister.*')"
                    :collapsed="isCollapsed"
                    icon="cash"
                >
                    Caja Registradora
                </NavItem>

                <NavItem
                    :href="route('tables.index')"
                    :active="route().current('tables.*')"
                    :collapsed="isCollapsed"
                    icon="table"
                >
                    Mesas
                </NavItem>
            </template>

            <!-- Inventario (Admin + Almacenero) -->
            <template v-if="canAccess(['admin', 'almacenero'])">
                <NavItem
                    :href="route('inventory.products.index')"
                    :active="route().current('inventory.*')"
                    :collapsed="isCollapsed"
                    icon="inventory"
                    :badge="lowStockCount"
                    badgeColor="red"
                >
                    Inventario
                </NavItem>

                <NavItem
                    :href="route('inventory.categories.index')"
                    :active="route().current('inventory.categories.*')"
                    :collapsed="isCollapsed"
                    icon="category"
                >
                    Categorías
                </NavItem>

                <NavItem
                    :href="route('suppliers.index')"
                    :active="route().current('suppliers.*')"
                    :collapsed="isCollapsed"
                    icon="users"
                >
                    Proveedores
                </NavItem>
            </template>

            <!-- Menú (Admin + Chef) -->
            <template v-if="canAccess(['admin', 'chef'])">
                <NavItem
                    :href="route('carta.index')"
                    :active="route().current('carta.*')"
                    :collapsed="isCollapsed"
                    icon="menu"
                >
                    Menú
                </NavItem>
            </template>

            <!-- Kitchen Display (Admin + Chef + Cajero) -->
            <template v-if="canAccess(['admin', 'chef', 'cajero'])">
                <NavItem
                    :href="route('kitchen.display')"
                    :active="route().current('kitchen.*')"
                    :collapsed="isCollapsed"
                    icon="kitchen"
                >
                    Cocina
                </NavItem>
            </template>

            <!-- Admin Only -->
            <template v-if="canAccess(['admin'])">
                <div v-if="!isCollapsed" class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Administración
                </div>

                <NavItem
                    :href="route('expenses.index')"
                    :active="route().current('expenses.*')"
                    :collapsed="isCollapsed"
                    icon="cashflow"
                >
                    Gastos
                </NavItem>

                <NavItem
                    :href="route('cashflow.index')"
                    :active="route().current('cashflow.*')"
                    :collapsed="isCollapsed"
                    icon="cashflow"
                >
                    Flujo de Caja
                </NavItem>

                <NavItem
                    :href="route('admin.reports')"
                    :active="route().current('admin.reports') || route().current('reports.*')"
                    :collapsed="isCollapsed"
                    icon="reports"
                >
                    Reportes
                </NavItem>

                <NavItem
                    :href="route('users.index')"
                    :active="route().current('users.*')"
                    :collapsed="isCollapsed"
                    icon="users"
                >
                    Usuarios
                </NavItem>

                <NavItem
                    :href="route('customers.index')"
                    :active="route().current('customers.*')"
                    :collapsed="isCollapsed"
                    icon="users"
                >
                    Clientes
                </NavItem>

                <NavItem
                    :href="route('settings.business')"
                    :active="route().current('settings.*')"
                    :collapsed="isCollapsed"
                    icon="settings"
                >
                    Configuración
                </NavItem>
            </template>
        </nav>

        <!-- User Profile (bottom) -->
        <div class="border-t border-gray-200 dark:border-gray-800 p-4">
            <div :class="['flex items-center', isCollapsed ? 'justify-center' : 'justify-between']">
                <div v-if="!isCollapsed" class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                        {{ userInitials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ userName }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ userRole }}</p>
                    </div>
                </div>
                <div v-else class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                    {{ userInitials }}
                </div>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import NavItem from './NavItem.vue';

const props = defineProps({
    isCollapsed: Boolean,
    isMobileOpen: Boolean,
    lowStockCount: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['toggle-collapse', 'close-mobile']);

const page = usePage();

const toggleCollapse = () => {
    emit('toggle-collapse');
};

const closeMobile = () => {
    emit('close-mobile');
};

const canAccess = (allowedRoles) => {
    const userRole = page.props.auth.user.role;
    return allowedRoles.includes(userRole);
};

const userName = computed(() => page.props.auth.user.name);
const userRole = computed(() => page.props.auth.user.role);
const userInitials = computed(() => {
    const names = userName.value.split(' ');
    return names.length > 1
        ? names[0][0] + names[1][0]
        : names[0][0] + (names[0][1] || '');
});
</script>
