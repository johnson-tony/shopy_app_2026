<header class="sticky top-0 z-30 shrink-0 h-16 bg-slate-900/95 backdrop-blur-md border-b border-slate-800 px-6 flex items-center justify-between text-slate-300">
    <div class="flex items-center gap-3">
        <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-950/60 text-emerald-400 border border-emerald-500/30">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            Admin Secured Area
        </span>
    </div>

    <div class="flex items-center gap-4">
        <a href="{{ route('home') }}" target="_blank" class="text-xs text-slate-400 hover:text-white flex items-center gap-1 transition">
            <span>View Storefront</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
        </a>

        <div class="h-4 w-px bg-slate-800"></div>

        <form method="POST" action="{{ route('admin.logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-xs font-semibold text-rose-400 hover:text-rose-300 transition cursor-pointer">
                Logout
            </button>
        </form>
    </div>
</header>
