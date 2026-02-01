<template>
    <Head :title="`Venta #${sale?.sale_number || 'N/A'}`" />

    <AdminLayout>
        <template #header>
            <div class="space-y-3 md:space-y-0 md:flex md:items-center md:justify-between">
                <h2 class="font-semibold text-lg md:text-xl text-gray-800 dark:text-white leading-tight">
                    Detalle de Venta #{{ sale?.sale_number || 'N/A' }}
                    <span v-if="sale?.has_returns" class="ml-2 text-xs md:text-sm bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 px-2 py-1 rounded">
                        Con devoluciones
                    </span>
                </h2>
                <!-- Botones: Grid en móvil, flex en desktop -->
                <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
                    <Link :href="route('sales.index')" class="bg-gray-500 hover:bg-gray-600 active:bg-gray-700 text-white font-semibold py-3 sm:py-2.5 px-4 rounded-lg text-sm transition-all active:scale-95 text-center flex items-center justify-center">
                        <span class="hidden sm:inline mr-1">←</span> Volver
                    </Link>
                    <!-- Botón para cobrar/continuar órdenes pendientes -->
                    <Link
                        v-if="sale?.status === 'pendiente'"
                        :href="route('sales.pos', { load_sale: sale.id })"
                        class="bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-white font-semibold py-3 sm:py-2.5 px-4 rounded-lg flex items-center justify-center text-sm transition-all active:scale-95"
                    >
                        <svg class="w-5 h-5 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="hidden sm:inline">Cobrar</span>
                        <span class="sm:hidden">Cobrar</span>
                    </Link>
                    <!-- Botón para eliminar órdenes pendientes -->
                    <button
                        v-if="sale?.status === 'pendiente'"
                        @click="deletePendingSale"
                        class="bg-red-500 hover:bg-red-600 active:bg-red-700 text-white font-semibold py-3 sm:py-2.5 px-4 rounded-lg flex items-center justify-center text-sm transition-all active:scale-95"
                    >
                        <svg class="w-5 h-5 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span class="hidden sm:inline">Eliminar</span>
                        <span class="sm:hidden">Eliminar</span>
                    </button>
                    <Link
                        v-if="sale?.can_return"
                        :href="route('returns.create', { sale_id: sale.id })"
                        class="bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-semibold py-3 sm:py-2.5 px-4 rounded-lg text-sm transition-all active:scale-95 text-center flex items-center justify-center"
                    >
                        Devolver
                    </Link>
                    <Link :href="route('sales.pos')" class="bg-green-500 hover:bg-green-600 active:bg-green-700 text-white font-semibold py-3 sm:py-2.5 px-4 rounded-lg text-sm transition-all active:scale-95 text-center flex items-center justify-center">
                        <span class="hidden sm:inline">Nueva Venta</span>
                        <span class="sm:hidden">+ Venta</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Información General de la Venta -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                            <!-- Ticket Number -->
                            <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                                <div class="text-xl font-bold text-blue-600 dark:text-blue-400 break-all">
                                    #{{ sale?.sale_number || 'N/A' }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Número de Ticket</div>
                            </div>

                            <!-- Total Bruto -->
                            <div class="text-center p-4 bg-green-50 dark:bg-green-900/30 rounded-lg">
                                <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                                    ${{ formatPrice(sale?.total) }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Total Bruto</div>
                            </div>

                            <!-- Total Devuelto -->
                            <div class="text-center p-4 bg-red-50 dark:bg-red-900/30 rounded-lg">
                                <div class="text-3xl font-bold text-red-600 dark:text-red-400">
                                    ${{ formatPrice(sale?.total_returned || 0) }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Total Devuelto</div>
                            </div>

                            <!-- Total Neto -->
                            <div class="text-center p-4 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg">
                                <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                                    ${{ formatPrice(sale?.net_total || sale?.total) }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Total Neto</div>
                            </div>

                            <!-- Estado -->
                            <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg">
                                <div class="text-2xl font-bold">
                                    <span
                                        :class="{
                                            'text-green-600 dark:text-green-400': sale?.status === 'completada',
                                            'text-yellow-600 dark:text-yellow-400': sale?.status === 'pendiente',
                                            'text-red-600 dark:text-red-400': sale?.status === 'cancelada'
                                        }"
                                    >
                                        {{ getStatusIcon(sale?.status) }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 capitalize">{{ sale?.status || 'N/A' }}</div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Información del Cajero</h4>
                                <p class="text-gray-600 dark:text-gray-300">{{ sale?.user?.name || 'Usuario no disponible' }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ sale?.user?.email || 'Email no disponible' }}</p>
                            </div>

                            <!-- Cliente (si existe) -->
                            <div v-if="sale?.customer_name">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Cliente</h4>
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-300 font-medium">{{ sale.customer_name }}</p>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Fecha y Hora</h4>
                                <p class="text-gray-600 dark:text-gray-300">{{ formatDate(sale?.created_at) }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatTime(sale?.created_at) }}</p>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Mesa</h4>
                                <div v-if="sale?.table" class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    <div>
                                        <p class="text-gray-600 dark:text-gray-300 font-semibold">Mesa {{ sale.table.table_number }}</p>
                                        <p v-if="sale.table.name" class="text-sm text-gray-500 dark:text-gray-400">{{ sale.table.name }}</p>
                                        <p class="text-xs text-gray-400">Capacidad: {{ sale.table.capacity }} pers.</p>
                                    </div>
                                </div>
                                <div v-else class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <span class="text-gray-400">Para llevar</span>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Método de Pago</h4>
                                <div class="flex items-center space-x-2">
                                    <span class="text-2xl">{{ getPaymentIcon(sale?.payment_method) }}</span>
                                    <span class="text-gray-600 dark:text-gray-300 capitalize">{{ sale?.payment_method || 'N/A' }}</span>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Desglose Financiero</h4>
                                <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                                    <div class="flex justify-between">
                                        <span>Subtotal:</span>
                                        <span>${{ formatPrice(sale?.subtotal) }}</span>
                                    </div>
                                    <div v-if="sale?.discount > 0" class="flex justify-between text-red-600 dark:text-red-400">
                                        <span>Descuento:</span>
                                        <span>-${{ formatPrice(sale?.discount) }}</span>
                                    </div>
                                    <div v-if="sale?.tax > 0" class="flex justify-between">
                                        <span>Impuesto:</span>
                                        <span>+${{ formatPrice(sale?.tax) }}</span>
                                    </div>
                                    <div v-if="sale?.has_returns" class="flex justify-between text-red-600 dark:text-red-400 border-t border-gray-200 dark:border-gray-600 pt-1">
                                        <span>Devuelto:</span>
                                        <span>-${{ formatPrice(sale?.total_returned) }}</span>
                                    </div>
                                    <div class="flex justify-between font-bold border-t border-gray-200 dark:border-gray-600 pt-1" :class="{'text-emerald-600 dark:text-emerald-400': sale?.has_returns, 'text-green-600 dark:text-green-400': !sale?.has_returns}">
                                        <span>{{ sale?.has_returns ? 'Total Neto:' : 'Total:' }}</span>
                                        <span>${{ formatPrice(sale?.net_total || sale?.total) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notas de la Orden (si existen) -->
                        <div v-if="sale?.notes" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Notas de la Orden
                            </h4>
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ sale.notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sistema de Tickets e Impresión -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center">
                            Sistema de Tickets e Impresión
                        </h3>
                        
                        <!-- Botones de impresión MEJORADOS -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Comanda de Cocina -->
                            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="font-medium text-orange-900">Comanda de Cocina</h4>
                                        <p class="text-sm text-orange-700">Para preparación de platillos</p>
                                    </div>
                                </div>
                                
                                <div class="flex space-x-2">
                                    <button
                                        @click="previewKitchenOrder"
                                        class="flex-1 bg-orange-100 dark:bg-orange-900 hover:bg-orange-200 dark:hover:bg-orange-800 text-orange-800 dark:text-orange-200 font-medium py-2 px-3 rounded text-sm transition-colors inline-flex items-center justify-center gap-1"
                                    >
                                        <component :is="icons.view" class="w-4 h-4" />
                                        Vista Previa
                                    </button>
                                    <button
                                        @click="printKitchenTicket"
                                        :disabled="printingKitchen"
                                        class="flex-1 bg-orange-600 hover:bg-orange-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium py-2 px-3 rounded text-sm transition-colors"
                                    >
                                        {{ printingKitchen ? 'Enviando...' : 'Enviar' }}
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Ticket de Cliente -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="font-medium text-blue-900">Ticket de Cliente</h4>
                                        <p class="text-sm text-blue-700">Comprobante de compra</p>
                                    </div>
                                </div>
                                
                                <div class="flex space-x-2">
                                    <button
                                        @click="previewCustomerReceipt"
                                        class="flex-1 bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 text-blue-800 dark:text-blue-200 font-medium py-2 px-3 rounded text-sm transition-colors inline-flex items-center justify-center gap-1"
                                    >
                                        <component :is="icons.view" class="w-4 h-4" />
                                        Vista Previa
                                    </button>
                                    <button
                                        @click="printCustomerTicket"
                                        :disabled="printingCustomer"
                                        class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium py-2 px-3 rounded text-sm transition-colors"
                                    >
                                        {{ printingCustomer ? 'Imprimiendo...' : 'Imprimir' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Botón de Imprimir Ambos -->
                        <div class="mb-6">
                            <button
                                @click="printBothTickets"
                                :disabled="printingKitchen || printingCustomer"
                                class="w-full bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium py-3 px-4 rounded-lg transition-colors"
                            >
                                <svg class="w-5 h-5 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 4a3 3 0 00-3 3v6a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H5zm-1 9v-1h5v2H5a1 1 0 01-1-1zm7 1h4a1 1 0 001-1v-1h-5v2zm0-4h5V8h-5v2zM9 8H4v2h5V8z" clip-rule="evenodd"/>
                                </svg>
                                {{ (printingKitchen || printingCustomer) ? 'Procesando...' : 'Imprimir Ambos Tickets' }}
                            </button>
                        </div>
                        <!-- Información adicional -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <h5 class="font-medium text-gray-900 dark:text-white mb-2">Información del Ticket</h5>
                                    <ul class="space-y-1 text-gray-600 dark:text-gray-300">
                                        <li>• Ticket: #{{ sale?.sale_number }}</li>
                                        <li>• Fecha: {{ formatDate(sale?.created_at) }}</li>
                                        <li>• Cajero: {{ sale?.user?.name }}</li>
                                        <li>• Total: ${{ formatPrice(sale?.total) }}</li>
                                    </ul>
                                </div>
                                <div>
                                    <h5 class="font-medium text-gray-900 dark:text-white mb-2">Estado de Impresión</h5>
                                    <div class="space-y-2">
                                        <div class="flex items-center text-gray-600 dark:text-gray-300">
                                            <span class="w-3 h-3 bg-gray-300 dark:bg-gray-600 rounded-full mr-2"></span>
                                            <span>Los tickets se pueden reimprimir las veces necesarias</span>
                                        </div>
                                        <div class="flex items-center text-gray-600 dark:text-gray-300">
                                            <span class="w-3 h-3 bg-green-400 rounded-full mr-2"></span>
                                            <span>Sistema de impresión térmica activo</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Devoluciones (si existen) -->
                <div v-if="sale?.has_returns && sale?.returns?.length > 0" class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg mb-6 p-6">
                    <h3 class="text-lg font-semibold text-orange-900 dark:text-orange-300 mb-4 flex items-center">
                        Historial de Devoluciones
                        <span class="ml-2 bg-orange-200 dark:bg-orange-800 text-orange-800 dark:text-orange-200 px-2 py-1 rounded text-sm">
                            {{ sale.returns.length }} {{ sale.returns.length === 1 ? 'devolución' : 'devoluciones' }}
                        </span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div
                            v-for="return_item in sale.returns"
                            :key="return_item.id"
                            class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-orange-200 dark:border-orange-700"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white">#{{ return_item.return_number }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ formatDate(return_item.return_date) }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-red-600 dark:text-red-400">${{ formatPrice(return_item.total_returned) }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ return_item.items_count }} items</div>
                                </div>
                            </div>

                            <div class="text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Razón:</span>
                                    <span class="text-gray-900 dark:text-white">{{ getReturnReasonText(return_item.reason) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Reembolso:</span>
                                    <span class="text-gray-900 dark:text-white capitalize">{{ return_item.refund_method }}</span>
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-t border-orange-100 dark:border-orange-700">
                                <Link
                                    :href="route('returns.show', return_item.id)"
                                    class="text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 text-sm font-medium"
                                >
                                    Ver detalle de devolución
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items de la Venta -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center justify-between">
                            <span>Productos Vendidos</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ getTotalItems() }} productos | {{ getTotalQuantity() }} unidades
                            </span>
                        </h3>

                        <div v-if="!sale?.sale_items || sale.sale_items.length === 0">
                            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                                <component :is="icons.package" class="w-16 h-16 mx-auto mb-4 text-gray-400 dark:text-gray-600" />
                                <div class="text-lg font-medium">No se encontraron productos</div>
                                <div class="text-sm">Esta venta no tiene productos asociados</div>
                            </div>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="(item, index) in sale.sale_items"
                                :key="item.id || index"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow"
                                :class="{'border-orange-200 dark:border-orange-700 bg-orange-50 dark:bg-orange-900/20': hasItemReturns(item)}"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <span
                                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full"
                                                    :class="{
                                                        'bg-orange-100 dark:bg-orange-900/40 text-orange-600 dark:text-orange-400': item.product_type === 'menu' || item.product_type === 'variant',
                                                        'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400': item.product_type === 'simple',
                                                        'bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400': item.product_type === 'free',
                                                        'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400': item.product_type === 'combo'
                                                    }"
                                                >
                                                    <component
                                                        :is="item.product_type === 'menu' || item.product_type === 'variant' ? icons.menu : item.product_type === 'simple' ? icons.product : item.product_type === 'combo' ? icons.menu : icons.add"
                                                        class="w-5 h-5"
                                                    />
                                                </span>
                                            </div>

                                            <div class="flex-1">
                                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                    {{ getProductName(item) }}
                                                </h4>

                                                <p class="text-sm text-gray-600 dark:text-gray-400" v-if="getProductDescription(item)">
                                                    {{ getProductDescription(item) }}
                                                </p>

                                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                        :class="{
                                                            'bg-orange-100 dark:bg-orange-900/40 text-orange-800 dark:text-orange-300': item.product_type === 'menu' || item.product_type === 'variant',
                                                            'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300': item.product_type === 'simple',
                                                            'bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-300': item.product_type === 'free',
                                                            'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-300': item.product_type === 'combo'
                                                        }"
                                                    >
                                                        {{ item.product_type === 'combo' ? 'Combo' : item.product_type === 'variant' ? 'Variante' : item.product_type === 'menu' ? 'Platillo del Menú' : item.product_type === 'simple' ? 'Producto Individual' : 'Venta Libre' }}
                                                    </span>

                                                    <span
                                                        v-if="hasItemReturns(item)"
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300"
                                                    >
                                                        {{ item.quantity_returned || 0 }} devueltos
                                                    </span>

                                                    <span
                                                        v-if="canReturnItem(item)"
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300"
                                                    >
                                                        {{ item.can_return_quantity || item.quantity }} disponibles
                                                    </span>
                                                </div>

                                                <div v-if="item.product_type === 'menu' && item.menu_item?.recipes" class="mt-2">
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Ingredientes utilizados:</p>
                                                    <div class="flex flex-wrap gap-1">
                                                        <span
                                                            v-for="recipe in item.menu_item.recipes"
                                                            :key="recipe.id"
                                                            class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300"
                                                        >
                                                            {{ recipe.product?.name || 'Producto no disponible' }}
                                                            <span class="ml-1 text-gray-500 dark:text-gray-400">
                                                                ({{ (recipe.quantity_needed * item.quantity).toFixed(2) }} {{ recipe.product?.unit_type || 'unidades' }})
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Componentes del combo -->
                                                <div v-if="item.product_type === 'combo' && item.components_detail?.length" class="mt-2">
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Contenido del combo:</p>
                                                    <div class="space-y-1">
                                                        <div
                                                            v-for="(comp, idx) in item.components_detail"
                                                            :key="idx"
                                                            class="flex items-center gap-2 text-xs"
                                                        >
                                                            <span
                                                                class="inline-flex items-center justify-center w-4 h-4 rounded-full"
                                                                :class="comp.type === 'fixed' ? 'bg-green-100 dark:bg-green-900/30 text-green-600' : 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600'"
                                                            >
                                                                <svg v-if="comp.type === 'fixed'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                                <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                                                </svg>
                                                            </span>
                                                            <span class="text-gray-700 dark:text-gray-300">
                                                                <span v-if="comp.componentName" class="font-medium">{{ comp.componentName }}:</span>
                                                                {{ comp.name }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-right ml-6">
                                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                            × {{ item.quantity }}
                                        </div>
                                        <div v-if="hasItemReturns(item)" class="text-sm text-red-600 dark:text-red-400">
                                            - {{ item.quantity_returned || 0 }} devueltos
                                        </div>
                                        <div v-if="canReturnItem(item)" class="text-sm text-green-600 dark:text-green-400">
                                            {{ item.can_return_quantity || item.quantity }} disponibles
                                        </div>

                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            ${{ formatPrice(item.unit_price) }} c/u
                                        </div>
                                        <div class="text-lg font-bold text-green-600 dark:text-green-400 mt-1">
                                            ${{ formatPrice(item.total_price || (item.unit_price * item.quantity)) }}
                                        </div>

                                        <div v-if="hasItemReturns(item)" class="text-sm text-red-600 dark:text-red-400 mt-1">
                                            -${{ formatPrice((item.quantity_returned || 0) * item.unit_price) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen Final -->
                            <div class="mt-8 pt-6 border-t-2 border-gray-300 dark:border-gray-600 space-y-2">
                                <div class="flex justify-between items-center text-lg text-gray-900 dark:text-white">
                                    <span>Total de {{ getTotalItems() }} productos ({{ getTotalQuantity() }} unidades):</span>
                                    <span class="text-green-600 dark:text-green-400 font-bold">${{ formatPrice(sale?.total) }}</span>
                                </div>

                                <div v-if="sale?.has_returns" class="flex justify-between items-center text-lg text-red-600 dark:text-red-400">
                                    <span>Total devuelto:</span>
                                    <span class="font-bold">-${{ formatPrice(sale?.total_returned) }}</span>
                                </div>

                                <div v-if="sale?.has_returns" class="flex justify-between items-center text-xl font-bold border-t border-gray-300 dark:border-gray-600 pt-2" :class="{'text-emerald-600 dark:text-emerald-400': sale?.has_returns}">
                                    <span>Total neto:</span>
                                    <span>${{ formatPrice(sale?.net_total || sale?.total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="mt-6 flex justify-center space-x-4">
                    <Link 
                        :href="route('sales.index')" 
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition-colors"
                    >
                        ← Volver al Historial
                    </Link>
                    
                    <Link 
                        v-if="sale?.can_return"
                        :href="route('returns.create', { sale_id: sale.id })" 
                        class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-3 px-6 rounded-lg transition-colors"
                    >
                        Procesar Devolución
                    </Link>
                    
                    <Link 
                        :href="route('sales.pos')" 
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition-colors"
                    >
                        Nueva Venta
                    </Link>
                </div>
            </div>
        </div>

        <!-- Sistema de Notificaciones -->
        <div v-if="notification" class="fixed top-4 right-4 z-50">
            <div 
                class="px-6 py-4 rounded-lg shadow-lg transition-all duration-300 max-w-sm border"
                :class="{
                    'bg-green-50 border-green-200 text-green-800': notification.type === 'success',
                    'bg-red-50 border-red-200 text-red-800': notification.type === 'error',
                    'bg-blue-50 border-blue-200 text-blue-800': notification.type === 'info',
                    'bg-yellow-50 border-yellow-200 text-yellow-800': notification.type === 'warning'
                }"
            >
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-3">
                        <svg v-if="notification.type === 'success'" class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <svg v-else-if="notification.type === 'error'" class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <svg v-else-if="notification.type === 'warning'" class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <svg v-else class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="font-medium">{{ notification.message }}</div>
                </div>
            </div>
        </div>
        <!-- Agregar al final del template, antes de </AdminLayout> -->

<!-- Modal de Vista Previa de Comanda de Cocina -->
<div v-if="showKitchenPreview" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-11/12 max-w-md max-h-96 flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                </svg>
                Vista Previa - Comanda de Cocina
            </h3>
            <button
                @click="showKitchenPreview = false"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
            <div v-if="loadingPreview" class="flex items-center justify-center py-8">
                <svg class="animate-spin h-8 w-8 text-orange-600 dark:text-orange-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2 text-gray-600 dark:text-gray-400">Generando vista previa...</span>
            </div>

            <pre v-else class="whitespace-pre-wrap text-sm font-mono bg-gray-50 dark:bg-gray-900 p-3 rounded border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 leading-tight">{{ kitchenPreviewContent }}</pre>
        </div>

        <div class="flex space-x-3 p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <button
                @click="showKitchenPreview = false"
                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded"
            >
                Cerrar
            </button>
            <button
                @click="printKitchenFromPreview"
                :disabled="printingKitchen"
                class="flex-1 bg-orange-600 hover:bg-orange-700 disabled:bg-gray-400 text-white font-medium py-2 px-4 rounded"
            >
                {{ printingKitchen ? 'Enviando...' : 'Enviar a Cocina' }}
            </button>
        </div>
    </div>
</div>

<!-- Modal de Vista Previa de Ticket de Cliente -->
<div v-if="showCustomerPreview" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-11/12 max-w-md max-h-96 flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
                Vista Previa - Ticket de Cliente
            </h3>
            <button
                @click="showCustomerPreview = false"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
            <div v-if="loadingPreview" class="flex items-center justify-center py-8">
                <svg class="animate-spin h-8 w-8 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2 text-gray-600 dark:text-gray-400">Generando vista previa...</span>
            </div>

            <pre v-else class="whitespace-pre-wrap text-sm font-mono bg-gray-50 dark:bg-gray-900 p-3 rounded border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 leading-tight">{{ customerPreviewContent }}</pre>
        </div>

        <div class="flex space-x-3 p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <button
                @click="showCustomerPreview = false"
                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded"
            >
                Cerrar
            </button>
            <button
                @click="printCustomerFromPreview"
                :disabled="printingCustomer"
                class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium py-2 px-4 rounded"
            >
                {{ printingCustomer ? 'Imprimiendo...' : 'Imprimir Ticket' }}
            </button>
        </div>
    </div>
</div>
    </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useIcons } from '@/composables/useIcons';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

// Icons & Dialogs
const { icons } = useIcons();
const { confirm } = useConfirmDialog();

// Props
const props = defineProps({
    sale: {
        type: Object,
        required: true
    }
});

// Variables para el sistema de tickets (EXISTENTES)
const printingKitchen = ref(false);
const printingCustomer = ref(false);
const notification = ref(null);

// Variables para modales de vista previa (NUEVAS)
const showKitchenPreview = ref(false);
const showCustomerPreview = ref(false);
const loadingPreview = ref(false);
const kitchenPreviewContent = ref('');
const customerPreviewContent = ref('');

// Función para eliminar orden pendiente
const deletePendingSale = async () => {
    if (!props.sale || props.sale.status !== 'pendiente') return;

    const confirmed = await confirm({
        title: '¿Eliminar orden pendiente?',
        message: `Se eliminará permanentemente la orden #${props.sale.sale_number}. Esta acción no se puede deshacer.`,
        confirmText: 'Eliminar',
        type: 'danger'
    });

    if (confirmed) {
        router.delete(route('sales.destroy', props.sale.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Redirigir a la lista de ventas después de eliminar
            }
        });
    }
};

//  MÉTODOS PROFESIONALES CON INERTIA (EXISTENTES - SIN CAMBIOS)
const printKitchenTicket = () => {
    if (!props.sale) return;
    
    printingKitchen.value = true;
    
    router.post(`/tickets/kitchen/${props.sale.id}`, {}, {
        onSuccess: (page) => {
            showNotification('Comanda enviada a cocina exitosamente', 'success');
            console.log('✅ Ticket de cocina enviado:', page);
        },
        onError: (errors) => {
            console.error('❌ Error enviando comanda:', errors);
            if (errors.message) {
                showNotification(errors.message, 'error');
            } else if (errors.errors) {
                const errorMessages = Object.values(errors.errors).flat();
                showNotification(`Error: ${errorMessages.join(', ')}`, 'error');
            } else {
                showNotification('Error al enviar comanda a cocina', 'error');
            }
        },
        onFinish: () => {
            printingKitchen.value = false;
        }
    });
};

const printCustomerTicket = () => {
    if (!props.sale) return;
    
    printingCustomer.value = true;
    
    router.post(`/tickets/customer/${props.sale.id}`, {}, {
        onSuccess: (page) => {
            showNotification('Ticket impreso exitosamente', 'success');
            console.log('✅ Ticket de cliente impreso:', page);
        },
        onError: (errors) => {
            console.error('❌ Error imprimiendo ticket:', errors);
            if (errors.message) {
                showNotification(errors.message, 'error');
            } else if (errors.errors) {
                const errorMessages = Object.values(errors.errors).flat();
                showNotification(`Error: ${errorMessages.join(', ')}`, 'error');
            } else {
                showNotification('Error al imprimir ticket de cliente', 'error');
            }
        },
        onFinish: () => {
            printingCustomer.value = false;
        }
    });
};

const printBothTickets = async () => {
    // Imprimir comanda primero
    printKitchenTicket();
    
    // Esperar un poco y luego imprimir ticket de cliente
    setTimeout(() => {
        printCustomerTicket();
    }, 1500);
};

// 👁️ FUNCIONES DE VISTA PREVIA (NUEVAS)
const previewKitchenOrder = async () => {
    if (!props.sale) return;
    
    showKitchenPreview.value = true;
    loadingPreview.value = true;
    kitchenPreviewContent.value = '';
    
    try {
        const response = await fetch(`/tickets/preview/kitchen/${props.sale.id}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            kitchenPreviewContent.value = result.content;
        } else {
            showNotification('Error generando vista previa de comanda', 'error');
            showKitchenPreview.value = false;
        }
        
    } catch (error) {
        console.error('Error obteniendo vista previa de cocina:', error);
        showNotification('Error de conexión al generar vista previa', 'error');
        showKitchenPreview.value = false;
    }
    
    loadingPreview.value = false;
};

const previewCustomerReceipt = async () => {
    if (!props.sale) return;
    
    showCustomerPreview.value = true;
    loadingPreview.value = true;
    customerPreviewContent.value = '';
    
    try {
        const response = await fetch(`/tickets/preview/customer/${props.sale.id}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            customerPreviewContent.value = result.content;
        } else {
            showNotification('Error generando vista previa de ticket', 'error');
            showCustomerPreview.value = false;
        }
        
    } catch (error) {
        console.error('Error obteniendo vista previa de cliente:', error);
        showNotification('Error de conexión al generar vista previa', 'error');
        showCustomerPreview.value = false;
    }
    
    loadingPreview.value = false;
};

// 🖨️ FUNCIONES DE IMPRESIÓN DESDE MODAL (NUEVAS)
const printKitchenFromPreview = () => {
    showKitchenPreview.value = false;
    printKitchenTicket();
};

const printCustomerFromPreview = () => {
    showCustomerPreview.value = false;
    printCustomerTicket();
};

// 🔧 FUNCIÓN PARA OBTENER CSRF TOKEN (NUEVA)
const getCsrfToken = () => {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (!token) {
        console.warn('⚠️ CSRF token no encontrado en el meta tag');
        return '';
    }
    return token.getAttribute('content');
};

const showNotification = (message, type = 'info') => {
    notification.value = { message, type };
    
    setTimeout(() => {
        notification.value = null;
    }, 5000);
};

// Funciones auxiliares existentes (SIN CAMBIOS)
const formatPrice = (price) => {
    return parseFloat(price || 0).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return 'Fecha no disponible';
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatTime = (date) => {
    if (!date) return 'Hora no disponible';
    return new Date(date).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getProductName = (item) => {
    if (item.product_type === 'free' && item.free_sale) {
        return item.free_sale.name;
    } else if (item.product_type === 'combo' && item.combo) {
        return item.combo.name;
    } else if (item.product_type === 'variant' && item.menu_item_variant) {
        const parentName = item.menu_item_variant.menu_item?.name || '';
        return parentName ? `${parentName} - ${item.menu_item_variant.variant_name}` : item.menu_item_variant.variant_name;
    } else if (item.product_type === 'menu' && item.menu_item) {
        return item.menu_item.name;
    } else if (item.product_type === 'simple' && item.simple_product) {
        return item.simple_product.name;
    }
    // Fallback: use product_name if available
    if (item.product_name) {
        return item.product_name;
    }
    if (item.variant_name) {
        return `${item.product_name || 'Producto'} - ${item.variant_name}`;
    }
    return 'Producto';
};

const getProductDescription = (item) => {
    if (item.product_type === 'free') {
        return null;
    } else if (item.product_type === 'combo' && item.combo) {
        return item.combo.description;
    } else if (item.product_type === 'menu' && item.menu_item) {
        return item.menu_item.description;
    } else if (item.product_type === 'simple' && item.simple_product) {
        return item.simple_product.description;
    }
    return null;
};

const getStatusIcon = (status) => {
    const icons = {
        'completada': '✅',
        'pendiente': '⏳',
        'cancelada': '❌'
    };
    return icons[status] || '📋';
};

const getPaymentIcon = (method) => {
    const icons = {
        'efectivo': '💵',
        'tarjeta': '💳',
        'transferencia': '📱',
        'mixto': '🔄'
    };
    return icons[method] || '💰';
};

const getTotalItems = () => {
    if (!props.sale?.sale_items) return 0;
    return props.sale.sale_items.length;
};

const getTotalQuantity = () => {
    if (!props.sale?.sale_items) return 0;
    return props.sale.sale_items.reduce((total, item) => total + item.quantity, 0);
};

// Funciones para devoluciones (EXISTENTES - SIN CAMBIOS)
const hasItemReturns = (item) => {
    return (item.quantity_returned && item.quantity_returned > 0);
};

const canReturnItem = (item) => {
    return item.can_return && (item.can_return_quantity && item.can_return_quantity > 0);
};

const getReturnReasonText = (reason) => {
    const reasons = {
        'defective': 'Producto defectuoso',
        'wrong_order': 'Orden incorrecta',
        'customer_request': 'Solicitud del cliente',
        'error': 'Error del sistema',
        'other': 'Otra razón'
    };
    return reasons[reason] || 'Razón no especificada';
};

// Log para debug (EXISTENTE - SIN CAMBIOS)
console.log('🧪 SaleDetail Professional - Sale object:', {
    sale: props.sale,
    has_returns: props.sale?.has_returns,
    total_returned: props.sale?.total_returned,
    can_return: props.sale?.can_return,
    returns_count: props.sale?.returns?.length || 0
});
</script>

<style scoped>
/* Animaciones profesionales */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification-enter-active {
    animation: slideIn 0.3s ease-out;
}

.notification-leave-active {
    animation: slideIn 0.3s ease-in reverse;
}

/* Estados de botones */
button:disabled {
    transform: none !important;
    box-shadow: none !important;
}

/* Responsividad mejorada */
@media (max-width: 768px) {
    .grid-cols-3 {
        grid-template-columns: 1fr;
    }
    
    .flex-wrap > * {
        flex: 1 1 100%;
        min-width: unset;
    }
}
</style>
