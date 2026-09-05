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
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-8 items-start">
        <!-- Left 4 Cols: Product Image Gallery & Badges -->
        <div class="lg:col-span-4 w-full flex flex-col items-center lg:items-start space-y-3">
            <div class="relative bg-white dark:bg-slate-800/80 rounded-2xl p-2.5 sm:p-3 shadow-sm overflow-hidden group w-full max-w-[240px] sm:max-w-[260px]">
                <!-- Sale Discount Badge -->
                @if($hasDiscount || $discountPercent > 0)
                    <div class="absolute top-2.5 left-2.5 z-10 px-2 py-0.5 rounded-full text-[10px] font-black tracking-wide bg-rose-500 text-white shadow-md shadow-rose-500/25">
                        {{ $discountPercent }}% OFF
                    </div>
                @endif

                <!-- Wishlist Floating Button -->
                <button type="button" 
                        class="absolute top-2.5 right-2.5 z-10 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-900/90 backdrop-blur-md shadow-sm flex items-center justify-center transition cursor-pointer hover:scale-105"
                        onclick="toggleWishlist({{ $product->id }}, this);"
                        title="{{ $isWishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}">
                    <i class="{{ $isWishlisted ? 'fa-solid fa-heart text-rose-500' : 'fa-regular fa-heart text-slate-400 dark:text-slate-300' }} text-xs"></i>
                </button>

                <!-- Product Visual (Simple, Clean, Compact ~190-200px) -->
                <div class="h-48 sm:h-52 w-full flex items-center justify-center overflow-hidden rounded-xl bg-slate-50 dark:bg-slate-900/40 p-2.5">
                    <img id="mainProductImg"
                         src="{{ $imageUrl }}" 
                         alt="{{ $product->name }}" 
                         class="max-h-full max-w-full object-contain transition-all duration-300">
                </div>

                <!-- Floating Delivery Pill on Image (Blinkit / Zepto Style) -->
                <div class="absolute bottom-2.5 left-2.5 z-10 inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-white/95 dark:bg-slate-900/95 text-slate-900 dark:text-white shadow-sm backdrop-blur-md">
                    <i class="{{ $product->deliveryIcon() }}"></i>
                    <span>{{ $product->deliveryEstimate() }}</span>
                </div>
            </div>

            <!-- Gallery Thumbnails Strip (Clickable angles & variant views) -->
            @php
                $gallery = $product->galleryImages();
            @endphp
            @if(count($gallery) > 1)
                <div class="flex items-center gap-2 overflow-x-auto max-w-[240px] sm:max-w-[260px] pb-1 no-scrollbar w-full">
                    @foreach($gallery as $gIdx => $gImg)
                        <button type="button"
                                class="gallery-thumb-btn w-11 h-11 sm:w-12 sm:h-12 rounded-xl p-1 bg-white dark:bg-slate-800 border-2 {{ $gIdx === 0 ? 'border-indigo-600 dark:border-indigo-500 ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-400 opacity-70 hover:opacity-100' }} shrink-0 flex items-center justify-center overflow-hidden transition cursor-pointer"
                                onclick="switchGalleryImage('{{ $gImg }}', this);"
                                title="View image {{ $gIdx + 1 }}">
                            <img src="{{ $gImg }}" alt="Thumbnail {{ $gIdx + 1 }}" class="max-h-full max-w-full object-contain">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right 8 Cols: Product Details & Commerce Options -->
        <div class="lg:col-span-8 space-y-5">
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
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <div class="product-rating-badge inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-600 text-white font-bold text-xs shrink-0 whitespace-nowrap shadow-xs" style="background-color: #059669 !important; color: #ffffff !important;">
                    <span style="color: #ffffff !important;">{{ number_format($product->averageRating(), 1) }}</span>
                    <i class="fa-solid fa-star text-[10px]" style="color: #ffffff !important;"></i>
                </div>
                <a href="#customerReviewsSection" class="text-xs text-slate-500 dark:text-slate-400 font-medium whitespace-nowrap hover:underline">
                    {{ number_format($product->ratingsCount()) }} Verified Ratings &bull; {{ number_format($product->reviewsCount()) }} Reviews
                </a>
                <span class="text-xs text-slate-300 dark:text-slate-600 hidden sm:inline">|</span>
                @if($inStock)
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold whitespace-nowrap {{ $isLowStock ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        <span class="w-2 h-2 rounded-full {{ $isLowStock ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                        {{ $isLowStock ? "Only {$product->stock} Left in Stock!" : 'In Stock' }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold whitespace-nowrap text-rose-600 dark:text-rose-400">
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

            @php
                $colorOptions = $product->colorOptions();
                $sizeOptions = $product->sizeOptions();
                $defaultColor = count($colorOptions) > 0 ? $colorOptions[0]['name'] : null;
                $defaultSize = count($sizeOptions) > 0 ? $sizeOptions[0] : null;
            @endphp

            @if(count($colorOptions) > 0 || count($sizeOptions) > 0)
                <div class="space-y-4 pt-1">
                    <!-- Hidden inputs for selected variants -->
                    <input type="hidden" id="pdpSelectedColor" value="{{ $defaultColor }}">
                    <input type="hidden" id="pdpSelectedSize" value="{{ $defaultSize }}">

                    <!-- Color Swatches -->
                    @if(count($colorOptions) > 0)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700 dark:text-slate-300">
                                    Color: <span id="selectedColorDisplay" class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $defaultColor }}</span>
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2.5" id="colorSwatchesGroup">
                                @foreach($colorOptions as $cIdx => $c)
                                    <button type="button"
                                            class="color-swatch-btn relative w-8 h-8 rounded-full transition-transform cursor-pointer flex items-center justify-center {{ $cIdx === 0 ? 'ring-2 ring-indigo-600 ring-offset-2 dark:ring-offset-slate-900 scale-110' : 'hover:scale-105 opacity-85 hover:opacity-100' }}"
                                            style="background-color: {{ $c['hex'] }};"
                                            data-color="{{ $c['name'] }}"
                                            onclick="selectPdpColor('{{ $c['name'] }}', this);"
                                            title="{{ $c['name'] }}">
                                        @if(in_array(strtolower($c['name']), ['white', 'starlight']))
                                            <span class="absolute inset-0 rounded-full border border-slate-300 dark:border-slate-600"></span>
                                        @endif
                                        <i class="fa-solid fa-check text-xs {{ in_array(strtolower($c['name']), ['white', 'starlight', 'silver', 'yellow']) ? 'text-slate-900' : 'text-white' }} {{ $cIdx === 0 ? '' : 'hidden' }}"></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Size / Option Pills -->
                    @if(count($sizeOptions) > 0)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700 dark:text-slate-300">
                                    Size / Option: <span id="selectedSizeDisplay" class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $defaultSize }}</span>
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2" id="sizePillsGroup">
                                @foreach($sizeOptions as $sIdx => $s)
                                    <button type="button"
                                            class="size-pill-btn px-3.5 py-1.5 rounded-xl text-xs font-bold border-2 transition cursor-pointer {{ $sIdx === 0 ? 'border-indigo-600 bg-indigo-50/70 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 dark:border-indigo-500 shadow-xs' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:border-slate-400' }}"
                                            data-size="{{ $s }}"
                                            onclick="selectPdpSize('{{ $s }}', this);">
                                        {{ $s }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Action Buttons: Quantity Stepper + Add to Cart + Buy Now -->
            <div class="pt-2 space-y-3">
                <div class="flex items-center gap-3">
                    @if($inStock)
                        @php
                            $supportsQuantityStepper = in_array($product->mode?->slug, ['minutes', 'food']);
                        @endphp

                        @if($supportsQuantityStepper)
                            <!-- Quantity Selector Stepper (Only for Minutes & Food modes) -->
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
                        @else
                            <!-- Fixed quantity 1 for regular Shopy mode products -->
                            <input type="hidden" id="pdpQuantity" value="1">
                        @endif

                        <!-- Add to Cart Button -->
                        <button type="button" 
                                onclick="handlePdpAddToCart(this);"
                                class="flex-1 py-3 px-6 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-md shadow-indigo-600/25 transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-bag-shopping"></i>
                            <span>Add to Cart</span>
                        </button>

                        <!-- Buy Now Button (Adds to cart & navigates directly to cart) -->
                        <button type="button" 
                                onclick="handlePdpBuyNow(this);"
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

            <!-- 4 Flipkart/Amazon Policy Trust Badges (Horizontally scrollable on small screens, 4 columns on desktop) -->
            <div class="flex items-stretch overflow-x-auto md:grid md:grid-cols-4 gap-2.5 sm:gap-3 pt-1 pb-1 scrollbar-none no-scrollbar snap-x">
                <!-- 1. Return Policy Badge -->
                <div class="shrink-0 w-36 sm:w-40 md:w-auto snap-start p-3 rounded-2xl bg-slate-100/80 dark:bg-slate-800/60 space-y-1">
                    <i class="{{ $product->returnPolicyIcon() }} text-base mb-1 block"></i>
                    <span class="text-xs font-bold text-slate-900 dark:text-white block leading-tight">
                        {{ $product->returnPolicyText() }}
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block leading-tight">
                        {{ $product->isReturnable() ? 'Hassle-free replacement' : 'Perishable / Fresh item' }}
                    </span>
                </div>

                <!-- 2. Delivery ETA -->
                <div class="shrink-0 w-36 sm:w-40 md:w-auto snap-start p-3 rounded-2xl bg-slate-100/80 dark:bg-slate-800/60 space-y-1">
                    <i class="{{ $product->deliveryIcon() }} text-base mb-1 block"></i>
                    <span class="text-xs font-bold text-slate-900 dark:text-white block leading-tight">
                        {{ $product->deliveryEstimate() }}
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block leading-tight">
                        Fast Doorstep Delivery
                    </span>
                </div>

                <!-- 3. Free Delivery Threshold -->
                <div class="shrink-0 w-36 sm:w-40 md:w-auto snap-start p-3 rounded-2xl bg-slate-100/80 dark:bg-slate-800/60 space-y-1">
                    <i class="fa-solid fa-truck text-indigo-500 text-base mb-1 block"></i>
                    <span class="text-xs font-bold text-slate-900 dark:text-white block leading-tight">
                        Free Delivery
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block leading-tight">
                        Orders above ₹{{ number_format($freeDeliveryThreshold, 0) }}
                    </span>
                </div>

                <!-- 4. Cash on Delivery -->
                <div class="shrink-0 w-36 sm:w-40 md:w-auto snap-start p-3 rounded-2xl bg-slate-100/80 dark:bg-slate-800/60 space-y-1">
                    <i class="fa-solid fa-wallet text-amber-500 text-base mb-1 block"></i>
                    <span class="text-xs font-bold text-slate-900 dark:text-white block leading-tight">
                        Pay on Delivery
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block leading-tight">
                        Cash or UPI accepted
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Ratings & Customer Reviews Section (Flipkart / Amazon Grade) -->
    <div id="customerReviewsSection" class="pt-10 border-t border-slate-200 dark:border-slate-800 space-y-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                    <i class="fa-solid fa-star text-amber-500 text-lg"></i>
                    <span>Ratings &amp; Customer Reviews</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Genuine feedback from customers who bought this product. Verified purchases receive the <span class="font-bold text-emerald-600 dark:text-emerald-400">✔ Certified Buyer</span> badge.
                </p>
            </div>

            <!-- Review Action Button -->
            <div>
                @auth
                    @if($userReview)
                        <button type="button" 
                                onclick="openPdpReviewModal();"
                                class="px-5 py-2.5 rounded-xl font-bold text-xs text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 border border-indigo-200 dark:border-indigo-800 transition flex items-center gap-2 cursor-pointer shadow-xs">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>Edit Your Review</span>
                        </button>
                    @else
                        <button type="button" 
                                onclick="openPdpReviewModal();"
                                class="px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-500/20 transition flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-star text-amber-300"></i>
                            <span>Rate Product</span>
                        </button>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-500/20 transition flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>Sign In to Review</span>
                    </a>
                @endauth
            </div>
        </div>

        @php
            $avgScore = $product->averageRating();
            $breakdown = $product->ratingBreakdown();
            $customerPhotos = $product->customerPhotos();
            $approvedReviews = $product->approvedReviews;
        @endphp

        <!-- Overall Rating & Star Breakdown Grid -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 bg-slate-50 dark:bg-slate-800/60 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80">
            <!-- Col 1: Average Rating Score Card (4/12 Cols) -->
            <div class="md:col-span-4 flex flex-col justify-center items-center text-center p-4 border-b md:border-b-0 md:border-r border-slate-200 dark:border-slate-700 space-y-2">
                <div class="text-4xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <span>{{ number_format($avgScore, 1) }}</span>
                    <i class="fa-solid fa-star text-amber-500 text-3xl"></i>
                </div>
                <div class="flex items-center gap-1 text-amber-400 text-sm">
                    @for($s = 1; $s <= 5; $s++)
                        <i class="{{ $s <= round($avgScore) ? 'fa-solid fa-star' : 'fa-regular fa-star' }}"></i>
                    @endfor
                </div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                    {{ number_format($product->ratingsCount()) }} Ratings &amp; {{ number_format($product->reviewsCount()) }} Reviews
                </p>
                @if($hasPurchased)
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400">
                        <i class="fa-solid fa-badge-check"></i>
                        <span>You purchased this product</span>
                    </div>
                @endif
            </div>

            <!-- Col 2: Star Breakdown Progress Bars (8/12 Cols) -->
            <div class="md:col-span-8 flex flex-col justify-center space-y-2.5 px-2 sm:px-6">
                @for($star = 5; $star >= 1; $star--)
                    @php 
                        $pct = $breakdown[$star]['percentage'] ?? 0;
                        $cnt = $breakdown[$star]['count'] ?? 0;
                        $barColor = match($star) {
                            5, 4 => 'bg-emerald-500',
                            3    => 'bg-amber-500',
                            default => 'bg-rose-500',
                        };
                    @endphp
                    <div class="flex items-center gap-3 text-xs">
                        <span class="w-8 font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1 shrink-0">
                            <span>{{ $star }}</span>
                            <i class="fa-solid fa-star text-[10px] text-amber-400"></i>
                        </span>
                        
                        <div class="flex-1 h-2.5 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden">
                            <div class="h-full rounded-full {{ $barColor }} transition-all duration-500" style="width: {{ $pct }}%;"></div>
                        </div>

                        <span class="w-12 text-right font-medium text-slate-400 dark:text-slate-500 text-[11px] shrink-0">
                            {{ $cnt }}
                        </span>
                    </div>
                @endfor
            </div>
        </div>

        <!-- Customer Uploaded Photos Gallery Strip (if photos exist) -->
        @if(count($customerPhotos) > 0)
            <div class="space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <i class="fa-solid fa-camera text-indigo-500"></i>
                    <span>Customer Photos ({{ count($customerPhotos) }})</span>
                </h3>

                <div class="flex items-center gap-3 overflow-x-auto pb-2 snap-x no-scrollbar">
                    @foreach($customerPhotos as $cPhoto)
                        <button type="button" 
                                onclick="openPdpLightbox('{{ $cPhoto }}');"
                                class="shrink-0 w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden border-2 border-slate-200 dark:border-slate-700 hover:border-indigo-600 transition snap-start cursor-pointer hover:scale-105">
                            <img src="{{ $cPhoto }}" alt="Customer review photo" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Customer Reviews List -->
        <div class="space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                Customer Reviews ({{ $approvedReviews->count() }})
            </h3>

            @if($approvedReviews->count() > 0)
                <div class="divide-y divide-slate-100 dark:divide-slate-800 space-y-4">
                    @foreach($approvedReviews as $rev)
                        <div class="pt-4 space-y-2.5">
                            <!-- Star Badge & Review Title -->
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-white font-bold text-xs {{ $rev->rating >= 4 ? 'bg-emerald-600' : ($rev->rating == 3 ? 'bg-amber-500' : 'bg-rose-500') }}">
                                    <span>{{ $rev->rating }}</span>
                                    <i class="fa-solid fa-star text-[9px]"></i>
                                </div>
                                @if($rev->title)
                                    <h4 class="font-bold text-sm text-slate-900 dark:text-white">
                                        {{ $rev->title }}
                                    </h4>
                                @endif
                            </div>

                            <!-- Comment Content -->
                            <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                                {!! nl2br(e($rev->comment)) !!}
                            </p>

                            <!-- Customer Uploaded Photos in this review -->
                            @php $reviewPhotos = $rev->imageUrls(); @endphp
                            @if(count($reviewPhotos) > 0)
                                <div class="flex items-center gap-2.5 pt-1">
                                    @foreach($reviewPhotos as $imgUrl)
                                        <button type="button" 
                                                onclick="openPdpLightbox('{{ $imgUrl }}');"
                                                class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-indigo-600 transition cursor-pointer hover:scale-105">
                                            <img src="{{ $imgUrl }}" alt="Review photo" class="w-full h-full object-cover">
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Reviewer Meta Details -->
                            <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-400 pt-1">
                                <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $rev->user->name ?? 'Customer' }}</span>
                                
                                @if($rev->is_verified_buyer)
                                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                                        <span>Certified Buyer</span>
                                    </span>
                                @endif

                                <span>&bull;</span>
                                <span>{{ $rev->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- No Reviews Yet State -->
                <div class="bg-slate-50 dark:bg-slate-800/40 rounded-3xl p-8 text-center border border-slate-200 dark:border-slate-700/80 space-y-3">
                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-700/60 text-slate-400 flex items-center justify-center mx-auto text-xl">
                        <i class="fa-regular fa-star"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">No Reviews Yet</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                        Be the first verified customer to share your thoughts, photos, and rating with other shoppers!
                    </p>
                    @auth
                        <div class="pt-1">
                            <button type="button" 
                                    onclick="openPdpReviewModal();" 
                                    class="px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-500/20 transition cursor-pointer">
                                Write the First Review
                            </button>
                        </div>
                    @endauth
                </div>
            @endif
        </div>
    </div>

    <!-- Rate & Review Modal on PDP -->
    <div id="pdpReviewModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                <div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">Rate &amp; Review Product</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-xs">{{ $product->name }}</p>
                </div>
                <button type="button" onclick="closePdpReviewModal();" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <form action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data" id="pdpReviewForm" class="space-y-4">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="order_id" value="{{ $userOrderId ?? '' }}">
                <input type="hidden" name="rating" id="pdpModalRatingValue" value="{{ $userReview?->rating ?? 5 }}">

                <!-- Star Rating Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Overall Rating *</label>
                    <div class="flex items-center gap-2" id="pdpStarContainer">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" 
                                    onclick="setPdpModalRating({{ $i }});"
                                    class="text-2xl text-amber-400 hover:scale-125 transition pdp-modal-star-btn cursor-pointer" 
                                    data-star="{{ $i }}">
                                <i class="{{ $i <= ($userReview?->rating ?? 5) ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                            </button>
                        @endfor
                        <span id="pdpRatingDescriptor" class="text-xs font-bold text-amber-600 dark:text-amber-400 ml-2">Excellent (5/5)</span>
                    </div>
                </div>

                <!-- Review Headline -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Headline (Optional)</label>
                    <input type="text" name="title" value="{{ old('title', $userReview?->title) }}" placeholder="e.g. Great quality and fits perfectly" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Review Comment -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Detailed Review *</label>
                    <textarea name="comment" rows="4" required placeholder="Tell other shoppers what you liked or disliked about this product..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">{{ old('comment', $userReview?->comment) }}</textarea>
                </div>

                <!-- Multiple Customer Photo Uploads -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Add Customer Photos (Max 5 images)</label>
                    <input type="file" name="images[]" multiple accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Upload actual photos of the item you received.</p>
                </div>

                <!-- Verified Badge indicator -->
                @if($hasPurchased)
                    <div class="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 flex items-center gap-2 text-xs text-emerald-800 dark:text-emerald-300">
                        <i class="fa-solid fa-badge-check text-emerald-600 text-sm"></i>
                        <span>Order verified! Your review will automatically display the <strong>✔ Certified Buyer</strong> badge.</span>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" onclick="closePdpReviewModal();" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-500/25 cursor-pointer">
                        {{ $userReview ? 'Update Review' : 'Submit Review' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customer Photo Lightbox Modal -->
    <div id="pdpLightboxModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md" onclick="closePdpLightbox();">
        <div class="relative max-w-3xl max-h-[85vh] p-2" onclick="event.stopPropagation();">
            <button type="button" onclick="closePdpLightbox();" class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-white text-slate-800 flex items-center justify-center shadow-lg font-bold">
                &times;
            </button>
            <img id="pdpLightboxImage" src="" alt="Customer photo enlarged" class="max-w-full max-h-[80vh] rounded-2xl shadow-2xl object-contain">
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

@push('scripts')
<script>
    function switchGalleryImage(url, btnEl) {
        const mainImg = document.getElementById('mainProductImg');
        if (mainImg) {
            mainImg.src = url;
        }
        document.querySelectorAll('.gallery-thumb-btn').forEach(btn => {
            btn.classList.remove('border-indigo-600', 'dark:border-indigo-500', 'ring-2', 'ring-indigo-500/20', 'opacity-100');
            btn.classList.add('border-slate-200', 'dark:border-slate-700', 'opacity-70');
        });
        if (btnEl) {
            btnEl.classList.remove('border-slate-200', 'dark:border-slate-700', 'opacity-70');
            btnEl.classList.add('border-indigo-600', 'dark:border-indigo-500', 'ring-2', 'ring-indigo-500/20', 'opacity-100');
        }
    }

    function selectPdpColor(colorName, btnEl) {
        const input = document.getElementById('pdpSelectedColor');
        if (input) input.value = colorName;
        const display = document.getElementById('selectedColorDisplay');
        if (display) display.textContent = colorName;

        document.querySelectorAll('.color-swatch-btn').forEach(btn => {
            btn.classList.remove('ring-2', 'ring-indigo-600', 'ring-offset-2', 'dark:ring-offset-slate-900', 'scale-110');
            btn.classList.add('opacity-85');
            const check = btn.querySelector('i');
            if (check) check.classList.add('hidden');
        });

        if (btnEl) {
            btnEl.classList.remove('opacity-85');
            btnEl.classList.add('ring-2', 'ring-indigo-600', 'ring-offset-2', 'dark:ring-offset-slate-900', 'scale-110');
            const check = btnEl.querySelector('i');
            if (check) check.classList.remove('hidden');
        }
    }

    function selectPdpSize(sizeName, btnEl) {
        const input = document.getElementById('pdpSelectedSize');
        if (input) input.value = sizeName;
        const display = document.getElementById('selectedSizeDisplay');
        if (display) display.textContent = sizeName;

        document.querySelectorAll('.size-pill-btn').forEach(btn => {
            btn.classList.remove('border-indigo-600', 'bg-indigo-50/70', 'text-indigo-600', 'dark:bg-indigo-950/60', 'dark:text-indigo-400', 'dark:border-indigo-500', 'shadow-xs');
            btn.classList.add('border-slate-200', 'dark:border-slate-700', 'bg-white', 'dark:bg-slate-800', 'text-slate-700', 'dark:text-slate-300');
        });

        if (btnEl) {
            btnEl.classList.remove('border-slate-200', 'dark:border-slate-700', 'bg-white', 'dark:bg-slate-800', 'text-slate-700', 'dark:text-slate-300');
            btnEl.classList.add('border-indigo-600', 'bg-indigo-50/70', 'text-indigo-600', 'dark:bg-indigo-950/60', 'dark:text-indigo-400', 'dark:border-indigo-500', 'shadow-xs');
        }
    }

    function handlePdpAddToCart(btnEl) {
        const qty = parseInt(document.getElementById('pdpQuantity')?.value) || 1;
        const color = document.getElementById('pdpSelectedColor')?.value || null;
        const size = document.getElementById('pdpSelectedSize')?.value || null;
        window.addToCart({{ $product->id }}, qty, btnEl, color, size);
    }

    function handlePdpBuyNow(btnEl) {
        const qty = parseInt(document.getElementById('pdpQuantity')?.value) || 1;
        const color = document.getElementById('pdpSelectedColor')?.value || null;
        const size = document.getElementById('pdpSelectedSize')?.value || null;
        window.addToCart({{ $product->id }}, qty, btnEl, color, size);
        setTimeout(() => {
            window.location.href = '{{ route('cart.index', ['mode' => $product->mode?->slug]) }}';
        }, 350);
    }

    function openPdpReviewModal() {
        document.getElementById('pdpReviewModal')?.classList.remove('hidden');
    }

    function closePdpReviewModal() {
        document.getElementById('pdpReviewModal')?.classList.add('hidden');
    }

    function setPdpModalRating(rating) {
        const input = document.getElementById('pdpModalRatingValue');
        if (input) input.value = rating;

        const labels = {
            1: 'Terrible (1/5)',
            2: 'Bad (2/5)',
            3: 'Average (3/5)',
            4: 'Good (4/5)',
            5: 'Excellent (5/5)'
        };
        const desc = document.getElementById('pdpRatingDescriptor');
        if (desc) desc.textContent = labels[rating] || (rating + '/5');

        document.querySelectorAll('.pdp-modal-star-btn').forEach(btn => {
            const star = parseInt(btn.getAttribute('data-star'));
            const icon = btn.querySelector('i');
            if (star <= rating) {
                btn.classList.add('text-amber-400');
                btn.classList.remove('text-slate-300', 'dark:text-slate-600');
                if (icon) {
                    icon.classList.remove('fa-regular');
                    icon.classList.add('fa-solid');
                }
            } else {
                btn.classList.remove('text-amber-400');
                btn.classList.add('text-slate-300', 'dark:text-slate-600');
                if (icon) {
                    icon.classList.remove('fa-solid');
                    icon.classList.add('fa-regular');
                }
            }
        });
    }

    function openPdpLightbox(imgUrl) {
        const modal = document.getElementById('pdpLightboxModal');
        const img = document.getElementById('pdpLightboxImage');
        if (modal && img) {
            img.src = imgUrl;
            modal.classList.remove('hidden');
        }
    }

    function closePdpLightbox() {
        document.getElementById('pdpLightboxModal')?.classList.add('hidden');
    }
</script>
@endpush
@endsection
