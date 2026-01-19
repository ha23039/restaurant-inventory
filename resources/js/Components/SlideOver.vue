<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: ''
    },
    subtitle: {
        type: String,
        default: ''
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg, full
        validator: (value) => ['sm', 'md', 'lg', 'full'].includes(value)
    },
    closable: {
        type: Boolean,
        default: true
    },
    preventClose: {
        type: Boolean,
        default: false
    },
    nested: {
        type: Boolean,
        default: false
    },
    swipeable: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['close', 'backdrop-click']);

// Touch gesture state for horizontal swipe
const touchStart = ref({ x: 0 });
const touchDelta = ref(0);
const isDragging = ref(false);

const handleTouchStart = (e) => {
    if (!props.swipeable || !props.closable) return;
    touchStart.value.x = e.touches[0].clientX;
    touchDelta.value = 0;
    isDragging.value = false;
};

const handleTouchMove = (e) => {
    if (!props.swipeable || !props.closable) return;
    const deltaX = e.touches[0].clientX - touchStart.value.x;

    // Only allow dragging right (positive delta)
    if (deltaX > 20) {
        isDragging.value = true;
        touchDelta.value = Math.min(deltaX, 300);
    }
};

const handleTouchEnd = () => {
    if (!props.swipeable || !props.closable) return;
    if (isDragging.value && touchDelta.value > 100) {
        handleClose();
    }
    touchDelta.value = 0;
    isDragging.value = false;
};

// Reset touch state when closing
watch(() => props.show, (newVal) => {
    if (!newVal) {
        touchDelta.value = 0;
        isDragging.value = false;
    }
});

const handleBackdropClick = () => {
    if (props.preventClose) {
        emit('backdrop-click');
    } else if (props.closable) {
        handleClose();
    }
};

const handleClose = () => {
    if (!props.closable) return;
    if (props.preventClose) {
        emit('backdrop-click');
    } else {
        emit('close');
    }
};

const sizeClasses = {
    sm: 'w-full md:max-w-md',      // 28rem (448px)
    md: 'w-full md:max-w-2xl',     // 42rem (672px)
    lg: 'w-full md:max-w-4xl',     // 56rem (896px)
    full: 'w-full'
};
</script>

<template>
    <!-- Backdrop -->
    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            @click="handleBackdropClick"
            :class="[
                'fixed inset-0',
                nested
                    ? 'z-[60] bg-gray-900/60'
                    : 'z-40 bg-gray-900/50 backdrop-blur-sm lg:backdrop-blur-none lg:bg-gray-900/70'
            ]"
        ></div>
    </Transition>

    <!-- Slide-over panel -->
    <Transition
        enter-active-class="transition-transform duration-200 ease-out"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition-transform duration-200 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="show"
            @touchstart="handleTouchStart"
            @touchmove.passive="handleTouchMove"
            @touchend="handleTouchEnd"
            :class="[
                'fixed top-0 right-0 h-full bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto transform-gpu',
                nested ? 'z-[70]' : 'z-50',
                sizeClasses[size]
            ]"
            :style="isDragging ? { transform: `translateX(${touchDelta}px)`, transition: 'none' } : {}"
        >
            <!-- Header -->
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <button
                            v-if="closable"
                            @click="handleClose"
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        >
                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ title }}
                            </h2>
                            <p v-if="subtitle" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ subtitle }}
                            </p>
                        </div>
                    </div>

                    <!-- Header actions slot -->
                    <slot name="header-actions"></slot>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <slot></slot>
            </div>

            <!-- Footer slot (sticky at bottom) -->
            <div v-if="$slots.footer" class="sticky bottom-0 z-10 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 px-6 py-4">
                <slot name="footer"></slot>
            </div>
        </div>
    </Transition>
</template>
