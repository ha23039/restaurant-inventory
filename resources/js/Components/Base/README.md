# Base Components

Componentes base fundamentales para construcción de interfaces de usuario. Estos componentes son altamente reutilizables y personalizables.

## BaseButton

Botón con múltiples variantes, tamaños y estados.

### Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| variant | String | 'primary' | Variante de color: primary, secondary, danger, success, warning, info |
| size | String | 'md' | Tamaño: sm, md, lg |
| disabled | Boolean | false | Deshabilitar botón |
| loading | Boolean | false | Mostrar spinner de carga |
| outline | Boolean | false | Estilo outline en lugar de sólido |
| type | String | 'button' | Tipo HTML: button, submit, reset |

### Events

| Event | Payload | Descripción |
|-------|---------|-------------|
| click | Event | Se emite cuando se hace click (no se emite si disabled o loading) |

### Uso

```vue
<template>
    <!-- Botón básico -->
    <BaseButton @click="handleSave">
        Guardar
    </BaseButton>

    <!-- Botón con carga -->
    <BaseButton variant="primary" :loading="isLoading" @click="handleSubmit">
        Enviar
    </BaseButton>

    <!-- Botón destructivo -->
    <BaseButton variant="danger" outline @click="handleDelete">
        Eliminar
    </BaseButton>

    <!-- Botón grande -->
    <BaseButton size="lg" variant="success">
        Confirmar Pedido
    </BaseButton>
</template>
```

---

## BaseInput

Input de texto con validación, estados de error y slot para iconos.

### Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| modelValue | String/Number | '' | Valor del input (v-model) |
| type | String | 'text' | Tipo de input HTML |
| placeholder | String | '' | Placeholder |
| disabled | Boolean | false | Deshabilitar input |
| error | String | null | Mensaje de error |

### Events

| Event | Payload | Descripción |
|-------|---------|-------------|
| update:modelValue | String/Number | Valor actualizado |
| change | Event | Evento change nativo |
| blur | Event | Input pierde foco |
| focus | Event | Input gana foco |

### Slots

| Slot | Descripción |
|------|-------------|
| icon | Icono para mostrar dentro del input |

### Uso

```vue
<template>
    <!-- Input básico -->
    <BaseInput
        v-model="email"
        type="email"
        placeholder="correo@ejemplo.com"
    />

    <!-- Input con error -->
    <BaseInput
        v-model="password"
        type="password"
        :error="errors.password"
    />

    <!-- Input con icono -->
    <BaseInput v-model="search" placeholder="Buscar...">
        <template #icon>
            <svg><!-- Icono de búsqueda --></svg>
        </template>
    </BaseInput>
</template>
```

---

## BaseSelect

Select dropdown con validación y soporte para selección múltiple.

### Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| modelValue | String/Number/Array | null | Valor seleccionado |
| options | Array | required | Array de { value, label, disabled? } |
| placeholder | String | 'Seleccionar...' | Placeholder |
| disabled | Boolean | false | Deshabilitar select |
| error | String | null | Mensaje de error |
| multiple | Boolean | false | Permitir selección múltiple |
| size | String | 'md' | Tamaño: sm, md, lg |

### Events

| Event | Payload | Descripción |
|-------|---------|-------------|
| update:modelValue | Any | Valor seleccionado actualizado |
| change | Any | Valor cambiado |

### Uso

```vue
<script setup>
const categories = [
    { value: 1, label: 'Bebidas' },
    { value: 2, label: 'Comida' },
    { value: 3, label: 'Postres', disabled: true },
];

const selectedCategory = ref(null);
</script>

<template>
    <!-- Select básico -->
    <BaseSelect
        v-model="selectedCategory"
        :options="categories"
        placeholder="Selecciona categoría"
    />

    <!-- Select múltiple -->
    <BaseSelect
        v-model="selectedTags"
        :options="tags"
        multiple
        :error="errors.tags"
    />
</template>
```

---

## BaseCheckbox

Checkbox con label, descripción y soporte para arrays.

### Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| modelValue | Boolean/Array | false | Valor checked o array de valores |
| value | String/Number/Boolean | null | Valor para modo array |
| label | String | null | Texto del label |
| description | String | null | Texto descriptivo |
| disabled | Boolean | false | Deshabilitar checkbox |
| error | String | null | Mensaje de error |
| size | String | 'md' | Tamaño: sm, md, lg |

### Events

| Event | Payload | Descripción |
|-------|---------|-------------|
| update:modelValue | Boolean/Array | Valor actualizado |
| change | Boolean/Array | Valor cambiado |

### Uso

```vue
<template>
    <!-- Checkbox simple -->
    <BaseCheckbox
        v-model="acceptTerms"
        label="Acepto términos y condiciones"
    />

    <!-- Checkbox con descripción -->
    <BaseCheckbox
        v-model="notifications"
        label="Notificaciones por email"
        description="Recibe actualizaciones sobre tus pedidos"
    />

    <!-- Múltiples checkboxes (array) -->
    <BaseCheckbox
        v-model="selectedRoles"
        value="admin"
        label="Administrador"
    />
    <BaseCheckbox
        v-model="selectedRoles"
        value="cajero"
        label="Cajero"
    />
</template>
```

---

## BaseRadio

Radio button con states y validación.

### Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| modelValue | String/Number/Boolean | required | Valor seleccionado |
| value | String/Number/Boolean | required | Valor de esta opción |
| label | String | null | Texto del label |
| description | String | null | Texto descriptivo |
| disabled | Boolean | false | Deshabilitar radio |
| error | String | null | Mensaje de error |
| size | String | 'md' | Tamaño: sm, md, lg |

### Events

| Event | Payload | Descripción |
|-------|---------|-------------|
| update:modelValue | Any | Valor seleccionado |
| change | Any | Valor cambiado |

### Uso

```vue
<script setup>
const paymentMethod = ref('cash');
</script>

<template>
    <BaseRadio
        v-model="paymentMethod"
        value="cash"
        label="Efectivo"
        description="Pago en efectivo al entregar"
    />
    <BaseRadio
        v-model="paymentMethod"
        value="card"
        label="Tarjeta"
        description="Pago con tarjeta de crédito/débito"
    />
    <BaseRadio
        v-model="paymentMethod"
        value="transfer"
        label="Transferencia"
    />
</template>
```

---

## BaseTextarea

Textarea con contador de caracteres y control de resize.

### Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| modelValue | String | '' | Valor del textarea |
| placeholder | String | '' | Placeholder |
| disabled | Boolean | false | Deshabilitar textarea |
| error | String | null | Mensaje de error |
| rows | Number | 4 | Número de filas |
| maxlength | Number | null | Longitud máxima |
| showCount | Boolean | false | Mostrar contador de caracteres |
| resize | String | 'vertical' | Control de resize: none, vertical, horizontal, both |

### Events

| Event | Payload | Descripción |
|-------|---------|-------------|
| update:modelValue | String | Valor actualizado |
| change | String | Valor cambiado |
| blur | Event | Textarea pierde foco |
| focus | Event | Textarea gana foco |

### Uso

```vue
<template>
    <!-- Textarea básico -->
    <BaseTextarea
        v-model="description"
        placeholder="Descripción del producto..."
        :rows="6"
    />

    <!-- Textarea con límite y contador -->
    <BaseTextarea
        v-model="notes"
        placeholder="Notas adicionales..."
        :maxlength="500"
        show-count
        :error="errors.notes"
    />

    <!-- Sin resize -->
    <BaseTextarea
        v-model="comment"
        resize="none"
    />
</template>
```

---

## BaseBadge

Badges/etiquetas con variantes, dot indicator y opción removable.

### Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| variant | String | 'default' | Variante: default, primary, success, warning, danger, info |
| size | String | 'md' | Tamaño: sm, md, lg |
| dot | Boolean | false | Mostrar dot indicator |
| removable | Boolean | false | Mostrar botón para remover |

### Events

| Event | Payload | Descripción |
|-------|---------|-------------|
| remove | - | Se emite al hacer click en el botón remover |

### Uso

```vue
<template>
    <!-- Badges básicos -->
    <BaseBadge>Default</BaseBadge>
    <BaseBadge variant="success">Activo</BaseBadge>
    <BaseBadge variant="danger">Agotado</BaseBadge>

    <!-- Con dot indicator -->
    <BaseBadge variant="warning" dot>
        Pendiente
    </BaseBadge>

    <!-- Removable -->
    <BaseBadge
        v-for="tag in tags"
        :key="tag.id"
        variant="primary"
        removable
        @remove="removeTag(tag.id)"
    >
        {{ tag.name }}
    </BaseBadge>
</template>
```

---

## BaseCard

Card contenedor con header, footer y slots personalizables.

### Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| shadow | String | 'sm' | Nivel de sombra: none, sm, md, lg, xl |
| padding | String | 'md' | Padding: none, sm, md, lg |

### Slots

| Slot | Descripción |
|------|-------------|
| default | Contenido principal del card |
| header | Contenido del header |
| footer | Contenido del footer |

### Uso

```vue
<template>
    <!-- Card básico -->
    <BaseCard>
        <p>Contenido del card</p>
    </BaseCard>

    <!-- Card con header y footer -->
    <BaseCard shadow="lg">
        <template #header>
            <h3>Título del Card</h3>
        </template>

        <p>Contenido principal aquí...</p>

        <template #footer>
            <BaseButton>Acción</BaseButton>
        </template>
    </BaseCard>
</template>
```

---

## BaseModal

Modal/dialog con overlay, transitions y control de teclado.

### Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| show | Boolean | false | Mostrar/ocultar modal |
| maxWidth | String | '2xl' | Ancho máximo: sm, md, lg, xl, 2xl, 3xl, 4xl, 5xl |
| closeable | Boolean | true | Permitir cerrar con ESC o click outside |

### Events

| Event | Payload | Descripción |
|-------|---------|-------------|
| close | - | Se emite al intentar cerrar el modal |

### Slots

| Slot | Descripción |
|------|-------------|
| default | Contenido del modal |

### Uso

```vue
<script setup>
const showModal = ref(false);

const openModal = () => {
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};
</script>

<template>
    <BaseButton @click="openModal">
        Abrir Modal
    </BaseButton>

    <BaseModal :show="showModal" @close="closeModal" max-width="md">
        <div class="p-6">
            <h2 class="text-xl font-bold mb-4">Confirmar Acción</h2>
            <p class="mb-4">¿Estás seguro de continuar?</p>

            <div class="flex justify-end gap-2">
                <BaseButton variant="secondary" @click="closeModal">
                    Cancelar
                </BaseButton>
                <BaseButton variant="primary" @click="handleConfirm">
                    Confirmar
                </BaseButton>
            </div>
        </div>
    </BaseModal>
</template>
```

---

## Testing

Todos los componentes base tienen tests unitarios:

```bash
npm test -- BaseButton
npm test -- BaseSelect
```

## Accesibilidad

- Labels apropiados para inputs
- Atributos ARIA cuando necesario
- Navegación por teclado
- Estados disabled visualmente claros
