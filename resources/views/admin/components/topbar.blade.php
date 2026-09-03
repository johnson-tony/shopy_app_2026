<header class="sticky top-0 z-30 shrink-0 h-16 bg-slate-900/95 backdrop-blur-md border-b border-slate-800 px-6 flex items-center justify-between text-slate-300">
    <div class="flex items-center gap-3">
        <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-950/60 text-emerald-400 border border-emerald-500/30">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            Admin Secured Area
        </span>
    </div>

    <div class="flex items-center gap-4">
        <a href="{{ route('admin.settings.index') }}" title="System Settings" class="text-xs text-slate-400 hover:text-indigo-400 flex items-center gap-1.5 transition">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="hidden sm:inline">Settings</span>
        </a>

        <div class="h-4 w-px bg-slate-800"></div>

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
