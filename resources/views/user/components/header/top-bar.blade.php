<div class="container header-top">
    <!-- Logo -->
    <div class="logo">
        <a href="{{ $homepageLogoLink ?? route('home') }}" class="logo-link">
            <div class="logo-row">
                <img 
                    src="{{ asset($logoMedia?->file_path ?? 'images/logo/logo.png') }}" 
                    alt="{{ $homepageTitle ?? config('app.name', 'Shopy') }}" 
                    class="logo-img"
                    onerror="this.onerror=null;this.src='{{ asset('images/default-logo.svg') }}';"
                >
            </div>
            @if(!empty($homepageSubtitle))
                <div class="logo-subtitle">{{ $homepageSubtitle }}</div>
            @endif
        </a>
    </div>

    <!-- Desktop Search Bar -->
    @include('user.components.header.search-bar')

    <!-- Right Icons & Actions -->
    <div class="right-icons">
        @auth
            <!-- Logged In (Auth): Show Wishlist, Notifications, Cart, Account -->
            <!-- 1. Wishlist -->
            <a href="{{ route('wishlist.index', ['mode' => session('active_shopping_mode', 'shopy')]) }}"
               class="icon-btn wishlist-wrapper {{ request()->routeIs('wishlist.*') ? 'active' : '' }}"
               id="wishlistBtn"
               title="Wishlist">
                <img src="{{ asset('images/navbar/wishlist.svg') }}" alt="Wishlist" class="nav-icon" />
                <span class="count-badge" id="wishlistCount" style="display:none;">0</span>
            </a>

            <!-- 2. Notifications -->
            <a href="{{ Route::has('notifications.index') ? route('notifications.index') : url('/notifications') }}"
               class="icon-btn notification-wrapper {{ request()->routeIs('notifications.*') ? 'active' : '' }}"
               id="notificationBtn"
               title="Notifications">
                <img src="{{ asset('images/navbar/notification.svg') }}" alt="Notifications" class="nav-icon" />
                <span class="count-badge" id="notificationCount" style="display:none;">0</span>
            </a>

            <!-- 3. Cart -->
            <a href="{{ url('/cart') }}"
               class="icon-btn cart-wrapper {{ request()->is('cart*') ? 'active' : '' }}"
               id="cartBtn"
               title="Cart">
                <img src="{{ asset('images/navbar/cart.svg') }}" alt="Cart" class="nav-icon" />
                <span class="count-badge" id="cartCount" style="display:none;">0</span>
            </a>

            <!-- 4. User Dropdown Menu -->
            @include('user.components.header.user-menu')
        @else
            <!-- Guest (Flipkart-Style): ONLY Show Login & Cart in Header -->
            <!-- 1. Login with Dropdown -->
            @include('user.components.header.user-menu')

            <!-- 2. Cart -->
            <a href="{{ url('/cart') }}"
               class="icon-btn cart-wrapper {{ request()->is('cart*') ? 'active' : '' }}"
               id="cartBtn"
               title="Cart">
                <img src="{{ asset('images/navbar/cart.svg') }}" alt="Cart" class="nav-icon" />
                <span class="cart-label hidden sm:inline text-sm font-semibold text-slate-700 ml-1">Cart</span>
                <span class="count-badge" id="cartCount" style="display:none;">0</span>
            </a>
        @endauth

        @if(!empty($isDarkMode))
            <!-- Theme Switcher (Available when Admin enables Dark Theme: User can switch both Dark & Light) -->
            <button type="button"
                    class="icon-btn theme-toggle-btn"
                    id="userThemeToggle"
                    title="Toggle Light / Dark Theme"
                    aria-label="Toggle Theme">
                <span id="userThemeIcon">
                    <i class="fas fa-moon"></i>
                </span>
            </button>
        @endif

        <!-- Mobile Hamburger Toggle -->
        <button class="mobile-toggle" id="mobileToggle" type="button" aria-label="Open navigation menu">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</div>
