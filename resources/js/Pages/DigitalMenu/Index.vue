<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import DigitalMenuLayout from '@/Layouts/DigitalMenuLayout.vue';
import ProductCard from './Components/ProductCard.vue';
import CartSlideOver from './Components/CartSlideOver.vue';

const props = defineProps({
    menuItems: Array,
    simpleProducts: Array,
    categories: Array,
    settings: Object,
});

const searchQuery = ref('');
const selectedCategory = ref(null);
const cart = ref([]);
const showCart = ref(false);
const showVariantModal = ref(false);
const selectedProduct = ref(null);
const showCheckout = ref(false);

// All products combined
const allProducts = computed(() => {
    const menuProducts = props.menuItems.map(item => ({ ...item, type: 'menu' }));
    const simpleProductsList = props.simpleProducts.map(item => ({ ...item, type: 'simple' }));
    return [...menuProducts, ...simpleProductsList];
});

// Filtered products
const filteredProducts = computed(() => {
    let products = allProducts.value;

    // Filter by category
    if (selectedCategory.value) {
        products = products.filter(p => p.category_id === selectedCategory.value);
    }

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        products = products.filter(p =>
            p.name.toLowerCase().includes(query) ||
            (p.description && p.description.toLowerCase().includes(query))
        );
    }

    return products;
});

// Cart total
const cartTotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const cartItemsCount = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.quantity, 0);
});

// Add to cart
const addToCart = (item) => {
    const existingItem = cart.value.find(cartItem =>
        cartItem.type === item.type && cartItem.id === item.id && cartItem.variant_id === item.variant_id
    );

    if (existingItem) {
        existingItem.quantity += item.quantity;
    } else {
        cart.value.push({ ...item });
    }
};

// Show variant modal
const selectVariant = (product) => {
    selectedProduct.value = product;
    showVariantModal.value = true;
};

// Add variant to cart
const addVariantToCart = (variant) => {
    addToCart({
        type: 'variant',
        id: variant.id,
        name: `${selectedProduct.value.name} - ${variant.variant_name}`,
        price: variant.price,
        quantity: 1,
        variant_id: variant.id,
        image_path: selectedProduct.value.image_path,
    });
    showVariantModal.value = false;
};

// Remove from cart
const removeFromCart = (index) => {
    cart.value.splice(index, 1);
};

// Update quantity
const updateQuantity = (index, newQuantity) => {
    if (newQuantity <= 0) {
        removeFromCart(index);
    } else {
        cart.value[index].quantity = newQuantity;
    }
};

// Clear cart
const clearCart = () => {
    cart.value = [];
};

// Proceed to checkout
const proceedToCheckout = () => {
    showCart.value = false;
    showCheckout.value = true;
};
</script>

<template>
    <DigitalMenuLayout>
        <!-- Welcome Message -->
        <div v-if="settings.welcome_message" class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <p class="text-sm text-blue-800 dark:text-blue-200 text-center">
                {{ settings.welcome_message }}
            </p>
        </div>

        <!-- Closed Message -->
        <div v-if="!settings.is_open" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
            <p class="text-sm text-red-800 dark:text-red-200 text-center font-medium">
                {{ settings.closed_message }}
            </p>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar productos..."
                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                >
            </div>
        </div>

        <!-- Category Filters -->
        <div class="mb-6 flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
            <button
                @click="selectedCategory = null"
                class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors duration-200"
                :class="selectedCategory === null
                    ? 'bg-orange-600 text-white shadow-sm'
                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:border-orange-500 dark:hover:border-orange-500'
                "
            >
                Todos
            </button>
            <button
                v-for="category in categories"
                :key="category.id"
                @click="selectedCategory = category.id"
                class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors duration-200"
                :class="selectedCategory === category.id
                    ? 'bg-orange-600 text-white shadow-sm'
                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:border-orange-500 dark:hover:border-orange-500'
                "
            >
                {{ category.name }}
            </button>
        </div>

        <!-- Products Grid -->
        <div v-if="filteredProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
            <ProductCard
                v-for="product in filteredProducts"
                :key="`${product.type}-${product.id}`"
                :product="product"
                :type="product.type"
                @add-to-cart="addToCart"
                @select-variant="selectVariant"
            />
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
            <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-gray-600 dark:text-gray-400 text-lg mb-2">No se encontraron productos</p>
            <p class="text-gray-500 dark:text-gray-500 text-sm">Intenta con otra busqueda o categoria</p>
        </div>

        <!-- Cart Floating Button -->
        <button
            v-if="cartItemsCount > 0"
            @click="showCart = true"
            class="fixed bottom-6 right-6 z-50 bg-orange-600 hover:bg-orange-700 text-white rounded-full shadow-lg hover:shadow-xl px-6 py-4 flex items-center space-x-3 transition-all duration-200 active:scale-95"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <div class="text-left">
                <div class="text-xs opacity-90">{{ cartItemsCount }} item{{ cartItemsCount > 1 ? 's' : '' }}</div>
                <div class="font-bold">${{ cartTotal.toFixed(2) }}</div>
            </div>
        </button>

        <!-- Variant Selection Modal -->
        <div v-if="showVariantModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <!-- Backdrop -->
                <div
                    @click="showVariantModal = false"
                    class="fixed inset-0 bg-black/50 dark:bg-black/70 transition-opacity"
                ></div>

                <!-- Modal -->
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Selecciona una opcion
                        </h3>
                        <button
                            @click="showVariantModal = false"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-2">
                        <button
                            v-for="variant in selectedProduct?.variants"
                            :key="variant.id"
                            @click="addVariantToCart(variant)"
                            :disabled="variant.available_quantity <= 0"
                            class="w-full flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-orange-500 dark:hover:border-orange-500 transition-colors duration-200"
                            :class="variant.available_quantity <= 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                        >
                            <div class="text-left flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">{{ variant.variant_name }}</p>
                                <p v-if="variant.description" class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ variant.description }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                    {{ variant.available_quantity }} disponibles
                                </p>
                            </div>
                            <p class="text-lg font-bold text-orange-600 dark:text-orange-400 ml-4">
                                ${{ parseFloat(variant.price).toFixed(2) }}
                            </p>
                        </button>
                    </div>
                </div>
            </div>
        </div>

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
