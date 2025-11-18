import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useAppStore = defineStore('app', () => {
    // State
    const sidebarOpen = ref(true);
    const loading = ref(false);
    const theme = ref('light'); // 'light' or 'dark'
    const locale = ref('es'); // 'es' or 'en'

    // Loading state for different sections
    const loadingStates = ref({
        global: false,
        inventory: false,
        sales: false,
        menu: false,
    });

    // Getters
    const isLoading = computed(() => {
        return loading.value || Object.values(loadingStates.value).some(state => state);
    });

    const isDarkMode = computed(() => theme.value === 'dark');

    // Actions
    function toggleSidebar() {
        sidebarOpen.value = !sidebarOpen.value;
    }

    function closeSidebar() {
        sidebarOpen.value = false;
    }

    function openSidebar() {
        sidebarOpen.value = true;
    }

    function setLoading(state) {
        loading.value = state;
    }

    function setLoadingState(section, state) {
        if (loadingStates.value.hasOwnProperty(section)) {
            loadingStates.value[section] = state;
        }
    }

    function toggleTheme() {
        theme.value = theme.value === 'light' ? 'dark' : 'light';
        localStorage.setItem('theme', theme.value);
    }

    function setTheme(newTheme) {
        if (['light', 'dark'].includes(newTheme)) {
            theme.value = newTheme;
            localStorage.setItem('theme', newTheme);
        }
    }

    function setLocale(newLocale) {
        if (['es', 'en'].includes(newLocale)) {
            locale.value = newLocale;
            localStorage.setItem('locale', newLocale);
        }
    }

    function initializeFromLocalStorage() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            theme.value = savedTheme;
        }

        const savedLocale = localStorage.getItem('locale');
        if (savedLocale) {
            locale.value = savedLocale;
        }

        const savedSidebarState = localStorage.getItem('sidebarOpen');
        if (savedSidebarState !== null) {
            sidebarOpen.value = savedSidebarState === 'true';
        }
    }

    function saveSidebarState() {
        localStorage.setItem('sidebarOpen', sidebarOpen.value);
    }

    return {
        // State
        sidebarOpen,
        loading,
        theme,
        locale,
        loadingStates,
        // Getters
        isLoading,
        isDarkMode,
        // Actions
        toggleSidebar,
        closeSidebar,
        openSidebar,
        setLoading,
        setLoadingState,
        toggleTheme,
        setTheme,
        setLocale,
        initializeFromLocalStorage,
        saveSidebarState
    };
});
