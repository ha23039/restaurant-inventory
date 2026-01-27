<template>
    <!-- FAB Button - Only visible on mobile/tablet -->
    <div class="lg:hidden">
        <!-- Overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                class="fixed inset-0 bg-black/50 z-40"
                @click="close"
            />
        </Transition>

        <!-- SlideOver Panel -->
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-y-full"
            enter-to-class="translate-y-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-y-0"
            leave-to-class="translate-y-full"
        >
            <div
                v-if="isOpen"
                class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 rounded-t-3xl shadow-2xl z-50 max-h-[85vh] overflow-y-auto"
            >
                <!-- Handle bar -->
                <div class="flex justify-center pt-3 pb-2">
                    <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full" />
                </div>

                <!-- Header -->
                <div class="px-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Menú Rápido
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ userName }} · <span class="capitalize">{{ userRole }}</span>
                            </p>
                        </div>
                        <button
                            @click="close"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Navigation Grid -->
                <div class="p-4">
                    <div class="grid grid-cols-3 gap-3">
                        <template v-for="item in filteredNavItems" :key="item.route">
                            <Link
                                :href="route(item.route)"
                                @click="close"
                                class="flex flex-col items-center justify-center p-4 rounded-2xl transition-all duration-200 active:scale-95"
                                :class="[
                                    isActive(item.routePattern)
                                        ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 ring-2 ring-blue-500/30'
                                        : 'bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                ]"
                            >
                                <div
                                    class="w-12 h-12 flex items-center justify-center rounded-xl mb-2"
                                    :class="item.iconBg"
                                >
                                    <component :is="item.icon" class="w-6 h-6 text-white" />
                                </div>
                                <span class="text-xs font-medium text-center leading-tight">
                                    {{ item.label }}
                                </span>
                            </Link>
                        </template>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="px-4 pb-4" v-if="quickActions.length > 0">
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3 px-1">
                            Acciones Rápidas
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-for="action in quickActions"
                                :key="action.route"
                                :href="route(action.route)"
                                @click="close"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 active:scale-95"
                                :class="action.class"
                            >
                                <component :is="action.icon" class="w-5 h-5" />
                                {{ action.label }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-4 pb-6 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <div class="flex gap-3">
                        <Link
                            :href="route('profile.edit')"
                            @click="close"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium text-sm hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors active:scale-95"
                        >
                            <UserIcon class="w-5 h-5" />
                            Perfil
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            @click="close"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-xl font-medium text-sm hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors active:scale-95"
                        >
                            <LogoutIcon class="w-5 h-5" />
                            Salir
                        </Link>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- FAB Button -->
        <button
            @click="toggle"
            class="fixed bottom-6 right-6 w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 z-30 flex items-center justify-center active:scale-95"
            :class="{ 'rotate-45': isOpen }"
        >
            <Transition
                enter-active-class="transition-all duration-200"
                enter-from-class="opacity-0 rotate-90"
                enter-to-class="opacity-100 rotate-0"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100 rotate-0"
                leave-to-class="opacity-0 -rotate-90"
                mode="out-in"
            >
                <svg v-if="!isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </Transition>
        </button>
    </div>
</template>

<script setup>
import { ref, computed, h } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const isOpen = ref(false);
const page = usePage();

const userName = computed(() => page.props.auth.user.name);
const userRole = computed(() => page.props.auth.user.role);

const toggle = () => {
    isOpen.value = !isOpen.value;
};

const close = () => {
    isOpen.value = false;
};

const canAccess = (allowedRoles) => {
    return allowedRoles.includes(userRole.value);
};

const isActive = (pattern) => {
    return route().current(pattern);
};

// Icon Components (inline SVGs as render functions)
const HomeIcon = {
    render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' })
    ])
};

const POSIcon = {
    render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z' })
    ])
};

const SalesIcon = {
    render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' })
    ])
};

const InventoryIcon = {
    render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' })
    ])
};

const MenuIcon = {
    render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' })
    ])
};

const CashFlowIcon = {
    render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' })
    ])
};

const SimpleProductsIcon = {
    render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' })
    ])
};

const AdminIcon = {
    render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' }),
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z' })
    ])
};

const UserIcon = {
    render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' })
    ])
};

const LogoutIcon = {
    render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1' })
    ])
};

const ReportsIcon = {
    render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })
    ])
};

// Navigation items with role permissions
const navItems = [
    {
        route: 'dashboard',
        routePattern: 'dashboard',
        label: 'Inicio',
        icon: HomeIcon,
        iconBg: 'bg-gray-500',
        roles: ['admin', 'chef', 'almacenero', 'cajero']
    },
    {
        route: 'sales.pos',
        routePattern: 'sales.pos*',
        label: 'POS',
        icon: POSIcon,
        iconBg: 'bg-green-500',
        roles: ['admin', 'cajero']
    },
    {
        route: 'sales.index',
        routePattern: 'sales.*',
        label: 'Ventas',
        icon: SalesIcon,
        iconBg: 'bg-blue-500',
        roles: ['admin', 'cajero']
    },
    {
        route: 'inventory.index',
        routePattern: 'inventory.*',
        label: 'Inventario',
        icon: InventoryIcon,
        iconBg: 'bg-amber-500',
        roles: ['admin', 'almacenero']
    },
    {
        route: 'simple-products.index',
        routePattern: 'simple-products.*',
        label: 'Productos',
        icon: SimpleProductsIcon,
        iconBg: 'bg-purple-500',
        roles: ['admin', 'almacenero']
    },
    {
        route: 'carta.index',
        routePattern: 'carta.*',
        label: 'Menú',
        icon: MenuIcon,
        iconBg: 'bg-orange-500',
        roles: ['admin', 'chef']
    },
    {
        route: 'cashflow.index',
        routePattern: 'cashflow.*',
        label: 'Flujo Caja',
        icon: CashFlowIcon,
        iconBg: 'bg-emerald-500',
        roles: ['admin']
    },
    {
        route: 'reports.sales',
        routePattern: 'reports.*',
        label: 'Reportes',
        icon: ReportsIcon,
        iconBg: 'bg-indigo-500',
        roles: ['admin']
    },
    {
        route: 'admin.reports',
        routePattern: 'admin.*',
        label: 'Admin',
        icon: AdminIcon,
        iconBg: 'bg-slate-600',
        roles: ['admin']
    }
];

// Filter nav items by user role
const filteredNavItems = computed(() => {
    return navItems.filter(item => canAccess(item.roles));
});

// Quick actions based on role
const quickActions = computed(() => {
    const actions = [];

    if (canAccess(['admin', 'cajero'])) {
        actions.push({
            route: 'sales.pos',
            label: 'Ir a POS',
            icon: POSIcon,
            class: 'bg-green-600 hover:bg-green-700 text-white'
        });
    }

    return actions;
});
</script>
