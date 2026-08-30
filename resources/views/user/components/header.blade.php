<header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Brand Logo -->
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-black text-xl tracking-tight text-indigo-600">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-600 text-white font-bold shadow-md shadow-indigo-200">
                        S
                    </span>
                    <span class="text-slate-900 font-extrabold">Shopy</span><span class="text-indigo-600 text-xs font-semibold px-1.5 py-0.5 rounded bg-indigo-50 border border-indigo-100">2026</span>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-6">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-indigo-600 font-semibold' : 'text-slate-600 hover:text-slate-900' }} transition">
                        Dashboard
                    </a>
                    <a href="{{ route('profile') }}" class="text-sm font-medium {{ request()->routeIs('profile') ? 'text-indigo-600 font-semibold' : 'text-slate-600 hover:text-slate-900' }} transition">
                        Profile
                    </a>
                @endauth
            </nav>

            <!-- User Actions / Auth Buttons -->
            <div class="flex items-center gap-3">
                @auth
                    <div class="flex items-center gap-3">
                        <div class="hidden sm:flex flex-col text-right">
                            <span class="text-xs font-semibold text-slate-800">{{ auth()->user()->name }}</span>
                            <span class="text-[11px] text-slate-500 capitalize">{{ auth()->user()->roles->pluck('name')->join(', ') ?: 'Customer' }}</span>
                        </div>
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-slate-700 text-xs font-bold ring-2 ring-indigo-500/20">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 text-xs font-medium text-slate-700 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition border border-slate-200">
                                Logout
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-indigo-600 transition">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition">
                        Create Account
                    </a>
                @endauth
            </div>
        </div>
    </div>
</header>
