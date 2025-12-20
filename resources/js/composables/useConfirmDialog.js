/**
 * Composable para diálogos de confirmación
 * Reemplaza el confirm() nativo del navegador con un modal bonito
 */
import { ref, readonly } from 'vue';

// Estado global del diálogo
const isOpen = ref(false);
const dialogConfig = ref({
    title: '¿Confirmar acción?',
    message: '',
    confirmText: 'Confirmar',
    cancelText: 'Cancelar',
    type: 'danger', // 'danger' | 'warning' | 'info'
});

// Callbacks para resolver la promesa
let resolvePromise = null;

/**
 * Hook principal para mostrar diálogos de confirmación
 */
export function useConfirmDialog() {
    /**
     * Muestra un diálogo de confirmación y retorna una promesa
     * @param {Object} options - Opciones del diálogo
     * @param {string} options.title - Título del diálogo
     * @param {string} options.message - Mensaje descriptivo
     * @param {string} options.confirmText - Texto del botón de confirmar
     * @param {string} options.cancelText - Texto del botón de cancelar
     * @param {string} options.type - Tipo de diálogo: 'danger', 'warning', 'info'
     * @returns {Promise<boolean>} - true si confirmó, false si canceló
     */
    const confirm = (options = {}) => {
        return new Promise((resolve) => {
            dialogConfig.value = {
                title: options.title || '¿Confirmar acción?',
                message: options.message || '',
                confirmText: options.confirmText || 'Confirmar',
                cancelText: options.cancelText || 'Cancelar',
                type: options.type || 'danger',
            };

            resolvePromise = resolve;
            isOpen.value = true;
        });
    };

    /**
     * Confirma el diálogo
     */
    const handleConfirm = () => {
        isOpen.value = false;
        if (resolvePromise) {
            resolvePromise(true);
            resolvePromise = null;
        }
    };

    /**
     * Cancela el diálogo
     */
    const handleCancel = () => {
        isOpen.value = false;
        if (resolvePromise) {
            resolvePromise(false);
            resolvePromise = null;
        }
    };

    return {
        // Estado (readonly para evitar mutaciones externas)
        isOpen: readonly(isOpen),
        dialogConfig: readonly(dialogConfig),

        // Métodos
        confirm,
        handleConfirm,
        handleCancel,
    };
}
