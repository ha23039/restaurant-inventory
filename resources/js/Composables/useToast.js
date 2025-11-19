import { usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

export function useToast() {
    const page = usePage();
    const toast = ref(null);

    // Watch for flash messages from Laravel
    watch(
        () => page.props.flash,
        (flash) => {
            if (flash?.success) {
                success(flash.success);
            }
            if (flash?.error) {
                error(flash.error);
            }
            if (flash?.warning) {
                warning(flash.warning);
            }
            if (flash?.info) {
                info(flash.info);
            }
        },
        { deep: true }
    );

    const showToast = (message, type = 'info', duration = 3000) => {
        toast.value = { message, type };

        if (duration > 0) {
            setTimeout(() => {
                toast.value = null;
            }, duration);
        }
    };

    const success = (message, duration = 3000) => {
        showToast(message, 'success', duration);
    };

    const error = (message, duration = 4000) => {
        showToast(message, 'error', duration);
    };

    const warning = (message, duration = 3500) => {
        showToast(message, 'warning', duration);
    };

    const info = (message, duration = 3000) => {
        showToast(message, 'info', duration);
    };

    const clear = () => {
        toast.value = null;
    };

    return {
        toast,
        success,
        error,
        warning,
        info,
        clear,
    };
}
