<script setup>
import { ref, watch } from 'vue';
import { useDebounce } from '@/composables';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Buscar...',
    },
    debounce: {
        type: Number,
        default: 300,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    showClear: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['update:modelValue', 'search', 'clear']);

const searchValue = ref(props.modelValue);
const debouncedSearch = useDebounce(searchValue, props.debounce);

watch(() => props.modelValue, (newValue) => {
    searchValue.value = newValue;
});

watch(debouncedSearch, (newValue) => {
    emit('update:modelValue', newValue);
    emit('search', newValue);
});

const handleInput = (event) => {
    searchValue.value = event.target.value;
};

const handleClear = () => {
    searchValue.value = '';
    emit('update:modelValue', '');
    emit('clear');
};
</script>

<template>
    <div class="relative">
        <!-- Search Icon -->
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg
                v-if="!loading"
                class="w-5 h-5 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
            </svg>

            <!-- Loading Spinner -->
            <svg
                v-else
                class="w-5 h-5 text-gray-400 animate-spin"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
            </svg>
        </div>

        <!-- Input Field -->
        <input
            type="text"
            :value="searchValue"
            :placeholder="placeholder"
            :disabled="disabled"
            class="block w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed"
            @input="handleInput"
        />

        <!-- Clear Button -->
        <div
            v-if="showClear && searchValue"
            class="absolute inset-y-0 right-0 flex items-center pr-3"
        >
            <button
                type="button"
                class="text-gray-400 hover:text-gray-500 focus:outline-none"
                @click="handleClear"
            >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>
        </div>
    </div>
</template>
