import { ref, computed } from 'vue';

export function usePagination(items, itemsPerPage = 10) {
    const currentPage = ref(1);
    const perPage = ref(itemsPerPage);

    const totalPages = computed(() => {
        return Math.ceil(items.value.length / perPage.value);
    });

    const paginatedItems = computed(() => {
        const start = (currentPage.value - 1) * perPage.value;
        const end = start + perPage.value;
        return items.value.slice(start, end);
    });

    const hasNextPage = computed(() => {
        return currentPage.value < totalPages.value;
    });

    const hasPreviousPage = computed(() => {
        return currentPage.value > 1;
    });

    const goToPage = (page) => {
        if (page >= 1 && page <= totalPages.value) {
            currentPage.value = page;
        }
    };

    const nextPage = () => {
        if (hasNextPage.value) {
            currentPage.value++;
        }
    };

    const previousPage = () => {
        if (hasPreviousPage.value) {
            currentPage.value--;
        }
    };

    const firstPage = () => {
        currentPage.value = 1;
    };

    const lastPage = () => {
        currentPage.value = totalPages.value;
    };

    const setPerPage = (newPerPage) => {
        perPage.value = newPerPage;
        currentPage.value = 1; // Reset to first page
    };

    return {
        currentPage,
        perPage,
        totalPages,
        paginatedItems,
        hasNextPage,
        hasPreviousPage,
        goToPage,
        nextPage,
        previousPage,
        firstPage,
        lastPage,
        setPerPage
    };
}
