<?php

use App\Http\Controllers\Partner\AuthController;
use App\Http\Controllers\Partner\DashboardController;
use App\Http\Controllers\Partner\PartnerLocationController;
use App\Http\Controllers\Partner\PartnerOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Partner Routes (Delivery Partner Web Panel)
|--------------------------------------------------------------------------
*/

// Partner Auth / Guest Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest:partner')->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest:partner')->name('register.submit');

// Partner Protected Routes (All Authenticated Active Partners)
Route::middleware(['auth:partner'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/toggle-availability', [DashboardController::class, 'toggleAvailability'])->name('toggle_availability');
    Route::post('/location/update', [PartnerLocationController::class, 'update'])->name('location.update');

    // Order Fulfillment & Proof of Delivery
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/{order}', [PartnerOrderController::class, 'show'])->name('show');
        Route::post('/{order}/status', [PartnerOrderController::class, 'updateStatus'])->name('update_status');
        Route::post('/{order}/deliver', [PartnerOrderController::class, 'deliver'])->name('deliver');
        Route::post('/{order}/pickup-return', [PartnerOrderController::class, 'pickupReturn'])->name('pickup_return');
    });
});