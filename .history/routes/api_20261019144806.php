<?php
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\PasswordResetTokenController;
use App\Http\Controllers\API\SessionController;
use App\Http\Controllers\API\CacheController;
use App\Http\Controllers\API\CacheLockController;
use App\Http\Controllers\API\JobController;
use App\Http\Controllers\API\JobBatchController;
use App\Http\Controllers\API\FailedJobController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\InventoryController;
use App\Http\Controllers\API\PurchaseController;
use App\Http\Controllers\API\SaleController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
// Inventories Routes
Route::controller(InventoryController::class)->group(function() {
    Route::get('inventories/{id?}', 'get')->name('get.inventories');
    Route::post('inventories/import', 'import')->name('import.inventories');
    Route::post('inventories', 'post')->name('post.inventories');
    Route::patch('inventories/{id}', 'patch')->name('patch.inventories');
    Route::put('inventories/{id}', 'put')->name('put.inventories');
    Route::delete('inventories/{id}', 'delete')->name('delete.inventories');
    Route::post('inventories_datatables', 'datatables')->name('datatable.inventories');
    Route::patch('inventories/{id}/approve', 'approve')->name('approve.inventories');
});

    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

// Purchases Routes
Route::controller(PurchaseController::class)->group(function() {
    Route::get('purchases/download-per-day', 'downloadPerDay')->name('download-per-day.purchases');
    Route::get('purchases/{id?}', 'get')->name('get.purchases');
    Route::post('purchases', 'post')->name('post.purchases');
    Route::patch('purchases/{id}', 'patch')->name('patch.purchases');
    Route::put('purchases/{id}', 'put')->name('put.purchases');
    Route::delete('purchases/{id}', 'delete')->name('delete.purchases');
    Route::post('purchases_datatables', 'datatables')->name('datatable.purchases');
    Route::patch('purchases/{id}/approve', 'approve')->name('approve.purchases');
});

// Sales Routes
Route::controller(SaleController::class)->group(function() {
    Route::get('sales/download-per-day', 'downloadPerDay')->name('download-per-day.sales');
    Route::get('sales/{id?}', 'get')->name('get.sales');
    Route::post('sales', 'post')->name('post.sales');
    Route::patch('sales/{id}', 'patch')->name('patch.sales');
    Route::put('sales/{id}', 'put')->name('put.sales');
    Route::delete('sales/{id}', 'delete')->name('delete.sales');
    Route::post('sales_datatables', 'datatables')->name('datatable.sales');
    Route::patch('sales/{id}/approve', 'approve')->name('approve.sales');
});



