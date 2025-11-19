<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'danger', 'success', 'warning', 'info'].includes(value)
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    disabled: {
        type: Boolean,
        default: false
    },
    loading: {
        type: Boolean,
        default: false
    },
    type: {
        type: String,
        default: 'button'
    },
    outline: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['click']);

const buttonClasses = computed(() => {
    const base = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    const variants = {
        primary: props.outline
            ? 'border-2 border-blue-600 text-blue-600 hover:bg-blue-50 focus:ring-blue-500'
            : 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
        secondary: props.outline
            ? 'border-2 border-gray-600 text-gray-700 hover:bg-gray-50 focus:ring-gray-500'
            : 'bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-500',
        danger: props.outline
            ? 'border-2 border-red-600 text-red-600 hover:bg-red-50 focus:ring-red-500'
            : 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        success: props.outline
            ? 'border-2 border-green-600 text-green-600 hover:bg-green-50 focus:ring-green-500'
            : 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
        warning: props.outline
            ? 'border-2 border-yellow-600 text-yellow-600 hover:bg-yellow-50 focus:ring-yellow-500'
            : 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500',
        info: props.outline
            ? 'border-2 border-cyan-600 text-cyan-600 hover:bg-cyan-50 focus:ring-cyan-500'
            : 'bg-cyan-600 text-white hover:bg-cyan-700 focus:ring-cyan-500',
    };

    const sizes = {
        sm: 'px-3 py-1.5 text-sm',
        md: 'px-4 py-2 text-base',
        lg: 'px-6 py-3 text-lg'
    };

    return `${base} ${variants[props.variant]} ${sizes[props.size]}`;
});

const handleClick = (event) => {
    if (!props.disabled && !props.loading) {
        emit('click', event);
    }
};
</script>

<template>
    <button
        :type="type"
        :class="buttonClasses"
        :disabled="disabled || loading"
        @click="handleClick"
    >
        <svg
            v-if="loading"
            class="animate-spin -ml-1 mr-2 h-4 w-4"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <slot />
    </button>
</template>
