<aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col shrink-0 min-h-screen text-slate-300">
    <!-- Admin Brand Header -->
    <div class="h-16 px-6 flex items-center gap-3 border-b border-slate-800">
        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-500 text-white font-black text-sm shadow-md shadow-indigo-500/20">
            A
        </span>
        <div class="flex flex-col">
            <span class="text-white font-bold text-sm tracking-wide leading-tight">Shopy Admin</span>
            <span class="text-[10px] text-slate-400 font-mono">Control Panel 2026</span>
        </div>
    </div>

    <!-- Navigation -->
    <div class="flex-1 py-6 px-4 space-y-1">
        <div class="px-3 pb-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
            Foundation Modules
        </div>

        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Dashboard</span>
        </a>

        <div class="pt-6 px-3 pb-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
            System & Security
        </div>

        <div class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs text-slate-500 cursor-not-allowed">
            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>User Management (Step 1)</span>
        </div>

        <div class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs text-slate-500 cursor-not-allowed">
            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>Roles & Permissions</span>
        </div>
    </div>

    <!-- Admin Footer & Quick Logout -->
    <div class="p-4 border-t border-slate-800 bg-slate-950/40">
        <div class="flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-slate-200 truncate max-w-[130px]">{{ auth('admin')->user()?->name }}</span>
                <span class="text-[10px] text-indigo-400 font-mono">{{ auth('admin')->user()?->roles->pluck('name')->join(', ') }}</span>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" title="Sign out of Admin Portal" class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
