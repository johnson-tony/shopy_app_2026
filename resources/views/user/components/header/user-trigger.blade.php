<div class="relative" id="userMenu">
    <button type="button" class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-100 transition" id="userMenuBtn">
        @auth
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full ring-2 font-bold text-sm text-white" style="background:#2962ff;">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </span>
            <span class="hidden sm:flex flex-col text-left leading-tight">
                <span class="text-xs font-semibold text-slate-800">{{ auth()->user()->name }}</span>
                <span class="text-[11px] text-slate-500">My Account</span>
            </span>
        @else
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-slate-100 text-slate-500">
                <i class="fas fa-user text-sm"></i>
            </span>
            <span class="hidden sm:block text-sm font-medium text-slate-700">Login</span>
        @endauth
        <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
    </button>

    @include('user.components.header.user-dropdown')
</div>
