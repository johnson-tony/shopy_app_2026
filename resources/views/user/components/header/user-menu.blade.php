<div class="user-menu" id="userMenu">
    <button class="user-btn" id="userBtn" type="button" aria-haspopup="true" aria-expanded="false">
        @guest
            <div class="guest-account">
                <div class="guest-icon-bg">
                    <img src="{{ asset('images/navbar/profile.svg') }}" alt="Profile" class="profile-icon" />
                </div>
                <span class="account-text">Login</span>
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
            <span class="account-text">My Account</span>
        @endguest
    </button>

    <div class="dropdown" id="userDropdown">
        @guest
            <a href="{{ route('login') }}">
                <i class="fas fa-right-to-bracket mr-2 text-slate-400"></i> Sign In
            </a>
            @if (Route::has('register'))
                <a href="{{ route('register') }}">
                    <i class="fas fa-user-plus mr-2 text-slate-400"></i> Register
                </a>
            @endif
        @else
            <div style="padding: 0.65rem 1rem; border-bottom: 1px solid #f0f0f0;">
                <div style="font-weight:600; font-size: 0.88rem; color: var(--text-default);">{{ Auth::user()->name }}</div>
                <div style="font-size: 0.78rem; color: var(--text-muted); text-overflow: ellipsis; overflow: hidden;">{{ Auth::user()->email }}</div>
            </div>
            <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}">
                <i class="fas fa-gauge-high mr-2 text-slate-400"></i> Dashboard
            </a>
            <a href="{{ Route::has('profile') ? route('profile') : url('/profile') }}">
                <i class="fas fa-user mr-2 text-slate-400"></i> Profile
            </a>
            @if (Route::has('user.addresses.index'))
                <a href="{{ route('user.addresses.index') }}">
                    <i class="fas fa-location-dot mr-2 text-slate-400"></i> My Addresses
                </a>
            @endif
            @if (Route::has('orders.history'))
                <a href="{{ route('orders.history') }}">
                    <i class="fas fa-box mr-2 text-slate-400"></i> My Orders
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
    </div>
</div>
