<script setup>
import FormGroup from './FormGroup.vue';
import BaseInput from '@/Components/Base/BaseInput.vue';

defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    label: {
        type: String,
        default: null,
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
    type: {
        type: String,
        default: 'text',
    },
    placeholder: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['update:modelValue', 'change', 'blur', 'focus']);
</script>

<template>
    <FormGroup
        :label="label"
        :error="error"
        :hint="hint"
        :required="required"
    >
        <BaseInput
            :model-value="modelValue"
            :type="type"
            :placeholder="placeholder"
            :disabled="disabled"
            :error="error"
            @update:model-value="$emit('update:modelValue', $event)"
            @change="$emit('change', $event)"
            @blur="$emit('blur', $event)"
            @focus="$emit('focus', $event)"
        >
            <template v-if="$slots.icon" #icon>
                <slot name="icon" />
            </template>
        </BaseInput>
    </FormGroup>
</template>
