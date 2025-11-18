# Composables

Funciones reutilizables de Vue 3 Composition API para lógica común de la aplicación.

## useToast

Wrapper mejorado para vue-toastification con componentes personalizados.

### Métodos

#### success(message, options)
Muestra toast de éxito.

```vue
<script setup>
import { useToast } from '@/composables';

const toast = useToast();

toast.success('Producto guardado correctamente');

// Con título
toast.success('Cambios guardados', {
    title: 'Éxito',
    timeout: 5000,
});
</script>
```

#### error(message, options)
Muestra toast de error.

```vue
toast.error('Error al guardar el producto');

// Con título
toast.error('No se pudo conectar al servidor', {
    title: 'Error de Conexión',
});
```

#### warning(message, options)
Muestra toast de advertencia.

```vue
toast.warning('El stock está bajo');
```

#### info(message, options)
Muestra toast informativo.

```vue
toast.info('Se encontraron 5 resultados');
```

#### loading(message, options)
Muestra toast de carga que no se auto-cierra.

```vue
const toastId = toast.loading('Procesando...');

// Luego actualizar:
toast.update(toastId, {
    type: 'success',
    message: 'Completado',
});
```

#### promise(promise, messages, options)
Toast automático para promesas.

```vue
<script setup>
const handleSave = async () => {
    await toast.promise(
        saveProduct(productData),
        {
            pending: 'Guardando producto...',
            success: 'Producto guardado exitosamente',
            error: 'Error al guardar producto',
        }
    );
};

// Con función de error personalizada
await toast.promise(
    deleteProduct(id),
    {
        pending: 'Eliminando...',
        success: 'Eliminado correctamente',
        error: (err) => `Error: ${err.message}`,
    }
);
</script>
```

#### update(toastId, options)
Actualiza un toast existente.

```vue
const toastId = toast.loading('Subiendo archivo...');

// Simular progreso
setTimeout(() => {
    toast.update(toastId, {
        type: 'info',
        message: 'Procesando archivo...',
    });
}, 2000);

setTimeout(() => {
    toast.update(toastId, {
        type: 'success',
        message: 'Archivo subido correctamente',
    });
}, 4000);
```

#### clear() / clearAll()
Limpia todos los toasts.

```vue
toast.clearAll();
```

### Opciones Disponibles

```javascript
{
    title: String,              // Título del toast
    timeout: Number,            // Duración en ms (false = no auto-close)
    closeOnClick: Boolean,      // Cerrar al hacer click
    pauseOnFocusLoss: Boolean,  // Pausar cuando pierde foco
    pauseOnHover: Boolean,      // Pausar al hacer hover
    draggable: Boolean,         // Permitir arrastrar
    position: String,           // Posición: top-right, top-left, etc.
    disableCustomContent: Boolean, // No usar componente personalizado
}
```

---

## useForm

Manejo simplificado de formularios con Inertia.

### Uso

```vue
<script setup>
import { useForm } from '@/composables';

const form = useForm({
    name: '',
    email: '',
    password: '',
});

const handleSubmit = () => {
    form.post('/api/users', {
        onSuccess: () => {
            console.log('Usuario creado');
        },
        onError: () => {
            console.log('Errores:', form.errors);
        },
    });
};
</script>

<template>
    <form @submit.prevent="handleSubmit">
        <FormInput
            v-model="form.data.name"
            label="Nombre"
            :error="form.errors.name"
        />

        <FormInput
            v-model="form.data.email"
            type="email"
            label="Email"
            :error="form.errors.email"
        />

        <BaseButton
            type="submit"
            :loading="form.processing"
            :disabled="form.processing"
        >
            Guardar
        </BaseButton>
    </form>
</template>
```

### Propiedades

| Propiedad | Tipo | Descripción |
|-----------|------|-------------|
| data | Object | Datos reactivos del formulario |
| errors | Object | Errores de validación |
| processing | Boolean | Estado de procesamiento |

### Métodos

| Método | Descripción |
|--------|-------------|
| post(url, options) | POST request |
| put(url, options) | PUT request |
| patch(url, options) | PATCH request |
| delete(url, options) | DELETE request |
| reset() | Resetea el formulario |
| clearErrors() | Limpia errores |

---

## useDebounce

Debouncing de valores y funciones.

### useDebounce(value, delay)
Debounce de un valor reactivo.

```vue
<script setup>
import { ref } from 'vue';
import { useDebounce } from '@/composables';

const searchQuery = ref('');
const debouncedSearch = useDebounce(searchQuery, 500);

watch(debouncedSearch, async (value) => {
    // Solo se ejecuta 500ms después del último cambio
    const results = await searchAPI(value);
});
</script>

<template>
    <input v-model="searchQuery" placeholder="Buscar..." />
</template>
```

### useDebounceFn(fn, delay)
Debounce de una función.

```vue
<script setup>
import { useDebounceFn } from '@/composables';

const handleSearch = useDebounceFn(async (query) => {
    const results = await searchAPI(query);
    console.log(results);
}, 500);
</script>

<template>
    <input @input="handleSearch($event.target.value)" />
</template>
```

---

## useModal

Control de estado de modales.

### Uso

```vue
<script setup>
import { useModal } from '@/composables';

const confirmModal = useModal();
const editModal = useModal({
    user: null,
});

const openEdit = (user) => {
    editModal.data.user = user;
    editModal.open();
};

const handleDelete = async () => {
    await deleteUser(confirmModal.data.userId);
    confirmModal.close();
};
</script>

<template>
    <BaseButton @click="confirmModal.open()">
        Eliminar
    </BaseButton>

    <BaseModal :show="confirmModal.isOpen" @close="confirmModal.close">
        <h2>¿Confirmar eliminación?</h2>
        <BaseButton @click="handleDelete">Sí, eliminar</BaseButton>
    </BaseModal>

    <BaseModal :show="editModal.isOpen" @close="editModal.close">
        <h2>Editar Usuario</h2>
        <FormInput v-model="editModal.data.user.name" />
    </BaseModal>
</template>
```

### Propiedades

| Propiedad | Tipo | Descripción |
|-----------|------|-------------|
| isOpen | Boolean | Estado del modal |
| data | Object | Datos asociados al modal |

### Métodos

| Método | Descripción |
|--------|-------------|
| open() | Abrir modal |
| close() | Cerrar modal |
| toggle() | Toggle estado |

---

## usePagination

Paginación client-side.

### Uso

```vue
<script setup>
import { ref } from 'vue';
import { usePagination } from '@/composables';

const products = ref([/* ... 100 items ... */]);
const currentPage = ref(1);

const {
    paginatedItems,
    totalPages,
    goToPage,
    nextPage,
    prevPage,
} = usePagination(products, {
    page: currentPage,
    perPage: 10,
});
</script>

<template>
    <DataTable :data="paginatedItems" />

    <Pagination
        :current-page="currentPage"
        :total-pages="totalPages"
        @page-change="goToPage"
    />
</template>
```

### Opciones

```javascript
{
    page: ref(1),        // Página actual (reactive)
    perPage: 10,         // Items por página
}
```

### Retorna

```javascript
{
    paginatedItems,      // Items de la página actual
    totalPages,          // Total de páginas
    startIndex,          // Índice inicial
    endIndex,            // Índice final
    goToPage(page),      // Ir a página
    nextPage(),          // Página siguiente
    prevPage(),          // Página anterior
    isFirstPage,         // Boolean
    isLastPage,          // Boolean
}
```

---

## useLocalStorage

Persistencia reactiva en localStorage.

### Uso

```vue
<script setup>
import { useLocalStorage } from '@/composables';

// Valor simple
const theme = useLocalStorage('theme', 'light');

// Objeto
const userPreferences = useLocalStorage('preferences', {
    sidebar: true,
    notifications: true,
});

// Cambiar valor (se sincroniza automáticamente)
theme.value = 'dark';
userPreferences.value.sidebar = false;
</script>

<template>
    <select v-model="theme">
        <option value="light">Claro</option>
        <option value="dark">Oscuro</option>
    </select>
</template>
```

---

## useApi

Wrapper de Axios con estados de loading/error.

### Uso

```vue
<script setup>
import { useApi } from '@/composables';

const { data, loading, error, execute } = useApi();

const fetchProducts = async () => {
    await execute('/api/products');
    console.log(data.value); // Productos
};

// Con parámetros
const { data: product, execute: fetchProduct } = useApi();
await fetchProduct('/api/products/1');

// POST request
const { execute: createProduct } = useApi();
await createProduct('/api/products', {
    method: 'POST',
    data: { name: 'Nuevo Producto' },
});
</script>

<template>
    <div v-if="loading">Cargando...</div>
    <div v-else-if="error">Error: {{ error.message }}</div>
    <div v-else>
        <!-- Mostrar data -->
    </div>
</template>
```

### useApiFetch

Versión que se ejecuta automáticamente.

```vue
<script setup>
import { useApiFetch } from '@/composables';

// Se ejecuta inmediatamente
const { data, loading, error } = useApiFetch('/api/products');
</script>

<template>
    <DataTable
        :data="data"
        :loading="loading"
    />
</template>
```

---

## Ejemplo Combinado

```vue
<script setup>
import { ref } from 'vue';
import {
    useToast,
    useForm,
    useModal,
    useDebounce,
    usePagination,
} from '@/composables';

const toast = useToast();
const createModal = useModal();

// Formulario
const form = useForm({
    name: '',
    price: 0,
});

// Búsqueda con debounce
const searchQuery = ref('');
const debouncedSearch = useDebounce(searchQuery, 300);

// Productos filtrados
const filteredProducts = computed(() => {
    if (!debouncedSearch.value) return products.value;
    return products.value.filter(p =>
        p.name.toLowerCase().includes(debouncedSearch.value.toLowerCase())
    );
});

// Paginación
const currentPage = ref(1);
const { paginatedItems, totalPages } = usePagination(filteredProducts, {
    page: currentPage,
    perPage: 10,
});

// Crear producto
const handleCreate = async () => {
    await toast.promise(
        form.post('/api/products'),
        {
            pending: 'Creando producto...',
            success: 'Producto creado',
            error: 'Error al crear',
        }
    );

    createModal.close();
    form.reset();
};
</script>

<template>
    <div>
        <SearchBar v-model="searchQuery" />

        <BaseButton @click="createModal.open()">
            Nuevo Producto
        </BaseButton>

        <DataTable :data="paginatedItems" />

        <Pagination
            :current-page="currentPage"
            :total-pages="totalPages"
            @page-change="currentPage = $event"
        />

        <BaseModal :show="createModal.isOpen" @close="createModal.close">
            <form @submit.prevent="handleCreate">
                <FormInput
                    v-model="form.data.name"
                    label="Nombre"
                    :error="form.errors.name"
                />

                <BaseButton
                    type="submit"
                    :loading="form.processing"
                >
                    Crear
                </BaseButton>
            </form>
        </BaseModal>
    </div>
</template>
```

## Testing

```bash
npm test -- useDebounce
npm test -- useModal
```

## Tips de Performance

1. **useDebounce**: Ajusta el delay según el uso
   - API calls: 500-800ms
   - Búsqueda local: 200-300ms
   - Validación: 300-500ms

2. **usePagination**: Para datasets grandes (>1000 items), considera server-side pagination

3. **useLocalStorage**: No almacenar objetos muy grandes (>1MB)

4. **useApi**: Reutiliza instancias para requests similares
