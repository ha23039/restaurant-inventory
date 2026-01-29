<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-purple-500 dark:focus:border-purple-500 focus:ring-purple-500 dark:focus:ring-purple-500 dark:placeholder-gray-400"
        v-model="model"
        ref="input"
    />
</template>
