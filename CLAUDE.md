# CLAUDE.md - Guía para Asistentes IA del Sistema de Inventario y POS para Restaurantes

**Última Actualización**: 2025-11-15
**Proyecto**: Sistema de Gestión de Restaurantes con POS, Inventario e Impresión Térmica
**Stack**: Laravel 12 + Inertia.js + Vue 3 + Tailwind CSS

---

## Tabla de Contenidos

1. [Resumen del Proyecto](#resumen-del-proyecto)
2. [Stack Tecnológico](#stack-tecnológico)
3. [Estructura del Proyecto](#estructura-del-proyecto)
4. [Configuración de Desarrollo](#configuración-de-desarrollo)
5. [Arquitectura de Base de Datos](#arquitectura-de-base-de-datos)
6. [Lógica de Negocio Clave](#lógica-de-negocio-clave)
7. [Autenticación y Autorización](#autenticación-y-autorización)
8. [Arquitectura del Frontend](#arquitectura-del-frontend)
9. [Guías de Testing](#guías-de-testing)
10. [Consideraciones de Despliegue](#consideraciones-de-despliegue)
11. [Tareas y Flujos Comunes](#tareas-y-flujos-comunes)
12. [Convenciones y Patrones de Código](#convenciones-y-patrones-de-código)
13. [Solución de Problemas](#solución-de-problemas)

---

## Resumen del Proyecto

### Qué Hace Este Sistema

Este es un **sistema completo de gestión de restaurantes** diseñado para restaurantes mexicanos/latinoamericanos con las siguientes capacidades:

- **Punto de Venta (POS)**: Procesar pedidos de clientes con validación de stock en tiempo real
- **Gestión de Inventario**: Seguimiento de materias primas (ingredientes) con deducción automática
- **Gestión de Recetas**: Definir platillos como combinaciones de artículos de inventario (Lista de Materiales)
- **Sistema Dual de Productos**:
  - Artículos del Menú (platillos preparados usando recetas)
  - Productos Simples (artículos vendibles individuales vinculados directamente al inventario)
- **Devoluciones de Ventas**: Procesar reembolsos con restauración de inventario y ajustes de flujo de efectivo
- **Seguimiento de Flujo de Efectivo**: Pista de auditoría financiera completa
- **Integración de Impresora Térmica**: Órdenes de cocina (comandas) y recibos de clientes
- **Acceso Basado en Roles**: Administrador, Chef, Almacenero, Cajero

### Dominio de Negocio

- Idioma principal para términos de negocio: **Español**
- Mercado objetivo: Operaciones de restaurantes en México/Latinoamérica
- Los conceptos clave de negocio usan términos en español (cajero, almacenero, devoluciones, comandas)

---

## Stack Tecnológico

### Backend

- **Framework**: Laravel 12.0
- **Versión de PHP**: 8.2+ (8.4 en Docker)
- **Base de Datos**: SQLite (desarrollo) / MySQL 8.0 (producción)
- **ORM**: Eloquent
- **Autenticación**: Laravel Breeze + Sanctum
- **Colas**: Driver de base de datos
- **Caché/Sesión**: Driver de base de datos

### Frontend

- **Framework SPA**: Inertia.js 2.0 (patrón de monolito moderno)
- **Framework JavaScript**: Vue 3.5 (Composition API disponible)
- **Herramienta de Build**: Vite 6.2
- **Framework CSS**: Tailwind CSS 3.2 + plugin @tailwindcss/forms
- **Enrutamiento**: Rutas de Laravel + Ziggy (helpers de rutas JS)

### Herramientas de Desarrollo

- **Docker**: Laravel Sail 1.41
- **Estilo de Código**: Laravel Pint 1.13
- **Testing**: PHPUnit 11.5
- **REPL**: Laravel Tinker 2.10
- **Visor de Logs**: Laravel Pail 1.2

### Dependencias Especiales

- **mike42/escpos-php**: Comandos ESC/POS para impresoras térmicas
- **barryvdh/laravel-dompdf**: Generación de PDF
- **simplesoftwareio/simple-qrcode**: Generación de códigos QR para ventas
- **tightenco/ziggy**: Rutas de Laravel en JavaScript

---

## Estructura del Proyecto

```
/home/user/restaurant-inventory/
├── app/
│   ├── Http/
│   │   ├── Controllers/           # Manejadores de peticiones
│   │   │   ├── Auth/             # Autenticación Laravel Breeze
│   │   │   ├── CategoryController.php
│   │   │   ├── InventoryController.php
│   │   │   ├── InventoryMovementController.php
│   │   │   ├── POSController.php          # ⚠️ CRÍTICO: Lógica del POS
│   │   │   ├── ProductController.php
│   │   │   ├── ReturnController.php
│   │   │   ├── SaleController.php
│   │   │   └── TicketController.php
│   │   ├── Middleware/
│   │   │   ├── HandleInertiaRequests.php  # Datos compartidos de Inertia
│   │   │   └── RoleMiddleware.php         # ⚠️ Implementación RBAC
│   │   └── Requests/              # Form requests (futuro)
│   ├── Models/                    # Modelos Eloquent (13 modelos)
│   │   ├── CashFlow.php
│   │   ├── Category.php
│   │   ├── InventoryMovement.php
│   │   ├── MenuItem.php           # Platillos preparados
│   │   ├── Product.php            # ⚠️ Artículos de inventario
│   │   ├── Recipe.php             # ⚠️ Lista de Materiales
│   │   ├── Sale.php
│   │   ├── SaleItem.php           # ⚠️ Relación polimórfica
│   │   ├── SaleReturn.php
│   │   ├── SaleReturnItem.php
│   │   ├── SimpleProduct.php      # Artículos vendibles individuales
│   │   ├── Supplier.php
│   │   └── User.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── Services/
│       └── ThermalTicketService.php  # ⚠️ Integración de impresora
├── bootstrap/                     # Bootstrap del framework
├── config/                        # Archivos de configuración
│   ├── thermal_printer.php        # ⚠️ Configuración de impresora
│   ├── dompdf.php
│   └── [configuraciones estándar de Laravel]
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/                # 18 migraciones (secuenciales)
│   └── seeders/                   # 9 seeders con datos de ejemplo
├── public/                        # Raíz web
│   └── build/                     # Assets compilados por Vite
├── resources/
│   ├── css/
│   │   └── app.css               # Punto de entrada de Tailwind
│   ├── js/
│   │   ├── Components/           # Componentes Vue reutilizables (15)
│   │   ├── Layouts/              # Layouts de página (2)
│   │   ├── Pages/                # Componentes de ruta (24)
│   │   │   ├── Auth/
│   │   │   ├── Dashboard.vue
│   │   │   ├── Inventory/
│   │   │   ├── Profile/
│   │   │   ├── Returns/
│   │   │   ├── Sales/
│   │   │   │   └── POS.vue       # ⚠️ Interfaz del Punto de Venta
│   │   │   └── Welcome.vue
│   │   ├── app.js                # ⚠️ Bootstrap de Vue + Inertia
│   │   └── bootstrap.js
│   └── views/                    # Blade mínimo (shell de Inertia)
├── routes/
│   ├── auth.php                  # Rutas de autenticación Breeze
│   ├── console.php
│   └── web.php                   # ⚠️ CRÍTICO: Rutas principales
├── storage/
│   ├── app/
│   │   └── tickets/              # Tickets térmicos de desarrollo (*.txt)
│   ├── framework/
│   └── logs/
├── tests/
│   ├── Feature/
│   └── Unit/
├── vendor/                       # Dependencias de Composer
├── .env.example                  # Plantilla de entorno
├── artisan                       # CLI de Laravel
├── composer.json                 # Dependencias PHP
├── docker-compose.yml            # ⚠️ Configuración de Sail
├── package.json                  # Dependencias NPM
├── phpunit.xml                   # Configuración de testing
├── tailwind.config.js            # Configuración de Tailwind
└── vite.config.js                # Configuración de build de Vite
```

**⚠️ Archivos Clave** que los asistentes IA deben entender completamente antes de hacer cambios.

---

## Configuración de Desarrollo

### Opción 1: Laravel Sail (Docker) - Recomendado

```bash
# Configuración inicial
composer install
./vendor/bin/sail up -d

# Acceder al contenedor
./vendor/bin/sail bash

# Dentro del contenedor
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build

# Acceder a la aplicación
# http://localhost (aplicación)
# http://localhost:5173 (servidor Vite)
```

### Opción 2: Desarrollo Local

```bash
# Prerequisitos: PHP 8.2+, Composer, Node.js 18+

# Instalar dependencias
composer install
npm install

# Configuración del entorno
cp .env.example .env
php artisan key:generate

# Base de datos (SQLite por defecto)
touch database/database.sqlite
php artisan migrate --seed

# Ejecutar servidores de desarrollo concurrentes (recomendado)
composer run dev
# Esto ejecuta: Servidor PHP + Worker de colas + Logs + Vite HMR

# O manualmente en terminales separadas:
php artisan serve              # http://localhost:8000
npm run dev                    # http://localhost:5173
php artisan queue:listen       # Worker de colas
php artisan pail               # Logs en tiempo real
```

### Configuración del Entorno

**Variables críticas de `.env`**:

```env
APP_NAME="Restaurant POS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Base de datos (SQLite para desarrollo, MySQL para producción)
DB_CONNECTION=sqlite
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=restaurant_pos
# DB_USERNAME=root
# DB_PASSWORD=

# Drivers
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database

# Impresoras Térmicas (producción)
KITCHEN_PRINTER_IP=192.168.1.100
KITCHEN_PRINTER_PORT=9100
CUSTOMER_PRINTER_IP=192.168.1.101
CUSTOMER_PRINTER_PORT=9100

# Información del Restaurante (para recibos)
RESTAURANT_NAME="Restaurante Demo"
RESTAURANT_ADDRESS="Calle Principal #123"
RESTAURANT_PHONE="(555) 123-4567"
RESTAURANT_TAX_ID="RFC123456789"

# Configuración de Impresora
AUTO_PRINT_KITCHEN=true
AUTO_PRINT_CUSTOMER=true
```

### Credenciales de Login por Defecto (después del seeding)

- **Admin**: admin@example.com / password
- **Chef**: chef@example.com / password
- **Almacenero**: almacenero@example.com / password
- **Cajero**: cajero@example.com / password

---

## Arquitectura de Base de Datos

### Entidades Principales y Relaciones

```
┌─────────────┐
│   Product   │  Artículos de inventario (ingredientes, suministros)
│  (products) │  - current_stock (decimal 10,3)
└─────┬───────┘  - unit_type (kg/lt/pcs/g/ml)
      │          - niveles min/max de stock
      │          - unit_cost, expiry_date
      ├──────────┐
      │          ▼
      │    ┌──────────┐
      │    │  Recipe  │  Lista de Materiales
      │    │(recipes) │  Vincula MenuItem → Products
      │    └────┬─────┘  - quantity_needed, unit
      │         │
      │         ▼
      │   ┌──────────────┐
      │   │   MenuItem   │  Platillos preparados (combos)
      │   │(menu_items)  │  - price, image_path
      │   └──────────────┘  - is_available
      │
      ├──────────┐
      │          ▼
      │   ┌─────────────────┐
      │   │ SimpleProduct   │  Artículos vendibles individuales
      │   │(simple_products)│  - sale_price, cost_per_unit
      │   └─────────────────┘  - Vinculado al Product base
      │
      ▼
┌──────────────────────┐
│ InventoryMovement    │  Pista de auditoría de cambios de stock
│(inventory_movements) │  - movement_type (entrada/salida/ajuste)
└──────────────────────┘  - reason (compra, venta_automatica, etc.)

┌──────────┐
│   Sale   │  Transacciones de ventas
│ (sales)  │  - sale_number (YYYYMMDD0001)
└────┬─────┘  - payment_method, status
     │
     ├────────────────┐
     │                ▼
     │         ┌──────────────┐
     │         │   SaleItem   │  POLIMÓRFICO: menu_item O simple_product
     │         │(sale_items)  │  - product_type ('menu'/'simple')
     │         └──────────────┘  - quantity, unit_price
     │
     ├────────────────┐
     │                ▼
     │         ┌──────────────┐
     │         │  CashFlow    │  Pista de auditoría financiera
     │         │(cash_flow)   │  - type (entrada/salida)
     │         └──────────────┘  - category (ventas, devoluciones, etc.)
     │
     └────────────────┐
                      ▼
               ┌──────────────┐
               │ SaleReturn   │  Reembolsos/devoluciones
               │(sale_returns)│  - return_number (RETYYYYMMDD0001)
               └──────────────┘  - inventory_restored (bool)
                                 - cash_flow_adjusted (bool)
```

### Conceptos Críticos de Base de Datos

#### 1. Sistema Dual de Productos

**Products (Inventario)**:
- Materias primas e ingredientes
- Rastreado con precisión decimal (0.001 unidades)
- Ejemplo: "Pollo" (1kg), "Coca Cola" (1 litro), "Tortillas" (1kg)

**Menu Items (Platillos Preparados)**:
- Compuestos de múltiples Products vía Recipes
- Ejemplo: "Tacos de Pollo" requiere 0.2kg Pollo, 0.1kg Tortillas, etc.
- Disponibilidad de stock calculada desde el ingrediente mínimo disponible

**Simple Products (Ventas Individuales)**:
- Ventas directas vinculadas al inventario
- Ejemplo: "Coca Cola 355ml" consume 0.355 litros del Product base
- Cantidad disponible calculada: `floor(current_stock / cost_per_unit)`

#### 2. Deducción Automática de Inventario

**Ubicación**: `app/Http/Controllers/POSController.php:processMenuItemInventoryDeduction()`

Cuando se completa una venta:
1. Para cada Menu Item vendido:
   - Consulta Recipes relacionadas
   - Deduce `quantity_needed * items_sold` de cada Product
   - Crea InventoryMovement con reason `venta_automatica`

2. Para cada Simple Product vendido:
   - Deduce `cost_per_unit * items_sold` del Product base
   - Crea InventoryMovement

**⚠️ CRÍTICO**: Esta lógica asegura que el inventario permanezca sincronizado con las ventas. NO omitir esto al procesar ventas.

#### 3. Cálculo de Disponibilidad de Stock

**Menu Items** (`app/Models/MenuItem.php`):
```php
// Calcula las porciones mínimas posibles basado en todos los ingredientes de la receta
$available_quantity = min(
    floor($product1_stock / $recipe1_quantity),
    floor($product2_stock / $recipe2_quantity),
    // ... para todos los items de la receta
);
```

**Simple Products** (`app/Models/SimpleProduct.php`):
```php
$available_quantity = floor($product->current_stock / $this->cost_per_unit);
```

**⚠️ IMPORTANTE**: El POS valida el stock antes de permitir ventas para prevenir sobreventa.

#### 4. Orden de Ejecución de Migraciones

Las migraciones DEBEN ejecutarse en este orden (manejado por timestamps):

1. Tablas core de Laravel (users, cache, jobs, sessions)
2. categories, suppliers
3. products (depende de categories, suppliers)
4. menu_items
5. recipes (depende de menu_items, products)
6. simple_products (depende de products)
7. inventory_movements (depende de products, users)
8. sales (depende de users)
9. sale_items (depende de sales, menu_items, simple_products)
10. cash_flow (depende de sales, users)
11. sale_returns (depende de sales, users)
12. sale_return_items (depende de sale_returns, sale_items)
13. Adición de rol de usuario
14. Actualizaciones de categoría de flujo de efectivo

**Ejecutar Migraciones**:
```bash
php artisan migrate --seed    # Instalación nueva con datos de ejemplo
php artisan migrate:fresh      # Resetear base de datos (DESTRUCTIVO)
php artisan migrate:rollback   # Revertir último lote
```

---

## Lógica de Negocio Clave

### 1. Flujo del Punto de Venta (POS)

**Controlador**: `app/Http/Controllers/POSController.php`
**Vista**: `resources/js/Pages/Sales/POS.vue`

**Proceso**:
1. Usuario selecciona productos (menu items o simple products)
2. Sistema valida disponibilidad de stock en tiempo real
3. Usuario completa la venta (método de pago, descuento, etc.)
4. Backend:
   - Crea registro Sale con número único `YYYYMMDD0001`
   - Crea SaleItems (referencias polimórficas)
   - **Deduce inventario automáticamente** (crítico)
   - Crea entrada CashFlow (categoría: 'ventas')
   - Activa impresora térmica (recibos de cocina + cliente)
5. Retorna datos de venta al frontend

**Formato de Número de Venta**: `20251115` + `0001` (contador diario con padding)

### 2. Integración de Impresora Térmica

**Servicio**: `app/Services/ThermalTicketService.php`
**Configuración**: `config/thermal_printer.php`

**Tres Tipos de Tickets**:

1. **Orden de Cocina (Comanda)**:
   - Impresora térmica de 58mm
   - Solo items que requieren preparación en cocina (excluye 'Bebidas', 'Postres', 'Extras')
   - Cálculo de número de prioridad
   - Impreso automáticamente en venta

2. **Recibo de Cliente**:
   - Impresora térmica de 80mm
   - Detalles completos de la venta
   - Código QR con número de venta
   - Desglose de impuestos
   - Método de pago

3. **Recibo de Devolución**:
   - Similar al recibo de cliente
   - Muestra items devueltos
   - Monto de reembolso

**Modo de Desarrollo**:
```php
if (app()->environment('local')) {
    // Guarda en storage/app/tickets/
    file_put_contents(storage_path("app/tickets/sale_{$saleNumber}.txt"), $ticket);
} else {
    // Envía a impresora de red
    $connector = new NetworkPrintConnector($printerIp, $printerPort);
    $printer = new Printer($connector);
    $printer->text($ticket);
    $printer->close();
}
```

**⚠️ IMPORTANTE**: En producción, asegurar que las IPs de impresora estén configuradas en `.env` y las impresoras estén en la misma red.

### 3. Flujo de Devoluciones de Ventas

**Controladores**: `app/Http/Controllers/ReturnController.php`
**Vistas**: `resources/js/Pages/Returns/`

**Proceso**:
1. Buscar venta original por número de venta
2. Seleccionar items a devolver (parcial o total)
3. Especificar razón de devolución
4. Backend:
   - Crea SaleReturn con número único `RETYYYYMMDD0001`
   - Crea SaleReturnItems
   - **Restaura inventario** (agrega de vuelta a Products)
   - **Ajusta flujo de efectivo** (entrada negativa, categoría: 'devoluciones')
   - Marca banderas: `inventory_restored=true`, `cash_flow_adjusted=true`
5. Imprime recibo de devolución

**Formato de Número de Devolución**: `RET` + `20251115` + `0001`

**⚠️ CRÍTICO**: Las devoluciones deben restaurar inventario para mantener precisión. El sistema rastrea esto con banderas booleanas.

### 4. Seguimiento de Flujo de Efectivo

**Modelo**: `app/Models/CashFlow.php`

**Categorías**:
- `ventas` - Ingresos de ventas (entrada)
- `compras` - Compras de inventario (salida)
- `gastos_operativos` - Gastos operacionales (salida)
- `gastos_admin` - Gastos administrativos (salida)
- `devoluciones` - Reembolsos/devoluciones (salida)
- `otros` - Otras transacciones

**Creado Automáticamente En**:
- Finalización de ventas (ventas)
- Procesamiento de devoluciones (devoluciones)

**Entrada Manual**:
- Compras de inventario
- Gastos operacionales
- Otras transacciones

**Reportes**: Disponibles en dashboard de admin (resumen de flujo de efectivo por rango de fechas)

---

## Autenticación y Autorización

### Stack de Autenticación

- **Laravel Breeze**: Provee login, registro, reset de contraseña, verificación de email
- **Laravel Sanctum**: Autenticación de tokens API (modo SPA)
- **Middleware Inertia**: Comparte datos de usuario autenticado globalmente

### Control de Acceso Basado en Roles (RBAC)

**Middleware**: `app/Http/Middleware/RoleMiddleware.php`

**Roles** (Enum en modelo User):
- `admin` - Acceso completo al sistema
- `chef` - Gestión de menú, ver órdenes
- `almacenero` - Gestión de inventario
- `cajero` - Operaciones de POS, ventas

**Protección de Rutas**:
```php
Route::middleware(['auth', 'role:admin,cajero'])->group(function () {
    // Solo admin y cajero pueden acceder
});
```

**Asignación de Rol** (migración: `2025_06_10_004200_add_role_to_users_table.php`):
```php
$table->enum('role', ['admin', 'chef', 'almacenero', 'cajero'])->default('cajero');
$table->boolean('is_active')->default(true);
```

### Matriz de Acceso a Rutas

| Funcionalidad | Admin | Chef | Almacenero | Cajero |
|---------------|-------|------|------------|--------|
| Dashboard | ✓ | ✓ | ✓ | ✓ |
| POS | ✓ | ✗ | ✗ | ✓ |
| Historial de Ventas | ✓ | ✗ | ✗ | ✓ |
| Devoluciones | ✓ | ✗ | ✗ | ✓ |
| Inventario | ✓ | ✗ | ✓ | ✗ |
| Productos | ✓ | ✗ | ✓ | ✗ |
| Items del Menú | ✓ | ✓ | ✗ | ✗ |
| Recetas | ✓ | ✓ | ✗ | ✗ |
| Flujo de Efectivo | ✓ | ✗ | ✗ | ✗ |
| Gestión de Usuarios | ✓ | ✗ | ✗ | ✗ |

**⚠️ IMPORTANTE**: Al crear nuevas rutas, siempre considerar permisos de rol.

---

## Arquitectura del Frontend

### Patrón Inertia.js

**Concepto**: "Monolito Moderno"
- Enrutamiento del lado del servidor (Laravel)
- Renderizado del lado del cliente (Vue 3)
- No se necesita capa API
- Props pasadas directamente desde controladores

**Ejemplo de Flujo**:
```php
// Controlador
return Inertia::render('Sales/POS', [
    'menuItems' => MenuItem::with('recipes.product')->get(),
    'simpleProducts' => SimpleProduct::with('product')->get(),
]);
```

```vue
<!-- resources/js/Pages/Sales/POS.vue -->
<script setup>
const props = defineProps({
    menuItems: Array,
    simpleProducts: Array,
});
</script>
```

### Organización de Componentes

**Pages** (`resources/js/Pages/`):
- Componentes específicos de ruta
- Reciben props de controladores
- Uno por ruta (Dashboard.vue, POS.vue, etc.)

**Components** (`resources/js/Components/`):
- Elementos de UI reutilizables
- Inputs de formulario, modales, dropdowns, etc.
- Sin lógica específica de ruta

**Layouts** (`resources/js/Layouts/`):
- Envolturas de página
- `AuthenticatedLayout.vue` - Para usuarios logueados (sidebar, header)
- `GuestLayout.vue` - Para páginas de autenticación

### Helpers de Rutas Ziggy

**Uso en Vue**:
```vue
<script setup>
import { router } from '@inertiajs/vue3';

// Navegar a ruta
router.visit(route('sales.show', saleId));

// Generar URL
const url = route('api.products.search', { query: 'coca' });
</script>

<template>
    <Link :href="route('sales.index')" class="btn">
        Ver Ventas
    </Link>
</template>
```

**Disponible globalmente** vía plugin Ziggy en `app.js`.

### Convenciones de Tailwind

**Enfoque Utility-First**:
```vue
<template>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h1 class="text-2xl font-semibold text-gray-900 mb-4">
                Punto de Venta
            </h1>
        </div>
    </div>
</template>
```

**Sin CSS Personalizado**: Evitar crear archivos `.css`. Usar utilidades de Tailwind.

**Estilo de Formularios**: El plugin `@tailwindcss/forms` provee estilos base.

---

## Guías de Testing

### Cobertura Actual de Tests

**Tests Existentes**:
- `tests/Feature/ExampleTest.php` - Test básico de aplicación
- `tests/Feature/ProfileTest.php` - Gestión de perfil de usuario
- Tests de autenticación Laravel Breeze (login, registro, reset de contraseña)

**Brechas de Cobertura** (Oportunidades):
- ❌ Procesamiento de transacciones POS
- ❌ Lógica de deducción de inventario
- ❌ Cálculos de disponibilidad de stock
- ❌ Procesamiento de devoluciones de ventas
- ❌ Aplicación de middleware de roles
- ❌ Servicio de impresora térmica

### Escribiendo Tests

**Plantilla de Feature Test**:
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/inventory/products', [
            'name' => 'Test Product',
            'category_id' => 1,
            'unit_type' => 'kg',
            'current_stock' => 10,
            'unit_cost' => 5.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }
}
```

**Ejecutar Tests**:
```bash
php artisan test                    # Ejecutar todos los tests
php artisan test --filter=Inventory # Ejecutar test específico
php artisan test --coverage         # Con reporte de cobertura
```

**Base de Datos de Test**:
- Usa base de datos `testing` separada (desde `phpunit.xml`)
- Recomendado SQLite en memoria: `:memory:`
- Migraciones se ejecutan automáticamente vía trait `RefreshDatabase`

---

## Consideraciones de Despliegue

### Checklist Pre-Despliegue

**Entorno**:
- [ ] Establecer `APP_ENV=production`
- [ ] Establecer `APP_DEBUG=false`
- [ ] Generar clave de producción: `php artisan key:generate`
- [ ] Configurar base de datos (MySQL recomendado)
- [ ] Establecer `APP_URL` correcto

**Base de Datos**:
- [ ] Ejecutar migraciones: `php artisan migrate --force`
- [ ] Sembrar datos iniciales si es necesario
- [ ] Estrategia de respaldo en su lugar

**Assets**:
- [ ] Construir frontend: `npm run build`
- [ ] Verificar assets en `public/build/`

**Optimización**:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

**Permisos**:
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Worker de Colas** (requerido para operaciones asíncronas):
```bash
# Servicio Systemd o Supervisor
php artisan queue:work --tries=3 --timeout=90
```

**Impresoras Térmicas**:
- [ ] Configurar IPs de impresora en `.env`
- [ ] Probar conectividad de red a impresoras
- [ ] Verificar compatibilidad de comandos ESC/POS
- [ ] Establecer banderas `AUTO_PRINT_KITCHEN` y `AUTO_PRINT_CUSTOMER`

**Servidor Web**:
- Raíz de documentos: `/public`
- Configuración Nginx/Apache para Laravel
- HTTPS recomendado (Let's Encrypt)

### Ejemplo de .env de Producción

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://turestaurante.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restaurant_pos
DB_USERNAME=restaurant_user
DB_PASSWORD=contraseña_segura_aqui

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1

KITCHEN_PRINTER_IP=192.168.1.100
CUSTOMER_PRINTER_IP=192.168.1.101
AUTO_PRINT_KITCHEN=true
AUTO_PRINT_CUSTOMER=true
```

---

## Tareas y Flujos Comunes

### Crear un Nuevo Producto

```bash
# 1. Vía Seeder (desarrollo)
php artisan db:seed --class=ProductSeeder

# 2. Vía UI (producción)
# Login como admin o almacenero
# Navegar a /inventory/products
# Click "Agregar Producto"
```

### Procesar una Venta

```bash
# Solo vía UI (sin comando artisan)
# Login como admin o cajero
# Navegar a /sales/pos
# Seleccionar items, procesar pago
```

### Agregar un Nuevo Item del Menú con Receta

```bash
# 1. Crear menu item (admin o chef)
# Navegar a /menu/items/create

# 2. Definir receta
# En formulario de menu item, agregar productos con cantidades
# Ejemplo: "Tacos de Pollo"
#   - Pollo: 0.2 kg
#   - Tortillas: 0.1 kg
#   - Salsa: 0.05 lt
```

### Ver Reportes

```bash
# Reporte de Flujo de Efectivo
# Navegar a /cashflow (solo admin)
# Filtrar por rango de fechas

# Reporte de Inventario
# Navegar a /inventory (admin o almacenero)
# Ver items con stock bajo, movimientos
```

### Reset de Base de Datos (Desarrollo)

```bash
# ⚠️ DESTRUCTIVO - Elimina todos los datos
php artisan migrate:fresh --seed

# Crea base de datos nueva con datos de ejemplo
```

### Arreglar Estilo de Código

```bash
# Ejecutar Laravel Pint
./vendor/bin/pint

# Arreglar archivo específico
./vendor/bin/pint app/Http/Controllers/POSController.php
```

### Debugging

```bash
# Logs en tiempo real
php artisan pail

# Tinker REPL
php artisan tinker
>>> $sales = \App\Models\Sale::with('saleItems')->latest()->take(5)->get();
>>> $product = \App\Models\Product::find(1);
>>> $product->current_stock;
```

---

## Convenciones y Patrones de Código

### Convenciones de Nomenclatura

**PHP (Estándares Laravel)**:
- Modelos: `PascalCase`, singular (Product, MenuItem)
- Controladores: `PascalCase` + sufijo `Controller` (ProductController)
- Métodos: `camelCase` (processMenuItemInventoryDeduction)
- Tablas de base de datos: `snake_case`, plural (menu_items, sale_returns)
- Migraciones: Timestamp + descripción (2025_06_10_001727_create_categories_table.php)

**JavaScript/Vue**:
- Componentes: `PascalCase` (ApplicationLogo.vue, TextInput.vue)
- Props/variables: `camelCase` (menuItems, currentStock)
- Eventos: `kebab-case` (@click, @update:model-value)

**Términos de Negocio (Español)**:
- Usar español para términos específicos del dominio en código
- Ejemplos: `cajero`, `almacenero`, `devoluciones`, `comandas`
- Los comentarios pueden ser en español o inglés

### Patrones de Diseño

**Capa de Servicio**:
```php
// app/Services/ThermalTicketService.php
class ThermalTicketService
{
    public function printKitchenOrder(Sale $sale): void
    {
        // Lógica centralizada de impresora
    }
}

// Uso en controlador
app(ThermalTicketService::class)->printKitchenOrder($sale);
```

**Patrón Repository** (No implementado, pero recomendado):
```php
// Mejora futura
interface ProductRepositoryInterface
{
    public function getLowStockProducts(): Collection;
    public function getExpiringSoonProducts(): Collection;
}
```

**Relaciones Polimórficas**:
```php
// SaleItem puede referenciar MenuItem O SimpleProduct
class SaleItem extends Model
{
    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function simpleProduct()
    {
        return $this->belongsTo(SimpleProduct::class);
    }

    // Accessor para interfaz unificada
    public function getProductNameAttribute()
    {
        return $this->product_type === 'menu'
            ? $this->menuItem->name
            : $this->simpleProduct->name;
    }
}
```

### Convenciones de Base de Datos

**Precisión Decimal**:
- Cantidades de stock: `decimal(10, 3)` - Soporta gramos/mililitros
- Precios/costos: `decimal(10, 2)` - Moneda estándar
- Uso: Siempre usar decimales para cantidades (nunca enteros)

**Timestamps**:
- Usar `timestamps()` en migraciones (created_at, updated_at)
- Fechas de negocio separadas: `movement_date`, `flow_date`, `return_date`

**Claves Foráneas**:
- Siempre definir: `$table->foreignId('product_id')->constrained()`
- Eliminaciones en cascada donde sea apropiado: `->onDelete('cascade')`
- Establecer null para datos históricos: `->onDelete('set null')`

**Restricciones Únicas**:
```php
$table->unique('email');
$table->unique('sale_number');
$table->unique(['menu_item_id', 'product_id']); // Compuesta
```

### Manejo de Errores

**Validación en Controlador**:
```php
$request->validate([
    'name' => 'required|string|max:255',
    'current_stock' => 'required|numeric|min:0',
    'unit_type' => 'required|in:kg,lt,pcs,g,ml',
]);
```

**Try-Catch para Transacciones**:
```php
DB::beginTransaction();
try {
    // Crear venta
    // Deducir inventario
    // Crear flujo de efectivo
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    return back()->withErrors(['error' => $e->getMessage()]);
}
```

**Mensajes Flash** (Inertia):
```php
return redirect()->route('sales.index')->with('success', 'Venta completada exitosamente');
```

### Mejores Prácticas de Seguridad

**Protección de Asignación Masiva**:
```php
class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'description', 'unit_type',
        'current_stock', 'min_stock', 'max_stock', 'unit_cost',
    ];

    // NUNCA usar $guarded = [];
}
```

**Prevención de Inyección SQL**:
- Usar Eloquent ORM (auto-escapa)
- Usar query builder con bindings
- NUNCA concatenar input de usuario en SQL crudo

**Prevención de XSS**:
- Blade/Vue auto-escapa: `{{ $variable }}`
- HTML crudo (cuidado): `{!! $html !!}` o `v-html`
- Sanitizar input de usuario antes de almacenar

**Protección CSRF**:
- Habilitada por defecto en Laravel
- Inertia maneja tokens automáticamente
- Formularios incluyen directiva `@csrf`

---

## Solución de Problemas

### Problemas Comunes

**1. "Class 'X' not found"**
```bash
composer dump-autoload
```

**2. Vite connection refused (HMR)**
```bash
# Verificar que Vite esté ejecutándose
npm run dev

# En .env, verificar:
APP_URL=http://localhost
```

**3. Desajuste de versión de Inertia**
```bash
# Actualizar servidor y cliente
composer update inertiajs/inertia-laravel
npm update @inertiajs/vue3
```

**4. Base de datos bloqueada (SQLite)**
```bash
# Detener workers de cola
# Verificar otros procesos PHP accediendo a BD
ps aux | grep artisan
```

**5. Impresora térmica no responde**
```bash
# Probar conectividad de red
ping 192.168.1.100

# Verificar puerto
nc -zv 192.168.1.100 9100

# Verificar en logs
php artisan pail --filter=printer
```

**6. Stock no se deduce**
```bash
# Verificar movimientos de inventario
php artisan tinker
>>> \App\Models\InventoryMovement::where('reason', 'venta_automatica')->latest()->take(10)->get();

# Verificar que la cola esté ejecutándose
php artisan queue:work
```

**7. Permiso denegado (storage/logs)**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Herramientas de Debugging

**Laravel Debugbar** (opcional, instalar si es necesario):
```bash
composer require barryvdh/laravel-debugbar --dev
```

**Ray** (opcional, herramienta premium de debugging):
```bash
composer require spatie/laravel-ray
```

**Consultas de Base de Datos**:
```php
DB::enableQueryLog();
// ... ejecutar código
dd(DB::getQueryLog());
```

**Debug de Inertia**:
```vue
<script setup>
const props = defineProps({
    menuItems: Array,
});

console.log('Props:', props);
</script>
```

---

## Recursos Adicionales

### Documentación de Laravel
- Framework: https://laravel.com/docs
- Eloquent ORM: https://laravel.com/docs/eloquent
- Migraciones: https://laravel.com/docs/migrations
- Validación: https://laravel.com/docs/validation

### Documentación de Inertia.js
- Docs Principales: https://inertiajs.com
- Integración Vue 3: https://inertiajs.com/client-side-setup

### Documentación de Vue 3
- Docs Oficiales: https://vuejs.org
- Composition API: https://vuejs.org/api/composition-api-setup.html

### Documentación de Tailwind CSS
- Framework: https://tailwindcss.com/docs
- Plugin de Forms: https://github.com/tailwindlabs/tailwindcss-forms

### Impresión Térmica
- ESC/POS PHP: https://github.com/mike42/escpos-php
- Referencia de Comandos: https://reference.epson-biz.com/modules/ref_escpos/

---

## Notas de Mantenimiento del Repositorio

### Deuda Técnica Conocida

1. **Cobertura de Testing**: Tests de features mínimos (solo auth/profile)
2. **Capa API**: Ninguna (todo basado en Inertia)
3. **Seguridad de Tipos**: Sin TypeScript (JavaScript puro)
4. **Patrón Repository**: No implementado (controladores usan modelos directamente)
5. **Form Requests**: No usado extensivamente (validación en controladores)

### Archivos Basura para Limpiar

Los siguientes archivos en el directorio raíz deben ser eliminados:
- `CashFlow::where('category', 'devoluciones')->latest()->first();`
- `CashFlow::where('category', 'gastos_operativos')`
- `InventoryMovement::where('reason', 'devolucion_producto_simple')->get();`
- `available_quantity`
- `product`
- `t()->first();exi`

Estos parecen ser artefactos de debugging del desarrollo.

### Mejoras Futuras (Sugeridas)

1. **Analíticas de Dashboard**: Gráficas de tendencias de ventas, items populares
2. **Soporte Multi-Tienda**: Gestión de restaurantes en franquicia/cadena
3. **Seguimiento de Tiempo de Empleados**: Check-in/out para personal
4. **Gestión de Mesas**: Plano del restaurante, asignación de mesas
5. **Órdenes Online**: Integración con plataformas de delivery
6. **Programa de Lealtad**: Sistema de puntos/recompensas para clientes
7. **Gestión de Proveedores**: Órdenes de compra, seguimiento de entregas
8. **Planeación de Producción**: Pronóstico de ingredientes basado en ventas
9. **App Móvil**: iOS/Android nativo para POS
10. **Notificaciones en Tiempo Real**: WebSockets para sistema de display de cocina

---

## Comandos de Referencia Rápida

```bash
# Desarrollo
composer run dev              # Iniciar todos los servidores de desarrollo
php artisan serve            # Servidor HTTP
npm run dev                  # Vite HMR
php artisan queue:work       # Worker de colas
php artisan pail             # Visor de logs

# Base de Datos
php artisan migrate          # Ejecutar migraciones
php artisan migrate:fresh    # Resetear base de datos
php artisan db:seed          # Sembrar datos
php artisan migrate:fresh --seed  # Resetear + sembrar

# Testing
php artisan test             # Ejecutar tests
php artisan test --filter=X  # Ejecutar test específico
composer test                # Vía script de composer

# Optimización
php artisan optimize         # Optimizar todo
php artisan config:cache     # Cachear configuración
php artisan route:cache      # Cachear rutas
php artisan view:cache       # Cachear vistas
php artisan optimize:clear   # Limpiar todos los cachés

# Calidad de Código
./vendor/bin/pint           # Arreglar estilo de código

# Docker (Sail)
./vendor/bin/sail up -d     # Iniciar contenedores
./vendor/bin/sail down      # Detener contenedores
./vendor/bin/sail artisan   # Ejecutar artisan en contenedor
./vendor/bin/sail composer  # Ejecutar composer en contenedor
```

---

**Fin de la Documentación**

Para preguntas o aclaraciones sobre este código base, por favor referirse a las secciones específicas arriba o consultar los comentarios en línea en los archivos críticos marcados con ⚠️.
