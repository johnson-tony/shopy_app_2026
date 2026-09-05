@extends('user.layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
        @php
            $currentModeModel = $modes->firstWhere('slug', $selectedMode);
            $modeTitle = ($selectedMode && $selectedMode !== 'all' && $currentModeModel) 
                ? $currentModeModel->name . ' Wishlist' 
                : 'My Wishlist';
        @endphp
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('home') }}" class="hover:text-indigo-600 transition">Home</a>
                @if($currentModeModel && $selectedMode !== 'all')
                    <span>/</span>
                    <a href="{{ route('home', ['mode' => $currentModeModel->slug]) }}" class="hover:text-indigo-600 transition">{{ $currentModeModel->name }}</a>
                @endif
                <span>/</span>
                <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $modeTitle }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                <span class="text-rose-500">❤️</span>
                <span>My Wishlist</span>
                @if($currentModeModel && $selectedMode && $selectedMode !== 'all')
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800">
                        {{ $currentModeModel->name }}
                    </span>
                @endif
                <span class="text-sm font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400" id="wishlistItemsCount">
                    {{ $wishlistProducts->total() }} {{ Str::plural('item', $wishlistProducts->total()) }}
                </span>
            </h1>
        </div>

        @if($wishlistProducts->count() > 0)
            <form method="POST" action="{{ route('wishlist.clear') }}" onsubmit="return confirm('Are you sure you want to clear your entire wishlist?');">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 rounded-xl border border-rose-200 dark:border-rose-900/50 transition cursor-pointer">
                    <i class="fa-regular fa-trash-can"></i>
                    <span>Clear Wishlist</span>
                </button>
            </form>
        @endif
    </div>

    <!-- Mode Filter Tabs (Only show modes with items > 0, hiding empty 0-count stores) -->
    @php
        $modesWithItems = $modes->filter(function($mode) use ($modeCounts, $selectedMode) {
            return ($modeCounts[$mode->slug] ?? 0) > 0 || $selectedMode === $mode->slug;
        });
    @endphp

    @if(($totalCount ?? 0) > 0 && ($modesWithItems->count() > 1 || $selectedMode === 'all'))
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
            @foreach($modesWithItems as $mode)
                @php
                    $countForMode = $modeCounts[$mode->slug] ?? 0;
                    $isActive = $selectedMode === $mode->slug;
                @endphp
                <a href="{{ route('wishlist.index', ['mode' => $mode->slug]) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $isActive ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    @if($mode->icon)<i class="{{ $mode->icon }}"></i>@endif
                    <span>{{ $mode->name }}</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                        {{ $countForMode }}
                    </span>
                </a>
            @endforeach

            @if($modesWithItems->count() > 1)
                <!-- All Stores Tab -->
                <a href="{{ route('wishlist.index', ['mode' => 'all']) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $selectedMode === 'all' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    <span>All Stores</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $selectedMode === 'all' ? 'bg-white/20 text-white dark:bg-slate-900/20 dark:text-slate-900' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                        {{ $totalCount ?? 0 }}
                    </span>
                </a>
            @endif
        </div>
    @endif

    @if($wishlistProducts->count() > 0)
        <!-- Wishlist Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="wishlistGrid">
            @foreach($wishlistProducts as $product)
                @php
                    $hasDiscount = $product->is_on_sale;
                    $currentPrice = $hasDiscount ? $product->sale_price : $product->price;
                    $originalPrice = $hasDiscount ? $product->price : null;
                    $discountPercent = $product->discount_percentage ?? 0;
                    $imageUrl = $product->image_url ?? 'https://placehold.co/400x400/e2e8f0/475569?text=' . urlencode(substr($product->name, 0, 8));
                    $inStock = ($product->stock ?? 0) > 0;
                @endphp

                <div class="group relative bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs hover:shadow-md transition-all flex flex-col overflow-hidden" 
                     id="wishlist-card-{{ $product->id }}">
                    
                    <!-- Image Container -->
                    <div class="relative aspect-square w-full overflow-hidden bg-slate-50 dark:bg-slate-900">
                        <img src="{{ $imageUrl }}" 
                             alt="{{ $product->name }}" 
                             class="h-full w-full object-cover object-center group-hover:scale-105 transition-transform duration-300"
                             loading="lazy">

                        <!-- Badges -->
                        <div class="absolute top-2.5 left-2.5 flex flex-col gap-1.5">
                            @if($hasDiscount || $discountPercent > 0)
                                <span class="px-2 py-0.5 rounded-lg bg-emerald-600 text-white text-[11px] font-bold shadow-xs">
                                    {{ $discountPercent }}% OFF
                                </span>
                            @endif

                            @if($product->mode)
                                <span class="px-2 py-0.5 rounded-lg bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-semibold flex items-center gap-1 shadow-xs">
                                    @if($product->mode->icon)<i class="{{ $product->mode->icon }}"></i>@endif
                                    {{ $product->mode->name }}
                                </span>
                            @endif
                        </div>

                        <!-- Remove Button (Top Right) -->
                        <button type="button" 
                                class="absolute top-2.5 right-2.5 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 flex items-center justify-center shadow-md transition hover:scale-110 cursor-pointer"
                                onclick="removeFromWishlist({{ $product->id }}, '{{ addslashes($product->name) }}')"
                                title="Remove from wishlist">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <!-- Card Body -->
                    <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <div class="text-[11px] uppercase tracking-wider font-semibold text-indigo-600 dark:text-indigo-400 mb-1">
                                {{ $product->category?->name ?? 'Store Item' }}
                            </div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white line-clamp-2 leading-snug">
                                {{ $product->name }}
                            </h2>
                        </div>

                        <!-- Price & Stock -->
                        <div class="space-y-1">
                            <div class="flex items-baseline gap-2">
                                <span class="text-lg font-extrabold text-slate-900 dark:text-white">
                                    ₹{{ number_format($currentPrice, 2) }}
                                </span>
                                @if($hasDiscount)
                                    <span class="text-xs text-slate-400 line-through">
                                        ₹{{ number_format($originalPrice, 2) }}
                                    </span>
                                @endif
                            </div>

                            <div>
                                @if($inStock)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        In Stock ({{ $product->stock }} left)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-600 dark:text-rose-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Out of Stock
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60">
                            <button type="button" 
                                    class="w-full py-2 px-3 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center gap-1.5 transition shadow-xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                    {{ !$inStock ? 'disabled' : '' }}
                                    onclick="window.addToCart({{ $product->id }}, 1, this);">
                                <i class="fa-solid fa-cart-plus"></i>
                                <span>{{ $inStock ? 'Add to Cart' : 'Out of Stock' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $wishlistProducts->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="py-16 text-center max-w-md mx-auto">
            <div class="w-20 h-20 mx-auto mb-4 rounded-3xl bg-rose-50 dark:bg-rose-950/40 text-rose-500 dark:text-rose-400 flex items-center justify-center shadow-xs">
                <i class="fa-regular fa-heart text-3xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Your Wishlist is Empty</h2>
            @if($selectedMode && $selectedMode !== 'all' && $currentModeModel)
                <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 mt-1 uppercase tracking-wider">
                    {{ $currentModeModel->name }} Store
                </p>
            @endif
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5 max-w-sm mx-auto">
                {{ $selectedMode && $selectedMode !== 'all' && $currentModeModel ? "Browse {$currentModeModel->name} products and tap the heart icon to save your favorites." : 'Explore our store and tap the heart icon on any product to save your favorites here.' }}
            </p>
            <div class="mt-6">
                <a href="{{ $selectedMode && $selectedMode !== 'all' && $currentModeModel ? route('home', ['mode' => $currentModeModel->slug]) : route('home') }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span>Start Shopping</span>
                </a>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function removeFromWishlist(productId, productName) {
    if (!confirm('Remove "' + productName + '" from your wishlist?')) {
        return;
    }

    fetch('{{ url("/wishlist") }}/' + productId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const card = document.getElementById('wishlist-card-' + productId);
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    card.remove();
                    const remaining = document.querySelectorAll('#wishlistGrid > div').length;
                    const countEl = document.getElementById('wishlistItemsCount');
                    if (countEl) countEl.textContent = data.count + ' ' + (data.count === 1 ? 'item' : 'items');
                    
                    // Update header navbar badge
                    const navBadge = document.getElementById('wishlistCount');
                    if (navBadge) {
                        navBadge.textContent = data.count;
                        navBadge.style.display = data.count > 0 ? 'inline-block' : 'none';
                    }

                    if (remaining === 0) {
                        window.location.reload();
                    }
                }, 300);
            }
            if (typeof toastr !== 'undefined') {
                toastr.success(data.message || 'Item removed from wishlist');
            }
        }
    })
    .catch(err => {
        console.error('Wishlist remove error:', err);
    });
}
</script>
@endpush
@endsection
