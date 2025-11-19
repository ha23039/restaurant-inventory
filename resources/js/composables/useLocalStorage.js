import { ref, watch } from 'vue';

export function useLocalStorage(key, defaultValue = null) {
    const data = ref(defaultValue);

    // Read from localStorage on initialization
    const read = () => {
        try {
            const item = window.localStorage.getItem(key);
            if (item !== null) {
                data.value = JSON.parse(item);
            }
        } catch (error) {
            console.warn(`Error reading localStorage key "${key}":`, error);
        }
    };

    // Write to localStorage
    const write = (value) => {
        try {
            window.localStorage.setItem(key, JSON.stringify(value));
        } catch (error) {
            console.warn(`Error writing localStorage key "${key}":`, error);
        }
    };

    // Remove from localStorage
    const remove = () => {
        try {
            window.localStorage.removeItem(key);
            data.value = defaultValue;
        } catch (error) {
            console.warn(`Error removing localStorage key "${key}":`, error);
        }
    };

    // Initialize
    read();

    // Watch for changes
    watch(data, (newValue) => {
        if (newValue === null) {
            remove();
        } else {
            write(newValue);
        }
    }, { deep: true });

    return {
        data,
        remove
    };
}
