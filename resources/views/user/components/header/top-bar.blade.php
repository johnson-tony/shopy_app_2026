<div class="border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        {{-- Brand Logo --}}
        <div class="flex items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-black text-2xl tracking-tight" style="color: #2962ff;">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-white font-bold shadow-md" style="background:#2962ff;">
                    S
                </span>
                <span class="text-slate-900 font-extrabold">Shopy</span>
                <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded" style="background:#2962ff1a;color:#2962ff;">2026</span>
            </a>
        </div>

        {{-- Right Icons / Actions --}}
        <div class="flex items-center gap-2">
            @include('user.components.header.user-trigger')

            {{-- Mobile Toggle --}}
            <button type="button" class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg text-slate-600 hover:bg-slate-100 transition" id="mobileMenuToggle">
                <i class="fas fa-bars text-lg"></i>
            </button>
        </div>
    </div>
</div>
