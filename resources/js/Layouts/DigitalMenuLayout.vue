<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const settings = computed(() => page.props.settings || {});
const showMobileMenu = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Header -->
        <header class="sticky top-0 z-40 bg-white dark:bg-gray-800 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo / Restaurant Name -->
                    <div class="flex items-center space-x-3">
                        <img
                            v-if="settings.logo_path"
                            :src="settings.logo_path"
                            :alt="settings.restaurant_name"
                            class="h-10 w-10 rounded-full object-cover"
                        >
                        <div v-else class="h-10 w-10 rounded-full bg-orange-500 flex items-center justify-center">
                            <span class="text-white font-bold text-lg">{{ settings.restaurant_name?.charAt(0) || 'R' }}</span>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ settings.restaurant_name || 'Menu Digital' }}
                            </h1>
                            <p v-if="settings.restaurant_phone" class="text-xs text-gray-500 dark:text-gray-400">
                                {{ settings.restaurant_phone }}
                            </p>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="flex items-center space-x-2">
                        <span
                            v-if="settings.is_open"
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200"
                        >
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                            Abierto
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200"
                        >
                            <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                            Cerrado
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-24">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-center space-y-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ settings.restaurant_name }}
                    </p>
                    <p v-if="settings.restaurant_address" class="text-xs text-gray-500 dark:text-gray-500">
                        {{ settings.restaurant_address }}
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-600">
                        Menu Digital - Powered by Restaurant POS
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
