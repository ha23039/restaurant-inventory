<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // Si el usuario está autenticado, redirigir al dashboard
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    // Si no está autenticado, redirigir al login
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Rutas Autenticadas
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Dashboard principal (todos los usuarios autenticados)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notificaciones
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/', [NotificationController::class, 'clearAll'])->name('clear-all');
    });
});

/*
|--------------------------------------------------------------------------
| Rutas para ADMINISTRADORES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/reports', function () {
        return Inertia::render('Admin/Reports');
    })->name('reports');

    Route::get('/settings', function () {
        return Inertia::render('Admin/Settings');
    })->name('settings');
});

/*
|--------------------------------------------------------------------------
| Rutas para REPORTES DE VENTAS (Admin solamente)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/sales', [App\Http\Controllers\ReportController::class, 'sales'])->name('sales');
    Route::get('/sales/pdf', [App\Http\Controllers\ReportController::class, 'exportPdf'])->name('sales.pdf');
    Route::get('/sales/excel', [App\Http\Controllers\ReportController::class, 'exportExcel'])->name('sales.excel');
});

/*
|--------------------------------------------------------------------------
| Rutas para GESTIÓN DE USUARIOS (Admin solamente)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('users')->name('users.')->group(function () {
    Route::get('/', [App\Http\Controllers\UserController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\UserController::class, 'store'])->name('store');
    Route::get('/{user}', [App\Http\Controllers\UserController::class, 'show'])->name('show');
    Route::put('/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('destroy');

    // Special actions
    Route::post('/{user}/toggle-status', [App\Http\Controllers\UserController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{user}/reset-password', [App\Http\Controllers\UserController::class, 'resetPassword'])->name('reset-password');
});

/*
|--------------------------------------------------------------------------
| Rutas para GESTIÓN DE CLIENTES (Admin solamente)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('customers')->name('customers.')->group(function () {
    Route::get('/', [App\Http\Controllers\CustomerController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\CustomerController::class, 'store'])->name('store');
    Route::get('/{customer}', [App\Http\Controllers\CustomerController::class, 'show'])->name('show');
    Route::put('/{customer}', [App\Http\Controllers\CustomerController::class, 'update'])->name('update');
    Route::delete('/{customer}', [App\Http\Controllers\CustomerController::class, 'destroy'])->name('destroy');

    // Special actions
    Route::post('/{customer}/toggle-status', [App\Http\Controllers\CustomerController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{customer}/toggle-verified', [App\Http\Controllers\CustomerController::class, 'toggleVerified'])->name('toggle-verified');

    // Cleanup incomplete customers
    Route::post('/cleanup-incomplete', [App\Http\Controllers\CustomerController::class, 'cleanupIncomplete'])->name('cleanup-incomplete');
});

/*
|--------------------------------------------------------------------------
| Rutas para GESTIÓN DE PROVEEDORES (Admin + Almacenero)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,almacenero'])->prefix('suppliers')->name('suppliers.')->group(function () {
    Route::get('/', [App\Http\Controllers\SupplierController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\SupplierController::class, 'store'])->name('store');
    Route::get('/{supplier}', [App\Http\Controllers\SupplierController::class, 'show'])->name('show');
    Route::put('/{supplier}', [App\Http\Controllers\SupplierController::class, 'update'])->name('update');
    Route::delete('/{supplier}', [App\Http\Controllers\SupplierController::class, 'destroy'])->name('destroy');

    // Special actions
    Route::post('/{supplier}/toggle-status', [App\Http\Controllers\SupplierController::class, 'toggleStatus'])->name('toggle-status');
});

/*
|--------------------------------------------------------------------------
| Rutas para GESTIÓN DE MESAS (Admin + Cajero + Mesero)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,cajero,mesero'])->prefix('tables')->name('tables.')->group(function () {
    Route::get('/', [App\Http\Controllers\TableController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\TableController::class, 'store'])->name('store');
    Route::put('/{table}', [App\Http\Controllers\TableController::class, 'update'])->name('update');
    Route::delete('/{table}', [App\Http\Controllers\TableController::class, 'destroy'])->name('destroy');

    // Special actions
    Route::get('/{table}', [App\Http\Controllers\TableController::class, 'show'])->name('show');
    Route::post('/{table}/update-status', [App\Http\Controllers\TableController::class, 'updateStatus'])->name('update-status');
    Route::post('/{table}/occupy', [App\Http\Controllers\TableController::class, 'occupyTable'])->name('occupy');
    Route::post('/{table}/release', [App\Http\Controllers\TableController::class, 'releaseTable'])->name('release');
    Route::post('/{table}/toggle-active', [App\Http\Controllers\TableController::class, 'toggleActive'])->name('toggle-active');

    // Multi-order table management (cobro por mesa)
    Route::get('/{table}/pending-sales', [App\Http\Controllers\TableController::class, 'getPendingSales'])->name('pending-sales');
    Route::post('/{table}/charge-all', [App\Http\Controllers\TableController::class, 'chargeAllSales'])->name('charge-all');
});

// Charge individual sale (separate route because it uses Sale model binding)
Route::middleware(['auth', 'role:admin,cajero,mesero'])->post('/sales/{sale}/charge', [App\Http\Controllers\TableController::class, 'chargeSale'])->name('sales.charge');

/*
|--------------------------------------------------------------------------
| Rutas para CAJA REGISTRADORA (Admin + Cajero + Mesero)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,cajero,mesero'])->prefix('cashregister')->name('cashregister.')->group(function () {
    // Vista principal de caja
    Route::get('/', [App\Http\Controllers\CashRegisterController::class, 'index'])->name('index');

    // Historial de sesiones (ANTES de /{id})
    Route::get('/history', [App\Http\Controllers\CashRegisterController::class, 'history'])->name('history');

    // Estadísticas (ANTES de /{id})
    Route::get('/stats', [App\Http\Controllers\CashRegisterController::class, 'stats'])->name('stats');

    // Abrir caja
    Route::get('/open', [App\Http\Controllers\CashRegisterController::class, 'create'])->name('create');
    Route::post('/open', [App\Http\Controllers\CashRegisterController::class, 'store'])->name('store');

    // Cerrar caja
    Route::get('/close', [App\Http\Controllers\CashRegisterController::class, 'closeForm'])->name('close.form');
    Route::post('/close', [App\Http\Controllers\CashRegisterController::class, 'close'])->name('close');

    // API: Sesión actual
    Route::get('/api/current', [App\Http\Controllers\CashRegisterController::class, 'current'])->name('api.current');

    // Ver detalles de sesión (DEBE estar al final por ser ruta dinámica)
    Route::get('/{id}', [App\Http\Controllers\CashRegisterController::class, 'show'])->name('show');
});

/*
|--------------------------------------------------------------------------
| Rutas para INVENTARIO (Admin + Almacenero)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,almacenero'])->prefix('inventory')->name('inventory.')->group(function () {
    // Dashboard de inventario
    Route::get('/', [App\Http\Controllers\InventoryController::class, 'index'])->name('index');

    // Products
    Route::get('/products', [App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [App\Http\Controllers\ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [App\Http\Controllers\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [App\Http\Controllers\ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/edit', [App\Http\Controllers\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [App\Http\Controllers\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [App\Http\Controllers\ProductController::class, 'destroy'])->name('products.destroy');

    // Categories
    Route::get('/categories', [App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');

    // Movements
    Route::get('/movements', [App\Http\Controllers\InventoryMovementController::class, 'index'])->name('movements.index');

    // API para alertas
    Route::get('/alerts', [App\Http\Controllers\ProductController::class, 'alerts'])->name('alerts');
});

/*
|--------------------------------------------------------------------------
| Rutas para VENTAS (Admin + Cajero + Mesero)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,cajero,mesero'])->prefix('sales')->name('sales.')->group(function () {
    // Dashboard de ventas
    Route::get('/', [App\Http\Controllers\SaleController::class, 'index'])->name('index');

    // POS (Punto de Venta) - Requiere caja abierta
    Route::middleware('validate.cashregister')->group(function () {
        Route::get('/pos', [App\Http\Controllers\POSController::class, 'index'])->name('pos');
        Route::post('/pos', [App\Http\Controllers\POSController::class, 'store'])->name('pos.store');
        Route::post('/{sale}/complete-pending', [App\Http\Controllers\POSController::class, 'completePendingSale'])->name('complete-pending');
    });

    // Ver venta específica
    Route::get('/{sale}', [App\Http\Controllers\SaleController::class, 'show'])->name('show');

    // Eliminar/Cancelar venta (solo admin - validado por policy)
    Route::delete('/{sale}', [App\Http\Controllers\SaleController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Rutas para KITCHEN DISPLAY (Admin + Chef + Cajero + Mesero)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,chef,cajero,mesero'])->prefix('kitchen')->name('kitchen.')->group(function () {
    // Vista principal del Kitchen Display
    Route::get('/display', [App\Http\Controllers\KitchenDisplayController::class, 'index'])->name('display');

    // API para obtener órdenes (polling)
    Route::get('/api/orders', [App\Http\Controllers\KitchenDisplayController::class, 'getOrders'])->name('api.orders');

    // Actualizar estado de orden
    Route::patch('/api/orders/{order}/status', [App\Http\Controllers\KitchenDisplayController::class, 'updateStatus'])->name('api.update-status');
});

/*
|--------------------------------------------------------------------------
| Rutas para COCINA/MENÚ (Admin + Chef)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,chef'])->prefix('carta')->name('carta.')->group(function () {
    Route::get('/', function () {
        $stats = [
            'total_items' => \App\Models\MenuItem::count(),
            'available_items' => \App\Models\MenuItem::where('is_available', true)->count(),
            'with_recipes' => \App\Models\MenuItem::has('recipes')->count(),
            'total_recipes' => \App\Models\Recipe::count(),
            'total_simple_products' => \App\Models\SimpleProduct::count(),
        ];

        return Inertia::render('Menu/Index', [
            'stats' => $stats,
        ]);
    })->name('index');

    // Menu Items (Platillos)
    Route::get('/items', [App\Http\Controllers\MenuItemController::class, 'index'])->name('items');
    Route::post('/items', [App\Http\Controllers\MenuItemController::class, 'store'])->name('items.store');
    Route::get('/items/{menuItem}', [App\Http\Controllers\MenuItemController::class, 'show'])->name('items.show');
    Route::put('/items/{menuItem}', [App\Http\Controllers\MenuItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{menuItem}', [App\Http\Controllers\MenuItemController::class, 'destroy'])->name('items.destroy');
    Route::patch('/items/{menuItem}/toggle-availability', [App\Http\Controllers\MenuItemController::class, 'toggleAvailability'])->name('items.toggle-availability');
    Route::get('/items/{menuItem}/variants', [App\Http\Controllers\MenuItemController::class, 'getVariants'])->name('api.menu-items.variants');

    // Menu Item Variants
    Route::post('/items/{menuItem}/variants', [App\Http\Controllers\MenuItemVariantController::class, 'store'])->name('variants.store');
    Route::put('/variants/{variant}', [App\Http\Controllers\MenuItemVariantController::class, 'update'])->name('variants.update');
    Route::delete('/variants/{variant}', [App\Http\Controllers\MenuItemVariantController::class, 'destroy'])->name('variants.destroy');
    Route::patch('/variants/{variant}/toggle-availability', [App\Http\Controllers\MenuItemVariantController::class, 'toggleAvailability'])->name('variants.toggle-availability');

    // Export Menu
    Route::get('/export/pdf', [App\Http\Controllers\MenuExportController::class, 'exportPdf'])->name('export.pdf');

    // Recipes (Recetas)
    Route::get('/recipes', [App\Http\Controllers\RecipeController::class, 'index'])->name('recipes');
    Route::post('/recipes', [App\Http\Controllers\RecipeController::class, 'store'])->name('recipes.store');
    Route::post('/recipes/bulk', [App\Http\Controllers\RecipeController::class, 'bulkStore'])->name('recipes.bulk-store');
    Route::put('/recipes/{recipe}', [App\Http\Controllers\RecipeController::class, 'update'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [App\Http\Controllers\RecipeController::class, 'destroy'])->name('recipes.destroy');
});

/*
|--------------------------------------------------------------------------
| Rutas para PRODUCTOS SIMPLES (Admin + Almacenero)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,almacenero'])->prefix('simple-products')->name('simple-products.')->group(function () {
    Route::get('/', [App\Http\Controllers\SimpleProductController::class, 'index'])->name('index');
    Route::get('/{simpleProduct}', [App\Http\Controllers\SimpleProductController::class, 'show'])->name('show');
    Route::post('/', [App\Http\Controllers\SimpleProductController::class, 'store'])->name('store');
    Route::put('/{simpleProduct}', [App\Http\Controllers\SimpleProductController::class, 'update'])->name('update');
    Route::delete('/{simpleProduct}', [App\Http\Controllers\SimpleProductController::class, 'destroy'])->name('destroy');

    // Simple Product Variants
    Route::post('/{simpleProduct}/variants', [App\Http\Controllers\SimpleProductVariantController::class, 'store'])->name('variants.store');
    Route::put('/variants/{variant}', [App\Http\Controllers\SimpleProductVariantController::class, 'update'])->name('variants.update');
    Route::delete('/variants/{variant}', [App\Http\Controllers\SimpleProductVariantController::class, 'destroy'])->name('variants.destroy');
    Route::patch('/variants/{variant}/toggle-availability', [App\Http\Controllers\SimpleProductVariantController::class, 'toggleAvailability'])->name('variants.toggle-availability');
});

/*
|--------------------------------------------------------------------------
| Rutas para FLUJO DE CAJA (Admin solamente)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('cashflow')->name('cashflow.')->group(function () {
    Route::get('/', [App\Http\Controllers\CashFlowController::class, 'index'])->name('index');
    Route::get('/search', [App\Http\Controllers\CashFlowController::class, 'search'])->name('search');
    Route::get('/summary', [App\Http\Controllers\CashFlowController::class, 'getSummary'])->name('summary');
    Route::get('/export-csv', [App\Http\Controllers\CashFlowController::class, 'exportCsv'])->name('export-csv');
    Route::get('/export-excel', [App\Http\Controllers\CashFlowController::class, 'exportExcel'])->name('export-excel');
    Route::get('/export-pdf', [App\Http\Controllers\CashFlowController::class, 'exportPdf'])->name('export-pdf');
});

/*
|--------------------------------------------------------------------------
| Rutas para DASHBOARD FINANCIERO (Admin solamente)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('financial')->name('financial.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\FinancialDashboardController::class, 'index'])->name('dashboard');
    Route::get('/kpis', [App\Http\Controllers\FinancialDashboardController::class, 'getKpis'])->name('kpis');
    Route::get('/trends', [App\Http\Controllers\FinancialDashboardController::class, 'getTrends'])->name('trends');
});

/*
|--------------------------------------------------------------------------
| Rutas para CONFIGURACIÓN (Admin solamente)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('/business', [App\Http\Controllers\SettingsController::class, 'index'])->name('business');
    Route::post('/business', [App\Http\Controllers\SettingsController::class, 'update'])->name('update');

    // Payment Methods
    Route::post('/payment-methods', [App\Http\Controllers\SettingsController::class, 'updatePaymentMethods'])->name('payment-methods.update');
    Route::delete('/payment-methods/{paymentMethod}', [App\Http\Controllers\SettingsController::class, 'deletePaymentMethod'])->name('payment-methods.destroy');

    // Printer Settings
    Route::post('/printers', [App\Http\Controllers\SettingsController::class, 'updatePrinterSettings'])->name('printers.update');

    // Ticket Settings
    Route::post('/tickets', [App\Http\Controllers\SettingsController::class, 'updateTicketSettings'])->name('tickets.update');

    // Order Settings
    Route::post('/orders', [App\Http\Controllers\SettingsController::class, 'updateOrderSettings'])->name('orders.update');
});

/*
|--------------------------------------------------------------------------
| Rutas para GASTOS (Admin solamente)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('expenses')->name('expenses.')->group(function () {
    Route::get('/', [App\Http\Controllers\ExpenseController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\ExpenseController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\ExpenseController::class, 'store'])->name('store');
    Route::get('/{expense}', [App\Http\Controllers\ExpenseController::class, 'show'])->name('show');
    Route::get('/{expense}/edit', [App\Http\Controllers\ExpenseController::class, 'edit'])->name('edit');
    Route::put('/{expense}', [App\Http\Controllers\ExpenseController::class, 'update'])->name('update');
    Route::delete('/{expense}', [App\Http\Controllers\ExpenseController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Rutas API
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    Route::get('/products', [App\Http\Controllers\Api\ProductApiController::class, 'index'])->name('products.index');
});

/*
|--------------------------------------------------------------------------
| Rutas para DASHBOARD FINANCIERO (Admin solamente)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('financial')->name('financial.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\FinancialDashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/kpis', [App\Http\Controllers\FinancialDashboardController::class, 'getKpis'])->name('api.kpis');
    Route::get('/api/trends', [App\Http\Controllers\FinancialDashboardController::class, 'getTrends'])->name('api.trends');
});

/*
|--------------------------------------------------------------------------
|  Rutas para DEVOLUCIONES (Admin + Cajero)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,cajero'])->prefix('returns')->name('returns.')->group(function () {
    // Lista de devoluciones
    Route::get('/', [App\Http\Controllers\ReturnController::class, 'index'])->name('index');

    // Crear nueva devolución
    Route::get('/create', [App\Http\Controllers\ReturnController::class, 'create'])->name('create');
    Route::post('/search-sale', [App\Http\Controllers\ReturnController::class, 'searchSale'])->name('search-sale');
    Route::post('/', [App\Http\Controllers\ReturnController::class, 'store'])->name('store');

    // Ver devolución específica
    Route::get('/{return}', [App\Http\Controllers\ReturnController::class, 'show'])->name('show');
    //  NUEVAS RUTAS PARA REPORTES
    Route::get('/api/metrics', [App\Http\Controllers\ReturnController::class, 'getMetrics'])->name('api.metrics');
    Route::get('/api/operational-losses', [App\Http\Controllers\ReturnController::class, 'getOperationalLossReport'])->name('api.operational-losses');
});

/*
|--------------------------------------------------------------------------
|  RUTAS PARA SISTEMA DE TICKETS TÉRMICOS
|--------------------------------------------------------------------------
| Rutas para impresión de tickets y comandas
*/

Route::middleware(['auth', 'role:admin,cajero,mesero'])->prefix('tickets')->name('tickets.')->group(function () {

    // Rutas existentes de impresión
    Route::post('/kitchen/{sale}', [App\Http\Controllers\TicketController::class, 'printKitchenOrder'])
        ->name('kitchen');

    Route::post('/customer/{sale}', [App\Http\Controllers\TicketController::class, 'printCustomerReceipt'])
        ->name('customer');

    Route::post('/return/{return}', [App\Http\Controllers\TicketController::class, 'printReturnReceipt'])
        ->name('return');

    // NUEVAS RUTAS DE VISTA PREVIA (AGREGAR ESTAS)
    Route::get('/preview/kitchen/{sale}', [App\Http\Controllers\TicketController::class, 'previewKitchenOrder'])
        ->name('preview.kitchen');

    Route::get('/preview/customer/{sale}', [App\Http\Controllers\TicketController::class, 'previewCustomerReceipt'])
        ->name('preview.customer');

    // Resto de rutas existentes
    Route::post('/autoprint/{sale}', [App\Http\Controllers\TicketController::class, 'autoprint'])
        ->name('autoprint');

    Route::post('/reprint/{sale}', [App\Http\Controllers\TicketController::class, 'reprint'])
        ->name('reprint');

    Route::middleware('role:admin')->group(function () {
        Route::get('/stats', [App\Http\Controllers\TicketController::class, 'getPrintingStats'])
            ->name('stats');
    });
});

/*
|--------------------------------------------------------------------------
|  RUTAS DE DESARROLLO Y TESTING
|--------------------------------------------------------------------------
| Solo disponibles en entorno de desarrollo
*/

if (app()->environment(['local', 'development'])) {
    Route::middleware(['auth'])->prefix('dev')->name('dev.')->group(function () {
        // Test de impresoras
        Route::get('/test-printers', [App\Http\Controllers\TicketController::class, 'testPrinters'])
            ->name('test.printers');

        // Ver archivos de tickets generados (desarrollo)
        Route::get('/tickets', function () {
            $ticketPath = storage_path('app/tickets/');
            $files = [];

            if (is_dir($ticketPath)) {
                $files = array_map(function ($file) {
                    return [
                        'name' => basename($file),
                        'content' => file_get_contents($file),
                        'size' => filesize($file),
                        'created' => date('Y-m-d H:i:s', filemtime($file)),
                    ];
                }, glob($ticketPath.'*.txt'));
            }

            return response()->json([
                'files' => $files,
                'total' => count($files),
            ]);
        })->name('tickets.list');

        // Limpiar archivos de desarrollo
        Route::delete('/tickets/clean', function () {
            $ticketPath = storage_path('app/tickets/');
            $deleted = 0;

            if (is_dir($ticketPath)) {
                foreach (glob($ticketPath.'*.txt') as $file) {
                    if (unlink($file)) {
                        $deleted++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'deleted' => $deleted,
                'message' => "Se eliminaron {$deleted} archivos de prueba",
            ]);
        })->name('tickets.clean');
    });
}

/*
|--------------------------------------------------------------------------
|  RUTAS PARA REPORTES CONSOLIDADOS (Admin solamente)
|--------------------------------------------------------------------------
| Rutas para exportar reportes consolidados en diferentes formatos
*/

Route::middleware(['auth', 'role:admin'])->prefix('consolidated-reports')->name('consolidated-reports.')->group(function () {
    Route::post('/executive/export', [App\Http\Controllers\ConsolidatedReportController::class, 'exportExecutiveReport'])->name('executive.export');
    Route::post('/financial/export', [App\Http\Controllers\ConsolidatedReportController::class, 'exportFinancialReport'])->name('financial.export');
    Route::post('/profitability/export', [App\Http\Controllers\ConsolidatedReportController::class, 'exportProfitabilityReport'])->name('profitability.export');
    Route::post('/inventory/export', [App\Http\Controllers\ConsolidatedReportController::class, 'exportInventoryReport'])->name('inventory.export');
});

/*
|--------------------------------------------------------------------------
| Rutas del Menu Digital (Publicas)
|--------------------------------------------------------------------------
| Estas rutas permiten a los clientes ver el menu, registrarse,
| verificarse y realizar pedidos desde sus dispositivos moviles
*/

Route::prefix('menu')->name('digital-menu.')->group(function () {
    // Vista principal del menu (publica)
    Route::get('/', [App\Http\Controllers\DigitalMenu\MenuController::class, 'index'])->name('index');

    // Autenticacion de clientes
    Route::post('/auth/request-code', [App\Http\Controllers\DigitalMenu\AuthController::class, 'requestCode'])->name('auth.request');
    Route::post('/auth/verify', [App\Http\Controllers\DigitalMenu\AuthController::class, 'verifyCode'])->name('auth.verify');
    Route::post('/auth/logout', [App\Http\Controllers\DigitalMenu\AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/auth/me', [App\Http\Controllers\DigitalMenu\AuthController::class, 'me'])->name('auth.me');

    // Crear orden
    Route::post('/order', [App\Http\Controllers\DigitalMenu\OrderController::class, 'store'])->name('order.store');

    // Tracking de orden
    Route::get('/order/{saleNumber}', [App\Http\Controllers\DigitalMenu\OrderTrackingController::class, 'show'])->name('order.show');
    Route::get('/order/{saleNumber}/status', [App\Http\Controllers\DigitalMenu\OrderTrackingController::class, 'getStatus'])->name('order.status');
});

/*
|--------------------------------------------------------------------------
| API Routes para Menú Digital - MOVIDAS A routes/api.php
|--------------------------------------------------------------------------
| Las rutas de API ahora están en routes/api.php para evitar problemas
| con CSRF y usar el middleware API correcto
*/

require __DIR__.'/auth.php';
