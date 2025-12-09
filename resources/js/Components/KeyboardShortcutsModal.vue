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
import { computed } from 'vue';
import BaseModal from '@/Components/Base/BaseModal.vue';

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

// Detect if user is on Mac
const isMac = computed(() => {
    return typeof navigator !== 'undefined' && navigator.platform.toUpperCase().indexOf('MAC') >= 0;
});
</script>

<script>
// Shortcut Item Component
export const ShortcutItem = {
    name: 'ShortcutItem',
    props: {
        keys: {
            type: String,
            required: true
        },
        macKeys: {
            type: String,
            default: null
        },
        description: {
            type: String,
            required: true
        },
        icon: {
            type: String,
            default: 'default'
        }
    },
    computed: {
        isMac() {
            return typeof navigator !== 'undefined' && navigator.platform.toUpperCase().indexOf('MAC') >= 0;
        },
        displayKeys() {
            return this.isMac && this.macKeys ? this.macKeys : this.keys;
        },
        keyArray() {
            // Split by + to handle combinations like Ctrl+K
            return this.displayKeys.split('+');
        },
        iconPath() {
            const icons = {
                search: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
                cash: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                expense: 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z',
                sidebar: 'M4 6h16M4 12h16M4 18h16',
                theme: 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z',
                help: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                default: 'M13 10V3L4 14h7v7l9-11h-7z'
            };
            return icons[this.icon] || icons.default;
        }
    },
    template: `
        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="iconPath" />
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ description }}</span>
            </div>
            <div class="flex items-center space-x-1">
                <kbd
                    v-for="(key, index) in keyArray"
                    :key="index"
                    class="px-2 py-1 text-xs font-semibold bg-white dark:bg-gray-600 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-500 rounded shadow-sm"
                >
                    {{ key }}
                </kbd>
            </div>
        </div>
    `
};
</script>
