<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseCard from '@/Components/Base/BaseCard.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import ScheduleEditor from '@/Components/Settings/ScheduleEditor.vue';
import DeliveryMethodsEditor from '@/Components/Settings/DeliveryMethodsEditor.vue';
import { useToast } from '@/composables';

const props = defineProps({
    settings: Object,
    paymentMethods: Array,
    printerSettings: Object,
    ticketSettings: Object,
    orderSettings: Object,
});

const toast = useToast();
const activeTab = ref('general');

const form = ref({
    // General
    restaurant_name: props.settings.restaurant_name || '',
    restaurant_address: props.settings.restaurant_address || '',
    restaurant_phone: props.settings.restaurant_phone || '',
    restaurant_email: props.settings.restaurant_email || '',
    restaurant_tax_id: props.settings.restaurant_tax_id || '',
    currency: props.settings.currency || 'MXN',
    country_code: props.settings.country_code || '+503',
    timezone: props.settings.timezone || 'America/El_Salvador',

    // Branding
    primary_color: props.settings.primary_color || '#f97316',
    secondary_color: props.settings.secondary_color || '#ea580c',
    accent_color: props.settings.accent_color || '#fb923c',
    logo: null,

    // Digital Menu
    digital_menu_enabled: props.settings.digital_menu_enabled || false,
    whatsapp_number: props.settings.whatsapp_number || '',
    digital_menu_schedule: props.settings.digital_menu_schedule || {
        monday: { enabled: false, open: '09:00', close: '22:00' },
        tuesday: { enabled: false, open: '09:00', close: '22:00' },
        wednesday: { enabled: false, open: '09:00', close: '22:00' },
        thursday: { enabled: false, open: '09:00', close: '22:00' },
        friday: { enabled: false, open: '09:00', close: '22:00' },
        saturday: { enabled: false, open: '09:00', close: '22:00' },
        sunday: { enabled: false, open: '09:00', close: '22:00' },
    },
    allow_pickup: props.settings.allow_pickup ?? true,
    allow_delivery: props.settings.allow_delivery ?? false,
    allow_dine_in: props.settings.allow_dine_in ?? false,
    delivery_fee: props.settings.delivery_fee || 0,
    min_order_amount: props.settings.min_order_amount || 0,
    welcome_message: props.settings.welcome_message || '',
    closed_message: props.settings.closed_message || 'El menú digital no está disponible en este momento.',
    footer_message: props.settings.footer_message || '',
});

const logoPreview = ref(props.settings.logo_path);
const processing = ref(false);

const tabs = [
    { id: 'general', name: 'General', icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { id: 'branding', name: 'Marca', icon: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01' },
    { id: 'digital-menu', name: 'Menú Digital', icon: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z' },
    { id: 'payment-methods', name: 'Pagos', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
    { id: 'printers', name: 'Impresoras', icon: 'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z' },
    { id: 'tickets', name: 'Tickets', icon: 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z' },
    { id: 'orders', name: 'Pedidos', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' },
];

// ===== PAYMENT METHODS =====
const paymentMethodsForm = ref(JSON.parse(JSON.stringify(props.paymentMethods || [])));
const paymentMethodsProcessing = ref(false);

const paymentMethodIcons = [
    { value: 'cash', label: 'Efectivo' },
    { value: 'card', label: 'Tarjeta' },
    { value: 'transfer', label: 'Transferencia' },
    { value: 'mixed', label: 'Mixto' },
    { value: 'qr', label: 'QR' },
    { value: 'wallet', label: 'Billetera' },
];

const addPaymentMethod = () => {
    paymentMethodsForm.value.push({
        id: null,
        name: '',
        label: '',
        icon: 'cash',
        is_active: true,
        requires_reference: false,
        requires_amount_input: false,
        commission_percent: 0,
        sort_order: paymentMethodsForm.value.length,
    });
};

const removePaymentMethod = (index) => {
    paymentMethodsForm.value.splice(index, 1);
};

const savePaymentMethods = () => {
    paymentMethodsProcessing.value = true;
    router.post(route('settings.payment-methods.update'), {
        methods: paymentMethodsForm.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Métodos de pago actualizados');
            paymentMethodsProcessing.value = false;
        },
        onError: () => {
            toast.error('Error al actualizar métodos de pago');
            paymentMethodsProcessing.value = false;
        },
    });
};

// ===== PRINTER SETTINGS =====
const printerForm = ref({
    kitchen: {
        ip_address: props.printerSettings?.kitchen?.ip_address || '',
        port: props.printerSettings?.kitchen?.port || 9100,
        width_mm: props.printerSettings?.kitchen?.width_mm || 58,
        is_enabled: props.printerSettings?.kitchen?.is_enabled ?? false,
        auto_print: props.printerSettings?.kitchen?.auto_print ?? true,
    },
    customer: {
        ip_address: props.printerSettings?.customer?.ip_address || '',
        port: props.printerSettings?.customer?.port || 9100,
        width_mm: props.printerSettings?.customer?.width_mm || 80,
        is_enabled: props.printerSettings?.customer?.is_enabled ?? false,
        auto_print: props.printerSettings?.customer?.auto_print ?? true,
    },
});
const printerProcessing = ref(false);

const savePrinterSettings = () => {
    printerProcessing.value = true;
    router.post(route('settings.printers.update'), printerForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Configuración de impresoras actualizada');
            printerProcessing.value = false;
        },
        onError: () => {
            toast.error('Error al actualizar impresoras');
            printerProcessing.value = false;
        },
    });
};

// ===== TICKET SETTINGS =====
const ticketForm = ref({
    // Ticket de cliente
    show_logo: props.ticketSettings?.show_logo ?? true,
    show_address: props.ticketSettings?.show_address ?? true,
    show_phone: props.ticketSettings?.show_phone ?? true,
    show_qr_code: props.ticketSettings?.show_qr_code ?? true,
    qr_content: props.ticketSettings?.qr_content || 'sale_number',
    header_message: props.ticketSettings?.header_message || '',
    footer_message: props.ticketSettings?.footer_message || '¡Gracias por su compra!',
    // Ticket de cocina
    kitchen_show_customer_name: props.ticketSettings?.kitchen_show_customer_name ?? false,
    kitchen_show_table_number: props.ticketSettings?.kitchen_show_table_number ?? true,
    kitchen_show_notes: props.ticketSettings?.kitchen_show_notes ?? true,
    kitchen_show_order_number: props.ticketSettings?.kitchen_show_order_number ?? true,
    kitchen_font_size: props.ticketSettings?.kitchen_font_size || 'large',
    // Categorías y prioridades
    non_kitchen_categories: props.ticketSettings?.non_kitchen_categories || ['Bebidas', 'Postres', 'Extras'],
    priority_high_threshold: props.ticketSettings?.priority_high_threshold || 10,
    priority_medium_threshold: props.ticketSettings?.priority_medium_threshold || 5,
});
const ticketProcessing = ref(false);
const newNonKitchenCategory = ref('');

const addNonKitchenCategory = () => {
    if (newNonKitchenCategory.value && !ticketForm.value.non_kitchen_categories.includes(newNonKitchenCategory.value)) {
        ticketForm.value.non_kitchen_categories.push(newNonKitchenCategory.value);
        newNonKitchenCategory.value = '';
    }
};

const removeNonKitchenCategory = (index) => {
    ticketForm.value.non_kitchen_categories.splice(index, 1);
};

const saveTicketSettings = () => {
    ticketProcessing.value = true;
    router.post(route('settings.tickets.update'), ticketForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Configuración de tickets actualizada');
            ticketProcessing.value = false;
        },
        onError: () => {
            toast.error('Error al actualizar tickets');
            ticketProcessing.value = false;
        },
    });
};

// ===== ORDER SETTINGS =====
const orderForm = ref({
    order_number_prefix: props.orderSettings?.order_number_prefix || '',
    order_number_padding: props.orderSettings?.order_number_padding || 4,
    order_number_reset: props.orderSettings?.order_number_reset || 'daily',
    allow_dine_in: props.orderSettings?.allow_dine_in ?? true,
    allow_takeout: props.orderSettings?.allow_takeout ?? true,
    allow_delivery: props.orderSettings?.allow_delivery ?? false,
    allow_scheduled: props.orderSettings?.allow_scheduled ?? false,
    tax_included: props.orderSettings?.tax_included ?? true,
    tax_percentage: props.orderSettings?.tax_percentage || 13,
    show_tax_breakdown: props.orderSettings?.show_tax_breakdown ?? false,
    tip_enabled: props.orderSettings?.tip_enabled ?? false,
    tip_suggestions: props.orderSettings?.tip_suggestions || [10, 15, 20],
    tip_mandatory_above: props.orderSettings?.tip_mandatory_above || null,
    require_customer_name: props.orderSettings?.require_customer_name ?? false,
    require_customer_phone: props.orderSettings?.require_customer_phone ?? false,
    allow_notes_per_item: props.orderSettings?.allow_notes_per_item ?? true,
    default_prep_time_minutes: props.orderSettings?.default_prep_time_minutes || 20,
    min_order_amount: props.orderSettings?.min_order_amount || 0,
    delivery_min_amount: props.orderSettings?.delivery_min_amount || 0,
    delivery_fee: props.orderSettings?.delivery_fee || 0,
});
const orderProcessing = ref(false);

const saveOrderSettings = () => {
    orderProcessing.value = true;
    router.post(route('settings.orders.update'), orderForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Configuración de pedidos actualizada');
            orderProcessing.value = false;
        },
        onError: () => {
            toast.error('Error al actualizar configuración de pedidos');
            orderProcessing.value = false;
        },
    });
};

const handleLogoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.value.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const handleSubmit = () => {
    processing.value = true;

    const formData = new FormData();
    Object.keys(form.value).forEach(key => {
        const value = form.value[key];

        // Handle special cases
        if (key === 'digital_menu_schedule') {
            formData.append(key, JSON.stringify(value));
        } else if (typeof value === 'boolean') {
            // Convert boolean to 0 or 1 for Laravel
            formData.append(key, value ? '1' : '0');
        } else if (value !== null && value !== '') {
            formData.append(key, value);
        }
    });

    router.post(route('settings.update'), formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Configuración actualizada exitosamente');
            processing.value = false;
        },
        onError: (errors) => {
            toast.error('Error al actualizar configuración');
            console.error(errors);
            processing.value = false;
        },
    });
};
</script>

<template>
    <Head title="Configuración del Negocio" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Configuración del Negocio
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Personaliza la información de tu restaurante
                    </p>
                </div>
            </div>
        </template>

        <div class="py-3 md:py-6 lg:py-12 px-2 sm:px-6 lg:px-8 max-w-5xl mx-auto">
            <!-- Tabs Navigation -->
            <div class="mb-6">
                <nav class="flex overflow-x-auto scrollbar-hide gap-1 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-1 -mx-2 px-2 sm:mx-0 sm:px-1">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        type="button"
                        class="flex-shrink-0 flex items-center justify-center gap-1.5 px-3 py-2.5 sm:px-4 sm:py-3 rounded-lg text-xs sm:text-sm font-medium transition-all duration-200 whitespace-nowrap"
                        :class="activeTab === tab.id
                            ? 'bg-purple-600 text-white shadow-sm'
                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'"
                    >
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon" />
                        </svg>
                        <span class="hidden sm:inline">{{ tab.name }}</span>
                    </button>
                </nav>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- General Tab -->
                <div v-show="activeTab === 'general'">
                    <BaseCard>
                        <div class="p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                    Información General
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Configura los datos básicos de tu restaurante
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="restaurant_name" value="Nombre del Restaurante *" />
                                    <TextInput
                                        id="restaurant_name"
                                        v-model="form.restaurant_name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full"
                                        placeholder="Ej: Restaurante El Buen Sabor"
                                    />
                                </div>

                                <div>
                                    <InputLabel for="restaurant_tax_id" value="RFC / Tax ID" />
                                    <TextInput
                                        id="restaurant_tax_id"
                                        v-model="form.restaurant_tax_id"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="Ej: ABC123456XYZ"
                                    />
                                </div>

                                <div class="md:col-span-2">
                                    <InputLabel for="restaurant_address" value="Dirección" />
                                    <TextInput
                                        id="restaurant_address"
                                        v-model="form.restaurant_address"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="Calle Principal #123, Col. Centro"
                                    />
                                </div>

                                <div>
                                    <InputLabel for="restaurant_phone" value="Teléfono" />
                                    <TextInput
                                        id="restaurant_phone"
                                        v-model="form.restaurant_phone"
                                        type="tel"
                                        class="mt-1 block w-full"
                                        placeholder="(555) 123-4567"
                                    />
                                </div>

                                <div>
                                    <InputLabel for="restaurant_email" value="Email" />
                                    <TextInput
                                        id="restaurant_email"
                                        v-model="form.restaurant_email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        placeholder="contacto@restaurante.com"
                                    />
                                </div>

                                <div>
                                    <InputLabel for="currency" value="Moneda" />
                                    <select
                                        id="currency"
                                        v-model="form.currency"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-purple-500 dark:focus:border-purple-600 focus:ring-purple-500 dark:focus:ring-purple-600 rounded-md shadow-sm"
                                    >
                                        <option value="MXN">MXN - Peso Mexicano</option>
                                        <option value="USD">USD - Dólar Estadounidense</option>
                                        <option value="EUR">EUR - Euro</option>
                                    </select>
                                </div>

                                <div>
                                    <InputLabel for="country_code" value="Código de País" />
                                    <select
                                        id="country_code"
                                        v-model="form.country_code"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-purple-500 dark:focus:border-purple-600 focus:ring-purple-500 dark:focus:ring-purple-600 rounded-md shadow-sm"
                                    >
                                        <optgroup label="Centroamérica">
                                            <option value="+503">+503 - El Salvador</option>
                                            <option value="+502">+502 - Guatemala</option>
                                            <option value="+504">+504 - Honduras</option>
                                            <option value="+505">+505 - Nicaragua</option>
                                            <option value="+506">+506 - Costa Rica</option>
                                            <option value="+507">+507 - Panamá</option>
                                            <option value="+501">+501 - Belice</option>
                                        </optgroup>
                                        <optgroup label="México">
                                            <option value="+52">+52 - México</option>
                                        </optgroup>
                                        <optgroup label="Sudamérica">
                                            <option value="+57">+57 - Colombia</option>
                                            <option value="+58">+58 - Venezuela</option>
                                            <option value="+51">+51 - Perú</option>
                                            <option value="+593">+593 - Ecuador</option>
                                            <option value="+591">+591 - Bolivia</option>
                                            <option value="+56">+56 - Chile</option>
                                            <option value="+54">+54 - Argentina</option>
                                            <option value="+598">+598 - Uruguay</option>
                                            <option value="+595">+595 - Paraguay</option>
                                            <option value="+55">+55 - Brasil</option>
                                        </optgroup>
                                        <optgroup label="Caribe">
                                            <option value="+53">+53 - Cuba</option>
                                            <option value="+1-809">+1-809 - República Dominicana</option>
                                            <option value="+1-787">+1-787 - Puerto Rico</option>
                                        </optgroup>
                                        <optgroup label="Norteamérica">
                                            <option value="+1">+1 - Estados Unidos / Canadá</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <div>
                                    <InputLabel for="timezone" value="Zona Horaria" />
                                    <select
                                        id="timezone"
                                        v-model="form.timezone"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-purple-500 dark:focus:border-purple-600 focus:ring-purple-500 dark:focus:ring-purple-600 rounded-md shadow-sm"
                                    >
                                        <optgroup label="Centroamérica">
                                            <option value="America/Guatemala">Guatemala (GMT-6)</option>
                                            <option value="America/El_Salvador">El Salvador (GMT-6)</option>
                                            <option value="America/Tegucigalpa">Honduras (GMT-6)</option>
                                            <option value="America/Managua">Nicaragua (GMT-6)</option>
                                            <option value="America/Costa_Rica">Costa Rica (GMT-6)</option>
                                            <option value="America/Panama">Panamá (GMT-5)</option>
                                            <option value="America/Belize">Belice (GMT-6)</option>
                                        </optgroup>
                                        <optgroup label="México">
                                            <option value="America/Mexico_City">Ciudad de México (GMT-6)</option>
                                            <option value="America/Cancun">Cancún (GMT-5)</option>
                                            <option value="America/Tijuana">Tijuana (GMT-8)</option>
                                            <option value="America/Mazatlan">Mazatlán (GMT-7)</option>
                                            <option value="America/Monterrey">Monterrey (GMT-6)</option>
                                        </optgroup>
                                        <optgroup label="Sudamérica">
                                            <option value="America/Bogota">Colombia (GMT-5)</option>
                                            <option value="America/Caracas">Venezuela (GMT-4)</option>
                                            <option value="America/Lima">Perú (GMT-5)</option>
                                            <option value="America/Guayaquil">Ecuador (GMT-5)</option>
                                            <option value="America/La_Paz">Bolivia (GMT-4)</option>
                                            <option value="America/Santiago">Chile (GMT-3)</option>
                                            <option value="America/Argentina/Buenos_Aires">Argentina (GMT-3)</option>
                                            <option value="America/Montevideo">Uruguay (GMT-3)</option>
                                            <option value="America/Asuncion">Paraguay (GMT-4)</option>
                                            <option value="America/Sao_Paulo">Brasil - São Paulo (GMT-3)</option>
                                        </optgroup>
                                        <optgroup label="Caribe">
                                            <option value="America/Havana">Cuba (GMT-5)</option>
                                            <option value="America/Santo_Domingo">República Dominicana (GMT-4)</option>
                                            <option value="America/Puerto_Rico">Puerto Rico (GMT-4)</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </BaseCard>
                </div>

                <!-- Branding Tab -->
                <div v-show="activeTab === 'branding'">
                    <BaseCard>
                        <div class="p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                    Identidad de Marca
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Personaliza el logo y colores de tu restaurante
                                </p>
                            </div>

                            <!-- Logo -->
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                                    Logo del Restaurante
                                </h4>
                                <div class="flex items-center space-x-6">
                                    <div v-if="logoPreview" class="flex-shrink-0">
                                        <img
                                            :src="logoPreview"
                                            class="h-24 w-24 object-contain rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-2"
                                            alt="Logo"
                                        />
                                    </div>
                                    <div v-else class="flex-shrink-0">
                                        <div class="h-24 w-24 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex items-center justify-center">
                                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="flex-1">
                                        <input
                                            type="file"
                                            @change="handleLogoChange"
                                            accept="image/png,image/jpg,image/jpeg,image/svg+xml,image/webp"
                                            class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 dark:file:bg-purple-900/50 file:text-purple-700 dark:file:text-purple-200 hover:file:bg-purple-100 dark:hover:file:bg-purple-800/50 cursor-pointer"
                                        />
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                            PNG, JPG, SVG, WEBP hasta 2MB. Recomendado: 512x512px
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Brand Colors -->
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                                    Paleta de Colores
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <InputLabel for="primary_color" value="Color Primario" />
                                        <div class="mt-1 flex items-center space-x-3">
                                            <input
                                                v-model="form.primary_color"
                                                type="color"
                                                class="h-12 w-16 rounded-lg border-2 border-gray-200 dark:border-gray-700 cursor-pointer"
                                            />
                                            <TextInput
                                                id="primary_color"
                                                v-model="form.primary_color"
                                                type="text"
                                                class="flex-1"
                                                placeholder="#f97316"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <InputLabel for="secondary_color" value="Color Secundario" />
                                        <div class="mt-1 flex items-center space-x-3">
                                            <input
                                                v-model="form.secondary_color"
                                                type="color"
                                                class="h-12 w-16 rounded-lg border-2 border-gray-200 dark:border-gray-700 cursor-pointer"
                                            />
                                            <TextInput
                                                id="secondary_color"
                                                v-model="form.secondary_color"
                                                type="text"
                                                class="flex-1"
                                                placeholder="#ea580c"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <InputLabel for="accent_color" value="Color de Acento" />
                                        <div class="mt-1 flex items-center space-x-3">
                                            <input
                                                v-model="form.accent_color"
                                                type="color"
                                                class="h-12 w-16 rounded-lg border-2 border-gray-200 dark:border-gray-700 cursor-pointer"
                                            />
                                            <TextInput
                                                id="accent_color"
                                                v-model="form.accent_color"
                                                type="text"
                                                class="flex-1"
                                                placeholder="#fb923c"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </BaseCard>
                </div>

                <!-- Digital Menu Tab -->
                <div v-show="activeTab === 'digital-menu'">
                    <BaseCard>
                        <div class="p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                    Configuración del Menú Digital
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Permite que tus clientes ordenen desde sus dispositivos
                                </p>
                            </div>

                            <!-- Enable/Disable Toggle -->
                            <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                <input
                                    id="digital_menu_enabled"
                                    v-model="form.digital_menu_enabled"
                                    type="checkbox"
                                    class="mt-1 w-5 h-5 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 dark:focus:ring-purple-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                />
                                <div class="flex-1">
                                    <label for="digital_menu_enabled" class="block text-sm font-semibold text-gray-900 dark:text-white cursor-pointer">
                                        Habilitar Menú Digital
                                    </label>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                        Permite a los clientes ver el menú, registrarse y realizar pedidos desde sus dispositivos móviles
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                        :class="form.digital_menu_enabled
                                            ? 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-200'
                                            : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200'"
                                    >
                                        {{ form.digital_menu_enabled ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Digital Menu Configuration (shown only when enabled) -->
                            <div v-if="form.digital_menu_enabled" class="space-y-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <!-- WhatsApp Number -->
                                <div>
                                    <InputLabel for="whatsapp_number" value="Número de WhatsApp *" />
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 sm:text-sm">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                            </svg>
                                        </span>
                                        <TextInput
                                            id="whatsapp_number"
                                            v-model="form.whatsapp_number"
                                            type="text"
                                            class="flex-1 rounded-l-none"
                                            placeholder="5215512345678"
                                            :required="form.digital_menu_enabled"
                                        />
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Número con código de país (Ej: 5215512345678 para México). Usado para verificación de clientes.
                                    </p>
                                </div>

                                <!-- Messages -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <InputLabel for="welcome_message" value="Mensaje de Bienvenida" />
                                        <textarea
                                            id="welcome_message"
                                            v-model="form.welcome_message"
                                            rows="3"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-purple-500 dark:focus:border-purple-600 focus:ring-purple-500 dark:focus:ring-purple-600 rounded-md shadow-sm"
                                            placeholder="¡Bienvenido a nuestro restaurante!"
                                        ></textarea>
                                    </div>

                                    <div>
                                        <InputLabel for="closed_message" value="Mensaje de Cerrado" />
                                        <textarea
                                            id="closed_message"
                                            v-model="form.closed_message"
                                            rows="3"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-purple-500 dark:focus:border-purple-600 focus:ring-purple-500 dark:focus:ring-purple-600 rounded-md shadow-sm"
                                            placeholder="El menú digital no está disponible en este momento."
                                        ></textarea>
                                    </div>
                                </div>

                                <!-- Schedule -->
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                                        Horario de Atención
                                    </h4>
                                    <ScheduleEditor v-model="form.digital_menu_schedule" />
                                </div>

                                <!-- Delivery Methods -->
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                                        Métodos de Entrega
                                    </h4>
                                    <DeliveryMethodsEditor
                                        v-model:allow-pickup="form.allow_pickup"
                                        v-model:allow-delivery="form.allow_delivery"
                                        v-model:allow-dine-in="form.allow_dine_in"
                                        v-model:delivery-fee="form.delivery_fee"
                                        v-model:min-order-amount="form.min_order_amount"
                                    />
                                </div>

                                <!-- Footer Message -->
                                <div>
                                    <InputLabel for="footer_message" value="Mensaje del Footer" />
                                    <TextInput
                                        id="footer_message"
                                        v-model="form.footer_message"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="Gracias por su preferencia"
                                    />
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Texto que aparecerá en el pie de página del menú digital
                                    </p>
                                </div>
                            </div>

                            <!-- Warning when disabled -->
                            <div v-else class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                            El menú digital está deshabilitado
                                        </h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Habilita el menú digital arriba para configurar los ajustes adicionales.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </BaseCard>
                </div>

                <!-- Payment Methods Tab -->
                <div v-show="activeTab === 'payment-methods'">
                    <BaseCard>
                        <div class="p-4 md:p-6 space-y-6">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Métodos de Pago</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Configura los métodos de pago aceptados</p>
                                </div>
                                <button type="button" @click="addPaymentMethod" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Agregar
                                </button>
                            </div>
                            <div class="space-y-4">
                                <div v-for="(method, index) in paymentMethodsForm" :key="index" class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <div>
                                            <InputLabel :for="'method_name_' + index" value="Nombre *" />
                                            <TextInput :id="'method_name_' + index" v-model="method.name" type="text" class="mt-1 block w-full" placeholder="efectivo" required />
                                        </div>
                                        <div>
                                            <InputLabel :for="'method_label_' + index" value="Etiqueta *" />
                                            <TextInput :id="'method_label_' + index" v-model="method.label" type="text" class="mt-1 block w-full" placeholder="Efectivo" required />
                                        </div>
                                        <div>
                                            <InputLabel :for="'method_icon_' + index" value="Icono" />
                                            <select :id="'method_icon_' + index" v-model="method.icon" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm">
                                                <option v-for="icon in paymentMethodIcons" :key="icon.value" :value="icon.value">{{ icon.label }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <InputLabel :for="'method_commission_' + index" value="Comisión %" />
                                            <TextInput :id="'method_commission_' + index" v-model="method.commission_percent" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" />
                                        </div>
                                    </div>
                                    <div class="mt-4 flex flex-wrap items-center gap-4">
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" v-model="method.is_active" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 focus:ring-purple-500" />
                                            <span class="text-sm text-gray-700 dark:text-gray-300">Activo</span>
                                        </label>
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" v-model="method.requires_reference" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 focus:ring-purple-500" />
                                            <span class="text-sm text-gray-700 dark:text-gray-300">Requiere referencia</span>
                                        </label>
                                        <button type="button" @click="removePaymentMethod(index)" class="ml-auto text-red-600 hover:text-red-700 dark:text-red-400 text-sm font-medium">Eliminar</button>
                                    </div>
                                </div>
                                <div v-if="paymentMethodsForm.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    <p>No hay métodos de pago configurados</p>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <BaseButton variant="primary" type="button" @click="savePaymentMethods" :disabled="paymentMethodsProcessing">
                                    {{ paymentMethodsProcessing ? 'Guardando...' : 'Guardar Métodos de Pago' }}
                                </BaseButton>
                            </div>
                        </div>
                    </BaseCard>
                </div>

                <!-- Printers Tab -->
                <div v-show="activeTab === 'printers'">
                    <BaseCard>
                        <div class="p-4 md:p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Configuración de Impresoras</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Configura las impresoras térmicas del sistema</p>
                            </div>
                            <!-- Kitchen Printer -->
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center gap-3 mb-4">
                                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <h4 class="font-medium text-gray-900 dark:text-white">Impresora de Cocina</h4>
                                    <label class="ml-auto inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="printerForm.kitchen.is_enabled" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Habilitada</span>
                                    </label>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div><InputLabel value="IP" /><TextInput v-model="printerForm.kitchen.ip_address" type="text" class="mt-1 block w-full" placeholder="192.168.1.100" /></div>
                                    <div><InputLabel value="Puerto" /><TextInput v-model="printerForm.kitchen.port" type="number" class="mt-1 block w-full" placeholder="9100" /></div>
                                    <div><InputLabel value="Ancho (mm)" /><select v-model="printerForm.kitchen.width_mm" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm"><option value="58">58mm</option><option value="80">80mm</option></select></div>
                                    <div class="flex items-end"><label class="inline-flex items-center gap-2 cursor-pointer pb-2"><input type="checkbox" v-model="printerForm.kitchen.auto_print" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Auto imprimir</span></label></div>
                                </div>
                            </div>
                            <!-- Customer Printer -->
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center gap-3 mb-4">
                                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <h4 class="font-medium text-gray-900 dark:text-white">Impresora de Cliente</h4>
                                    <label class="ml-auto inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="printerForm.customer.is_enabled" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Habilitada</span>
                                    </label>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div><InputLabel value="IP" /><TextInput v-model="printerForm.customer.ip_address" type="text" class="mt-1 block w-full" placeholder="192.168.1.101" /></div>
                                    <div><InputLabel value="Puerto" /><TextInput v-model="printerForm.customer.port" type="number" class="mt-1 block w-full" placeholder="9100" /></div>
                                    <div><InputLabel value="Ancho (mm)" /><select v-model="printerForm.customer.width_mm" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm"><option value="58">58mm</option><option value="80">80mm</option></select></div>
                                    <div class="flex items-end"><label class="inline-flex items-center gap-2 cursor-pointer pb-2"><input type="checkbox" v-model="printerForm.customer.auto_print" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Auto imprimir</span></label></div>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <BaseButton variant="primary" type="button" @click="savePrinterSettings" :disabled="printerProcessing">{{ printerProcessing ? 'Guardando...' : 'Guardar Impresoras' }}</BaseButton>
                            </div>
                        </div>
                    </BaseCard>
                </div>

                <!-- Tickets Tab -->
                <div v-show="activeTab === 'tickets'">
                    <BaseCard>
                        <div class="p-4 md:p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Configuración de Tickets</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Personaliza el contenido de los tickets impresos</p>
                            </div>
                            <!-- Customer Ticket Content -->
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Ticket de Cliente
                                </h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="ticketForm.show_logo" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Logo</span></label>
                                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="ticketForm.show_address" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Dirección</span></label>
                                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="ticketForm.show_phone" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Teléfono</span></label>
                                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="ticketForm.show_qr_code" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Código QR</span></label>
                                </div>
                                <div v-if="ticketForm.show_qr_code" class="mt-4">
                                    <InputLabel value="Contenido QR" />
                                    <select v-model="ticketForm.qr_content" class="mt-1 block w-full sm:w-auto border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm">
                                        <option value="sale_number">Número de venta</option>
                                        <option value="sale_url">URL de venta</option>
                                        <option value="menu_url">URL del menú</option>
                                        <option value="custom">Personalizado</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Messages -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div><InputLabel value="Mensaje de encabezado" /><TextInput v-model="ticketForm.header_message" type="text" class="mt-1 block w-full" placeholder="¡Bienvenido!" /></div>
                                <div><InputLabel value="Mensaje de pie" /><TextInput v-model="ticketForm.footer_message" type="text" class="mt-1 block w-full" placeholder="¡Gracias por su compra!" /></div>
                            </div>
                            <!-- Kitchen Ticket -->
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                    Ticket de Cocina
                                </h4>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="ticketForm.kitchen_show_order_number" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300"># Pedido</span></label>
                                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="ticketForm.kitchen_show_table_number" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300"># Mesa</span></label>
                                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="ticketForm.kitchen_show_customer_name" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Nombre cliente</span></label>
                                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="ticketForm.kitchen_show_notes" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Notas</span></label>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel value="Tamaño de fuente" />
                                        <select v-model="ticketForm.kitchen_font_size" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm">
                                            <option value="small">Pequeño</option>
                                            <option value="normal">Normal</option>
                                            <option value="large">Grande</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- Non-Kitchen Categories -->
                            <div>
                                <InputLabel value="Categorías que NO van a cocina" />
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span v-for="(cat, idx) in ticketForm.non_kitchen_categories" :key="idx" class="inline-flex items-center gap-1 px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded-full text-sm text-gray-700 dark:text-gray-300">
                                        {{ cat }}
                                        <button type="button" @click="removeNonKitchenCategory(idx)" class="text-gray-400 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                    </span>
                                </div>
                                <div class="mt-2 flex gap-2">
                                    <TextInput v-model="newNonKitchenCategory" type="text" class="flex-1" placeholder="Nueva categoría..." @keyup.enter="addNonKitchenCategory" />
                                    <button type="button" @click="addNonKitchenCategory" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300">Agregar</button>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <BaseButton variant="primary" type="button" @click="saveTicketSettings" :disabled="ticketProcessing">{{ ticketProcessing ? 'Guardando...' : 'Guardar Tickets' }}</BaseButton>
                            </div>
                        </div>
                    </BaseCard>
                </div>

                <!-- Orders Tab -->
                <div v-show="activeTab === 'orders'">
                    <BaseCard>
                        <div class="p-4 md:p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Configuración de Pedidos</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Personaliza el comportamiento de los pedidos</p>
                            </div>
                            <!-- Order Numbering -->
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                                    Numeración de Pedidos
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div><InputLabel value="Prefijo" /><TextInput v-model="orderForm.order_number_prefix" type="text" class="mt-1 block w-full" placeholder="ORD-" /></div>
                                    <div><InputLabel value="Dígitos" /><TextInput v-model="orderForm.order_number_padding" type="number" min="1" max="10" class="mt-1 block w-full" /></div>
                                    <div><InputLabel value="Reiniciar" /><select v-model="orderForm.order_number_reset" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm"><option value="never">Nunca</option><option value="daily">Diario</option><option value="monthly">Mensual</option><option value="yearly">Anual</option></select></div>
                                </div>
                            </div>
                            <!-- Service Types -->
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                    Tipos de Servicio
                                </h4>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="orderForm.allow_dine_in" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Comer aquí</span></label>
                                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="orderForm.allow_takeout" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Para llevar</span></label>
                                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="orderForm.allow_delivery" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Delivery</span></label>
                                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="orderForm.allow_scheduled" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Programados</span></label>
                                </div>
                            </div>
                            <!-- Taxes -->
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg>
                                    Impuestos
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="orderForm.tax_included" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">IVA incluido en precios</span></label>
                                    <div><InputLabel value="% IVA" /><TextInput v-model="orderForm.tax_percentage" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" /></div>
                                    <label class="flex items-center gap-2 cursor-pointer sm:mt-6"><input type="checkbox" v-model="orderForm.show_tax_breakdown" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Desglosar en ticket</span></label>
                                </div>
                            </div>
                            <!-- Tips & Requirements -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                    <h4 class="font-medium text-gray-900 dark:text-white mb-4">Propinas</h4>
                                    <label class="flex items-center gap-2 cursor-pointer mb-4"><input type="checkbox" v-model="orderForm.tip_enabled" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Habilitar propinas</span></label>
                                    <div v-if="orderForm.tip_enabled"><InputLabel value="Tiempo prep. (min)" /><TextInput v-model="orderForm.default_prep_time_minutes" type="number" min="1" class="mt-1 block w-full" /></div>
                                </div>
                                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                    <h4 class="font-medium text-gray-900 dark:text-white mb-4">Requisitos</h4>
                                    <div class="space-y-3">
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="orderForm.require_customer_name" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Nombre obligatorio</span></label>
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="orderForm.require_customer_phone" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Teléfono obligatorio</span></label>
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="orderForm.allow_notes_per_item" class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-purple-500" /><span class="text-sm text-gray-700 dark:text-gray-300">Notas por ítem</span></label>
                                    </div>
                                </div>
                            </div>
                            <!-- Amounts -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div><InputLabel value="Monto mínimo" /><TextInput v-model="orderForm.min_order_amount" type="number" step="0.01" min="0" class="mt-1 block w-full" /></div>
                                <div><InputLabel value="Mínimo delivery" /><TextInput v-model="orderForm.delivery_min_amount" type="number" step="0.01" min="0" class="mt-1 block w-full" /></div>
                                <div><InputLabel value="Cargo delivery" /><TextInput v-model="orderForm.delivery_fee" type="number" step="0.01" min="0" class="mt-1 block w-full" /></div>
                            </div>
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <BaseButton variant="primary" type="button" @click="saveOrderSettings" :disabled="orderProcessing">{{ orderProcessing ? 'Guardando...' : 'Guardar Pedidos' }}</BaseButton>
                            </div>
                        </div>
                    </BaseCard>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end items-center gap-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <BaseButton
                        variant="secondary"
                        @click="$inertia.visit(route('dashboard'))"
                        type="button"
                    >
                        Cancelar
                    </BaseButton>

                    <BaseButton
                        variant="primary"
                        type="submit"
                        :disabled="processing"
                    >
                        <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ processing ? 'Guardando...' : 'Guardar Cambios' }}
                    </BaseButton>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
