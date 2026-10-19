<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleControll
use Illuminate\Support\Facades\Route;

// Redirect awal
Route::get('/', fn () => redirect()->route(session()->has('user_id') ? 'dashboard' : 'login'));

// Authentication (Login/Logout)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Group route yang membutuhkan login
Route::middleware('role')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // List item untuk Select2 dropdown transaksi
    Route::get('/inventories/items', [InventoryController::class, 'list'])->name('inventories.items');

    // ==========================================
    // INVENTORIES (Khusus SuperAdmin)
    // ==========================================
    Route::middleware('role:SuperAdmin')->group(function () {
        Route::get('inventories/data', [InventoryController::class, 'data'])->name('inventories.data');
        Route::resource('inventories', InventoryController::class)->except(['create', 'edit']);
    });

    // ==========================================
    // SALES (Penjualan)
    // ==========================================
    // Read & Print: SuperAdmin, Sales, Manager
    Route::middleware('role:SuperAdmin,Sales,Manager')->group(function () {
        Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('sales/data', [SaleController::class, 'data'])->name('sales.data');
        Route::get('sales/{id}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('sales/{id}/print', [SaleController::class, 'print'])->name('sales.print');
    });

    // Write (Create, Update, Delete): SuperAdmin & Sales
    Route::middleware('role:SuperAdmin,Sales')->group(function () {
        Route::post('sales', [SaleController::class, 'store'])->name('sales.store');
        Route::put('sales/{id}', [SaleController::class, 'update'])->name('sales.update');
        Route::delete('sales/{id}', [SaleController::class, 'destroy'])->name('sales.destroy');
    });

    // ==========================================
    // PURCHASES (Pembelian)
    // ==========================================
    // Read & Print: SuperAdmin, Purchase, Manager
    Route::middleware('role:SuperAdmin,Purchase,Manager')->group(function () {
        Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('purchases/data', [PurchaseController::class, 'data'])->name('purchases.data');
        Route::get('purchases/{id}', [PurchaseController::class, 'show'])->name('purchases.show');
        Route::get('purchases/{id}/print', [PurchaseController::class, 'print'])->name('purchases.print');
    });

    // Write (Create, Update, Delete): SuperAdmin & Purchase
    Route::middleware('role:SuperAdmin,Purchase')->group(function () {
        Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::put('purchases/{id}', [PurchaseController::class, 'update'])->name('purchases.update');
        Route::delete('purchases/{id}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
    });
});