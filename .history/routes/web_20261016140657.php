<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(session()->has('user_id') ? 'dashboard' : 'login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('role')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // Inventories: hanya SuperAdmin (route "data" harus sebelum resource)
    Route::middleware('role:SuperAdmin')->group(function () {
        Route::get('inventories/data', [InventoryController::class, 'data'])->name('inventories.data');
        Route::resource('inventories', InventoryController::class)->except(['create', 'edit']);
    });
});