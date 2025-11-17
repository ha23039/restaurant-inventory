<template>
    <header class="sticky top-0 z-30 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm">
        <div class="flex items-center justify-between h-16 px-4 lg:px-6">
            <!-- Left: Mobile menu + Breadcrumbs -->
            <div class="flex items-center space-x-4">
                <!-- Mobile menu button -->
                <button
                    @click="toggleMobileSidebar"
                    class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                >
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Breadcrumbs -->
                <nav class="hidden md:flex items-center space-x-2 text-sm">
                    <Link
                        v-for="(crumb, index) in breadcrumbs"
                        :key="index"
                        :href="crumb.href"
                        :class="[
                            'hover:text-blue-600 dark:hover:text-blue-400 transition-colors',
                            index === breadcrumbs.length - 1
                                ? 'text-gray-900 dark:text-white font-medium'
                                : 'text-gray-500 dark:text-gray-400'
                        ]"
                    >
                        {{ crumb.label }}
                        <span v-if="index < breadcrumbs.length - 1" class="mx-2 text-gray-400 dark:text-gray-600">/</span>
                    </Link>
                </nav>
            </div>

            <!-- Right: Search, Notifications, Dark Mode, Profile -->
            <div class="flex items-center space-x-2">
                <!-- Global Search -->
                <button
                    @click="openSearch"
                    class="hidden md:flex items-center space-x-2 px-3 py-2 text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>Buscar...</span>
                    <kbd class="hidden lg:inline-flex items-center px-1.5 py-0.5 text-xs font-semibold text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded">
                        ⌘K
                    </kbd>
                </button>

                <!-- Mobile search -->
                <button
                    @click="openSearch"
                    class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                >
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>

                <!-- Notifications -->
                <Menu as="div" class="relative">
                    <MenuButton class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span v-if="notificationCount > 0" class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </MenuButton>

                    <transition
                        enter-active-class="transition duration-100 ease-out"
                        enter-from-class="transform scale-95 opacity-0"
                        enter-to-class="transform scale-100 opacity-100"
                        leave-active-class="transition duration-75 ease-in"
                        leave-from-class="transform scale-100 opacity-100"
                        leave-to-class="transform scale-95 opacity-0"
                    >
                        <MenuItems class="absolute right-0 mt-2 w-80 origin-top-right bg-white dark:bg-gray-900 rounded-lg shadow-lg border border-gray-200 dark:border-gray-800 focus:outline-none overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notificaciones</h3>
                                    <span class="text-xs text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">Marcar todas como leídas</span>
                                </div>
                            </div>

                            <div class="max-h-96 overflow-y-auto">
                                <MenuItem v-for="notification in notifications" :key="notification.id" v-slot="{ active }">
                                    <div :class="['px-4 py-3 border-b border-gray-100 dark:border-gray-800 cursor-pointer', active ? 'bg-gray-50 dark:bg-gray-800' : '']">
                                        <div class="flex items-start space-x-3">
                                            <div :class="[
                                                'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center',
                                                notification.type === 'warning' ? 'bg-yellow-100 text-yellow-600' :
                                                notification.type === 'success' ? 'bg-green-100 text-green-600' :
                                                'bg-blue-100 text-blue-600'
                                            ]">
                                                <component :is="getNotificationIcon(notification.type)" class="w-4 h-4" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ notification.title }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ notification.message }}</p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ notification.time }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </MenuItem>
                            </div>

                            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 text-center">
                                <Link href="#" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                    Ver todas las notificaciones
                                </Link>
                            </div>
                        </MenuItems>
                    </transition>
                </Menu>

                <!-- Dark Mode Toggle -->
                <button
                    @click="toggleDarkMode"
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    title="Toggle dark mode"
                >
                    <svg v-if="isDark" class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                    </svg>
                    <svg v-else class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                </button>

                <!-- Profile Dropdown -->
                <Menu as="div" class="relative">
                    <MenuButton class="flex items-center space-x-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                            {{ userInitials }}
                        </div>
                        <div class="hidden md:block text-left">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ userName }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ userRole }}</p>
                        </div>
                        <svg class="hidden md:block w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </MenuButton>

                    <transition
                        enter-active-class="transition duration-100 ease-out"
                        enter-from-class="transform scale-95 opacity-0"
                        enter-to-class="transform scale-100 opacity-100"
                        leave-active-class="transition duration-75 ease-in"
                        leave-from-class="transform scale-100 opacity-100"
                        leave-to-class="transform scale-95 opacity-0"
                    >
                        <MenuItems class="absolute right-0 mt-2 w-56 origin-top-right bg-white dark:bg-gray-900 rounded-lg shadow-lg border border-gray-200 dark:border-gray-800 focus:outline-none overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ userName }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ userEmail }}</p>
                            </div>

                            <div class="py-1">
                                <MenuItem v-slot="{ active }">
                                    <Link
                                        :href="route('profile.edit')"
                                        :class="['block px-4 py-2 text-sm', active ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300']"
                                    >
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span>Mi Perfil</span>
                                        </div>
                                    </Link>
                                </MenuItem>

                                <MenuItem v-slot="{ active }">
                                    <Link
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        :class="['w-full text-left block px-4 py-2 text-sm', active ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300']"
                                    >
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            <span>Cerrar Sesión</span>
                                        </div>
                                    </Link>
                                </MenuItem>
                            </div>
                        </MenuItems>
                    </transition>
                </Menu>
            </div>
        </div>
    </header>
</template>

<script setup>
import { computed, h } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { useDarkMode } from '@/composables/useDarkMode';

const props = defineProps({
    breadcrumbs: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['toggle-mobile-sidebar', 'open-search']);

const page = usePage();
const { isDark, toggle: toggleDarkMode } = useDarkMode();

const toggleMobileSidebar = () => {
    emit('toggle-mobile-sidebar');
};

const openSearch = () => {
    emit('open-search');
};

const userName = computed(() => page.props.auth.user.name);
const userEmail = computed(() => page.props.auth.user.email);
const userRole = computed(() => page.props.auth.user.role);
const userInitials = computed(() => {
    const names = userName.value.split(' ');
    return names.length > 1
        ? names[0][0] + names[1][0]
        : names[0][0] + (names[0][1] || '');
});

// Mock notifications (replace with real data later)
const notifications = [
    {
        id: 1,
        type: 'warning',
        title: 'Stock Bajo',
        message: '3 productos necesitan reabastecimiento',
        time: 'Hace 5 minutos'
    },
    {
        id: 2,
        type: 'success',
        title: 'Venta Completada',
        message: 'Nueva venta de $1,250.00 registrada',
        time: 'Hace 15 minutos'
    },
    {
        id: 3,
        type: 'info',
        title: 'Nueva Devolución',
        message: 'Se procesó una devolución de $350.00',
        time: 'Hace 1 hora'
    }
];

const notificationCount = computed(() => notifications.length);

const getNotificationIcon = (type) => {
    const icons = {
        warning: h('svg', { fill: 'currentColor', viewBox: '0 0 20 20' }, [
            h('path', { 'fill-rule': 'evenodd', d: 'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z', 'clip-rule': 'evenodd' })
        ]),
        success: h('svg', { fill: 'currentColor', viewBox: '0 0 20 20' }, [
            h('path', { 'fill-rule': 'evenodd', d: 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z', 'clip-rule': 'evenodd' })
        ]),
        info: h('svg', { fill: 'currentColor', viewBox: '0 0 20 20' }, [
            h('path', { 'fill-rule': 'evenodd', d: 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z', 'clip-rule': 'evenodd' })
        ])
    };
    return icons[type] || icons.info;
};
</script>
