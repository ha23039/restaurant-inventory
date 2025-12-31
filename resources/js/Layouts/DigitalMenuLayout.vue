<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import MyOrdersSlideOver from '@/Components/MyOrdersSlideOver.vue';

const page = usePage();
const settings = computed(() => page.props.settings || {});

// My Orders slideOver
const showMyOrders = ref(false);

// Dark mode toggle
const isDark = ref(false);

const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('digital_menu_theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('digital_menu_theme', 'light');
    }
};

onMounted(() => {
    // Check for saved theme or system preference
    const savedTheme = localStorage.getItem('digital_menu_theme');
    if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDark.value = true;
        document.documentElement.classList.add('dark');
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <!-- Header -->
        <header class="sticky top-0 z-40 bg-white/80 dark:bg-gray-900/80 backdrop-blur-lg border-b border-gray-200 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo / Restaurant Name -->
                    <div class="flex items-center space-x-3">
                        <img
                            v-if="settings.logo_path"
                            :src="settings.logo_path"
                            :alt="settings.restaurant_name"
                            class="h-10 w-10 rounded-xl object-cover shadow-sm"
                        >
                        <div v-else class="h-10 w-10 rounded-xl bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center shadow-sm">
                            <span class="text-white font-bold text-lg">{{ settings.restaurant_name?.charAt(0) || 'R' }}</span>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ settings.restaurant_name || 'Menu Digital' }}
                            </h1>
                            <p v-if="settings.restaurant_phone" class="text-xs text-gray-500 dark:text-gray-400">
                                {{ settings.restaurant_phone }}
                            </p>
                        </div>
                    </div>

                    <!-- Right Side Actions -->
                    <div class="flex items-center space-x-2">
                        <!-- My Orders Button -->
                        <button
                            @click="showMyOrders = true"
                            class="p-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                            title="Mis pedidos"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </button>

                        <!-- Dark Mode Toggle -->
                        <button
                            @click="toggleDarkMode"
                            class="p-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                            :title="isDark ? 'Modo claro' : 'Modo oscuro'"
                        >
                            <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>

                        <!-- Status Badge -->
                        <span
                            v-if="settings.is_open"
                            class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400"
                        >
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                            Abierto
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400"
                        >
                            <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                            Cerrado
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-32">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-center space-y-2">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ settings.restaurant_name }}
                    </p>
                    <p v-if="settings.restaurant_address" class="text-xs text-gray-500 dark:text-gray-400">
                        {{ settings.restaurant_address }}
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-600">
                        Menu Digital · Powered by Restaurant POS
                    </p>
                </div>
            </div>
        </footer>

        <!-- Confirm Dialog -->
        <ConfirmDialog />

        <!-- My Orders SlideOver -->
        <MyOrdersSlideOver
            :show="showMyOrders"
            @close="showMyOrders = false"
        />
    </div>
</template>
