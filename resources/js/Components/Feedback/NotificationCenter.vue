<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useNotificationsStore } from '@/stores/notifications';

const notificationStore = useNotificationsStore();

const isOpen = ref(false);

const notifications = computed(() => notificationStore.notifications);
const unreadCount = computed(() => notificationStore.unreadCount);
const hasNotifications = computed(() => notifications.value.length > 0);
const loading = computed(() => notificationStore.loading);

const togglePanel = () => {
    if (!isOpen.value) {
        notificationStore.fetchNotifications();
    }
    isOpen.value = !isOpen.value;
};

const closePanel = () => {
    isOpen.value = false;
};

const markAsRead = (notification) => {
    notificationStore.markAsRead(notification.id);
};

const markAllAsRead = () => {
    notificationStore.markAllAsRead();
};

const clearAll = () => {
    notificationStore.clearAll();
    closePanel();
};

const removeNotification = (id) => {
    notificationStore.removeNotification(id);
};

const getIconClasses = (type) => {
    const variants = {
        success: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
        error: 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
        warning: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
        info: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
    };

    return variants[type] || variants.info;
};

const getIconPath = (type) => {
    const icons = {
        success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    };

    return icons[type] || icons.info;
};

onMounted(() => {
    notificationStore.startPolling(30000);
});

onUnmounted(() => {
    notificationStore.stopPolling();
});
</script>

<template>
    <div class="relative">
        <!-- Notification Bell Button -->
        <button
            type="button"
            class="relative p-2 text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 rounded-full transition-colors"
            @click="togglePanel"
        >
            <!-- Bell Icon -->
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
            </svg>

            <!-- Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute top-0 right-0 block h-4 w-4 rounded-full bg-red-600 dark:bg-red-500 text-xs text-white flex items-center justify-center"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- Notification Panel -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-show="isOpen"
                class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-900 rounded-lg shadow-lg ring-1 ring-black dark:ring-gray-800 ring-opacity-5 z-50"
            >
                <!-- Header -->
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Notificaciones
                    </h3>
                    <div class="flex items-center gap-2">
                        <button
                            v-if="unreadCount > 0"
                            type="button"
                            class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300"
                            @click="markAllAsRead"
                        >
                            Marcar todo leído
                        </button>
                        <button
                            v-if="hasNotifications"
                            type="button"
                            class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                            @click="clearAll"
                        >
                            Limpiar todo
                        </button>
                    </div>
                </div>

                <!-- Notifications List -->
                <div class="max-h-96 overflow-y-auto">
                    <!-- Loading State -->
                    <div v-if="loading" class="px-4 py-8 text-center">
                        <svg class="animate-spin mx-auto h-8 w-8 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Cargando notificaciones...
                        </p>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="!hasNotifications" class="px-4 py-8 text-center">
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                            />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            No tienes notificaciones
                        </p>
                    </div>

                    <!-- Notification Items -->
                    <div
                        v-else
                        v-for="notification in notifications"
                        :key="notification.id"
                        class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 border-b border-gray-100 dark:border-gray-800 cursor-pointer transition-colors"
                        :class="{ 'bg-blue-50 dark:bg-blue-900/20': !notification.read }"
                        @click="markAsRead(notification)"
                    >
                        <div class="flex items-start gap-3">
                            <!-- Icon -->
                            <div
                                :class="getIconClasses(notification.type)"
                                class="flex-shrink-0 p-2 rounded-full"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        :d="getIconPath(notification.type)"
                                    />
                                </svg>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <p
                                    v-if="notification.title"
                                    class="text-sm font-medium text-gray-900 dark:text-white"
                                >
                                    {{ notification.title }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                    {{ notification.message }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    {{ notification.time_ago }}
                                </p>
                            </div>

                            <!-- Unread Indicator & Remove Button -->
                            <div class="flex-shrink-0 flex items-center gap-1">
                                <span
                                    v-if="!notification.read"
                                    class="h-2 w-2 bg-blue-600 dark:bg-blue-500 rounded-full"
                                ></span>
                                <button
                                    type="button"
                                    class="text-gray-400 dark:text-gray-600 hover:text-gray-600 dark:hover:text-gray-400"
                                    @click.stop="removeNotification(notification.id)"
                                >
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Backdrop -->
        <div
            v-if="isOpen"
            class="fixed inset-0 z-40"
            @click="closePanel"
        ></div>
    </div>
</template>
