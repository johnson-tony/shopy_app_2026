@extends('admin.layouts.admin')

@section('title', 'Product Management — Shopy Admin')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                Product Management
            </h1>
            <p class="text-xs text-slate-400 mt-1">Manage catalog items across shopping modes (Shopy, Food, Minutes).</p>
        </div>

        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition shadow-lg shadow-indigo-600/20 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add New Product
        </a>
    </div>

    <!-- Statistics Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex items-center gap-4 shadow-lg">
            <div class="w-11 h-11 rounded-xl bg-indigo-600/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Products</p>
                <p class="text-xl font-bold text-white mt-0.5">{{ number_format($stats['total']) }}</p>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex items-center gap-4 shadow-lg">
            <div class="w-11 h-11 rounded-xl bg-emerald-600/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Items</p>
                <p class="text-xl font-bold text-emerald-400 mt-0.5">{{ number_format($stats['active']) }}</p>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex items-center gap-4 shadow-lg">
            <div class="w-11 h-11 rounded-xl bg-rose-600/10 border border-rose-500/20 flex items-center justify-center text-rose-400 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Out of Stock</p>
                <p class="text-xl font-bold text-rose-400 mt-0.5">{{ number_format($stats['out_of_stock']) }}</p>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex items-center gap-4 shadow-lg">
            <div class="w-11 h-11 rounded-xl bg-amber-600/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Featured Items</p>
                <p class="text-xl font-bold text-amber-400 mt-0.5">{{ number_format($stats['featured']) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl">
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-center">
            <!-- Search Keyword -->
            <div class="relative lg:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, SKU, or slug..."
                    class="w-full px-4 py-2.5 pr-10 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Mode Filter -->
            <div>
                <select name="mode" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">All Modes</option>
                    @foreach ($modes as $m)
                        <option value="{{ $m->id }}" {{ (string) $modeFilter === (string) $m->id ? 'selected' : '' }}>
                            {{ $m->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <select name="category" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string) $categoryFilter === (string) $cat->id ? 'selected' : '' }}>
                            {{ $cat->parent ? $cat->parent->name . ' > ' : '' }}{{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>

            <!-- Actions (Filter & Reset) -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition cursor-pointer">
                    Apply
                </button>
                @if ($search !== '' || $modeFilter !== null || $categoryFilter !== null || $statusFilter !== null || $featuredFilter !== null)
                    <a href="{{ route('admin.products.index') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium transition" title="Clear Filters">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-left text-xs whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3.5 px-4">Product</th>
                        <th class="py-3.5 px-4">Mode</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4">Price</th>
                        <th class="py-3.5 px-4">Stock</th>
                        <th class="py-3.5 px-4">Featured</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse ($products as $product)
                        <tr class="hover:bg-slate-800/30 transition">
                            <!-- Product Image & Info -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    @if ($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-12 h-12 rounded-xl object-cover border border-slate-700 shrink-0 bg-slate-800">
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-500 shrink-0">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('admin.products.edit', $product) }}" class="font-bold text-white text-sm hover:text-indigo-400 transition whitespace-nowrap">
                                            {{ $product->name }}
                                        </a>
                                        <div class="flex items-center gap-2 mt-0.5 whitespace-nowrap">
                                            @if ($product->sku)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-800 text-[10px] text-slate-400 font-mono border border-slate-700">
                                                    {{ $product->sku }}
                                                </span>
                                            @endif
                                            <span class="text-[11px] text-slate-500 font-mono whitespace-nowrap">
                                                {{ $product->slug }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Shopping Mode Badge -->
                            <td class="py-3.5 px-4">
                                @if ($product->mode)
                                    @php
                                        $modeSlug = $product->mode->slug;
                                        $badgeClasses = match($modeSlug) {
                                            'shopy' => 'bg-indigo-950/70 text-indigo-300 border-indigo-500/40',
                                            'food' => 'bg-amber-950/70 text-amber-300 border-amber-500/40',
                                            'minutes' => 'bg-emerald-950/70 text-emerald-300 border-emerald-500/40',
                                            default => 'bg-slate-800 text-slate-300 border-slate-700'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $badgeClasses }}">
                                        @if ($product->mode->image_url)
                                            <img src="{{ $product->mode->image_url }}" alt="" class="w-3.5 h-3.5 object-contain">
                                        @elseif ($product->mode->icon)
                                            <i class="{{ $product->mode->icon }} text-[10px]"></i>
                                        @else
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        @endif
                                        {{ $product->mode->name }}
                                    </span>
                                @else
                                    <span class="text-slate-500 text-[10px]">Unassigned</span>
                                @endif
                            </td>

                            <!-- Category -->
                            <td class="py-3.5 px-4">
                                @if ($product->category)
                                    <div class="flex flex-col">
                                        @if ($product->category->parent)
                                            <span class="text-[10px] text-slate-500 font-medium">
                                                {{ $product->category->parent->name }} &rsaquo;
                                            </span>
                                        @endif
                                        <span class="text-slate-300 font-semibold text-xs">
                                            {{ $product->category->name }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-slate-500 text-[10px]">No Category</span>
                                @endif
                            </td>

                            <!-- Price & Sale Price -->
                            <td class="py-3.5 px-4">
                                @if ($product->is_on_sale)
                                    <div class="flex flex-col">
                                        <span class="font-bold text-white text-xs">
                                            ${{ number_format($product->sale_price, 2) }}
                                        </span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[10px] text-slate-500 line-through">
                                                ${{ number_format($product->price, 2) }}
                                            </span>
                                            <span class="px-1.5 py-0.2 rounded bg-rose-950 text-[10px] font-bold text-rose-400 border border-rose-500/30">
                                                -{{ $product->discount_percentage }}%
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <span class="font-bold text-white text-xs">
                                        ${{ number_format($product->price, 2) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Stock -->
                            <td class="py-3.5 px-4">
                                @if ($product->stock_status === 'out_of_stock')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-950/70 text-rose-400 border border-rose-500/40">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                        Out of Stock (0)
                                    </span>
                                @elseif ($product->stock_status === 'low_stock')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-950/70 text-amber-400 border border-amber-500/40">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        Low: {{ $product->stock }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-950/70 text-emerald-400 border border-emerald-500/40">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        {{ $product->stock }} in stock
                                    </span>
                                @endif
                            </td>

                            <!-- Featured Toggle -->
                            <td class="py-3.5 px-4">
                                <form method="POST" action="{{ route('admin.products.toggleFeatured', $product) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold transition cursor-pointer {{ $product->featured ? 'bg-amber-950/70 text-amber-300 border border-amber-500/40 hover:bg-amber-900/60' : 'bg-slate-800 text-slate-400 border border-slate-700 hover:bg-slate-700' }}">
                                        <svg class="w-3 h-3 {{ $product->featured ? 'text-amber-400 fill-amber-400' : 'text-slate-400' }}" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        {{ $product->featured ? 'Featured' : 'Standard' }}
                                    </button>
                                </form>
                            </td>

                            <!-- Active Status Toggle -->
                            <td class="py-3.5 px-4">
                                <form method="POST" action="{{ route('admin.products.toggleStatus', $product) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold transition cursor-pointer {{ $product->status ? 'bg-emerald-950/70 text-emerald-300 border border-emerald-500/40 hover:bg-emerald-900/60' : 'bg-rose-950/70 text-rose-300 border border-rose-500/40 hover:bg-rose-900/60' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $product->status ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                        {{ $product->status ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.products.show', $product) }}" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition" title="View Product Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    <a href="{{ route('admin.products.edit', $product) }}" class="p-2 rounded-lg bg-slate-800 hover:bg-indigo-600 text-slate-300 hover:text-white transition" title="Edit Product">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Are you sure you want to delete product \'{{ $product->name }}\'?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white transition cursor-pointer" title="Delete Product">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-500 mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-300">No products found</p>
                                    <p class="text-xs text-slate-500 mt-1 max-w-sm">No products match your search or filter criteria. Try resetting filters or add a new product.</p>
                                    <a href="{{ route('admin.products.create') }}" class="mt-4 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition">
                                        Add First Product
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($products->hasPages())
            <div class="pt-4 border-t border-slate-800">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
