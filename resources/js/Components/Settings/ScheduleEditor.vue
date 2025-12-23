<script setup>
import { ref, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({
            monday: { enabled: false, open: '09:00', close: '22:00' },
            tuesday: { enabled: false, open: '09:00', close: '22:00' },
            wednesday: { enabled: false, open: '09:00', close: '22:00' },
            thursday: { enabled: false, open: '09:00', close: '22:00' },
            friday: { enabled: false, open: '09:00', close: '22:00' },
            saturday: { enabled: false, open: '09:00', close: '22:00' },
            sunday: { enabled: false, open: '09:00', close: '22:00' },
        }),
    },
});

const emit = defineEmits(['update:modelValue']);

const schedule = ref({ ...props.modelValue });

watch(schedule, (newValue) => {
    emit('update:modelValue', newValue);
}, { deep: true });

const days = [
    { key: 'monday', label: 'Lunes' },
    { key: 'tuesday', label: 'Martes' },
    { key: 'wednesday', label: 'Miércoles' },
    { key: 'thursday', label: 'Jueves' },
    { key: 'friday', label: 'Viernes' },
    { key: 'saturday', label: 'Sábado' },
    { key: 'sunday', label: 'Domingo' },
];

const toggleAll = (enable) => {
    days.forEach(day => {
        schedule.value[day.key].enabled = enable;
    });
};

const copyToAll = (sourceDay) => {
    const source = schedule.value[sourceDay];
    days.forEach(day => {
        if (day.key !== sourceDay) {
            schedule.value[day.key] = { ...source };
        }
    });
};
</script>

<template>
    <div class="space-y-4">
        <!-- Quick Actions -->
        <div class="flex gap-2 pb-4 border-b border-gray-200 dark:border-gray-700">
            <button
                type="button"
                @click="toggleAll(true)"
                class="px-3 py-1.5 text-xs font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md transition-colors duration-200"
            >
                Abrir todos
            </button>
            <button
                type="button"
                @click="toggleAll(false)"
                class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-md transition-colors duration-200"
            >
                Cerrar todos
            </button>
        </div>

        <!-- Days Schedule -->
        <div class="space-y-3">
            <div
                v-for="day in days"
                :key="day.key"
                class="flex flex-col sm:flex-row sm:items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600"
            >
                <!-- Day Toggle -->
                <div class="flex items-center gap-3 sm:w-32">
                    <input
                        :id="`day-${day.key}`"
                        v-model="schedule[day.key].enabled"
                        type="checkbox"
                        class="w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500 dark:focus:ring-orange-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                    >
                    <label
                        :for="`day-${day.key}`"
                        class="text-sm font-medium cursor-pointer select-none"
                        :class="schedule[day.key].enabled
                            ? 'text-gray-900 dark:text-white'
                            : 'text-gray-400 dark:text-gray-500'"
                    >
                        {{ day.label }}
                    </label>
                </div>

                <!-- Time Inputs -->
                <div
                    v-if="schedule[day.key].enabled"
                    class="flex items-center gap-2 flex-1"
                >
                    <div class="flex-1">
                        <label :for="`${day.key}-open`" class="sr-only">Hora de apertura</label>
                        <input
                            :id="`${day.key}-open`"
                            v-model="schedule[day.key].open"
                            type="time"
                            class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 focus:border-orange-500 dark:focus:border-orange-600 focus:ring-orange-500 dark:focus:ring-orange-600 rounded-md shadow-sm text-sm"
                        >
                    </div>
                    <span class="text-gray-500 dark:text-gray-400 text-sm">a</span>
                    <div class="flex-1">
                        <label :for="`${day.key}-close`" class="sr-only">Hora de cierre</label>
                        <input
                            :id="`${day.key}-close`"
                            v-model="schedule[day.key].close"
                            type="time"
                            class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 focus:border-orange-500 dark:focus:border-orange-600 focus:ring-orange-500 dark:focus:ring-orange-600 rounded-md shadow-sm text-sm"
                        >
                    </div>

                    <!-- Copy Button -->
                    <button
                        type="button"
                        @click="copyToAll(day.key)"
                        title="Copiar este horario a todos los días"
                        class="p-2 text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 transition-colors duration-200"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>

                <!-- Closed Label -->
                <div
                    v-else
                    class="flex-1 text-sm text-gray-400 dark:text-gray-500 italic"
                >
                    Cerrado
                </div>
            </div>
        </div>

        <!-- Helper Text -->
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            Define el horario de atención del menú digital. Los clientes solo podrán realizar pedidos durante estas horas.
        </p>
    </div>
</template>
