<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, Boolean],
        required: true,
    },
    value: {
        type: [String, Number, Boolean],
        required: true,
    },
    label: {
        type: String,
        default: null,
    },
    description: {
        type: String,
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const isChecked = computed(() => {
    return props.modelValue === props.value;
});

const radioClasses = computed(() => {
    const base = 'border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500';

    const sizes = {
        sm: 'h-4 w-4',
        md: 'h-5 w-5',
        lg: 'h-6 w-6',
    };

    const state = props.error
        ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
        : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500';

    const disabled = props.disabled ? 'bg-gray-100 cursor-not-allowed opacity-60' : 'cursor-pointer';

    return [base, sizes[props.size], state, disabled].filter(Boolean).join(' ');
});

const labelClasses = computed(() => {
    const sizes = {
        sm: 'text-sm',
        md: 'text-base',
        lg: 'text-lg',
    };

    const disabled = props.disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer';

    return ['font-medium text-gray-700', sizes[props.size], disabled].filter(Boolean).join(' ');
});

const handleChange = () => {
    if (!props.disabled) {
        emit('update:modelValue', props.value);
        emit('change', props.value);
    }
};
</script>

<template>
    <div class="flex items-start">
        <div class="flex items-center h-5">
            <input
                :id="String(value)"
                type="radio"
                :checked="isChecked"
                :value="value"
                :disabled="disabled"
                :class="radioClasses"
                @change="handleChange"
            />
        </div>

        <div v-if="label || description" class="ml-3">
            <label v-if="label" :for="String(value)" :class="labelClasses">
                {{ label }}
            </label>
            <p v-if="description" class="text-sm text-gray-500">
                {{ description }}
            </p>
            <p v-if="error" class="mt-1 text-sm text-red-600">
                {{ error }}
            </p>
        </div>
    </div>
</template>
