# Biblioteca de Componentes Vue

Esta es la biblioteca completa de componentes reutilizables para el sistema de gestión de restaurantes. Los componentes están organizados en categorías y diseñados con Tailwind CSS.

## 📁 Estructura de Directorios

```
Components/
├── Base/           # Componentes base fundamentales
├── Data/           # Componentes para visualización de datos
├── Feedback/       # Componentes de retroalimentación
└── Forms/          # Componentes de formulario
```

## 🎨 Categorías de Componentes

### Base Components
Componentes fundamentales reutilizables para construcción de UI.

- **BaseButton** - Botón con múltiples variantes y estados
- **BaseInput** - Input de texto con validación
- **BaseSelect** - Select dropdown con validación
- **BaseCheckbox** - Checkbox con label y descripción
- **BaseRadio** - Radio button con states
- **BaseTextarea** - Textarea con contador de caracteres
- **BaseBadge** - Badges/etiquetas con variantes
- **BaseCard** - Card contenedor con slots
- **BaseModal** - Modal/dialog con overlay

### Data Components
Componentes para mostrar y manipular datos.

- **DataTable** - Tabla completa con sorting y slots personalizables
- **Pagination** - Paginación con navegación completa
- **SearchBar** - Barra de búsqueda con debounce
- **FilterDropdown** - Dropdown de filtros con multi-select

### Feedback Components
Componentes para comunicar estados y acciones al usuario.

- **Alert** - Alertas inline con tipos
- **LoadingSpinner** - Spinner de carga
- **EmptyState** - Estado vacío con acción
- **ToastContent** - Contenido personalizado para toasts
- **NotificationCenter** - Centro de notificaciones con badge

### Forms Components
Componentes wrapper para formularios completos.

- **FormGroup** - Wrapper con label y error display
- **FormInput** - Input completo con label
- **FormSelect** - Select completo con label
- **FormCheckbox** - Checkbox wrapper

## 🚀 Uso Rápido

### Importación

```vue
<script setup>
import BaseButton from '@/Components/Base/BaseButton.vue';
import DataTable from '@/Components/Data/DataTable.vue';
import { useToast } from '@/composables';

const toast = useToast();
</script>
```

### Ejemplos Básicos

#### Button
```vue
<BaseButton variant="primary" @click="handleClick">
    Guardar
</BaseButton>

<BaseButton variant="danger" :loading="isLoading">
    Eliminar
</BaseButton>
```

#### DataTable
```vue
<DataTable
    :columns="[
        { key: 'id', label: 'ID' },
        { key: 'name', label: 'Nombre' },
        { key: 'price', label: 'Precio', align: 'right' }
    ]"
    :data="products"
    :sortable="true"
    @row-click="handleRowClick"
/>
```

#### Toast Notifications
```vue
<script setup>
const toast = useToast();

const handleSave = async () => {
    await toast.promise(
        saveData(),
        {
            pending: 'Guardando...',
            success: 'Guardado exitosamente',
            error: 'Error al guardar'
        }
    );
};
</script>
```

## 📖 Documentación Detallada

Consulta los README específicos en cada directorio:

- [Base Components](./Base/README.md)
- [Data Components](./Data/README.md)
- [Feedback Components](./Feedback/README.md)
- [Forms Components](./Forms/README.md)

## 🎨 Sistema de Diseño

### Variantes de Color

- **primary** - Azul (acciones principales)
- **secondary** - Gris (acciones secundarias)
- **success** - Verde (confirmaciones)
- **danger** - Rojo (acciones destructivas)
- **warning** - Amarillo (advertencias)
- **info** - Índigo (información)

### Tamaños

- **sm** - Pequeño
- **md** - Mediano (default)
- **lg** - Grande

### Estados

- **disabled** - Deshabilitado
- **loading** - Cargando
- **error** - Con error
- **success** - Con éxito

## 🧪 Testing

Todos los componentes principales tienen tests unitarios con Vitest.

```bash
# Ejecutar todos los tests
npm test

# Ejecutar tests con UI
npm run test:ui

# Generar reporte de cobertura
npm run test:coverage
```

## 🔧 Composables Relacionados

Los componentes utilizan estos composables:

- **useToast** - Notificaciones toast
- **useModal** - Control de modales
- **useDebounce** - Debouncing de valores
- **useForm** - Manejo de formularios
- **usePagination** - Paginación client-side

Consulta [/composables/README.md](../../composables/README.md) para más detalles.

## 📦 Stores de Pinia

Algunos componentes se integran con stores:

- **useNotificationsStore** - NotificationCenter
- **useCartStore** - Estado del carrito POS
- **useAuthStore** - Permisos y usuario
- **useAppStore** - Estado global de app

## 🎯 Mejores Prácticas

### 1. Props Validation
Todos los componentes validan props con validadores personalizados.

### 2. Event Emission
Los componentes emiten eventos para comunicación padre-hijo.

### 3. Slots para Flexibilidad
Muchos componentes ofrecen slots para personalización.

### 4. Accesibilidad
Los componentes incluyen atributos ARIA básicos.

### 5. Responsive Design
Todos los componentes son responsive con Tailwind.

## 🤝 Contribuir

Al agregar nuevos componentes:

1. Sigue la estructura de directorios existente
2. Incluye validación de props
3. Escribe tests unitarios
4. Documenta props, events y slots
5. Usa Tailwind CSS para estilos
6. Mantén consistencia con componentes existentes

## 📄 Licencia

Este código es parte del sistema de gestión de restaurantes.
