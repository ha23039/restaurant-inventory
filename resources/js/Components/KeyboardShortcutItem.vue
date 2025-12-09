<template>
    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="iconPath" />
                </svg>
            </div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ description }}</span>
        </div>
        <div class="flex items-center space-x-1">
            <kbd
                v-for="(key, index) in keyArray"
                :key="index"
                class="px-2 py-1 text-xs font-semibold bg-white dark:bg-gray-600 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-500 rounded shadow-sm"
            >
                {{ key }}
            </kbd>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    keys: {
        type: String,
        required: true
    },
    macKeys: {
        type: String,
        default: null
    },
    description: {
        type: String,
        required: true
    },
    icon: {
        type: String,
        default: 'default'
    }
});

const isMac = computed(() => {
    return typeof navigator !== 'undefined' && navigator.platform.toUpperCase().indexOf('MAC') >= 0;
});

const displayKeys = computed(() => {
    return isMac.value && props.macKeys ? props.macKeys : props.keys;
});

const keyArray = computed(() => {
    return displayKeys.value.split('+');
});

const iconPath = computed(() => {
    const icons = {
        search: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
        cash: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
        expense: 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z',
        sidebar: 'M4 6h16M4 12h16M4 18h16',
        theme: 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z',
        help: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        default: 'M13 10V3L4 14h7v7l9-11h-7z'
    };
    return icons[props.icon] || icons.default;
});
</script>
