<script setup>
defineProps({
    title: {
        type: String,
        default: ''
    },
    padding: {
        type: Boolean,
        default: true
    },
    shadow: {
        type: String,
        default: 'sm',
        validator: (value) => ['none', 'sm', 'md', 'lg'].includes(value)
    }
});
</script>

<template>
    <div :class="[
        'bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden',
        {
            'shadow-none': shadow === 'none',
            'shadow-sm': shadow === 'sm',
            'shadow-md': shadow === 'md',
            'shadow-lg': shadow === 'lg'
        }
    ]">
        <div v-if="title || $slots.header" class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750">
            <slot name="header">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
            </slot>
        </div>

        <div :class="{ 'p-6': padding }">
            <slot />
        </div>

        <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750">
            <slot name="footer" />
        </div>
    </div>
</template>
