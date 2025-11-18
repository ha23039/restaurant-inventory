# Data Components

Componentes especializados para visualización y manipulación de datos.

## DataTable

Tabla completa con sorting, estados de carga, filas seleccionables y slots personalizables.

### Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| columns | Array | required | Definición de columnas: [{ key, label, sortable?, align? }] |
| data | Array | [] | Array de datos a mostrar |
| loading | Boolean | false | Estado de carga |
| sortable | Boolean | true | Habilitar sorting global |
| hoverable | Boolean | true | Efecto hover en filas |
| striped | Boolean | false | Filas alternadas con fondo |
| emptyMessage | String | 'No hay datos disponibles' | Mensaje cuando no hay datos |
| rowKey | String | 'id' | Propiedad única para key de filas |

### Events

| Event | Payload | Descripción |
|-------|---------|-------------|
| row-click | { row, index } | Click en una fila |
| sort | { key, order } | Cambio de sorting |

### Slots

| Slot | Props | Descripción |
|------|-------|-------------|
| cell-{key} | { row, column, value } | Personalizar celda específica |

### Uso Básico

```vue
<script setup>
const columns = [
    { key: 'id', label: 'ID' },
    { key: 'name', label: 'Nombre' },
    { key: 'stock', label: 'Stock', align: 'right' },
    { key: 'price', label: 'Precio', align: 'right' },
    { key: 'actions', label: 'Acciones', sortable: false },
];

const products = ref([
    { id: 1, name: 'Coca Cola', stock: 50, price: 15 },
    { id: 2, name: 'Pepsi', stock: 30, price: 14 },
]);

const handleRowClick = ({ row }) => {
    console.log('Clicked:', row);
};
</script>

<template>
    <DataTable
        :columns="columns"
        :data="products"
        :sortable="true"
        :hoverable="true"
        @row-click="handleRowClick"
    />
</template>
```

### Uso Avanzado con Slots

```vue
<template>
    <DataTable
        :columns="columns"
        :data="products"
        :loading="isLoading"
    >
        <!-- Personalizar celda de stock -->
        <template #cell-stock="{ value }">
            <BaseBadge
                :variant="value > 10 ? 'success' : 'danger'"
            >
                {{ value }} unidades
            </BaseBadge>
        </template>

        <!-- Personalizar celda de precio -->
        <template #cell-price="{ value }">
            <span class="font-semibold">
                ${{ value.toFixed(2) }}
            </span>
        </template>

        <!-- Personalizar celda de acciones -->
        <template #cell-actions="{ row }">
            <div class="flex gap-2">
                <BaseButton size="sm" @click="edit(row)">
                    Editar
                </BaseButton>
                <BaseButton
                    size="sm"
                    variant="danger"
                    @click="deleteItem(row)"
                >
                    Eliminar
                </BaseButton>
            </div>
        </template>
    </DataTable>
</template>
```

### Sorting

El sorting es automático para columnas con datos primitivos. Soporta:

- Strings (alfabético)
- Numbers (numérico)
- Nested keys: `user.name`, `category.parent.name`

```vue
<script setup>
const columns = [
    { key: 'name', label: 'Nombre' }, // Sortable por default
    { key: 'user.name', label: 'Creado por' }, // Nested key
    { key: 'actions', label: 'Acciones', sortable: false }, // No sortable
];
</script>
```

### Estados

```vue
<template>
    <!-- Loading state -->
    <DataTable
        :columns="columns"
        :data="products"
        :loading="true"
    />

    <!-- Empty state -->
    <DataTable
        :columns="columns"
        :data="[]"
        empty-message="No se encontraron productos"
    />
</template>
```

---

## Pagination

Paginación completa con first/last/prev/next y números de página.

### Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| currentPage | Number | required | Página actual (1-indexed) |
| totalPages | Number | required | Total de páginas |
| totalItems | Number | 0 | Total de items (para info text) |
| perPage | Number | 10 | Items por página |
| maxVisibleButtons | Number | 5 | Máximo de botones de página visibles (debe ser impar) |
| showFirstLast | Boolean | true | Mostrar botones first/last |
| showInfo | Boolean | true | Mostrar texto informativo |

### Events

| Event | Payload | Descripción |
|-------|---------|-------------|
| page-change | Number | Nueva página seleccionada |

### Uso

```vue
<script setup>
import { ref, computed } from 'vue';
import { usePagination } from '@/composables';

const products = ref([/* ... muchos productos ... */]);
const currentPage = ref(1);
const perPage = 10;

const { paginatedItems, totalPages } = usePagination(products, {
    page: currentPage,
    perPage,
});

const handlePageChange = (page) => {
    currentPage.value = page;
};
</script>

<template>
    <div>
        <!-- Mostrar items paginados -->
        <DataTable
            :columns="columns"
            :data="paginatedItems"
        />

        <!-- Controles de paginación -->
        <Pagination
            :current-page="currentPage"
            :total-pages="totalPages"
            :total-items="products.length"
            :per-page="perPage"
            @page-change="handlePageChange"
        />
    </div>
</template>
```

### Responsive

- Mobile: Muestra solo prev/next
- Desktop: Muestra navegación completa con números de página

---

## SearchBar

Barra de búsqueda con debounce, loading state y botón clear.

### Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| modelValue | String | '' | Valor de búsqueda (v-model) |
| placeholder | String | 'Buscar...' | Placeholder |
| debounce | Number | 300 | Delay de debounce en ms |
| loading | Boolean | false | Mostrar spinner de carga |
| disabled | Boolean | false | Deshabilitar input |
| showClear | Boolean | true | Mostrar botón clear |

### Events

| Event | Payload | Descripción |
|-------|---------|-------------|
| update:modelValue | String | Valor actualizado |
| search | String | Búsqueda debouncedada |
| clear | - | Se limpió el input |

### Uso Básico

```vue
<script setup>
const searchQuery = ref('');
const isSearching = ref(false);
const products = ref([]);

const handleSearch = async (query) => {
    if (!query) {
        products.value = allProducts.value;
        return;
    }

    isSearching.value = true;

    try {
        const response = await fetch(`/api/products/search?q=${query}`);
        products.value = await response.json();
    } finally {
        isSearching.value = false;
    }
};
</script>

<template>
    <SearchBar
        v-model="searchQuery"
        :loading="isSearching"
        placeholder="Buscar productos..."
        @search="handleSearch"
    />

    <DataTable :columns="columns" :data="products" />
</template>
```

### Búsqueda Local con Composable

```vue
<script setup>
import { useDebounce } from '@/composables';

const searchQuery = ref('');
const allProducts = ref([/* ... */]);

const debouncedSearch = useDebounce(searchQuery, 300);

const filteredProducts = computed(() => {
    if (!debouncedSearch.value) return allProducts.value;

    const query = debouncedSearch.value.toLowerCase();
    return allProducts.value.filter(p =>
        p.name.toLowerCase().includes(query) ||
        p.description.toLowerCase().includes(query)
    );
});
</script>

<template>
    <SearchBar
        v-model="searchQuery"
        placeholder="Filtrar productos..."
    />

    <DataTable :columns="columns" :data="filteredProducts" />
</template>
```

---

## FilterDropdown

Dropdown de filtros con selección múltiple y estado visual.

### Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| modelValue | String/Number/Array | null | Valor seleccionado |
| options | Array | required | Opciones: [{ value, label, disabled? }] |
| label | String | 'Filtrar' | Label del botón |
| multiple | Boolean | false | Permitir selección múltiple |
| placeholder | String | 'Seleccionar filtro' | Placeholder cuando no hay selección |
| showClear | Boolean | true | Mostrar botón para limpiar |

### Events

| Event | Payload | Descripción |
|-------|---------|-------------|
| update:modelValue | Any | Valor seleccionado actualizado |
| change | Any | Valor cambiado |

### Uso

```vue
<script setup>
const categoryFilter = ref(null);
const statusFilter = ref([]);

const categories = [
    { value: 'bebidas', label: 'Bebidas' },
    { value: 'comida', label: 'Comida' },
    { value: 'postres', label: 'Postres' },
];

const statuses = [
    { value: 'active', label: 'Activo' },
    { value: 'inactive', label: 'Inactivo' },
    { value: 'pending', label: 'Pendiente' },
];

const filteredProducts = computed(() => {
    let result = products.value;

    if (categoryFilter.value) {
        result = result.filter(p => p.category === categoryFilter.value);
    }

    if (statusFilter.value.length > 0) {
        result = result.filter(p => statusFilter.value.includes(p.status));
    }

    return result;
});
</script>

<template>
    <div class="flex gap-2">
        <!-- Filtro simple -->
        <FilterDropdown
            v-model="categoryFilter"
            :options="categories"
            label="Categoría"
        />

        <!-- Filtro múltiple -->
        <FilterDropdown
            v-model="statusFilter"
            :options="statuses"
            label="Estado"
            multiple
        />
    </div>

    <DataTable :columns="columns" :data="filteredProducts" />
</template>
```

---

## Ejemplo Completo: Tabla con Todo

```vue
<script setup>
import { ref, computed } from 'vue';
import DataTable from '@/Components/Data/DataTable.vue';
import Pagination from '@/Components/Data/Pagination.vue';
import SearchBar from '@/Components/Data/SearchBar.vue';
import FilterDropdown from '@/Components/Data/FilterDropdown.vue';
import { usePagination, useDebounce } from '@/composables';

// Datos
const products = ref([/* ... */]);
const isLoading = ref(false);

// Búsqueda
const searchQuery = ref('');
const debouncedSearch = useDebounce(searchQuery, 300);

// Filtros
const categoryFilter = ref(null);
const categories = [
    { value: 'all', label: 'Todas' },
    { value: 'bebidas', label: 'Bebidas' },
    { value: 'comida', label: 'Comida' },
];

// Filtrar y buscar
const filteredProducts = computed(() => {
    let result = products.value;

    // Aplicar búsqueda
    if (debouncedSearch.value) {
        const query = debouncedSearch.value.toLowerCase();
        result = result.filter(p =>
            p.name.toLowerCase().includes(query)
        );
    }

    // Aplicar filtro de categoría
    if (categoryFilter.value && categoryFilter.value !== 'all') {
        result = result.filter(p => p.category === categoryFilter.value);
    }

    return result;
});

// Paginación
const currentPage = ref(1);
const perPage = 10;

const { paginatedItems, totalPages } = usePagination(filteredProducts, {
    page: currentPage,
    perPage,
});

// Columnas
const columns = [
    { key: 'id', label: 'ID' },
    { key: 'name', label: 'Nombre' },
    { key: 'category', label: 'Categoría' },
    { key: 'stock', label: 'Stock', align: 'right' },
    { key: 'price', label: 'Precio', align: 'right' },
    { key: 'actions', label: 'Acciones', sortable: false },
];
</script>

<template>
    <div class="space-y-4">
        <!-- Barra de búsqueda y filtros -->
        <div class="flex gap-4">
            <div class="flex-1">
                <SearchBar
                    v-model="searchQuery"
                    placeholder="Buscar productos..."
                    :loading="isLoading"
                />
            </div>
            <FilterDropdown
                v-model="categoryFilter"
                :options="categories"
                label="Categoría"
            />
        </div>

        <!-- Tabla de datos -->
        <DataTable
            :columns="columns"
            :data="paginatedItems"
            :loading="isLoading"
            :sortable="true"
            :hoverable="true"
        >
            <template #cell-stock="{ value }">
                <BaseBadge
                    :variant="value > 10 ? 'success' : 'danger'"
                >
                    {{ value }}
                </BaseBadge>
            </template>

            <template #cell-price="{ value }">
                ${{ value.toFixed(2) }}
            </template>

            <template #cell-actions="{ row }">
                <div class="flex gap-2">
                    <BaseButton size="sm" @click="edit(row)">
                        Editar
                    </BaseButton>
                    <BaseButton
                        size="sm"
                        variant="danger"
                        @click="deleteItem(row)"
                    >
                        Eliminar
                    </BaseButton>
                </div>
            </template>
        </DataTable>

        <!-- Paginación -->
        <Pagination
            :current-page="currentPage"
            :total-pages="totalPages"
            :total-items="filteredProducts.length"
            :per-page="perPage"
            @page-change="currentPage = $event"
        />
    </div>
</template>
```

## Testing

```bash
npm test -- DataTable
npm test -- SearchBar
```

## Performance Tips

1. **DataTable**: Usa row-key único para mejor rendimiento
2. **SearchBar**: Ajusta debounce según uso (API calls = 500ms, local = 300ms)
3. **Pagination**: Considera server-side pagination para datasets grandes
4. **FilterDropdown**: Usa virtualización para muchas opciones (>100)
