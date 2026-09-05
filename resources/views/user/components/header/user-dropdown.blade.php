<div class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-slate-100 py-2 hidden" id="userMenuDropdown">
    @auth
        <div class="px-4 py-3 border-b border-slate-100">
            <p class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
            <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
        </div>
        <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition">
            <i class="fas fa-gauge-high w-5 text-slate-400"></i> Dashboard
        </a>
        <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition">
            <i class="fas fa-box w-5 text-slate-400"></i> My Orders
        </a>
        <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition">
            <i class="fas fa-user w-5 text-slate-400"></i> Profile
        </a>
        <a href="{{ route('user.addresses.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition">
            <i class="fas fa-location-dot w-5 text-slate-400"></i> My Addresses
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 transition">
                <i class="fas fa-right-from-bracket w-5"></i> Logout
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition">
            <i class="fas fa-right-to-bracket w-5 text-slate-400"></i> Sign In
        </a>
        @if (Route::has('register'))
            <a href="{{ route('register') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition">
                <i class="fas fa-user-plus w-5 text-slate-400"></i> Create Account
            </a>
        @endif
    @endauth
</div>
