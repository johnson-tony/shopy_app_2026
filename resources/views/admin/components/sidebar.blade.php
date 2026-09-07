@php
    $adminUser = auth('admin')->user();
@endphp

<aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col shrink-0 h-screen sticky top-0 overflow-y-auto text-slate-300">
    <!-- Admin Brand Header -->
    <div class="h-16 px-6 flex items-center gap-3 border-b border-slate-800 shrink-0 sticky top-0 bg-slate-900 z-10">
        @if(\App\Models\AdminSetting::hasCustomLogo())
            <img src="{{ \App\Models\AdminSetting::siteLogoUrl() }}" alt="{{ \App\Models\AdminSetting::siteName() }}" class="w-8 h-8 rounded-lg object-contain bg-slate-800 p-0.5 border border-slate-700">
        @else
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-500 text-white font-black text-sm shadow-md shadow-indigo-500/20">
                {{ strtoupper(substr(\App\Models\AdminSetting::siteName(), 0, 1)) }}
            </span>
        @endif
        <div class="flex flex-col min-w-0">
            <span class="text-white font-bold text-sm tracking-wide leading-tight truncate">{{ \App\Models\AdminSetting::siteName() }} Admin</span>
            <span class="text-[10px] text-slate-400 font-mono">Control Panel 2026</span>
        </div>
    </div>

    <!-- Navigation -->
    <div class="flex-1 py-6 px-4 space-y-1">
        <div class="px-3 pb-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
            Overview
        </div>

        @if($adminUser?->hasPermission('dashboard.view'))
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>
        @endif

        @if($adminUser?->hasPermission('categories.view') || $adminUser?->hasPermission('products.view') || $adminUser?->hasPermission('restaurants.view') || $adminUser?->hasPermission('modes.view') || $adminUser?->hasPermission('coupons.view'))
            <div class="pt-6 px-3 pb-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                Catalog &amp; Store
            </div>

            @if($adminUser?->hasPermission('categories.view'))
                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.categories.*') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.categories.*') ? 'text-indigo-400' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Categories</span>
                </a>
            @endif

            @if($adminUser?->hasPermission('products.view'))
                <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.products.*') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.products.*') ? 'text-indigo-400' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>Products</span>
                </a>
            @endif

            @if($adminUser?->hasPermission('restaurants.view'))
                <a href="{{ route('admin.restaurants.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.restaurants.*') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-utensils w-5 text-center text-sm {{ request()->routeIs('admin.restaurants.*') ? 'text-indigo-400' : 'text-slate-500' }}"></i>
                    <span>Restaurants</span>
                </a>
            @endif

            @if($adminUser?->hasPermission('modes.view'))
                <a href="{{ route('admin.modes.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.modes.*') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.modes.*') ? 'text-indigo-400' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Modes</span>
                </a>
            @endif

            @if($adminUser?->hasPermission('coupons.view'))
                <a href="{{ route('admin.coupons.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.coupons.*') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.coupons.*') ? 'text-indigo-400' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                    <span>Coupons &amp; Offers</span>
                </a>
            @endif
        @endif

        @if($adminUser?->hasPermission('orders.view'))
            <div class="pt-6 px-3 pb-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                Orders &amp; Fulfillment
            </div>

            <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.orders.*') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('admin.orders.*') ? 'text-indigo-400' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Orders</span>
            </a>

            @if($adminUser?->hasPermission('reviews.view'))
                <a href="{{ route('admin.reviews.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.reviews.*') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-star w-5 text-center text-sm {{ request()->routeIs('admin.reviews.*') ? 'text-indigo-400' : 'text-slate-500' }}"></i>
                    <span>Customer Reviews</span>
                </a>
            @endif
        @endif

        @if($adminUser?->hasPermission('users.view') || $adminUser?->hasPermission('admins.view') || $adminUser?->hasPermission('roles.view') || $adminUser?->hasPermission('settings.view'))
            <div class="pt-6 px-3 pb-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                System &amp; Security
            </div>

            @if($adminUser?->hasPermission('users.view'))
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.users.*') ? 'text-indigo-400' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>User Management</span>
                </a>
            @endif

            @if($adminUser?->hasPermission('admins.view'))
                <a href="{{ route('admin.admins.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.admins.*') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-users-gear text-sm {{ request()->routeIs('admin.admins.*') ? 'text-indigo-400' : 'text-slate-500' }}"></i>
                    <span>Administrators</span>
                </a>
            @endif

            @if($adminUser?->hasPermission('roles.view'))
                <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.roles.*') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-shield-halved text-sm {{ request()->routeIs('admin.roles.*') ? 'text-indigo-400' : 'text-slate-500' }}"></i>
                    <span>Roles &amp; Permissions</span>
                </a>
            @endif

            @if($adminUser?->hasPermission('settings.view'))
                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.settings.*') ? 'text-indigo-400' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>System Settings</span>
                </a>
            @endif
        @endif
    </div>

    <!-- Admin Footer & Quick Logout -->
    <div class="p-4 border-t border-slate-800 bg-slate-950/40 shrink-0 sticky bottom-0">
        <div class="flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-slate-200 truncate max-w-[130px]">{{ $adminUser?->name }}</span>
                <span class="text-[10px] text-indigo-400 font-mono">{{ $adminUser?->roles->pluck('name')->join(', ') }}</span>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" title="Sign out of Admin Portal" class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
