<script setup>
import { computed } from 'vue';

const props = defineProps({
    currentPage: {
        type: Number,
        required: true,
        validator: (value) => value >= 1,
    },
    totalPages: {
        type: Number,
        required: true,
        validator: (value) => value >= 0,
    },
    totalItems: {
        type: Number,
        default: 0,
    },
    perPage: {
        type: Number,
        default: 10,
    },
    maxVisibleButtons: {
        type: Number,
        default: 5,
        validator: (value) => value >= 3 && value % 2 === 1, // Must be odd number >= 3
    },
    showFirstLast: {
        type: Boolean,
        default: true,
    },
    showInfo: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['page-change']);

const visiblePages = computed(() => {
    const pages = [];
    const half = Math.floor(props.maxVisibleButtons / 2);

    let start = Math.max(1, props.currentPage - half);
    let end = Math.min(props.totalPages, props.currentPage + half);

    // Adjust if we're near the beginning
    if (props.currentPage <= half) {
        end = Math.min(props.totalPages, props.maxVisibleButtons);
    }

    // Adjust if we're near the end
    if (props.currentPage > props.totalPages - half) {
        start = Math.max(1, props.totalPages - props.maxVisibleButtons + 1);
    }

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    return pages;
});

const startItem = computed(() => {
    return (props.currentPage - 1) * props.perPage + 1;
});

const endItem = computed(() => {
    return Math.min(props.currentPage * props.perPage, props.totalItems);
});

const isFirstPage = computed(() => props.currentPage === 1);
const isLastPage = computed(() => props.currentPage === props.totalPages);

const goToPage = (page) => {
    if (page >= 1 && page <= props.totalPages && page !== props.currentPage) {
        emit('page-change', page);
    }
};

const goToFirstPage = () => goToPage(1);
const goToLastPage = () => goToPage(props.totalPages);
const goToPreviousPage = () => goToPage(props.currentPage - 1);
const goToNextPage = () => goToPage(props.currentPage + 1);

const buttonClasses = (active = false) => {
    const base = 'relative inline-flex items-center px-4 py-2 text-sm font-medium border';

    if (active) {
        return `${base} z-10 bg-blue-600 border-blue-600 text-white`;
    }

    return `${base} bg-white border-gray-300 text-gray-700 hover:bg-gray-50`;
};

const navButtonClasses = (disabled = false) => {
    const base = 'relative inline-flex items-center px-2 py-2 text-sm font-medium border border-gray-300 rounded-md';

    if (disabled) {
        return `${base} bg-gray-100 text-gray-400 cursor-not-allowed`;
    }

    return `${base} bg-white text-gray-700 hover:bg-gray-50`;
};
</script>

<template>
    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
        <!-- Mobile View -->
        <div class="flex flex-1 justify-between sm:hidden">
            <button
                :class="navButtonClasses(isFirstPage)"
                :disabled="isFirstPage"
                @click="goToPreviousPage"
            >
                Anterior
            </button>
            <button
                :class="navButtonClasses(isLastPage)"
                :disabled="isLastPage"
                @click="goToNextPage"
            >
                Siguiente
            </button>
        </div>

        <!-- Desktop View -->
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <!-- Info Text -->
            <div v-if="showInfo">
                <p class="text-sm text-gray-700">
                    Mostrando
                    <span class="font-medium">{{ startItem }}</span>
                    a
                    <span class="font-medium">{{ endItem }}</span>
                    de
                    <span class="font-medium">{{ totalItems }}</span>
                    resultados
                </p>
            </div>

            <!-- Pagination Buttons -->
            <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    <!-- First Page -->
                    <button
                        v-if="showFirstLast"
                        :class="navButtonClasses(isFirstPage)"
                        :disabled="isFirstPage"
                        @click="goToFirstPage"
                    >
                        <span class="sr-only">Primera</span>
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fill-rule="evenodd"
                                d="M15.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 010 1.414zm-6 0a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L5.414 10l4.293 4.293a1 1 0 010 1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>

                    <!-- Previous Page -->
                    <button
                        :class="navButtonClasses(isFirstPage)"
                        :disabled="isFirstPage"
                        @click="goToPreviousPage"
                    >
                        <span class="sr-only">Anterior</span>
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fill-rule="evenodd"
                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>

                    <!-- Page Numbers -->
                    <button
                        v-for="page in visiblePages"
                        :key="page"
                        :class="buttonClasses(page === currentPage)"
                        @click="goToPage(page)"
                    >
                        {{ page }}
                    </button>

                    <!-- Next Page -->
                    <button
                        :class="navButtonClasses(isLastPage)"
                        :disabled="isLastPage"
                        @click="goToNextPage"
                    >
                        <span class="sr-only">Siguiente</span>
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>

                    <!-- Last Page -->
                    <button
                        v-if="showFirstLast"
                        :class="navButtonClasses(isLastPage)"
                        :disabled="isLastPage"
                        @click="goToLastPage"
                    >
                        <span class="sr-only">Última</span>
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fill-rule="evenodd"
                                d="M10.293 15.707a1 1 0 010-1.414L14.586 10l-4.293-4.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z M4.293 15.707a1 1 0 010-1.414L8.586 10 4.293 5.707a1 1 0 011.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</template>
