<!-- Mobile App Bottom Navigation Bar (Thumb-Friendly E-Commerce UX) -->
<nav class="mobile-bottom-nav" id="mobileBottomNav" aria-label="Mobile Bottom Navigation">
    <div class="mobile-bottom-nav-inner">
        <!-- 1. Home -->
        <a href="{{ route('home') }}" class="mobile-nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="fas fa-house"></i>
            <span>Home</span>
        </a>

        <!-- 2. Categories Drawer Trigger -->
        <button type="button" class="mobile-nav-item" id="mobileBottomCategoriesBtn" aria-label="Browse categories">
            <i class="fas fa-layer-group"></i>
            <span>Categories</span>
        </button>

        <!-- 3. Shop -->
        <a href="{{ route('home', ['mode' => 'shopy']) }}" class="mobile-nav-item {{ request()->query('mode') === 'shopy' ? 'active' : '' }}">
            <i class="fas fa-bag-shopping"></i>
            <span>Shop</span>
        </a>

        <!-- 4. Cart with Badge -->
        <a href="{{ url('/cart') }}" class="mobile-nav-item {{ request()->is('cart*') ? 'active' : '' }}">
            <i class="fas fa-cart-shopping"></i>
            <span class="mobile-nav-badge" id="mobileBottomCartCount" style="display:none;">0</span>
            <span>Cart</span>
        </a>

        <!-- 5. Profile / Account -->
        @auth
            <a href="{{ Route::has('profile') ? route('profile') : url('/profile') }}" class="mobile-nav-item {{ request()->routeIs('profile') || request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-circle-user"></i>
                <span>Account</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="mobile-nav-item {{ request()->routeIs('login') ? 'active' : '' }}">
                <i class="fas fa-circle-user"></i>
                <span>Login</span>
            </a>
        @endauth
    </div>
</nav>
