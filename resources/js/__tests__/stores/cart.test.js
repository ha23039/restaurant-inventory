import { describe, it, expect, beforeEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useCartStore } from '@/stores/cart';

describe('Cart Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it('initializes with empty cart', () => {
        const cart = useCartStore();
        expect(cart.items).toHaveLength(0);
        expect(cart.itemCount).toBe(0);
        expect(cart.isEmpty).toBe(true);
        expect(cart.total).toBe(0);
    });

    it('adds item to cart', () => {
        const cart = useCartStore();
        cart.addItem({
            id: 1,
            product_type: 'menu',
            name: 'Tacos',
            price: 10,
            available_quantity: 5
        });

        expect(cart.items).toHaveLength(1);
        expect(cart.itemCount).toBe(1);
        expect(cart.isEmpty).toBe(false);
    });

    it('increments quantity when adding same item', () => {
        const cart = useCartStore();
        const product = {
            id: 1,
            product_type: 'menu',
            name: 'Tacos',
            price: 10,
            available_quantity: 5
        };

        cart.addItem(product);
        cart.addItem(product);

        expect(cart.items).toHaveLength(1);
        expect(cart.items[0].quantity).toBe(2);
        expect(cart.itemCount).toBe(2);
    });

    it('calculates subtotal correctly', () => {
        const cart = useCartStore();

        cart.addItem({
            id: 1,
            product_type: 'menu',
            name: 'Tacos',
            price: 10,
            available_quantity: 5
        });

        cart.addItem({
            id: 2,
            product_type: 'menu',
            name: 'Burrito',
            price: 15,
            available_quantity: 3
        });

        expect(cart.subtotal).toBe(25);
    });

    it('calculates tax correctly', () => {
        const cart = useCartStore();

        cart.addItem({
            id: 1,
            product_type: 'menu',
            name: 'Tacos',
            price: 100,
            available_quantity: 5
        });

        // Subtotal: 100, Tax (16%): 16
        expect(cart.tax).toBe(16);
    });

    it('calculates total correctly with discount', () => {
        const cart = useCartStore();

        cart.addItem({
            id: 1,
            product_type: 'menu',
            name: 'Tacos',
            price: 100,
            available_quantity: 5
        });

        cart.setDiscount(10);

        // Subtotal: 100, Discount: 10, Taxable: 90, Tax (16%): 14.4, Total: 104.4
        expect(cart.total).toBe(104.4);
    });

    it('removes item from cart', () => {
        const cart = useCartStore();

        cart.addItem({
            id: 1,
            product_type: 'menu',
            name: 'Tacos',
            price: 10,
            available_quantity: 5
        });

        cart.removeItem(0);

        expect(cart.items).toHaveLength(0);
        expect(cart.isEmpty).toBe(true);
    });

    it('updates item quantity', () => {
        const cart = useCartStore();

        cart.addItem({
            id: 1,
            product_type: 'menu',
            name: 'Tacos',
            price: 10,
            available_quantity: 5
        });

        cart.updateQuantity(0, 3);

        expect(cart.items[0].quantity).toBe(3);
        expect(cart.itemCount).toBe(3);
    });

    it('validates stock correctly', () => {
        const cart = useCartStore();

        cart.addItem({
            id: 1,
            product_type: 'menu',
            name: 'Tacos',
            price: 10,
            available_quantity: 2
        });

        // Intentar actualizar a cantidad mayor debería lanzar error
        expect(() => cart.updateQuantity(0, 3)).toThrow('Solo hay 2 unidades disponibles');
    });

    it('clears cart', () => {
        const cart = useCartStore();

        cart.addItem({
            id: 1,
            product_type: 'menu',
            name: 'Tacos',
            price: 10,
            available_quantity: 5
        });

        cart.setDiscount(5);
        cart.clearCart();

        expect(cart.items).toHaveLength(0);
        expect(cart.discount).toBe(0);
        expect(cart.isEmpty).toBe(true);
    });

    it('generates sale data correctly', () => {
        const cart = useCartStore();

        cart.addItem({
            id: 1,
            product_type: 'menu',
            name: 'Tacos',
            price: 10,
            available_quantity: 5
        });

        cart.setDiscount(2);

        const saleData = cart.getSaleData();

        expect(saleData.items).toHaveLength(1);
        expect(saleData.subtotal).toBe(10);
        expect(saleData.discount).toBe(2);
        expect(saleData.tax).toBe(1.28); // (10 - 2) * 0.16
        expect(saleData.total).toBe(9.28);
    });
});
