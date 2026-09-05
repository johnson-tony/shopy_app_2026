@extends('admin.layouts.admin')

@section('title', 'Edit Product: ' . $product->name . ' — Shopy Admin')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.products.index') }}" class="hover:text-indigo-400 transition">Products</a>
                <span>&rsaquo;</span>
                <span class="text-slate-200">Edit</span>
                <span>&rsaquo;</span>
                <span class="text-slate-400 font-mono text-[11px] truncate max-w-[150px]">{{ $product->slug }}</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-3">
                <span>Edit: {{ $product->name }}</span>
                @if ($product->status)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950/70 text-emerald-300 border border-emerald-500/40">Active</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-950/70 text-rose-300 border border-rose-500/40">Inactive</span>
                @endif
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.products.show', $product) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                View
            </a>

            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Are you sure you want to delete product \'{{ $product->name }}\'?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white text-xs font-semibold border border-rose-500/30 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Product Form -->
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Core Info (2 cols) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Mode & Category Hierarchy Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        Channel &amp; Category
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Mode Selector -->
                        <div>
                            <label for="mode_id" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Shopping Mode <span class="text-rose-400">*</span>
                            </label>
                            <select id="mode_id" name="mode_id" required
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition @error('mode_id') border-rose-500 @enderror">
                                <option value="">Select Mode (e.g. Shopy, Food, Minutes)</option>
                                @foreach ($modes as $mode)
                                    <option value="{{ $mode->id }}" data-slug="{{ $mode->slug }}" {{ old('mode_id', $product->mode_id) == $mode->id ? 'selected' : '' }}>
                                        {{ $mode->name }} ({{ $mode->slug }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1">If mode changes, you must pick a category from the new mode.</p>
                            @error('mode_id')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category Selector (Filtered by Mode) -->
                        <div>
                            <label for="category_id" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Category <span class="text-rose-400">*</span>
                            </label>
                            <select id="category_id" name="category_id" required
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition @error('category_id') border-rose-500 @enderror">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" data-mode-id="{{ $category->mode_id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->parent ? $category->parent->name . ' > ' : '' }}{{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1">Filtered dynamically to match the selected mode.</p>
                            @error('category_id')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Basic Product Information -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Product Details
                    </h2>

                    <!-- Product Name -->
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Product Name <span class="text-rose-400">*</span>
                        </label>
                        <input id="name" type="text" name="name" value="{{ old('name', $product->name) }}" required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-rose-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Slug with Auto-Generator Helper -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="slug" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                                    URL Slug <span class="text-slate-500 font-normal font-mono">(Unique)</span>
                                </label>
                                <button type="button" id="regenerateSlugBtn" class="text-[11px] text-indigo-400 hover:text-indigo-300 transition cursor-pointer">
                                    Regenerate from Name
                                </button>
                            </div>
                            <input id="slug" type="text" name="slug" value="{{ old('slug', $product->slug) }}"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('slug') border-rose-500 @enderror">
                            @error('slug')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- SKU -->
                        <div>
                            <label for="sku" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                SKU / Item Code <span class="text-slate-500 font-normal font-mono">(Optional)</span>
                            </label>
                            <input id="sku" type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('sku') border-rose-500 @enderror">
                            @error('sku')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Short Description -->
                    <div>
                        <label for="short_description" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Short Summary <span class="text-slate-500 font-normal">(Shown in listing cards)</span>
                        </label>
                        <input id="short_description" type="text" name="short_description" value="{{ old('short_description', $product->short_description) }}"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('short_description') border-rose-500 @enderror">
                        @error('short_description')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Full Description -->
                    <div>
                        <label for="description" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Full Product Description
                        </label>
                        <textarea id="description" name="description" rows="4"
                            class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-rose-500 @enderror">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Pricing & Inventory Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Pricing &amp; Inventory
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Regular Price -->
                        <div>
                            <label for="price" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Regular Price ($) <span class="text-rose-400">*</span>
                            </label>
                            <input id="price" type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" required
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('price') border-rose-500 @enderror">
                            @error('price')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sale Price -->
                        <div>
                            <label for="sale_price" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Sale Price ($) <span class="text-slate-500 font-normal font-mono">(&le; Regular)</span>
                            </label>
                            <input id="sale_price" type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}"
                                placeholder="Leave blank if no sale"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('sale_price') border-rose-500 @enderror">
                            @error('sale_price')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Stock Inventory -->
                        <div>
                            <label for="stock" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Stock Count <span class="text-rose-400">*</span>
                            </label>
                            <input id="stock" type="number" min="0" name="stock" value="{{ old('stock', $product->stock) }}" required
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('stock') border-rose-500 @enderror">
                            @error('stock')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Delivery Timing Option -->
                    <div class="pt-2 border-t border-slate-800/80">
                        <label for="delivery_time" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>Estimated Delivery Timing</span>
                            <span class="text-indigo-400 font-mono text-[10px] font-normal lowercase">(optional - overrides channel default)</span>
                        </label>
                        <select id="delivery_time" name="delivery_time" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('delivery_time') border-rose-500 @enderror">
                            <option value="">Channel Default (Automated: 10-15m Minutes, 30-45m Food, 2-3d Shopy)</option>
                            <optgroup label="⚡ Quick Commerce (Minutes)">
                                <option value="10-15 mins" {{ old('delivery_time', $product->delivery_time) === '10-15 mins' ? 'selected' : '' }}>⚡ 10-15 mins (Standard Quick Delivery)</option>
                                <option value="15-25 mins" {{ old('delivery_time', $product->delivery_time) === '15-25 mins' ? 'selected' : '' }}>⚡ 15-25 mins (Fresh Bakery / Dairy)</option>
                                <option value="25-35 mins" {{ old('delivery_time', $product->delivery_time) === '25-35 mins' ? 'selected' : '' }}>⚡ 25-35 mins (Extended Hub Item)</option>
                            </optgroup>
                            <optgroup label="🛵 Hot Food & Kitchen (Food)">
                                <option value="20-30 mins" {{ old('delivery_time', $product->delivery_time) === '20-30 mins' ? 'selected' : '' }}>🛵 20-30 mins (Fast Food, Beverages & Desserts)</option>
                                <option value="30-45 mins" {{ old('delivery_time', $product->delivery_time) === '30-45 mins' ? 'selected' : '' }}>🛵 30-45 mins (Cooked Meals, Biryani & Curries)</option>
                                <option value="45-60 mins" {{ old('delivery_time', $product->delivery_time) === '45-60 mins' ? 'selected' : '' }}>🛵 45-60 mins (Tandoor & Slow-Cooked Dishes)</option>
                                <option value="1-2 hours" {{ old('delivery_time', $product->delivery_time) === '1-2 hours' ? 'selected' : '' }}>🎂 1-2 hours (Cakes & Custom Baking)</option>
                            </optgroup>
                            <optgroup label="🚚 Standard Shipping (Shopy)">
                                <option value="Same Day" {{ old('delivery_time', $product->delivery_time) === 'Same Day' ? 'selected' : '' }}>🚚 Same Day Delivery (Local City Orders)</option>
                                <option value="Tomorrow" {{ old('delivery_time', $product->delivery_time) === 'Tomorrow' ? 'selected' : '' }}>🚚 Tomorrow / Next Day Delivery</option>
                                <option value="2-3 days" {{ old('delivery_time', $product->delivery_time) === '2-3 days' ? 'selected' : '' }}>🚚 2-3 Business Days (Standard Courier)</option>
                                <option value="4-7 days" {{ old('delivery_time', $product->delivery_time) === '4-7 days' ? 'selected' : '' }}>🚚 4-7 Days (Bulky / Freight Goods)</option>
                            </optgroup>
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">Leave as Channel Default for automated timing or pick a specific duration for this item.</p>
                        @error('delivery_time')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Return & Replacement Policy -->
                    <div class="pt-2 border-t border-slate-800/80">
                        <label for="return_policy" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>Return &amp; Replacement Policy</span>
                            <span class="text-indigo-400 font-mono text-[10px] font-normal lowercase">(customer protection terms)</span>
                        </label>
                        <select id="return_policy" name="return_policy" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('return_policy') border-rose-500 @enderror">
                            <option value="7_days_return" {{ old('return_policy', $product->return_policy) === '7_days_return' ? 'selected' : '' }}>🔄 7 Days Returnable (Standard E-Commerce Goods)</option>
                            <option value="non_returnable" {{ old('return_policy', $product->return_policy) === 'non_returnable' ? 'selected' : '' }}>🚫 Non-Returnable (Perishable, Food, Dairy &amp; Groceries)</option>
                            <option value="7_days_replacement" {{ old('return_policy', $product->return_policy) === '7_days_replacement' ? 'selected' : '' }}>🔄 7 Days Replacement Only (Electronics &amp; Mobiles)</option>
                            <option value="10_days_return" {{ old('return_policy', $product->return_policy) === '10_days_return' ? 'selected' : '' }}>🔄 10 Days Return &amp; Exchange (Fashion, Shoes &amp; Apparel)</option>
                            <option value="30_days_return" {{ old('return_policy', $product->return_policy) === '30_days_return' ? 'selected' : '' }}>🔄 30 Days Returnable (Extended Guarantee Items)</option>
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">Select Non-Returnable for cooked meals/groceries, or choose standard refund/exchange window.</p>
                        @error('return_policy')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

            </div>

            <!-- Right Column: Image & Publishing Settings (1 col) -->
            <div class="space-y-6">

                <!-- Cloudinary Image Upload Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Product Media
                    </h2>

                    <!-- Current or New Image Preview Box -->
                    <div id="imagePreviewContainer" class="{{ $product->image_url ? '' : 'hidden' }} relative rounded-2xl overflow-hidden border border-slate-700 bg-slate-950 aspect-video flex items-center justify-center">
                        <img id="imagePreview" src="{{ $product->image_url ?? '' }}" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain">
                    </div>

                    <!-- Drag & Drop Zone -->
                    <div id="dropzone" class="border-2 border-dashed border-slate-700 hover:border-indigo-500/60 rounded-2xl p-6 text-center transition cursor-pointer bg-slate-950/40">
                        <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/jpg,image/webp,image/svg+xml" class="hidden">
                        <div class="flex flex-col items-center justify-center space-y-2">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-300">Click or drag image to replace</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">JPEG, PNG, WEBP, SVG up to 3MB</p>
                            </div>
                            <span class="inline-block px-2.5 py-1 rounded-md bg-indigo-950/60 text-indigo-300 text-[10px] font-mono border border-indigo-500/30">
                                Cloudinary: shopy_so/products
                            </span>
                        </div>
                    </div>
                    @error('image')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Visibility & Publishing Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Publishing Options
                    </h2>

                    <!-- Sort Order -->
                    <div>
                        <label for="sort_order" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Display Sort Order
                        </label>
                        <input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $product->sort_order) }}"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('sort_order') border-rose-500 @enderror">
                        <p class="text-[11px] text-slate-500 mt-1">Lower numbers display first.</p>
                    </div>

                    <!-- Active Storefront Status -->
                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-950 border border-slate-800">
                        <div>
                            <p class="text-xs font-semibold text-white">Active Status</p>
                            <p class="text-[11px] text-slate-400">Show this product in storefront</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="status" value="1" {{ old('status', $product->status ? '1' : '0') == '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <!-- Featured Product Toggle -->
                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-950 border border-slate-800">
                        <div>
                            <p class="text-xs font-semibold text-white">Featured Product</p>
                            <p class="text-[11px] text-slate-400">Promote in featured grids &amp; carousels</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="featured" value="1" {{ old('featured', $product->featured ? '1' : '0') == '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                        </label>
                    </div>

                    <!-- Form Action Buttons -->
                    <div class="pt-2 space-y-2">
                        <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition shadow-lg shadow-indigo-600/20 cursor-pointer flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Changes
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="w-full block py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-center font-medium text-xs transition">
                            Cancel
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </form>
</div>

<!-- Dynamic JavaScript for Mode ⇄ Category Cascading & Slug/Dropzone -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modeSelect = document.getElementById('mode_id');
    const categorySelect = document.getElementById('category_id');
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const regenerateSlugBtn = document.getElementById('regenerateSlugBtn');

    // Filter categories based on selected mode
    function filterCategoriesByMode() {
        const selectedModeId = modeSelect.value;
        const currentCategoryVal = categorySelect.value;
        let hasValidSelection = false;

        Array.from(categorySelect.options).forEach(option => {
            if (!option.value) {
                option.style.display = '';
                return;
            }

            const optionModeId = option.getAttribute('data-mode-id');
            if (!selectedModeId || optionModeId === selectedModeId) {
                option.style.display = '';
                if (option.value === currentCategoryVal) {
                    hasValidSelection = true;
                }
            } else {
                option.style.display = 'none';
            }
        });

        if (!hasValidSelection && currentCategoryVal) {
            categorySelect.value = '';
        }
    }

    modeSelect.addEventListener('change', filterCategoriesByMode);
    filterCategoriesByMode(); // Run on initial render

    // Slug Generator
    function slugify(text) {
        return text.toString().toLowerCase()
            .trim()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    }

    regenerateSlugBtn.addEventListener('click', function () {
        slugInput.value = slugify(nameInput.value);
    });

    // Image Drag & Drop and Preview
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('image');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImg = document.getElementById('imagePreview');

    dropzone.addEventListener('click', () => fileInput.click());

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('border-indigo-500', 'bg-indigo-950/20');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('border-indigo-500', 'bg-indigo-950/20');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-indigo-500', 'bg-indigo-950/20');
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            handleImagePreview(e.dataTransfer.files[0]);
        }
    });

    fileInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            handleImagePreview(this.files[0]);
        }
    });

    function handleImagePreview(file) {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImg.src = e.target.result;
            previewContainer.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endsection
