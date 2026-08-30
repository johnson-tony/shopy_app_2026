<div class="fixed inset-0 z-50 hidden" id="mobileMenu">
    <div class="absolute inset-0 bg-black/40" data-close-mobile></div>
    <aside class="absolute left-0 top-0 h-full w-72 max-w-[85%] bg-white shadow-xl flex flex-col">
        <div class="flex items-center justify-between px-4 py-4 border-b border-slate-100">
            <span class="font-black text-lg" style="color:#2962ff;">Shopy 2026</span>
            <button type="button" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:bg-slate-100" data-close-mobile>
                <i class="fas fa-times"></i>
            </button>
        </div>

        @auth
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full font-bold text-sm text-white" style="background:#2962ff;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </span>
                <p class="mt-2 text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
            </div>
        @endauth

        <nav class="flex-1 overflow-y-auto py-2">
            <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('home') ? 'font-semibold' : '' }}">
                <i class="fas fa-house w-5 text-slate-400"></i> Home
            </a>
            @auth
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('dashboard') ? 'font-semibold' : '' }}">
                    <i class="fas fa-gauge-high w-5 text-slate-400"></i> Dashboard
                </a>
                <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('profile') ? 'font-semibold' : '' }}">
                    <i class="fas fa-user w-5 text-slate-400"></i> Profile
                </a>
                <a href="{{ route('user.addresses.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('user.addresses.*') ? 'font-semibold' : '' }}">
                    <i class="fas fa-location-dot w-5 text-slate-400"></i> My Addresses
                </a>
            @endauth
            @guest
                <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50">
                    <i class="fas fa-right-to-bracket w-5 text-slate-400"></i> Sign In
                </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50">
                        <i class="fas fa-user-plus w-5 text-slate-400"></i> Create Account
                    </a>
                @endif
            @endguest
        </nav>

        @auth
            <div class="border-t border-slate-100 p-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition">
                        <i class="fas fa-right-from-bracket"></i> Logout
                    </button>
                </form>
            </div>
        @endauth
    </aside>
</div>
