import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCartStore = defineStore('cart', () => {
    // State
    const items = ref([]);
    const discount = ref(0);
    const taxRate = ref(0.16); // 16% IVA México

    // Getters
    const subtotal = computed(() => {
        return items.value.reduce((sum, item) => {
            return sum + (item.price * item.quantity);
        }, 0);
    });

    const tax = computed(() => {
        return (subtotal.value - discount.value) * taxRate.value;
    });

    const total = computed(() => {
        return subtotal.value - discount.value + tax.value;
    });

    const itemCount = computed(() => {
        return items.value.reduce((sum, item) => sum + item.quantity, 0);
    });

    const isEmpty = computed(() => {
        return items.value.length === 0;
    });

    // Actions
    function addItem(product) {
        const existingItem = items.value.find(item => {
            if (product.product_type === 'menu') {
                return item.product_type === 'menu' && item.id === product.id;
            } else {
                return item.product_type === 'simple' && item.id === product.id;
            }
        });

        if (existingItem) {
            if (existingItem.quantity < existingItem.available_quantity) {
                existingItem.quantity++;
            } else {
                throw new Error(`Stock insuficiente para ${product.name}`);
            }
        } else {
            items.value.push({
                id: product.id,
                product_type: product.product_type,
                name: product.name,
                price: product.price || product.sale_price,
                quantity: 1,
                available_quantity: product.available_quantity || 0
            });
        }
    }

    function removeItem(index) {
        items.value.splice(index, 1);
    }

    function updateQuantity(index, quantity) {
        if (quantity <= 0) {
            removeItem(index);
        } else {
            const item = items.value[index];
            if (quantity > item.available_quantity) {
                throw new Error(`Solo hay ${item.available_quantity} unidades disponibles`);
            }
            item.quantity = quantity;
        }
    }

    function setDiscount(amount) {
        if (amount < 0) {
            throw new Error('El descuento no puede ser negativo');
        }
        if (amount > subtotal.value) {
            throw new Error('El descuento no puede ser mayor que el subtotal');
        }
        discount.value = amount;
    }

    function clearCart() {
        items.value = [];
        discount.value = 0;
    }

    function validateStock() {
        const errors = [];

        items.value.forEach((item, index) => {
            if (item.quantity > item.available_quantity) {
                errors.push({
                    index,
                    item: item.name,
                    requested: item.quantity,
                    available: item.available_quantity,
                    message: `${item.name} solo tiene ${item.available_quantity} unidades disponibles`
                });
            }
        });

        return errors;
    }

    function getSaleData() {
        return {
            items: items.value.map(item => ({
                id: item.id,
                product_type: item.product_type,
                quantity: item.quantity,
                unit_price: item.price
            })),
            subtotal: parseFloat(subtotal.value.toFixed(2)),
            discount: parseFloat(discount.value.toFixed(2)),
            tax: parseFloat(tax.value.toFixed(2)),
            total: parseFloat(total.value.toFixed(2))
        };
    }

    return {
        // State
        items,
        discount,
        taxRate,
        // Getters
        subtotal,
        tax,
        total,
        itemCount,
        isEmpty,
        // Actions
        addItem,
        removeItem,
        updateQuantity,
        setDiscount,
        clearCart,
        validateStock,
        getSaleData
    };
});
