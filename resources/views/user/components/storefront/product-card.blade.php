@php
    $hasDiscount = $product->is_on_sale;
    $currentPrice = $hasDiscount ? $product->sale_price : $product->price;
    $originalPrice = $hasDiscount ? $product->price : null;
    $discountPercent = $product->discount_percentage ?? 0;
    $imageUrl = $product->image_url ?? 'https://placehold.co/400x400/e2e8f0/475569?text=' . urlencode(substr($product->name, 0, 8));
    $inStock = ($product->stock ?? 0) > 0;
@endphp

<div class="product-card" 
     data-product-id="{{ $product->id }}"
     data-product-name="{{ $product->name }}"
     data-product-price="₹{{ number_format($currentPrice, 2) }}"
     data-product-compare="{{ $hasDiscount ? '₹' . number_format($originalPrice, 2) : '' }}"
     data-product-image="{{ $imageUrl }}"
     data-product-category="{{ $product->category?->name ?? 'Store Item' }}"
     data-product-mode="{{ $product->mode?->name ?? 'Store' }}"
     data-product-description="{{ $product->short_description ?? $product->description ?? 'Premium authentic product available on Shopy.' }}"
     data-product-stock="{{ $product->stock ?? 0 }}">
    
    <!-- Image Wrapper with Badges -->
    <div class="product-image-wrapper">
        <a href="javascript:void(0)" class="product-img-container quick-view-trigger" title="Quick view {{ $product->name }}">
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

        @if($product->mode)
            <div class="mode-tag-badge">
                @if($product->mode->icon)<i class="{{ $product->mode->icon }} mr-1"></i>@endif
                {{ $product->mode->name }}
            </div>
        @endif

        <button type="button" 
                class="wishlist-btn shopy-wishlist-btn" 
                title="Add to wishlist"
                onclick="event.stopPropagation(); if(typeof toastr !== 'undefined') toastr.info('Added {{ addslashes($product->name) }} to Wishlist');">
            <i class="far fa-heart"></i>
        </button>
    </div>

    <!-- Product Details -->
    <div class="product-details">
        <div class="product-category-sub">
            {{ $product->category?->name ?? 'General' }}
        </div>

        <div class="product-title">
            <h3 title="{{ $product->name }}">{{ $product->name }}</h3>
        </div>

        <!-- Rating Stars -->
        <div class="rating">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-stroke"></i>
            <span class="rating-value">(4.8)</span>
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
            <button type="button" 
                    class="add-to-cart"
                    onclick="if(typeof toastr !== 'undefined') toastr.success('{{ addslashes($product->name) }} added to cart!');">
                <i class="fas fa-bag-shopping text-xs"></i> Add to Cart
            </button>
        @else
            <button type="button" class="add-to-cart out-of-stock" disabled>
                Out of Stock
            </button>
        @endif
    </div>
</div>
