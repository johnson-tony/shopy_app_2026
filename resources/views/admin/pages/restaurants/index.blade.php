@extends('admin.layouts.admin')

@section('title', 'Restaurant Management')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-xs font-semibold text-amber-400 mb-2">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                <span>Food Delivery Channel</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Restaurant Management</h1>
            <p class="text-slate-400 text-sm mt-1">
                Manage partner restaurants, cuisines, logos, banners, delivery timing, and food menu catalog.
            </p>
        </div>
        @if(auth('admin')->user()?->hasPermission('restaurants.create'))
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.restaurants.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm shadow-md shadow-amber-500/25 transition cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Register Restaurant</span>
                </a>
            </div>
        @endif
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Restaurants</p>
            <p class="text-2xl font-black text-white mt-1.5">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Partners</p>
            <p class="text-2xl font-black text-emerald-400 mt-1.5">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pure Veg</p>
            <p class="text-2xl font-black text-green-400 mt-1.5">{{ $stats['pure_veg'] }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Featured</p>
            <p class="text-2xl font-black text-amber-400 mt-1.5">{{ $stats['featured'] }}</p>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl">
        <form method="GET" action="{{ route('admin.restaurants.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-center">
            <!-- Search Keyword -->
            <div>
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Search by restaurant, cuisine, city..." 
                       class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:border-amber-500 placeholder-slate-500">
            </div>

            <!-- Status Filter -->
            <div>
                <select name="status" class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:border-amber-500">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>

            <!-- Veg Type Filter -->
            <div>
                <select name="veg" class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:border-amber-500">
                    <option value="">All Diets</option>
                    <option value="veg" {{ $vegFilter === 'veg' ? 'selected' : '' }}>Pure Veg Only</option>
                    <option value="non_veg" {{ $vegFilter === 'non_veg' ? 'selected' : '' }}>Non-Veg / Multi-Cuisine</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold rounded-xl transition cursor-pointer">
                    Filter
                </button>
                @if($search || $statusFilter || $vegFilter || $featuredFilter)
                    <a href="{{ route('admin.restaurants.index') }}" class="px-3 py-2 bg-slate-950 hover:bg-slate-800 text-slate-400 text-sm font-semibold rounded-xl border border-slate-800 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Restaurants Listing Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Restaurant</th>
                        <th class="px-6 py-4">Cuisine &amp; City</th>
                        <th class="px-6 py-4">Delivery &amp; Cost</th>
                        <th class="px-6 py-4">Rating &amp; Dishes</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($restaurants as $restaurant)
                        <tr class="hover:bg-slate-800/40 transition">
                            <!-- Restaurant Image & Name -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 overflow-hidden shrink-0 flex items-center justify-center">
                                        @if($restaurant->image)
                                            <img src="{{ $restaurant->image_url }}" alt="{{ $restaurant->name }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="fa-solid fa-utensils text-slate-500"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-white font-bold truncate">{{ $restaurant->name }}</span>
                                            @if($restaurant->is_pure_veg)
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-800/50">
                                                    Veg
                                                </span>
                                            @endif
                                            @if($restaurant->is_featured)
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-950 text-amber-300 border border-amber-800/50">
                                                    Featured
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-xs text-slate-500 block truncate">{{ $restaurant->address ?? 'No address set' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Cuisine & City -->
                            <td class="px-6 py-4">
                                <span class="text-slate-200 block truncate max-w-xs">{{ $restaurant->cuisine }}</span>
                                <span class="text-xs text-slate-500">{{ $restaurant->city ?? 'Chennai' }}</span>
                            </td>

                            <!-- Delivery & Cost -->
                            <td class="px-6 py-4">
                                <span class="text-slate-200 font-semibold block">{{ $restaurant->delivery_time }} mins</span>
                                <span class="text-xs text-slate-500">₹{{ number_format($restaurant->cost_for_two, 0) }} for two</span>
                            </td>

                            <!-- Rating & Dishes -->
                            <td class="px-6 py-4">
                                <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold">
                                    <span>{{ number_format($restaurant->rating, 1) }}</span>
                                    <i class="fa-solid fa-star text-[9px]"></i>
                                </div>
                                <span class="text-xs text-slate-500 block mt-1">
                                    {{ $restaurant->products_count }} {{ Str::plural('dish', $restaurant->products_count) }}
                                </span>
                            </td>

                            <!-- Status Toggle -->
                            <td class="px-6 py-4">
                                @if(auth('admin')->user()?->hasPermission('restaurants.edit'))
                                    <form method="POST" action="{{ route('admin.restaurants.toggleStatus', $restaurant) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold transition cursor-pointer {{ $restaurant->status ? 'bg-emerald-950/70 text-emerald-400 border border-emerald-800/50 hover:bg-emerald-900/50' : 'bg-rose-950/70 text-rose-400 border border-rose-800/50 hover:bg-rose-900/50' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $restaurant->status ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                            <span>{{ $restaurant->status ? 'Active' : 'Inactive' }}</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $restaurant->status ? 'bg-emerald-950/70 text-emerald-400 border border-emerald-800/50' : 'bg-rose-950/70 text-rose-400 border border-rose-800/50' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $restaurant->status ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                        <span>{{ $restaurant->status ? 'Active' : 'Inactive' }}</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if(auth('admin')->user()?->hasPermission('restaurants.edit'))
                                        <a href="{{ route('admin.restaurants.edit', $restaurant) }}" 
                                           class="p-2 text-slate-400 hover:text-amber-400 hover:bg-slate-800 rounded-xl transition"
                                           title="Edit Restaurant">
                                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                                        </a>
                                    @endif

                                    @if(auth('admin')->user()?->hasPermission('restaurants.delete'))
                                        <form method="POST" action="{{ route('admin.restaurants.destroy', $restaurant) }}" 
                                              onsubmit="return confirm('Are you sure you want to remove {{ addslashes($restaurant->name) }}? Associated dishes will be unlinked.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-xl transition cursor-pointer"
                                                    title="Delete Restaurant">
                                                <i class="fa-solid fa-trash-can text-sm"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <i class="fa-solid fa-utensils text-3xl mb-2 text-slate-600 block"></i>
                                <p class="text-base font-semibold text-slate-400">No restaurants found</p>
                                <p class="text-xs mt-1">Try refining your search keyword or register a new restaurant partner.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($restaurants->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950">
                {{ $restaurants->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
