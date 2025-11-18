import { ref, watch, onMounted } from 'vue';

const isDark = ref(false);

export function useDarkMode() {
    const toggle = () => {
        isDark.value = !isDark.value;
        updateDOM();
        savePreference();
    };

    const enable = () => {
        isDark.value = true;
        updateDOM();
        savePreference();
    };

    const disable = () => {
        isDark.value = false;
        updateDOM();
        savePreference();
    };

    const updateDOM = () => {
        if (isDark.value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    };

    const savePreference = () => {
        localStorage.setItem('darkMode', isDark.value ? 'true' : 'false');
    };

    const loadPreference = () => {
        const saved = localStorage.getItem('darkMode');
        if (saved !== null) {
            isDark.value = saved === 'true';
        } else {
            // Auto-detectar preferencia del sistema
            isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
        updateDOM();
    };

    onMounted(() => {
        loadPreference();
    });

    return {
        isDark,
        toggle,
        enable,
        disable
    };
}
