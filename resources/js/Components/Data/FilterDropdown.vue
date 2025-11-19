<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, Array],
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
    label: {
        type: String,
        default: 'Filtrar',
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: 'Seleccionar filtro',
    },
    showClear: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const dropdownRef = ref(null);

const selectedLabel = computed(() => {
    if (props.multiple) {
        if (!props.modelValue || props.modelValue.length === 0) {
            return props.placeholder;
        }

        const selectedOptions = props.options.filter(opt =>
            props.modelValue.includes(opt.value)
        );

        if (selectedOptions.length === 1) {
            return selectedOptions[0].label;
        }

        return `${selectedOptions.length} seleccionados`;
    }

    if (!props.modelValue) {
        return props.placeholder;
    }

    const selected = props.options.find(opt => opt.value === props.modelValue);
    return selected ? selected.label : props.placeholder;
});

const hasSelection = computed(() => {
    if (props.multiple) {
        return props.modelValue && props.modelValue.length > 0;
    }
    return props.modelValue !== null && props.modelValue !== undefined && props.modelValue !== '';
});

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const selectOption = (option) => {
    if (props.multiple) {
        const currentValue = props.modelValue || [];
        const index = currentValue.indexOf(option.value);

        let newValue;
        if (index > -1) {
            newValue = currentValue.filter(v => v !== option.value);
        } else {
            newValue = [...currentValue, option.value];
        }

        emit('update:modelValue', newValue);
        emit('change', newValue);
    } else {
        emit('update:modelValue', option.value);
        emit('change', option.value);
        isOpen.value = false;
    }
};

const clearSelection = () => {
    const newValue = props.multiple ? [] : null;
    emit('update:modelValue', newValue);
    emit('change', newValue);
};

const isSelected = (option) => {
    if (props.multiple) {
        return props.modelValue && props.modelValue.includes(option.value);
    }
    return props.modelValue === option.value;
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="dropdownRef" class="relative inline-block text-left">
        <!-- Dropdown Button -->
        <button
            type="button"
            class="inline-flex items-center justify-between w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            @click="toggleDropdown"
        >
            <span class="flex items-center gap-2">
                <!-- Filter Icon -->
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                    />
                </svg>

                <span :class="hasSelection ? 'text-gray-900 font-semibold' : 'text-gray-500'">
                    {{ label }}: {{ selectedLabel }}
                </span>
            </span>

            <div class="flex items-center gap-1">
                <!-- Clear Button -->
                <button
                    v-if="showClear && hasSelection"
                    type="button"
                    class="text-gray-400 hover:text-gray-600"
                    @click.stop="clearSelection"
                >
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>

                <!-- Chevron Icon -->
                <svg
                    class="h-5 w-5 text-gray-400 transition-transform"
                    :class="{ 'rotate-180': isOpen }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </button>

        <!-- Dropdown Menu -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-show="isOpen"
                class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10"
            >
                <div class="py-1 max-h-60 overflow-y-auto" role="menu">
                    <button
                        v-for="option in options"
                        :key="option.value"
                        type="button"
                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 flex items-center justify-between"
                        :class="{
                            'bg-blue-50 text-blue-700': isSelected(option),
                            'text-gray-700': !isSelected(option),
                        }"
                        @click="selectOption(option)"
                    >
                        <span>{{ option.label }}</span>

                        <!-- Checkmark for selected items -->
                        <svg
                            v-if="isSelected(option)"
                            class="h-5 w-5 text-blue-600"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
