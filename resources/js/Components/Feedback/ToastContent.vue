<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'info',
        validator: (value) => ['success', 'error', 'warning', 'info'].includes(value),
    },
    title: {
        type: String,
        default: null,
    },
    message: {
        type: String,
        required: true,
    },
});

const iconClasses = computed(() => {
    const variants = {
        success: 'text-green-500',
        error: 'text-red-500',
        warning: 'text-yellow-500',
        info: 'text-blue-500',
    };

    return variants[props.type];
});

const iconPath = computed(() => {
    const icons = {
        success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    };

    return icons[props.type];
});
</script>

<template>
    <div class="flex items-start gap-3">
        <!-- Icon -->
        <div class="flex-shrink-0">
            <svg
                :class="iconClasses"
                class="h-6 w-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    :d="iconPath"
                />
            </svg>
        </div>

        <!-- Content -->
        <div class="flex-1">
            <p v-if="title" class="font-semibold text-gray-900 mb-1">
                {{ title }}
            </p>
            <p class="text-sm text-gray-700">
                {{ message }}
            </p>
        </div>
    </div>
</template>
