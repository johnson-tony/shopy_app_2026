<nav class="hidden lg:block border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-center gap-8 py-3">
        <a href="{{ route('home') }}"
           class="text-sm font-medium {{ request()->routeIs('home') ? 'font-semibold' : 'text-slate-600 hover:text-slate-900' }} transition"
           style="{{ request()->routeIs('home') ? 'color:#2962ff;' : '' }}">
            Home
        </a>
        @auth
            <a href="{{ route('dashboard') }}"
               class="text-sm font-medium {{ request()->routeIs('dashboard') ? 'font-semibold' : 'text-slate-600 hover:text-slate-900' }} transition"
               style="{{ request()->routeIs('dashboard') ? 'color:#2962ff;' : '' }}">
                Dashboard
            </a>
            <a href="{{ route('user.addresses.index') }}"
               class="text-sm font-medium {{ request()->routeIs('user.addresses.*') ? 'font-semibold' : 'text-slate-600 hover:text-slate-900' }} transition"
               style="{{ request()->routeIs('user.addresses.*') ? 'color:#2962ff;' : '' }}">
                My Addresses
            </a>
            <a href="{{ route('profile') }}"
               class="text-sm font-medium {{ request()->routeIs('profile') ? 'font-semibold' : 'text-slate-600 hover:text-slate-900' }} transition"
               style="{{ request()->routeIs('profile') ? 'color:#2962ff;' : '' }}">
                Profile
            </a>
        @endauth
    </div>
</nav>
