<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <label class="block text-sm font-medium text-gray-700">
                Rango de Fechas
            </label>
            <div class="flex items-center gap-2">
                <button
                    v-for="preset in presets"
                    :key="preset.value"
                    type="button"
                    :class="[
                        'text-xs px-2 py-1 rounded border transition-colors',
                        isPresetActive(preset)
                            ? 'bg-blue-500 text-white border-blue-500'
                            : 'bg-white text-gray-600 border-gray-300 hover:border-blue-500 hover:text-blue-500'
                    ]"
                    @click="applyPreset(preset)"
                >
                    {{ preset.label }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormInput
                :model-value="from"
                type="date"
                label="Fecha Inicio"
                :max="to || undefined"
                @update:model-value="handleFromChange"
            />

            <FormInput
                :model-value="to"
                type="date"
                label="Fecha Fin"
                :min="from || undefined"
                :max="maxDate"
                @update:model-value="handleToChange"
            />
        </div>

        <div v-if="from && to" class="text-sm text-gray-600">
            <span class="font-medium">{{ getDaysDifference() }}</span> días seleccionados
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import FormInput from '@/Components/Form/FormInput.vue';

const props = defineProps({
    from: {
        type: String,
        default: ''
    },
    to: {
        type: String,
        default: ''
    },
    maxDate: {
        type: String,
        default: () => new Date().toISOString().split('T')[0]
    }
});

const emit = defineEmits(['update:from', 'update:to', 'change']);

const presets = [
    {
        label: 'Hoy',
        value: 'today',
        getRange: () => {
            const today = new Date();
            return {
                from: today.toISOString().split('T')[0],
                to: today.toISOString().split('T')[0]
            };
        }
    },
    {
        label: 'Ayer',
        value: 'yesterday',
        getRange: () => {
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            return {
                from: yesterday.toISOString().split('T')[0],
                to: yesterday.toISOString().split('T')[0]
            };
        }
    },
    {
        label: 'Esta Semana',
        value: 'this_week',
        getRange: () => {
            const today = new Date();
            const dayOfWeek = today.getDay();
            const diff = dayOfWeek === 0 ? 6 : dayOfWeek - 1; // Monday is first day
            const monday = new Date(today);
            monday.setDate(today.getDate() - diff);

            return {
                from: monday.toISOString().split('T')[0],
                to: today.toISOString().split('T')[0]
            };
        }
    },
    {
        label: 'Este Mes',
        value: 'this_month',
        getRange: () => {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

            return {
                from: firstDay.toISOString().split('T')[0],
                to: today.toISOString().split('T')[0]
            };
        }
    },
    {
        label: 'Últimos 7 días',
        value: 'last_7_days',
        getRange: () => {
            const today = new Date();
            const sevenDaysAgo = new Date();
            sevenDaysAgo.setDate(today.getDate() - 6);

            return {
                from: sevenDaysAgo.toISOString().split('T')[0],
                to: today.toISOString().split('T')[0]
            };
        }
    },
    {
        label: 'Últimos 30 días',
        value: 'last_30_days',
        getRange: () => {
            const today = new Date();
            const thirtyDaysAgo = new Date();
            thirtyDaysAgo.setDate(today.getDate() - 29);

            return {
                from: thirtyDaysAgo.toISOString().split('T')[0],
                to: today.toISOString().split('T')[0]
            };
        }
    }
];

const handleFromChange = (value) => {
    emit('update:from', value);
    emit('change', { from: value, to: props.to });
};

const handleToChange = (value) => {
    emit('update:to', value);
    emit('change', { from: props.from, to: value });
};

const applyPreset = (preset) => {
    const range = preset.getRange();
    emit('update:from', range.from);
    emit('update:to', range.to);
    emit('change', range);
};

const isPresetActive = (preset) => {
    if (!props.from || !props.to) return false;

    const range = preset.getRange();
    return props.from === range.from && props.to === range.to;
};

const getDaysDifference = () => {
    if (!props.from || !props.to) return 0;

    const fromDate = new Date(props.from);
    const toDate = new Date(props.to);
    const diffTime = Math.abs(toDate - fromDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

    return diffDays;
};
</script>
