/**
 * Composable para iconos del sistema
 * Centraliza el uso de Heroicons y proporciona un mapeo semántico
 */

import {
    // Outline icons (24x24)
    ShoppingCartIcon,
    CurrencyDollarIcon,
    DocumentTextIcon,
    ChartBarIcon,
    ArrowPathIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    PlusIcon,
    CubeIcon,
    TagIcon,
    BellAlertIcon,
    CalendarIcon,
    DocumentCheckIcon,
    ReceiptRefundIcon,
    EyeIcon,
    BanknotesIcon,
    CreditCardIcon,
    DevicePhoneMobileIcon,
    UserIcon,
    Cog6ToothIcon,
    HomeIcon,
    ChartPieIcon,
    ArchiveBoxIcon,
    ListBulletIcon,
} from '@heroicons/vue/24/outline';

import {
    // Solid icons (20x20) - para badges y estados
    ShoppingCartIcon as ShoppingCartIconSolid,
    CurrencyDollarIcon as CurrencyDollarIconSolid,
    CheckCircleIcon as CheckCircleIconSolid,
    XCircleIcon as XCircleIconSolid,
    ClockIcon as ClockIconSolid,
    ExclamationCircleIcon as ExclamationCircleIconSolid,
} from '@heroicons/vue/24/solid';

/**
 * Mapeo de contextos a iconos
 */
export const icons = {
    // Navegación y acciones principales
    pos: ShoppingCartIcon,
    sales: CurrencyDollarIcon,
    inventory: CubeIcon,
    returns: ArrowPathIcon,
    menu: DocumentTextIcon,
    reports: ChartBarIcon,
    cashflow: BanknotesIcon,
    profile: UserIcon,
    settings: Cog6ToothIcon,
    home: HomeIcon,

    // Estados
    warning: ExclamationTriangleIcon,
    success: CheckCircleIcon,
    error: XCircleIcon,
    pending: ClockIcon,
    alert: BellAlertIcon,

    // Acciones
    add: PlusIcon,
    view: EyeIcon,

    // Productos y categorías
    product: CubeIcon,
    category: TagIcon,
    package: ArchiveBoxIcon,

    // Fechas y tiempo
    calendar: CalendarIcon,
    expiring: CalendarIcon,

    // Transacciones
    receipt: DocumentTextIcon,
    ticket: DocumentCheckIcon,
    refund: ReceiptRefundIcon,

    // Métodos de pago
    cash: BanknotesIcon,
    card: CreditCardIcon,
    transfer: DevicePhoneMobileIcon,
    mixed: ArrowPathIcon,

    // Métricas
    chart: ChartBarIcon,
    pie: ChartPieIcon,
    list: ListBulletIcon,
};

/**
 * Iconos sólidos para badges y estados
 */
export const solidIcons = {
    success: CheckCircleIconSolid,
    error: XCircleIconSolid,
    pending: ClockIconSolid,
    warning: ExclamationCircleIconSolid,
    sales: CurrencyDollarIconSolid,
    pos: ShoppingCartIconSolid,
};

/**
 * Hook principal
 */
export function useIcons() {
    /**
     * Obtiene un icono por contexto
     */
    const getIcon = (context, solid = false) => {
        if (solid && solidIcons[context]) {
            return solidIcons[context];
        }
        return icons[context] || CubeIcon;
    };

    /**
     * Obtiene el icono para un método de pago
     */
    const getPaymentIcon = (paymentMethod) => {
        const mapping = {
            'efectivo': icons.cash,
            'tarjeta': icons.card,
            'transferencia': icons.transfer,
            'mixto': icons.mixed,
        };
        return mapping[paymentMethod] || icons.cash;
    };

    /**
     * Obtiene el icono para un estado
     */
    const getStatusIcon = (status, solid = false) => {
        const mapping = {
            'completada': solid ? solidIcons.success : icons.success,
            'completed': solid ? solidIcons.success : icons.success,
            'pendiente': solid ? solidIcons.pending : icons.pending,
            'pending': solid ? solidIcons.pending : icons.pending,
            'cancelada': solid ? solidIcons.error : icons.error,
            'cancelled': solid ? solidIcons.error : icons.error,
        };
        return mapping[status] || (solid ? solidIcons.pending : icons.pending);
    };

    return {
        icons,
        solidIcons,
        getIcon,
        getPaymentIcon,
        getStatusIcon,
    };
}
