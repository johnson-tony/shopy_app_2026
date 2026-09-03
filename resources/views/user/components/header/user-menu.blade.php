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
            <!-- Flipkart-Style New Customer Sign Up Row -->
            <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-b border-slate-100 text-xs">
                <span class="text-slate-600 font-medium">New customer?</span>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="font-bold text-indigo-600 hover:text-indigo-700 !p-0 !w-auto !inline" style="color:var(--brand-blue); padding:0 !important; width:auto !important; display:inline !important;">Sign Up</a>
                @endif
            </div>

            <a href="{{ route('login') }}">
                <i class="fas fa-circle-user mr-2 text-slate-400"></i> My Profile
            </a>
            <a href="{{ Route::has('orders.history') ? route('orders.history') : url('/orders') }}">
                <i class="fas fa-box mr-2 text-slate-400"></i> Orders
            </a>
            <a href="{{ Route::has('wishlist.index') ? route('wishlist.index') : url('/wishlist') }}">
                <i class="fas fa-heart mr-2 text-slate-400"></i> Wishlist
            </a>
            <a href="{{ Route::has('coupons') ? route('coupons') : url('/coupons') }}">
                <i class="fas fa-ticket mr-2 text-slate-400"></i> Rewards &amp; Coupons
            </a>
            <a href="{{ Route::has('faqs') ? route('faqs') : url('/faqs') }}">
                <i class="fas fa-headset mr-2 text-slate-400"></i> 24x7 Customer Care
            </a>
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
