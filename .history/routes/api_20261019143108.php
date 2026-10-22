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

// ---- Route Use Generator ----
Route::controller(UserController::class)->group(function() {
    Route::get('users/{id?}', 'get')->name('get.users');
    Route::post('users', 'post')->name('post.users');
    Route::patch('users/{id}', 'patch')->name('patch.users');
    Route::put('users/{id}', 'put')->name('put.users');
    Route::delete('users/{id}', 'delete')->name('delete.users');
    Route::post('users_datatables', 'datatables')->name('datatable.users');
    Route::patch('users/{id}/approve', 'approve')->name('approve.users');
});
Route::controller(PasswordResetTokenController::class)->group(function() {
    Route::get('password_reset_tokens/{id?}', 'get')->name('get.password_reset_tokens');
    Route::post('password_reset_tokens', 'post')->name('post.password_reset_tokens');
    Route::patch('password_reset_tokens/{id}', 'patch')->name('patch.password_reset_tokens');
    Route::put('password_reset_tokens/{id}', 'put')->name('put.password_reset_tokens');
    Route::delete('password_reset_tokens/{id}', 'delete')->name('delete.password_reset_tokens');
    Route::post('password_reset_tokens_datatables', 'datatables')->name('datatable.password_reset_tokens');
    Route::patch('password_reset_tokens/{id}/approve', 'approve')->name('approve.password_reset_tokens');
});
Route::controller(SessionController::class)->group(function() {
    Route::get('sessions/{id?}', 'get')->name('get.sessions');
    Route::post('sessions', 'post')->name('post.sessions');
    Route::patch('sessions/{id}', 'patch')->name('patch.sessions');
    Route::put('sessions/{id}', 'put')->name('put.sessions');
    Route::delete('sessions/{id}', 'delete')->name('delete.sessions');
    Route::post('sessions_datatables', 'datatables')->name('datatable.sessions');
    Route::patch('sessions/{id}/approve', 'approve')->name('approve.sessions');
});
Route::controller(CacheController::class)->group(function() {
    Route::get('cache/{id?}', 'get')->name('get.cache');
    Route::post('cache', 'post')->name('post.cache');
    Route::patch('cache/{id}', 'patch')->name('patch.cache');
    Route::put('cache/{id}', 'put')->name('put.cache');
    Route::delete('cache/{id}', 'delete')->name('delete.cache');
    Route::post('cache_datatables', 'datatables')->name('datatable.cache');
    Route::patch('cache/{id}/approve', 'approve')->name('approve.cache');
});
Route::controller(CacheLockController::class)->group(function() {
    Route::get('cache_locks/{id?}', 'get')->name('get.cache_locks');
    Route::post('cache_locks', 'post')->name('post.cache_locks');
    Route::patch('cache_locks/{id}', 'patch')->name('patch.cache_locks');
    Route::put('cache_locks/{id}', 'put')->name('put.cache_locks');
    Route::delete('cache_locks/{id}', 'delete')->name('delete.cache_locks');
    Route::post('cache_locks_datatables', 'datatables')->name('datatable.cache_locks');
    Route::patch('cache_locks/{id}/approve', 'approve')->name('approve.cache_locks');
});
Route::controller(JobController::class)->group(function() {
    Route::get('jobs/{id?}', 'get')->name('get.jobs');
    Route::post('jobs', 'post')->name('post.jobs');
    Route::patch('jobs/{id}', 'patch')->name('patch.jobs');
    Route::put('jobs/{id}', 'put')->name('put.jobs');
    Route::delete('jobs/{id}', 'delete')->name('delete.jobs');
    Route::post('jobs_datatables', 'datatables')->name('datatable.jobs');
    Route::patch('jobs/{id}/approve', 'approve')->name('approve.jobs');
});
Route::controller(JobBatchController::class)->group(function() {
    Route::get('job_batches/{id?}', 'get')->name('get.job_batches');
    Route::post('job_batches', 'post')->name('post.job_batches');
    Route::patch('job_batches/{id}', 'patch')->name('patch.job_batches');
    Route::put('job_batches/{id}', 'put')->name('put.job_batches');
    Route::delete('job_batches/{id}', 'delete')->name('delete.job_batches');
    Route::post('job_batches_datatables', 'datatables')->name('datatable.job_batches');
    Route::patch('job_batches/{id}/approve', 'approve')->name('approve.job_batches');
});
Route::controller(FailedJobController::class)->group(function() {
    Route::get('failed_jobs/{id?}', 'get')->name('get.failed_jobs');
    Route::post('failed_jobs', 'post')->name('post.failed_jobs');
    Route::patch('failed_jobs/{id}', 'patch')->name('patch.failed_jobs');
    Route::put('failed_jobs/{id}', 'put')->name('put.failed_jobs');
    Route::delete('failed_jobs/{id}', 'delete')->name('delete.failed_jobs');
    Route::post('failed_jobs_datatables', 'datatables')->name('datatable.failed_jobs');
    Route::patch('failed_jobs/{id}/approve', 'approve')->name('approve.failed_jobs');
});
});
// ---- Route Controller Generator ----