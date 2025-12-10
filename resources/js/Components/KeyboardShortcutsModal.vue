<template>
    <BaseModal :show="show" max-width="2xl" @close="handleClose">
        <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white/20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">
                                Atajos de Teclado
                            </h3>
                            <p class="text-sm text-blue-100">
                                Aumenta tu productividad con estos atajos
                            </p>
                        </div>
                    </div>
                    <button
                        @click="handleClose"
                        class="p-2 hover:bg-white/10 rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                <!-- Navigation Section -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                        Navegación
                    </h4>
                    <div class="space-y-2">
                        <ShortcutItem
                            keys="Ctrl+K"
                            mac-keys="⌘K"
                            description="Búsqueda global"
                            icon="search"
                        />
                        <ShortcutItem
                            keys="Ctrl+P"
                            mac-keys="⌘P"
                            description="Ir al Punto de Venta (POS)"
                            icon="cash"
                        />
                    </div>
                </div>

                <!-- Actions Section -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                        Acciones Rápidas
                    </h4>
                    <div class="space-y-2">
                        <ShortcutItem
                            keys="Ctrl+E"
                            mac-keys="⌘E"
                            description="Registrar nuevo gasto"
                            icon="expense"
                        />
                    </div>
                </div>

                <!-- Interface Section -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                        Interfaz
                    </h4>
                    <div class="space-y-2">
                        <ShortcutItem
                            keys="Ctrl+B"
                            mac-keys="⌘B"
                            description="Colapsar/Expandir sidebar"
                            icon="sidebar"
                        />
                        <ShortcutItem
                            keys="Ctrl+D"
                            mac-keys="⌘D"
                            description="Cambiar modo oscuro/claro"
                            icon="theme"
                        />
                    </div>
                </div>

                <!-- POS Section -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                        Punto de Venta (POS)
                    </h4>
                    <div class="space-y-2">
                        <ShortcutItem
                            keys="/"
                            description="Buscar productos"
                            icon="search"
                        />
                        <ShortcutItem
                            keys="*"
                            description="Ingresar efectivo recibido"
                            icon="cash"
                        />
                        <ShortcutItem
                            keys="F1"
                            description="Pagar con Efectivo"
                            icon="cash"
                        />
                        <ShortcutItem
                            keys="F2"
                            description="Pagar con Tarjeta"
                            icon="cash"
                        />
                        <ShortcutItem
                            keys="F3"
                            description="Pagar con Transferencia"
                            icon="cash"
                        />
                        <ShortcutItem
                            keys="F9"
                            description="Guardar como pendiente"
                            icon="expense"
                        />
                        <ShortcutItem
                            keys="F10"
                            description="Completar y pagar venta"
                            icon="expense"
                        />
                        <ShortcutItem
                            keys="Ctrl+Enter"
                            mac-keys="⌘↵"
                            description="Completar y pagar venta (alternativo)"
                            icon="expense"
                        />
                    </div>
                </div>

                <!-- Help Section -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                        Ayuda
                    </h4>
                    <div class="space-y-2">
                        <ShortcutItem
                            keys="?"
                            description="Mostrar esta ayuda"
                            icon="help"
                        />
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-medium">Tip:</span> Presiona <kbd class="px-2 py-1 text-xs font-semibold bg-gray-200 dark:bg-gray-600 rounded">?</kbd> en cualquier momento para ver estos atajos
                    </p>
                    <button
                        @click="handleClose"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
                    >
                        Entendido
                    </button>
                </div>
            </div>
        </div>
    </BaseModal>
</template>

<script setup>
import BaseModal from '@/Components/Base/BaseModal.vue';
import ShortcutItem from '@/Components/KeyboardShortcutItem.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close']);

const handleClose = () => {
    emit('close');
};
</script>
