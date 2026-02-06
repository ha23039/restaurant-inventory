<script setup>
// 1. IMPORTS CORRECTOS AL INICIO
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FreeSaleSlideOver from '@/Components/FreeSaleSlideOver.vue';
import ProductVariantSlideOver from '@/Components/ProductVariantSlideOver.vue';
import ComboSelectionSlideOver from '@/Components/ComboSelectionSlideOver.vue';
import CartSlideOver from '@/Components/CartSlideOver.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { useToast } from 'vue-toastification';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

// 2. PROPS DEL COMPONENTE
const props = defineProps({
    menu_items: Object,
    simple_products: Object,
    combos: Array,
    pending_sales: Array,
    available_tables: Array,
    payment_methods: Array
});

// Computed para métodos de pago activos (con fallback)
const activePaymentMethods = computed(() => {
    if (props.payment_methods && props.payment_methods.length > 0) {
        return props.payment_methods;
    }
    // Fallback si no vienen del servidor
    return [
        { name: 'efectivo', label: 'Efectivo', icon: 'cash', requires_amount_input: true },
        { name: 'tarjeta', label: 'Tarjeta', icon: 'credit-card', requires_amount_input: false },
        { name: 'transferencia', label: 'Transferencia', icon: 'bank', requires_amount_input: false },
    ];
});

// Computed para saber si el método actual requiere input de monto
const currentMethodRequiresAmountInput = computed(() => {
    const method = activePaymentMethods.value.find(m => m.name === paymentMethod.value);
    return method?.requires_amount_input ?? (paymentMethod.value === 'efectivo' || paymentMethod.value === 'mixto');
});

// 3. ESTADO PRINCIPAL DEL POS
const searchTerm = ref('');
const searchInputRef = ref(null);
const selectedCategory = ref('');
const activeTab = ref('menu');
const cartItems = ref([]);
const discount = ref(0);
const tax = ref(0);
const paymentMethod = ref('efectivo');
const cashReceived = ref(0);
const cashReceivedInputRef = ref(null);
const customerName = ref('');
const orderNotes = ref('');
const showCustomerInfo = ref(false);
const processing = ref(false);
const toast = useToast();
const { confirm } = useConfirmDialog();

// VENTA LIBRE
const showFreeSaleSlideOver = ref(false);
const isFreeSale = ref(false);
const freeSaleDescription = ref('');
const freeSaleTotal = ref('');

// VARIANTES DE PRODUCTOS
const showVariantSlideOver = ref(false);
const selectedMenuItemForVariants = ref(null);

// COMBOS
const showComboSlideOver = ref(false);
const selectedCombo = ref(null);

// ORDEN ACTIVA (Venta pendiente)
const selectedExistingSale = ref(null);
const showPendingSales = ref(false);

// SELECCIÓN DE MESA
const selectedTable = ref(null);

// CARRITO MÓVIL
const showMobileCart = ref(false);

// 4. FUNCIONES DE PERSISTENCIA DEL CARRITO
const saveCartToStorage = () => {
    const cartData = {
        items: cartItems.value,
        discount: discount.value,
        tax: tax.value,
        paymentMethod: paymentMethod.value,
        cashReceived: cashReceived.value,
        selectedTable: selectedTable.value,
        customerName: customerName.value,
        orderNotes: orderNotes.value,
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
                cashReceived.value = cartData.cashReceived || 0;
                selectedTable.value = cartData.selectedTable || null;
                customerName.value = cartData.customerName || '';
                orderNotes.value = cartData.orderNotes || '';

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

// Normalizar categorías para agrupar variaciones
const normalizeCategoryKey = (category) => {
    if (!category) return 'otros';

    const normalized = category.toLowerCase().trim();

    // Mapear variaciones comunes
    if (normalized.includes('bebida')) return 'bebidas';
    if (normalized.includes('extra')) return 'extras';
    if (normalized.includes('postre')) return 'postres';
    if (normalized.includes('entrada')) return 'entradas';
    if (normalized.includes('plato fuerte') || normalized.includes('platillo')) return 'platos-fuertes';
    if (normalized.includes('condimento') || normalized.includes('salsa')) return 'condimentos';

    return normalized;
};

const getCategoryTitle = (key) => {
    const titles = {
        'bebidas': 'Bebidas',
        'extras': 'Extras',
        'postres': 'Postres',
        'entradas': 'Entradas',
        'platos-fuertes': 'Platos Fuertes',
        'condimentos': 'Condimentos',
        'combos': 'Combos',
        'otros': 'Otros'
    };

    return titles[key] || key.charAt(0).toUpperCase() + key.slice(1);
};

const availableCategories = computed(() => {
    const categories = new Set();

    // Agregar categorías normalizadas de productos simples
    (props.simple_products.data || []).forEach(item => {
        if (item.category) {
            const normalized = normalizeCategoryKey(item.category);
            categories.add(normalized);
        }
    });

    // Agregar categoría de combos si hay combos disponibles
    const hasCombos = (props.combos || []).length > 0;

    return ['menu', ...(hasCombos ? ['combos'] : []), ...Array.from(categories).sort()];
});

const groupedProducts = computed(() => {
    const menuProducts = (props.menu_items.data || []).map(item => ({
        ...item,
        product_type: 'menu'
    }));

    const simpleProducts = (props.simple_products.data || []).map(item => ({
        ...item,
        product_type: 'simple',
        price: item.sale_price, // Mapear sale_price a price para uniformidad
        is_in_stock: item.is_in_stock !== undefined ? item.is_in_stock : (item.available_quantity > 0)
    }));

    // Mapear combos
    const comboProducts = (props.combos || []).map(combo => ({
        ...combo,
        product_type: 'combo',
        price: combo.base_price,
        is_in_stock: true, // Los combos siempre están disponibles si están activos
        available_quantity: 999
    }));

    // Filtrar por búsqueda y categoría
    const allProducts = [...menuProducts, ...simpleProducts].filter(product => {
        const matchesSearch = !searchTerm.value ||
            product.name.toLowerCase().includes(searchTerm.value.toLowerCase());

        // Si hay búsqueda activa, ignorar filtro de categoría del dropdown
        // para buscar en TODAS las categorías
        let matchesCategory = true;
        if (!searchTerm.value && selectedCategory.value) {
            if (selectedCategory.value === 'menu') {
                matchesCategory = product.product_type === 'menu';
            } else if (selectedCategory.value === 'combos') {
                matchesCategory = false; // Los combos se filtran aparte
            } else {
                // Normalizar la categoría del producto para comparar
                const normalizedProductCategory = normalizeCategoryKey(product.category);
                matchesCategory = normalizedProductCategory === selectedCategory.value;
            }
        }

        return matchesSearch && matchesCategory;
    });

    // Filtrar combos por búsqueda
    const filteredCombos = comboProducts.filter(combo => {
        return !searchTerm.value ||
            combo.name.toLowerCase().includes(searchTerm.value.toLowerCase());
    });

    // Agrupar por categorías - dinámicamente con normalización
    const groups = {
        menu: { title: 'Platillos del Menu', items: [] },
    };

    // Agregar grupo de combos si hay combos
    if (filteredCombos.length > 0) {
        groups.combos = { title: 'Combos', items: filteredCombos };
    }

    allProducts.forEach(product => {
        let categoryKey;

        if (product.product_type === 'menu') {
            categoryKey = 'menu';
        } else {
            // Normalizar la categoría del producto simple
            categoryKey = normalizeCategoryKey(product.category);
        }

        // Crear grupo dinámicamente si no existe
        if (!groups[categoryKey]) {
            groups[categoryKey] = {
                title: getCategoryTitle(categoryKey),
                items: []
            };
        }

        groups[categoryKey].items.push(product);
    });

    // Ordenar grupos: menú primero, combos segundo, luego alfabéticamente
    const sortedGroups = {};
    if (groups.menu && groups.menu.items.length > 0) {
        sortedGroups.menu = groups.menu;
    }
    if (groups.combos && groups.combos.items.length > 0) {
        sortedGroups.combos = groups.combos;
    }

    Object.keys(groups)
        .filter(key => key !== 'menu' && key !== 'combos')
        .sort()
        .forEach(key => {
            if (groups[key].items.length > 0) {
                sortedGroups[key] = groups[key];
            }
        });

    return sortedGroups;
});

const subtotal = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const total = computed(() => {
    return Math.max(0, subtotal.value - parseFloat(discount.value || 0) + parseFloat(tax.value || 0));
});

// Verifica si hay algo que vender (carrito, venta libre, u orden activa)
const hasItemsToSell = computed(() => {
    return cartItems.value.length > 0 || isFreeSale.value || selectedExistingSale.value !== null;
});

// Total final a cobrar (considera orden activa + nuevos items)
const finalTotal = computed(() => {
    if (isFreeSale.value) {
        return parseFloat(freeSaleTotal.value || 0);
    } else if (selectedExistingSale.value && cartItems.value.length > 0) {
        // Si hay orden activa con nuevos items, sumar ambos totales
        return parseFloat(selectedExistingSale.value.total) + total.value;
    } else if (selectedExistingSale.value) {
        // Si solo hay orden activa sin nuevos items
        return parseFloat(selectedExistingSale.value.total);
    } else {
        return total.value;
    }
});

// Calculadora de cambio
const changeAmount = computed(() => {
    const received = parseFloat(cashReceived.value || 0);
    return Math.max(0, received - finalTotal.value);
});

// Desglose de billetes y monedas mexicanas
const changeBillBreakdown = computed(() => {
    let remaining = changeAmount.value;
    const denominations = [1000, 500, 200, 100, 50, 20, 10, 5, 2, 1, 0.50];
    const breakdown = [];

    for (const denom of denominations) {
        if (remaining >= denom) {
            const count = Math.floor(remaining / denom);
            breakdown.push({ value: denom, count });
            remaining = Math.round((remaining - (denom * count)) * 100) / 100;
        }
    }

    return breakdown;
});

// Helper: Obtener nombre de item de venta (soporta menu, simple, variant, simple_variant, free, combo)
const getSaleItemName = (item) => {
    if (item.product_type === 'variant' && item.menu_item_variant) {
        // Para variantes de platillo, mostrar nombre del platillo padre + nombre de variante
        const parentName = item.menu_item_variant.menu_item?.name || '';
        const variantName = item.menu_item_variant.variant_name;
        return parentName ? `${parentName} - ${variantName}` : variantName;
    } else if (item.product_type === 'simple_variant' && item.simple_product_variant) {
        // Para variantes de producto simple (bebidas), mostrar nombre del producto padre + variante
        const parentName = item.simple_product_variant.simple_product?.name || '';
        const variantName = item.simple_product_variant.variant_name;
        return parentName ? `${parentName} - ${variantName}` : variantName;
    } else if (item.product_type === 'combo' && item.combo) {
        return item.combo.name;
    } else if (item.menu_item?.name) {
        return item.menu_item.name;
    } else if (item.simple_product?.name) {
        return item.simple_product.name;
    } else if (item.free_sale_name) {
        return item.free_sale_name;
    }
    return 'Producto';
};

// 6. MÉTODOS DEL CARRITO CON PERSISTENCIA
const addToCart = (product) => {
    // Si es un combo, abrir el slideover de selección de combo
    if (product.product_type === 'combo') {
        selectCombo(product);
        return;
    }

    // Si el producto tiene variantes, abrir slideover de selección
    if (product.has_variants && product.variants && product.variants.length > 0) {
        selectedMenuItemForVariants.value = product;
        showVariantSlideOver.value = true;
        return;
    }

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

// Agregar variante al carrito desde el slideover
const addVariantToCart = (variant) => {
    if (variant.available_quantity <= 0) {
        showNotification('Esta variante no está disponible por falta de stock', 'warning');
        return;
    }

    const existingIndex = cartItems.value.findIndex(item =>
        item.id === variant.id && item.product_type === 'variant'
    );

    if (existingIndex >= 0) {
        if (cartItems.value[existingIndex].quantity < variant.available_quantity) {
            cartItems.value[existingIndex].quantity++;
            showNotification(`${variant.name} agregado al carrito`, 'success');
        } else {
            showNotification(`Solo hay ${variant.available_quantity} ${variant.name} disponibles`, 'warning');
        }
    } else {
        cartItems.value.push({
            id: variant.id,
            name: variant.name,
            price: variant.price,
            quantity: 1,
            available_quantity: variant.available_quantity,
            product_type: 'variant'
        });
        showNotification(`${variant.name} agregado al carrito`, 'success');
    }

    // Guardar automáticamente
    saveCartToStorage();
};

// Handle multiple variants update from the new SlideOver
const handleUpdateVariants = ({ productId, productName, variants }) => {
    // Remove existing variants of this product from cart
    cartItems.value = cartItems.value.filter(item => {
        // Keep items that are not variants of this menu item
        return !(item.product_type === 'variant' && item.menu_item_id === productId);
    });

    // Add all selected variants with their quantities
    variants.forEach(variant => {
        if (variant.quantity > 0) {
            cartItems.value.push({
                id: variant.id,
                name: variant.name,
                price: variant.price,
                quantity: variant.quantity,
                available_quantity: variant.available_quantity,
                product_type: 'variant',
                variant_id: variant.variant_id,
                menu_item_id: variant.menu_item_id
            });
        }
    });

    if (variants.length > 0) {
        const totalItems = variants.reduce((sum, v) => sum + v.quantity, 0);
        showNotification(`${totalItems} ${totalItems === 1 ? 'item' : 'items'} de ${productName} actualizados`, 'success');
    }

    saveCartToStorage();
};

const addFreeSaleToCart = (freeSaleData) => {
    cartItems.value.push({
        id: `free-${Date.now()}`,
        name: freeSaleData.name,
        price: freeSaleData.price,
        quantity: 1,
        product_type: 'free',
        category: freeSaleData.category,
        available_quantity: 999 // Sin límite de stock
    });
    showNotification(`${freeSaleData.name} agregado al carrito`, 'success');
    saveCartToStorage();
};

// Seleccionar un combo para personalizar
const selectCombo = (combo) => {
    // Si el combo no tiene componentes de elección, agregarlo directamente
    const hasChoices = combo.components?.some(c => c.component_type === 'choice');
    if (!hasChoices) {
        addComboToCart({
            id: combo.id,
            name: combo.name,
            price: parseFloat(combo.base_price),
            quantity: 1,
            product_type: 'combo',
            available_quantity: 999,
            selections: {},
            components_detail: combo.components?.filter(c => c.component_type === 'fixed').map(c => {
                let name = c.sellable?.name || c.name;
                // Incluir variante por defecto si existe
                if (c.default_variant_id && c.default_variant) {
                    const variantName = c.default_variant.variant_name || c.default_variant.name;
                    if (variantName) {
                        name += ` - ${variantName}`;
                    }
                }
                return {
                    type: 'fixed',
                    name: name,
                    quantity: c.quantity
                };
            }) || []
        });
        return;
    }

    selectedCombo.value = combo;
    showComboSlideOver.value = true;
};

// Agregar combo al carrito
const addComboToCart = (comboItem) => {
    // Generar un ID único para este combo con sus selecciones
    const comboCartId = `combo-${comboItem.id}-${Date.now()}`;

    cartItems.value.push({
        ...comboItem,
        cartId: comboCartId,
    });

    showNotification(`${comboItem.name} agregado al carrito`, 'success');
    saveCartToStorage();
    showComboSlideOver.value = false;
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

const clearCart = async () => {
    const confirmed = await confirm({
        title: '¿Limpiar carrito?',
        message: 'Se eliminarán todos los productos del carrito.',
        confirmText: 'Limpiar',
        type: 'warning'
    });

    if (confirmed) {
        cartItems.value = [];
        discount.value = 0;
        tax.value = 0;
        selectedTable.value = null;
        clearCartStorage();
        showNotification('Carrito limpiado', 'info');
    }
};

// 7. GESTIÓN DE ÓRDENES PENDIENTES
const selectExistingSale = async (sale) => {
    // Asignar la venta seleccionada PRIMERO
    selectedExistingSale.value = sale;

    // Cargar datos de la venta seleccionada
    discount.value = parseFloat(sale.discount || 0);
    tax.value = parseFloat(sale.tax || 0);
    paymentMethod.value = sale.payment_method || 'efectivo';
    selectedTable.value = sale.table_id || null;

    // Pre-llenar nombre del cliente y notas (si existen)
    customerName.value = sale.customer_name || '';
    orderNotes.value = sale.notes || '';

    // Esperar a que el DOM se actualice con todos los datos
    await nextTick();

    // Cerrar panel de órdenes pendientes DESPUÉS
    showPendingSales.value = false;

    showNotification(`Orden #${sale.sale_number} lista para cobrar o agregar más items.`, 'info');
};

const completeExistingSale = async (sale) => {
    if (processing.value) return;

    const confirmed = await confirm({
        title: '¿Completar orden?',
        message: `Se completará la orden #${sale.sale_number} por $${parseFloat(sale.total).toFixed(2)}.`,
        confirmText: 'Completar',
        type: 'info'
    });
    if (!confirmed) return;

    processing.value = true;

    try {
        const response = await fetch(route('sales.complete-pending', sale.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });

        const data = await response.json();

        if (data.success) {
            showNotification(data.message || `Orden #${sale.sale_number} completada exitosamente`, 'success');

            // Recargar página para actualizar lista de órdenes pendientes
            router.reload({ only: ['pending_sales'] });
        } else {
            showNotification(data.message || 'Error al completar la orden', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al procesar la orden', 'error');
    } finally {
        processing.value = false;
    }
};

// Eliminar orden pendiente desde el POS
const deletePendingOrder = async (sale) => {
    const confirmed = await confirm({
        title: '¿Eliminar orden pendiente?',
        message: `Se eliminará la orden #${sale.sale_number} por $${parseFloat(sale.total).toFixed(2)}. Esta acción no se puede deshacer.`,
        confirmText: 'Eliminar',
        type: 'danger'
    });

    if (!confirmed) return;

    router.delete(route('sales.destroy', sale.id), {
        preserveScroll: true,
        onSuccess: () => {
            showNotification(`Orden #${sale.sale_number} eliminada`, 'success');
            // Limpiar si era la orden seleccionada
            if (selectedExistingSale.value?.id === sale.id) {
                clearSelectedSale();
            }
        },
        onError: (errors) => {
            showNotification(errors.message || 'Error al eliminar la orden', 'error');
        }
    });
};

const clearSelectedSale = () => {
    selectedExistingSale.value = null;
    cartItems.value = [];
    discount.value = 0;
    tax.value = 0;
    paymentMethod.value = 'efectivo';
    cashReceived.value = 0;
    customerName.value = '';
    orderNotes.value = '';
    selectedTable.value = null;
    showPendingSales.value = false; // Cerrar modal
    showNotification('Nueva venta iniciada', 'info');
};

// 8. PROCESAR VENTA
const processSale = (action = 'complete') => {
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
        // Validación para venta normal (solo si no hay venta existente o si hay items nuevos)
        if (!selectedExistingSale.value && cartItems.value.length === 0) {
            showNotification('El carrito está vacío', 'warning');
            return;
        }
    }

    // Solo requerir método de pago al completar
    if (action === 'complete' && !paymentMethod.value) {
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
        table_id: selectedTable.value,
        customer_name: customerName.value || null,
        notes: orderNotes.value || null,
        action: action,
        discount: 0,
        tax: 0
    } : {
        // Datos para venta normal
        is_free_sale: false,
        action: action,
        existing_sale_id: selectedExistingSale.value?.id || null,
        table_id: selectedTable.value,
        customer_name: customerName.value || null,
        notes: orderNotes.value || null,
        items: cartItems.value.map(item => {
            // Construir objeto base con campos comunes
            const itemData = {
                product_type: item.product_type,
                quantity: item.quantity,
                unit_price: item.price, // Requerido para todos los items
            };

            // Para items de tipo 'free', incluir campos adicionales
            if (item.product_type === 'free') {
                itemData.name = item.name;
                itemData.price = item.price;
                itemData.category = item.category;
            } else if (item.product_type === 'combo') {
                // Para combos, incluir id y selecciones
                itemData.id = item.id;
                itemData.combo_selections = item.selections || {};
                itemData.components_detail = item.components_detail || [];
            } else {
                // Para items regulares (menu/simple/variant), incluir id
                itemData.id = item.id;
            }

            return itemData;
        }),
        payment_method: paymentMethod.value,
        discount: parseFloat(discount.value || 0),
        tax: parseFloat(tax.value || 0)
    };

    console.log('Enviando datos de venta:', saleData);

    // Para ventas completadas, dejamos que Inertia siga el redirect automático
    // Para ventas pendientes, nos quedamos en el POS
    
    // Guardar backup del carrito en caso de error
    const cartBackup = JSON.stringify(cartItems.value);
    const discountBackup = discount.value;
    const taxBackup = tax.value;
    
    const routerOptions = action === 'complete' ? {
        // Ventas completadas: seguir redirect a detalle
        preserveState: false,
        preserveScroll: false,
        onSuccess: () => {
            // Limpiar carrito solo después de éxito
            // (aunque técnicamente la página ya cambió, esto limpia localStorage)
            clearCartStorage();
            console.log('Venta completada - carrito limpiado');
        },
        onError: (errors) => {
            console.error('Error en venta:', errors);
            // Mostrar mensaje de error
            if (errors.error) {
                showNotification('Error: ' + errors.error, 'error');
            } else if (errors.message) {
                showNotification('Error: ' + errors.message, 'error');
            } else if (typeof errors === 'object' && Object.keys(errors).length > 0) {
                showNotification('Error: ' + Object.values(errors).flat().join(', '), 'error');
            } else {
                showNotification('Error desconocido al procesar la venta', 'error');
            }
            
            // Restaurar carrito si fue limpiado
            if (cartItems.value.length === 0 && cartBackup) {
                try {
                    cartItems.value = JSON.parse(cartBackup);
                    discount.value = discountBackup;
                    tax.value = taxBackup;
                } catch (e) {
                    console.error('Error restaurando carrito:', e);
                }
            }
        },
        onFinish: () => {
            processing.value = false;
        }
    } : {
        // Ventas pendientes: quedarse en POS
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('Orden guardada:', page);
            const message = selectedExistingSale.value
                ? '¡Orden actualizada! Los cambios se guardaron.'
                : '¡Orden guardada! Puedes completarla después.';
            showNotification(message, 'success');

            // Limpiar carrito pero quedarse en POS
            cartItems.value = [];
            discount.value = 0;
            tax.value = 0;
            paymentMethod.value = 'efectivo';
            cashReceived.value = 0;
            customerName.value = '';
            orderNotes.value = '';
            selectedExistingSale.value = null;
            selectedTable.value = null;
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
    };

    router.post(route('sales.pos.store'), saleData, routerOptions);
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
watch([discount, tax, paymentMethod, cashReceived, selectedTable, customerName, orderNotes], () => {
    if (cartItems.value.length > 0) {
        saveCartToStorage();
    }
});

// 10. WATCHERS
// Watch search term to auto-switch to first category with results
watch(searchTerm, (newValue) => {
    if (newValue && newValue.length > 0) {
        // Si hay búsqueda activa, cambiar al primer tab que tenga resultados
        const firstCategoryWithResults = Object.keys(groupedProducts.value)[0];
        if (firstCategoryWithResults) {
            activeTab.value = firstCategoryWithResults;
        }
    }
});

// 11. LIFECYCLE HOOKS
// Keyboard shortcuts handler
const handleKeyboardShortcuts = (event) => {
    // Ctrl+F - Focus search
    if ((event.metaKey || event.ctrlKey) && event.key === 'f') {
        event.preventDefault();
        searchInputRef.value?.focus();
        return;
    }

    // Ignore if user is typing in an input
    const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName);
    if (isTyping) return;

    // / key - Focus search (like GitHub, Slack, etc.)
    if (event.key === '/' && !event.ctrlKey && !event.metaKey) {
        event.preventDefault();
        searchInputRef.value?.focus();
        return;
    }

    // * key - Focus cash received input (only if efectivo or mixto is selected)
    if (event.key === '*' && (paymentMethod.value === 'efectivo' || paymentMethod.value === 'mixto') && (cartItems.value.length > 0 || isFreeSale.value || selectedExistingSale.value)) {
        event.preventDefault();
        cashReceivedInputRef.value?.focus();
        showNotification('💵 Ingresa el efectivo recibido', 'info');
        return;
    }

    // Payment method shortcuts (F1, F2, F3)
    // Only work if there are items in cart or free sale is active or existing sale selected
    if (cartItems.value.length > 0 || isFreeSale.value || selectedExistingSale.value) {
        if (event.key === 'F1') {
            event.preventDefault();
            paymentMethod.value = 'efectivo';
            showNotification('💵 Método de pago: Efectivo', 'info');
            return;
        }

        if (event.key === 'F2') {
            event.preventDefault();
            paymentMethod.value = 'tarjeta';
            showNotification('💳 Método de pago: Tarjeta', 'info');
            return;
        }

        if (event.key === 'F3') {
            event.preventDefault();
            paymentMethod.value = 'transferencia';
            showNotification('🏦 Método de pago: Transferencia', 'info');
            return;
        }

        // F9 - Save as pending (only if there are new items in cart)
        if (event.key === 'F9' && !isFreeSale.value && cartItems.value.length > 0) {
            event.preventDefault();
            processSale('save_pending');
            return;
        }

        // F10 - Complete sale
        if (event.key === 'F10') {
            event.preventDefault();
            // Check if sale can be completed
            const canComplete = isFreeSale.value
                ? (freeSaleDescription.value && freeSaleTotal.value && parseFloat(freeSaleTotal.value) > 0)
                : (selectedExistingSale.value || cartItems.value.length > 0);

            if (canComplete) {
                processSale('complete');
            } else {
                showNotification('⚠️ Agrega productos al carrito para completar la venta', 'warning');
            }
            return;
        }

        // Ctrl+Enter - Alternative shortcut for complete sale (more intuitive)
        if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
            event.preventDefault();
            // Check if sale can be completed
            const canComplete = isFreeSale.value
                ? (freeSaleDescription.value && freeSaleTotal.value && parseFloat(freeSaleTotal.value) > 0)
                : (selectedExistingSale.value || cartItems.value.length > 0);

            if (canComplete) {
                processSale('complete');
            } else {
                showNotification('⚠️ Agrega productos al carrito para completar la venta', 'warning');
            }
            return;
        }
    }
};

onMounted(() => {
    // Cargar carrito guardado
    loadCartFromStorage();
    console.log('POS iniciado - Carrito persistente activado');

    // Verificar si viene de la vista de mesas con table_id en URL
    const urlParams = new URLSearchParams(window.location.search);
    const tableIdFromUrl = urlParams.get('table_id');

    if (tableIdFromUrl) {
        const tableId = parseInt(tableIdFromUrl);
        // Verificar que la mesa existe en las disponibles
        const tableExists = props.available_tables?.some(t => t.id === tableId);
        if (tableExists) {
            selectedTable.value = tableId;
            showNotification('Mesa pre-seleccionada desde el modulo de mesas', 'info');
        }
    }

    // Verificar si viene con load_sale para cargar una orden pendiente
    const loadSaleId = urlParams.get('load_sale');
    if (loadSaleId && props.pending_sales) {
        const saleToLoad = props.pending_sales.find(s => s.id == loadSaleId);
        if (saleToLoad) {
            // Usar nextTick para asegurar que el DOM está listo
            nextTick(() => {
                selectExistingSale(saleToLoad);
                showNotification(`Orden #${saleToLoad.sale_number} cargada. Lista para cobrar o modificar.`, 'success');
            });
        }
    }

    // Agregar keyboard shortcuts (/ o Ctrl+F para buscar)
    document.addEventListener('keydown', handleKeyboardShortcuts);
});

onBeforeUnmount(() => {
    // Guardar antes de salir de la página
    // Remove keyboard shortcuts
    document.removeEventListener('keydown', handleKeyboardShortcuts);
    if (cartItems.value.length > 0) {
        saveCartToStorage();
    }
});
</script>

<template>
    <Head title="Punto de Venta" />

    <AdminLayout>
        <template #header>
            <div class="space-y-3 md:space-y-0 md:flex md:justify-between md:items-center">
                <div class="min-w-0 flex-1">
                    <h2 class="font-semibold text-lg md:text-xl text-gray-800 dark:text-white leading-tight truncate">
                        Punto de Venta (POS)
                    </h2>
                    <p v-if="selectedExistingSale" class="text-xs md:text-sm text-orange-600 dark:text-orange-400 mt-1 truncate">
                        Editando: #{{ selectedExistingSale.sale_number }} - ${{ parseFloat(selectedExistingSale.total).toFixed(2) }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 md:gap-4">
                    <!-- Botón de órdenes pendientes -->
                    <button
                        v-if="pending_sales && pending_sales.length > 0"
                        @click="showPendingSales = !showPendingSales"
                        class="flex items-center space-x-1.5 md:space-x-2 px-4 py-2.5 md:px-5 md:py-2.5 bg-orange-600 hover:bg-orange-700 active:bg-orange-800 text-white rounded-xl transition-colors relative text-sm font-medium shadow-sm active:scale-95"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span class="hidden sm:inline">Órdenes</span>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center shadow">
                            {{ pending_sales.length }}
                        </span>
                    </button>
                    <div class="hidden md:block text-sm text-gray-600 dark:text-gray-400">
                        {{ currentDate }}
                    </div>
                    <div class="text-xs md:text-sm text-gray-600 dark:text-gray-400 truncate max-w-[120px] md:max-w-none">
                        {{ $page.props.auth.user.name }}
                    </div>
                </div>
            </div>
        </template>

        <div class="py-3 md:py-6">
            <div class="max-w-full mx-auto px-2 sm:px-6 lg:px-8">
                <!-- Panel de Órdenes Pendientes -->
                <div v-if="showPendingSales" class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 md:p-6 overflow-hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-base md:text-lg font-semibold text-gray-900 dark:text-white">
                            Órdenes Activas ({{ pending_sales.length }})
                        </h3>
                        <button
                            @click="showPendingSales = false"
                            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors active:scale-95"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
                        <div
                            v-for="sale in pending_sales"
                            :key="sale.id"
                            class="border-2 rounded-lg p-3 md:p-4 hover:shadow-md transition-shadow cursor-pointer min-w-0"
                            :class="selectedExistingSale?.id === sale.id ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20' : 'border-gray-300 dark:border-gray-600'"
                            @click="selectExistingSale(sale)"
                        >
                            <div class="flex justify-between items-start mb-3 gap-2">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-gray-900 dark:text-white text-sm md:text-base truncate">
                                        #{{ sale.sale_number }}
                                    </h4>
                                    <!-- Nombre del Cliente (si existe) -->
                                    <p v-if="sale.customer_name" class="text-xs md:text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center mt-1 truncate">
                                        <svg class="w-3 h-3 md:w-3.5 md:h-3.5 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="truncate">{{ sale.customer_name }}</span>
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ new Date(sale.created_at).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' }) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-1 md:gap-2 flex-shrink-0">
                                    <span class="px-1.5 md:px-2 py-0.5 md:py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 text-xs font-semibold rounded-full whitespace-nowrap">
                                        Pend.
                                    </span>
                                    <!-- Botón eliminar -->
                                    <button
                                        @click.stop="deletePendingOrder(sale)"
                                        class="p-2 text-red-500 hover:text-red-700 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors flex-shrink-0 active:scale-95"
                                        title="Eliminar orden"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div v-if="sale.table" class="mb-2 flex items-center min-w-0">
                                <svg class="w-3 h-3 md:w-4 md:h-4 mr-1 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span class="text-xs md:text-sm text-gray-600 dark:text-gray-400 truncate">
                                    Mesa {{ sale.table.table_number }}{{ sale.table.name ? ` - ${sale.table.name}` : '' }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Items:</p>
                                <div class="space-y-1">
                                    <div
                                        v-for="item in sale.sale_items.slice(0, 2)"
                                        :key="item.id"
                                        class="text-xs text-gray-700 dark:text-gray-300 truncate"
                                    >
                                        {{ item.quantity }}x {{ getSaleItemName(item) }}
                                    </div>
                                    <p v-if="sale.sale_items.length > 2" class="text-xs text-gray-500 dark:text-gray-400">
                                        + {{ sale.sale_items.length - 2 }} más...
                                    </p>
                                </div>
                            </div>

                            <div class="flex justify-between items-center pt-2 md:pt-3 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-xs text-gray-600 dark:text-gray-400 truncate max-w-[80px]">
                                    {{ sale.user?.name }}
                                </span>
                                <span class="text-base md:text-lg font-bold text-green-600 dark:text-green-400">
                                    ${{ parseFloat(sale.total).toFixed(2) }}
                                </span>
                            </div>

                            <div class="mt-2 md:mt-3 grid grid-cols-2 gap-1.5 md:gap-2">
                                <button
                                    @click.stop="selectExistingSale(sale)"
                                    class="px-2 md:px-3 py-1.5 md:py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition-colors"
                                >
                                    + Items
                                </button>
                                <button
                                    @click.stop="completeExistingSale(sale)"
                                    class="px-2 md:px-3 py-1.5 md:py-2 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded-lg transition-colors"
                                >
                                    Cobrar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <button
                            v-if="selectedExistingSale"
                            @click="clearSelectedSale"
                            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors"
                        >
                            Nueva Venta
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Panel de Productos -->
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <!-- Header con controles -->
                                <div class="space-y-4 mb-6">
                                    <!-- Search Bar Prominente -->
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                        <input
                                            ref="searchInputRef"
                                            v-model="searchTerm"
                                            type="text"
                                            placeholder="Buscar productos rápidamente..."
                                            class="w-full pl-11 pr-32 py-3 text-base border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900 rounded-lg shadow-sm transition-all"
                                        />
                                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center gap-2 pointer-events-none">
                                            <kbd class="hidden sm:inline-flex items-center px-2 py-1 text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded">
                                                Ctrl+F
                                            </kbd>
                                            <span class="text-gray-400 dark:text-gray-500">o</span>
                                            <kbd class="hidden sm:inline-flex items-center px-2 py-1 text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded">
                                                /
                                            </kbd>
                                        </div>
                                    </div>

                                    <!-- Header y Filtros (Responsive) -->
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                        <h3 class="text-base md:text-lg font-semibold text-gray-900 dark:text-white">Catálogo de Productos</h3>
                                        <div class="flex items-center gap-2 md:gap-3">
                                            <button
                                                @click="showFreeSaleSlideOver = true"
                                                class="flex items-center space-x-1 md:space-x-2 px-3 md:px-4 py-2 text-xs md:text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-lg transition-all shadow-sm hover:shadow-md"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                <span class="hidden sm:inline">Venta Libre</span>
                                                <span class="sm:hidden">Libre</span>
                                            </button>
                                            <select
                                                v-model="selectedCategory"
                                                class="text-xs md:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm flex-1 md:flex-none"
                                            >
                                                <option value="">Todas</option>
                                                <option
                                                    v-for="category in availableCategories"
                                                    :key="category"
                                                    :value="category"
                                                >
                                                    {{ category === 'menu' ? 'Menú' : getCategoryTitle(category) }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Search Results Info -->
                                <div v-if="searchTerm" class="mb-4 px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                    <p class="text-sm text-blue-700 dark:text-blue-300 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Buscando "<strong>{{ searchTerm }}</strong>" en todas las categorías
                                    </p>
                                </div>

                                <!-- Tabs de categorías (scrollable en móvil) -->
                                <div class="border-b border-gray-200 dark:border-gray-700 mb-6 -mx-4 px-4">
                                    <nav class="-mb-px flex space-x-4 md:space-x-8 overflow-x-auto scrollbar-hide pb-px">
                                        <button
                                            v-for="(group, key) in groupedProducts"
                                            :key="key"
                                            @click="activeTab = key"
                                            :class="[
                                                activeTab === key
                                                    ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                                    : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                                                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex-shrink-0'
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
                                            (item.is_in_stock || item.has_variants || item.allows_variants)
                                                ? 'hover:shadow-md hover:border-blue-300 dark:hover:border-blue-600 bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600'
                                                : 'bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-700 cursor-not-allowed opacity-60'
                                        ]"
                                        @click="addToCart(item)"
                                    >
                                        <!-- Badge de disponibilidad -->
                                        <div class="absolute top-2 right-2">
                                            <!-- Combos -->
                                            <span
                                                v-if="item.product_type === 'combo'"
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                                Combo
                                            </span>
                                            <!-- Productos con variantes -->
                                            <span
                                                v-else-if="item.has_variants || item.allows_variants"
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200"
                                            >
                                                Variantes
                                            </span>
                                            <!-- Productos normales con stock -->
                                            <span
                                                v-else-if="item.is_in_stock"
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200"
                                            >
                                                Stock: {{ item.available_quantity }}
                                            </span>
                                            <!-- Productos agotados -->
                                            <span
                                                v-else
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200"
                                            >
                                                Agotado
                                            </span>
                                        </div>

                                        <div class="mt-4">
                                            <h4 class="font-semibold text-gray-900 dark:text-white text-lg">{{ item.name }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1" v-if="item.description">
                                                {{ item.description }}
                                            </p>

                                            <!-- Ingredientes para platillos del menú -->
                                            <div class="mt-2" v-if="item.product_type === 'menu' && item.recipes && item.recipes.length > 0">
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Ingredientes:</p>
                                                <div class="flex flex-wrap gap-1">
                                                    <span
                                                        v-for="recipe in item.recipes"
                                                        :key="recipe.id"
                                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300"
                                                    >
                                                        {{ recipe.product.name }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Información para productos simples -->
                                            <div class="mt-2" v-else-if="item.product_type === 'simple'">
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    Categoria: {{ item.category }}
                                                </p>
                                            </div>

                                            <!-- Información para combos -->
                                            <div class="mt-2" v-else-if="item.product_type === 'combo'">
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Incluye:</p>
                                                <div class="flex flex-wrap gap-1">
                                                    <span
                                                        v-for="component in (item.components || []).slice(0, 3)"
                                                        :key="component.id"
                                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-xs"
                                                        :class="component.component_type === 'fixed'
                                                            ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'
                                                            : 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300'"
                                                    >
                                                        {{ component.sellable?.name || component.name || 'Opcion' }}
                                                    </span>
                                                    <span
                                                        v-if="(item.components || []).length > 3"
                                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300"
                                                    >
                                                        +{{ (item.components || []).length - 3 }} mas
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="flex justify-between items-center mt-4">
                                                <span class="text-2xl font-bold text-green-600 dark:text-green-400">
                                                    ${{ formatPrice(item.price) }}
                                                </span>
                                                <!-- Combos -->
                                                <button
                                                    v-if="item.product_type === 'combo'"
                                                    @click.stop="addToCart(item)"
                                                    class="bg-purple-500 hover:bg-purple-600 dark:bg-purple-600 dark:hover:bg-purple-700 text-white px-3 py-1 rounded-md text-sm font-medium transition-colors flex items-center gap-1.5"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                    </svg>
                                                    {{ item.components?.some(c => c.component_type === 'choice') ? 'Personalizar' : 'Agregar' }}
                                                </button>
                                                <!-- Productos con variantes -->
                                                <button
                                                    v-else-if="item.has_variants || item.allows_variants"
                                                    @click.stop="addToCart(item)"
                                                    class="bg-purple-500 hover:bg-purple-600 dark:bg-purple-600 dark:hover:bg-purple-700 text-white px-3 py-1 rounded-md text-sm font-medium transition-colors"
                                                >
                                                    Ver variantes
                                                </button>
                                                <!-- Productos normales con stock -->
                                                <button
                                                    v-else-if="item.is_in_stock"
                                                    @click.stop="addToCart(item)"
                                                    class="bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm font-medium transition-colors"
                                                >
                                                    Agregar
                                                </button>
                                                <!-- Productos agotados -->
                                                <span v-else class="text-sm text-red-500 dark:text-red-400 font-medium">
                                                    Agotado
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mensaje si no hay productos -->
                                <div v-if="(groupedProducts[activeTab]?.items || []).length === 0" class="text-center py-12">
                                    <div class="text-gray-500 dark:text-gray-400">
                                        <div class="text-4xl mb-4">📦</div>
                                        <div>No hay productos disponibles en esta categoría</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel del Carrito (Solo visible en desktop) -->
                    <div class="hidden lg:block lg:col-span-1">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Carrito de Compras</h3>
                                    <div class="flex items-center space-x-2">
                                        <!-- Indicador de carrito guardado -->
                                        <div v-if="cartItems.length > 0" class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded-full">
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

                                <!-- Items existentes de orden seleccionada -->
                                <div v-if="selectedExistingSale" class="mb-6 bg-orange-50 dark:bg-orange-900/20 border-2 border-orange-300 dark:border-orange-700 rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-orange-900 dark:text-orange-100 mb-2">
                                        📋 Items existentes en orden #{{ selectedExistingSale.sale_number }}
                                    </h4>
                                    <div class="space-y-2 max-h-48 overflow-y-auto">
                                        <div
                                            v-for="item in selectedExistingSale.sale_items"
                                            :key="item.id"
                                            class="flex justify-between items-center text-xs bg-white dark:bg-gray-800 rounded p-2"
                                        >
                                            <div class="flex-1">
                                                <span class="font-medium text-gray-900 dark:text-white">
                                                    {{ item.quantity }}x {{ getSaleItemName(item) }}
                                                </span>
                                            </div>
                                            <span class="text-gray-600 dark:text-gray-400">
                                                ${{ parseFloat(item.total_price).toFixed(2) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-2 pt-2 border-t border-orange-200 dark:border-orange-800">
                                        <div class="flex justify-between text-xs font-semibold text-orange-900 dark:text-orange-100">
                                            <span>Subtotal existente:</span>
                                            <span>${{ parseFloat(selectedExistingSale.total).toFixed(2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Items del carrito (nuevos) -->
                                <div v-if="cartItems.length === 0 && !selectedExistingSale" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <div class="text-3xl mb-2">🛒</div>
                                    <div class="font-medium">Carrito vacío</div>
                                    <div class="text-sm">Agrega productos del catálogo</div>
                                    <div class="text-xs mt-2 text-blue-600 dark:text-blue-400">
                                        El carrito se guarda automáticamente
                                    </div>
                                </div>

                                <!-- Título de items nuevos si hay orden seleccionada -->
                                <h4 v-if="selectedExistingSale && cartItems.length > 0" class="text-sm font-semibold text-green-700 dark:text-green-400 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Items nuevos a agregar
                                </h4>

                                <div v-if="cartItems.length > 0" class="space-y-3 mb-6">
                                    <div
                                        v-for="(item, index) in cartItems"
                                        :key="index"
                                        class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg"
                                    >
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900 dark:text-white">{{ item.name }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">${{ formatPrice(item.price) }} c/u</p>
                                            <span
                                                class="text-xs px-2 py-1 rounded"
                                                :class="item.product_type === 'combo'
                                                    ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300'
                                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'"
                                            >
                                                {{ item.product_type === 'combo' ? 'Combo' : item.product_type === 'menu' ? 'Platillo' : item.product_type === 'variant' ? 'Variante' : item.product_type === 'free' ? 'Libre' : 'Individual' }}
                                            </span>
                                            <!-- Mostrar componentes del combo -->
                                            <div v-if="item.product_type === 'combo' && item.components_detail?.length > 0" class="mt-1 space-y-0.5">
                                                <p v-for="(comp, idx) in item.components_detail.slice(0, 3)" :key="idx" class="text-xs text-gray-500 dark:text-gray-400">
                                                    - {{ comp.name }}
                                                </p>
                                                <p v-if="item.components_detail.length > 3" class="text-xs text-gray-400">
                                                    +{{ item.components_detail.length - 3 }} mas...
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-2">
                                            <button
                                                @click="decrementQuantity(index)"
                                                class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 flex items-center justify-center text-gray-600 dark:text-gray-200 font-medium"
                                            >
                                                -
                                            </button>
                                            <span class="w-8 text-center font-medium dark:text-white">{{ item.quantity }}</span>
                                            <button
                                                @click="incrementQuantity(index)"
                                                :disabled="item.quantity >= item.available_quantity"
                                                class="w-8 h-8 rounded-full bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 disabled:bg-gray-300 dark:disabled:bg-gray-700 flex items-center justify-center text-white font-medium"
                                            >
                                                +
                                            </button>
                                            <button
                                                @click="removeFromCart(index)"
                                                class="w-8 h-8 rounded-full bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 flex items-center justify-center text-white font-medium ml-2"
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form de Venta Libre (solo si está activado) -->
                                <div v-if="isFreeSale" class="border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4 bg-blue-50 dark:bg-blue-900/20">
                                    <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-3">
                                        💵 Datos de Venta Libre
                                    </h4>

                                    <div class="space-y-3">
                                        <!-- Descripción -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Descripción *
                                            </label>
                                            <textarea
                                                v-model="freeSaleDescription"
                                                rows="3"
                                                maxlength="500"
                                                placeholder="Ej: Servicio de catering para evento corporativo"
                                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm"
                                            ></textarea>
                                            <div class="flex justify-between items-center mt-1">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    Mínimo 3 caracteres
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ freeSaleDescription.length }}/500
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Monto -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Monto Total *
                                            </label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-2.5 text-gray-500 dark:text-gray-400">$</span>
                                                <input
                                                    v-model="freeSaleTotal"
                                                    type="number"
                                                    step="0.01"
                                                    min="0.01"
                                                    placeholder="0.00"
                                                    class="w-full pl-7 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                                >
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                Este monto no afectará el inventario
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Totales -->
                                <div v-if="cartItems.length > 0 && !isFreeSale" class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                    <div class="space-y-3">
                                        <div class="flex justify-between text-sm dark:text-gray-300">
                                            <span class="font-medium">Subtotal:</span>
                                            <span>${{ formatPrice(subtotal) }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm items-center dark:text-gray-300">
                                            <span class="font-medium">Descuento:</span>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs">$</span>
                                                <input
                                                    v-model="discount"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    :max="subtotal"
                                                    class="w-20 text-right text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded"
                                                />
                                            </div>
                                        </div>
                                        <div class="flex justify-between text-sm items-center dark:text-gray-300">
                                            <span class="font-medium">Impuesto:</span>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs">$</span>
                                                <input
                                                    v-model="tax"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    class="w-20 text-right text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded"
                                                />
                                            </div>
                                        </div>
                                        <div class="flex justify-between text-lg font-bold border-t dark:border-gray-700 pt-3 dark:text-white">
                                            <span>Total:</span>
                                            <span class="text-green-600 dark:text-green-400">${{ formatPrice(total) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Resumen de Venta Libre -->
                                <div v-if="isFreeSale" class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                    <div class="flex justify-between text-lg font-bold dark:text-white">
                                        <span>Total (Venta Libre):</span>
                                        <span class="text-green-600 dark:text-green-400">${{ formatPrice(parseFloat(freeSaleTotal || 0)) }}</span>
                                    </div>
                                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">
                                        Esta venta no afectará el inventario
                                    </p>
                                </div>

                                <!-- Selección de Mesa (opcional) -->
                                <div v-if="hasItemsToSell" class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Mesa (opcional)
                                        </label>
                                        <select
                                            v-model="selectedTable"
                                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        >
                                            <option :value="null">Sin asignar mesa</option>
                                            <option
                                                v-for="table in available_tables"
                                                :key="table.id"
                                                :value="table.id"
                                                :disabled="table.status === 'ocupada' && table.current_sale_id !== selectedExistingSale?.id"
                                            >
                                                Mesa {{ table.table_number }} {{ table.name ? `- ${table.name}` : '' }}
                                                ({{ table.capacity }} pers.)
                                                {{ table.status === 'ocupada' ? '- Ocupada' : '' }}
                                                {{ table.status === 'reservada' ? '- Reservada' : '' }}
                                                {{ table.status === 'en_limpieza' ? '- En Limpieza' : '' }}
                                            </option>
                                        </select>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ selectedTable ? '✓ Mesa asignada' : 'Para llevar o sin mesa' }}
                                        </p>
                                    </div>

                                    <!-- Información del Cliente (colapsable) -->
                                    <div v-show="hasItemsToSell" class="mt-4">
                                        <button
                                            @click="showCustomerInfo = !showCustomerInfo"
                                            type="button"
                                            class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors"
                                        >
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                Información del Cliente
                                                <span v-if="customerName || orderNotes" class="ml-2 text-xs text-green-600 dark:text-green-400">✓</span>
                                            </span>
                                            <svg
                                                class="w-5 h-5 transition-transform"
                                                :class="{ 'rotate-180': showCustomerInfo }"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Contenido colapsable -->
                                        <div v-show="showCustomerInfo" class="mt-3 space-y-3 pl-1">
                                            <!-- Nombre del Cliente -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                                    Nombre (opcional)
                                                </label>
                                                <input
                                                    v-model="customerName"
                                                    type="text"
                                                    maxlength="100"
                                                    placeholder="Ej: Juan Pérez, Mesa 5..."
                                                    class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                />
                                            </div>

                                            <!-- Notas -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                                    Notas (opcional)
                                                </label>
                                                <textarea
                                                    v-model="orderNotes"
                                                    rows="2"
                                                    maxlength="500"
                                                    placeholder="Ej: Sin cebolla, extra picante..."
                                                    class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                ></textarea>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 text-right">
                                                    {{ orderNotes.length }}/500
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Método de pago (siempre visible si hay carrito, venta libre o orden seleccionada) -->
                                    <div v-show="hasItemsToSell" class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Método de Pago
                                        </label>
                                        <select
                                            v-model="paymentMethod"
                                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        >
                                            <option
                                                v-for="method in activePaymentMethods"
                                                :key="method.name"
                                                :value="method.name"
                                            >
                                                {{ method.label }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Calculadora de Cambio (solo para métodos que requieren input de monto) -->
                                    <div v-show="hasItemsToSell && currentMethodRequiresAmountInput" class="mt-4">
                                        <label class="flex items-center justify-between text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                            <span>💵 Efectivo Recibido</span>
                                            <span class="text-gray-500 dark:text-gray-400 font-normal">(Atajo: *)</span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 text-sm">$</span>
                                            <input
                                                ref="cashReceivedInputRef"
                                                v-model.number="cashReceived"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                placeholder="0.00"
                                                class="w-full pl-7 pr-3 py-1.5 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-base font-semibold"
                                                @focus="$event.target.select()"
                                                @keydown.enter="$event.target.blur()"
                                            />
                                        </div>

                                        <!-- Botones de acceso rápido -->
                                        <div v-if="!cashReceived || cashReceived === 0" class="mt-1.5 flex flex-wrap gap-1.5">
                                            <button
                                                v-for="quickAmount in [5, 10, 15, 20, 50, 100]"
                                                :key="quickAmount"
                                                @click="cashReceived = quickAmount"
                                                type="button"
                                                class="px-2 py-0.5 text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded transition-colors"
                                            >
                                                ${{ quickAmount }}
                                            </button>
                                        </div>

                                        <!-- Resultado del cambio (compacto) -->
                                        <div v-if="cashReceived > 0" class="mt-2">
                                            <!-- Insuficiente -->
                                            <div v-if="cashReceived < finalTotal" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-2">
                                                <p class="text-xs text-red-700 dark:text-red-300 font-medium">
                                                    ⚠️ Falta: ${{ formatPrice(finalTotal - cashReceived) }}
                                                </p>
                                            </div>

                                            <!-- Suficiente -->
                                            <div v-else class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-2">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs font-medium text-green-700 dark:text-green-300">Cambio:</span>
                                                    <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                                        ${{ formatPrice(changeAmount) }}
                                                    </span>
                                                </div>

                                                <!-- Desglose compacto -->
                                                <div v-if="changeBillBreakdown.length > 0 && changeAmount > 0" class="mt-2 pt-2 border-t border-green-200 dark:border-green-700">
                                                    <div class="flex flex-wrap gap-1">
                                                        <span
                                                            v-for="bill in changeBillBreakdown"
                                                            :key="bill.value"
                                                            class="inline-flex items-center px-1.5 py-0.5 bg-white dark:bg-gray-700 rounded text-xs"
                                                        >
                                                            <span class="font-semibold text-gray-700 dark:text-gray-300">
                                                                {{ bill.value >= 1 ? `$${bill.value}` : `${bill.value * 100}¢` }}
                                                            </span>
                                                            <span class="text-green-600 dark:text-green-400 font-bold ml-1">
                                                                ×{{ bill.count }}
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botones de acción -->
                                    <div v-show="hasItemsToSell" class="mt-4 space-y-2">
                                        <!-- Información de orden existente -->
                                        <div v-if="selectedExistingSale" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-3">
                                            <p class="text-sm text-blue-900 dark:text-blue-100 font-medium">
                                                <span v-if="cartItems.length > 0">
                                                    ℹ️ Agregando items a orden #{{ selectedExistingSale.sale_number }}
                                                </span>
                                                <span v-else>
                                                    📋 Orden activa #{{ selectedExistingSale.sale_number }}
                                                </span>
                                            </p>
                                            <div v-if="cartItems.length > 0" class="mt-2 space-y-1">
                                                <div class="flex justify-between text-xs text-blue-700 dark:text-blue-300">
                                                    <span>Total anterior:</span>
                                                    <span class="font-semibold">${{ parseFloat(selectedExistingSale.total).toFixed(2) }}</span>
                                                </div>
                                                <div class="flex justify-between text-xs text-blue-700 dark:text-blue-300">
                                                    <span>Nuevos items:</span>
                                                    <span class="font-semibold">${{ formatPrice(total) }}</span>
                                                </div>
                                                <div class="flex justify-between text-sm font-bold text-blue-900 dark:text-blue-100 pt-1 border-t border-blue-200 dark:border-blue-700">
                                                    <span>Total a cobrar:</span>
                                                    <span class="text-green-600 dark:text-green-400">${{ formatPrice(parseFloat(selectedExistingSale.total) + total) }}</span>
                                                </div>
                                            </div>
                                            <div v-else class="mt-1">
                                                <p class="text-xs text-blue-700 dark:text-blue-300">
                                                    Total a cobrar: <span class="font-semibold">${{ parseFloat(selectedExistingSale.total).toFixed(2) }}</span>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Botón: Guardar Pendiente (solo si HAY items nuevos en carrito) -->
                                        <button
                                            v-if="!isFreeSale && cartItems.length > 0"
                                            @click="processSale('save_pending')"
                                            :disabled="processing"
                                            class="w-full bg-yellow-500 hover:bg-yellow-600 disabled:bg-gray-400 text-white font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center"
                                        >
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                            </svg>
                                            <span v-if="selectedExistingSale">
                                                Guardar Cambios
                                            </span>
                                            <span v-else>
                                                Guardar Pendiente
                                            </span>
                                        </button>

                                        <!-- Botón: Completar y Pagar (SIEMPRE disponible si hay orden activa O carrito) -->
                                        <button
                                            @click="processSale('complete')"
                                            :disabled="processing || (isFreeSale && (!freeSaleDescription || !freeSaleTotal || parseFloat(freeSaleTotal) <= 0)) || (!isFreeSale && !selectedExistingSale && cartItems.length === 0)"
                                            class="w-full bg-green-500 hover:bg-green-600 disabled:bg-gray-400 text-white font-bold py-3 px-4 rounded-lg transition-colors"
                                        >
                                            <span v-if="processing" class="flex items-center justify-center">
                                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Procesando...
                                            </span>
                                            <span v-else-if="isFreeSale">
                                                💵 Completar Venta Libre (${{ formatPrice(parseFloat(freeSaleTotal || 0)) }})
                                            </span>
                                            <span v-else-if="selectedExistingSale && cartItems.length > 0">
                                                💳 Completar y Pagar (${{ formatPrice(parseFloat(selectedExistingSale.total) + total) }})
                                            </span>
                                            <span v-else-if="selectedExistingSale">
                                                💳 Completar Orden (${{ parseFloat(selectedExistingSale.total).toFixed(2) }})
                                            </span>
                                            <span v-else>
                                                💳 Completar y Pagar (${{ formatPrice(total) }})
                                            </span>
                                        </button>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón flotante del carrito (Solo visible en móvil) -->
        <button
            v-if="cartItems.length > 0 || selectedExistingSale || isFreeSale"
            @click="showMobileCart = true"
            class="lg:hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center space-x-3 px-6 py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-full shadow-xl hover:shadow-2xl transition-all"
        >
            <span class="relative">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="absolute -top-2 -right-2 bg-red-500 text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                    {{ cartItems.length + (selectedExistingSale?.sale_items?.length || 0) }}
                </span>
            </span>
            <span class="font-bold text-lg">${{ formatPrice(finalTotal) }}</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
            </svg>
        </button>
    </AdminLayout>

    <!-- Cart SlideOver (Solo para móvil) -->
    <CartSlideOver
        :show="showMobileCart"
        :cart-items="cartItems"
        :selected-existing-sale="selectedExistingSale"
        :discount="discount"
        :tax="tax"
        :payment-method="paymentMethod"
        :cash-received="cashReceived"
        :selected-table="selectedTable"
        :available-tables="available_tables"
        :customer-name="customerName"
        :order-notes="orderNotes"
        :is-free-sale="isFreeSale"
        :free-sale-description="freeSaleDescription"
        :free-sale-total="freeSaleTotal"
        :processing="processing"
        @close="showMobileCart = false"
        @update:discount="discount = $event"
        @update:tax="tax = $event"
        @update:paymentMethod="paymentMethod = $event"
        @update:cashReceived="cashReceived = $event"
        @update:selectedTable="selectedTable = $event"
        @update:customerName="customerName = $event"
        @update:orderNotes="orderNotes = $event"
        @increment="incrementQuantity"
        @decrement="decrementQuantity"
        @remove="removeFromCart"
        @clear="clearCart"
        @process-sale="processSale"
    />

    <!-- Free Sale SlideOver -->
    <FreeSaleSlideOver
        :show="showFreeSaleSlideOver"
        @close="showFreeSaleSlideOver = false"
        @add="addFreeSaleToCart"
    />

    <!-- Product Variant SlideOver -->
    <ProductVariantSlideOver
        :show="showVariantSlideOver"
        :menu-item="selectedMenuItemForVariants"
        :cart="cartItems"
        @close="showVariantSlideOver = false"
        @select="addVariantToCart"
        @update-variants="handleUpdateVariants"
    />

    <!-- Combo Selection SlideOver -->
    <ComboSelectionSlideOver
        :show="showComboSlideOver"
        :combo="selectedCombo"
        @close="showComboSlideOver = false"
        @add-to-cart="addComboToCart"
    />

    <!-- Confirm Dialog -->
</template>