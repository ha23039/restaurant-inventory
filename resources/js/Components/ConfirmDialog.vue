<script setup>
import { computed, watch } from 'vue';
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';
import {
    ExclamationTriangleIcon,
    ExclamationCircleIcon,
    InformationCircleIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

const { isOpen, dialogConfig, handleConfirm, handleCancel } = useConfirmDialog();

// Configuración de estilos según el tipo
const typeConfig = computed(() => {
    const configs = {
        danger: {
            icon: ExclamationTriangleIcon,
            iconBg: 'bg-red-100 dark:bg-red-900/30',
            iconColor: 'text-red-600 dark:text-red-400',
            confirmBg: 'bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600',
        },
        warning: {
            icon: ExclamationCircleIcon,
            iconBg: 'bg-amber-100 dark:bg-amber-900/30',
            iconColor: 'text-amber-600 dark:text-amber-400',
            confirmBg: 'bg-amber-600 hover:bg-amber-700 dark:bg-amber-700 dark:hover:bg-amber-600',
        },
        info: {
            icon: InformationCircleIcon,
            iconBg: 'bg-blue-100 dark:bg-blue-900/30',
            iconColor: 'text-blue-600 dark:text-blue-400',
            confirmBg: 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600',
        },
    };
    return configs[dialogConfig.value.type] || configs.danger;
});
</script>

<template>
    <TransitionRoot v-if="isOpen" appear :show="isOpen" as="template">
        <Dialog as="div" class="relative z-50" @close="handleCancel">
            <!-- Backdrop -->
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/50 dark:bg-black/70" />
            </TransitionChild>

            <!-- Dialog container -->
            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all"
                        >
                            <!-- Header con icono y título -->
                            <div class="flex items-start gap-4">
                                <!-- Icono -->
                                <div
                                    class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-full"
                                    :class="typeConfig.iconBg"
                                >
                                    <component
                                        :is="typeConfig.icon"
                                        class="w-6 h-6"
                                        :class="typeConfig.iconColor"
                                        aria-hidden="true"
                                    />
                                </div>

                                <!-- Título y mensaje -->
                                <div class="flex-1 min-w-0">
                                    <DialogTitle
                                        as="h3"
                                        class="text-lg font-semibold leading-6 text-gray-900 dark:text-white"
                                    >
                                        {{ dialogConfig.title }}
                                    </DialogTitle>
                                    <p
                                        v-if="dialogConfig.message"
                                        class="mt-2 text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ dialogConfig.message }}
                                    </p>
                                </div>

                                <!-- Botón cerrar -->
                                <button
                                    type="button"
                                    class="flex-shrink-0 rounded-lg p-1.5 text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    @click="handleCancel"
                                >
                                    <span class="sr-only">Cerrar</span>
                                    <XMarkIcon class="w-5 h-5" aria-hidden="true" />
                                </button>
                            </div>

                            <!-- Botones de acción -->
                            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                                <button
                                    type="button"
                                    class="inline-flex justify-center rounded-lg px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800"
                                    @click="handleCancel"
                                >
                                    {{ dialogConfig.cancelText }}
                                </button>
                                <button
                                    ref="confirmButtonRef"
                                    type="button"
                                    class="inline-flex justify-center rounded-lg px-4 py-2.5 text-sm font-semibold text-white transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800"
                                    :class="[typeConfig.confirmBg, 'focus-visible:ring-' + dialogConfig.type + '-500']"
                                    @click="handleConfirm"
                                >
                                    {{ dialogConfig.confirmText }}
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
