@extends('admin.layouts.admin')

@section('title', $product->name . ' — Product Details — Shopy Admin')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.products.index') }}" class="hover:text-indigo-400 transition">Products</a>
                <span>&rsaquo;</span>
                <span class="text-slate-200">Details</span>
                <span>&rsaquo;</span>
                <span class="text-slate-400 font-mono text-[11px]">{{ $product->slug }}</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-3">
                <span>{{ $product->name }}</span>
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition shadow-lg shadow-indigo-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Product
            </a>

            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to List
            </a>
        </div>
    </div>

    <!-- Product Summary Hero Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Visual Media Card (Left 1 col) -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <div class="relative rounded-2xl overflow-hidden border border-slate-700 bg-slate-950 aspect-square flex items-center justify-center">
                @if ($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain">
                @else
                    <div class="flex flex-col items-center justify-center text-slate-500 space-y-2">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-xs">No image uploaded</p>
                    </div>
                @endif
            </div>

            <!-- Badges -->
            <div class="flex flex-wrap items-center gap-2 pt-2">
                @if ($product->status)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-950/70 text-emerald-300 border border-emerald-500/40">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Active
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-950/70 text-rose-300 border border-rose-500/40">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Inactive
                    </span>
                @endif

                @if ($product->featured)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-950/70 text-amber-300 border border-amber-500/40">
                        <svg class="w-3.5 h-3.5 text-amber-400 fill-amber-400" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        Featured
                    </span>
                @endif

                @if ($product->stock_status === 'out_of_stock')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-950/70 text-rose-400 border border-rose-500/40">
                        Out of Stock
                    </span>
                @elseif ($product->stock_status === 'low_stock')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-950/70 text-amber-400 border border-amber-500/40">
                        Low Stock ({{ $product->stock }})
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-950/70 text-emerald-400 border border-emerald-500/40">
                        In Stock ({{ $product->stock }})
                    </span>
                @endif
            </div>
        </div>

        <!-- Details Grid (Right 2 cols) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Primary Metadata -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3">
                    Overview &amp; Channel
                </h2>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Shopping Mode</p>
                        <p class="text-sm font-bold text-white mt-1">
                            @if ($product->mode)
                                <span class="inline-flex items-center gap-1.5 text-indigo-400">
                                    @if ($product->mode->icon)
                                        <i class="{{ $product->mode->icon }} text-xs"></i>
                                    @endif
                                    {{ $product->mode->name }} ({{ $product->mode->slug }})
                                </span>
                            @else
                                <span class="text-slate-500">Unassigned</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Category</p>
                        <p class="text-sm font-bold text-white mt-1">
                            @if ($product->category)
                                {{ $product->category->parent ? $product->category->parent->name . ' > ' : '' }}{{ $product->category->name }}
                            @else
                                <span class="text-slate-500">Unassigned</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">SKU</p>
                        <p class="text-sm font-mono text-slate-300 mt-1">
                            {{ $product->sku ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Regular Price</p>
                        <p class="text-lg font-bold text-white mt-0.5">
                            ${{ number_format($product->price, 2) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sale Price</p>
                        <p class="text-lg font-bold text-emerald-400 mt-0.5">
                            @if ($product->is_on_sale)
                                ${{ number_format($product->sale_price, 2) }}
                                <span class="text-xs text-rose-400">(-{{ $product->discount_percentage }}%)</span>
                            @else
                                <span class="text-slate-500 text-sm font-normal">None</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Stock Inventory</p>
                        <p class="text-lg font-bold text-white mt-0.5">
                            {{ $product->stock }} units
                        </p>
                    </div>
                </div>

                @if ($product->short_description)
                    <div class="pt-3 border-t border-slate-800">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Short Description</p>
                        <p class="text-sm text-slate-300">{{ $product->short_description }}</p>
                    </div>
                @endif

                @if ($product->description)
                    <div class="pt-3 border-t border-slate-800">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Full Description</p>
                        <div class="text-sm text-slate-300 leading-relaxed whitespace-pre-line bg-slate-950 p-4 rounded-xl border border-slate-800">
                            {{ $product->description }}
                        </div>
                    </div>
                @endif

                <div class="pt-3 border-t border-slate-800 grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs text-slate-400">
                    <div>
                        <span class="font-semibold text-slate-500">Slug:</span>
                        <span class="font-mono text-slate-300 block truncate">{{ $product->slug }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-500">Created:</span>
                        <span class="text-slate-300 block">{{ $product->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-500">Last Updated:</span>
                        <span class="text-slate-300 block">{{ $product->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
