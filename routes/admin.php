<?php

use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvitationController;
use App\Http\Controllers\Admin\ModeController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
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

// Admin Invitation Acceptance (Public Guest/Invited Flow)
Route::prefix('invitations')->name('invitations.')->group(function () {
    Route::get('/accept/{token}', [InvitationController::class, 'showAcceptForm'])->name('accept');
    Route::post('/accept/{token}', [InvitationController::class, 'processAccept'])->name('process');
});

// Admin Protected Routes (All Authenticated Active Admins)
Route::middleware(['auth:admin', 'active:admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view,view-dashboard')
        ->name('dashboard');

    // Customer User Management & Impersonation
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])
            ->middleware('permission:users.view,users.manage,manage-customers')
            ->name('index');
        Route::post('/{user}/login-as', [UserController::class, 'loginAs'])
            ->middleware('permission:users.impersonate,manage-customers')
            ->name('login-as');
    });

    // Sub-Administrator Staff Management
    Route::prefix('admins')->name('admins.')->group(function () {
        Route::get('/', [AdminManagementController::class, 'index'])
            ->middleware('permission:admins.view,manage-admins')
            ->name('index');
        Route::get('/create', [AdminManagementController::class, 'create'])
            ->middleware('permission:admins.create,manage-admins')
            ->name('create');
        Route::post('/', [AdminManagementController::class, 'store'])
            ->middleware('permission:admins.create,manage-admins')
            ->name('store');
        Route::get('/{admin}/edit', [AdminManagementController::class, 'edit'])
            ->middleware('permission:admins.edit,manage-admins')
            ->name('edit');
        Route::put('/{admin}', [AdminManagementController::class, 'update'])
            ->middleware('permission:admins.edit,manage-admins')
            ->name('update');
        Route::delete('/{admin}', [AdminManagementController::class, 'destroy'])
            ->middleware('permission:admins.delete,manage-admins')
            ->name('destroy');
        Route::post('/{admin}/resend-invitation', [AdminManagementController::class, 'resendInvitation'])
            ->middleware('permission:admins.create,manage-admins')
            ->name('resendInvitation');
        Route::patch('/{admin}/toggle-status', [AdminManagementController::class, 'toggleStatus'])
            ->middleware('permission:admins.edit,manage-admins')
            ->name('toggleStatus');
    });

    // Roles & Permissions Management
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])
            ->middleware('permission:roles.view,manage-roles')
            ->name('index');
        Route::get('/create', [RoleController::class, 'create'])
            ->middleware('permission:roles.create,manage-roles')
            ->name('create');
        Route::post('/', [RoleController::class, 'store'])
            ->middleware('permission:roles.create,manage-roles')
            ->name('store');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])
            ->middleware('permission:roles.edit,manage-roles')
            ->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])
            ->middleware('permission:roles.edit,manage-roles')
            ->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])
            ->middleware('permission:roles.delete,manage-roles')
            ->name('destroy');
    });

    // Category Management
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])
            ->middleware('permission:categories.view')
            ->name('index');
        Route::get('/create', [CategoryController::class, 'create'])
            ->middleware('permission:categories.create')
            ->name('create');
        Route::post('/', [CategoryController::class, 'store'])
            ->middleware(['permission:categories.create', 'mode.access'])
            ->name('store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])
            ->middleware(['permission:categories.edit', 'mode.access'])
            ->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])
            ->middleware(['permission:categories.edit', 'mode.access'])
            ->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])
            ->middleware(['permission:categories.delete', 'mode.access'])
            ->name('destroy');
        Route::patch('/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])
            ->middleware(['permission:categories.edit', 'mode.access'])
            ->name('toggleStatus');
        Route::patch('/{category}/toggle-featured', [CategoryController::class, 'toggleFeatured'])
            ->middleware(['permission:categories.edit', 'mode.access'])
            ->name('toggleFeatured');
    });

    // Product Management
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])
            ->middleware('permission:products.view,manage-products')
            ->name('index');
        Route::get('/create', [ProductController::class, 'create'])
            ->middleware('permission:products.create,manage-products')
            ->name('create');
        Route::post('/', [ProductController::class, 'store'])
            ->middleware(['permission:products.create,manage-products', 'mode.access'])
            ->name('store');
        Route::get('/categories-by-mode', [ProductController::class, 'getCategoriesByMode'])
            ->name('categoriesByMode');
        Route::get('/{product}', [ProductController::class, 'show'])
            ->middleware(['permission:products.view,manage-products', 'mode.access'])
            ->name('show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])
            ->middleware(['permission:products.edit,manage-products', 'mode.access'])
            ->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])
            ->middleware(['permission:products.edit,manage-products', 'mode.access'])
            ->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])
            ->middleware(['permission:products.delete,manage-products', 'mode.access'])
            ->name('destroy');
        Route::patch('/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
            ->middleware(['permission:products.edit,manage-products', 'mode.access'])
            ->name('toggleStatus');
        Route::patch('/{product}/toggle-featured', [ProductController::class, 'toggleFeatured'])
            ->middleware(['permission:products.edit,manage-products', 'mode.access'])
            ->name('toggleFeatured');
    });

    // Restaurant Management (Food Delivery)
    Route::prefix('restaurants')->name('restaurants.')->group(function () {
        Route::get('/', [RestaurantController::class, 'index'])
            ->middleware('permission:restaurants.view')
            ->name('index');
        Route::get('/create', [RestaurantController::class, 'create'])
            ->middleware('permission:restaurants.create')
            ->name('create');
        Route::post('/', [RestaurantController::class, 'store'])
            ->middleware('permission:restaurants.create')
            ->name('store');
        Route::get('/{restaurant}/edit', [RestaurantController::class, 'edit'])
            ->middleware('permission:restaurants.edit')
            ->name('edit');
        Route::put('/{restaurant}', [RestaurantController::class, 'update'])
            ->middleware('permission:restaurants.edit')
            ->name('update');
        Route::delete('/{restaurant}', [RestaurantController::class, 'destroy'])
            ->middleware('permission:restaurants.delete')
            ->name('destroy');
        Route::patch('/{restaurant}/toggle-status', [RestaurantController::class, 'toggleStatus'])
            ->middleware('permission:restaurants.edit')
            ->name('toggleStatus');
    });

    // Shopping Mode Management
    Route::prefix('modes')->name('modes.')->group(function () {
        Route::get('/', [ModeController::class, 'index'])
            ->middleware('permission:modes.view')
            ->name('index');
        Route::get('/create', [ModeController::class, 'create'])
            ->middleware('permission:modes.create')
            ->name('create');
        Route::post('/', [ModeController::class, 'store'])
            ->middleware('permission:modes.create')
            ->name('store');
        Route::get('/{mode}/edit', [ModeController::class, 'edit'])
            ->middleware('permission:modes.edit')
            ->name('edit');
        Route::put('/{mode}', [ModeController::class, 'update'])
            ->middleware('permission:modes.edit')
            ->name('update');
        Route::delete('/{mode}', [ModeController::class, 'destroy'])
            ->middleware('permission:modes.delete')
            ->name('destroy');
        Route::patch('/{mode}/toggle-status', [ModeController::class, 'toggleStatus'])
            ->middleware('permission:modes.edit')
            ->name('toggleStatus');
    });

    // Coupons & Promotion Management
    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/', [CouponController::class, 'index'])
            ->middleware('permission:coupons.view')
            ->name('index');
        Route::get('/create', [CouponController::class, 'create'])
            ->middleware('permission:coupons.create')
            ->name('create');
        Route::post('/', [CouponController::class, 'store'])
            ->middleware('permission:coupons.create')
            ->name('store');
        Route::get('/{coupon}', [CouponController::class, 'show'])
            ->middleware('permission:coupons.view')
            ->name('show');
        Route::get('/{coupon}/edit', [CouponController::class, 'edit'])
            ->middleware('permission:coupons.edit')
            ->name('edit');
        Route::put('/{coupon}', [CouponController::class, 'update'])
            ->middleware('permission:coupons.edit')
            ->name('update');
        Route::delete('/{coupon}', [CouponController::class, 'destroy'])
            ->middleware('permission:coupons.delete')
            ->name('destroy');
        Route::patch('/{coupon}/toggle-status', [CouponController::class, 'toggleStatus'])
            ->middleware('permission:coupons.edit')
            ->name('toggleStatus');
    });

    // System Settings & Theme Management
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])
            ->middleware('permission:settings.view')
            ->name('index');
        Route::put('/', [SettingController::class, 'update'])
            ->middleware('permission:settings.edit')
            ->name('update');
    });
});
