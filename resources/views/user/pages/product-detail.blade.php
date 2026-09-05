@extends('user.layouts.app')

@php
    $hasDiscount = $product->is_on_sale;
    $currentPrice = $hasDiscount ? $product->sale_price : $product->price;
    $discountPercent = $product->discount_percentage ?? 0;
    $imageUrl = $product->image_url ?? 'https://placehold.co/600x600/e2e8f0/475569?text=' . urlencode(substr($product->name, 0, 8));
    $inStock = ($product->stock ?? 0) > 0;
    $isLowStock = $inStock && $product->stock <= 5;
    $isWishlisted = Auth::guard('web')->check() && $product->isWishlistedBy(Auth::guard('web')->user());
@endphp

@section('title', $product->name . ' — Buy Online at Shopy')

@section('content')
<div class="space-y-8">
    <!-- Breadcrumb Navigation -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 overflow-x-auto whitespace-nowrap pb-2 border-b border-slate-100 dark:border-slate-800" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-indigo-600 transition flex items-center gap-1">
            <i class="fa-solid fa-house text-[11px]"></i>
            <span>Home</span>
        </a>
        @if($product->mode)
            <span>/</span>
            <a href="{{ route('home', ['mode' => $product->mode->slug]) }}" class="hover:text-indigo-600 transition font-medium">
                {{ $product->mode->name }}
            </a>
        @endif
        @if($product->category)
            <span>/</span>
            <a href="{{ route('home', ['category' => $product->category->slug, 'mode' => $product->mode?->slug]) }}" class="hover:text-indigo-600 transition">
                {{ $product->category->name }}
            </a>
        @endif
        <span>/</span>
        <span class="text-slate-800 dark:text-slate-200 font-semibold truncate max-w-[240px]">{{ $product->name }}</span>
    </nav>

    <!-- Main Product Section: Two Columns (Desktop: Left Image, Right Content) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
        <!-- Left 5 Cols: Product Image & Badges -->
        <div class="lg:col-span-5 space-y-4">
            <div class="relative bg-white dark:bg-slate-800/80 rounded-3xl p-4 sm:p-6 shadow-sm overflow-hidden group">
                <!-- Sale Discount Badge -->
                @if($hasDiscount || $discountPercent > 0)
                    <div class="absolute top-4 left-4 z-10 px-3 py-1 rounded-full text-xs font-black tracking-wide bg-rose-500 text-white shadow-md shadow-rose-500/25">
                        {{ $discountPercent }}% OFF
                    </div>
                @endif

                <!-- Wishlist Floating Button -->
                <button type="button" 
                        class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-white/90 dark:bg-slate-900/90 backdrop-blur-md shadow-sm flex items-center justify-center transition cursor-pointer hover:scale-105"
                        onclick="toggleWishlist({{ $product->id }}, this);"
                        title="{{ $isWishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}">
                    <i class="{{ $isWishlisted ? 'fa-solid fa-heart text-rose-500' : 'fa-regular fa-heart text-slate-400 dark:text-slate-300' }} text-base"></i>
                </button>

                <!-- High-Res Product Visual (Generous, crisp, not tiny) -->
                <div class="aspect-square w-full flex items-center justify-center overflow-hidden rounded-2xl bg-slate-50 dark:bg-slate-900/40 p-4">
                    <img src="{{ $imageUrl }}" 
                         alt="{{ $product->name }}" 
                         class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300">
                </div>

                <!-- Floating Delivery Pill on Image (Blinkit / Zepto Style) -->
                <div class="absolute bottom-4 left-4 z-10 inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black uppercase tracking-wider bg-white/95 dark:bg-slate-900/95 text-slate-900 dark:text-white shadow-md backdrop-blur-md">
                    <i class="{{ $product->deliveryIcon() }}"></i>
                    <span>{{ $product->deliveryEstimate() }}</span>
                </div>
            </div>
        </div>

        <!-- Right 7 Cols: Product Details & Commerce Options -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Channel & Category Pill -->
            <div class="flex items-center gap-2">
                @if($product->mode)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                        @if($product->mode->icon)<i class="{{ $product->mode->icon }} text-[11px]"></i>@endif
                        {{ $product->mode->name }}
                    </span>
                @endif
                @if($product->category)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-indigo-600 bg-indigo-50 dark:bg-indigo-950/60 dark:text-indigo-400">
                        {{ $product->category->name }}
                    </span>
                @endif
            </div>

            <!-- Title -->
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-tight">
                    {{ $product->name }}
                </h1>
                @if($product->short_description)
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                        {{ $product->short_description }}
                    </p>
                @endif
            </div>

            <!-- Product Details & Overview Section (Positioned under the Name) -->
            @if($product->description)
                <div class="space-y-2 py-1">
                    <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                        Product Details &amp; Overview
                    </h2>
                    <div class="prose dark:prose-invert max-w-none text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-2">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                </div>
            @endif

            <!-- Rating & Verified Reviews -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-emerald-500 text-white font-bold text-xs">
                    <span>4.8</span>
                    <i class="fa-solid fa-star text-[10px]"></i>
                </div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">1,420 Verified Ratings &bull; 85 Reviews</span>
                <span class="text-xs text-slate-300 dark:text-slate-600">|</span>
                @if($inStock)
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold {{ $isLowStock ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        <span class="w-2 h-2 rounded-full {{ $isLowStock ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                        {{ $isLowStock ? "Only {$product->stock} Left in Stock!" : 'In Stock' }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-600 dark:text-rose-400">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        Currently Out of Stock
                    </span>
                @endif
            </div>

            <!-- Price Card (No border) -->
            <div class="p-5 rounded-3xl bg-slate-100/80 dark:bg-slate-800/60 space-y-2">
                <div class="flex items-baseline gap-3">
                    <span class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                        ₹{{ number_format($currentPrice, 2) }}
                    </span>
                    @if($hasDiscount)
                        <span class="text-lg text-slate-400 line-through">
                            ₹{{ number_format($product->price, 2) }}
                        </span>
                        <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-md">
                            Save ₹{{ number_format($product->price - $currentPrice, 2) }} ({{ $discountPercent }}% off)
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-400">Inclusive of all applicable taxes</p>
            </div>

            <!-- 4 Flipkart/Amazon Policy Trust Badges Grid (4 per row on big screen, 2 per row on small screen) -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-2.5 sm:gap-3">
                <!-- 1. Return Policy Badge -->
                <div class="p-3 rounded-2xl bg-slate-100/80 dark:bg-slate-800/60 space-y-1">
                    <i class="{{ $product->returnPolicyIcon() }} text-base mb-1 block"></i>
                    <span class="text-xs font-bold text-slate-900 dark:text-white block leading-tight">
                        {{ $product->returnPolicyText() }}
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block leading-tight">
                        {{ $product->isReturnable() ? 'Hassle-free replacement' : 'Perishable / Fresh item' }}
                    </span>
                </div>

                <!-- 2. Delivery ETA -->
                <div class="p-3 rounded-2xl bg-slate-100/80 dark:bg-slate-800/60 space-y-1">
                    <i class="{{ $product->deliveryIcon() }} text-base mb-1 block"></i>
                    <span class="text-xs font-bold text-slate-900 dark:text-white block leading-tight">
                        {{ $product->deliveryEstimate() }}
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block leading-tight">
                        Fast Doorstep Delivery
                    </span>
                </div>

                <!-- 3. Free Delivery Threshold -->
                <div class="p-3 rounded-2xl bg-slate-100/80 dark:bg-slate-800/60 space-y-1">
                    <i class="fa-solid fa-truck text-indigo-500 text-base mb-1 block"></i>
                    <span class="text-xs font-bold text-slate-900 dark:text-white block leading-tight">
                        Free Delivery
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block leading-tight">
                        Orders above ₹{{ number_format($freeDeliveryThreshold, 0) }}
                    </span>
                </div>

                <!-- 4. Cash on Delivery -->
                <div class="p-3 rounded-2xl bg-slate-100/80 dark:bg-slate-800/60 space-y-1">
                    <i class="fa-solid fa-wallet text-amber-500 text-base mb-1 block"></i>
                    <span class="text-xs font-bold text-slate-900 dark:text-white block leading-tight">
                        Pay on Delivery
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block leading-tight">
                        Cash or UPI accepted
                    </span>
                </div>
            </div>

            <!-- Action Buttons: Quantity Stepper + Add to Cart + Buy Now -->
            <div class="pt-2 space-y-3">
                <div class="flex items-center gap-3">
                    @if($inStock)
                        <!-- Quantity Selector Stepper -->
                        <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden bg-slate-50 dark:bg-slate-800 shrink-0">
                            <button type="button" 
                                    onclick="let q = document.getElementById('pdpQuantity'); if(parseInt(q.value) > 1) q.value = parseInt(q.value) - 1;"
                                    class="w-10 h-11 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition font-bold text-sm cursor-pointer">
                                <i class="fa-solid fa-minus text-xs"></i>
                            </button>
                            <input type="number" 
                                    id="pdpQuantity" 
                                    value="1" 
                                    min="1" 
                                    max="{{ $product->stock }}" 
                                    readonly
                                    class="w-12 text-center bg-transparent font-bold text-sm text-slate-900 dark:text-white focus:outline-none">
                            <button type="button" 
                                    onclick="let q = document.getElementById('pdpQuantity'); if(parseInt(q.value) < {{ $product->stock }}) q.value = parseInt(q.value) + 1;"
                                    class="w-10 h-11 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition font-bold text-sm cursor-pointer">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>
                        </div>

                        <!-- Add to Cart Button -->
                        <button type="button" 
                                onclick="const qty = parseInt(document.getElementById('pdpQuantity').value) || 1; window.addToCart({{ $product->id }}, qty, this);"
                                class="flex-1 py-3 px-6 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-md shadow-indigo-600/25 transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-bag-shopping"></i>
                            <span>Add to Cart</span>
                        </button>

                        <!-- Buy Now Button (Adds to cart & navigates directly to cart) -->
                        <button type="button" 
                                onclick="const qty = parseInt(document.getElementById('pdpQuantity').value) || 1; window.addToCart({{ $product->id }}, qty, this); setTimeout(() => window.location.href = '{{ route('cart.index', ['mode' => $product->mode?->slug]) }}', 300);"
                                class="py-3 px-6 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-sm shadow-md shadow-amber-500/20 transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Buy Now</span>
                        </button>
                    @else
                        <button type="button" disabled class="w-full py-3.5 px-6 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-bold text-sm cursor-not-allowed">
                            Currently Out of Stock
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products Section -->
    @if($relatedProducts->count() > 0)
        <div class="pt-10 border-t border-slate-200 dark:border-slate-800 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Similar Products in {{ $product->category?->name ?? 'Store' }}
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Customers also viewed these items</p>
                </div>
                @if($product->category)
                    <a href="{{ route('home', ['category' => $product->category->slug, 'mode' => $product->mode?->slug]) }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                        View all &rarr;
                    </a>
                @endif
            </div>

            <div class="products-grid">
                @foreach($relatedProducts as $related)
                    @include('user.components.storefront.product-card', ['product' => $related])
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
