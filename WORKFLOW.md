# 📋 Flujos y Funcionalidades de Treinta para Implementar
## Sistema de Inventario para Restaurante - Laravel + Vue.js

---

## 🎯 Objetivo
Implementar funcionalidades avanzadas de gestión financiera, inventario y operaciones para restaurante, basadas en los flujos de trabajo de Treinta.com.

---

## 🏗️ Stack Tecnológico
- **Backend:** Laravel 12.x
- **Frontend:** Vue.js 3
- **Base de datos:** MariaDB/MySQL
- **Autenticación:** Laravel Sanctum
- **UI Components:** Posiblemente Tailwind CSS o similar

---

## 📊 Módulos y Funcionalidades a Implementar

### 1. 💰 **Módulo de Movimientos Financieros (Transacciones)**

#### Descripción
Dashboard central para el control financiero con registro de todas las transacciones del negocio.

#### Funcionalidades
- **Dashboard de Movimientos**
  - Visualización de balance actual ($0 inicial)
  - Ventas totales del período
  - Gastos totales del período
  - Gráfico de tendencias (Ingresos, Egresos, Por cobrar, Por pagar)

- **Filtros y Búsqueda**
  - Filtro por fecha (selector de calendario)
  - Filtro por período (Diario, Semanal, Mensual, Anual)
  - Búsqueda por concepto
  - Exportar reporte (PDF/Excel)

- **Tipos de Transacciones**
  - ✅ **Nueva Venta** (con dos opciones):
    - Venta de productos (desde inventario)
    - Venta libre (sin productos específicos)
  - ❌ **Nuevo Gasto**
    - Registro de egresos
    - Categorización de gastos
    - Asociación con proveedores

- **Estado de Transacciones**
  - Pagadas
  - En deuda
  - Por cobrar
  - Por pagar

#### Endpoints API Necesarios
```
GET    /api/v1/transactions              # Lista de transacciones
POST   /api/v1/transactions              # Crear transacción
GET    /api/v1/transactions/{id}         # Detalle de transacción
PUT    /api/v1/transactions/{id}         # Actualizar transacción
DELETE /api/v1/transactions/{id}         # Eliminar transacción
GET    /api/v1/transactions/summary      # Resumen financiero (balance, ventas, gastos)
GET    /api/v1/transactions/export       # Exportar reporte
```

#### Modelos a Crear
```php
// Transaction.php
- id
- type (enum: 'sale', 'expense', 'receivable', 'payable')
- amount
- payment_method (enum: 'cash', 'card', 'transfer', 'other')
- status (enum: 'paid', 'pending', 'debt')
- category_id
- customer_id (nullable)
- supplier_id (nullable)
- employee_id
- description
- transaction_date
- timestamps
```

---

### 2. 🍽️ **Módulo de Gestión de Mesas**

#### Descripción
Sistema de control de mesas para gestión de comandas en tiempo real.

#### Funcionalidades
- **Vista de Mesas**
  - Grid visual de todas las mesas
  - Numeración de mesas (Mesa 1, Mesa 2, ..., Mesa 10)
  - Estados visuales:
    - 🔴 Ocupada
    - 🟢 Cerrada/Disponible
    - 🟡 En proceso
  - Búsqueda rápida de mesa

- **Operaciones**
  - Abrir mesa
  - Agregar productos a la mesa
  - Ver cuenta actual
  - Cerrar mesa y generar venta
  - Transferir productos entre mesas
  - Dividir cuenta

#### Endpoints API Necesarios
```
GET    /api/v1/tables                    # Lista de mesas
POST   /api/v1/tables                    # Crear mesa
GET    /api/v1/tables/{id}               # Detalle de mesa
PUT    /api/v1/tables/{id}/open          # Abrir mesa
PUT    /api/v1/tables/{id}/close         # Cerrar mesa
POST   /api/v1/tables/{id}/add-items     # Agregar productos a mesa
GET    /api/v1/tables/{id}/bill          # Obtener cuenta de la mesa
POST   /api/v1/tables/{id}/split         # Dividir cuenta
```

#### Modelos a Crear
```php
// Table.php
- id
- number
- capacity
- status (enum: 'available', 'occupied', 'processing')
- current_bill_amount
- opened_at
- closed_at
- timestamps

// TableOrder.php (relación entre mesa y pedido)
- id
- table_id
- order_id
- status
- timestamps

// OrderItem.php
- id
- order_id
- product_id
- quantity
- unit_price
- subtotal
- notes
- timestamps
```

---

### 3. 📈 **Módulo de Estadísticas y Reportes**

#### Descripción
Dashboard analítico con métricas de negocio y visualizaciones.

#### Funcionalidades
- **Métricas Principales**
  - Total de ventas (comparativa con período anterior)
  - Ganancia de las ventas (comparativa)
  - Gráfico de barras con ventas por día/semana

- **Detalle de Productos Vendidos**
  - Tabla con productos más vendidos
  - Total de ventas por producto
  - Total de productos vendidos (unidades)
  - Indicador de productos estrella

- **Filtros**
  - Período (Diario, Semanal, Mensual, Anual)
  - Rango de fechas personalizado
  - Por categoría de producto
  - Por empleado

#### Endpoints API Necesarios
```
GET /api/v1/statistics/sales             # Estadísticas de ventas
GET /api/v1/statistics/products          # Productos más vendidos
GET /api/v1/statistics/employees         # Rendimiento de empleados
GET /api/v1/statistics/trends            # Tendencias de negocio
GET /api/v1/statistics/comparison        # Comparativas de períodos
```

---

### 4. 🍔 **Módulo de Menú/Carta (Productos)**

#### Descripción
Gestión completa del menú con precios, costos y recetas.

#### Funcionalidades
- **Gestión de Productos**
  - Lista de productos con:
    - Nombre del producto
    - Precio de venta
    - Costo de producción
    - Ganancia (precio - costo)
    - Porcentaje de ganancia
  - Búsqueda de productos
  - Selección múltiple

- **Operaciones**
  - Crear productos
  - Editar productos
  - Eliminar productos
  - Organizar por categorías
  - Gestionar recetas (ingredientes por producto)

- **Catálogo Virtual**
  - Compartir catálogo (link público)
  - Configurar datos del negocio para catálogo
  - Horarios de atención
  - Métodos de entrega

#### Endpoints API Necesarios
```
GET    /api/v1/products                  # Lista de productos
POST   /api/v1/products                  # Crear producto
GET    /api/v1/products/{id}             # Detalle de producto
PUT    /api/v1/products/{id}             # Actualizar producto
DELETE /api/v1/products/{id}             # Eliminar producto
GET    /api/v1/products/categories       # Categorías de productos
POST   /api/v1/products/{id}/recipe      # Asignar receta a producto
GET    /api/v1/products/catalog          # Catálogo virtual público
```

#### Modelos a Crear
```php
// Product.php
- id
- name
- description
- price
- cost
- profit (calculated)
- profit_percentage (calculated)
- category_id
- image
- is_available
- has_recipe
- timestamps

// ProductCategory.php
- id
- name
- description
- icon
- sort_order
- timestamps

// Recipe.php (relación producto-ingredientes)
- id
- product_id
- ingredient_id (references inventory_item_id)
- quantity_needed
- unit
- timestamps
```

---

### 5. 📦 **Módulo de Inventario (Materia Prima)**

#### Descripción
Control de inventario con seguimiento de stock y alertas.

#### Funcionalidades
- **Gestión de Items**
  - Lista de items de inventario
  - Costo unitario
  - Cantidad disponible
  - Búsqueda de items

- **Operaciones**
  - Crear item manualmente
  - Carga masiva desde Excel
  - Ajuste de inventario
  - Registro de entradas y salidas
  - Alertas de stock mínimo

- **Kardex**
  - Historial de movimientos por item
  - Entradas (compras a proveedores)
  - Salidas (consumo en producción)
  - Ajustes manuales

#### Endpoints API Necesarios
```
GET    /api/v1/inventory                 # Lista de items
POST   /api/v1/inventory                 # Crear item
GET    /api/v1/inventory/{id}            # Detalle de item
PUT    /api/v1/inventory/{id}            # Actualizar item
DELETE /api/v1/inventory/{id}            # Eliminar item
POST   /api/v1/inventory/bulk-upload     # Carga masiva desde Excel
GET    /api/v1/inventory/{id}/kardex     # Historial de movimientos
POST   /api/v1/inventory/{id}/adjust     # Ajuste de inventario
GET    /api/v1/inventory/alerts          # Items con stock bajo
```

#### Modelos a Crear
```php
// InventoryItem.php
- id
- name
- unit (enum: 'kg', 'g', 'l', 'ml', 'unit')
- unit_cost
- current_stock
- min_stock
- max_stock
- supplier_id
- category
- timestamps

// InventoryMovement.php (Kardex)
- id
- inventory_item_id
- type (enum: 'entry', 'exit', 'adjustment')
- quantity
- unit_cost
- total_cost
- reference_type (nullable - order, purchase, adjustment)
- reference_id (nullable)
- notes
- created_by (user_id)
- timestamps
```

---

### 6. 👥 **Módulo de Clientes**

#### Descripción
Gestión de clientes con historial de compras y cuentas por cobrar.

#### Funcionalidades
- **Gestión de Clientes**
  - Lista de clientes
  - Total de clientes
  - Total por cobrar (suma de deudas)
  - Búsqueda de clientes

- **Información del Cliente**
  - Nombre
  - Teléfono
  - Documento (DUI, NIT, pasaporte)
  - Total por cobrar
  - Historial de compras

- **Operaciones**
  - Crear cliente
  - Editar cliente
  - Ver detalles y transacciones
  - Registrar pagos

#### Endpoints API Necesarios
```
GET    /api/v1/customers                 # Lista de clientes
POST   /api/v1/customers                 # Crear cliente
GET    /api/v1/customers/{id}            # Detalle de cliente
PUT    /api/v1/customers/{id}            # Actualizar cliente
DELETE /api/v1/customers/{id}            # Eliminar cliente
GET    /api/v1/customers/{id}/transactions # Transacciones del cliente
GET    /api/v1/customers/receivables     # Clientes con deuda
POST   /api/v1/customers/{id}/payment    # Registrar pago
```

#### Modelos a Crear
```php
// Customer.php
- id
- name
- phone
- email (nullable)
- document_type (enum: 'dui', 'nit', 'passport', 'other')
- document_number
- address (nullable)
- total_receivable (calculated)
- timestamps
```

---

### 7. 🏭 **Módulo de Proveedores**

#### Descripción
Gestión de proveedores con control de cuentas por pagar.

#### Funcionalidades
- **Gestión de Proveedores**
  - Lista de proveedores
  - Total de proveedores
  - Total por pagar (suma de deudas)
  - Búsqueda de proveedores

- **Información del Proveedor**
  - Nombre
  - Teléfono
  - Documento
  - Total por pagar
  - Historial de compras

- **Operaciones**
  - Crear proveedor
  - Editar proveedor
  - Ver detalles y compras
  - Registrar pagos
  - Asociar gastos a proveedor

#### Endpoints API Necesarios
```
GET    /api/v1/suppliers                 # Lista de proveedores
POST   /api/v1/suppliers                 # Crear proveedor
GET    /api/v1/suppliers/{id}            # Detalle de proveedor
PUT    /api/v1/suppliers/{id}            # Actualizar proveedor
DELETE /api/v1/suppliers/{id}            # Eliminar proveedor
GET    /api/v1/suppliers/{id}/purchases  # Compras al proveedor
GET    /api/v1/suppliers/payables        # Proveedores con deuda
POST   /api/v1/suppliers/{id}/payment    # Registrar pago
```

#### Modelos a Crear
```php
// Supplier.php
- id
- name
- phone
- email (nullable)
- document_type
- document_number
- address (nullable)
- total_payable (calculated)
- timestamps
```

---

### 8. 👨‍💼 **Módulo de Empleados**

#### Descripción
Gestión de personal con roles y permisos.

#### Funcionalidades
- **Gestión de Empleados**
  - Lista de empleados
  - Nombre
  - Teléfono
  - Rol (Mesero, Cocinero, Administrador, etc.)
  - Estado (Activo/Inactivo)

- **Operaciones**
  - Crear empleado
  - Editar empleado
  - Asignar rol
  - Desactivar/activar empleado
  - Ver historial de ventas por empleado

- **Control de Caja por Empleado**
  - Asignar caja a empleado
  - Registrar monto inicial
  - Seguimiento de transacciones del turno
  - Cierre de caja

#### Endpoints API Necesarios
```
GET    /api/v1/employees                 # Lista de empleados
POST   /api/v1/employees                 # Crear empleado
GET    /api/v1/employees/{id}            # Detalle de empleado
PUT    /api/v1/employees/{id}            # Actualizar empleado
DELETE /api/v1/employees/{id}            # Eliminar empleado
GET    /api/v1/employees/{id}/sales      # Ventas del empleado
POST   /api/v1/employees/{id}/assign-register # Asignar caja
```

#### Modelos a Crear
```php
// Employee.php
- id
- user_id (references users.id)
- name
- phone
- role (enum: 'waiter', 'cook', 'cashier', 'admin')
- status (enum: 'active', 'inactive')
- hire_date
- timestamps

// CashRegisterAssignment.php
- id
- employee_id
- cash_register_id
- initial_amount
- final_amount (nullable)
- opened_at
- closed_at (nullable)
- status (enum: 'open', 'closed')
- timestamps
```

---

### 9. 💵 **Módulo de Control de Caja**

#### Descripción
Sistema de apertura y cierre de caja con control de efectivo.

#### Funcionalidades
- **Operaciones de Caja**
  - Abrir caja
  - Seleccionar empleado encargado
  - Registrar monto inicial
  - Seguimiento en tiempo real
  - Cierre de caja con arqueo

- **Funcionalidades Premium** (Modal)
  - Control detallado de ingresos y egresos
  - Registro de movimientos en tiempo real
  - Evitar descuadres al final del día
  - Asignación de cajas por empleado

#### Endpoints API Necesarios
```
GET    /api/v1/cash-registers            # Lista de cajas
POST   /api/v1/cash-registers/open       # Abrir caja
POST   /api/v1/cash-registers/{id}/close # Cerrar caja
GET    /api/v1/cash-registers/{id}/status # Estado actual de caja
GET    /api/v1/cash-registers/{id}/movements # Movimientos de caja
POST   /api/v1/cash-registers/{id}/adjustment # Ajuste de caja
```

#### Modelos a Crear
```php
// CashRegister.php
- id
- name
- location
- status (enum: 'open', 'closed')
- timestamps

// CashRegisterSession.php
- id
- cash_register_id
- employee_id
- initial_amount
- expected_amount (calculated)
- actual_amount (on close)
- difference (calculated)
- opened_at
- closed_at (nullable)
- notes
- timestamps
```

---

### 10. 🧾 **Módulo de Registro de Gastos**

#### Descripción
Sistema completo de registro y categorización de gastos operativos.

#### Funcionalidades
- **Formulario de Registro**
  - Fecha del gasto (con selector de calendario)
  - Categoría del gasto (dropdown)
  - Valor (campo numérico con validación)
  - Nombre/descripción del gasto
  - Agregar proveedor al gasto (toggle opcional)
  - Método de pago:
    - 💵 Efectivo
    - 💳 Tarjeta
    - 🏦 Transferencia bancaria
    - 📱 Otro

- **Estados del Gasto**
  - ✅ Pagado (verde)
  - ⏳ En deuda (tab separado)

- **Categorías de Gastos** (ejemplos)
  - Servicios públicos
  - Alquiler
  - Salarios
  - Compras de inventario
  - Mantenimiento
  - Marketing
  - Otros

#### Endpoints API Necesarios
```
GET    /api/v1/expenses                  # Lista de gastos
POST   /api/v1/expenses                  # Crear gasto
GET    /api/v1/expenses/{id}             # Detalle de gasto
PUT    /api/v1/expenses/{id}             # Actualizar gasto
DELETE /api/v1/expenses/{id}             # Eliminar gasto
GET    /api/v1/expenses/categories       # Categorías de gastos
POST   /api/v1/expenses/{id}/mark-paid   # Marcar como pagado
```

#### Modelos a Crear
```php
// Expense.php
- id
- category_id
- supplier_id (nullable)
- amount
- description
- payment_method (enum: 'cash', 'card', 'transfer', 'other')
- status (enum: 'paid', 'pending')
- expense_date
- paid_at (nullable)
- created_by (user_id)
- timestamps

// ExpenseCategory.php
- id
- name
- icon (nullable)
- color (nullable)
- timestamps
```

---

### 11. ⚙️ **Módulo de Configuraciones**

#### Descripción
Configuración general del negocio y parámetros operativos.

#### Funcionalidades Principales
- **Datos del Negocio**
  - Logo (carga de imagen)
  - Tipo de negocio (selector de categoría)
  - Nombre del negocio
  - Dirección
  - Ciudad
  - Número de celular
  - Correo electrónico
  - Número de documento (DUI, NIT, etc.)

- **Propinas**
  - Configuración de propinas sugeridas
  - Porcentajes predefinidos

- **Impuestos**
  - Configuración de IVA u otros impuestos
  - Aplicación automática en ventas

- **Catálogo Virtual**
  - Horarios de atención
  - Métodos de entrega
  - URL del menú público

#### Endpoints API Necesarios
```
GET    /api/v1/settings                  # Obtener configuraciones
PUT    /api/v1/settings                  # Actualizar configuraciones
POST   /api/v1/settings/logo             # Subir logo
GET    /api/v1/settings/business-types   # Tipos de negocio disponibles
```

#### Modelo a Crear
```php
// BusinessSetting.php (tabla key-value o JSON)
- id
- business_name
- business_type
- address
- city
- phone
- email
- document_number
- logo_url
- tip_percentages (JSON)
- tax_rate
- opening_hours (JSON)
- delivery_methods (JSON)
- timestamps
```

---

## 🔐 Sistema de Permisos y Roles

### Roles Sugeridos
1. **Super Admin** - Acceso total
2. **Administrador** - Gestión completa excepto configuraciones críticas
3. **Cajero** - Ventas, gastos, caja
4. **Mesero** - Mesas, pedidos, ventas
5. **Cocinero** - Ver pedidos, inventario (solo lectura)
6. **Contador** - Reportes, estadísticas, transacciones (solo lectura)

### Permisos por Módulo
```php
// Ejemplo de permisos
'transactions' => ['view', 'create', 'update', 'delete', 'export']
'tables' => ['view', 'create', 'update', 'delete', 'manage']
'products' => ['view', 'create', 'update', 'delete']
'inventory' => ['view', 'create', 'update', 'delete', 'adjust']
'customers' => ['view', 'create', 'update', 'delete']
'suppliers' => ['view', 'create', 'update', 'delete']
'employees' => ['view', 'create', 'update', 'delete']
'cash_registers' => ['view', 'open', 'close', 'manage']
'expenses' => ['view', 'create', 'update', 'delete']
'settings' => ['view', 'update']
'reports' => ['view', 'export']
```

---

## 📱 Consideraciones de UI/UX

### Componentes Vue Reutilizables a Crear

1. **DatePicker** - Selector de fechas
2. **DataTable** - Tabla con paginación, búsqueda y ordenamiento
3. **StatsCard** - Tarjeta de estadísticas con icono y valor
4. **ChartComponent** - Wrapper para gráficos (Chart.js o similar)
5. **Modal** - Modal reutilizable para formularios
6. **SearchBar** - Barra de búsqueda con debounce
7. **FilterDropdown** - Dropdown para filtros
8. **StatusBadge** - Badge para estados (pagado, pendiente, etc.)
9. **PaymentMethodSelector** - Selector de método de pago con iconos
10. **TableGrid** - Grid visual para mesas
11. **ProductCard** - Tarjeta de producto para el menú
12. **InventoryItemRow** - Fila de item de inventario
13. **TransactionRow** - Fila de transacción
14. **FormInput** - Input reutilizable con validación
15. **FileUploader** - Componente para subir archivos

### Diseño Responsivo
- Mobile First
- Adaptación para tablets
- Vista completa para desktop

### Temas de Color
- Definir paleta de colores corporativa
- Modo claro/oscuro (opcional)
- Variables CSS para fácil personalización

---

## 🔄 Integraciones Adicionales

### Pagos
- Integración con pasarelas de pago locales (El Salvador)
- Wompi
- Tigo Money
- Bitcoin (opcional, dado el contexto de El Salvador)

### Impresión
- Tickets de cocina
- Facturas para clientes
- Reportes de caja

### Notificaciones
- Push notifications para nuevos pedidos
- Alertas de stock bajo
- Recordatorios de cuentas por cobrar

### Exportación de Datos
- Exportar a Excel
- Exportar a PDF
- Integración con sistemas contables

---

## 📋 Checklist de Implementación

### Fase 1: Base del Sistema (Sprint 1-2)
- [ ] Configurar proyecto Laravel + Vue
- [ ] Diseñar base de datos completa
- [ ] Crear migraciones
- [ ] Implementar autenticación y autorización
- [ ] Crear seeders con datos de prueba
- [ ] Configurar API REST con Laravel Sanctum

### Fase 2: Módulos Core (Sprint 3-5)
- [ ] Módulo de Movimientos/Transacciones
- [ ] Módulo de Productos/Menú
- [ ] Módulo de Inventario
- [ ] Módulo de Gastos
- [ ] Dashboard básico

### Fase 3: Operaciones de Restaurante (Sprint 6-8)
- [ ] Módulo de Mesas
- [ ] Sistema de Pedidos
- [ ] Control de Caja
- [ ] Módulo de Empleados

### Fase 4: CRM y Reportes (Sprint 9-10)
- [ ] Módulo de Clientes
- [ ] Módulo de Proveedores
- [ ] Módulo de Estadísticas completo
- [ ] Reportes avanzados

### Fase 5: Configuraciones y Pulido (Sprint 11-12)
- [ ] Módulo de Configuraciones
- [ ] Catálogo virtual público
- [ ] Optimización de rendimiento
- [ ] Testing completo
- [ ] Documentación de API

---

## 🧪 Testing

### Backend (Laravel)
- Unit Tests para modelos
- Feature Tests para endpoints
- Tests de integración

### Frontend (Vue)
- Unit Tests para componentes
- E2E Tests con Cypress o Playwright

---

## 📚 Documentación Adicional

### API Documentation
- Usar Swagger/OpenAPI o Postman Collection
- Documentar todos los endpoints
- Incluir ejemplos de requests/responses

### Base de Datos
- Diagrama ER completo
- Documentación de relaciones
- Índices y optimizaciones

---

## 🚀 Próximos Pasos

1. **Revisar y aprobar** este documento
2. **Diseñar la base de datos** completa con todas las relaciones
3. **Crear los modelos** de Laravel con sus relaciones
4. **Implementar las migraciones**
5. **Desarrollar los controllers y servicios**
6. **Crear los componentes Vue**
7. **Integrar frontend con backend**
8. **Testing y refinamiento**

---

## 💡 Notas Importantes

- Implementar **soft deletes** en todos los modelos principales
- Usar **UUIDs** en lugar de IDs incrementales para mayor seguridad
- Implementar **audit logs** para tracking de cambios
- Considerar **multi-tenancy** si se planea escalar a múltiples restaurantes
- Implementar **rate limiting** en la API
- Usar **queues** para operaciones pesadas (reportes, exportaciones)
- Implementar **caching** para mejorar rendimiento (Redis)

---

## 📞 Contacto y Soporte

**Desarrollador:** Erick  
**Stack:** Laravel 12.x + Vue.js + MariaDB  
**Proyecto:** Sistema de Inventario para Restaurante  

---

**Última actualización:** Noviembre 18, 2025  
**Versión del documento:** 1.0  

---

## ⚡ Quick Start para Claude Code

Para implementar este sistema, Claude debe:

1. Leer este documento completo
2. Analizar la estructura de base de datos necesaria
3. Crear migraciones siguiendo las mejores prácticas de Laravel
4. Implementar modelos con relaciones Eloquent
5. Desarrollar controllers siguiendo el patrón Repository
6. Crear componentes Vue 3 con Composition API
7. Implementar validaciones tanto en backend como frontend
8. Seguir principios SOLID y Clean Code
9. Escribir tests para funcionalidades críticas
10. Documentar código y decisiones de arquitectura

**Metodología:** Scrum con sprints de 2 semanas  
**Definición de Done:** Código revisado + Tests passing + Documentado  

---