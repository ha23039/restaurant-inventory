<script setup>
// 1. IMPORTS CORRECTOS AL INICIO
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useToast } from 'vue-toastification';

// 2. PROPS DEL COMPONENTE
const props = defineProps({
    menu_items: Object,
    simple_products: Object
});

// 3. ESTADO PRINCIPAL DEL POS
const searchTerm = ref('');
const selectedCategory = ref('');
const activeTab = ref('menu');
const cartItems = ref([]);
const discount = ref(0);
const tax = ref(0);
const paymentMethod = ref('efectivo');
const processing = ref(false);
const toast = useToast();

// VENTA LIBRE
const isFreeSale = ref(false);
const freeSaleDescription = ref('');
const freeSaleTotal = ref('');

// 4. FUNCIONES DE PERSISTENCIA DEL CARRITO
const saveCartToStorage = () => {
    const cartData = {
        items: cartItems.value,
        discount: discount.value,
        tax: tax.value,
        paymentMethod: paymentMethod.value,
        timestamp: Date.now()
    };
    localStorage.setItem('pos_cart_data', JSON.stringify(cartData));
};

const loadCartFromStorage = () => {
    try {
        const savedCart = localStorage.getItem('pos_cart_data');
        if (savedCart) {
            const cartData = JSON.parse(savedCart);
            
            // Verificar que no sea muy antiguo (24 horas)
            const isExpired = (Date.now() - cartData.timestamp) > 24 * 60 * 60 * 1000;
            
            if (!isExpired && cartData.items) {
                cartItems.value = cartData.items || [];
                discount.value = cartData.discount || 0;
                tax.value = cartData.tax || 0;
                paymentMethod.value = cartData.paymentMethod || 'efectivo';
                
                if (cartItems.value.length > 0) {
                    showNotification(`Carrito restaurado con ${cartItems.value.length} productos`, 'info');
                }
            } else {
                // Limpiar carrito expirado
                clearCartStorage();
            }
        }
    } catch (error) {
        console.warn('Error cargando carrito guardado:', error);
        clearCartStorage();
    }
};

const clearCartStorage = () => {
    localStorage.removeItem('pos_cart_data');
};

// 5. COMPUTED PROPERTIES
const currentDate = computed(() => {
    return new Date().toLocaleDateString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

const groupedProducts = computed(() => {
    const menuProducts = (props.menu_items.data || []).map(item => ({
        ...item,
        product_type: 'menu'
    }));
    
    const simpleProducts = (props.simple_products.data || []).map(item => ({
        ...item,
        product_type: 'simple'
    }));

    // Filtrar por búsqueda y categoría
    const allProducts = [...menuProducts, ...simpleProducts].filter(product => {
        const matchesSearch = !searchTerm.value || 
            product.name.toLowerCase().includes(searchTerm.value.toLowerCase());
        
        const matchesCategory = !selectedCategory.value || 
            (selectedCategory.value === 'menu' && product.product_type === 'menu') ||
            (selectedCategory.value !== 'menu' && product.category === selectedCategory.value);
        
        return matchesSearch && matchesCategory;
    });

    // Agrupar por categorías
    const groups = {
        menu: { title: 'Platillos del Menú', items: [] },
        bebida: { title: 'Bebidas', items: [] },
        extra: { title: 'Extras', items: [] },
        condimento: { title: 'Condimentos', items: [] },
    };

    allProducts.forEach(product => {
        const category = product.product_type === 'menu' ? 'menu' : product.category;
        if (groups[category]) {
            groups[category].items.push(product);
        }
    });

    return groups;
});

const subtotal = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const total = computed(() => {
    return Math.max(0, subtotal.value - parseFloat(discount.value || 0) + parseFloat(tax.value || 0));
});

// 6. MÉTODOS DEL CARRITO CON PERSISTENCIA
const addToCart = (product) => {
    if (!product.is_in_stock) {
        showNotification('Este producto no está disponible por falta de stock', 'warning');
        return;
    }

    const existingIndex = cartItems.value.findIndex(item => 
        item.id === product.id && item.product_type === product.product_type
    );
    
    if (existingIndex >= 0) {
        if (cartItems.value[existingIndex].quantity < product.available_quantity) {
            cartItems.value[existingIndex].quantity++;
            showNotification(`${product.name} agregado al carrito`, 'success');
        } else {
            showNotification(`Solo hay ${product.available_quantity} ${product.name} disponibles`, 'warning');
        }
    } else {
        cartItems.value.push({
            id: product.id,
            name: product.name,
            price: product.price,
            quantity: 1,
            available_quantity: product.available_quantity,
            product_type: product.product_type
        });
        showNotification(`${product.name} agregado al carrito`, 'success');
    }
    
    // Guardar automáticamente
    saveCartToStorage();
};

const incrementQuantity = (index) => {
    const item = cartItems.value[index];
    if (item.quantity < item.available_quantity) {
        item.quantity++;
        saveCartToStorage();
    } else {
        showNotification(`Stock máximo alcanzado para ${item.name}`, 'warning');
    }
};

const decrementQuantity = (index) => {
    const item = cartItems.value[index];
    if (item.quantity > 1) {
        item.quantity--;
        saveCartToStorage();
    } else {
        removeFromCart(index);
    }
};

const removeFromCart = (index) => {
    const item = cartItems.value[index];
    cartItems.value.splice(index, 1);
    showNotification(`${item.name} eliminado del carrito`, 'info');
    saveCartToStorage();
};

const clearCart = () => {
    if (confirm('¿Estás seguro de limpiar el carrito?')) {
        cartItems.value = [];
        discount.value = 0;
        tax.value = 0;
        clearCartStorage();
        showNotification('Carrito limpiado', 'info');
    }
};

// 7. PROCESAR VENTA
const processSale = () => {
    // Validaciones para venta libre
    if (isFreeSale.value) {
        if (!freeSaleDescription.value || freeSaleDescription.value.length < 3) {
            showNotification('La descripción debe tener al menos 3 caracteres', 'warning');
            return;
        }

        if (!freeSaleTotal.value || parseFloat(freeSaleTotal.value) <= 0) {
            showNotification('El monto debe ser mayor a 0', 'warning');
            return;
        }
    } else {
        // Validación para venta normal
        if (cartItems.value.length === 0) {
            showNotification('El carrito está vacío', 'warning');
            return;
        }
    }

    if (!paymentMethod.value) {
        showNotification('Selecciona un método de pago', 'warning');
        return;
    }

    processing.value = true;

    const saleData = isFreeSale.value ? {
        // Datos para venta libre
        is_free_sale: true,
        free_sale_description: freeSaleDescription.value,
        free_sale_total: parseFloat(freeSaleTotal.value),
        payment_method: paymentMethod.value,
        discount: 0,
        tax: 0
    } : {
        // Datos para venta normal
        is_free_sale: false,
        items: cartItems.value.map(item => ({
            id: item.id,
            product_type: item.product_type,
            quantity: item.quantity,
            unit_price: item.price
        })),
        payment_method: paymentMethod.value,
        discount: parseFloat(discount.value || 0),
        tax: parseFloat(tax.value || 0)
    };

    console.log('Enviando datos de venta:', saleData);

    router.post(route('sales.pos.store'), saleData, {
        onSuccess: (page) => {
            console.log('Venta exitosa:', page);
            const message = isFreeSale.value
                ? '¡Venta libre procesada exitosamente!'
                : '¡Venta procesada exitosamente!';
            showNotification(message, 'success');

            // Limpiar carrito y storage después de venta exitosa
            cartItems.value = [];
            discount.value = 0;
            tax.value = 0;
            paymentMethod.value = 'efectivo';

            // Limpiar datos de venta libre
            isFreeSale.value = false;
            freeSaleDescription.value = '';
            freeSaleTotal.value = '';

            clearCartStorage();
        },
        onError: (errors) => {
            console.error('Error en venta:', errors);
            
            if (errors.message) {
                showNotification('Error: ' + errors.message, 'error');
            } else if (typeof errors === 'object') {
                showNotification('Error: ' + Object.values(errors).join(', '), 'error');
            } else {
                showNotification('Error desconocido al procesar la venta', 'error');
            }
        },
        onFinish: () => {
            processing.value = false;
        }
    });
};

// 8. SISTEMA DE NOTIFICACIONES (usando vue-toastification)
const showNotification = (message, type = 'info') => {
    switch (type) {
        case 'success':
            toast.success(message);
            break;
        case 'error':
            toast.error(message);
            break;
        case 'warning':
            toast.warning(message);
            break;
        default:
            toast.info(message);
    }
};

// 9. FUNCIONES DE UTILIDAD
const formatPrice = (price) => {
    return parseFloat(price || 0).toFixed(2);
};

// 10. WATCHERS PARA AUTO-GUARDAR
watch([discount, tax, paymentMethod], () => {
    if (cartItems.value.length > 0) {
        saveCartToStorage();
    }
});

// 11. LIFECYCLE HOOKS
onMounted(() => {
    // Cargar carrito guardado
    loadCartFromStorage();
    console.log('🛒 POS iniciado - Carrito persistente activado');
});

onBeforeUnmount(() => {
    // Guardar antes de salir de la página
    if (cartItems.value.length > 0) {
        saveCartToStorage();
    }
});
</script>

<template>
    <Head title="Punto de Venta" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Punto de Venta (POS)
                </h2>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-600">
                        {{ currentDate }}
                    </div>
                    <div class="text-sm text-gray-600">
                        Cajero: {{ $page.props.auth.user.name }}
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Panel de Productos -->
                    <div class="lg:col-span-2">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <!-- Header con controles -->
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900">Catálogo de Productos</h3>
                                    <div class="flex items-center space-x-3">
                                        <input
                                            v-model="searchTerm"
                                            type="text"
                                            placeholder="Buscar producto..."
                                            class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        />
                                        <select
                                            v-model="selectedCategory"
                                            class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        >
                                            <option value="">Todas las categorías</option>
                                            <option value="menu">Platillos del Menú</option>
                                            <option value="bebida">Bebidas</option>
                                            <option value="extra">Extras</option>
                                            <option value="condimento">Condimentos</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Tabs de categorías -->
                                <div class="border-b border-gray-200 mb-6">
                                    <nav class="-mb-px flex space-x-8">
                                        <button
                                            v-for="(group, key) in groupedProducts"
                                            :key="key"
                                            @click="activeTab = key"
                                            :class="[
                                                activeTab === key
                                                    ? 'border-indigo-500 text-indigo-600'
                                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
                                            ]"
                                        >
                                            {{ group.title }} ({{ group.items.length }})
                                        </button>
                                    </nav>
                                </div>

                                <!-- Grid de productos -->
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                    <div
                                        v-for="item in groupedProducts[activeTab]?.items || []"
                                        :key="`${item.product_type}-${item.id}`"
                                        class="border rounded-lg p-4 transition-all duration-200 cursor-pointer relative"
                                        :class="[
                                            item.is_in_stock 
                                                ? 'hover:shadow-md hover:border-blue-300 bg-white' 
                                                : 'bg-gray-100 border-gray-300 cursor-not-allowed opacity-60'
                                        ]"
                                        @click="addToCart(item)"
                                    >
                                        <!-- Badge de disponibilidad -->
                                        <div class="absolute top-2 right-2">
                                            <span 
                                                v-if="item.is_in_stock"
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                            >
                                                Stock: {{ item.available_quantity }}
                                            </span>
                                            <span 
                                                v-else
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                            >
                                                Agotado
                                            </span>
                                        </div>

                                        <div class="mt-4">
                                            <h4 class="font-semibold text-gray-900 text-lg">{{ item.name }}</h4>
                                            <p class="text-sm text-gray-600 mt-1" v-if="item.description">
                                                {{ item.description }}
                                            </p>
                                            
                                            <!-- Ingredientes para platillos del menú -->
                                            <div class="mt-2" v-if="item.product_type === 'menu' && item.recipes && item.recipes.length > 0">
                                                <p class="text-xs text-gray-500 mb-1">Ingredientes:</p>
                                                <div class="flex flex-wrap gap-1">
                                                    <span 
                                                        v-for="recipe in item.recipes" 
                                                        :key="recipe.id"
                                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-gray-200 text-gray-700"
                                                    >
                                                        {{ recipe.product.name }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Información para productos simples -->
                                            <div class="mt-2" v-else-if="item.product_type === 'simple'">
                                                <p class="text-xs text-gray-500">
                                                    Categoría: {{ item.category }}
                                                </p>
                                            </div>

                                            <div class="flex justify-between items-center mt-4">
                                                <span class="text-2xl font-bold text-green-600">
                                                    ${{ formatPrice(item.price) }}
                                                </span>
                                                <button
                                                    v-if="item.is_in_stock"
                                                    @click.stop="addToCart(item)"
                                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md text-sm font-medium transition-colors"
                                                >
                                                    Agregar
                                                </button>
                                                <span v-else class="text-sm text-red-500 font-medium">
                                                    Agotado
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mensaje si no hay productos -->
                                <div v-if="(groupedProducts[activeTab]?.items || []).length === 0" class="text-center py-12">
                                    <div class="text-gray-500">
                                        <div class="text-4xl mb-4">📦</div>
                                        <div>No hay productos disponibles en esta categoría</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel del Carrito -->
                    <div class="lg:col-span-1">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900">Carrito de Compras</h3>
                                    <div class="flex items-center space-x-2">
                                        <!-- Indicador de carrito guardado -->
                                        <div v-if="cartItems.length > 0" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                            Guardado automáticamente
                                        </div>
                                        <button
                                            v-if="cartItems.length > 0"
                                            @click="clearCart"
                                            class="text-red-500 hover:text-red-700 text-sm font-medium"
                                        >
                                            Limpiar Carrito
                                        </button>
                                    </div>
                                </div>

                                <!-- Items del carrito -->
                                <div v-if="cartItems.length === 0" class="text-center py-8 text-gray-500">
                                    <div class="text-3xl mb-2">🛒</div>
                                    <div class="font-medium">Carrito vacío</div>
                                    <div class="text-sm">Agrega productos del catálogo</div>
                                    <div class="text-xs mt-2 text-blue-600">
                                        El carrito se guarda automáticamente
                                    </div>
                                </div>

                                <div v-else class="space-y-3 mb-6">
                                    <div
                                        v-for="(item, index) in cartItems"
                                        :key="index"
                                        class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
                                    >
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900">{{ item.name }}</h4>
                                            <p class="text-sm text-gray-600">${{ formatPrice(item.price) }} c/u</p>
                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                                {{ item.product_type === 'menu' ? 'Platillo' : 'Individual' }}
                                            </span>
                                        </div>
                                        
                                        <div class="flex items-center space-x-2">
                                            <button
                                                @click="decrementQuantity(index)"
                                                class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-gray-600 font-medium"
                                            >
                                                -
                                            </button>
                                            <span class="w-8 text-center font-medium">{{ item.quantity }}</span>
                                            <button
                                                @click="incrementQuantity(index)"
                                                :disabled="item.quantity >= item.available_quantity"
                                                class="w-8 h-8 rounded-full bg-blue-500 hover:bg-blue-600 disabled:bg-gray-300 flex items-center justify-center text-white font-medium"
                                            >
                                                +
                                            </button>
                                            <button
                                                @click="removeFromCart(index)"
                                                class="w-8 h-8 rounded-full bg-red-500 hover:bg-red-600 flex items-center justify-center text-white font-medium ml-2"
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Toggle de Venta Libre -->
                                <div class="border-t border-gray-200 pt-4 mb-4">
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            v-model="isFreeSale"
                                            type="checkbox"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                        >
                                        <span class="ml-2 text-sm font-medium text-gray-700">
                                            Venta Libre (sin productos)
                                        </span>
                                    </label>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Para ventas que no afectan el inventario
                                    </p>
                                </div>

                                <!-- Form de Venta Libre -->
                                <div v-if="isFreeSale" class="border border-blue-200 rounded-lg p-4 mb-4 bg-blue-50">
                                    <h4 class="text-sm font-semibold text-blue-900 mb-3">
                                        Datos de Venta Libre
                                    </h4>

                                    <div class="space-y-3">
                                        <!-- Descripción -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Descripción *
                                            </label>
                                            <textarea
                                                v-model="freeSaleDescription"
                                                rows="3"
                                                maxlength="500"
                                                placeholder="Ej: Servicio de catering para evento corporativo"
                                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm"
                                            ></textarea>
                                            <div class="flex justify-between items-center mt-1">
                                                <span class="text-xs text-gray-500">
                                                    Mínimo 3 caracteres
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    {{ freeSaleDescription.length }}/500
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Monto -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Monto Total *
                                            </label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                                                <input
                                                    v-model="freeSaleTotal"
                                                    type="number"
                                                    step="0.01"
                                                    min="0.01"
                                                    placeholder="0.00"
                                                    class="w-full pl-7 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                                >
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Este monto no afectará el inventario
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Totales -->
                                <div v-if="cartItems.length > 0 && !isFreeSale" class="border-t border-gray-200 pt-4">
                                    <div class="space-y-3">
                                        <div class="flex justify-between text-sm">
                                            <span class="font-medium">Subtotal:</span>
                                            <span>${{ formatPrice(subtotal) }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm items-center">
                                            <span class="font-medium">Descuento:</span>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs">$</span>
                                                <input
                                                    v-model="discount"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    :max="subtotal"
                                                    class="w-20 text-right text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded"
                                                />
                                            </div>
                                        </div>
                                        <div class="flex justify-between text-sm items-center">
                                            <span class="font-medium">Impuesto:</span>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs">$</span>
                                                <input
                                                    v-model="tax"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    class="w-20 text-right text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded"
                                                />
                                            </div>
                                        </div>
                                        <div class="flex justify-between text-lg font-bold border-t pt-3">
                                            <span>Total:</span>
                                            <span class="text-green-600">${{ formatPrice(total) }}</span>
                                        </div>
                                    </div>

                                    <!-- Resumen de Venta Libre -->
                                    <div v-if="isFreeSale" class="border-t border-gray-200 pt-4">
                                        <div class="flex justify-between text-lg font-bold">
                                            <span>Total (Venta Libre):</span>
                                            <span class="text-green-600">${{ formatPrice(parseFloat(freeSaleTotal || 0)) }}</span>
                                        </div>
                                        <p class="text-xs text-blue-600 mt-2">
                                            Esta venta no afectará el inventario
                                        </p>
                                    </div>

                                    <!-- Método de pago (siempre visible si hay carrito o es venta libre) -->
                                    <div v-if="cartItems.length > 0 || isFreeSale" class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Método de Pago
                                        </label>
                                        <select
                                            v-model="paymentMethod"
                                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        >
                                            <option value="efectivo">Efectivo</option>
                                            <option value="tarjeta">Tarjeta</option>
                                            <option value="transferencia">Transferencia</option>
                                            <option value="mixto">Mixto</option>
                                        </select>
                                    </div>

                                    <!-- Botón de procesar venta -->
                                    <button
                                        v-if="cartItems.length > 0 || isFreeSale"
                                        @click="processSale"
                                        :disabled="processing || (!isFreeSale && cartItems.length === 0) || (isFreeSale && (!freeSaleDescription || !freeSaleTotal || parseFloat(freeSaleTotal) <= 0))"
                                        class="w-full mt-4 bg-green-500 hover:bg-green-600 disabled:bg-gray-400 text-white font-bold py-3 px-4 rounded-lg transition-colors"
                                    >
                                        <span v-if="processing" class="flex items-center justify-center">
                                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Procesando...
                                        </span>
                                        <span v-else-if="isFreeSale">
                                            Procesar Venta Libre (${{ formatPrice(parseFloat(freeSaleTotal || 0)) }})
                                        </span>
                                        <span v-else>Procesar Venta (${{ formatPrice(total) }})</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>