<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    cart: Array,
    cartTotal: Number,
    settings: Object,
});

const emit = defineEmits(['close', 'orderCreated']);

// Estados del flujo
const currentStep = ref(1); // 1: teléfono, 2: código, 3: nombre (si es nuevo), 4: detalles orden
const loading = ref(false);
const error = ref('');

// Datos del cliente
const countryCode = ref('+52');
const phone = ref('');
const verificationCode = ref('');
const customerName = ref('');
const customerId = ref(null);
const isNewCustomer = ref(false);

// Datos de la orden
const deliveryMethod = ref('pickup');
const customerNotes = ref('');
const customerAddress = ref('');

// Touch gesture state
const touchStart = ref({ y: 0 });
const touchDelta = ref(0);
const isDragging = ref(false);

const countryCodes = [
    { value: '+52', label: '+52 (México)' },
    { value: '+503', label: '+503 (El Salvador)' },
    { value: '+56', label: '+56 (Chile)' },
    { value: '+1', label: '+1 (USA/Canada)' },
    { value: '+34', label: '+34 (España)' },
    { value: '+54', label: '+54 (Argentina)' },
    { value: '+55', label: '+55 (Brasil)' },
];

const deliveryMethods = computed(() => {
    const methods = props.settings?.delivery_methods || [];
    return methods.length > 0 ? methods : [
        { id: 'pickup', label: 'Para llevar', fee: 0 },
        { id: 'dine_in', label: 'Comer aquí', fee: 0 },
    ];
});

// Validaciones
const isPhoneValid = computed(() => phone.value.trim().length >= 8);
const isCodeValid = computed(() => verificationCode.value.length === 6);
const isNameValid = computed(() => customerName.value.trim().length >= 2);

// Paso 1: Enviar código de verificación
const sendVerificationCode = async () => {
    if (!isPhoneValid.value) {
        error.value = 'Por favor ingresa un número de teléfono válido';
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        const response = await axios.post('/api/digital-menu/auth/send-code', {
            phone: phone.value.trim(),
            country_code: countryCode.value,
        });

        if (response.data.success) {
            customerId.value = response.data.customer_id;
            isNewCustomer.value = response.data.is_new_customer || false;
            currentStep.value = 2;
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al enviar el código. Intenta de nuevo.';
    } finally {
        loading.value = false;
    }
};

// Paso 2: Verificar código
const verifyCode = async () => {
    if (!isCodeValid.value) {
        error.value = 'El código debe tener 6 dígitos';
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        const response = await axios.post('/api/digital-menu/auth/verify-code', {
            customer_id: customerId.value,
            code: verificationCode.value,
            name: isNewCustomer.value ? customerName.value.trim() : undefined,
        });

        if (response.data.success) {
            // Si es cliente nuevo y no pidió nombre, ir a paso 3
            if (isNewCustomer.value && !customerName.value) {
                currentStep.value = 3;
            } else {
                // Ir directo a detalles de orden
                customerName.value = response.data.customer.name || '';
                currentStep.value = 4;
            }
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Código inválido o expirado';
    } finally {
        loading.value = false;
    }
};

// Paso 3: Guardar nombre (clientes nuevos)
const saveName = async () => {
    if (!isNameValid.value) {
        error.value = 'Por favor ingresa tu nombre';
        return;
    }

    // Volver a verificar con el nombre
    await verifyCode();
};

// Paso 4: Crear orden
const createOrder = async () => {
    loading.value = true;
    error.value = '';

    try {
        const orderData = {
            items: props.cart.map(item => ({
                type: item.product_type,
                id: item.id,
                variant_id: item.variant_id || null,
                quantity: item.quantity,
                price: item.price,
            })),
            delivery_method: deliveryMethod.value,
            customer_notes: customerNotes.value.trim(),
            customer_address: customerAddress.value.trim(),
        };

        const response = await axios.post('/api/digital-menu/orders', orderData);

        if (response.data.success) {
            // Enviar notificación de WhatsApp al restaurante
            sendWhatsAppToRestaurant(response.data.sale);

            // Emitir evento de orden creada
            emit('orderCreated', response.data.sale);
            resetForm();
            emit('close');
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al crear la orden. Intenta de nuevo.';
    } finally {
        loading.value = false;
    }
};

// Enviar notificación de WhatsApp al restaurante
const sendWhatsAppToRestaurant = (sale) => {
    const restaurantName = props.settings.restaurant_name || 'Restaurant';
    const whatsappNumber = props.settings.whatsapp_number || '';

    if (!whatsappNumber) {
        console.warn('Número de WhatsApp del restaurante no configurado');
        return;
    }

    // Método de entrega
    const deliveryLabels = {
        'pickup': '🛍️ Para llevar',
        'dine_in': '🍽️ Comer aquí',
        'delivery': '🛵 Delivery'
    };

    // Construir mensaje
    let message = `🔔 *NUEVO PEDIDO #${sale.sale_number}*\n\n`;
    message += `🍽️ *${restaurantName}*\n`;
    message += `⏰ ${new Date().toLocaleString('es-MX', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    })}\n\n`;
    message += `👤 *Cliente:* ${customerName.value}\n`;
    message += `📞 *Teléfono:* ${countryCode.value} ${phone.value}\n`;
    message += `📍 *Método:* ${deliveryLabels[deliveryMethod.value] || deliveryMethod.value}\n`;

    if (deliveryMethod.value === 'delivery' && customerAddress.value) {
        message += `🏠 *Dirección:* ${customerAddress.value}\n`;
    }

    message += `\n📋 *DETALLE DEL PEDIDO:*\n`;
    message += `━━━━━━━━━━━━━━━━\n`;

    props.cart.forEach((item, index) => {
        const itemTotal = (item.price * item.quantity).toFixed(2);
        message += `${index + 1}. ${item.name}`;
        if (item.variant_name) {
            message += ` (${item.variant_name})`;
        }
        message += `\n   x${item.quantity} - $${itemTotal}\n`;
    });

    message += `━━━━━━━━━━━━━━━━\n`;
    message += `💰 *TOTAL: $${props.cartTotal.toFixed(2)}*\n`;

    if (customerNotes.value) {
        message += `\n📝 *Notas:*\n${customerNotes.value}\n`;
    }

    message += `\n━━━━━━━━━━━━━━━━`;
    message += `\n📱 *Pedido desde Menú Digital*`;
    message += `\n✅ Guardado en sistema`;

    // Limpiar número de WhatsApp
    const cleanNumber = whatsappNumber.replace(/[^0-9]/g, '');

    // Abrir WhatsApp en nueva pestaña
    const whatsappUrl = `https://wa.me/${cleanNumber}?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
};

const resetForm = () => {
    currentStep.value = 1;
    phone.value = '';
    verificationCode.value = '';
    customerName.value = '';
    customerId.value = null;
    isNewCustomer.value = false;
    deliveryMethod.value = 'pickup';
    customerNotes.value = '';
    customerAddress.value = '';
    error.value = '';
};

const handleClose = () => {
    if (!loading.value) {
        resetForm();
        emit('close');
    }
};

const goBack = () => {
    if (currentStep.value > 1 && !loading.value) {
        currentStep.value--;
        error.value = '';
    }
};

// Touch handlers for swipe-to-close
const handleTouchStart = (e) => {
    touchStart.value.y = e.touches[0].clientY;
    touchDelta.value = 0;
    isDragging.value = false;
};

const handleTouchMove = (e) => {
    const deltaY = e.touches[0].clientY - touchStart.value.y;
    if (deltaY > 5) {
        isDragging.value = true;
        touchDelta.value = Math.min(deltaY * 0.8, 250);
    }
};

const handleTouchEnd = () => {
    if (isDragging.value && touchDelta.value > 80) {
        handleClose();
    }
    touchDelta.value = 0;
    isDragging.value = false;
};
</script>

<template>
    <!-- Backdrop -->
    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            @click="handleClose"
            class="fixed inset-0 bg-black/60 z-50"
        ></div>
    </Transition>

    <!-- SlideOver -->
    <Transition
        enter-active-class="transition-transform duration-300"
        enter-from-class="translate-y-full"
        enter-to-class="translate-y-0"
        leave-active-class="transition-transform duration-200"
        leave-from-class="translate-y-0"
        leave-to-class="translate-y-full"
    >
        <div
            v-if="show"
            @touchstart="handleTouchStart"
            @touchmove.passive="handleTouchMove"
            @touchend="handleTouchEnd"
            class="fixed inset-x-0 bottom-0 z-50 max-h-[90vh] bg-white dark:bg-gray-800 rounded-t-2xl shadow-xl flex flex-col"
            :style="{
                transform: isDragging ? `translateY(${touchDelta}px)` : '',
                transition: isDragging ? 'none' : 'transform 0.2s ease-out'
            }"
        >
            <!-- Handle Bar -->
            <div class="flex justify-center pt-3 pb-2 cursor-grab">
                <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
            </div>

            <!-- Header -->
            <div class="px-5 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button
                            v-if="currentStep > 1"
                            @click="goBack"
                            :disabled="loading"
                            class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors disabled:opacity-50"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ currentStep === 1 ? 'Verificación' : currentStep === 2 ? 'Código' : currentStep === 3 ? 'Tu Nombre' : 'Detalles del Pedido' }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Paso {{ currentStep }} de 4
                            </p>
                        </div>
                    </div>
                    <button
                        @click="handleClose"
                        :disabled="loading"
                        class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="mx-5 mt-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-sm text-red-600 dark:text-red-400 text-center">{{ error }}</p>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-5 space-y-5">
                <!-- Paso 1: Teléfono -->
                <div v-if="currentStep === 1" class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Te enviaremos un código de verificación por WhatsApp para confirmar tu pedido
                    </p>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Código de país
                        </label>
                        <select
                            v-model="countryCode"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        >
                            <option v-for="country in countryCodes" :key="country.value" :value="country.value">
                                {{ country.label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Número de WhatsApp *
                        </label>
                        <input
                            v-model="phone"
                            type="tel"
                            placeholder="1234567890"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            @keyup.enter="sendVerificationCode"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Usaremos este número para enviarte el código de verificación
                        </p>
                    </div>
                </div>

                <!-- Paso 2: Código -->
                <div v-if="currentStep === 2" class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                        Enviamos un código de 6 dígitos a<br>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ countryCode }} {{ phone }}</span>
                    </p>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-center">
                            Código de verificación
                        </label>
                        <input
                            v-model="verificationCode"
                            type="text"
                            inputmode="numeric"
                            maxlength="6"
                            placeholder="000000"
                            class="w-full px-4 py-4 text-center text-2xl font-bold tracking-widest rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            @keyup.enter="verifyCode"
                        />
                    </div>

                    <button
                        @click="sendVerificationCode"
                        :disabled="loading"
                        class="w-full text-center text-sm text-orange-600 dark:text-orange-400 hover:underline disabled:opacity-50"
                    >
                        Reenviar código
                    </button>
                </div>

                <!-- Paso 3: Nombre (solo clientes nuevos) -->
                <div v-if="currentStep === 3" class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                        ¡Bienvenido! Para finalizar, necesitamos tu nombre
                    </p>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tu nombre *
                        </label>
                        <input
                            v-model="customerName"
                            type="text"
                            placeholder="¿Cómo te llamas?"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            @keyup.enter="saveName"
                        />
                    </div>
                </div>

                <!-- Paso 4: Detalles de la orden -->
                <div v-if="currentStep === 4" class="space-y-5">
                    <div class="p-4 bg-green-50 dark:bg-green-900/30 rounded-xl border border-green-200 dark:border-green-800">
                        <div class="flex items-center gap-2 text-green-700 dark:text-green-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="font-medium">Verificado: {{ customerName }}</span>
                        </div>
                    </div>

                    <!-- Método de entrega -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            ¿Cómo lo quieres?
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                v-for="method in deliveryMethods"
                                :key="method.id"
                                @click="deliveryMethod = method.id"
                                class="p-3 rounded-xl border-2 text-center transition-all"
                                :class="deliveryMethod === method.id
                                    ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300'
                                    : 'border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-orange-300'
                                "
                            >
                                <span class="text-lg mb-1 block">
                                    {{ method.id === 'pickup' ? '🛍️' : method.id === 'dine_in' ? '🍽️' : '🛵' }}
                                </span>
                                <span class="text-sm font-medium">{{ method.label }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Dirección (solo si es delivery) -->
                    <div v-if="deliveryMethod === 'delivery'">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Dirección de entrega *
                        </label>
                        <textarea
                            v-model="customerAddress"
                            rows="2"
                            placeholder="Calle, número, colonia..."
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"
                        ></textarea>
                    </div>

                    <!-- Notas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Comentarios (opcional)
                        </label>
                        <textarea
                            v-model="customerNotes"
                            rows="2"
                            placeholder="¿Alguna indicación especial?"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"
                        ></textarea>
                    </div>

                    <!-- Resumen -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
                        <div class="flex items-center justify-between text-lg font-bold">
                            <span class="text-gray-700 dark:text-gray-300">Total:</span>
                            <span class="text-orange-600 dark:text-orange-400">${{ cartTotal.toFixed(2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-5 bg-gray-50 dark:bg-gray-800/50">
                <button
                    @click="currentStep === 1 ? sendVerificationCode() : currentStep === 2 ? verifyCode() : currentStep === 3 ? saveName() : createOrder()"
                    :disabled="loading || (currentStep === 1 && !isPhoneValid) || (currentStep === 2 && !isCodeValid) || (currentStep === 3 && !isNameValid)"
                    class="w-full py-4 rounded-2xl font-bold text-lg transition-all duration-200 flex items-center justify-center gap-2"
                    :class="(loading || (currentStep === 1 && !isPhoneValid) || (currentStep === 2 && !isCodeValid) || (currentStep === 3 && !isNameValid))
                        ? 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                        : 'bg-orange-600 hover:bg-orange-700 text-white shadow-lg active:scale-98'
                    "
                >
                    <svg v-if="loading" class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span v-if="!loading">
                        {{ currentStep === 1 ? 'Enviar código' : currentStep === 2 ? 'Verificar' : currentStep === 3 ? 'Continuar' : 'Confirmar Pedido' }}
                    </span>
                    <span v-else>Procesando...</span>
                </button>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.active\:scale-98:active {
    transform: scale(0.98);
}
</style>
