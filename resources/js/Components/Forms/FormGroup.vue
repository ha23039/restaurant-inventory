<script setup>
defineProps({
    label: {
        type: String,
        default: ''
    },
    for: {
        type: String,
        default: ''
    },
    error: {
        type: String,
        default: ''
    },
    required: {
        type: Boolean,
        default: false
    },
    hint: {
        type: String,
        default: ''
    }
});
</script>

<template>
    <div class="space-y-1">
        <label v-if="label" :for="for" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <slot />

        <p v-if="hint && !error" class="text-sm text-gray-500 dark:text-gray-400">
            {{ hint }}
        </p>

        <p v-if="error" class="text-sm text-red-600 dark:text-red-400">
            {{ error }}
        </p>
    </div>
</template>
