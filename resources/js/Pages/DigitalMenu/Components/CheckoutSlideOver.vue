<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    cart: Array,
    cartTotal: Number,
    settings: Object,
    availableTables: Array,
});

const emit = defineEmits(['close', 'orderCreated']);

// Estados del flujo
const currentStep = ref(1); // 1: teléfono, 2: código, 3: nombre (si es nuevo), 4: detalles orden
const loading = ref(false);
const error = ref('');

// Datos del cliente
const countryCode = ref(props.settings?.country_code || '+52'); // Usar país del restaurante
const phone = ref('');
const verificationCode = ref('');
const generatedCode = ref(''); // El código generado por el backend
const customerName = ref('');
const customerId = ref(null);
const isNewCustomer = ref(false);
const whatsappUrl = ref(''); // URL para enviar código por WhatsApp

// Datos de la orden
const deliveryMethod = ref('pickup');
const customerNotes = ref('');
const customerAddress = ref('');
const selectedTable = ref(null);

// Touch gesture state
const touchStart = ref({ y: 0 });
const touchDelta = ref(0);
const isDragging = ref(false);

const deliveryMethods = computed(() => {
    const methods = props.settings?.delivery_methods || [];
    return methods.length > 0 ? methods : [
        { value: 'pickup', label: 'Para llevar', fee: 0 },
        { value: 'dine_in', label: 'Comer aquí', fee: 0 },
    ];
});

// Validaciones
const isPhoneValid = computed(() => {
    // Remover guiones, espacios y otros caracteres no numéricos para validar
    const digitsOnly = phone.value.replace(/\D/g, '');
    return digitsOnly.length >= 8;
});
const isCodeValid = computed(() => verificationCode.value.length === 6);
const isNameValid = computed(() => customerName.value.trim().length >= 2);

// Función para seleccionar método de entrega
const selectDeliveryMethod = (methodId) => {
    deliveryMethod.value = methodId;
};

// Verificar autenticación cuando se abre el modal
const checkAuthStatus = async () => {
    try {
        const response = await axios.get('/api/digital-menu/auth/me');
        if (response.data.authenticated) {
            customerId.value = response.data.customer.id;
            customerName.value = response.data.customer.name;
            phone.value = response.data.customer.phone;
            countryCode.value = response.data.customer.country_code;
            currentStep.value = 4; // Ir directo al paso de detalles
        }
    } catch (err) {
        // No hay sesión activa, empezar desde paso 1
        console.log('No hay sesión activa');
    }
};

// Cuando se abre el modal, verificar si ya está autenticado
watch(() => props.show, (newVal, oldVal) => {
    if (newVal && !oldVal) {
        // Modal se acaba de abrir
        checkAuthStatus();
    }
});

// Formateo inteligente de número de teléfono
const formatPhoneNumber = (event) => {
    let value = event.target.value;

    // Permitir solo números, espacios y guiones
    value = value.replace(/[^\d\s-]/g, '');

    // Aplicar formato según el código de país
    if (countryCode.value === '+503') {
        // El Salvador: 8 dígitos (7355-4002)
        value = value.replace(/\D/g, '').substring(0, 8);
        if (value.length > 4) {
            value = value.substring(0, 4) + '-' + value.substring(4);
        }
    } else if (countryCode.value === '+52') {
        // México: 10 dígitos (664-123-4567)
        value = value.replace(/\D/g, '').substring(0, 10);
        if (value.length > 6) {
            value = value.substring(0, 3) + '-' + value.substring(3, 6) + '-' + value.substring(6);
        } else if (value.length > 3) {
            value = value.substring(0, 3) + '-' + value.substring(3);
        }
    } else {
        // Otros países: máximo 15 dígitos
        value = value.replace(/\D/g, '').substring(0, 15);
    }

    phone.value = value;
};

// Placeholder según código de país
const getPhonePlaceholder = () => {
    const placeholders = {
        '+503': '7355-4002',
        '+52': '664-123-4567',
        '+1': '555-123-4567',
        '+34': '612-345-678',
        '+54': '11-2345-6789',
        '+55': '11-98765-4321',
        '+56': '9-1234-5678'
    };
    return placeholders[countryCode.value] || '1234567890';
};

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
            generatedCode.value = response.data.verification_code; // Código para mostrar
            whatsappUrl.value = response.data.whatsapp_url || ''; // URL de WhatsApp opcional
            currentStep.value = 2;
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al generar el código. Intenta de nuevo.';
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
            // Solo enviar el nombre si ya está lleno (paso 3)
            name: customerName.value.trim() || undefined,
        });

        if (response.data.success) {
            const returnedName = response.data.customer.name || '';

            // Si es cliente nuevo y no tiene nombre, ir a paso 3
            if (isNewCustomer.value && !returnedName) {
                currentStep.value = 3;
            } else {
                // Ir directo a detalles de orden
                customerName.value = returnedName;
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

    // Debug: ver valor de deliveryMethod
    console.log('deliveryMethod:', deliveryMethod.value);

    // Validar que delivery method esté seleccionado
    if (!deliveryMethod.value) {
        error.value = 'Por favor selecciona un método de entrega';
        loading.value = false;
        return;
    }

    // Validar que se haya seleccionado mesa si es dine_in
    if (deliveryMethod.value === 'dine_in' && !selectedTable.value) {
        error.value = 'Por favor selecciona una mesa';
        loading.value = false;
        return;
    }

    // Validar que se haya ingresado dirección si es delivery
    if (deliveryMethod.value === 'delivery' && !customerAddress.value.trim()) {
        error.value = 'Por favor ingresa tu dirección de entrega';
        loading.value = false;
        return;
    }

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
            table_id: selectedTable.value,
        };

        console.log('orderData:', orderData);

        const response = await axios.post('/api/digital-menu/orders', orderData);

        if (response.data.success) {
            // Emitir evento de orden creada con todos los datos
            emit('orderCreated', {
                sale: response.data.sale,
                whatsappUrl: response.data.whatsapp_url,
                trackingUrl: response.data.tracking_url,
            });
            resetForm();
            emit('close');
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al crear la orden. Intenta de nuevo.';
    } finally {
        loading.value = false;
    }
};

// Abrir WhatsApp para recibir código
const openWhatsAppCode = () => {
    if (whatsappUrl.value) {
        window.open(whatsappUrl.value, '_blank');
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
                            Número de WhatsApp *
                        </label>
                        <div class="flex gap-2">
                            <div class="flex-shrink-0 flex items-center px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium">
                                {{ countryCode }}
                            </div>
                            <input
                                v-model="phone"
                                type="tel"
                                :placeholder="getPhonePlaceholder()"
                                class="flex-1 px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                @input="formatPhoneNumber"
                                @keyup.enter="sendVerificationCode"
                            />
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Usaremos este número para enviarte el código de verificación
                        </p>
                    </div>
                </div>

                <!-- Paso 2: Código -->
                <div v-if="currentStep === 2" class="space-y-4">
                    <!-- Mostrar código generado prominentemente -->
                    <div v-if="generatedCode" class="p-5 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 rounded-2xl border-2 border-green-400 dark:border-green-600 shadow-lg">
                        <p class="text-sm text-center text-gray-700 dark:text-gray-300 mb-2">
                            Tu código de verificación es:
                        </p>
                        <div class="flex items-center justify-center gap-1 mb-3">
                            <p class="text-5xl font-bold text-center text-green-700 dark:text-green-400 tracking-[0.3em] font-mono">
                                {{ generatedCode }}
                            </p>
                            <button
                                @click="verificationCode = generatedCode"
                                class="ml-3 p-2 text-green-700 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-800/50 rounded-lg transition-colors"
                                title="Copiar código"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Válido por 10 minutos</span>
                        </div>

                        <!-- Botón opcional de WhatsApp -->
                        <button
                            v-if="whatsappUrl"
                            @click="openWhatsAppCode"
                            class="mt-3 w-full py-2 px-4 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            Recibir código por WhatsApp
                        </button>
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                        Ingresa el código en el campo de abajo para continuar
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
                            ¿Cómo lo quieres? <span v-if="deliveryMethod" class="text-xs text-gray-500">(Seleccionado: {{ deliveryMethod }})</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label
                                v-for="method in deliveryMethods"
                                :key="method.value"
                                class="relative cursor-pointer"
                            >
                                <input
                                    type="radio"
                                    name="delivery_method"
                                    :value="method.value"
                                    v-model="deliveryMethod"
                                    class="sr-only peer"
                                />
                                <div class="p-3 rounded-xl border-2 text-center transition-all peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/30 peer-checked:text-orange-700 dark:peer-checked:text-orange-300 border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-orange-300">
                                    <!-- Icono -->
                                    <svg v-if="method.value === 'pickup'" class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <svg v-else-if="method.value === 'dine_in'" class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <svg v-else class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                    <span class="text-sm font-medium block">{{ method.label }}</span>
                                </div>
                            </label>
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

                    <!-- Selector de Mesa (solo si es dine_in) -->
                    <div v-if="deliveryMethod === 'dine_in'">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Selecciona tu mesa *
                        </label>
                        <select
                            v-model="selectedTable"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        >
                            <option :value="null">Selecciona una mesa...</option>
                            <option
                                v-for="table in availableTables"
                                :key="table.id"
                                :value="table.id"
                            >
                                Mesa {{ table.table_number }} ({{ table.capacity }} personas)
                            </option>
                        </select>
                        <p v-if="deliveryMethod === 'dine_in' && !selectedTable" class="mt-1 text-xs text-red-500">
                            Debes seleccionar una mesa para continuar
                        </p>
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
