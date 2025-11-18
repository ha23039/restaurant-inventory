import { useToast as useVueToast } from 'vue-toastification';

export function useToast() {
    const toast = useVueToast();

    return {
        success: (message, options = {}) => {
            toast.success(message, {
                timeout: 3000,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                draggable: true,
                draggablePercent: 0.6,
                showCloseButtonOnHover: false,
                hideProgressBar: false,
                closeButton: 'button',
                icon: true,
                rtl: false,
                ...options
            });
        },
        error: (message, options = {}) => {
            toast.error(message, {
                timeout: 5000,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                ...options
            });
        },
        warning: (message, options = {}) => {
            toast.warning(message, {
                timeout: 4000,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                ...options
            });
        },
        info: (message, options = {}) => {
            toast.info(message, {
                timeout: 3000,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                ...options
            });
        },
        clear: () => {
            toast.clear();
        },
        clearAll: () => {
            toast.clear();
        }
    };
}
