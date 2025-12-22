<script setup>
import DigitalMenuLayout from '@/Layouts/DigitalMenuLayout.vue';

const props = defineProps({
    message: {
        type: String,
        default: 'El menu digital no esta disponible en este momento.',
    },
});
</script>

<template>
    <DigitalMenuLayout>
        <div class="min-h-[60vh] flex items-center justify-center px-4">
            <div class="text-center max-w-md">
                <!-- Icon -->
                <div class="mb-6">
                    <div class="mx-auto w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Message -->
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                    Menu Digital No Disponible
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    {{ message }}
                </p>

                <!-- Contact Info -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Puedes contactarnos directamente:
                    </p>
                    <div class="space-y-3">
                        <a
                            href="tel:+50312345678"
                            class="flex items-center justify-center space-x-2 text-orange-600 dark:text-orange-400 hover:underline"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>Llamar al restaurante</span>
                        </a>
                    </div>
                </div>

                <!-- Back Home Link -->
                <div class="mt-6">
                    <a
                        href="/"
                        class="text-sm text-gray-500 dark:text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 hover:underline"
                    >
                        Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </DigitalMenuLayout>
</template>
