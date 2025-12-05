<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, Boolean, Object, Array],
        default: null,
    },
    options: {
        type: Array,
        required: true,
        validator: (value) => {
            return value.every(option =>
                typeof option === 'object' && 'value' in option && 'label' in option
            );
        },
    },
    placeholder: {
        type: String,
        default: 'Seleccionar...',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const selectClasses = computed(() => {
    const base = 'block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white';

    const sizes = {
        sm: 'text-sm py-1 px-2',
        md: 'text-base py-2 px-3',
        lg: 'text-lg py-3 px-4',
    };

    const state = props.error
        ? 'border-red-300 dark:border-red-500 focus:border-red-500 focus:ring-red-500'
        : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500';

    const disabled = props.disabled ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed opacity-60' : '';

    return [base, sizes[props.size], state, disabled].filter(Boolean).join(' ');
});

const handleChange = (event) => {
    let value;

    if (props.multiple) {
        const selectedOptions = Array.from(event.target.selectedOptions);
        value = selectedOptions.map(option => {
            const opt = props.options.find(o => String(o.value) === option.value);
            return opt ? opt.value : option.value;
        });
    } else {
        const selectedOption = props.options.find(o => String(o.value) === event.target.value);
        value = selectedOption ? selectedOption.value : event.target.value;
    }

    emit('update:modelValue', value);
    emit('change', value);
};
</script>

<template>
    <div>
        <select
            :value="modelValue"
            :class="selectClasses"
            :disabled="disabled"
            :multiple="multiple"
            @change="handleChange"
        >
            <option v-if="!multiple && placeholder" value="" disabled>
                {{ placeholder }}
            </option>
            <option
                v-for="option in options"
                :key="option.value"
                :value="option.value"
                :disabled="option.disabled"
            >
                {{ option.label }}
            </option>
        </select>

        <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ error }}
        </p>
    </div>
</template>
