# ARCHITECTURE.md - Arquitectura del Sistema de Gestión de Restaurantes

**Versión**: 2.0
**Fecha**: 2025-11-15
**Estado**: En Desarrollo Activo → Producción
**Propietario**: Sistema de POS e Inventario para Restaurantes

---

## 📋 Tabla de Contenidos

1. [Visión del Proyecto](#visión-del-proyecto)
2. [Estado Actual del Sistema](#estado-actual-del-sistema)
3. [Arquitectura Técnica](#arquitectura-técnica)
4. [Funcionalidades Implementadas](#funcionalidades-implementadas)
5. [Funcionalidades Faltantes](#funcionalidades-faltantes)
6. [Roadmap de Desarrollo](#roadmap-de-desarrollo)
7. [Plan de Refactorización](#plan-de-refactorización)
8. [Sistema de Métricas y Reportes](#sistema-de-métricas-y-reportes)
9. [Cierre Diario de Caja](#cierre-diario-de-caja)
10. [Preparación para App Móvil](#preparación-para-app-móvil)
11. [Infraestructura y Deployment](#infraestructura-y-deployment)
12. [Mejores Prácticas y Estándares](#mejores-prácticas-y-estándares)
13. [Plan de Testing](#plan-de-testing)
14. [Seguridad y Compliance](#seguridad-y-compliance)

---

## 🎯 Visión del Proyecto

### Objetivo Principal
Desarrollar un **sistema completo de gestión de restaurantes** que permita a propietarios y personal administrar eficientemente:
- Ventas en punto de venta (POS)
- Inventario de productos (ingredientes, bebidas, abarrotes)
- Control de flujo de efectivo
- Métricas de negocio en tiempo real
- Impresión de comandas y tickets
- Gestión por roles (Admin, Chef, Almacenero, Cajero)

### Alcance
**Fase 1 (Actual)**: Sistema web completo con funcionalidades core
**Fase 2 (Q1 2026)**: App móvil para meseros con sincronización en tiempo real
**Fase 3 (Q2 2026)**: Sistema multi-sucursal y franquicias

### Usuarios Target
- **Restaurantes pequeños/medianos**: 1-3 sucursales
- **Flujo alto**: 50-200 ventas diarias
- **Mercado**: México y Latinoamérica
- **Equipo**: 4-15 empleados por sucursal

---

## 📊 Estado Actual del Sistema

### ✅ Qué Funciona Bien

#### Backend (Calificación: 8/10)
1. **Lógica de Negocio Excelente**
   - Deducción automática de inventario al vender
   - Sistema dual de productos (Menu Items con recetas + Simple Products)
   - Manejo sofisticado de devoluciones (restaura según tipo)
   - Generación de números únicos (ventas, devoluciones)
   - Transacciones DB con rollback automático

2. **Arquitectura Sólida**
   - Relaciones Eloquent bien definidas
   - Middleware RBAC funcional
   - Service layer para impresora térmica
   - Scopes útiles en modelos

3. **Seguridad Básica**
   - Mass assignment protection con `$fillable`
   - CSRF protection (Laravel default)
   - Password hashing con Bcrypt

#### Frontend (Calificación: 7.5/10)
1. **UX Destacada**
   - Carrito persistente con localStorage
   - Búsqueda en tiempo real con debounce
   - Loading states en operaciones críticas
   - Feedback visual inmediato
   - Responsive design completo

2. **Tecnología Moderna**
   - Vue 3 Composition API
   - Tailwind CSS (excelente UI)
   - Inertia.js (sin necesidad de API REST)
   - Vite HMR (desarrollo rápido)

### ⚠️ Áreas Críticas de Mejora

#### Backend
1. **Seguridad** (6/10)
   - ❌ NO hay Policies para authorization
   - ❌ Logs con datos sensibles (`$request->all()`)
   - ❌ Sin rate limiting en rutas críticas
   - ❌ Sin control de concurrencia (race conditions posibles)

2. **Performance** (7/10)
   - ⚠️ N+1 queries en búsquedas
   - ⚠️ Sin caching de cálculos costosos
   - ⚠️ `whereDate()` sin índices optimizados
   - ⚠️ `select *` implícito en queries

3. **Mantenibilidad** (7/10)
   - ⚠️ Métodos muy largos (120+ líneas)
   - ⚠️ Lógica mezclada en controllers
   - ⚠️ Sin Form Requests
   - ⚠️ Sin Repository Pattern

4. **Testing** (2/10)
   - ❌ Solo tests de autenticación
   - ❌ Sin tests de lógica de negocio

#### Frontend
1. **Consistencia** (6.5/10)
   - ❌ **100+ emojis hardcoded** (no profesional)
   - ⚠️ Código duplicado (formatters, search logic)
   - ⚠️ Magic numbers y strings
   - ⚠️ Componentes muy largos (600+ líneas)

2. **Validación** (5/10)
   - ❌ Solo validación HTML5 básica
   - ❌ Sin biblioteca de validación
   - ❌ Mensajes de error inconsistentes

3. **Testing** (0/10)
   - ❌ Cero tests de componentes Vue

### 🚨 Funcionalidades Críticas Faltantes

1. **Cierre de Caja Diario**
   - No existe sistema de apertura/cierre de turno
   - No hay cuadre de efectivo vs ventas
   - Sin reportes de diferencias

2. **Métricas y Reportes**
   - Dashboard básico solamente
   - Sin reportes de ventas por período
   - Sin top productos vendidos
   - Sin análisis de mermas
   - Sin forecast de inventario

3. **Control de Inventario Avanzado**
   - No hay alertas automáticas de stock bajo
   - Sin órdenes de compra
   - Sin gestión de proveedores completa
   - Sin trazabilidad de lotes

4. **Auditoría**
   - Logs básicos solamente
   - Sin registro de cambios (audit trail)
   - Sin firma electrónica en operaciones críticas

5. **Impresión**
   - No probado con impresoras físicas
   - Sin cola de impresión
   - Sin reimpresión controlada

---

## 🏗️ Arquitectura Técnica

### Patrón Arquitectónico Actual
**Monolito Moderno con Inertia.js**

```
┌──────────────────────────────────────────┐
│           USUARIO (Browser)              │
└────────────┬─────────────────────────────┘
             │ HTTP/HTTPS
             ▼
┌──────────────────────────────────────────┐
│         Laravel Application              │
│  ┌────────────────────────────────────┐  │
│  │   Inertia Middleware               │  │
│  └────────────┬───────────────────────┘  │
│               │                          │
│  ┌────────────▼───────────────────────┐  │
│  │   Controllers Layer                │  │
│  │  - POSController (Critical)        │  │
│  │  - ReturnController                │  │
│  │  - InventoryController             │  │
│  └────────────┬───────────────────────┘  │
│               │                          │
│  ┌────────────▼───────────────────────┐  │
│  │   Business Logic Layer             │  │
│  │  [FALTA: Services/Actions]         │  │
│  └────────────┬───────────────────────┘  │
│               │                          │
│  ┌────────────▼───────────────────────┐  │
│  │   Models (Eloquent ORM)            │  │
│  │  - 13 modelos principales          │  │
│  └────────────┬───────────────────────┘  │
│               │                          │
│  ┌────────────▼───────────────────────┐  │
│  │   Database (MySQL/SQLite)          │  │
│  └────────────────────────────────────┘  │
└──────────────┬───────────────────────────┘
               │ JSON Response
               ▼
┌──────────────────────────────────────────┐
│        Vue 3 Frontend (SPA)              │
│  ┌────────────────────────────────────┐  │
│  │   Pages (24 componentes)           │  │
│  │  - POS.vue (627 líneas)            │  │
│  │  - Returns/Create.vue (616 líneas) │  │
│  └────────────────────────────────────┘  │
│  ┌────────────────────────────────────┐  │
│  │   Components (15 reutilizables)    │  │
│  └────────────────────────────────────┘  │
│  ┌────────────────────────────────────┐  │
│  │   Layouts (2)                      │  │
│  └────────────────────────────────────┘  │
└──────────────────────────────────────────┘

┌──────────────────────────────────────────┐
│   External Services                      │
│  ┌────────────────────────────────────┐  │
│  │  Thermal Printers (ESC/POS)        │  │
│  │  - Kitchen: 58mm                   │  │
│  │  │  - Customer: 80mm                   │  │
│  └────────────────────────────────────┘  │
└──────────────────────────────────────────┘
```

### Arquitectura Propuesta (Refactorizada)

```
┌──────────────────────────────────────────┐
│         Laravel Application              │
│                                          │
│  ┌────────────────────────────────────┐  │
│  │   HTTP Layer                       │  │
│  │  - Controllers (delgados)          │  │
│  │  - Form Requests (validación)      │  │
│  │  - Middleware (auth, roles, etc)   │  │
│  │  - Resources (transformación)      │  │
│  └────────────┬───────────────────────┘  │
│               │                          │
│  ┌────────────▼───────────────────────┐  │
│  │   Application Layer (NUEVO)        │  │
│  │  - Actions (use cases)             │  │
│  │    • ProcessSaleAction             │  │
│  │    • ProcessReturnAction           │  │
│  │    • CloseCashRegisterAction       │  │
│  │  - DTOs (Data Transfer Objects)    │  │
│  │    • SaleData                      │  │
│  │    • ReturnData                    │  │
│  └────────────┬───────────────────────┘  │
│               │                          │
│  ┌────────────▼───────────────────────┐  │
│  │   Domain Layer                     │  │
│  │  - Services                        │  │
│  │    • InventoryService              │  │
│  │    • CashFlowService               │  │
│  │    • PrinterService                │  │
│  │  - Repositories                    │  │
│  │    • SaleRepository                │  │
│  │    • ProductRepository             │  │
│  │  - Events & Listeners              │  │
│  │    • SaleCompleted                 │  │
│  │    • → DeductInventory             │  │
│  │    • → PrintReceipt                │  │
│  │    • → UpdateCashFlow              │  │
│  └────────────┬───────────────────────┘  │
│               │                          │
│  ┌────────────▼───────────────────────┐  │
│  │   Data Layer                       │  │
│  │  - Models con Observers            │  │
│  │  - Database                        │  │
│  │  - Cache (Redis)                   │  │
│  │  - Queue (Redis/Database)          │  │
│  └────────────────────────────────────┘  │
└──────────────────────────────────────────┘
```

### Stack Tecnológico Completo

#### Backend
| Componente | Tecnología | Versión | Uso |
|------------|-----------|---------|-----|
| Framework | Laravel | 12.0 | Core aplicación |
| Lenguaje | PHP | 8.2+ | Runtime |
| Base de Datos | MySQL | 8.0 | Producción |
| Base de Datos | SQLite | 3.x | Desarrollo |
| Caché | Redis | 7.x | Cache + Sesiones + Colas |
| Queue | Redis/Database | - | Jobs asíncronos |
| Auth | Laravel Breeze | 2.3 | Autenticación |
| API Auth | Laravel Sanctum | 4.0 | Tokens SPA |
| PDF | DomPDF | 3.1 | Generación reportes |
| QR Codes | Simple QR Code | 4.2 | Códigos venta |
| Thermal Print | ESC/POS PHP | 4.0 | Impresoras |

#### Frontend
| Componente | Tecnología | Versión | Uso |
|------------|-----------|---------|-----|
| Framework | Vue.js | 3.5 | UI Framework |
| SPA Bridge | Inertia.js | 2.0 | Server-driven SPA |
| Build Tool | Vite | 6.2 | Build & HMR |
| CSS | Tailwind CSS | 3.2 | Utility-first CSS |
| Forms | @tailwindcss/forms | 0.5 | Form styling |
| Routes | Ziggy | 2.0 | Laravel routes en JS |

#### DevOps
| Componente | Tecnología | Uso |
|------------|-----------|-----|
| Docker | Laravel Sail | Entorno desarrollo |
| Testing | PHPUnit | Tests unitarios/features |
| Linting | Laravel Pint | Code style |
| REPL | Laravel Tinker | Debugging |
| Logs | Laravel Pail | Real-time logs |

---

## ✅ Funcionalidades Implementadas

### Gestión de Ventas (POS)
- ✅ Interfaz de punto de venta
- ✅ Carrito de compras con persistencia
- ✅ Validación de stock en tiempo real
- ✅ Soporte para descuentos
- ✅ Múltiples métodos de pago (efectivo, tarjeta, transferencia, mixto)
- ✅ Generación automática de número de venta
- ✅ Deducción automática de inventario
- ✅ Registro en flujo de efectivo
- ✅ Impresión de ticket cliente (simulado)
- ✅ Impresión de comanda cocina (simulado)

### Gestión de Devoluciones
- ✅ Búsqueda de ventas por número/fecha
- ✅ Devoluciones parciales y totales
- ✅ Restauración inteligente de inventario según tipo
- ✅ Registro de pérdidas operativas
- ✅ Ajuste de flujo de efectivo
- ✅ Generación de número de devolución único
- ✅ Reportes de devoluciones
- ✅ Métricas de tasa de devolución

### Gestión de Inventario
- ✅ CRUD de productos
- ✅ Categorización de productos
- ✅ Unidades de medida (kg, lt, pcs, g, ml)
- ✅ Niveles mínimos/máximos de stock
- ✅ Fechas de caducidad
- ✅ Movimientos de inventario (entrada/salida/ajuste)
- ✅ Historial de movimientos
- ✅ Alertas de stock bajo
- ✅ Productos próximos a vencer

### Gestión de Menú
- ✅ CRUD de items del menú
- ✅ Sistema de recetas (BOM)
- ✅ Cálculo automático de disponibilidad basado en ingredientes
- ✅ Productos simples (venta directa)
- ✅ Imágenes de productos

### Control de Acceso
- ✅ Autenticación (login/logout)
- ✅ 4 roles (Admin, Chef, Almacenero, Cajero)
- ✅ Middleware de protección por rol
- ✅ Gestión de usuarios (básica)

### Reportes Básicos
- ✅ Dashboard con métricas del día
- ✅ Historial de ventas
- ✅ Historial de devoluciones
- ✅ Movimientos de inventario

---

## ❌ Funcionalidades Faltantes

### 🔴 CRÍTICAS (Bloqueantes para Producción)

#### 1. Cierre de Caja Diario
**Prioridad**: CRÍTICA
**Descripción**: Sistema completo de apertura/cierre de turno con cuadre de efectivo

**Componentes necesarios**:
```
Models:
- CashRegister (id, user_id, opened_at, closed_at, opening_balance, expected_balance, actual_balance, difference, status, notes)
- CashRegisterMovement (id, cash_register_id, type, amount, description, created_at)

Controllers:
- CashRegisterController (open, close, addMovement, getCurrentRegister)

Views:
- CashRegister/Open.vue
- CashRegister/Close.vue
- CashRegister/Report.vue
```

**Flujo**:
1. Cajero abre turno con monto inicial
2. Se registran todas las ventas/devoluciones/movimientos
3. Al cierre: cuenta efectivo físico
4. Sistema compara efectivo real vs esperado
5. Genera reporte de cierre con diferencias
6. Admin puede revisar y aprobar cierres

**Estimado**: 3 días

#### 2. Sistema de Permisos Granular (Policies)
**Prioridad**: CRÍTICA (Seguridad)
**Descripción**: Implementar Laravel Policies para authorization

**Archivos a crear**:
```bash
php artisan make:policy SalePolicy --model=Sale
php artisan make:policy ProductPolicy --model=Product
php artisan make:policy CashFlowPolicy --model=CashFlow
php artisan make:policy CashRegisterPolicy --model=CashRegister
```

**Ejemplo**:
```php
// app/Policies/SalePolicy.php
public function viewAny(User $user): bool
{
    return in_array($user->role, ['admin', 'cajero']);
}

public function delete(User $user, Sale $sale): bool
{
    return $user->role === 'admin' && $sale->status !== 'completed';
}
```

**Estimado**: 2 días

#### 3. Control de Concurrencia
**Prioridad**: CRÍTICA (Integridad datos)
**Descripción**: Prevenir race conditions en ventas simultáneas

**Solución**:
```php
// POSController@store
DB::transaction(function() use ($items) {
    foreach ($items as $item) {
        $product = Product::lockForUpdate()->find($item['product_id']);

        if ($product->current_stock < $item['quantity']) {
            throw new \Exception("Stock insuficiente para {$product->name}");
        }

        $product->decrement('current_stock', $item['quantity']);
    }

    // Crear venta...
});
```

**Estimado**: 1 día

### 🟠 ALTAS (Necesarias para operación completa)

#### 4. Métricas y Reportes Avanzados
**Componentes**:
- Ventas por período (día/semana/mes/año)
- Top productos vendidos
- Ventas por categoría
- Ventas por método de pago
- Tendencias de ventas (gráficas)
- Análisis de mermas
- Rentabilidad por producto
- Forecast de inventario

**Estimado**: 5 días

#### 5. Órdenes de Compra
**Descripción**: Gestión de pedidos a proveedores

**Modelos**:
```
PurchaseOrder (id, supplier_id, order_number, order_date, expected_delivery, status, total, notes)
PurchaseOrderItem (id, purchase_order_id, product_id, quantity, unit_cost, total)
```

**Flujo**:
1. Almacenero crea orden de compra
2. Selecciona proveedor y productos
3. Sistema genera número de orden
4. Al recibir: marca como recibida y actualiza inventario automáticamente

**Estimado**: 3 días

#### 6. Gestión de Mesas (Para servicio en restaurante)
**Descripción**: Asignación de ventas a mesas

**Modelo**:
```
Table (id, number, capacity, status, current_sale_id)
```

**Campos adicionales en Sale**:
```
table_id
waiter_user_id
opened_at
```

**Estados de mesa**: libre, ocupada, reservada

**Estimado**: 2 días

### 🟡 MEDIAS (Mejoras de UX/Operación)

#### 7. Historial de Cambios (Audit Trail)
**Descripción**: Registro de quién modificó qué y cuándo

**Implementación**:
```bash
composer require owen-it/laravel-auditing
```

**Modelos a auditar**: Product, Sale, CashFlow, User

**Estimado**: 1 día

#### 8. Notificaciones del Sistema
**Tipos**:
- Stock bajo automático
- Productos por vencer
- Nuevas devoluciones (para admin)
- Cierre de caja pendiente
- Errores de impresión

**Implementación**: Laravel Notifications + Base de datos

**Estimado**: 2 días

#### 9. Impresión Automática Mejorada
**Mejoras**:
- Cola de impresión (retry en caso de fallo)
- Reimpresión controlada (con registro)
- Vista previa antes de imprimir
- Configuración de impresoras por tipo

**Estimado**: 2 días

#### 10. Exportación de Reportes
**Formatos**: Excel, PDF, CSV
**Reportes**: Ventas, Inventario, Flujo de Efectivo, Devoluciones

**Implementación**:
```bash
composer require maatwebsite/excel
```

**Estimado**: 2 días

### 🟢 BAJAS (Nice to have)

- Multi-sucursal
- Programa de lealtad
- Reservaciones
- Inventario por lotes
- Integración con facturación electrónica (SAT México)
- WhatsApp notifications

---

## 🗺️ Roadmap de Desarrollo

### Fase 1: Estabilización y Producción (4 semanas)

#### Sprint 1 (Semana 1-2): Refactorización Crítica
**Objetivos**:
- ✅ Eliminar emojis del código
- ✅ Implementar Policies
- ✅ Agregar Form Requests
- ✅ Resolver N+1 queries
- ✅ Implementar control de concurrencia
- ✅ Extraer lógica a Services/Actions

**Entregables**:
- Código refactorizado y profesional
- Tests de seguridad pasando
- Performance mejorado 30%

#### Sprint 2 (Semana 3): Cierre de Caja
**Objetivos**:
- ✅ Modelo CashRegister completo
- ✅ Apertura de turno
- ✅ Cierre de turno con cuadre
- ✅ Reportes de cierre
- ✅ Tests completos

**Entregables**:
- Sistema de cierre funcional
- Documentación de uso
- Videos de capacitación

#### Sprint 3 (Semana 4): Reportes y Deployment
**Objetivos**:
- ✅ Dashboard con métricas avanzadas
- ✅ Reportes de ventas por período
- ✅ Top productos
- ✅ Exportación a Excel/PDF
- ✅ Preparar para producción
- ✅ Configurar servidor
- ✅ Pruebas con impresoras físicas

**Entregables**:
- Sistema en producción
- Backup automático configurado
- Monitoreo activo

### Fase 2: Optimización y Expansión (4 semanas)

#### Sprint 4: Órdenes de Compra
#### Sprint 5: Gestión de Mesas
#### Sprint 6: Notificaciones y Auditoría
#### Sprint 7: Testing Completo

### Fase 3: App Móvil (8-12 semanas)

**Tecnologías propuestas**:
- **Framework**: Flutter (iOS + Android en una base de código)
- **Backend**: Laravel API (nueva capa REST)
- **Sync**: Pusher o Laravel WebSockets para real-time
- **Offline**: SQLite local con sync cuando hay conexión

**Módulos móviles**:
- Login con PIN rápido
- Lista de mesas
- Tomar órdenes
- Enviar a cocina
- Ver estado de órdenes
- Cerrar cuenta

---

## 🔧 Plan de Refactorización

### Backend

#### 1. Extraer Lógica de Controllers a Actions

**Antes** (POSController.php líneas 75-165):
```php
public function store(Request $request)
{
    // 90 líneas de lógica mezclada
    DB::beginTransaction();
    try {
        $sale = Sale::create([...]);
        foreach ($items as $item) {
            // lógica de deducción
        }
        DB::commit();
    }
}
```

**Después**:
```php
// app/Actions/ProcessSaleAction.php
class ProcessSaleAction
{
    public function __construct(
        private InventoryService $inventoryService,
        private CashFlowService $cashFlowService,
        private PrinterService $printerService
    ) {}

    public function execute(SaleData $data): Sale
    {
        return DB::transaction(function() use ($data) {
            $sale = $this->createSale($data);
            $this->inventoryService->deductFromSale($sale);
            $this->cashFlowService->registerSale($sale);
            $this->printerService->printSale($sale);

            return $sale;
        });
    }
}

// POSController.php
public function store(StoreSaleRequest $request)
{
    $sale = app(ProcessSaleAction::class)->execute(
        SaleData::fromRequest($request)
    );

    return redirect()->route('sales.show', $sale);
}
```

#### 2. Implementar Form Requests

**Crear**:
```bash
php artisan make:request StoreSaleRequest
php artisan make:request StoreReturnRequest
php artisan make:request StoreProductRequest
php artisan make:request UpdateProductRequest
```

**Ejemplo** (StoreSaleRequest.php):
```php
class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Sale::class);
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'in:efectivo,tarjeta,transferencia,mixto'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Debe agregar al menos un producto',
            'payment_method.required' => 'Seleccione un método de pago',
        ];
    }
}
```

#### 3. Crear DTOs (Data Transfer Objects)

```php
// app/DTOs/SaleData.php
class SaleData
{
    public function __construct(
        public readonly array $items,
        public readonly string $paymentMethod,
        public readonly float $discount,
        public readonly float $tax,
        public readonly ?int $tableId = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            items: $request->input('items'),
            paymentMethod: $request->input('payment_method'),
            discount: $request->input('discount', 0),
            tax: $request->input('tax', 0),
            tableId: $request->input('table_id')
        );
    }

    public function calculateTotal(): float
    {
        $subtotal = collect($this->items)->sum('total');
        $discountAmount = $subtotal * ($this->discount / 100);
        $taxAmount = ($subtotal - $discountAmount) * ($this->tax / 100);

        return $subtotal - $discountAmount + $taxAmount;
    }
}
```

#### 4. Implementar Repository Pattern

```php
// app/Repositories/SaleRepository.php
interface SaleRepositoryInterface
{
    public function findByNumber(string $number): ?Sale;
    public function getTodaySales(): Collection;
    public function getSalesByDateRange(Carbon $from, Carbon $to): Collection;
    public function getTopSellingProducts(int $limit = 10): Collection;
}

class SaleRepository implements SaleRepositoryInterface
{
    public function findByNumber(string $number): ?Sale
    {
        return Cache::remember("sale.{$number}", 3600, function() use ($number) {
            return Sale::with(['saleItems.menuItem', 'saleItems.simpleProduct', 'user'])
                ->where('sale_number', $number)
                ->first();
        });
    }

    public function getTodaySales(): Collection
    {
        return Sale::with('user')
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->orderByDesc('created_at')
            ->get();
    }
}
```

#### 5. Usar Enums en lugar de Strings

```php
// app/Enums/PaymentMethod.php
enum PaymentMethod: string
{
    case CASH = 'efectivo';
    case CARD = 'tarjeta';
    case TRANSFER = 'transferencia';
    case MIXED = 'mixto';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'Efectivo',
            self::CARD => 'Tarjeta',
            self::TRANSFER => 'Transferencia',
            self::MIXED => 'Pago Mixto',
        };
    }
}

// En modelo:
protected $casts = [
    'payment_method' => PaymentMethod::class,
];

// En validación:
'payment_method' => ['required', new Enum(PaymentMethod::class)],
```

### Frontend

#### 1. Eliminar Emojis

**Crear archivo de iconos**:
```javascript
// resources/js/utils/icons.js
export const icons = {
    sales: '<svg>...</svg>',  // Icono de ventas
    inventory: '<svg>...</svg>',  // Icono de inventario
    cash: '<svg>...</svg>',  // Icono de dinero
    // etc...
}
```

**O usar biblioteca**:
```bash
npm install @heroicons/vue
```

**Reemplazar**:
```vue
<!-- Antes -->
<span class="text-2xl">💰</span>

<!-- Después -->
<CurrencyDollarIcon class="w-6 h-6 text-green-600" />
```

#### 2. Extraer Utilidades Comunes

```javascript
// resources/js/utils/formatters.js
export const formatPrice = (price) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(price);
};

export const formatDate = (date, format = 'short') => {
    const options = format === 'short'
        ? { year: 'numeric', month: 'short', day: 'numeric' }
        : { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };

    return new Date(date).toLocaleDateString('es-MX', options);
};

export const formatNumber = (number, decimals = 2) => {
    return parseFloat(number).toFixed(decimals);
};
```

#### 3. Crear Composables Reutilizables

```javascript
// resources/js/composables/useSearch.js
import { ref } from 'vue';
import { debounce } from 'lodash';

export function useSearch(searchFn, delay = 300) {
    const query = ref('');
    const results = ref([]);
    const isSearching = ref(false);

    const search = debounce(async () => {
        if (!query.value) {
            results.value = [];
            return;
        }

        isSearching.value = true;
        try {
            results.value = await searchFn(query.value);
        } finally {
            isSearching.value = false;
        }
    }, delay);

    return { query, results, isSearching, search };
}

// Uso en componente:
const { query, results, isSearching, search } = useSearch(async (q) => {
    const response = await fetch(`/api/products/search?q=${q}`);
    return response.json();
});
```

#### 4. Sistema de Toasts Global

```javascript
// resources/js/plugins/toast.js
import { reactive } from 'vue';

const toasts = reactive([]);

export const useToast = () => {
    const add = (message, type = 'info', duration = 5000) => {
        const id = Date.now();
        toasts.push({ id, message, type });

        if (duration) {
            setTimeout(() => remove(id), duration);
        }
    };

    const remove = (id) => {
        const index = toasts.findIndex(t => t.id === id);
        if (index > -1) toasts.splice(index, 1);
    };

    return {
        toasts,
        success: (msg) => add(msg, 'success'),
        error: (msg) => add(msg, 'error'),
        warning: (msg) => add(msg, 'warning'),
        info: (msg) => add(msg, 'info'),
    };
};
```

#### 5. Refactorizar Componentes Largos

**POS.vue** (627 líneas) → Dividir en:
- `POS.vue` (layout principal)
- `ProductGrid.vue` (grid de productos)
- `Cart.vue` (carrito lateral)
- `CartItem.vue` (item individual)
- `PaymentModal.vue` (modal de pago)

---

## 📊 Sistema de Métricas y Reportes

### Dashboard Principal

**Métricas en Tiempo Real**:
```
┌─────────────────────────────────────────────────┐
│  HOY                                            │
│  ┌──────────┬──────────┬──────────┬──────────┐  │
│  │ Ventas   │ Ticket   │ Items    │ Clientes │  │
│  │ $12,450  │ $325     │ 156      │ 38       │  │
│  │ +15% ⬆   │ +5% ⬆    │ +12% ⬆   │ -3% ⬇    │  │
│  └──────────┴──────────┴──────────┴──────────┘  │
│                                                 │
│  GRÁFICA DE VENTAS (Últimas 24 horas)          │
│  [Gráfica de línea con ventas por hora]        │
│                                                 │
│  TOP 5 PRODUCTOS                                │
│  1. Tacos de Pollo........ $2,340 (78 vendidos)│
│  2. Refresco 600ml........ $1,890 (105 vendidos)│
│  3. Quesadillas........... $1,450 (45 vendidos)│
│                                                 │
│  ALERTAS                                        │
│  ⚠️  3 productos con stock bajo                 │
│  ⚠️  2 productos por vencer esta semana         │
│  ✅  Cierre de caja pendiente                   │
└─────────────────────────────────────────────────┘
```

### Reportes Necesarios

#### 1. Reporte de Ventas Detallado
**Filtros**:
- Rango de fechas
- Cajero
- Método de pago
- Categoría de producto

**Datos**:
- Total de ventas
- Cantidad de tickets
- Ticket promedio
- Ventas por hora
- Ventas por día de semana
- Comparativa con período anterior

**Formato**: Tabla + Gráfica + Exportación Excel/PDF

#### 2. Análisis de Productos
**Métricas**:
- Top vendidos (por cantidad y por monto)
- Productos sin movimiento
- Rotación de inventario
- Margen de ganancia por producto
- Productos con más devoluciones

#### 3. Reporte de Inventario
**Datos**:
- Valor total de inventario
- Productos con stock bajo
- Productos vencidos/por vencer
- Movimientos del período
- Mermas y pérdidas
- Costo de inventario vendido (COGS)

#### 4. Flujo de Efectivo
**Datos**:
- Ingresos por categoría
- Egresos por categoría
- Saldo neto
- Comparativa mensual
- Proyección de flujo

#### 5. Reporte de Cierre de Caja
```
CIERRE DE CAJA #00123
Fecha: 15/11/2025
Turno: Matutino (08:00 - 16:00)
Cajero: Juan Pérez

APERTURA
Efectivo inicial: $1,000.00

MOVIMIENTOS
Ventas en efectivo:    $8,450.00  (34 tickets)
Ventas con tarjeta:    $5,230.00  (18 tickets)
Ventas transferencia:  $1,890.00  (7 tickets)
Devoluciones:         -$  230.00  (2 tickets)
Retiros de caja:      -$2,000.00  (1 retiro)
Gastos menores:       -$  150.00  (café, limpieza)

CIERRE
Efectivo esperado:     $9,070.00
Efectivo contado:      $9,100.00
Diferencia:           +$   30.00  ✅

RESUMEN
Total vendido:        $15,570.00
Ticket promedio:      $  265.00
Clientes atendidos:   59

Firma cajero: _______________
Firma supervisor: _______________
```

---

## 💰 Cierre Diario de Caja

### Modelo de Datos

```php
// Migration: create_cash_registers_table
Schema::create('cash_registers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();  // Cajero
    $table->foreignId('approved_by')->nullable()->constrained('users');  // Supervisor
    $table->timestamp('opened_at');
    $table->timestamp('closed_at')->nullable();
    $table->decimal('opening_balance', 10, 2);  // Fondo inicial
    $table->decimal('expected_cash', 10, 2)->nullable();  // Esperado
    $table->decimal('actual_cash', 10, 2)->nullable();  // Contado físicamente
    $table->decimal('expected_card', 10, 2)->nullable();
    $table->decimal('expected_transfer', 10, 2)->nullable();
    $table->decimal('total_sales', 10, 2)->nullable();
    $table->decimal('total_returns', 10, 2)->nullable();
    $table->decimal('total_withdrawals', 10, 2)->nullable();  // Retiros
    $table->decimal('total_expenses', 10, 2)->nullable();  // Gastos menores
    $table->decimal('difference', 10, 2)->nullable();  // Diferencia
    $table->enum('status', ['open', 'closed', 'approved'])->default('open');
    $table->text('notes')->nullable();
    $table->timestamps();
});

// Migration: create_cash_register_movements
Schema::create('cash_register_movements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cash_register_id')->constrained()->onDelete('cascade');
    $table->enum('type', ['withdrawal', 'expense', 'deposit']);
    $table->decimal('amount', 10, 2);
    $table->string('description');
    $table->text('notes')->nullable();
    $table->foreignId('authorized_by')->nullable()->constrained('users');
    $table->timestamps();
});
```

### Flujo de Trabajo

**1. Apertura de Turno**:
```
Cajero llega → Login → "Abrir Caja"
↓
Ingresa monto inicial (ej: $1,000)
↓
Sistema crea CashRegister con status='open'
↓
Cajero puede comenzar a vender
```

**2. Durante el Turno**:
```
Ventas → Se registran automáticamente con el cash_register_id
Retiros → Cajero solicita retiro (ej: para depositar exceso)
Gastos → Cajero registra gasto menor (café, propinas, etc.)
```

**3. Cierre de Turno**:
```
Cajero termina → "Cerrar Caja"
↓
Sistema calcula:
- Total efectivo esperado = opening + ventas_efectivo - retiros - gastos
- Total tarjetas esperado = ventas_tarjeta
- Total transferencias esperado = ventas_transferencia
↓
Cajero cuenta efectivo físico
↓
Ingresa monto contado
↓
Sistema calcula diferencia
↓
Si diferencia > tolerancia (ej: ±$50):
  → Requiere justificación escrita
  → Notifica a supervisor
Sino:
  → Cierre automático
↓
Genera PDF del reporte
↓
Estado cambia a 'closed'
```

**4. Revisión de Supervisor**:
```
Supervisor revisa cierres del día
↓
Aprueba o solicita correcciones
↓
Estado cambia a 'approved'
↓
Efectivo se deposita/resguarda
```

### Componentes UI Necesarios

```vue
<!-- CashRegister/Open.vue -->
- Input de monto inicial
- Confirmación con contraseña
- Impresión de recibo de apertura

<!-- CashRegister/Close.vue -->
- Resumen automático de ventas
- Formulario de conteo físico
- Campo de notas/justificación
- Confirmación

<!-- CashRegister/Report.vue -->
- Vista detallada del cierre
- Desglose de ventas
- Lista de movimientos
- Gráfica de ventas por hora
- Botón de impresión/PDF
- Botón de aprobación (supervisor)

<!-- CashRegister/History.vue -->
- Lista de cierres anteriores
- Filtros por cajero/fecha
- Indicadores de diferencias
```

---

## 📱 Preparación para App Móvil

### Arquitectura API

**Agregar Capa REST API** (sin romper Inertia):

```php
// routes/api.php
Route::prefix('v1')->group(function() {
    Route::post('/login', [Api\AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function() {
        Route::get('/tables', [Api\TableController::class, 'index']);
        Route::post('/tables/{table}/open', [Api\TableController::class, 'open']);

        Route::get('/menu', [Api\MenuController::class, 'index']);

        Route::post('/orders', [Api\OrderController::class, 'store']);
        Route::get('/orders/{order}', [Api\OrderController::class, 'show']);
        Route::patch('/orders/{order}/status', [Api\OrderController::class, 'updateStatus']);

        Route::get('/kitchen/orders', [Api\KitchenController::class, 'pending']);
        Route::post('/kitchen/orders/{order}/complete', [Api\KitchenController::class, 'complete']);
    });
});
```

### Sincronización en Tiempo Real

**Opciones**:

1. **Laravel WebSockets** (Auto-hospedado):
```bash
composer require beyondcode/laravel-websockets
```

2. **Pusher** (Servicio):
```bash
composer require pusher/pusher-php-server
```

3. **Socket.io + Redis** (Personalizado)

**Eventos a Transmitir**:
- Nueva orden creada → Pantalla cocina
- Orden completada → App mesero
- Mesa disponible → App host
- Actualización de inventario → POS

### Base de Datos Local (App)

**Estrategia de Sync**:
```
App Móvil (SQLite local)
    ↕ Sync bidireccional
Servidor (MySQL)
```

**Flujo**:
1. App descarga menú completo al iniciar turno
2. Mesero toma órdenes → Guarda local
3. Cuando hay conexión → Sync automático al servidor
4. Servidor procesa y notifica a cocina
5. App recibe confirmación y actualiza estado

**Manejo de Conflictos**:
- Timestamp-based conflict resolution
- Server always wins en caso de empate
- Queue local de operaciones pendientes

---

## 🚀 Infraestructura y Deployment

### Entorno de Desarrollo (Debian 12)

**Tu Máquina Local**:
- **OS**: Debian 12 (Bookworm)
- **Método**: Laravel Sail (Docker) ✅ **RECOMENDADO**
- **Alternativa**: Desarrollo nativo (si prefieres)

#### Instalación en Debian 12

**Opción 1: Laravel Sail con Docker (✅ RECOMENDADO)**

```bash
# 1. Instalar Docker en Debian 12
sudo apt update && sudo apt upgrade -y
sudo apt install -y docker.io docker-compose
sudo systemctl enable docker
sudo systemctl start docker

# 2. Agregar tu usuario al grupo docker (para no usar sudo)
sudo usermod -aG docker $USER

# 3. IMPORTANTE: Cerrar sesión y volver a entrar
# O ejecutar: newgrp docker

# 4. Verificar Docker
docker --version
docker-compose --version

# 5. Clonar proyecto
git clone https://github.com/ha23039/restaurant-inventory.git
cd restaurant-inventory

# 6. Instalar dependencias (primera vez)
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs

# 7. Configurar entorno
cp .env.example .env

# 8. Iniciar Sail
./vendor/bin/sail up -d

# 9. Generar clave
./vendor/bin/sail artisan key:generate

# 10. Ejecutar migraciones
./vendor/bin/sail artisan migrate --seed

# 11. Instalar dependencias NPM
./vendor/bin/sail npm install

# 12. Iniciar Vite (desarrollo)
./vendor/bin/sail npm run dev

# Aplicación disponible en:
# - http://localhost (Laravel)
# - http://localhost:5173 (Vite HMR)

# Comandos útiles de Sail:
./vendor/bin/sail up -d          # Iniciar contenedores
./vendor/bin/sail down           # Detener contenedores
./vendor/bin/sail artisan        # Ejecutar artisan
./vendor/bin/sail composer       # Ejecutar composer
./vendor/bin/sail npm            # Ejecutar npm
./vendor/bin/sail mysql          # Conectar a MySQL
./vendor/bin/sail shell          # Shell dentro del contenedor

# Crear alias para facilitar (opcional):
echo "alias sail='./vendor/bin/sail'" >> ~/.bashrc
source ~/.bashrc
# Ahora puedes usar: sail up -d, sail artisan, etc.
```

**Servicios incluidos en Sail**:
- PHP 8.4
- MySQL 8.0
- Redis
- Mailpit (email testing)
- Selenium (para tests browser)

**Opción 2: Nativo (Alternativa)**

```bash
# 1. Actualizar sistema
sudo apt update && sudo apt upgrade -y

# 2. Instalar PHP 8.2 y extensiones necesarias
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-sqlite3 \
    php8.2-curl php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath \
    php8.2-intl php8.2-redis

# 3. Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 4. Instalar Node.js 18 LTS
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# 5. Instalar MySQL (opcional, si no usas SQLite)
sudo apt install -y mysql-server
sudo mysql_secure_installation

# 6. Clonar proyecto
git clone https://github.com/tuusuario/restaurant-pos.git
cd restaurant-pos

# 7. Instalar dependencias
composer install
npm install

# 8. Configurar entorno
cp .env.example .env
php artisan key:generate

# Para SQLite (recomendado desarrollo):
touch database/database.sqlite

# Para MySQL:
# Crear base de datos: mysql -u root -p
# CREATE DATABASE restaurant_pos;
# Editar .env con credenciales MySQL

# 9. Migrar base de datos
php artisan migrate --seed

# 10. Compilar assets
npm run dev

# 11. Iniciar servidor de desarrollo
php artisan serve
# http://localhost:8000

# En otra terminal, iniciar Vite
npm run dev
# http://localhost:5173
```

**Opción 2: Laravel Sail (Docker)**

```bash
# Instalar Docker en Debian 12
sudo apt install -y docker.io docker-compose
sudo usermod -aG docker $USER
# Cerrar sesión y volver a entrar

# Iniciar proyecto
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install && ./vendor/bin/sail npm run dev
```

### Entorno de Producción (WebEmpresa - Hosting Compartido)

**Características de WebEmpresa**:
- ✅ cPanel
- ✅ PHP 8.1/8.2 disponible
- ✅ MySQL 8.0
- ✅ SSL gratuito (Let's Encrypt)
- ✅ Backups diarios automáticos
- ✅ Dominio incluido
- ⚠️ NO tiene acceso SSH completo (o limitado)
- ⚠️ NO puede instalar Redis (cache database fallback)
- ⚠️ NO puede configurar Supervisor (queue database fallback)
- ⚠️ NO puede configurar Nginx (usa Apache)
- ⚠️ Límites de recursos compartidos

#### Configuración Específica para WebEmpresa

**Paso 1: Preparar el Proyecto Localmente**

```bash
# En tu Debian 12

# 1. Limpiar y optimizar
composer install --optimize-autoloader --no-dev
npm run build

# 2. Crear .env de producción
cp .env.example .env.production

# Editar .env.production:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com

# Base de datos (WebEmpresa te dará estos datos)
DB_CONNECTION=mysql
DB_HOST=localhost  # O el que te den
DB_PORT=3306
DB_DATABASE=tu_usuario_restaurantpos
DB_USERNAME=tu_usuario
DB_PASSWORD=contraseña_segura

# IMPORTANTE: No usar Redis en hosting compartido
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database  # NO "redis" o "sync"

# Configuración de impresoras
KITCHEN_PRINTER_IP=192.168.1.100
CUSTOMER_PRINTER_IP=192.168.1.101
# etc...

# 3. Crear archivo ZIP para subir
zip -r restaurant-pos.zip . -x "*.git*" "node_modules/*" "tests/*" "storage/logs/*"
```

**Paso 2: Configurar en cPanel de WebEmpresa**

**A. Crear Base de Datos MySQL**:
1. Login a cPanel
2. Ir a "Bases de datos MySQL"
3. Crear nueva base de datos: `tuusuario_restaurantpos`
4. Crear usuario: `tuusuario_admin`
5. Asignar todos los privilegios al usuario en esa BD
6. Anotar credenciales para `.env`

**B. Configurar Dominio y Directorio**:
1. En cPanel → "Administrador de archivos"
2. Ir a `public_html` (o el directorio de tu dominio)
3. **IMPORTANTE**: El directorio público de Laravel debe ser `public/`
4. Configurar dominio para apuntar a `public_html/tudominio/public`

**Estructura recomendada**:
```
/home/tuusuario/
├── public_html/
│   └── tudominio.com/     ← Aquí va el proyecto
│       ├── app/
│       ├── bootstrap/
│       ├── config/
│       ├── database/
│       ├── public/        ← Document root del dominio
│       ├── resources/
│       ├── routes/
│       ├── storage/
│       └── vendor/
```

**C. Subir Archivos**:

**Opción 1: Via cPanel File Manager**:
1. Subir `restaurant-pos.zip`
2. Extraer en el directorio del dominio
3. Eliminar el ZIP

**Opción 2: Via FTP** (si tienes acceso):
```bash
# Desde tu Debian
lftp -u tuusuario ftp.tudominio.com
cd public_html/tudominio.com
mirror -R --exclude .git/ --exclude node_modules/ .
```

**Opción 3: Via SSH** (si WebEmpresa lo permite):
```bash
# Solo si tienes SSH habilitado
ssh tuusuario@tudominio.com
cd public_html/tudominio.com
git clone https://github.com/tuusuario/restaurant-pos.git .
composer install --no-dev
```

**D. Configurar Permisos**:
```bash
# Via SSH o terminal de cPanel
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

**E. Ejecutar Migraciones**:
```bash
# Via SSH
php artisan migrate --force

# O crear un script temporal: install.php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->call('migrate', ['--force' => true]);
echo "Migraciones completadas\n";

# Ejecutar desde browser: https://tudominio.com/install.php
# ¡BORRAR DESPUÉS!
```

**F. Optimizar Laravel**:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Configuración de .htaccess (Apache)

WebEmpresa usa Apache, crear/verificar `.htaccess` en `public/`:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Seguridad adicional
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# Proteger .env
<Files .env>
    Order allow,deny
    Deny from all
</Files>

# Cache control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

#### Limitaciones y Workarounds en Hosting Compartido

**1. Queue Workers (NO Supervisor disponible)**

**Problema**: No puedes ejecutar `php artisan queue:work` permanentemente

**Solución**: Usar Cron Jobs de cPanel

```bash
# En cPanel → Cron Jobs
# Agregar tarea cada minuto:

* * * * * cd /home/tuusuario/public_html/tudominio.com && php artisan queue:work --stop-when-empty

# O configurar schedule:
* * * * * cd /home/tuusuario/public_html/tudominio.com && php artisan schedule:run >> /dev/null 2>&1
```

**2. Redis NO Disponible**

**Solución**: Usar database driver (ya configurado en .env)

```env
# En .env
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

**3. Límite de Memoria PHP**

**Problema**: Hosting compartido suele tener límite de 128-256MB

**Solución**: Crear `php.ini` o `.user.ini` en la raíz:

```ini
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

**4. Permisos de Archivos**

**Solución**: Usar permisos 755/644 (NO 777)

```bash
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 755 storage bootstrap/cache
```

**5. SSL Gratuito**

WebEmpresa incluye Let's Encrypt gratis:
1. cPanel → SSL/TLS
2. Activar AutoSSL
3. Forzar HTTPS en `.htaccess`:

```apache
# Redirigir HTTP a HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### Backups Automatizados

```bash
# Instalar
composer require spatie/laravel-backup

# Configurar
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"

# config/backup.php
'backup' => [
    'name' => 'restaurant-pos',
    'source' => [
        'files' => [
            'include' => [
                base_path(),
            ],
            'exclude' => [
                base_path('vendor'),
                base_path('node_modules'),
            ],
        ],
        'databases' => [
            'mysql',
        ],
    ],
    'destination' => [
        'disks' => [
            'local',
            's3',  // Amazon S3
        ],
    ],
],

# Programar en schedule
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('backup:clean')->daily()->at('01:00');
    $schedule->command('backup:run')->daily()->at('02:00');
}
```

### Monitoreo

**Herramientas Recomendadas**:
1. **Laravel Telescope** (desarrollo/staging):
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

2. **Sentry** (errores en producción):
```bash
composer require sentry/sentry-laravel
```

3. **New Relic / Scout APM** (performance):
- Monitoreo de queries lentas
- Detección de N+1
- Alertas de downtime

4. **Uptime Robot** (disponibilidad):
- Ping cada 5 minutos
- Alerta por email/SMS si cae

### CI/CD con GitHub Actions

```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2

      - name: Install dependencies
        run: composer install --prefer-dist --no-dev

      - name: Run tests
        run: php artisan test

      - name: Deploy to server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USER }}
          key: ${{ secrets.SERVER_SSH_KEY }}
          script: |
            cd /var/www/restaurant-pos
            git pull origin main
            composer install --no-dev
            npm install && npm run build
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            sudo systemctl restart php8.2-fpm
```

---

## 📚 Mejores Prácticas y Estándares

### Código Limpio

1. **Sin emojis en código de producción**
2. **Nombres descriptivos en inglés** (código) y español (negocio)
3. **Métodos cortos**: Máximo 50 líneas
4. **Clases con responsabilidad única**
5. **Documentación en funciones complejas**

### Commits y Versionado

**Formato de Commits**:
```
tipo(scope): mensaje corto

Descripción detallada opcional

tipo: feat, fix, refactor, docs, test, chore
scope: pos, inventory, auth, api, ui

Ejemplos:
feat(pos): agregar soporte para pagos con QR
fix(inventory): corregir cálculo de stock disponible
refactor(sales): extraer lógica a ProcessSaleAction
```

**Versionado Semántico**:
- v1.0.0 → Lanzamiento inicial
- v1.1.0 → Nuevas funcionalidades (cierre de caja)
- v1.1.1 → Bugfix
- v2.0.0 → Cambios breaking (API móvil)

### Revisión de Código

**Checklist PR**:
- [ ] Tests pasan
- [ ] Sin emojis
- [ ] Sin `dd()`, `dump()`, `console.log()` en código
- [ ] Código formateado (Pint)
- [ ] Sin N+1 queries
- [ ] Validaciones apropiadas
- [ ] Authorization implementada
- [ ] Documentación actualizada

---

## 🧪 Plan de Testing

### Cobertura Objetivo

| Tipo | Cobertura Meta | Actual |
|------|---------------|--------|
| Unitarios | 80% | 5% |
| Features | 70% | 10% |
| E2E | 50% | 0% |

### Tests Prioritarios

#### Backend (PHPUnit)

**1. Tests de Lógica de Negocio** (CRÍTICOS):
```php
// tests/Feature/POSTest.php
public function test_cannot_sell_product_without_sufficient_stock()
{
    $product = Product::factory()->create(['current_stock' => 1]);
    $menuItem = MenuItem::factory()->create();
    Recipe::factory()->create([
        'menu_item_id' => $menuItem->id,
        'product_id' => $product->id,
        'quantity_needed' => 2,  // Necesita 2 pero solo hay 1
    ]);

    $response = $this->actingAs($this->cajero)
        ->post(route('pos.store'), [
            'items' => [
                ['type' => 'menu', 'id' => $menuItem->id, 'quantity' => 1]
            ],
            'payment_method' => 'efectivo',
        ]);

    $response->assertSessionHasErrors();
    $this->assertEquals(1, $product->fresh()->current_stock);  // No debe cambiar
}

public function test_inventory_is_deducted_after_sale()
{
    // Test que verifica deducción correcta
}

public function test_cash_flow_entry_is_created_after_sale()
{
    // Test que verifica registro en flujo de efectivo
}

public function test_concurrent_sales_do_not_oversell()
{
    // Test de concurrencia
}
```

**2. Tests de Seguridad**:
```php
// tests/Feature/AuthorizationTest.php
public function test_cajero_cannot_access_cash_flow()
{
    $response = $this->actingAs($this->cajero)
        ->get(route('cashflow.index'));

    $response->assertForbidden();
}

public function test_almacenero_cannot_access_pos()
{
    $response = $this->actingAs($this->almacenero)
        ->get(route('pos.index'));

    $response->assertForbidden();
}
```

**3. Tests de Devoluciones**:
```php
public function test_simple_product_return_restores_inventory()
public function test_menu_item_return_creates_operational_loss()
public function test_cannot_return_more_than_sold()
```

#### Frontend (Vitest + Vue Test Utils)

```javascript
// tests/unit/components/Cart.spec.js
import { mount } from '@vue/test-utils'
import Cart from '@/Components/Cart.vue'

describe('Cart Component', () => {
  it('calculates total correctly', () => {
    const wrapper = mount(Cart, {
      props: {
        items: [
          { id: 1, name: 'Item 1', price: 100, quantity: 2 },
          { id: 2, name: 'Item 2', price: 50, quantity: 1 },
        ]
      }
    })

    expect(wrapper.text()).toContain('$250.00')
  })

  it('applies discount correctly', async () => {
    // Test de descuentos
  })
})
```

#### E2E (Laravel Dusk o Playwright)

```php
// tests/Browser/POSWorkflowTest.php
public function test_complete_sale_workflow()
{
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->cajero)
            ->visit('/sales/pos')
            ->clickLink('Tacos de Pollo')
            ->assertSee('1')  // Cantidad en carrito
            ->press('Procesar Venta')
            ->type('payment_method', 'efectivo')
            ->press('Completar')
            ->assertSee('Venta completada exitosamente')
            ->assertPathIs('/sales/*');
    });
}
```

---

## 🔒 Seguridad y Compliance

### Checklist de Seguridad

- [ ] **HTTPS obligatorio en producción**
- [ ] **Headers de seguridad configurados** (CSP, X-Frame-Options, etc.)
- [ ] **Rate limiting en todas las rutas públicas**
- [ ] **Validación de input en cliente Y servidor**
- [ ] **Sanitización de output (XSS prevention)**
- [ ] **SQL Injection protection** (usar Query Builder/Eloquent)
- [ ] **CSRF protection habilitado**
- [ ] **Password hashing con Bcrypt/Argon2**
- [ ] **Logs NO contienen información sensible**
- [ ] **Archivos de configuración NO en git**
- [ ] **Dependencies actualizadas** (composer audit, npm audit)
- [ ] **Backups encriptados**
- [ ] **2FA para admin** (opcional pero recomendado)

### Compliance (México)

**Facturación Electrónica (CFDi 4.0)**:
- Integración con PAC (Proveedor Autorizado de Certificación)
- Timbrado de facturas
- Generación de XML y PDF
- Cancelación de facturas
- Reporte a SAT

**Protección de Datos Personales (LFPDPPP)**:
- Aviso de privacidad
- Consentimiento para datos personales
- Derecho ARCO (Acceso, Rectificación, Cancelación, Oposición)
- Seguridad de datos

---

## 📝 Anexos

### Glosario de Términos

- **POS**: Point of Sale (Punto de Venta)
- **BOM**: Bill of Materials (Lista de Materiales/Receta)
- **SKU**: Stock Keeping Unit (Unidad de Gestión de Inventario)
- **COGS**: Cost of Goods Sold (Costo de Mercancía Vendida)
- **Comanda**: Orden de cocina
- **Merma**: Pérdida de producto por vencimiento/daño
- **Cuadre**: Proceso de cierre de caja

### Referencias

- [Laravel Documentation](https://laravel.com/docs)
- [Vue 3 Guide](https://vuejs.org/guide/)
- [Inertia.js Docs](https://inertiajs.com)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [ESC/POS Command Reference](https://reference.epson-biz.com/modules/ref_escpos/)

---

**Última Actualización**: 2025-11-15
**Próxima Revisión**: 2025-12-01
**Responsable**: Equipo de Desarrollo

---

## 🎯 Conclusión

Este documento define la arquitectura completa del sistema. Los próximos pasos inmediatos son:

1. **Semana 1-2**: Refactorización del código existente
2. **Semana 3**: Implementación de cierre de caja
3. **Semana 4**: Reportes y preparación para producción

**Meta**: Sistema en producción estable para **15 de diciembre de 2025**.

Para dudas o sugerencias sobre esta arquitectura, crear un issue en GitHub o contactar al equipo de desarrollo.
