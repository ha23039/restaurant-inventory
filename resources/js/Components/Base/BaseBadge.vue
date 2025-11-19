<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (value) => [
            'default',
            'primary',
            'success',
            'warning',
            'danger',
            'info',
        ].includes(value),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    dot: {
        type: Boolean,
        default: false,
    },
    removable: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['remove']);

const badgeClasses = computed(() => {
    const base = 'inline-flex items-center font-medium rounded-full';

    const sizes = {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-2.5 py-0.5 text-sm',
        lg: 'px-3 py-1 text-base',
    };

    const variants = {
        default: 'bg-gray-100 text-gray-800',
        primary: 'bg-blue-100 text-blue-800',
        success: 'bg-green-100 text-green-800',
        warning: 'bg-yellow-100 text-yellow-800',
        danger: 'bg-red-100 text-red-800',
        info: 'bg-indigo-100 text-indigo-800',
    };

    return [base, sizes[props.size], variants[props.variant]].filter(Boolean).join(' ');
});

const dotClasses = computed(() => {
    const variants = {
        default: 'bg-gray-400',
        primary: 'bg-blue-400',
        success: 'bg-green-400',
        warning: 'bg-yellow-400',
        danger: 'bg-red-400',
        info: 'bg-indigo-400',
    };

    return ['h-2 w-2 rounded-full mr-1.5', variants[props.variant]].join(' ');
});

const removeButtonClasses = computed(() => {
    const variants = {
        default: 'text-gray-400 hover:bg-gray-200 hover:text-gray-500',
        primary: 'text-blue-400 hover:bg-blue-200 hover:text-blue-500',
        success: 'text-green-400 hover:bg-green-200 hover:text-green-500',
        warning: 'text-yellow-400 hover:bg-yellow-200 hover:text-yellow-500',
        danger: 'text-red-400 hover:bg-red-200 hover:text-red-500',
        info: 'text-indigo-400 hover:bg-indigo-200 hover:text-indigo-500',
    };

    return [
        'ml-1 inline-flex rounded-full p-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2',
        variants[props.variant],
    ].join(' ');
});

const handleRemove = () => {
    emit('remove');
};
</script>

<template>
    <span :class="badgeClasses">
        <span v-if="dot" :class="dotClasses"></span>
        <slot />
        <button
            v-if="removable"
            type="button"
            :class="removeButtonClasses"
            @click="handleRemove"
        >
            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                <path
                    fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                />
            </svg>
        </button>
    </span>
</template>
