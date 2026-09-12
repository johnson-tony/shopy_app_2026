<div class="user-menu" id="userMenu">
    <button class="user-btn" id="userBtn" type="button" aria-haspopup="true" aria-expanded="false">
        @guest
            <div class="guest-account">
                <div class="guest-icon-bg">
                    <img src="{{ asset('images/navbar/profile.svg') }}" alt="Profile" class="profile-icon" />
                </div>
                <span class="account-text">Login</span>
                <i class="fas fa-chevron-down text-[10px] text-slate-400 ml-0.5"></i>
            </div>
        @else
            @if(Auth::user()->avatar)
                @if(Str::startsWith(Auth::user()->avatar, ['http://', 'https://']))
                    <img src="{{ Auth::user()->avatar }}" class="avatar-img" alt="{{ Auth::user()->name }}" />
                @else
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="avatar-img" alt="{{ Auth::user()->name }}" />
                @endif
            @else
                <div class="avatar-placeholder">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
            @endif
            <span class="account-text">{{ Str::limit(Auth::user()->name, 12) }}</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 ml-0.5"></i>
        @endguest
    </button>

    <div class="dropdown" id="userDropdown">
        @guest
            <!-- Guest: Only Sign In and Register (No Profile, No Orders, No Wishlist) -->
            <a href="{{ route('login') }}">
                <i class="fas fa-right-to-bracket mr-2 text-slate-400"></i> Sign In / Login
            </a>
            @if (Route::has('register'))
                <a href="{{ route('register') }}">
                    <i class="fas fa-user-plus mr-2 text-slate-400"></i> New Customer? Sign Up
                </a>
            @endif
        @else
            <!-- Authenticated: Shows Profile, Orders, Wishlist, Addresses, Logout -->
            <div style="padding: 0.65rem 1rem; border-bottom: 1px solid #f0f0f0;">
                <div style="font-weight:600; font-size: 0.88rem; color: var(--text-default);">{{ Auth::user()->name }}</div>
                <div style="font-size: 0.78rem; color: var(--text-muted); text-overflow: ellipsis; overflow: hidden;">{{ Auth::user()->email }}</div>
            </div>
            <a href="{{ Route::has('profile') ? route('profile') : url('/profile') }}">
                <i class="fas fa-circle-user mr-2 text-slate-400"></i> My Profile
            </a>
            <a href="{{ Route::has('orders.history') ? route('orders.history') : url('/orders') }}">
                <i class="fas fa-box mr-2 text-slate-400"></i> My Orders
            </a>
            <a href="{{ route('wishlist.index', ['mode' => session('active_shopping_mode', 'shopy')]) }}">
                <i class="fas fa-heart mr-2 text-slate-400"></i> Wishlist
            </a>
            @if (Route::has('user.addresses.index'))
                <a href="{{ route('user.addresses.index') }}">
                    <i class="fas fa-location-dot mr-2 text-slate-400"></i> My Addresses
                </a>
            @endif
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();"
               style="color: #e11d48;">
                <i class="fas fa-sign-out-alt mr-2 text-rose-500"></i> Logout
            </a>
            <form id="logout-form-header" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        @endguest

        @if(!empty($isDarkMode))
            <div style="border-top: 1px solid var(--border-light); padding: 0.25rem 0;">
                <button type="button" class="theme-toggle-btn-dropdown" style="display:flex; align-items:center; justify-content:space-between; width:100%; padding:0.65rem 1rem; color:var(--text-default); font-size:0.88rem; background:transparent; border:none; cursor:pointer;">
                    <span><i class="fas fa-circle-half-stroke mr-2 text-slate-400"></i> Theme Mode</span>
                    <span class="dropdownThemeLabel" style="font-size:0.75rem; padding:2px 8px; border-radius:12px; background:var(--brand-blue-light); color:var(--brand-blue); font-weight:700;">Light</span>
                </button>
            </div>
        @endif
    </div>
</div>
