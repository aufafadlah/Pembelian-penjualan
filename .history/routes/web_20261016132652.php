<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Jika user mengakses halaman utama (root /), arahkan otomatis
Route::get('/', function () {
    if (session()->has('user_id')) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Route Login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Route Dashboard (Dilindungi session sederhana)
Route::get('/dashboard', function () {
    if (!session()->has('user_id')) {
        return redirect()->route('login');
    }
    return view('dashboard');
})->name('dashboard');