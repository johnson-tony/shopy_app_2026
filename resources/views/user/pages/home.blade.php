@extends('user.layouts.app')

@section('title', ($currentMode ? $currentMode->name . ' — ' : '') . 'Online Shopping Store')

@section('content')
<div class="space-y-8">
    <!-- 1. Shopping Mode Switcher Pills Bar -->
    <div class="mode-switcher-bar shadow-xs">
        <a href="{{ route('home') }}" 
           class="mode-pill {{ empty($currentMode) ? 'active' : '' }}">
            <i class="fa-solid fa-store text-xs"></i>
            <span>All Stores</span>
        </a>

        @foreach($modes as $mode)
            <a href="{{ route('home', ['mode' => $mode->slug]) }}" 
               class="mode-pill {{ $currentMode?->slug === $mode->slug ? 'active' : '' }}">
                @if($mode->icon)
                    <i class="{{ $mode->icon }} text-xs"></i>
                @endif
                <span>{{ $mode->name }}</span>
            </a>
        @endforeach
    </div>

    <!-- 2. Hero Promotional Notch Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r {{ $currentMode?->slug === 'food' ? 'from-amber-600 via-orange-600 to-rose-600' : ($currentMode?->slug === 'minutes' ? 'from-emerald-600 via-teal-600 to-cyan-700' : 'from-indigo-600 via-blue-600 to-violet-700') }} p-8 sm:p-12 text-white shadow-xl">
        <div class="relative z-10 max-w-2xl space-y-4">
            <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-amber-300 animate-pulse"></span>
                @if($currentMode)
                    {{ $currentMode->name }} Channel Active
                @else
                    Unified Commerce 2026
                @endif
            </span>

            <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                @if($currentMode?->slug === 'food')
                    Hot &amp; Fresh Food Delivered Swiftly
                @elseif($currentMode?->slug === 'minutes')
                    Groceries &amp; Daily Essentials in Minutes
                @else
                    Find Everything You Need, All in One Place
                @endif
            </h1>

            <p class="text-white/80 text-sm sm:text-base leading-relaxed">
                @if($currentMode?->description)
                    {{ $currentMode->description }}
                @else
                    Discover top quality products across General Shopping, Food Ordering, and Quick Commerce groceries with lightning-fast delivery.
                @endif
            </p>

            <div class="pt-2 flex flex-wrap items-center gap-4">
                <a href="#featured-products" class="px-6 py-3 rounded-xl bg-white text-slate-900 font-bold text-sm hover:bg-slate-100 transition shadow-lg shadow-black/10">
                    Explore Products
                </a>
                <a href="#categories-section" class="px-6 py-3 rounded-xl bg-white/15 border border-white/20 text-white font-semibold text-sm hover:bg-white/25 transition backdrop-blur-md">
                    View Categories
                </a>
            </div>
        </div>

        <!-- Decorative background circles -->
        <div class="absolute -right-16 -bottom-16 w-80 h-80 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        <div class="absolute right-24 -top-12 w-64 h-64 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
    </div>

    <!-- 3. Shop by Category Section (Matching Demo E-Commerce) -->
    <div id="categories-section">
        @include('user.components.storefront.category-scroll', [
            'topCategories' => $topCategories,
            'currentMode' => $currentMode
        ])
    </div>

    <!-- 4. Best Sellers & Catalog Section (Matching Demo E-Commerce Tabs & Product Cards) -->
    <section class="new-products-section" id="featured-products">
        <div class="top-heading-wrapper">
            <div class="section-header new-section-header">
                <div class="header">
                    <h2>Best Sellers &amp; New Arrivals</h2>
                    <p>Curated top-selling products with exclusive discounts</p>
                </div>

                <!-- Category Filter Tabs -->
                <ul class="header-right new-category-tabs">
                    <li class="new-category-tab {{ empty($selectedCategory) ? 'active' : '' }}"
                        data-category-slug=""
                        onclick="window.location.href='{{ route('home', array_filter(['mode' => $currentMode?->slug])) }}'">
                        All Products
                    </li>
                    @foreach($tabCategories as $tabCat)
                        <li class="new-category-tab {{ $selectedCategory?->id === $tabCat->id ? 'active' : '' }}"
                            data-category-slug="{{ $tabCat->slug }}"
                            onclick="window.location.href='{{ route('home', array_filter(['mode' => $currentMode?->slug, 'category' => $tabCat->slug])) }}'">
                            {{ $tabCat->name }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Product Cards Grid / Horizontal Carousel -->
        <div class="new-scroll-wrapper" id="productsDisplayContainer">
            @include('user.components.storefront.product-grid', ['products' => $products])
        </div>
    </section>

    <!-- 5. Featured Products Spotlight (If Available) -->
    @if($featuredProducts->isNotEmpty())
        <section class="new-products-section border-t border-slate-200 dark:border-slate-800 pt-8">
            <div class="section-header">
                <div class="header">
                    <h2>Trending Spotlight Deals</h2>
                    <p>Hand-picked specials with verified customer ratings</p>
                </div>
                <a href="{{ route('home') }}" class="view-all">
                    See More Deals <i class="fas fa-arrow-right text-xs ml-1"></i>
                </a>
            </div>

            <div class="top-products-scroll">
                @foreach($featuredProducts as $product)
                    @include('user.components.storefront.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif
</div>

<!-- 6. Quick View Modal -->
@include('user.components.storefront.quick-view-modal')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Categories Horizontal Scroll Buttons
    const catLeftBtn = document.getElementById('categoryScrollLeft');
    const catRightBtn = document.getElementById('categoryScrollRight');
    const catContainer = document.getElementById('categoriesScrollContainer');

    if (catLeftBtn && catRightBtn && catContainer) {
        catLeftBtn.addEventListener('click', () => {
            catContainer.scrollBy({ left: -220, behavior: 'smooth' });
        });
        catRightBtn.addEventListener('click', () => {
            catContainer.scrollBy({ left: 220, behavior: 'smooth' });
        });
    }

    // 2. Quick View Modal Logic
    const modal = document.getElementById('productQuickViewModal');
    const closeModalBtn = document.getElementById('closeQuickViewModal');

    document.querySelectorAll('.quick-view-trigger').forEach(cardTrigger => {
        cardTrigger.addEventListener('click', function (e) {
            e.preventDefault();
            const card = this.closest('.product-card');
            if (!card) return;

            window.currentQuickViewProductId = card.dataset.productId;
            const cardWishlistBtn = card.querySelector('.wishlist-btn');
            const modalWishlistIcon = document.getElementById('modalWishlistIcon');
            if (modalWishlistIcon && cardWishlistBtn) {
                const isWishlisted = cardWishlistBtn.classList.contains('active');
                modalWishlistIcon.className = isWishlisted ? 'fa-solid fa-heart text-rose-500' : 'far fa-heart';
            }

            document.getElementById('modalProductName').textContent = card.dataset.productName || '';
            document.getElementById('modalProductCategory').textContent = card.dataset.productCategory || '';
            document.getElementById('modalProductMode').textContent = card.dataset.productMode || '';
            
            const deliveryText = card.dataset.productDelivery || '10-15 mins';
            const deliveryIcon = card.dataset.productDeliveryIcon || 'fa-solid fa-bolt';
            const modalDelivery = document.getElementById('modalProductDelivery');
            const modalDeliveryIcon = document.getElementById('modalProductDeliveryIcon');
            if (modalDelivery) modalDelivery.textContent = deliveryText;
            if (modalDeliveryIcon) modalDeliveryIcon.className = deliveryIcon + ' text-[10px]';

            document.getElementById('modalProductPrice').textContent = card.dataset.productPrice || '';
            document.getElementById('modalProductCompare').textContent = card.dataset.productCompare || '';
            document.getElementById('modalProductDescription').textContent = card.dataset.productDescription || '';
            
            const modalImg = document.getElementById('modalProductImage');
            if (modalImg) {
                modalImg.src = card.dataset.productImage || '';
            }

            modal.style.display = 'flex';
        });
    });

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    // Modal Add To Cart Click
    const modalAddToCartBtn = document.getElementById('modalAddToCartBtn');
    if (modalAddToCartBtn) {
        modalAddToCartBtn.addEventListener('click', () => {
            const name = document.getElementById('modalProductName').textContent;
            if (typeof toastr !== 'undefined') {
                toastr.success(name + ' added to cart!');
            }
            modal.style.display = 'none';
        });
    }
});
</script>
@endpush
@endsection
