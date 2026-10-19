<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Route;


// Jika user mengakses halaman utama (root /), arahkan otomatis
Route::get('/', function () {
    if (session()->has('user_id')) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});