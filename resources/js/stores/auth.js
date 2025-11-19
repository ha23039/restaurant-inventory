import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export const useAuthStore = defineStore('auth', () => {
    // State - Se obtiene de Inertia page props
    const page = usePage();

    // Getters
    const user = computed(() => page.props.auth?.user || null);
    const isAuthenticated = computed(() => !!user.value);
    const userRole = computed(() => user.value?.role || null);

    // Role checks
    const isAdmin = computed(() => userRole.value === 'admin');
    const isChef = computed(() => userRole.value === 'chef');
    const isAlmacenero = computed(() => userRole.value === 'almacenero');
    const isCajero = computed(() => userRole.value === 'cajero');

    // Permissions
    const canManageInventory = computed(() => {
        return isAdmin.value || isAlmacenero.value;
    });

    const canManageMenu = computed(() => {
        return isAdmin.value || isChef.value;
    });

    const canProcessSales = computed(() => {
        return isAdmin.value || isCajero.value;
    });

    const canViewReports = computed(() => {
        return isAdmin.value;
    });

    const canManageUsers = computed(() => {
        return isAdmin.value;
    });

    // Actions
    function hasRole(role) {
        return userRole.value === role;
    }

    function hasAnyRole(roles) {
        return roles.includes(userRole.value);
    }

    function hasPermission(permission) {
        const permissions = {
            'manage-inventory': canManageInventory.value,
            'manage-menu': canManageMenu.value,
            'process-sales': canProcessSales.value,
            'view-reports': canViewReports.value,
            'manage-users': canManageUsers.value,
        };

        return permissions[permission] || false;
    }

    return {
        // Getters
        user,
        isAuthenticated,
        userRole,
        isAdmin,
        isChef,
        isAlmacenero,
        isCajero,
        canManageInventory,
        canManageMenu,
        canProcessSales,
        canViewReports,
        canManageUsers,
        // Actions
        hasRole,
        hasAnyRole,
        hasPermission
    };
});
