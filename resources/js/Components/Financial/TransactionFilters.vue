<template>
    <BaseCard class="bg-white">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Filtros Avanzados
                </h3>
                <div class="flex items-center gap-2">
                    <BaseButton
                        v-if="hasActiveFilters"
                        variant="secondary"
                        size="sm"
                        @click="clearFilters"
                    >
                        Limpiar Filtros
                    </BaseButton>
                    <button
                        type="button"
                        class="text-gray-400 hover:text-gray-600"
                        @click="toggleExpanded"
                    >
                        <svg
                            :class="['w-5 h-5 transition-transform', isExpanded ? 'rotate-180' : '']"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Active Filters Summary (when collapsed) -->
            <div v-if="!isExpanded && hasActiveFilters" class="flex flex-wrap gap-2 mb-4">
                <BaseBadge
                    v-if="localFilters.search"
                    variant="info"
                    size="sm"
                    class="flex items-center gap-1"
                >
                    <span>Búsqueda: {{ localFilters.search }}</span>
                    <button
                        type="button"
                        class="ml-1 hover:text-blue-700"
                        @click="removeFilter('search')"
                    >
                        ×
                    </button>
                </BaseBadge>

                <BaseBadge
                    v-if="localFilters.type"
                    variant="info"
                    size="sm"
                    class="flex items-center gap-1"
                >
                    <span>Tipo: {{ localFilters.type === 'entrada' ? 'Ingreso' : 'Egreso' }}</span>
                    <button
                        type="button"
                        class="ml-1 hover:text-blue-700"
                        @click="removeFilter('type')"
                    >
                        ×
                    </button>
                </BaseBadge>

                <BaseBadge
                    v-if="localFilters.category"
                    variant="info"
                    size="sm"
                    class="flex items-center gap-1"
                >
                    <span>Categoría: {{ getCategoryLabel(localFilters.category) }}</span>
                    <button
                        type="button"
                        class="ml-1 hover:text-blue-700"
                        @click="removeFilter('category')"
                    >
                        ×
                    </button>
                </BaseBadge>

                <BaseBadge
                    v-if="localFilters.date_from || localFilters.date_to"
                    variant="info"
                    size="sm"
                    class="flex items-center gap-1"
                >
                    <span>
                        Fecha: {{ localFilters.date_from }} - {{ localFilters.date_to }}
                    </span>
                    <button
                        type="button"
                        class="ml-1 hover:text-blue-700"
                        @click="removeDateRange"
                    >
                        ×
                    </button>
                </BaseBadge>
            </div>

            <!-- Filter Form (when expanded) -->
            <div v-if="isExpanded" class="space-y-4">
                <!-- Row 1: Search and Type -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <SearchBar
                        v-model="localFilters.search"
                        placeholder="Buscar por descripción o notas..."
                        @search="handleSearch"
                    />

                    <FormSelect
                        v-model="localFilters.type"
                        :options="typeOptions"
                        label="Tipo de Transacción"
                        placeholder="Todos los tipos"
                        @change="applyFilters"
                    />
                </div>

                <!-- Row 2: Category and User -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FilterDropdown
                        v-model="localFilters.category"
                        :options="categoryOptions"
                        label="Categoría"
                        placeholder="Todas las categorías"
                        @change="applyFilters"
                    />

                    <FormSelect
                        v-if="users && users.length > 0"
                        v-model="localFilters.user_id"
                        :options="userOptions"
                        label="Usuario"
                        placeholder="Todos los usuarios"
                        @change="applyFilters"
                    />
                </div>

                <!-- Row 3: Date Range -->
                <DateRangePicker
                    v-model:from="localFilters.date_from"
                    v-model:to="localFilters.date_to"
                    @change="applyFilters"
                />

                <!-- Row 4: Amount Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FormInput
                        v-model="localFilters.amount_min"
                        type="number"
                        step="0.01"
                        label="Monto Mínimo"
                        placeholder="0.00"
                        @change="applyFilters"
                    />

                    <FormInput
                        v-model="localFilters.amount_max"
                        type="number"
                        step="0.01"
                        label="Monto Máximo"
                        placeholder="999999.99"
                        @change="applyFilters"
                    />
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <BaseButton
                        variant="secondary"
                        size="md"
                        @click="clearFilters"
                    >
                        Limpiar Filtros
                    </BaseButton>
                    <BaseButton
                        variant="primary"
                        size="md"
                        @click="applyFilters"
                    >
                        Aplicar Filtros
                    </BaseButton>
                </div>
            </div>
        </div>
    </BaseCard>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import BaseCard from '@/Components/Base/BaseCard.vue';
import BaseBadge from '@/Components/Base/BaseBadge.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import SearchBar from '@/Components/Data/SearchBar.vue';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import FilterDropdown from '@/Components/Data/FilterDropdown.vue';
import DateRangePicker from '@/Components/Financial/DateRangePicker.vue';

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({})
    },
    categories: {
        type: Array,
        default: () => []
    },
    users: {
        type: Array,
        default: () => []
    },
    expandedByDefault: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:filters', 'apply', 'clear']);

const isExpanded = ref(props.expandedByDefault);
const localFilters = ref({ ...props.filters });

const typeOptions = [
    { value: '', label: 'Todos los tipos' },
    { value: 'entrada', label: 'Ingresos' },
    { value: 'salida', label: 'Egresos' },
];

const categoryOptions = computed(() => [
    { value: '', label: 'Todas las categorías' },
    ...props.categories.map(cat => ({
        value: cat.value,
        label: cat.label
    }))
]);

const userOptions = computed(() => [
    { value: '', label: 'Todos los usuarios' },
    ...props.users.map(user => ({
        value: user.id,
        label: `${user.name} (${user.role})`
    }))
]);

const hasActiveFilters = computed(() => {
    return Object.keys(localFilters.value).some(key => {
        const value = localFilters.value[key];
        return value !== null && value !== undefined && value !== '';
    });
});

const getCategoryLabel = (categoryValue) => {
    const category = props.categories.find(cat => cat.value === categoryValue);
    return category ? category.label : categoryValue;
};

const toggleExpanded = () => {
    isExpanded.value = !isExpanded.value;
};

const handleSearch = () => {
    applyFilters();
};

const applyFilters = () => {
    emit('update:filters', { ...localFilters.value });
    emit('apply', { ...localFilters.value });
};

const clearFilters = () => {
    localFilters.value = {
        search: '',
        type: '',
        category: '',
        date_from: '',
        date_to: '',
        user_id: '',
        amount_min: '',
        amount_max: '',
    };
    applyFilters();
    emit('clear');
};

const removeFilter = (filterKey) => {
    localFilters.value[filterKey] = '';
    applyFilters();
};

const removeDateRange = () => {
    localFilters.value.date_from = '';
    localFilters.value.date_to = '';
    applyFilters();
};

// Watch for external filter changes
watch(() => props.filters, (newFilters) => {
    localFilters.value = { ...newFilters };
}, { deep: true });
</script>
