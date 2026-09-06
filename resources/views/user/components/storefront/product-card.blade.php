@php
    $hasDiscount = $product->is_on_sale;
    $currentPrice = $hasDiscount ? $product->sale_price : $product->price;
    $originalPrice = $hasDiscount ? $product->price : null;
    $discountPercent = $product->discount_percentage ?? 0;
    $imageUrl = $product->image_url ?? 'https://placehold.co/400x400/e2e8f0/475569?text=' . urlencode(substr($product->name, 0, 8));
    $inStock = ($product->stock ?? 0) > 0;
    $isWishlisted = Auth::guard('web')->check() && $product->isWishlistedBy(Auth::guard('web')->user());
@endphp

<div class="product-card" 
     data-product-id="{{ $product->id }}"
     data-product-name="{{ $product->name }}"
     data-product-price="₹{{ number_format($currentPrice, 2) }}"
     data-product-compare="{{ $hasDiscount ? '₹' . number_format($originalPrice, 2) : '' }}"
     data-product-image="{{ $imageUrl }}"
     data-product-category="{{ $product->category?->name ?? 'Store Item' }}"
     data-product-mode="{{ $product->mode?->name ?? 'Store' }}"
     data-product-delivery="{{ $product->deliveryEstimate() }}"
     data-product-delivery-icon="{{ $product->deliveryIcon() }}"
     data-product-description="{{ $product->short_description ?? $product->description ?? 'Premium authentic product available on Shopy.' }}"
     data-product-stock="{{ $product->stock ?? 0 }}">
    
    <!-- Image Wrapper with Badges -->
    <div class="product-image-wrapper">
        <a href="{{ route('product.show', $product->slug) }}" class="product-img-container" title="{{ $product->name }}">
            <img src="{{ $imageUrl }}" 
                 alt="{{ $product->name }}" 
                 loading="lazy"
                 onerror="this.onerror=null;this.src='https://placehold.co/400x400/e2e8f0/475569?text=Product';">
        </a>

        @if($hasDiscount || $discountPercent > 0)
            <div class="discount-badge">
                {{ $discountPercent }}% OFF
            </div>
        @endif

        <!-- Minimal Clean Top-Notch Delivery Timing Pill -->
        <div class="delivery-eta-badge" title="Estimated delivery: {{ $product->deliveryEstimate() }}">
            <i class="{{ $product->deliveryIcon() }}"></i>
            <span>{{ strtoupper($product->deliveryEstimate()) }}</span>
        </div>

        <button type="button" 
                class="wishlist-btn shopy-wishlist-btn {{ $isWishlisted ? 'active text-rose-500' : 'text-slate-400' }}" 
                data-product-id="{{ $product->id }}"
                data-wishlisted="{{ $isWishlisted ? 'true' : 'false' }}"
                title="{{ $isWishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}"
                onclick="event.stopPropagation(); toggleWishlist({{ $product->id }}, this);">
            <i class="{{ $isWishlisted ? 'fa-solid fa-heart text-rose-500' : 'fa-regular fa-heart' }}"></i>
        </button>
    </div>

    <!-- Product Details -->
    <div class="product-details">
        <div class="product-category-sub flex items-center justify-between">
            <span class="truncate">{{ $product->restaurant ? $product->restaurant->name : ($product->category?->name ?? 'General') }}</span>
            @if($product->mode)
                <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500 shrink-0">
                    {{ $product->mode->name }}
                </span>
            @endif
        </div>

        <div class="product-title flex items-start gap-1.5">
            @if($product->is_veg !== null)
                <span class="inline-flex items-center justify-center w-3.5 h-3.5 border {{ $product->is_veg ? 'border-emerald-600' : 'border-rose-600' }} p-0.5 rounded-xs shrink-0 mt-0.5" title="{{ $product->is_veg ? 'Pure Veg' : 'Non-Veg' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $product->is_veg ? 'bg-emerald-600' : 'bg-rose-600' }}"></span>
                </span>
            @endif
            <a href="{{ route('product.show', $product->slug) }}" class="flex-1 min-w-0">
                <h3 title="{{ $product->name }}" class="truncate">{{ $product->name }}</h3>
            </a>
        </div>

        <!-- Rating Stars -->
        <div class="rating flex items-center justify-between">
            <div class="flex items-center gap-0.5">
                <i class="fas fa-star text-amber-400 text-xs"></i>
                <i class="fas fa-star text-amber-400 text-xs"></i>
                <i class="fas fa-star text-amber-400 text-xs"></i>
                <i class="fas fa-star text-amber-400 text-xs"></i>
                <i class="fas fa-star-half-stroke text-amber-400 text-xs"></i>
                <span class="rating-value text-xs ml-1 font-semibold">({{ number_format($product->averageRating(), 1) }})</span>
            </div>
            @if($product->hasAddons())
                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300">
                    Customisable
                </span>
            @endif
        </div>

        <!-- Price Section -->
        <div class="price-wrapper">
            <div class="price">
                <span class="current-price">₹{{ number_format($currentPrice, 2) }}</span>
                @if($hasDiscount)
                    <span class="old-price">₹{{ number_format($originalPrice, 2) }}</span>
                @endif
            </div>
        </div>

        <!-- Add to Cart Action -->
        @if($inStock)
            @if($product->hasAddons())
                <a href="{{ route('product.show', $product->slug) }}" 
                   class="add-to-cart"
                   style="border-color: #f59e0b; color: #d97706;"
                   onmouseover="this.style.backgroundColor='#f59e0b'; this.style.color='#ffffff';"
                   onmouseout="this.style.backgroundColor='transparent'; this.style.color='#d97706';">
                    <i class="fas fa-sliders text-xs"></i> Customise
                </a>
            @else
                <button type="button" 
                        class="add-to-cart"
                        onclick="window.addToCart({{ $product->id }}, 1, this)">
                    <i class="fas fa-bag-shopping text-xs"></i> Add to Cart
                </button>
            @endif
        @else
            <button type="button" class="add-to-cart out-of-stock" disabled>
                Out of Stock
            </button>
        @endif
    </div>
</div>
