<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import SlideOver from './SlideOver.vue';
import { useToast } from 'vue-toastification';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    combo: {
        type: Object,
        default: null
    },
    menuItems: {
        type: Array,
        default: () => []
    },
    simpleProducts: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close']);
const toast = useToast();
const { confirm } = useConfirmDialog();

// Form state
const form = ref({
    name: '',
    description: '',
    base_price: '',
    category: 'Combos',
    is_available: true,
    show_in_menu: true,
    show_in_pos: true,
    image: null,
    remove_image: false,
    components: [],
});

const imagePreview = ref(null);
const imageInput = ref(null);
const initialFormState = ref(null);
const hasChanges = ref(false);
const processing = ref(false);

const isEditMode = computed(() => props.combo !== null);

// Combinar productos para selección con key único
const allProducts = computed(() => {
    const items = [];

    props.menuItems?.forEach(item => {
        items.push({
            id: item.id,
            type: 'menu_item',
            // Key único para identificar el producto: tipo-id
            uniqueKey: `menu_item-${item.id}`,
            name: item.name,
            price: item.price,
            image: item.image_path,
            has_variants: item.has_variants,
            category: 'Platillos',
        });
    });

    props.simpleProducts?.forEach(product => {
        items.push({
            id: product.id,
            type: 'simple_product',
            uniqueKey: `simple_product-${product.id}`,
            name: product.name,
            price: product.sale_price,
            image: product.image_path,
            has_variants: product.allows_variants,
            category: product.category || 'Productos',
        });
    });

    return items;
});

// Helper para parsear la key única y obtener tipo e id
const parseProductKey = (key) => {
    if (!key) return { type: '', id: '' };
    const [type, id] = key.split('-');
    return { type: type || '', id: id ? parseInt(id) : '' };
};

// Helper para crear key desde tipo e id
const makeProductKey = (type, id) => {
    if (!type || !id) return '';
    return `${type}-${id}`;
};

// Agrupar productos por categoría
const productsByCategory = computed(() => {
    const grouped = {};
    allProducts.value.forEach(product => {
        const cat = product.category || 'Sin categoría';
        if (!grouped[cat]) {
            grouped[cat] = [];
        }
        grouped[cat].push(product);
    });
    return grouped;
});

// Watch for changes
watch(() => form.value, () => {
    if (initialFormState.value) {
        hasChanges.value = JSON.stringify(form.value) !== JSON.stringify(initialFormState.value);
    }
}, { deep: true });

// Watch for combo changes (edit mode)
watch(() => props.combo, (newCombo) => {
    if (newCombo) {
        form.value = {
            name: newCombo.name || '',
            description: newCombo.description || '',
            base_price: newCombo.base_price || '',
            category: newCombo.category || 'Combos',
            is_available: newCombo.is_available ?? true,
            show_in_menu: newCombo.show_in_menu ?? true,
            show_in_pos: newCombo.show_in_pos ?? true,
            image: null,
            remove_image: false,
            components: newCombo.components?.map(comp => ({
                id: comp.id,
                component_type: comp.component_type,
                name: comp.name || '',
                quantity: comp.quantity || 1,
                is_required: comp.is_required ?? true,
                sellable_type: comp.sellable_type || '',
                sellable_id: comp.sellable_id || '',
                options: comp.options?.map(opt => ({
                    id: opt.id,
                    sellable_type: opt.sellable_type,
                    sellable_id: opt.sellable_id,
                    price_adjustment: opt.price_adjustment || 0,
                    is_default: opt.is_default || false,
                })) || [],
            })) || [],
        };
        imagePreview.value = newCombo.image_path || null;
        initialFormState.value = JSON.parse(JSON.stringify(form.value));
        hasChanges.value = false;
    } else if (props.show) {
        resetForm();
    }
}, { immediate: true });

// Reset form when closed
watch(() => props.show, (newVal) => {
    if (!newVal) {
        setTimeout(() => {
            resetForm();
        }, 300);
    } else if (newVal && !props.combo) {
        resetForm();
        setTimeout(() => {
            initialFormState.value = JSON.parse(JSON.stringify(form.value));
        }, 100);
    }
});

const resetForm = () => {
    form.value = {
        name: '',
        description: '',
        base_price: '',
        category: 'Combos',
        is_available: true,
        show_in_menu: true,
        show_in_pos: true,
        image: null,
        remove_image: false,
        components: [],
    };
    imagePreview.value = null;
    initialFormState.value = null;
    hasChanges.value = false;
    if (imageInput.value) {
        imageInput.value.value = '';
    }
};

// Manejar imagen
const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.value.image = file;
        imagePreview.value = URL.createObjectURL(file);
        hasChanges.value = true;
    }
};

const removeImage = () => {
    form.value.image = null;
    imagePreview.value = null;
    form.value.remove_image = true;
    hasChanges.value = true;
    if (imageInput.value) {
        imageInput.value.value = '';
    }
};

// Agregar componente
const addComponent = (type) => {
    form.value.components.push({
        component_type: type,
        name: type === 'choice' ? '' : null,
        quantity: 1,
        is_required: true,
        sellable_type: '',
        sellable_id: '',
        options: [],
    });
};

// Eliminar componente
const removeComponent = (index) => {
    form.value.components.splice(index, 1);
};

// Agregar opción a componente choice
const addOption = (componentIndex) => {
    form.value.components[componentIndex].options.push({
        sellable_type: '',
        sellable_id: '',
        price_adjustment: 0,
        is_default: false,
    });
};

// Eliminar opción
const removeOption = (componentIndex, optionIndex) => {
    form.value.components[componentIndex].options.splice(optionIndex, 1);
};

// Marcar opción como default
const setDefaultOption = (componentIndex, optionIndex) => {
    form.value.components[componentIndex].options.forEach((opt, idx) => {
        opt.is_default = idx === optionIndex;
    });
};

const handleClose = async () => {
    if (hasChanges.value) {
        const confirmed = await confirm({
            title: '¿Descartar cambios?',
            message: 'Tienes cambios sin guardar. Si sales ahora, se perderán.',
            confirmText: 'Descartar',
            type: 'warning'
        });
        if (confirmed) {
            emit('close');
        }
    } else {
        emit('close');
    }
};

// Submit
const handleSubmit = () => {
    // Validaciones básicas
    if (!form.value.name.trim()) {
        toast.error('Ingresa el nombre del combo');
        return;
    }
    if (!form.value.base_price || parseFloat(form.value.base_price) <= 0) {
        toast.error('Ingresa un precio válido');
        return;
    }
    if (form.value.components.length === 0) {
        toast.error('Agrega al menos un componente al combo');
        return;
    }

    // Validar componentes
    for (let i = 0; i < form.value.components.length; i++) {
        const comp = form.value.components[i];

        if (comp.component_type === 'fixed') {
            if (!comp.sellable_type || !comp.sellable_id) {
                toast.error(`Componente ${i + 1}: Selecciona un producto`);
                return;
            }
        } else if (comp.component_type === 'choice') {
            if (!comp.name.trim()) {
                toast.error(`Componente ${i + 1}: Ingresa un nombre (ej: "Bebida")`);
                return;
            }
            if (comp.options.length === 0) {
                toast.error(`Componente "${comp.name}": Agrega al menos una opción`);
                return;
            }
            for (let j = 0; j < comp.options.length; j++) {
                const opt = comp.options[j];
                if (!opt.sellable_type || !opt.sellable_id) {
                    toast.error(`Componente "${comp.name}", opción ${j + 1}: Selecciona un producto`);
                    return;
                }
            }
        }
    }

    processing.value = true;

    const formData = new FormData();
    formData.append('name', form.value.name.trim());
    formData.append('description', form.value.description?.trim() || '');
    formData.append('base_price', form.value.base_price);
    formData.append('category', form.value.category || 'Combos');
    formData.append('is_available', form.value.is_available ? '1' : '0');
    formData.append('show_in_menu', form.value.show_in_menu ? '1' : '0');
    formData.append('show_in_pos', form.value.show_in_pos ? '1' : '0');

    if (form.value.image) {
        formData.append('image', form.value.image);
    }
    if (form.value.remove_image) {
        formData.append('remove_image', '1');
    }

    // Componentes como JSON
    formData.append('components', JSON.stringify(form.value.components));

    const url = isEditMode.value
        ? route('combos.update', props.combo.id)
        : route('combos.store');

    if (isEditMode.value) {
        formData.append('_method', 'PUT');
    }

    router.post(url, formData, {
        forceFormData: true,
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success(isEditMode.value ? 'Combo actualizado' : 'Combo creado');
            emit('close');
            resetForm();
        },
        onError: (errors) => {
            console.error(errors);
            toast.error('Error al guardar el combo');
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const formatCurrency = (value) => {
    const num = parseFloat(value) || 0;
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'USD',
    }).format(num);
};
</script>

<template>
    <SlideOver
        :show="show"
        :prevent-close="hasChanges"
        @close="handleClose"
        @backdrop-click="handleClose"
        :title="isEditMode ? 'Editar Combo' : 'Nuevo Combo'"
        :subtitle="isEditMode ? 'Modifica los detalles del combo' : 'Crea un nuevo combo con productos'"
        size="xl"
    >
        <div class="space-y-6">
            <!-- Información básica -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Información del Combo
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nombre del Combo *
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"
                            placeholder="Ej: Combo Hamburguesa"
                            required
                        />
                    </div>

                    <!-- Precio base -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Precio Base *
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                            <input
                                v-model="form.base_price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full pl-8 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"
                                placeholder="0.00"
                                required
                            />
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Descripción
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="2"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"
                            placeholder="Descripción del combo..."
                        />
                    </div>

                    <!-- Imagen -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Imagen
                        </label>
                        <div class="flex items-start gap-3">
                            <div class="w-20 h-20 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                                <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <input
                                    ref="imageInput"
                                    type="file"
                                    @change="handleImageChange"
                                    accept="image/*"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 dark:file:bg-purple-900/50 dark:file:text-purple-300"
                                />
                                <button
                                    v-if="imagePreview"
                                    @click="removeImage"
                                    type="button"
                                    class="mt-2 text-sm text-red-600 hover:text-red-800"
                                >
                                    Eliminar imagen
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Opciones de visibilidad -->
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.is_available" type="checkbox" class="w-4 h-4 text-purple-600 rounded border-gray-300 focus:ring-purple-500" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">Combo disponible</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.show_in_menu" type="checkbox" class="w-4 h-4 text-purple-600 rounded border-gray-300 focus:ring-purple-500" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">Mostrar en menú digital</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.show_in_pos" type="checkbox" class="w-4 h-4 text-purple-600 rounded border-gray-300 focus:ring-purple-500" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">Mostrar en POS</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Componentes del combo -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Componentes del Combo
                    </h3>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            @click="addComponent('fixed')"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Fijo
                        </button>
                        <button
                            type="button"
                            @click="addComponent('choice')"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            A Elegir
                        </button>
                    </div>
                </div>

                <!-- Lista de componentes -->
                <div class="space-y-4">
                    <div
                        v-for="(component, cIndex) in form.components"
                        :key="cIndex"
                        :class="[
                            'p-4 rounded-lg border-2',
                            component.component_type === 'fixed'
                                ? 'bg-green-50 dark:bg-green-900/10 border-green-200 dark:border-green-800'
                                : 'bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800'
                        ]"
                    >
                        <!-- Header del componente -->
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span
                                    :class="[
                                        'px-2 py-0.5 text-xs font-bold rounded',
                                        component.component_type === 'fixed'
                                            ? 'bg-green-200 text-green-800 dark:bg-green-800 dark:text-green-200'
                                            : 'bg-blue-200 text-blue-800 dark:bg-blue-800 dark:text-blue-200'
                                    ]"
                                >
                                    {{ component.component_type === 'fixed' ? 'FIJO' : 'A ELEGIR' }}
                                </span>
                                <span class="text-sm text-gray-500">Componente {{ cIndex + 1 }}</span>
                            </div>
                            <button
                                type="button"
                                @click="removeComponent(cIndex)"
                                class="p-1 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Componente FIJO -->
                        <div v-if="component.component_type === 'fixed'" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Producto *</label>
                                <select
                                    :value="makeProductKey(component.sellable_type, component.sellable_id)"
                                    @change="(e) => {
                                        const parsed = parseProductKey(e.target.value);
                                        component.sellable_type = parsed.type;
                                        component.sellable_id = parsed.id;
                                    }"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Seleccionar producto...</option>
                                    <optgroup v-for="(products, category) in productsByCategory" :key="category" :label="category">
                                        <option v-for="product in products" :key="product.uniqueKey" :value="product.uniqueKey">
                                            {{ product.name }} - {{ formatCurrency(product.price) }}
                                        </option>
                                    </optgroup>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cantidad</label>
                                <input
                                    v-model="component.quantity"
                                    type="number"
                                    min="1"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                        </div>

                        <!-- Componente A ELEGIR -->
                        <div v-else class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre del grupo *</label>
                                    <input
                                        v-model="component.name"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Ej: Bebida"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cantidad</label>
                                    <input
                                        v-model="component.quantity"
                                        type="number"
                                        min="1"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                                <div class="flex items-end">
                                    <label class="flex items-center gap-2 cursor-pointer pb-2">
                                        <input v-model="component.is_required" type="checkbox" class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Requerido</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Opciones -->
                            <div class="pl-4 border-l-2 border-blue-300 dark:border-blue-700">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Opciones disponibles</span>
                                    <button
                                        type="button"
                                        @click="addOption(cIndex)"
                                        class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                    >
                                        + Agregar opción
                                    </button>
                                </div>

                                <div class="space-y-2">
                                    <div
                                        v-for="(option, oIndex) in component.options"
                                        :key="oIndex"
                                        class="flex items-center gap-2 p-2 bg-white dark:bg-gray-800 rounded-lg"
                                    >
                                        <select
                                            :value="makeProductKey(option.sellable_type, option.sellable_id)"
                                            @change="(e) => {
                                                const parsed = parseProductKey(e.target.value);
                                                option.sellable_type = parsed.type;
                                                option.sellable_id = parsed.id;
                                            }"
                                            class="flex-1 px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        >
                                            <option value="">Seleccionar...</option>
                                            <optgroup v-for="(products, category) in productsByCategory" :key="category" :label="category">
                                                <option v-for="product in products" :key="product.uniqueKey" :value="product.uniqueKey">
                                                    {{ product.name }}
                                                </option>
                                            </optgroup>
                                        </select>

                                        <div class="flex items-center gap-1">
                                            <span class="text-xs text-gray-500">Ajuste:</span>
                                            <input
                                                v-model="option.price_adjustment"
                                                type="number"
                                                step="0.01"
                                                class="w-20 px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                                placeholder="0.00"
                                            />
                                        </div>

                                        <button
                                            type="button"
                                            @click="setDefaultOption(cIndex, oIndex)"
                                            :class="[
                                                'p-1.5 rounded transition-colors',
                                                option.is_default
                                                    ? 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400'
                                                    : 'text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                                            ]"
                                            title="Marcar como default"
                                        >
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                            </svg>
                                        </button>

                                        <button
                                            type="button"
                                            @click="removeOption(cIndex, oIndex)"
                                            class="p-1.5 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <p v-if="component.options.length === 0" class="text-sm text-gray-500 italic">
                                        Agrega al menos una opción
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estado vacío -->
                    <div v-if="form.components.length === 0" class="text-center py-8 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400 mb-2">No hay componentes</p>
                        <p class="text-sm text-gray-400">
                            Agrega componentes <strong>Fijos</strong> (siempre incluidos) o <strong>A Elegir</strong> (cliente elige)
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer con botones -->
        <template #footer>
            <div class="flex items-center justify-end space-x-3">
                <button
                    @click="handleClose"
                    type="button"
                    class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                    Cancelar
                </button>
                <button
                    @click="handleSubmit"
                    :disabled="processing"
                    type="button"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 rounded-lg transition-colors flex items-center"
                >
                    <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ processing ? 'Guardando...' : (isEditMode ? 'Actualizar Combo' : 'Crear Combo') }}
                </button>
            </div>
        </template>
    </SlideOver>
</template>
