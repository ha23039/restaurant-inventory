<script setup>
import { ref, computed } from 'vue';
import SlideOver from '@/Components/SlideOver.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    show: Boolean,
    menuItems: Array,
});

const emit = defineEmits(['close']);

// Form state
const selectedFormat = ref('pdf');
const includeImages = ref(true);
const includePrices = ref(true);
const includeDescriptions = ref(true);
const onlyAvailable = ref(true);
const onlyPlatillos = ref(false); // Excluir servicios
const selectSpecificItems = ref(false); // Nueva opción para seleccionar platillos específicos
const selectedItems = ref([]); // IDs de platillos seleccionados
const imageSize = ref('square'); // square (1:1) o story (9:16)

const formats = [
    {
        value: 'pdf',
        label: 'PDF',
        description: 'Documento para imprimir o compartir por email',
        icon: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'
    },
    {
        value: 'image-square',
        label: 'Imagen (1:1)',
        description: 'Ideal para Instagram, Facebook (1080x1080)',
        icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'
    },
    {
        value: 'image-story',
        label: 'Imagen Story (9:16)',
        description: 'Para Instagram Stories, WhatsApp Status (1080x1920)',
        icon: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'
    }
];

// Computed properties
const availableMenuItems = computed(() => {
    if (!props.menuItems) return [];
    return props.menuItems;
});

const filteredItems = computed(() => {
    let items = availableMenuItems.value;

    if (onlyAvailable.value) {
        items = items.filter(item => item.is_available);
    }

    if (onlyPlatillos.value) {
        items = items.filter(item => !item.is_service);
    }

    return items;
});

const allSelected = computed(() => {
    return filteredItems.value.length > 0 &&
           filteredItems.value.every(item => selectedItems.value.includes(item.id));
});

const someSelected = computed(() => {
    return selectedItems.value.length > 0 && !allSelected.value;
});

// Methods
const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedItems.value = [];
    } else {
        selectedItems.value = filteredItems.value.map(item => item.id);
    }
};

const toggleItem = (itemId) => {
    const index = selectedItems.value.indexOf(itemId);
    if (index > -1) {
        selectedItems.value.splice(index, 1);
    } else {
        selectedItems.value.push(itemId);
    }
};

const handleExport = () => {
    // Construir URL con parámetros
    const params = new URLSearchParams({
        format: selectedFormat.value,
        include_images: includeImages.value ? '1' : '0',
        include_prices: includePrices.value ? '1' : '0',
        include_descriptions: includeDescriptions.value ? '1' : '0',
        only_available: onlyAvailable.value ? '1' : '0',
        only_platillos: onlyPlatillos.value ? '1' : '0',
    });

    // Si se seleccionaron platillos específicos, agregar sus IDs
    if (selectSpecificItems.value && selectedItems.value.length > 0) {
        params.append('items', selectedItems.value.join(','));
    }

    // Abrir en nueva ventana
    window.open(route('menu.export.pdf') + '?' + params.toString(), '_blank');

    emit('close');
};

const currentFormat = computed(() => {
    return formats.find(f => f.value === selectedFormat.value);
});
</script>

<template>
    <SlideOver
        :show="show"
        title="Exportar Menú"
        size="lg"
        @close="$emit('close')"
    >
        <div class="space-y-6">
                <!-- Formato de Exportación -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Formato de Exportación
                    </label>

                    <div class="space-y-3">
                        <div
                            v-for="format in formats"
                            :key="format.value"
                            @click="selectedFormat = format.value"
                            :class="[
                                'relative flex items-start p-4 rounded-lg border-2 cursor-pointer transition-all',
                                selectedFormat === format.value
                                    ? 'border-purple-600 bg-purple-50 dark:bg-purple-900/20'
                                    : 'border-gray-300 dark:border-gray-600 hover:border-purple-400 bg-white dark:bg-gray-800'
                            ]"
                        >
                            <div class="flex items-center h-5">
                                <input
                                    :id="format.value"
                                    type="radio"
                                    :value="format.value"
                                    v-model="selectedFormat"
                                    class="h-4 w-4 text-purple-600 border-gray-300 focus:ring-purple-500"
                                />
                            </div>
                            <div class="ml-3 flex-1">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="format.icon" />
                                    </svg>
                                    <label :for="format.value" class="font-medium text-gray-900 dark:text-white cursor-pointer">
                                        {{ format.label }}
                                    </label>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ format.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opciones de Contenido -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Contenido
                    </label>

                    <div class="space-y-3">
                        <!-- Incluir Imágenes -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input
                                    id="include-images"
                                    type="checkbox"
                                    v-model="includeImages"
                                    class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                />
                            </div>
                            <div class="ml-3">
                                <label for="include-images" class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                    Incluir Imágenes
                                </label>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Mostrar fotos de los platillos (si están disponibles)
                                </p>
                            </div>
                        </div>

                        <!-- Incluir Precios -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input
                                    id="include-prices"
                                    type="checkbox"
                                    v-model="includePrices"
                                    class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                />
                            </div>
                            <div class="ml-3">
                                <label for="include-prices" class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                    Incluir Precios
                                </label>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Mostrar el precio de cada platillo
                                </p>
                            </div>
                        </div>

                        <!-- Incluir Descripciones -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input
                                    id="include-descriptions"
                                    type="checkbox"
                                    v-model="includeDescriptions"
                                    class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                />
                            </div>
                            <div class="ml-3">
                                <label for="include-descriptions" class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                    Incluir Descripciones
                                </label>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Mostrar la descripción de cada platillo
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Filtros
                    </label>

                    <div class="space-y-3">
                        <!-- Solo Disponibles -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input
                                    id="only-available"
                                    type="checkbox"
                                    v-model="onlyAvailable"
                                    class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                />
                            </div>
                            <div class="ml-3">
                                <label for="only-available" class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                    Solo Platillos Disponibles
                                </label>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Excluir platillos que no están disponibles actualmente
                                </p>
                            </div>
                        </div>

                        <!-- Solo Platillos (Excluir Servicios) -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input
                                    id="only-platillos"
                                    type="checkbox"
                                    v-model="onlyPlatillos"
                                    class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                />
                            </div>
                            <div class="ml-3">
                                <label for="only-platillos" class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                    Excluir Servicios
                                </label>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Solo incluir platillos, excluir servicios adicionales
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selección de Platillos Específicos -->
                <div>
                    <div class="flex items-start mb-3">
                        <div class="flex items-center h-5">
                            <input
                                id="select-specific"
                                type="checkbox"
                                v-model="selectSpecificItems"
                                class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                            />
                        </div>
                        <div class="ml-3">
                            <label for="select-specific" class="block text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                Seleccionar Platillos Específicos
                            </label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Elige manualmente qué platillos incluir en el menú
                            </p>
                        </div>
                    </div>

                    <!-- Lista de platillos -->
                    <div v-if="selectSpecificItems" class="mt-3 border border-gray-300 dark:border-gray-600 rounded-lg max-h-64 overflow-y-auto">
                        <!-- Seleccionar todos -->
                        <div class="sticky top-0 bg-gray-50 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-600 p-3">
                            <label class="flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    :checked="allSelected"
                                    :indeterminate="someSelected"
                                    @change="toggleSelectAll"
                                    class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                />
                                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Seleccionar Todos ({{ selectedItems.length }} / {{ filteredItems.length }})
                                </span>
                            </label>
                        </div>

                        <!-- Items individuales -->
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            <label
                                v-for="item in filteredItems"
                                :key="item.id"
                                class="flex items-center p-3 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition-colors"
                            >
                                <input
                                    type="checkbox"
                                    :value="item.id"
                                    :checked="selectedItems.includes(item.id)"
                                    @change="toggleItem(item.id)"
                                    class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                />
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ item.name }}
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            ${{ parseFloat(item.price).toFixed(2) }}
                                        </span>
                                    </div>
                                    <p v-if="item.description" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-1">
                                        {{ item.description }}
                                    </p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span
                                            v-if="item.is_service"
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                        >
                                            Servicio
                                        </span>
                                        <span
                                            v-if="!item.is_available"
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"
                                        >
                                            No disponible
                                        </span>
                                        <span
                                            v-if="item.image_path"
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                                        >
                                            Con imagen
                                        </span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Empty state -->
                        <div v-if="filteredItems.length === 0" class="p-6 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                No hay platillos que coincidan con los filtros seleccionados
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Preview Info -->
                <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-purple-900 dark:text-purple-100">
                                Vista Previa
                            </p>
                            <p class="text-sm text-purple-700 dark:text-purple-300 mt-1">
                                <span class="font-semibold">Formato:</span> {{ currentFormat?.label }}<br>
                                <span class="font-semibold">Elementos:</span>
                                <span v-if="includePrices">Precios</span><span v-if="includePrices && includeDescriptions">, </span>
                                <span v-if="includeDescriptions">Descripciones</span><span v-if="(includePrices || includeDescriptions) && includeImages">, </span>
                                <span v-if="includeImages">Imágenes</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        <template #footer>
            <div class="flex items-center justify-end space-x-3">
                <BaseButton
                    variant="secondary"
                    @click="$emit('close')"
                >
                    Cancelar
                </BaseButton>

                <BaseButton
                    variant="primary"
                    @click="handleExport"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                    Exportar Menú
                </BaseButton>
            </div>
        </template>
    </SlideOver>
</template>
