<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: ''
    },
    type: {
        type: String,
        default: 'text'
    },
    placeholder: {
        type: String,
        default: ''
    },
    disabled: {
        type: Boolean,
        default: false
    },
    error: {
        type: String,
        default: ''
    },
    success: {
        type: Boolean,
        default: false
    },
    icon: {
        type: String,
        default: null
    }
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const inputClasses = computed(() => {
    const base = 'block w-full rounded-lg border px-4 py-2 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed bg-white dark:bg-gray-700 text-gray-900 dark:text-white';

    if (props.error) {
        return `${base} border-red-300 dark:border-red-500 placeholder-red-300 dark:placeholder-red-400 focus:border-red-500 focus:ring-red-500`;
    }

    if (props.success) {
        return `${base} border-green-300 dark:border-green-500 placeholder-green-300 dark:placeholder-green-400 focus:border-green-500 focus:ring-green-500`;
    }

    return `${base} border-gray-300 dark:border-gray-600 placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500`;
});

const handleInput = (event) => {
    emit('update:modelValue', event.target.value);
};
</script>

<template>
    <div class="relative">
        <input
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :class="inputClasses"
            @input="handleInput"
            @blur="emit('blur', $event)"
            @focus="emit('focus', $event)"
        />
        <div v-if="icon" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <slot name="icon">
                <span class="text-gray-400">{{ icon }}</span>
            </slot>
        </div>
    </div>
</template>
