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

            <!-- Right: Search, Quick Actions, Notifications, Dark Mode, Profile -->
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

                <!-- Quick Actions (only for admin & cajero) -->
                <div v-if="canAccessQuickActions" class="hidden lg:flex items-center space-x-2 px-3 border-l border-gray-200 dark:border-gray-700">
                    <!-- Abrir/Cerrar Caja (dynamic) -->
                    <Link
                        v-if="canAccess(['admin', 'cajero'])"
                        :href="hasCashRegisterSession ? route('cashregister.close.form') : route('cashregister.create')"
                        :class="[
                            'flex items-center space-x-1.5 px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                            hasCashRegisterSession 
                                ? 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/30'
                                : 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30'
                        ]"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>{{ hasCashRegisterSession ? 'Cerrar Caja' : 'Abrir Caja' }}</span>
                    </Link>

                    <!-- Nuevo Gasto -->
                    <button
                        v-if="canAccess(['admin'])"
                        @click="openNewExpense"
                        class="flex items-center space-x-1.5 px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Nuevo Gasto</span>
                    </button>

                    <!-- Nueva Venta -->
                    <Link
                        v-if="canAccess(['admin', 'cajero'])"
                        :href="route('sales.pos')"
                        class="flex items-center space-x-1.5 px-3 py-2 text-sm font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Nueva Venta</span>
                    </Link>
                </div>

                <!-- Notifications -->
                <NotificationCenter />

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
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { useDarkMode } from '@/composables/useDarkMode';
import NotificationCenter from '@/Components/Feedback/NotificationCenter.vue';
import axios from 'axios';

const props = defineProps({
    breadcrumbs: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['toggle-mobile-sidebar', 'open-search', 'open-new-expense']);

const page = usePage();
const { isDark, toggle: toggleDarkMode } = useDarkMode();

// Cash register state
const hasCashRegisterSession = ref(false);
const loadingCashRegister = ref(true);

// Fetch cash register status on mount
const fetchCashRegisterStatus = async () => {
    try {
        const response = await axios.get(route('cashregister.api.current'));
        hasCashRegisterSession.value = response.data.has_open_session || false;
    } catch (error) {
        console.error('Error fetching cash register status:', error);
        hasCashRegisterSession.value = false;
    } finally {
        loadingCashRegister.value = false;
    }
};

onMounted(() => {
    const userRole = page.props.auth.user.role;
    if (['admin', 'cajero'].includes(userRole)) {
        fetchCashRegisterStatus();
    }
});

const toggleMobileSidebar = () => {
    emit('toggle-mobile-sidebar');
};

const openSearch = () => {
    emit('open-search');
};

const openNewExpense = () => {
    emit('open-new-expense');
};

const canAccess = (allowedRoles) => {
    const userRole = page.props.auth.user.role;
    return allowedRoles.includes(userRole);
};

const canAccessQuickActions = computed(() => {
    const userRole = page.props.auth.user.role;
    return ['admin', 'cajero'].includes(userRole);
});

const userName = computed(() => page.props.auth.user.name);
const userEmail = computed(() => page.props.auth.user.email);
const userRole = computed(() => page.props.auth.user.role);
const userInitials = computed(() => {
    const names = userName.value.split(' ');
    return names.length > 1
        ? names[0][0] + names[1][0]
        : names[0][0] + (names[0][1] || '');
});
</script>
