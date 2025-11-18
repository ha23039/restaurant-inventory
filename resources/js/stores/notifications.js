import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationsStore = defineStore('notifications', () => {
    // State
    const notifications = ref([]);
    const unreadCount = ref(0);

    // Actions
    function addNotification(notification) {
        const id = Date.now() + Math.random();
        const newNotification = {
            id,
            type: notification.type || 'info', // 'success', 'error', 'warning', 'info'
            title: notification.title || '',
            message: notification.message,
            timestamp: new Date(),
            read: false,
            ...notification
        };

        notifications.value.unshift(newNotification);
        unreadCount.value++;

        // Auto-remove after 5 seconds for non-error notifications
        if (notification.type !== 'error' && !notification.persistent) {
            setTimeout(() => {
                removeNotification(id);
            }, 5000);
        }

        return id;
    }

    function removeNotification(id) {
        const index = notifications.value.findIndex(n => n.id === id);
        if (index !== -1) {
            if (!notifications.value[index].read) {
                unreadCount.value--;
            }
            notifications.value.splice(index, 1);
        }
    }

    function markAsRead(id) {
        const notification = notifications.value.find(n => n.id === id);
        if (notification && !notification.read) {
            notification.read = true;
            unreadCount.value--;
        }
    }

    function markAllAsRead() {
        notifications.value.forEach(notification => {
            notification.read = true;
        });
        unreadCount.value = 0;
    }

    function clearAll() {
        notifications.value = [];
        unreadCount.value = 0;
    }

    // Convenience methods
    function success(message, options = {}) {
        return addNotification({
            type: 'success',
            message,
            ...options
        });
    }

    function error(message, options = {}) {
        return addNotification({
            type: 'error',
            message,
            persistent: true,
            ...options
        });
    }

    function warning(message, options = {}) {
        return addNotification({
            type: 'warning',
            message,
            ...options
        });
    }

    function info(message, options = {}) {
        return addNotification({
            type: 'info',
            message,
            ...options
        });
    }

    return {
        // State
        notifications,
        unreadCount,
        // Actions
        addNotification,
        removeNotification,
        markAsRead,
        markAllAsRead,
        clearAll,
        success,
        error,
        warning,
        info
    };
});
