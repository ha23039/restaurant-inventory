<?php

use App\Http\Controllers\DashboardController;
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
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
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
});

/*
|--------------------------------------------------------------------------
| Rutas para ADMINISTRADORES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', function () {
        return Inertia::render('Admin/Users');
    })->name('users');

    Route::get('/reports', function () {
        return Inertia::render('Admin/Reports');
    })->name('reports');

    Route::get('/settings', function () {
        return Inertia::render('Admin/Settings');
    })->name('settings');
});

/*
|--------------------------------------------------------------------------
| Rutas para CAJA REGISTRADORA (Admin + Cajero)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,cajero'])->prefix('cashregister')->name('cashregister.')->group(function () {
    // Vista principal de caja
    Route::get('/', [App\Http\Controllers\CashRegisterController::class, 'index'])->name('index');

    // Abrir caja
    Route::get('/open', [App\Http\Controllers\CashRegisterController::class, 'create'])->name('create');
    Route::post('/open', [App\Http\Controllers\CashRegisterController::class, 'store'])->name('store');

    // Cerrar caja
    Route::get('/close', [App\Http\Controllers\CashRegisterController::class, 'closeForm'])->name('close.form');
    Route::post('/close', [App\Http\Controllers\CashRegisterController::class, 'close'])->name('close');

    // Ver detalles de sesión
    Route::get('/{id}', [App\Http\Controllers\CashRegisterController::class, 'show'])->name('show');

    // Historial de sesiones
    Route::get('/history', [App\Http\Controllers\CashRegisterController::class, 'history'])->name('history');

    // Estadísticas
    Route::get('/stats', [App\Http\Controllers\CashRegisterController::class, 'stats'])->name('stats');

    // API: Sesión actual
    Route::get('/api/current', [App\Http\Controllers\CashRegisterController::class, 'current'])->name('api.current');
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
| Rutas para VENTAS (Admin + Cajero)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,cajero'])->prefix('sales')->name('sales.')->group(function () {
    // Dashboard de ventas
    Route::get('/', [App\Http\Controllers\SaleController::class, 'index'])->name('index');

    // POS (Punto de Venta)
    Route::get('/pos', [App\Http\Controllers\POSController::class, 'index'])->name('pos');
    Route::post('/pos', [App\Http\Controllers\POSController::class, 'store'])->name('pos.store');

    // Ver venta específica
    Route::get('/{sale}', [App\Http\Controllers\SaleController::class, 'show'])->name('show');
});

/*
|--------------------------------------------------------------------------
| Rutas para COCINA/MENÚ (Admin + Chef)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,chef'])->prefix('menu')->name('menu.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Menu/Index');
    })->name('index');

    Route::get('/items', function () {
        return Inertia::render('Menu/Items');
    })->name('items');

    Route::get('/recipes', function () {
        return Inertia::render('Menu/Recipes');
    })->name('recipes');
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
| 🔄 Rutas para DEVOLUCIONES (Admin + Cajero)
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
    Route::get('/api/metrics', [ReturnController::class, 'getMetrics'])->name('api.metrics');
    Route::get('/api/operational-losses', [ReturnController::class, 'getOperationalLossReport'])->name('api.operational-losses');
});

/*
|--------------------------------------------------------------------------
| 🧾 RUTAS PARA SISTEMA DE TICKETS TÉRMICOS
|--------------------------------------------------------------------------
| Rutas para impresión de tickets y comandas
*/

Route::middleware(['auth', 'role:admin,cajero'])->prefix('tickets')->name('tickets.')->group(function () {

    // Rutas existentes de impresión
    Route::post('/kitchen/{sale}', [App\Http\Controllers\TicketController::class, 'printKitchenOrder'])
        ->name('kitchen');

    Route::post('/customer/{sale}', [App\Http\Controllers\TicketController::class, 'printCustomerReceipt'])
        ->name('customer');

    Route::post('/return/{return}', [App\Http\Controllers\TicketController::class, 'printReturnReceipt'])
        ->name('return');

    // 🆕 NUEVAS RUTAS DE VISTA PREVIA (AGREGAR ESTAS)
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
| 🧪 RUTAS DE DESARROLLO Y TESTING
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
require __DIR__.'/auth.php';
