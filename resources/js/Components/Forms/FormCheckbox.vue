<script setup>
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue';

defineProps({
    modelValue: {
        type: [Boolean, Array],
        default: false,
    },
    value: {
        type: [String, Number, Boolean],
        default: null,
    },
    label: {
        type: String,
        default: null,
    },
    description: {
        type: String,
        default: null,
    },
    error: {
        type: String,
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: 'md',
    },
});

defineEmits(['update:modelValue', 'change']);
</script>

<template>
    <BaseCheckbox
        :model-value="modelValue"
        :value="value"
        :label="label"
        :description="description"
        :error="error"
        :disabled="disabled"
        :size="size"
        @update:model-value="$emit('update:modelValue', $event)"
        @change="$emit('change', $event)"
    />
</template>
