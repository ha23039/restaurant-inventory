<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DigitalMenuLayout from '@/Layouts/DigitalMenuLayout.vue';
import ProductCard from './Components/ProductCard.vue';
import CartSlideOver from './Components/CartSlideOver.vue';
import VariantSlideOver from './Components/VariantSlideOver.vue';
import CheckoutSlideOver from './Components/CheckoutSlideOver.vue';

const props = defineProps({
    menuItems: Array,
    simpleProducts: Array,
    categories: Array,
    settings: Object,
});

const searchQuery = ref('');
const activeTab = ref('menu');
const cart = ref([]);
const showCart = ref(false);
const showVariantModal = ref(false);
const showCheckout = ref(false);
const selectedProduct = ref(null);

// ===== PERSISTENCIA DEL CARRITO =====
const CART_STORAGE_KEY = 'digital_menu_cart';
const CART_EXPIRY_HOURS = 24;

const saveCartToStorage = () => {
    const cartData = { items: cart.value, timestamp: Date.now() };
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cartData));
};

const loadCartFromStorage = () => {
    try {
        const savedCart = localStorage.getItem(CART_STORAGE_KEY);
        if (savedCart) {
            const cartData = JSON.parse(savedCart);
            const isExpired = (Date.now() - cartData.timestamp) > CART_EXPIRY_HOURS * 60 * 60 * 1000;
            if (!isExpired && cartData.items?.length > 0) {
                cart.value = cartData.items;
            } else {
                clearCartStorage();
            }
        }
    } catch (error) {
        clearCartStorage();
    }
};

const clearCartStorage = () => {
    localStorage.removeItem(CART_STORAGE_KEY);
};

watch(cart, () => {
    cart.value.length > 0 ? saveCartToStorage() : clearCartStorage();
}, { deep: true });

onMounted(() => {
    loadCartFromStorage();
});

// ===== CATEGORIZACIÓN DINÁMICA (como POS) =====
const normalizeCategoryKey = (category) => {
    if (!category) return 'otros';
    const normalized = category.toLowerCase().trim();
    if (normalized.includes('bebida')) return 'bebidas';
    if (normalized.includes('extra')) return 'extras';
    if (normalized.includes('postre')) return 'postres';
    if (normalized.includes('condimento') || normalized.includes('salsa')) return 'condimentos';
    return normalized;
};

const getCategoryTitle = (key) => {
    const titles = {
        'menu': 'Platillos',
        'bebidas': 'Bebidas',
        'extras': 'Extras',
        'postres': 'Postres',
        'condimentos': 'Condimentos',
        'otros': 'Otros'
    };
    return titles[key] || key.charAt(0).toUpperCase() + key.slice(1);
};

// Agrupar productos por categoría (como el POS)
const groupedProducts = computed(() => {
    const groups = {
        menu: { title: 'Platillos', items: [], count: 0 }
    };

    // Menu items van al grupo 'menu'
    props.menuItems.forEach(item => {
        groups.menu.items.push({ ...item, product_type: 'menu', price: item.price });
        groups.menu.count++;
    });

    // Simple products se agrupan por su categoría
    props.simpleProducts.forEach(product => {
        const categoryKey = normalizeCategoryKey(product.category_name);
        
        if (!groups[categoryKey]) {
            groups[categoryKey] = {
                title: getCategoryTitle(categoryKey),
                items: [],
                count: 0
            };
        }
        
        groups[categoryKey].items.push({
            ...product,
            product_type: 'simple',
            price: product.sale_price
        });
        groups[categoryKey].count++;
    });

    // Filtrar grupos vacíos y ordenar
    const sortedGroups = {};
    
    // Menu primero si tiene items
    if (groups.menu.count > 0) {
        sortedGroups.menu = groups.menu;
    }
    
    // Resto alfabéticamente
    Object.keys(groups)
        .filter(key => key !== 'menu' && groups[key].count > 0)
        .sort()
        .forEach(key => {
            sortedGroups[key] = groups[key];
        });

    return sortedGroups;
});

// Available tabs
const availableTabs = computed(() => Object.keys(groupedProducts.value));

// Current products based on active tab and search
const currentProducts = computed(() => {
    let products = groupedProducts.value[activeTab.value]?.items || [];
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        // Search across ALL categories
        products = Object.values(groupedProducts.value)
            .flatMap(g => g.items)
            .filter(p => 
                p.name.toLowerCase().includes(query) ||
                (p.description && p.description.toLowerCase().includes(query))
            );
    }
    
    return products;
});

// ===== CARRITO =====
const cartTotal = computed(() => cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0));
const cartItemsCount = computed(() => cart.value.reduce((sum, item) => sum + item.quantity, 0));

const addToCart = (item) => {
    const existingItem = cart.value.find(cartItem =>
        cartItem.product_type === item.product_type && 
        cartItem.id === item.id && 
        cartItem.variant_id === item.variant_id
    );

    if (existingItem) {
        existingItem.quantity += item.quantity || 1;
    } else {
        cart.value.push({ ...item, quantity: item.quantity || 1 });
    }
};

const selectVariant = (product) => {
    selectedProduct.value = product;
    showVariantModal.value = true;
};

const addVariantToCart = (variantItem) => {
    addToCart(variantItem);
    showVariantModal.value = false;
};

const removeFromCart = (index) => cart.value.splice(index, 1);

const updateQuantity = (index, newQuantity) => {
    if (newQuantity <= 0) {
        removeFromCart(index);
    } else {
        cart.value[index].quantity = newQuantity;
    }
};

const clearCart = () => {
    cart.value = [];
    clearCartStorage();
};

const proceedToCheckout = () => {
    showCart.value = false;
    showCheckout.value = true;
};

const handleOrderCreated = (sale) => {
    // Mostrar mensaje de éxito
    alert(`¡Pedido #${sale.sale_number} creado exitosamente!\n\nEstado: ${sale.status}\nTotal: $${sale.total.toFixed(2)}`);

    // Limpiar carrito
    clearCart();
};
</script>

<template>
    <Head title="Menu Digital" />
    
    <DigitalMenuLayout>
        <!-- Hero Section with Search -->
        <div class="mb-8">
            <!-- Welcome Banner -->
            <div v-if="settings.welcome_message" class="mb-6 p-4 bg-gradient-to-r from-orange-500 to-red-500 rounded-2xl shadow-lg">
                <p class="text-white text-center font-medium">
                    {{ settings.welcome_message }}
                </p>
            </div>

            <!-- Closed Warning -->
            <div v-if="!settings.is_open" class="mb-6 p-4 bg-red-500/10 dark:bg-red-500/20 rounded-xl border border-red-200 dark:border-red-800">
                <p class="text-red-600 dark:text-red-400 text-center font-medium text-sm">
                    ⏰ {{ settings.closed_message || 'Estamos cerrados en este momento' }}
                </p>
            </div>

            <!-- Search Bar -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="¿Qué se te antoja hoy?"
                    class="w-full pl-12 pr-4 py-4 text-base bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:focus:ring-orange-400 dark:focus:border-orange-400 transition-all dark:text-white dark:placeholder-gray-400"
                />
            </div>
        </div>

        <!-- Category Tabs (like POS) -->
        <div class="mb-6 -mx-4 px-4">
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                <button
                    v-for="(group, key) in groupedProducts"
                    :key="key"
                    @click="activeTab = key; searchQuery = ''"
                    class="flex-shrink-0 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200"
                    :class="activeTab === key && !searchQuery
                        ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/30'
                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:border-orange-300 dark:hover:border-orange-600'
                    "
                >
                    {{ group.title }} ({{ group.count }})
                </button>
            </div>
        </div>

        <!-- Search Results Header -->
        <div v-if="searchQuery" class="mb-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Resultados para "<span class="font-semibold text-gray-900 dark:text-white">{{ searchQuery }}</span>" 
                ({{ currentProducts.length }} encontrados)
            </p>
        </div>

        <!-- Products Grid -->
        <div v-if="currentProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
            <ProductCard
                v-for="product in currentProducts"
                :key="`${product.product_type}-${product.id}`"
                :product="product"
                :type="product.product_type"
                @add-to-cart="addToCart"
                @select-variant="selectVariant"
            />
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16">
            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No encontramos productos</h3>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Intenta con otra búsqueda o categoría</p>
        </div>

        <!-- Floating Cart Button -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="translate-y-full opacity-0 scale-95"
            enter-to-class="translate-y-0 opacity-100 scale-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100 scale-100"
            leave-to-class="translate-y-full opacity-0 scale-95"
        >
            <button
                v-if="cartItemsCount > 0"
                @click="showCart = true"
                class="fixed bottom-6 left-1/2 -translate-x-1/2 sm:left-auto sm:right-6 sm:translate-x-0 z-50 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-2xl shadow-2xl shadow-orange-500/40 px-6 py-4 flex items-center gap-4 transition-all duration-200 active:scale-95"
            >
                <div class="relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="absolute -top-2 -right-2 bg-white text-orange-600 text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                        {{ cartItemsCount }}
                    </span>
                </div>
                <div class="text-left">
                    <div class="text-xs opacity-90">Ver carrito</div>
                    <div class="text-lg font-bold">${{ cartTotal.toFixed(2) }}</div>
                </div>
                <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </Transition>

        <!-- Variant SlideOver -->
        <VariantSlideOver
            :show="showVariantModal"
            :menu-item="selectedProduct?.product_type === 'menu' ? selectedProduct : null"
            :product="selectedProduct"
            @close="showVariantModal = false"
            @select="addVariantToCart"
        />

        <!-- Cart SlideOver -->
        <CartSlideOver
            :show="showCart"
            :cart="cart"
            :settings="settings"
            @close="showCart = false"
            @update-quantity="updateQuantity"
            @remove-item="removeFromCart"
            @clear-cart="clearCart"
            @proceed-checkout="proceedToCheckout"
        />

        <!-- Checkout SlideOver -->
        <CheckoutSlideOver
            :show="showCheckout"
            :cart="cart"
            :cart-total="cartTotal"
            :settings="settings"
            @close="showCheckout = false"
            @order-created="handleOrderCreated"
        />
    </DigitalMenuLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
