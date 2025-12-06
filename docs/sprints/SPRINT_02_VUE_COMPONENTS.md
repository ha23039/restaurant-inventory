# 🎨 SPRINT 2: COMPONENTES VUE REUTILIZABLES + TESTING BASE

**Duración:** 2 semanas (Semana 3-4)
**Rama:** `sprint-02/vue-components-testing`
**Prioridad:** CRÍTICA ⚠️
**Story Points:** 18 puntos

---

## 🎯 OBJETIVOS DEL SPRINT

1. Crear librería de componentes Vue 3 reutilizables
2. Implementar sistema de testing frontend (Vitest)
3. Configurar Pinia para state management
4. Crear composables reutilizables
5. Implementar sistema de notificaciones mejorado
6. Documentar componentes con Storybook (opcional)

---

## 📋 TAREAS DETALLADAS

### **1. Setup de Testing Frontend** (3 puntos)

#### Subtareas:
- [ ] Instalar Vitest y dependencias
  ```bash
  npm install -D vitest @vue/test-utils jsdom
  npm install -D @vitest/ui @vitest/coverage-v8
  ```
- [ ] Configurar Vitest en `vite.config.js`
- [ ] Crear archivo `vitest.config.js`
- [ ] Crear carpeta de tests: `resources/js/__tests__/`
- [ ] Escribir primer test de componente

#### Configuración - vitest.config.js:
```javascript
import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath } from 'node:url'

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: 'jsdom',
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      exclude: [
        'node_modules/',
        'resources/js/__tests__/',
      ]
    }
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./resources/js', import.meta.url))
    }
  }
})
```

#### Entregable:
- Testing framework configurado
- Comando `npm run test` funcionando
- Coverage reports generándose

---

### **2. Crear Componentes Base** (5 puntos)

#### Estructura de componentes:
```
resources/js/
├── Components/
│   ├── Base/
│   │   ├── BaseButton.vue
│   │   ├── BaseInput.vue
│   │   ├── BaseSelect.vue
│   │   ├── BaseCheckbox.vue
│   │   ├── BaseRadio.vue
│   │   ├── BaseTextarea.vue
│   │   ├── BaseBadge.vue
│   │   ├── BaseCard.vue
│   │   └── BaseModal.vue
│   ├── Forms/
│   │   ├── FormInput.vue
│   │   ├── FormSelect.vue
│   │   ├── FormCheckbox.vue
│   │   ├── FormGroup.vue
│   │   └── FormError.vue
│   ├── Data/
│   │   ├── DataTable.vue
│   │   ├── Pagination.vue
│   │   ├── SearchBar.vue
│   │   └── FilterDropdown.vue
│   ├── Feedback/
│   │   ├── Alert.vue
│   │   ├── Toast.vue
│   │   ├── LoadingSpinner.vue
│   │   └── EmptyState.vue
│   └── Charts/
│       ├── LineChart.vue
│       ├── BarChart.vue
│       ├── PieChart.vue
│       └── StatsCard.vue
```

#### Componente ejemplo - BaseButton.vue:
```vue
<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'danger', 'success', 'warning'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'button'
  }
});

const emit = defineEmits(['click']);

const buttonClasses = computed(() => {
  const base = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2';

  const variants = {
    primary: 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
    secondary: 'bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-500',
    danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
    warning: 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500'
  };

  const sizes = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-base',
    lg: 'px-6 py-3 text-lg'
  };

  const disabled = props.disabled || props.loading ? 'opacity-50 cursor-not-allowed' : '';

  return `${base} ${variants[props.variant]} ${sizes[props.size]} ${disabled}`;
});

const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event);
  }
};
</script>

<template>
  <button
    :type="type"
    :class="buttonClasses"
    :disabled="disabled || loading"
    @click="handleClick"
  >
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <slot />
  </button>
</template>
```

#### Componente ejemplo - DataTable.vue:
```vue
<script setup>
import { ref, computed } from 'vue';
import Pagination from './Pagination.vue';

const props = defineProps({
  columns: {
    type: Array,
    required: true,
    // [{ key: 'name', label: 'Nombre', sortable: true }]
  },
  data: {
    type: Array,
    required: true
  },
  perPage: {
    type: Number,
    default: 10
  },
  loading: {
    type: Boolean,
    default: false
  },
  searchable: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['row-click', 'sort-change']);

const currentPage = ref(1);
const sortKey = ref('');
const sortOrder = ref('asc');
const searchQuery = ref('');

const filteredData = computed(() => {
  let data = [...props.data];

  // Búsqueda
  if (searchQuery.value) {
    data = data.filter(row => {
      return props.columns.some(col => {
        const value = row[col.key];
        return value && value.toString().toLowerCase().includes(searchQuery.value.toLowerCase());
      });
    });
  }

  // Ordenamiento
  if (sortKey.value) {
    data.sort((a, b) => {
      const aVal = a[sortKey.value];
      const bVal = b[sortKey.value];

      if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1;
      if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1;
      return 0;
    });
  }

  return data;
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * props.perPage;
  const end = start + props.perPage;
  return filteredData.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredData.value.length / props.perPage);
});

const handleSort = (column) => {
  if (!column.sortable) return;

  if (sortKey.value === column.key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = column.key;
    sortOrder.value = 'asc';
  }

  emit('sort-change', { key: sortKey.value, order: sortOrder.value });
};
</script>

<template>
  <div class="space-y-4">
    <!-- Búsqueda -->
    <div v-if="searchable" class="flex items-center">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Buscar..."
        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      />
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
              :class="{ 'cursor-pointer': column.sortable }"
              @click="handleSort(column)"
            >
              <div class="flex items-center space-x-1">
                <span>{{ column.label }}</span>
                <svg
                  v-if="column.sortable && sortKey === column.key"
                  class="w-4 h-4"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    v-if="sortOrder === 'asc'"
                    d="M5 10l5-5 5 5H5z"
                  />
                  <path
                    v-else
                    d="M15 10l-5 5-5-5h10z"
                  />
                </svg>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="loading">
            <td :colspan="columns.length" class="px-6 py-4 text-center">
              <LoadingSpinner />
            </td>
          </tr>
          <tr
            v-else-if="paginatedData.length === 0"
          >
            <td :colspan="columns.length" class="px-6 py-4 text-center text-gray-500">
              No hay datos disponibles
            </td>
          </tr>
          <tr
            v-else
            v-for="(row, index) in paginatedData"
            :key="index"
            class="hover:bg-gray-50 cursor-pointer"
            @click="emit('row-click', row)"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
            >
              <slot :name="`cell-${column.key}`" :row="row">
                {{ row[column.key] }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <Pagination
      v-if="totalPages > 1"
      :current-page="currentPage"
      :total-pages="totalPages"
      @page-change="currentPage = $event"
    />
  </div>
</template>
```

#### Entregable:
- 15+ componentes reutilizables
- Props con validación
- Eventos correctamente emitidos
- Slots para personalización

---

### **3. Implementar Pinia para State Management** (3 puntos)

#### Subtareas:
- [ ] Instalar Pinia
  ```bash
  npm install pinia
  ```
- [ ] Configurar Pinia en `app.js`
- [ ] Crear stores principales:
  ```
  resources/js/
  ├── stores/
  │   ├── auth.js
  │   ├── cart.js (para POS)
  │   ├── notifications.js
  │   └── app.js (estado global)
  ```

#### Store ejemplo - cart.js (POS):
```javascript
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCartStore = defineStore('cart', () => {
  // State
  const items = ref([]);
  const discount = ref(0);
  const taxRate = ref(0.13); // 13% IVA

  // Getters
  const subtotal = computed(() => {
    return items.value.reduce((sum, item) => {
      return sum + (item.price * item.quantity);
    }, 0);
  });

  const tax = computed(() => {
    return (subtotal.value - discount.value) * taxRate.value;
  });

  const total = computed(() => {
    return subtotal.value - discount.value + tax.value;
  });

  const itemCount = computed(() => {
    return items.value.reduce((sum, item) => sum + item.quantity, 0);
  });

  // Actions
  function addItem(product) {
    const existingItem = items.value.find(item => {
      if (product.type === 'menu') {
        return item.type === 'menu' && item.id === product.id;
      } else {
        return item.type === 'simple' && item.id === product.id;
      }
    });

    if (existingItem) {
      existingItem.quantity++;
    } else {
      items.value.push({
        id: product.id,
        type: product.type,
        name: product.name,
        price: product.price,
        quantity: 1,
        availableQuantity: product.available_quantity
      });
    }
  }

  function removeItem(index) {
    items.value.splice(index, 1);
  }

  function updateQuantity(index, quantity) {
    if (quantity <= 0) {
      removeItem(index);
    } else {
      items.value[index].quantity = quantity;
    }
  }

  function setDiscount(amount) {
    discount.value = amount;
  }

  function clearCart() {
    items.value = [];
    discount.value = 0;
  }

  function validateStock() {
    const errors = [];

    items.value.forEach((item, index) => {
      if (item.quantity > item.availableQuantity) {
        errors.push({
          index,
          message: `${item.name} solo tiene ${item.availableQuantity} unidades disponibles`
        });
      }
    });

    return errors;
  }

  return {
    // State
    items,
    discount,
    taxRate,
    // Getters
    subtotal,
    tax,
    total,
    itemCount,
    // Actions
    addItem,
    removeItem,
    updateQuantity,
    setDiscount,
    clearCart,
    validateStock
  };
});
```

#### Entregable:
- Pinia configurado y funcionando
- 4 stores principales creados
- Lógica de estado centralizada

---

### **4. Crear Composables Reutilizables** (3 puntos)

#### Estructura:
```
resources/js/
├── composables/
│   ├── useApi.js
│   ├── useForm.js
│   ├── useModal.js
│   ├── usePagination.js
│   ├── useDebounce.js
│   ├── useLocalStorage.js
│   └── useToast.js
```

#### Composable ejemplo - useForm.js:
```javascript
import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';

export function useForm(initialData = {}) {
  const data = reactive({ ...initialData });
  const errors = ref({});
  const processing = ref(false);
  const recentlySuccessful = ref(false);

  const reset = (...fields) => {
    if (fields.length === 0) {
      Object.keys(data).forEach(key => {
        data[key] = initialData[key];
      });
    } else {
      fields.forEach(field => {
        data[field] = initialData[field];
      });
    }
    errors.value = {};
  };

  const clearErrors = (...fields) => {
    if (fields.length === 0) {
      errors.value = {};
    } else {
      fields.forEach(field => {
        delete errors.value[field];
      });
    }
  };

  const setError = (field, message) => {
    errors.value[field] = message;
  };

  const submit = (method, url, options = {}) => {
    processing.value = true;
    errors.value = {};

    router[method](url, data, {
      ...options,
      onError: (responseErrors) => {
        errors.value = responseErrors;
        processing.value = false;
      },
      onSuccess: () => {
        processing.value = false;
        recentlySuccessful.value = true;
        setTimeout(() => {
          recentlySuccessful.value = false;
        }, 2000);

        if (options.onSuccess) {
          options.onSuccess();
        }
      },
      onFinish: () => {
        processing.value = false;
      }
    });
  };

  const post = (url, options) => submit('post', url, options);
  const put = (url, options) => submit('put', url, options);
  const patch = (url, options) => submit('patch', url, options);
  const del = (url, options) => submit('delete', url, options);

  return {
    data,
    errors,
    processing,
    recentlySuccessful,
    reset,
    clearErrors,
    setError,
    post,
    put,
    patch,
    delete: del,
  };
}
```

#### Composable ejemplo - useDebounce.js:
```javascript
import { ref, watch } from 'vue';

export function useDebounce(value, delay = 500) {
  const debouncedValue = ref(value);
  let timeout;

  watch(() => value.value, (newValue) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      debouncedValue.value = newValue;
    }, delay);
  });

  return debouncedValue;
}
```

#### Entregable:
- 7 composables funcionales
- Lógica reutilizable extraída
- Documentación de uso

---

### **5. Sistema de Notificaciones Mejorado** (2 puntos)

#### Subtareas:
- [ ] Mejorar integración con vue-toastification
- [ ] Crear wrapper de notificaciones
- [ ] Configurar tipos de notificaciones
- [ ] Crear componente NotificationCenter

#### Wrapper - useToast.js:
```javascript
import { useToast as useVueToast } from 'vue-toastification';

export function useToast() {
  const toast = useVueToast();

  return {
    success: (message, options = {}) => {
      toast.success(message, {
        timeout: 3000,
        closeOnClick: true,
        pauseOnFocusLoss: true,
        pauseOnHover: true,
        draggable: true,
        draggablePercent: 0.6,
        showCloseButtonOnHover: false,
        hideProgressBar: false,
        closeButton: 'button',
        icon: true,
        rtl: false,
        ...options
      });
    },
    error: (message, options = {}) => {
      toast.error(message, {
        timeout: 5000,
        ...options
      });
    },
    warning: (message, options = {}) => {
      toast.warning(message, {
        timeout: 4000,
        ...options
      });
    },
    info: (message, options = {}) => {
      toast.info(message, {
        timeout: 3000,
        ...options
      });
    }
  };
}
```

#### Entregable:
- Sistema de notificaciones consistente
- Integración con Inertia
- UX mejorada

---

### **6. Documentación de Componentes** (2 puntos)

#### Subtareas:
- [ ] Crear README para cada componente
- [ ] Documentar props, events y slots
- [ ] Crear ejemplos de uso
- [ ] Crear Storybook (opcional)

#### Formato de documentación:
```markdown
# BaseButton

## Props
| Name | Type | Default | Description |
|------|------|---------|-------------|
| variant | String | 'primary' | Color variant |
| size | String | 'md' | Button size |
| disabled | Boolean | false | Disabled state |
| loading | Boolean | false | Loading state |

## Events
| Name | Payload | Description |
|------|---------|-------------|
| click | Event | Emitted on button click |

## Slots
| Name | Description |
|------|-------------|
| default | Button content |

## Usage
```vue
<BaseButton
  variant="primary"
  size="md"
  @click="handleClick"
>
  Click me
</BaseButton>
```
```

---

## 🧪 TESTING REQUERIDO

### **Component Tests**
```javascript
// __tests__/components/BaseButton.test.js
import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BaseButton from '@/Components/Base/BaseButton.vue';

describe('BaseButton', () => {
  it('renders properly', () => {
    const wrapper = mount(BaseButton, {
      slots: {
        default: 'Click me'
      }
    });
    expect(wrapper.text()).toContain('Click me');
  });

  it('emits click event', async () => {
    const wrapper = mount(BaseButton);
    await wrapper.trigger('click');
    expect(wrapper.emitted()).toHaveProperty('click');
  });

  it('disables button when disabled prop is true', () => {
    const wrapper = mount(BaseButton, {
      props: { disabled: true }
    });
    expect(wrapper.find('button').element.disabled).toBe(true);
  });

  it('shows loading spinner when loading', () => {
    const wrapper = mount(BaseButton, {
      props: { loading: true }
    });
    expect(wrapper.find('svg').exists()).toBe(true);
  });
});
```

### **Store Tests**
```javascript
// __tests__/stores/cart.test.js
import { describe, it, expect, beforeEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useCartStore } from '@/stores/cart';

describe('Cart Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it('adds item to cart', () => {
    const cart = useCartStore();
    cart.addItem({
      id: 1,
      type: 'menu',
      name: 'Tacos',
      price: 10,
      available_quantity: 5
    });
    expect(cart.items).toHaveLength(1);
    expect(cart.itemCount).toBe(1);
  });

  it('calculates total correctly', () => {
    const cart = useCartStore();
    cart.addItem({ id: 1, type: 'menu', name: 'Tacos', price: 10, available_quantity: 5 });
    cart.addItem({ id: 2, type: 'menu', name: 'Burrito', price: 15, available_quantity: 3 });
    expect(cart.subtotal).toBe(25);
  });

  it('validates stock correctly', () => {
    const cart = useCartStore();
    cart.addItem({ id: 1, type: 'menu', name: 'Tacos', price: 10, available_quantity: 2 });
    cart.updateQuantity(0, 5);
    const errors = cart.validateStock();
    expect(errors).toHaveLength(1);
  });
});
```

### **Comando:**
```bash
npm run test
npm run test:coverage
```

---

## 📊 DEFINITION OF DONE

- [ ] 15+ componentes Vue creados
- [ ] Pinia configurado con 4 stores
- [ ] 7 composables funcionales
- [ ] Sistema de notificaciones implementado
- [ ] Tests de componentes (coverage > 80%)
- [ ] Documentación de componentes completa
- [ ] Pull Request creado
- [ ] Merge a develop

---

## 🚀 ENTREGABLES FINALES

1. **Componentes:**
   - 9 componentes base
   - 5 componentes de formulario
   - 4 componentes de datos
   - 4 componentes de feedback
   - 4 componentes de gráficos

2. **State Management:**
   - Pinia configurado
   - 4 stores funcionales

3. **Composables:**
   - 7 composables reutilizables

4. **Testing:**
   - 30+ tests de componentes
   - Coverage > 80%

---

**Siguiente Sprint:** [Sprint 3 - Dashboard Financiero](./SPRINT_03_FINANCIAL_DASHBOARD.md)
