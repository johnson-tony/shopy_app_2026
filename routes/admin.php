<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (Control Panel Area)
|--------------------------------------------------------------------------
*/

// Admin Auth / Guest Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest:admin')->name('login.submit');

// Admin Protected Routes
Route::middleware(['auth:admin', 'active:admin', 'role:admin,super-admin,order-manager'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Management & Impersonation
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/login-as', [UserController::class, 'loginAs'])
        ->middleware('role:admin,super-admin')
        ->name('users.login-as');

    // Category Management
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        Route::patch('/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('toggleStatus');
        Route::patch('/{category}/toggle-featured', [CategoryController::class, 'toggleFeatured'])->name('toggleFeatured');
    });
});
