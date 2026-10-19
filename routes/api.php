<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\InventoryController;
use App\Http\Controllers\API\PurchaseController;
use App\Http\Controllers\API\SaleController;
use Illuminate\Support\Facades\Route;

// Public Auth Endpoints
Route::get('/auth/users', [AuthController::class, 'users'])->name('api.auth.users');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Protected API Endpoints
Route::middleware('role')->group(function () {
    // Inventory dropdown list (needed for transaction forms)
    Route::get('/inventories/items', [InventoryController::class, 'items'])->name('api.inventories.items');

    // Inventories CRUD (SuperAdmin only)
    Route::middleware('role:SuperAdmin')->group(function () {
        Route::get('/inventories/data', [InventoryController::class, 'data'])->name('api.inventories.data');
        Route::get('/inventories/{id}', [InventoryController::class, 'show'])->name('api.inventories.show');
        Route::post('/inventories', [InventoryController::class, 'store'])->name('api.inventories.store');
        Route::put('/inventories/{id}', [InventoryController::class, 'update'])->name('api.inventories.update');
        Route::delete('/inventories/{id}', [InventoryController::class, 'destroy'])->name('api.inventories.destroy');
    });

    // Purchases Read (SuperAdmin, Purchase, Manager)
    Route::middleware('role:SuperAdmin,Purchase,Manager')->group(function () {
        Route::get('/purchases/data', [PurchaseController::class, 'data'])->name('api.purchases.data');
        Route::get('/purchases/{id}', [PurchaseController::class, 'show'])->name('api.purchases.show');
    });

    // Purchases Write (SuperAdmin, Purchase)
    Route::middleware('role:SuperAdmin,Purchase')->group(function () {
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('api.purchases.store');
        Route::put('/purchases/{id}', [PurchaseController::class, 'update'])->name('api.purchases.update');
        Route::delete('/purchases/{id}', [PurchaseController::class, 'destroy'])->name('api.purchases.destroy');
    });

    // Sales Read (SuperAdmin, Sales, Manager)
    Route::middleware('role:SuperAdmin,Sales,Manager')->group(function () {
        Route::get('/sales/data', [SaleController::class, 'data'])->name('api.sales.data');
        Route::get('/sales/{id}', [SaleController::class, 'show'])->name('api.sales.show');
    });

    // Sales Write (SuperAdmin, Sales)
    Route::middleware('role:SuperAdmin,Sales')->group(function () {
        Route::post('/sales', [SaleController::class, 'store'])->name('api.sales.store');
        Route::put('/sales/{id}', [SaleController::class, 'update'])->name('api.sales.update');
        Route::delete('/sales/{id}', [SaleController::class, 'destroy'])->name('api.sales.destroy');
    });
});
