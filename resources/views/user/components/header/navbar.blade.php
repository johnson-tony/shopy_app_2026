<div>
    <div class="container header-bottom">
        <nav class="nav-links" aria-label="Main Navigation">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                Home
            </a>

            <div class="nav-link dropdown {{ request()->is('category*') ? 'active' : '' }}" id="categoriesDropdown">
                <span>Categories</span>
                <span class="dropdown-icon" id="categoriesDropdownIcon">▾</span>

                <div class="dropdown-menu mega-dropdown">
                    @include('user.components.header.categories-mega-menu')
                </div>
            </div>

            <a href="{{ route('home', ['mode' => 'shopy']) }}"
               class="nav-link {{ request()->query('mode') === 'shopy' ? 'active' : '' }}">
                Shop
            </a>

            @auth
                <a href="{{ url('/cart') }}"
                   class="nav-link {{ request()->is('cart*') ? 'active' : '' }}">
                    My Cart
                </a>

                <a href="{{ Route::has('orders.history') ? route('orders.history') : url('/orders') }}"
                   class="nav-link {{ request()->is('orders*') ? 'active' : '' }}">
                    Orders
                </a>
            @endauth

            <a href="{{ url('/coupons') }}"
               class="nav-link {{ request()->is('coupons*') ? 'active' : '' }}">
                Coupons
            </a>

            <a href="{{ url('/contact') }}"
               class="nav-link {{ request()->is('contact*') ? 'active' : '' }}">
                Contact
            </a>
        </nav>
    </div>
</div>
