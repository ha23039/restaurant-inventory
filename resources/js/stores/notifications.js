import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useNotificationsStore = defineStore('notifications', () => {
    // State
    const notifications = ref([]);
    const unreadCount = ref(0);
    const loading = ref(false);
    const pollingInterval = ref(null);

    // Fetch notifications from API
    async function fetchNotifications(limit = 20) {
        loading.value = true;
        try {
            const response = await axios.get('/notifications', { params: { limit } });
            notifications.value = response.data.notifications;
            unreadCount.value = response.data.unread_count;
        } catch (error) {
            console.error('Error fetching notifications:', error);
        } finally {
            loading.value = false;
        }
    }

    // Fetch unread count
    async function fetchUnreadCount() {
        try {
            const response = await axios.get('/notifications/unread-count');
            unreadCount.value = response.data.unread_count;
        } catch (error) {
            console.error('Error fetching unread count:', error);
        }
    }

    // Mark notification as read
    async function markAsRead(id) {
        const notification = notifications.value.find(n => n.id === id);
        if (notification && !notification.read) {
            notification.read = true;
            unreadCount.value = Math.max(0, unreadCount.value - 1);

            try {
                await axios.post(`/notifications/${id}/read`);
            } catch (error) {
                console.error('Error marking notification as read:', error);
                notification.read = false;
                unreadCount.value++;
            }
        }
    }

    // Mark all notifications as read
    async function markAllAsRead() {
        const unreadNotifications = notifications.value.filter(n => !n.read);

        unreadNotifications.forEach(notification => {
            notification.read = true;
        });
        unreadCount.value = 0;

        try {
            await axios.post('/notifications/read-all');
        } catch (error) {
            console.error('Error marking all as read:', error);
            await fetchNotifications();
        }
    }

    // Remove notification
    async function removeNotification(id) {
        const index = notifications.value.findIndex(n => n.id === id);
        if (index !== -1) {
            const notification = notifications.value[index];
            notifications.value.splice(index, 1);

            if (!notification.read) {
                unreadCount.value = Math.max(0, unreadCount.value - 1);
            }

            try {
                await axios.delete(`/notifications/${id}`);
            } catch (error) {
                console.error('Error removing notification:', error);
                await fetchNotifications();
            }
        }
    }

    // Clear all notifications
    async function clearAll() {
        notifications.value = [];
        unreadCount.value = 0;

        try {
            await axios.delete('/notifications');
        } catch (error) {
            console.error('Error clearing all notifications:', error);
            await fetchNotifications();
        }
    }

    // Start polling for new notifications
    function startPolling(interval = 30000) {
        if (pollingInterval.value) {
            stopPolling();
        }

        fetchNotifications();

        pollingInterval.value = setInterval(() => {
            fetchUnreadCount();
        }, interval);
    }

    // Stop polling
    function stopPolling() {
        if (pollingInterval.value) {
            clearInterval(pollingInterval.value);
            pollingInterval.value = null;
        }
    }

    // Client-side notification methods (for immediate feedback)
    function addLocalNotification(notification) {
        const newNotification = {
            id: Date.now() + Math.random(),
            type: notification.type || 'info',
            title: notification.title || '',
            message: notification.message,
            timestamp: new Date().toISOString(),
            time_ago: 'hace un momento',
            read: false,
            ...notification
        };

        notifications.value.unshift(newNotification);
        unreadCount.value++;

        return newNotification.id;
    }

    // Convenience methods for local notifications
    function success(message, options = {}) {
        return addLocalNotification({
            type: 'success',
            message,
            ...options
        });
    }

    function error(message, options = {}) {
        return addLocalNotification({
            type: 'error',
            message,
            ...options
        });
    }

    function warning(message, options = {}) {
        return addLocalNotification({
            type: 'warning',
            message,
            ...options
        });
    }

    function info(message, options = {}) {
        return addLocalNotification({
            type: 'info',
            message,
            ...options
        });
    }

    return {
        // State
        notifications,
        unreadCount,
        loading,
        // API Actions
        fetchNotifications,
        fetchUnreadCount,
        markAsRead,
        markAllAsRead,
        removeNotification,
        clearAll,
        // Polling
        startPolling,
        stopPolling,
        // Local Actions
        addLocalNotification,
        success,
        error,
        warning,
        info
    };
});
