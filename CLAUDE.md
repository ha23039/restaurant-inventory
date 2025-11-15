# CLAUDE.md - AI Assistant Guide for Restaurant Inventory & POS System

**Last Updated**: 2025-11-15
**Project**: Restaurant Management System with POS, Inventory, and Thermal Printing
**Stack**: Laravel 12 + Inertia.js + Vue 3 + Tailwind CSS

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Technology Stack](#technology-stack)
3. [Project Structure](#project-structure)
4. [Development Setup](#development-setup)
5. [Database Architecture](#database-architecture)
6. [Key Business Logic](#key-business-logic)
7. [Authentication & Authorization](#authentication--authorization)
8. [Frontend Architecture](#frontend-architecture)
9. [Testing Guidelines](#testing-guidelines)
10. [Deployment Considerations](#deployment-considerations)
11. [Common Tasks & Workflows](#common-tasks--workflows)
12. [Code Conventions & Patterns](#code-conventions--patterns)
13. [Troubleshooting](#troubleshooting)

---

## Project Overview

### What This System Does

This is a **full-stack restaurant management system** designed for Mexican/Latin American restaurants with the following capabilities:

- **Point of Sale (POS)**: Process customer orders with real-time stock validation
- **Inventory Management**: Track raw materials (ingredients) with automatic deduction
- **Recipe Management**: Define dishes as combinations of inventory items (Bill of Materials)
- **Dual Product System**:
  - Menu Items (prepared dishes using recipes)
  - Simple Products (individual sellable items linked directly to inventory)
- **Sales Returns**: Process refunds with inventory restoration and cash flow adjustments
- **Cash Flow Tracking**: Complete financial audit trail
- **Thermal Printer Integration**: Kitchen orders (comandas) and customer receipts
- **Role-Based Access**: Admin, Chef, Warehouse Manager (Almacenero), Cashier (Cajero)

### Business Domain

- Primary language for business terms: **Spanish**
- Target market: Restaurant operations in Mexico/Latin America
- Key business concepts use Spanish terms (cajero, almacenero, devoluciones, comandas)

---

## Technology Stack

### Backend

- **Framework**: Laravel 12.0
- **PHP Version**: 8.2+ (8.4 in Docker)
- **Database**: SQLite (dev) / MySQL 8.0 (production)
- **ORM**: Eloquent
- **Authentication**: Laravel Breeze + Sanctum
- **Queue**: Database driver
- **Cache/Session**: Database driver

### Frontend

- **SPA Framework**: Inertia.js 2.0 (modern monolith pattern)
- **JavaScript Framework**: Vue 3.5 (Composition API available)
- **Build Tool**: Vite 6.2
- **CSS Framework**: Tailwind CSS 3.2 + @tailwindcss/forms plugin
- **Routing**: Laravel routes + Ziggy (JS route helpers)

### Development Tools

- **Docker**: Laravel Sail 1.41
- **Code Style**: Laravel Pint 1.13
- **Testing**: PHPUnit 11.5
- **REPL**: Laravel Tinker 2.10
- **Log Viewer**: Laravel Pail 1.2

### Special Dependencies

- **mike42/escpos-php**: ESC/POS thermal printer commands
- **barryvdh/laravel-dompdf**: PDF generation
- **simplesoftwareio/simple-qrcode**: QR code generation for sales
- **tightenco/ziggy**: Laravel routes in JavaScript

---

## Project Structure

```
/home/user/restaurant-inventory/
├── app/
│   ├── Http/
│   │   ├── Controllers/           # Request handlers
│   │   │   ├── Auth/             # Laravel Breeze auth
│   │   │   ├── CategoryController.php
│   │   │   ├── InventoryController.php
│   │   │   ├── InventoryMovementController.php
│   │   │   ├── POSController.php          # ⚠️ CRITICAL: POS logic
│   │   │   ├── ProductController.php
│   │   │   ├── ReturnController.php
│   │   │   ├── SaleController.php
│   │   │   └── TicketController.php
│   │   ├── Middleware/
│   │   │   ├── HandleInertiaRequests.php  # Shared Inertia data
│   │   │   └── RoleMiddleware.php         # ⚠️ RBAC implementation
│   │   └── Requests/              # Form requests (future)
│   ├── Models/                    # Eloquent models (13 models)
│   │   ├── CashFlow.php
│   │   ├── Category.php
│   │   ├── InventoryMovement.php
│   │   ├── MenuItem.php           # Prepared dishes
│   │   ├── Product.php            # ⚠️ Raw inventory items
│   │   ├── Recipe.php             # ⚠️ BOM for menu items
│   │   ├── Sale.php
│   │   ├── SaleItem.php           # ⚠️ Polymorphic relation
│   │   ├── SaleReturn.php
│   │   ├── SaleReturnItem.php
│   │   ├── SimpleProduct.php      # Individual sellable items
│   │   ├── Supplier.php
│   │   └── User.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── Services/
│       └── ThermalTicketService.php  # ⚠️ Printer integration
├── bootstrap/                     # Framework bootstrap
├── config/                        # Configuration files
│   ├── thermal_printer.php        # ⚠️ Printer configuration
│   ├── dompdf.php
│   └── [standard Laravel configs]
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/                # 18 migrations (sequential)
│   └── seeders/                   # 9 seeders with sample data
├── public/                        # Web root
│   └── build/                     # Vite compiled assets
├── resources/
│   ├── css/
│   │   └── app.css               # Tailwind entry point
│   ├── js/
│   │   ├── Components/           # Reusable Vue components (15)
│   │   ├── Layouts/              # Page layouts (2)
│   │   ├── Pages/                # Route components (24)
│   │   │   ├── Auth/
│   │   │   ├── Dashboard.vue
│   │   │   ├── Inventory/
│   │   │   ├── Profile/
│   │   │   ├── Returns/
│   │   │   ├── Sales/
│   │   │   │   └── POS.vue       # ⚠️ Point of Sale UI
│   │   │   └── Welcome.vue
│   │   ├── app.js                # ⚠️ Vue + Inertia bootstrap
│   │   └── bootstrap.js
│   └── views/                    # Minimal Blade (Inertia shell)
├── routes/
│   ├── auth.php                  # Breeze auth routes
│   ├── console.php
│   └── web.php                   # ⚠️ CRITICAL: Main routes
├── storage/
│   ├── app/
│   │   └── tickets/              # Dev thermal tickets (*.txt)
│   ├── framework/
│   └── logs/
├── tests/
│   ├── Feature/
│   └── Unit/
├── vendor/                       # Composer dependencies
├── .env.example                  # Environment template
├── artisan                       # Laravel CLI
├── composer.json                 # PHP dependencies
├── docker-compose.yml            # ⚠️ Sail configuration
├── package.json                  # NPM dependencies
├── phpunit.xml                   # Testing configuration
├── tailwind.config.js            # Tailwind configuration
└── vite.config.js                # Vite build configuration
```

**⚠️ Key Files** that AI assistants should understand thoroughly before making changes.

---

## Development Setup

### Option 1: Laravel Sail (Docker) - Recommended

```bash
# First time setup
composer install
./vendor/bin/sail up -d

# Access container
./vendor/bin/sail bash

# Inside container
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build

# Access application
# http://localhost (app)
# http://localhost:5173 (Vite dev server)
```

### Option 2: Local Development

```bash
# Prerequisites: PHP 8.2+, Composer, Node.js 18+

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database (SQLite default)
touch database/database.sqlite
php artisan migrate --seed

# Run concurrent dev servers (recommended)
composer run dev
# This runs: PHP server + Queue worker + Logs + Vite HMR

# OR manually in separate terminals:
php artisan serve              # http://localhost:8000
npm run dev                    # http://localhost:5173
php artisan queue:listen       # Queue worker
php artisan pail               # Real-time logs
```

### Environment Configuration

**Critical `.env` Variables**:

```env
APP_NAME="Restaurant POS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database (SQLite for dev, MySQL for production)
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

# Thermal Printers (production)
KITCHEN_PRINTER_IP=192.168.1.100
KITCHEN_PRINTER_PORT=9100
CUSTOMER_PRINTER_IP=192.168.1.101
CUSTOMER_PRINTER_PORT=9100

# Restaurant Info (for receipts)
RESTAURANT_NAME="Restaurante Demo"
RESTAURANT_ADDRESS="Calle Principal #123"
RESTAURANT_PHONE="(555) 123-4567"
RESTAURANT_TAX_ID="RFC123456789"

# Printer Settings
AUTO_PRINT_KITCHEN=true
AUTO_PRINT_CUSTOMER=true
```

### Default Login Credentials (after seeding)

- **Admin**: admin@example.com / password
- **Chef**: chef@example.com / password
- **Almacenero**: almacenero@example.com / password
- **Cajero**: cajero@example.com / password

---

## Database Architecture

### Core Entities & Relationships

```
┌─────────────┐
│   Product   │  Raw inventory items (ingredients, supplies)
│  (products) │  - current_stock (decimal 10,3)
└─────┬───────┘  - unit_type (kg/lt/pcs/g/ml)
      │          - min/max stock levels
      │          - unit_cost, expiry_date
      ├──────────┐
      │          ▼
      │    ┌──────────┐
      │    │  Recipe  │  Bill of Materials
      │    │(recipes) │  Links MenuItem → Products
      │    └────┬─────┘  - quantity_needed, unit
      │         │
      │         ▼
      │   ┌──────────────┐
      │   │   MenuItem   │  Prepared dishes (combos)
      │   │(menu_items)  │  - price, image_path
      │   └──────────────┘  - is_available
      │
      ├──────────┐
      │          ▼
      │   ┌─────────────────┐
      │   │ SimpleProduct   │  Individual sellable items
      │   │(simple_products)│  - sale_price, cost_per_unit
      │   └─────────────────┘  - Linked to base Product
      │
      ▼
┌──────────────────────┐
│ InventoryMovement    │  Stock change audit trail
│(inventory_movements) │  - movement_type (entrada/salida/ajuste)
└──────────────────────┘  - reason (compra, venta_automatica, etc.)

┌──────────┐
│   Sale   │  Sales transactions
│ (sales)  │  - sale_number (YYYYMMDD0001)
└────┬─────┘  - payment_method, status
     │
     ├────────────────┐
     │                ▼
     │         ┌──────────────┐
     │         │   SaleItem   │  POLYMORPHIC: menu_item OR simple_product
     │         │(sale_items)  │  - product_type ('menu'/'simple')
     │         └──────────────┘  - quantity, unit_price
     │
     ├────────────────┐
     │                ▼
     │         ┌──────────────┐
     │         │  CashFlow    │  Financial audit trail
     │         │(cash_flow)   │  - type (entrada/salida)
     │         └──────────────┘  - category (ventas, devoluciones, etc.)
     │
     └────────────────┐
                      ▼
               ┌──────────────┐
               │ SaleReturn   │  Refunds/returns
               │(sale_returns)│  - return_number (RETYYYYMMDD0001)
               └──────────────┘  - inventory_restored (bool)
                                 - cash_flow_adjusted (bool)
```

### Critical Database Concepts

#### 1. Dual Product System

**Products (Inventory)**:
- Raw materials and ingredients
- Tracked with decimal precision (0.001 units)
- Example: "Pollo" (1kg), "Coca Cola" (1 liter), "Tortillas" (1kg)

**Menu Items (Prepared Dishes)**:
- Composed of multiple Products via Recipes
- Example: "Tacos de Pollo" requires 0.2kg Pollo, 0.1kg Tortillas, etc.
- Stock availability calculated from minimum available ingredient

**Simple Products (Individual Sales)**:
- Direct sales linked to inventory
- Example: "Coca Cola 355ml" consumes 0.355 liters from base Product
- Calculated available quantity: `floor(current_stock / cost_per_unit)`

#### 2. Automatic Inventory Deduction

**Location**: `app/Http/Controllers/POSController.php:processMenuItemInventoryDeduction()`

When a sale is completed:
1. For each Menu Item sold:
   - Query related Recipes
   - Deduct `quantity_needed * items_sold` from each Product
   - Create InventoryMovement with reason `venta_automatica`

2. For each Simple Product sold:
   - Deduct `cost_per_unit * items_sold` from base Product
   - Create InventoryMovement

**⚠️ CRITICAL**: This logic ensures inventory stays synchronized with sales. DO NOT bypass this when processing sales.

#### 3. Stock Availability Calculation

**Menu Items** (`app/Models/MenuItem.php`):
```php
// Calculate minimum possible servings based on all recipe ingredients
$available_quantity = min(
    floor($product1_stock / $recipe1_quantity),
    floor($product2_stock / $recipe2_quantity),
    // ... for all recipe items
);
```

**Simple Products** (`app/Models/SimpleProduct.php`):
```php
$available_quantity = floor($product->current_stock / $this->cost_per_unit);
```

**⚠️ IMPORTANT**: The POS validates stock before allowing sales to prevent overselling.

#### 4. Migration Execution Order

Migrations MUST run in this order (handled by timestamps):

1. Core Laravel tables (users, cache, jobs, sessions)
2. categories, suppliers
3. products (depends on categories, suppliers)
4. menu_items
5. recipes (depends on menu_items, products)
6. simple_products (depends on products)
7. inventory_movements (depends on products, users)
8. sales (depends on users)
9. sale_items (depends on sales, menu_items, simple_products)
10. cash_flow (depends on sales, users)
11. sale_returns (depends on sales, users)
12. sale_return_items (depends on sale_returns, sale_items)
13. User role addition
14. Cash flow category updates

**Running Migrations**:
```bash
php artisan migrate --seed    # Fresh install with sample data
php artisan migrate:fresh      # Reset database (DESTRUCTIVE)
php artisan migrate:rollback   # Rollback last batch
```

---

## Key Business Logic

### 1. Point of Sale (POS) Flow

**Controller**: `app/Http/Controllers/POSController.php`
**View**: `resources/js/Pages/Sales/POS.vue`

**Process**:
1. User selects products (menu items or simple products)
2. System validates stock availability in real-time
3. User completes sale (payment method, discount, etc.)
4. Backend:
   - Creates Sale record with unique number `YYYYMMDD0001`
   - Creates SaleItems (polymorphic references)
   - **Automatically deducts inventory** (critical)
   - Creates CashFlow entry (category: 'ventas')
   - Triggers thermal printer (kitchen + customer receipts)
5. Returns sale data to frontend

**Sale Number Format**: `20251115` + `0001` (padded daily counter)

### 2. Thermal Printer Integration

**Service**: `app/Services/ThermalTicketService.php`
**Config**: `config/thermal_printer.php`

**Three Ticket Types**:

1. **Kitchen Order (Comanda)**:
   - 58mm thermal printer
   - Only items requiring kitchen prep (excludes 'Bebidas', 'Postres', 'Extras')
   - Priority number calculation
   - Printed automatically on sale

2. **Customer Receipt**:
   - 80mm thermal printer
   - Complete sale details
   - QR code with sale number
   - Tax breakdown
   - Payment method

3. **Return Receipt**:
   - Similar to customer receipt
   - Shows returned items
   - Refund amount

**Development Mode**:
```php
if (app()->environment('local')) {
    // Save to storage/app/tickets/
    file_put_contents(storage_path("app/tickets/sale_{$saleNumber}.txt"), $ticket);
} else {
    // Send to network printer
    $connector = new NetworkPrintConnector($printerIp, $printerPort);
    $printer = new Printer($connector);
    $printer->text($ticket);
    $printer->close();
}
```

**⚠️ IMPORTANT**: In production, ensure printer IPs are configured in `.env` and printers are on the same network.

### 3. Sales Returns Flow

**Controllers**: `app/Http/Controllers/ReturnController.php`
**Views**: `resources/js/Pages/Returns/`

**Process**:
1. Look up original sale by sale number
2. Select items to return (partial or full)
3. Specify return reason
4. Backend:
   - Creates SaleReturn with unique number `RETYYYYMMDD0001`
   - Creates SaleReturnItems
   - **Restores inventory** (adds back to Products)
   - **Adjusts cash flow** (negative entry, category: 'devoluciones')
   - Marks flags: `inventory_restored=true`, `cash_flow_adjusted=true`
5. Prints return receipt

**Return Number Format**: `RET` + `20251115` + `0001`

**⚠️ CRITICAL**: Returns must restore inventory to maintain accuracy. The system tracks this with boolean flags.

### 4. Cash Flow Tracking

**Model**: `app/Models/CashFlow.php`

**Categories**:
- `ventas` - Sales revenue (entrada)
- `compras` - Inventory purchases (salida)
- `gastos_operativos` - Operational expenses (salida)
- `gastos_admin` - Administrative expenses (salida)
- `devoluciones` - Returns/refunds (salida)
- `otros` - Other transactions

**Auto-Created On**:
- Sales completion (ventas)
- Returns processing (devoluciones)

**Manual Entry**:
- Inventory purchases
- Operational expenses
- Other transactions

**Reports**: Available in admin dashboard (cash flow summary by date range)

---

## Authentication & Authorization

### Authentication Stack

- **Laravel Breeze**: Provides login, registration, password reset, email verification
- **Laravel Sanctum**: API token authentication (SPA mode)
- **Inertia Middleware**: Shares auth user data globally

### Role-Based Access Control (RBAC)

**Middleware**: `app/Http/Middleware/RoleMiddleware.php`

**Roles** (Enum in User model):
- `admin` - Full system access
- `chef` - Menu management, view orders
- `almacenero` - Inventory management (warehouse manager)
- `cajero` - POS operations, sales (cashier)

**Route Protection**:
```php
Route::middleware(['auth', 'role:admin,cajero'])->group(function () {
    // Only admin and cajero can access
});
```

**Role Assignment** (migration: `2025_06_10_004200_add_role_to_users_table.php`):
```php
$table->enum('role', ['admin', 'chef', 'almacenero', 'cajero'])->default('cajero');
$table->boolean('is_active')->default(true);
```

### Route Access Matrix

| Feature | Admin | Chef | Almacenero | Cajero |
|---------|-------|------|------------|--------|
| Dashboard | ✓ | ✓ | ✓ | ✓ |
| POS | ✓ | ✗ | ✗ | ✓ |
| Sales History | ✓ | ✗ | ✗ | ✓ |
| Returns | ✓ | ✗ | ✗ | ✓ |
| Inventory | ✓ | ✗ | ✓ | ✗ |
| Products | ✓ | ✗ | ✓ | ✗ |
| Menu Items | ✓ | ✓ | ✗ | ✗ |
| Recipes | ✓ | ✓ | ✗ | ✗ |
| Cash Flow | ✓ | ✗ | ✗ | ✗ |
| User Management | ✓ | ✗ | ✗ | ✗ |

**⚠️ IMPORTANT**: When creating new routes, always consider role permissions.

---

## Frontend Architecture

### Inertia.js Pattern

**Concept**: "Modern Monolith"
- Server-side routing (Laravel)
- Client-side rendering (Vue 3)
- No API layer needed
- Props passed directly from controllers

**Example Flow**:
```php
// Controller
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

### Component Organization

**Pages** (`resources/js/Pages/`):
- Route-specific components
- Receive props from controllers
- One per route (Dashboard.vue, POS.vue, etc.)

**Components** (`resources/js/Components/`):
- Reusable UI elements
- Form inputs, modals, dropdowns, etc.
- No route-specific logic

**Layouts** (`resources/js/Layouts/`):
- Page wrappers
- `AuthenticatedLayout.vue` - For logged-in users (sidebar, header)
- `GuestLayout.vue` - For auth pages

### Ziggy Route Helpers

**Usage in Vue**:
```vue
<script setup>
import { router } from '@inertiajs/vue3';

// Navigate to route
router.visit(route('sales.show', saleId));

// Generate URL
const url = route('api.products.search', { query: 'coca' });
</script>

<template>
    <Link :href="route('sales.index')" class="btn">
        View Sales
    </Link>
</template>
```

**Available globally** via Ziggy plugin in `app.js`.

### Tailwind Conventions

**Utility-First Approach**:
```vue
<template>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h1 class="text-2xl font-semibold text-gray-900 mb-4">
                Point of Sale
            </h1>
        </div>
    </div>
</template>
```

**No Custom CSS**: Avoid creating `.css` files. Use Tailwind utilities.

**Form Styling**: `@tailwindcss/forms` plugin provides base styles.

---

## Testing Guidelines

### Current Test Coverage

**Existing Tests**:
- `tests/Feature/ExampleTest.php` - Basic application test
- `tests/Feature/ProfileTest.php` - User profile management
- Laravel Breeze auth tests (login, registration, password reset)

**Coverage Gaps** (Opportunities):
- ❌ POS transaction processing
- ❌ Inventory deduction logic
- ❌ Stock availability calculations
- ❌ Sales return processing
- ❌ Role middleware enforcement
- ❌ Thermal printer service

### Writing Tests

**Feature Test Template**:
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

**Running Tests**:
```bash
php artisan test                    # Run all tests
php artisan test --filter=Inventory # Run specific test
php artisan test --coverage         # With coverage report
```

**Test Database**:
- Uses separate `testing` database (from `phpunit.xml`)
- Recommend SQLite in-memory: `:memory:`
- Migrations run automatically via `RefreshDatabase` trait

---

## Deployment Considerations

### Pre-Deployment Checklist

**Environment**:
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate production key: `php artisan key:generate`
- [ ] Configure database (MySQL recommended)
- [ ] Set correct `APP_URL`

**Database**:
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed initial data if needed
- [ ] Backup strategy in place

**Assets**:
- [ ] Build frontend: `npm run build`
- [ ] Verify assets in `public/build/`

**Optimization**:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

**Permissions**:
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Queue Worker** (required for async operations):
```bash
# Systemd service or Supervisor
php artisan queue:work --tries=3 --timeout=90
```

**Thermal Printers**:
- [ ] Configure printer IPs in `.env`
- [ ] Test network connectivity to printers
- [ ] Verify ESC/POS command compatibility
- [ ] Set `AUTO_PRINT_KITCHEN` and `AUTO_PRINT_CUSTOMER` flags

**Web Server**:
- Document root: `/public`
- Nginx/Apache configuration for Laravel
- HTTPS recommended (Let's Encrypt)

### Production .env Example

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourrestaurant.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restaurant_pos
DB_USERNAME=restaurant_user
DB_PASSWORD=secure_password_here

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

## Common Tasks & Workflows

### Creating a New Product

```bash
# 1. Via Seeder (development)
php artisan db:seed --class=ProductSeeder

# 2. Via UI (production)
# Login as admin or almacenero
# Navigate to /inventory/products
# Click "Add Product"
```

### Processing a Sale

```bash
# Via UI only (no artisan command)
# Login as admin or cajero
# Navigate to /sales/pos
# Select items, process payment
```

### Adding a New Menu Item with Recipe

```bash
# 1. Create menu item (admin or chef)
# Navigate to /menu/items/create

# 2. Define recipe
# In menu item form, add products with quantities
# Example: "Tacos de Pollo"
#   - Pollo: 0.2 kg
#   - Tortillas: 0.1 kg
#   - Salsa: 0.05 lt
```

### Viewing Reports

```bash
# Cash Flow Report
# Navigate to /cashflow (admin only)
# Filter by date range

# Inventory Report
# Navigate to /inventory (admin or almacenero)
# View low stock items, movements
```

### Database Reset (Development)

```bash
# ⚠️ DESTRUCTIVE - Deletes all data
php artisan migrate:fresh --seed

# Creates fresh database with sample data
```

### Code Style Fixing

```bash
# Run Laravel Pint
./vendor/bin/pint

# Fix specific file
./vendor/bin/pint app/Http/Controllers/POSController.php
```

### Debugging

```bash
# Real-time logs
php artisan pail

# Tinker REPL
php artisan tinker
>>> $sales = \App\Models\Sale::with('saleItems')->latest()->take(5)->get();
>>> $product = \App\Models\Product::find(1);
>>> $product->current_stock;
```

---

## Code Conventions & Patterns

### Naming Conventions

**PHP (Laravel Standards)**:
- Models: `PascalCase`, singular (Product, MenuItem)
- Controllers: `PascalCase` + `Controller` suffix (ProductController)
- Methods: `camelCase` (processMenuItemInventoryDeduction)
- Database tables: `snake_case`, plural (menu_items, sale_returns)
- Migrations: Timestamp + description (2025_06_10_001727_create_categories_table.php)

**JavaScript/Vue**:
- Components: `PascalCase` (ApplicationLogo.vue, TextInput.vue)
- Props/variables: `camelCase` (menuItems, currentStock)
- Events: `kebab-case` (@click, @update:model-value)

**Business Terms (Spanish)**:
- Use Spanish for domain-specific terms in code
- Examples: `cajero`, `almacenero`, `devoluciones`, `comandas`
- Comments can be Spanish or English

### Design Patterns

**Service Layer**:
```php
// app/Services/ThermalTicketService.php
class ThermalTicketService
{
    public function printKitchenOrder(Sale $sale): void
    {
        // Centralized printer logic
    }
}

// Usage in controller
app(ThermalTicketService::class)->printKitchenOrder($sale);
```

**Repository Pattern** (Not implemented, but recommended):
```php
// Future improvement
interface ProductRepositoryInterface
{
    public function getLowStockProducts(): Collection;
    public function getExpiringSoonProducts(): Collection;
}
```

**Polymorphic Relationships**:
```php
// SaleItem can reference MenuItem OR SimpleProduct
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

    // Accessor for unified interface
    public function getProductNameAttribute()
    {
        return $this->product_type === 'menu'
            ? $this->menuItem->name
            : $this->simpleProduct->name;
    }
}
```

### Database Conventions

**Decimal Precision**:
- Stock quantities: `decimal(10, 3)` - Supports grams/milliliters
- Prices/costs: `decimal(10, 2)` - Standard currency
- Usage: Always use decimals for quantities (never integers)

**Timestamps**:
- Use `timestamps()` in migrations (created_at, updated_at)
- Business dates separate: `movement_date`, `flow_date`, `return_date`

**Foreign Keys**:
- Always define: `$table->foreignId('product_id')->constrained()`
- Cascade deletes where appropriate: `->onDelete('cascade')`
- Set null for historical data: `->onDelete('set null')`

**Unique Constraints**:
```php
$table->unique('email');
$table->unique('sale_number');
$table->unique(['menu_item_id', 'product_id']); // Composite
```

### Error Handling

**Controller Validation**:
```php
$request->validate([
    'name' => 'required|string|max:255',
    'current_stock' => 'required|numeric|min:0',
    'unit_type' => 'required|in:kg,lt,pcs,g,ml',
]);
```

**Try-Catch for Transactions**:
```php
DB::beginTransaction();
try {
    // Create sale
    // Deduct inventory
    // Create cash flow
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    return back()->withErrors(['error' => $e->getMessage()]);
}
```

**Flash Messages** (Inertia):
```php
return redirect()->route('sales.index')->with('success', 'Sale completed successfully');
```

### Security Best Practices

**Mass Assignment Protection**:
```php
class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'description', 'unit_type',
        'current_stock', 'min_stock', 'max_stock', 'unit_cost',
    ];

    // NEVER use $guarded = [];
}
```

**SQL Injection Prevention**:
- Use Eloquent ORM (auto-escapes)
- Use query builder with bindings
- NEVER concatenate user input into raw SQL

**XSS Prevention**:
- Blade/Vue auto-escapes: `{{ $variable }}`
- Raw HTML (careful): `{!! $html !!}` or `v-html`
- Sanitize user input before storage

**CSRF Protection**:
- Enabled by default in Laravel
- Inertia handles tokens automatically
- Forms include `@csrf` directive

---

## Troubleshooting

### Common Issues

**1. "Class 'X' not found"**
```bash
composer dump-autoload
```

**2. Vite connection refused (HMR)**
```bash
# Check Vite is running
npm run dev

# In .env, verify:
APP_URL=http://localhost
```

**3. Inertia version mismatch**
```bash
# Update both server and client
composer update inertiajs/inertia-laravel
npm update @inertiajs/vue3
```

**4. Database locked (SQLite)**
```bash
# Stop queue workers
# Check for other PHP processes accessing DB
ps aux | grep artisan
```

**5. Thermal printer not responding**
```bash
# Test network connectivity
ping 192.168.1.100

# Check port
nc -zv 192.168.1.100 9100

# Verify in logs
php artisan pail --filter=printer
```

**6. Stock not deducting**
```bash
# Check inventory movements
php artisan tinker
>>> \App\Models\InventoryMovement::where('reason', 'venta_automatica')->latest()->take(10)->get();

# Verify queue is running
php artisan queue:work
```

**7. Permission denied (storage/logs)**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Debugging Tools

**Laravel Debugbar** (optional, install if needed):
```bash
composer require barryvdh/laravel-debugbar --dev
```

**Ray** (optional, premium debugging tool):
```bash
composer require spatie/laravel-ray
```

**Database Queries**:
```php
DB::enableQueryLog();
// ... run code
dd(DB::getQueryLog());
```

**Inertia Debug**:
```vue
<script setup>
const props = defineProps({
    menuItems: Array,
});

console.log('Props:', props);
</script>
```

---

## Additional Resources

### Laravel Documentation
- Framework: https://laravel.com/docs
- Eloquent ORM: https://laravel.com/docs/eloquent
- Migrations: https://laravel.com/docs/migrations
- Validation: https://laravel.com/docs/validation

### Inertia.js Documentation
- Main Docs: https://inertiajs.com
- Vue 3 Integration: https://inertiajs.com/client-side-setup

### Vue 3 Documentation
- Official Docs: https://vuejs.org
- Composition API: https://vuejs.org/api/composition-api-setup.html

### Tailwind CSS Documentation
- Framework: https://tailwindcss.com/docs
- Forms Plugin: https://github.com/tailwindlabs/tailwindcss-forms

### Thermal Printing
- ESC/POS PHP: https://github.com/mike42/escpos-php
- Command Reference: https://reference.epson-biz.com/modules/ref_escpos/

---

## Repository Maintenance Notes

### Known Technical Debt

1. **Testing Coverage**: Minimal feature tests (only auth/profile)
2. **API Layer**: None (all Inertia-based)
3. **Type Safety**: No TypeScript (pure JavaScript)
4. **Repository Pattern**: Not implemented (controllers directly use models)
5. **Form Requests**: Not extensively used (validation in controllers)

### Junk Files to Clean Up

The following files in the root directory should be removed:
- `CashFlow::where('category', 'devoluciones')->latest()->first();`
- `CashFlow::where('category', 'gastos_operativos')`
- `InventoryMovement::where('reason', 'devolucion_producto_simple')->get();`
- `available_quantity`
- `product`
- `t()->first();exi`

These appear to be debugging artifacts from development.

### Future Enhancements (Suggested)

1. **Dashboard Analytics**: Charts for sales trends, popular items
2. **Multi-Store Support**: Franchise/chain restaurant management
3. **Employee Time Tracking**: Clock in/out for staff
4. **Table Management**: Restaurant floor plan, table assignments
5. **Online Orders**: Integration with delivery platforms
6. **Loyalty Program**: Customer points/rewards system
7. **Supplier Management**: Purchase orders, delivery tracking
8. **Production Planning**: Ingredient forecasting based on sales
9. **Mobile App**: Native iOS/Android for POS
10. **Real-Time Notifications**: WebSockets for kitchen display system

---

## Quick Reference Commands

```bash
# Development
composer run dev              # Start all dev servers
php artisan serve            # HTTP server
npm run dev                  # Vite HMR
php artisan queue:work       # Queue worker
php artisan pail             # Log viewer

# Database
php artisan migrate          # Run migrations
php artisan migrate:fresh    # Reset database
php artisan db:seed          # Seed data
php artisan migrate:fresh --seed  # Reset + seed

# Testing
php artisan test             # Run tests
php artisan test --filter=X  # Run specific test
composer test                # Via composer script

# Optimization
php artisan optimize         # Optimize everything
php artisan config:cache     # Cache config
php artisan route:cache      # Cache routes
php artisan view:cache       # Cache views
php artisan optimize:clear   # Clear all caches

# Code Quality
./vendor/bin/pint           # Fix code style

# Docker (Sail)
./vendor/bin/sail up -d     # Start containers
./vendor/bin/sail down      # Stop containers
./vendor/bin/sail artisan   # Run artisan in container
./vendor/bin/sail composer  # Run composer in container
```

---

**End of Documentation**

For questions or clarifications about this codebase, please refer to specific sections above or consult the inline code comments in critical files marked with ⚠️.
