<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DigitalMenu\AuthController;
use App\Http\Controllers\DigitalMenu\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| API Routes para Menú Digital
|--------------------------------------------------------------------------
| Rutas públicas para el menú digital (sin autenticación de usuarios del sistema)
| IMPORTANTE: Usa middleware 'web' en lugar de 'api' porque necesita sesiones
| para la autenticación de clientes digitales
*/

Route::middleware('web')->prefix('digital-menu')->name('api.digital-menu.')->group(function () {
    // Autenticación
    Route::post('/auth/send-code', [AuthController::class, 'requestCode'])->name('auth.send-code');
    Route::post('/auth/verify-code', [AuthController::class, 'verifyCode'])->name('auth.verify-code');
    Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');

    // Órdenes
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/pending', [OrderController::class, 'getPendingOrders'])->name('orders.pending');
    Route::get('/orders/my-orders', [OrderController::class, 'getMyOrders'])->name('orders.my-orders');
});
