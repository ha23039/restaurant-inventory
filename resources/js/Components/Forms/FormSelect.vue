<script setup>
import FormGroup from './FormGroup.vue';
import BaseSelect from '@/Components/Base/BaseSelect.vue';

defineProps({
    modelValue: {
        type: [String, Number, Boolean, Object, Array],
        default: null,
    },
    label: {
        type: String,
        default: null,
    },
    options: {
        type: Array,
        required: true,
    },
    error: {
        type: String,
        default: null,
    },
    hint: {
        type: String,
        default: null,
    },
    required: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: 'Seleccionar...',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    multiple: {
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
    <FormGroup
        :label="label"
        :error="error"
        :hint="hint"
        :required="required"
    >
        <BaseSelect
            :model-value="modelValue"
            :options="options"
            :placeholder="placeholder"
            :disabled="disabled"
            :multiple="multiple"
            :size="size"
            :error="error"
            @update:model-value="$emit('update:modelValue', $event)"
            @change="$emit('change', $event)"
        />
    </FormGroup>
</template>
