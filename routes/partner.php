<?php

use App\Http\Controllers\Partner\AuthController;
use App\Http\Controllers\Partner\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Partner Routes (Delivery Partner Web Panel)
|--------------------------------------------------------------------------
*/

// Partner Auth / Guest Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest:partner')->name('login.submit');

// Partner Protected Routes (All Authenticated Active Partners)
Route::middleware(['auth:partner'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});