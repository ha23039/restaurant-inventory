import { ref } from 'vue';

export function useModal() {
    const isOpen = ref(false);
    const data = ref(null);

    const open = (modalData = null) => {
        data.value = modalData;
        isOpen.value = true;
    };

    const close = () => {
        isOpen.value = false;
        setTimeout(() => {
            data.value = null;
        }, 300); // Wait for close animation
    };

    const toggle = () => {
        if (isOpen.value) {
            close();
        } else {
            open();
        }
    };

    return {
        isOpen,
        data,
        open,
        close,
        toggle
    };
}
