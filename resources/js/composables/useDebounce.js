import { ref, watch, unref } from 'vue';

export function useDebounce(value, delay = 500) {
    const debouncedValue = ref(unref(value));
    let timeout;

    watch(() => unref(value), (newValue) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            debouncedValue.value = newValue;
        }, delay);
    }, { immediate: true });

    return debouncedValue;
}

export function useDebounceFn(fn, delay = 500) {
    let timeout;

    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            fn.apply(this, args);
        }, delay);
    };
}
