import { h } from 'vue';
import { useToast as useVueToast } from 'vue-toastification';
import ToastContent from '@/Components/Feedback/ToastContent.vue';

export function useToast() {
    const toast = useVueToast();

    const defaultOptions = {
        closeOnClick: true,
        pauseOnFocusLoss: true,
        pauseOnHover: true,
        draggable: true,
        draggablePercent: 0.6,
        showCloseButtonOnHover: false,
        hideProgressBar: false,
        closeButton: 'button',
        icon: false, // We use our custom component's icon
        rtl: false,
    };

    const createContent = (type, message, title = null) => {
        return {
            component: ToastContent,
            props: {
                type,
                message,
                title,
            },
        };
    };

    return {
        success: (message, options = {}) => {
            const { title, ...restOptions } = options;
            const content = typeof message === 'string' && !options.disableCustomContent
                ? createContent('success', message, title)
                : message;

            toast.success(content, {
                ...defaultOptions,
                timeout: 3000,
                ...restOptions,
            });
        },

        error: (message, options = {}) => {
            const { title, ...restOptions } = options;
            const content = typeof message === 'string' && !options.disableCustomContent
                ? createContent('error', message, title)
                : message;

            toast.error(content, {
                ...defaultOptions,
                timeout: 5000,
                ...restOptions,
            });
        },

        warning: (message, options = {}) => {
            const { title, ...restOptions } = options;
            const content = typeof message === 'string' && !options.disableCustomContent
                ? createContent('warning', message, title)
                : message;

            toast.warning(content, {
                ...defaultOptions,
                timeout: 4000,
                ...restOptions,
            });
        },

        info: (message, options = {}) => {
            const { title, ...restOptions } = options;
            const content = typeof message === 'string' && !options.disableCustomContent
                ? createContent('info', message, title)
                : message;

            toast.info(content, {
                ...defaultOptions,
                timeout: 3000,
                ...restOptions,
            });
        },

        /**
         * Show a loading toast that can be updated later
         * @param {string} message - Loading message
         * @param {object} options - Toast options
         * @returns {number} Toast ID for updating
         */
        loading: (message, options = {}) => {
            const { title, ...restOptions } = options;
            const content = h(ToastContent, {
                type: 'info',
                message,
                title: title || 'Cargando...',
            });

            return toast(content, {
                ...defaultOptions,
                timeout: false, // No auto-close for loading
                closeButton: false,
                ...restOptions,
            });
        },

        /**
         * Update an existing toast
         * @param {number} toastId - Toast ID to update
         * @param {object} options - Update options
         */
        update: (toastId, options = {}) => {
            const { type = 'info', message, title, ...restOptions } = options;
            const content = createContent(type, message, title);

            toast.update(toastId, {
                content,
                options: {
                    ...defaultOptions,
                    timeout: type === 'error' ? 5000 : 3000,
                    closeButton: 'button',
                    ...restOptions,
                },
            });
        },

        /**
         * Promise toast - automatically updates based on promise resolution
         * @param {Promise} promise - Promise to track
         * @param {object} messages - Messages for pending, success, error states
         * @param {object} options - Toast options
         */
        promise: async (promise, messages = {}, options = {}) => {
            const {
                pending = 'Procesando...',
                success = 'Completado exitosamente',
                error = 'Ocurrió un error',
            } = messages;

            const toastId = toast.loading(
                h(ToastContent, {
                    type: 'info',
                    message: pending,
                    title: 'Procesando...',
                }),
                {
                    ...defaultOptions,
                    timeout: false,
                    closeButton: false,
                    ...options,
                }
            );

            try {
                const result = await promise;

                toast.update(toastId, {
                    content: createContent('success', success),
                    options: {
                        ...defaultOptions,
                        timeout: 3000,
                        closeButton: 'button',
                    },
                });

                return result;
            } catch (err) {
                const errorMessage = typeof error === 'function' ? error(err) : error;

                toast.update(toastId, {
                    content: createContent('error', errorMessage),
                    options: {
                        ...defaultOptions,
                        timeout: 5000,
                        closeButton: 'button',
                    },
                });

                throw err;
            }
        },

        /**
         * Dismiss a specific toast
         * @param {number} toastId - Toast ID to dismiss
         */
        dismiss: (toastId) => {
            toast.dismiss(toastId);
        },

        /**
         * Clear all toasts
         */
        clear: () => {
            toast.clear();
        },

        /**
         * Alias for clear
         */
        clearAll: () => {
            toast.clear();
        },
    };
}
