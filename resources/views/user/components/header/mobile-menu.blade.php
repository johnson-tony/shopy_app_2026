<!-- Backdrop Overlay for Mobile Drawer -->
<div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

<!-- Mobile Sidebar Drawer -->
<nav class="mobile-menu" id="mobileMenu" aria-label="Mobile Navigation">
    <div class="mobile-menu-header">
        <span class="mobile-menu-brand">{{ $homepageTitle ?? config('app.name', 'Shopy') }}</span>
        <button id="mobileMenuClose" class="mobile-menu-close" type="button" aria-label="Close menu">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- User Info (Mobile) -->
    @auth
        <div class="mobile-user-info">
            @if(Auth::user()->avatar)
                @if(Str::startsWith(Auth::user()->avatar, ['http://', 'https://']))
                    <img src="{{ Auth::user()->avatar }}" class="mobile-avatar-img" alt="{{ Auth::user()->name }}">
                @else
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="mobile-avatar-img" alt="{{ Auth::user()->name }}">
                @endif
            @else
                <div class="mobile-avatar-placeholder">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
            @endif
            <div style="min-width:0; overflow:hidden;">
                <div class="mobile-user-name" style="text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">{{ Auth::user()->name }}</div>
                <div style="font-size:0.78rem; color:var(--text-muted); text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">{{ Auth::user()->email }}</div>
            </div>
        </div>
    @endauth

    <ul>
        <li>
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                <span><i class="fas fa-house mr-2 text-slate-400"></i> Home</span>
            </a>
        </li>
        <li>
            <a href="{{ route('home', ['mode' => 'shopy']) }}" class="{{ request()->query('mode') === 'shopy' ? 'active' : '' }}">
                <span><i class="fas fa-bag-shopping mr-2 text-slate-400"></i> Shop</span>
            </a>
        </li>

        @if(!empty($navcategories) && count($navcategories) > 0)
            <li class="dropdown" id="mobileCategoriesDropdown">
                <a href="#">
                    <span><i class="fas fa-layer-group mr-2 text-slate-400"></i> Categories</span>
                </a>
                <ul class="dropdown-menu">
                    @foreach($navcategories as $category)
                        <li>
                            <a href="{{ Route::has('shop.category.show') ? route('shop.category.show', $category->slug) : url('/category/' . $category->slug) }}">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endif

        <li>
            <a href="{{ url('/cart') }}" class="{{ request()->is('cart*') ? 'active' : '' }}">
                <span><i class="fas fa-shopping-cart mr-2 text-slate-400"></i> Cart</span>
            </a>
        </li>
        @auth
            <li>
                <a href="{{ route('wishlist.index', ['mode' => session('active_shopping_mode', 'shopy')]) }}" class="{{ request()->is('wishlist*') ? 'active' : '' }}">
                    <span><i class="fas fa-heart mr-2 text-slate-400"></i> Wishlist</span>
                </a>
            </li>
            <li>
                <a href="{{ Route::has('orders.history') ? route('orders.history') : url('/orders') }}" class="{{ request()->is('orders*') ? 'active' : '' }}">
                    <span><i class="fas fa-box mr-2 text-slate-400"></i> Orders</span>
                </a>
            </li>
        @endauth
        <li>
            <a href="{{ url('/coupons') }}" class="{{ request()->is('coupons*') ? 'active' : '' }}">
                <span><i class="fas fa-ticket mr-2 text-slate-400"></i> Coupons</span>
            </a>
        </li>
        <li>
            <a href="{{ url('/contact') }}" class="{{ request()->is('contact*') ? 'active' : '' }}">
                <span><i class="fas fa-envelope mr-2 text-slate-400"></i> Contact</span>
            </a>
        </li>

        @guest
            <li style="border-top: 1px solid var(--hover-bg); margin-top: 0.5rem; padding-top: 0.5rem;">
                <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">
                    <span><i class="fas fa-right-to-bracket mr-2 text-slate-400"></i> Sign In</span>
                </a>
            </li>
            @if (Route::has('register'))
                <li>
                    <a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'active' : '' }}">
                        <span><i class="fas fa-user-plus mr-2 text-slate-400"></i> Register</span>
                    </a>
                </li>
            @endif
        @else
            <li style="border-top: 1px solid var(--hover-bg); margin-top: 0.5rem; padding-top: 0.5rem;">
                <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span><i class="fas fa-gauge-high mr-2 text-slate-400"></i> Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ Route::has('profile') ? route('profile') : url('/profile') }}" class="{{ request()->routeIs('profile') ? 'active' : '' }}">
                    <span><i class="fas fa-user mr-2 text-slate-400"></i> Profile</span>
                </a>
            </li>
            @if (Route::has('user.addresses.index'))
                <li>
                    <a href="{{ route('user.addresses.index') }}" class="{{ request()->routeIs('user.addresses.*') ? 'active' : '' }}">
                        <span><i class="fas fa-location-dot mr-2 text-slate-400"></i> My Addresses</span>
                    </a>
                </li>
            @endif
            <li>
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();"
                   class="mobile-logout">
                    <span>Logout</span> <i class="fas fa-sign-out-alt"></i>
                </a>
                <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" style="display:none;">
                    @csrf
                </form>
            </li>
        @endguest

        @if(!empty($isDarkMode))
            <li style="border-top: 1px solid var(--border-light); margin-top: 0.5rem; padding-top: 0.5rem;">
                <button type="button" class="theme-toggle-btn-mobile w-full flex items-center justify-between px-5 py-3 text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-indigo-600 bg-transparent border-none cursor-pointer">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-circle-half-stroke text-slate-400"></i>
                        <span>Theme Mode</span>
                    </span>
                    <span class="mobileThemeLabel text-xs px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold">Light</span>
                </button>
            </li>
        @endif
    </ul>
</nav>
