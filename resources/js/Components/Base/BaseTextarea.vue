<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    },
    rows: {
        type: Number,
        default: 4,
    },
    maxlength: {
        type: Number,
        default: null,
    },
    showCount: {
        type: Boolean,
        default: false,
    },
    resize: {
        type: String,
        default: 'vertical',
        validator: (value) => ['none', 'vertical', 'horizontal', 'both'].includes(value),
    },
});

const emit = defineEmits(['update:modelValue', 'change', 'blur', 'focus']);

const textareaRef = ref(null);

const textareaClasses = computed(() => {
    const base = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500';

    const state = props.error
        ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
        : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500';

    const disabled = props.disabled ? 'bg-gray-100 cursor-not-allowed opacity-60' : '';

    const resizeClasses = {
        none: 'resize-none',
        vertical: 'resize-y',
        horizontal: 'resize-x',
        both: 'resize',
    };

    return [base, state, disabled, resizeClasses[props.resize]].filter(Boolean).join(' ');
});

const characterCount = computed(() => {
    return props.modelValue.length;
});

const handleInput = (event) => {
    emit('update:modelValue', event.target.value);
    emit('change', event.target.value);
};

const handleBlur = (event) => {
    emit('blur', event);
};

const handleFocus = (event) => {
    emit('focus', event);
};

const focus = () => {
    textareaRef.value?.focus();
};

defineExpose({
    focus,
    textareaRef,
});
</script>

<template>
    <div>
        <textarea
            ref="textareaRef"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :rows="rows"
            :maxlength="maxlength"
            :class="textareaClasses"
            @input="handleInput"
            @blur="handleBlur"
            @focus="handleFocus"
        />

        <div v-if="showCount || error" class="mt-1 flex items-center justify-between">
            <p v-if="error" class="text-sm text-red-600">
                {{ error }}
            </p>
            <p v-if="showCount" class="text-sm text-gray-500 ml-auto">
                {{ characterCount }}{{ maxlength ? ` / ${maxlength}` : '' }}
            </p>
        </div>
    </div>
</template>
