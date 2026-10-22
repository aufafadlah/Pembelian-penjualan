<?php

use App\Http\Controllers\WEB\AuthController;
use App\Http\Controllers\WEB\DashboardController;
use App\Http\Controllers\WEB\InventoryController;
use App\Http\Controllers\WEB\PurchaseController;
use App\Http\Controllers\WEB\SaleController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard if logged in, otherwise login
Route::get('/', function () {
    return redirect()->route(session()->has('user_id') ? 'dashboard' : 'login');
});

// Authentication View Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected View Routes
Route::middleware('role')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Inventories (SuperAdmin only)
    Route::middleware('role:SuperAdmin')->group(function () {
        Route::get('/inventories', [InventoryController::class, 'index'])->name('inventories.index');
    });

    // Purchases Views
    Route::middleware('role:SuperAdmin,Purchase,Manager')->group(function () {
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/{id}/print', [PurchaseController::class, 'print'])->name('purchases.print');
    });

    Route::middleware('role:SuperAdmin,Purchase')->group(function () {
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::get('/purchases/{id}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
    });

    // Sales Views
    Route::middleware('role:SuperAdmin,Sales,Manager')->group(function () {
        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/{id}/print', [SaleController::class, 'print'])->name('sales.print');
    });

    Route::middleware('role:SuperAdmin,Sales')->group(function () {
        Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::get('/sales/{id}/edit', [SaleController::class, 'edit'])->name('sales.edit');
    });
});