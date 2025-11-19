# Sprint 3: Dashboard de Transacciones Financieras

**Duración:** 2 semanas
**Story Points:** 21 puntos
**Prioridad:** ALTA 🔥
**Rama:** `sprint-03/financial-transactions-dashboard`

---

## 🎯 Objetivos del Sprint

Crear un módulo completo de gestión de transacciones financieras con dashboard interactivo, visualizaciones gráficas y sistema de reportes exportables.

### Objetivos Específicos

1. ✅ Mejorar y expandir el módulo CashFlow existente
2. ✅ Implementar dashboard financiero con KPIs y gráficos
3. ✅ Crear sistema de filtros avanzados y búsqueda
4. ✅ Implementar exportación de reportes (PDF/Excel)
5. ✅ Agregar "Venta Libre" (venta sin productos específicos)
6. ✅ Crear módulo de gastos completo
7. ✅ Implementar visualizaciones con Chart.js

---

## 📊 Contexto Actual

### ✅ Ya Implementado (Base Existente)

El proyecto ya tiene:
- Modelo `CashFlow` con categorías (ventas, compras, gastos_operativos, gastos_admin, devoluciones, otros)
- Controlador `CashFlowController` básico
- Registro automático de cash flow en ventas
- Registro automático de cash flow en devoluciones

### ❌ Falta Implementar (Sprint 3)

- Dashboard financiero visual
- CRUD completo de gastos
- Filtros avanzados por fecha, categoría, tipo
- Gráficos y estadísticas
- Exportación de reportes
- Venta libre (sin productos del inventario)
- Balance y resumen financiero
- Comparativas de períodos

---

## 📋 Tareas del Sprint

### **Task 1: Mejorar Modelo CashFlow y Crear Repositorio (3 pts)**

**Descripción:** Expandir el modelo CashFlow existente y crear repositorio con queries optimizadas.

**Entregables:**
- [ ] Agregar scopes al modelo CashFlow:
  - `scopeByDateRange()`
  - `scopeByCategory()`
  - `scopeByType()`
  - `scopeIncome()` / `scopeExpense()`
- [ ] Crear `CashFlowRepository` con métodos:
  - `getBalance()` - Balance actual
  - `getSummaryByDateRange()` - Resumen por período
  - `getTrendsByPeriod()` - Datos para gráficos
  - `getByCategory()` - Agrupado por categoría
- [ ] Agregar accessors y mutators útiles
- [ ] Crear Resource API para CashFlow
- [ ] Tests unitarios del repositorio

**Story Points:** 3

---

### **Task 2: Crear Módulo de Gastos (5 pts)**

**Descripción:** Implementar CRUD completo de gastos con categorización y asociación a proveedores.

**Entregables:**

**Backend:**
- [ ] Crear `ExpenseController` con CRUD completo
- [ ] Crear `ExpenseRequest` con validaciones:
  - amount (required, numeric, > 0)
  - category (required, enum)
  - description (required, min:3)
  - expense_date (required, date)
  - payment_method (required, enum)
  - supplier_id (nullable, exists)
- [ ] Crear `ExpenseService` para lógica de negocio:
  - Crear gasto y registrar en CashFlow automáticamente
  - Actualizar gasto y ajustar CashFlow
  - Eliminar gasto y revertir CashFlow
- [ ] Agregar categorías de gastos específicas:
  - `compras` - Compras de inventario
  - `gastos_operativos` - Servicios, luz, agua, etc.
  - `gastos_admin` - Salarios, papelería
  - `mantenimiento` - Reparaciones, limpieza
  - `marketing` - Publicidad, promociones
  - `otros` - Otros gastos

**Frontend:**
- [ ] Vista `Expenses/Index.vue` con tabla de gastos
- [ ] Vista `Expenses/Create.vue` con formulario
- [ ] Vista `Expenses/Edit.vue` con formulario
- [ ] Componente `ExpenseForm.vue` reutilizable
- [ ] Integración con DataTable y filtros

**API Endpoints:**
```php
GET    /expenses              # Lista de gastos
POST   /expenses              # Crear gasto
GET    /expenses/{id}         # Ver gasto
PUT    /expenses/{id}         # Actualizar gasto
DELETE /expenses/{id}         # Eliminar gasto
```

**Story Points:** 5

---

### **Task 3: Dashboard Financiero con KPIs (5 pts)**

**Descripción:** Crear dashboard principal con métricas clave y gráficos interactivos.

**Entregables:**

**Backend:**
- [ ] Crear `FinancialDashboardController`
- [ ] Endpoint `/api/dashboard/financial-summary`:
  - Balance actual
  - Total ventas (día/semana/mes)
  - Total gastos (día/semana/mes)
  - Ganancia neta
  - Comparativa con período anterior (%)
- [ ] Endpoint `/api/dashboard/trends`:
  - Ingresos por día (últimos 30 días)
  - Gastos por día (últimos 30 días)
  - Balance por día
- [ ] Endpoint `/api/dashboard/by-category`:
  - Gastos agrupados por categoría
  - Ventas por método de pago

**Frontend:**
- [ ] Vista `Dashboard/Financial.vue`
- [ ] Componente `StatsCard.vue` para KPIs:
  - Balance actual con indicador (+/-)
  - Ventas del período con % cambio
  - Gastos del período con % cambio
  - Ganancia neta con % cambio
- [ ] Componente `TrendChart.vue` con Chart.js:
  - Gráfico de líneas (Ingresos, Gastos, Balance)
  - Selector de período (7d, 30d, 90d, 1y)
- [ ] Componente `CategoryPieChart.vue`:
  - Gráfico de pastel para gastos por categoría
- [ ] Componente `PaymentMethodChart.vue`:
  - Gráfico de barras para ventas por método de pago

**Dependencias:**
- [ ] Instalar Chart.js: `npm install chart.js vue-chartjs`

**Story Points:** 5

---

### **Task 4: Sistema de Filtros y Búsqueda (3 pts)**

**Descripción:** Implementar filtros avanzados para transacciones y reportes.

**Entregables:**

**Backend:**
- [ ] Actualizar `CashFlowController@index` con filtros:
  - `date_from` / `date_to`
  - `category`
  - `type` (entrada/salida)
  - `payment_method`
  - `search` (buscar en description)
- [ ] Paginación con 20 items por página
- [ ] Ordenamiento por fecha (desc por defecto)

**Frontend:**
- [ ] Vista `CashFlow/Index.vue` mejorada
- [ ] Componente `TransactionFilters.vue`:
  - DateRangePicker (componente a crear)
  - FilterDropdown para categorías
  - FilterDropdown para tipo
  - FilterDropdown para método de pago
  - SearchBar para búsqueda
- [ ] Componente `DateRangePicker.vue`:
  - Selector de rango de fechas
  - Presets: Hoy, Ayer, Esta semana, Este mes, Último mes
- [ ] Integración con DataTable existente
- [ ] Mostrar resumen de filtros aplicados

**Story Points:** 3

---

### **Task 5: Exportación de Reportes (3 pts)**

**Descripción:** Implementar exportación de reportes financieros a PDF y Excel.

**Entregables:**

**Backend:**
- [ ] Instalar dependencias:
  - `composer require maatwebsite/excel` (Excel)
  - Ya tenemos `barryvdh/laravel-dompdf` (PDF)
- [ ] Crear `FinancialReportService`
- [ ] Crear `CashFlowExport` (Excel export):
  - Hoja 1: Transacciones detalladas
  - Hoja 2: Resumen por categoría
  - Hoja 3: Resumen por método de pago
- [ ] Crear vista Blade `reports/cashflow-pdf.blade.php`
- [ ] Endpoint `GET /cash-flow/export`:
  - Query param `format` (pdf/excel)
  - Query param `date_from` / `date_to`
  - Query param `category` (opcional)
- [ ] Generar nombre de archivo: `reporte-financiero-YYYY-MM-DD.pdf`

**Frontend:**
- [ ] Botón "Exportar" en CashFlow/Index
- [ ] Modal de exportación con opciones:
  - Formato (PDF/Excel)
  - Rango de fechas
  - Categorías a incluir
- [ ] Indicador de descarga en progreso
- [ ] Toast de éxito/error

**Story Points:** 3

---

### **Task 6: Venta Libre (Sin Productos) (2 pts)**

**Descripción:** Implementar funcionalidad de "Venta Libre" para ventas que no requieren productos del inventario.

**Entregables:**

**Backend:**
- [ ] Actualizar `Sale` model:
  - Agregar campo `is_free_sale` (boolean)
  - Agregar campo `free_sale_concept` (string, nullable)
- [ ] Migración: `add_free_sale_to_sales_table`
- [ ] Actualizar `SaleService`:
  - Método `createFreeSale()`:
    - No deduce inventario
    - No requiere items
    - Solo registra monto y concepto en CashFlow
- [ ] Validar que venta libre no pueda tener items
- [ ] Endpoint `POST /sales/free`

**Frontend:**
- [ ] Actualizar `Sales/POS.vue`:
  - Agregar toggle "Venta Libre"
  - Cuando está activo:
    - Ocultar selector de productos
    - Mostrar campo "Concepto"
    - Mostrar campo "Monto"
    - Mostrar selector de método de pago
- [ ] Validar que monto > 0

**Caso de Uso:**
- Cliente paga un servicio (delivery, propina, etc.)
- Se registra ingreso sin afectar inventario

**Story Points:** 2

---

## 🗂️ Estructura de Archivos

### Backend

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ExpenseController.php          # NUEVO
│   │   ├── FinancialDashboardController.php  # NUEVO
│   │   └── CashFlowController.php         # MEJORAR
│   ├── Requests/
│   │   └── ExpenseRequest.php             # NUEVO
│   └── Resources/
│       ├── CashFlowResource.php           # NUEVO
│       └── ExpenseResource.php            # NUEVO
├── Models/
│   └── CashFlow.php                       # MEJORAR (scopes)
├── Repositories/
│   └── CashFlowRepository.php             # NUEVO
├── Services/
│   ├── ExpenseService.php                 # NUEVO
│   └── FinancialReportService.php         # NUEVO
└── Exports/
    └── CashFlowExport.php                 # NUEVO

database/
└── migrations/
    ├── add_expense_categories_to_cash_flow.php  # NUEVO
    └── add_free_sale_to_sales_table.php         # NUEVO

resources/
└── views/
    └── reports/
        └── cashflow-pdf.blade.php         # NUEVO
```

### Frontend

```
resources/js/
├── Pages/
│   ├── Dashboard/
│   │   └── Financial.vue                  # NUEVO
│   ├── Expenses/
│   │   ├── Index.vue                      # NUEVO
│   │   ├── Create.vue                     # NUEVO
│   │   └── Edit.vue                       # NUEVO
│   ├── CashFlow/
│   │   └── Index.vue                      # MEJORAR
│   └── Sales/
│       └── POS.vue                        # MEJORAR (venta libre)
├── Components/
│   ├── Charts/                            # NUEVA CARPETA
│   │   ├── TrendChart.vue                 # NUEVO
│   │   ├── CategoryPieChart.vue           # NUEVO
│   │   └── PaymentMethodChart.vue         # NUEVO
│   ├── Financial/                         # NUEVA CARPETA
│   │   ├── StatsCard.vue                  # NUEVO
│   │   ├── TransactionFilters.vue         # NUEVO
│   │   ├── DateRangePicker.vue            # NUEVO
│   │   ├── ExpenseForm.vue                # NUEVO
│   │   └── ExportModal.vue                # NUEVO
│   └── Data/
│       └── (usar componentes existentes)
└── composables/
    ├── useCharts.js                       # NUEVO
    └── useExport.js                       # NUEVO
```

---

## 🎨 Diseño de UI

### Dashboard Financiero Layout

```
┌─────────────────────────────────────────────────────┐
│  Dashboard Financiero                    [Exportar] │
├─────────────────────────────────────────────────────┤
│                                                      │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌─────┐│
│  │ Balance  │  │  Ventas  │  │  Gastos  │  │Ganan││
│  │ $10,500  │  │  $45,000 │  │  $34,500 │  │$10,5││
│  │  +15.2%  │  │  +8.5%   │  │  +12.3%  │  │+2.1%││
│  └──────────┘  └──────────┘  └──────────┘  └─────┘│
│                                                      │
│  ┌─────────────────────────────────────────────────┐│
│  │     Tendencias (Últimos 30 días)         [7d ▼]││
│  │  $                                               ││
│  │  │    ╱╲     ╱╲                                 ││
│  │  │   ╱  ╲   ╱  ╲  Ingresos                     ││
│  │  │  ╱    ╲ ╱    ╲                               ││
│  │  │ ╱      ╳      ╲  Gastos                     ││
│  │  │╱      ╱ ╲      ╲                             ││
│  │  ┴──────┴───┴──────┴────────────────────────   ││
│  └─────────────────────────────────────────────────┘│
│                                                      │
│  ┌───────────────────┐  ┌────────────────────────┐ │
│  │Gastos por Categoría  │Ventas por Método Pago │ │
│  │                    │  │                        │ │
│  │   [Pie Chart]     │  │   [Bar Chart]         │ │
│  │                    │  │                        │ │
│  └───────────────────┘  └────────────────────────┘ │
└─────────────────────────────────────────────────────┘
```

### Vista de Transacciones

```
┌─────────────────────────────────────────────────────┐
│  Movimientos Financieros                             │
├─────────────────────────────────────────────────────┤
│  [Buscar...]  [Categoría ▼]  [01/11 - 30/11]  [PDF]│
│                                                [Excel]│
├─────────────────────────────────────────────────────┤
│  Fecha      │ Concepto       │ Categoría  │ Monto   │
├─────────────────────────────────────────────────────┤
│  15/11/2025 │ Venta #0001    │ Ventas     │ +$125.00│
│  15/11/2025 │ Compra Pollo   │ Compras    │ -$450.00│
│  14/11/2025 │ Luz CFE        │ Gastos Op. │ -$200.00│
│  14/11/2025 │ Venta #0002    │ Ventas     │ +$89.50 │
└─────────────────────────────────────────────────────┘
         Mostrando 1-20 de 156    [< 1 2 3 4 >]
```

---

## 🔌 API Endpoints

### Nuevos Endpoints

```php
// Dashboard Financiero
GET  /api/dashboard/financial-summary
GET  /api/dashboard/trends?period=30d
GET  /api/dashboard/by-category

// Gastos
GET    /expenses
POST   /expenses
GET    /expenses/{id}
PUT    /expenses/{id}
DELETE /expenses/{id}

// Cash Flow
GET  /cash-flow?date_from=X&date_to=Y&category=Z
GET  /cash-flow/export?format=pdf&date_from=X&date_to=Y

// Ventas Libres
POST /sales/free
```

### Respuestas de Ejemplo

**GET /api/dashboard/financial-summary**
```json
{
  "balance": {
    "current": 10500.00,
    "change_percentage": 15.2
  },
  "sales": {
    "total": 45000.00,
    "count": 156,
    "change_percentage": 8.5
  },
  "expenses": {
    "total": 34500.00,
    "count": 42,
    "change_percentage": 12.3
  },
  "profit": {
    "amount": 10500.00,
    "margin": 23.33,
    "change_percentage": 2.1
  },
  "period": {
    "from": "2025-11-01",
    "to": "2025-11-30"
  }
}
```

**GET /api/dashboard/trends?period=30d**
```json
{
  "labels": ["Nov 1", "Nov 2", "Nov 3", ...],
  "datasets": [
    {
      "label": "Ingresos",
      "data": [1200, 1450, 980, ...]
    },
    {
      "label": "Gastos",
      "data": [800, 920, 750, ...]
    }
  ]
}
```

---

## 🧪 Testing Strategy

### Backend Tests

**Feature Tests:**
```php
// tests/Feature/ExpenseTest.php
- test_can_create_expense()
- test_expense_creates_cash_flow_entry()
- test_can_update_expense_and_adjust_cash_flow()
- test_can_delete_expense_and_revert_cash_flow()
- test_validates_expense_data()

// tests/Feature/FinancialDashboardTest.php
- test_returns_financial_summary()
- test_returns_trends_data()
- test_filters_by_date_range()

// tests/Feature/CashFlowExportTest.php
- test_exports_to_pdf()
- test_exports_to_excel()
- test_respects_filters_in_export()
```

**Unit Tests:**
```php
// tests/Unit/CashFlowRepositoryTest.php
- test_calculates_balance_correctly()
- test_gets_summary_by_date_range()
- test_groups_by_category()

// tests/Unit/FinancialReportServiceTest.php
- test_generates_pdf_report()
- test_generates_excel_report()
```

### Frontend Tests

```javascript
// resources/js/__tests__/components/StatsCard.test.js
- renders stats correctly
- shows positive/negative indicator
- formats currency

// resources/js/__tests__/components/TrendChart.test.js
- renders chart with data
- switches period correctly
- handles empty data
```

### Objetivo de Cobertura

- Backend: 80%+
- Frontend: 70%+

---

## 📦 Dependencias Nuevas

### Backend
```bash
composer require maatwebsite/excel
# barryvdh/laravel-dompdf ya está instalado
```

### Frontend
```bash
npm install chart.js vue-chartjs
npm install date-fns  # ya instalado en Sprint 2
```

---

## 🚀 Orden de Implementación

### Semana 1 (Tasks 1-3)

**Día 1-2: Task 1**
- Mejorar modelo CashFlow
- Crear CashFlowRepository
- Tests unitarios

**Día 3-5: Task 2**
- Backend de gastos (controlador, servicio)
- Frontend de gastos (vistas, formularios)
- Tests de gastos

**Día 6-7: Task 3 (inicio)**
- Backend del dashboard
- Instalar Chart.js

### Semana 2 (Tasks 3-6)

**Día 8-10: Task 3 (continuación)**
- Componentes de gráficos
- StatsCards
- Integración completa del dashboard

**Día 11-12: Task 4**
- Filtros avanzados
- DateRangePicker
- Integración con tabla

**Día 13: Task 5**
- Exportación PDF/Excel
- Modal de exportación

**Día 14: Task 6**
- Venta libre
- Tests finales
- Documentación

---

## ✅ Definition of Done

El Sprint 3 se considera completado cuando:

### Backend
- [ ] 6 nuevos endpoints funcionando
- [ ] Repositorio y servicios con inyección de dependencias
- [ ] Validaciones con Form Requests
- [ ] Migraciones ejecutadas y testeadas
- [ ] 15+ feature tests pasando
- [ ] 10+ unit tests pasando
- [ ] Sin errores PHPStan nivel 5
- [ ] Código formateado con Pint

### Frontend
- [ ] Dashboard financiero completo y funcional
- [ ] CRUD de gastos completo
- [ ] 4 gráficos funcionando (Chart.js)
- [ ] Filtros y búsqueda operativos
- [ ] Exportación PDF/Excel funcionando
- [ ] Venta libre implementada
- [ ] Responsive en mobile/tablet/desktop
- [ ] Sin errores en consola

### Documentación
- [ ] README del Sprint actualizado
- [ ] Comentarios inline en código complejo
- [ ] API endpoints documentados
- [ ] CHANGELOG.md actualizado

### QA
- [ ] Testing manual completado
- [ ] Pull Request creado
- [ ] Code review aprobado
- [ ] Merge a develop exitoso

---

## 📊 Story Points Breakdown

| Task | Descripción | Story Points |
|------|-------------|--------------|
| 1 | Mejorar CashFlow y Repositorio | 3 |
| 2 | Módulo de Gastos | 5 |
| 3 | Dashboard Financiero | 5 |
| 4 | Filtros y Búsqueda | 3 |
| 5 | Exportación de Reportes | 3 |
| 6 | Venta Libre | 2 |
| **TOTAL** | | **21** |

---

## 🎯 Métricas de Éxito

Al final del Sprint 3 deberíamos tener:

### Funcionalidad
- ✅ Dashboard financiero completo
- ✅ Módulo de gastos CRUD
- ✅ Sistema de reportes exportables
- ✅ Venta libre operativa

### Código
- 25+ tests nuevos
- 80%+ cobertura en código nuevo
- 0 errores PHPStan
- 0 errores ESLint

### Performance
- Dashboard carga en <500ms
- Gráficos renderizan en <200ms
- Exportación PDF en <2s

### UX
- UI intuitiva y profesional
- Gráficos interactivos
- Filtros responsivos
- Feedback visual adecuado

---

## 🚨 Riesgos Identificados

### Riesgo 1: Complejidad de Chart.js
- **Impacto:** Medio
- **Probabilidad:** Media
- **Mitigación:** Usar vue-chartjs (wrapper oficial), seguir documentación

### Riesgo 2: Performance con muchas transacciones
- **Impacto:** Alto
- **Probabilidad:** Media
- **Mitigación:**
  - Paginación obligatoria
  - Índices en BD (date, category)
  - Eager loading
  - Cache de dashboard (5 minutos)

### Riesgo 3: Exportación de archivos grandes
- **Impacto:** Medio
- **Probabilidad:** Baja
- **Mitigación:**
  - Límite de 1000 registros por exportación
  - Queue jobs para reportes grandes
  - Timeout de 60s

---

## 🎨 Assets y Recursos

### Iconos Necesarios
- 💰 Money/Cash (balance)
- 📈 Trending Up (ventas)
- 📉 Trending Down (gastos)
- 💵 Dollar Sign (ganancia)
- 📊 Bar Chart
- 🥧 Pie Chart
- 📅 Calendar
- 📄 Document (exportar)
- ✏️ Edit
- 🗑️ Delete

### Colores del Dashboard
```css
--color-income: #10b981    /* green-500 */
--color-expense: #ef4444   /* red-500 */
--color-balance: #3b82f6   /* blue-500 */
--color-profit: #8b5cf6    /* purple-500 */
```

---

## 📚 Referencias

- [WORKFLOW.md](./WORKFLOW.md) - Módulo 1: Movimientos Financieros
- [CLAUDE.md](./CLAUDE.md) - Arquitectura del proyecto
- [Chart.js Docs](https://www.chartjs.org/docs/latest/)
- [Vue Chart.js](https://vue-chartjs.org/)
- [Laravel Excel](https://docs.laravel-excel.com/)

---

**¡Vamos a crear un dashboard financiero increíble! 🚀💰**
