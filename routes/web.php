<?php

use App\Http\Controllers\User\AddressController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\CouponController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\ForgotPasswordController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\ImpersonationController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\ProductDetailController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\ReviewController;
use App\Http\Controllers\User\WishlistController;
use App\Models\Cart;

/*
|--------------------------------------------------------------------------
| Web Routes (Customer / User Area)
|--------------------------------------------------------------------------
*/

// Public Storefront Routes (Accessible by Guests & Authenticated Users)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/category-products/ajax/{slug?}', [HomeController::class, 'ajaxCategoryProducts'])->name('category.products.ajax');
Route::get('/coupons', [CouponController::class, 'index'])->name('coupons.index');
Route::get('/product/{slug}', [ProductDetailController::class, 'show'])->name('product.show');

// Guest Authentication Routes (Login page opens only when user clicks Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

    // Forgot & Reset Password Routes (Multi-device Email OTP: Step 1 Email -> Step 2 OTP -> Step 3 Password)
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetOtp'])->name('password.email');

    // Step 2: Verify OTP
    Route::get('/forgot-password/verify', [ForgotPasswordController::class, 'showVerifyOtpForm'])->name('password.otp');
    Route::post('/forgot-password/verify', [ForgotPasswordController::class, 'verifyResetOtp'])->name('password.verify_otp');

    // Step 3: Set New Password
    Route::get('/reset-password/new', [ForgotPasswordController::class, 'showNewPasswordForm'])->name('password.reset_new');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::get('/reset-password/link/{token}', [ForgotPasswordController::class, 'showResetFormWithToken'])->name('password.reset_link');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
    Route::post('/reset-password/resend', [ForgotPasswordController::class, 'resendResetOtp'])->name('password.resend');
});

// Email OTP Verification Routes (Cross-device accessible)
Route::get('/verify-otp', [AuthController::class, 'showVerifyOtpForm'])->name('verification.notice');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('verification.verify');
Route::get('/verify-otp/link/{token}', [AuthController::class, 'verifyOtpLink'])->name('verification.verify_link');
Route::post('/verify-otp/resend', [AuthController::class, 'resendOtp'])->name('verification.resend');

// Signed impersonation entry point (opened in a NEW tab, no session yet)
Route::get('/impersonate/{user}', [ImpersonationController::class, 'login'])
    ->middleware('signed')
    ->name('impersonate.login');

// Authenticated Customer Routes
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Stop impersonation and return to the admin panel
    Route::post('/stop-impersonation', [ImpersonationController::class, 'stop'])->name('stop.impersonation');

    // Address Management Routes
    Route::prefix('addresses')->name('user.addresses.')->group(function () {
        Route::get('/', [AddressController::class, 'index'])->name('index');
        Route::get('/create', [AddressController::class, 'create'])->name('create');
        Route::post('/', [AddressController::class, 'store'])->name('store');
        Route::get('/{address}/edit', [AddressController::class, 'edit'])->name('edit');
        Route::put('/{address}', [AddressController::class, 'update'])->name('update');
        Route::delete('/{address}', [AddressController::class, 'destroy'])->name('destroy');
        Route::patch('/{address}/default', [AddressController::class, 'setDefault'])->name('default');
        Route::post('/reverse-geocode', [AddressController::class, 'reverseGeocode'])->name('reverseGeocode');
    });

    // Wishlist Management Routes (Customer Area)
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::delete('/{product}', [WishlistController::class, 'destroy'])->name('destroy');
        Route::post('/clear', [WishlistController::class, 'clear'])->name('clear');
    });

    // Checkout Routes
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])->name('checkout.place_order');

    // Customer Orders Routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/success/{order_number}', [OrderController::class, 'success'])->name('success');
        Route::get('/{order_number}', [OrderController::class, 'show'])->name('show');
        Route::get('/{order_number}/live-location', [OrderController::class, 'liveLocation'])->name('live_location');
        Route::post('/{order_number}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::post('/{order_number}/return', [OrderController::class, 'requestReturn'])->name('return');
    });

    // Customer Product Review Submission
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

// Wishlist AJAX Toggle (accessible with unauthenticated handling)
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

// Cart Routes (Accessible by both guests and authenticated customers)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/update', [CartController::class, 'updateQuantity'])->name('update');
    Route::delete('/item/{product}', [CartController::class, 'remove'])->name('remove');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
    Route::post('/apply-coupon', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::post('/remove-coupon', [CartController::class, 'removeCoupon'])->name('coupon.remove');
});

// Footer & Navbar utility endpoints
Route::get('/footer/fetch-counts', function (\Illuminate\Http\Request $request) {
    $activeMode = $request->query('mode') ?? session('active_shopping_mode', 'shopy');
    $user = Auth::guard('web')->user();

    $wishlistCount = $user ? $user->wishlistCount($activeMode) : 0;
    $totalWishlist = $user ? $user->wishlistCount() : 0;

    $cartCount = 0;
    if ($user) {
        $cartCount = $user->cartCount($activeMode);
    } else {
        $sessionId = $request->session()->getId();
        $guestCart = Cart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->whereHas('mode', fn ($q) => $q->where('slug', $activeMode))
            ->first();
        $cartCount = $guestCart ? $guestCart->totalQuantity() : 0;
    }

    return response()->json([
        'wishlist_count'     => $wishlistCount,
        'total_wishlist'     => $totalWishlist,
        'active_mode'        => $activeMode,
        'cart_count'         => $cartCount,
        'notification_count' => 0,
    ]);
})->name('footer.fetch-counts');

Route::post('/newsletter/subscribe', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'email' => 'required|email',
    ]);

    return response()->json([
        'message' => 'Thank you for subscribing to our newsletter!',
    ]);
})->name('newsletter.subscribe');
