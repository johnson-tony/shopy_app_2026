@extends('user.layouts.app')

@section('title', ($cart->mode ? $cart->mode->name . ' Cart' : 'My Cart') . ' — Checkout')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
        @php
            $currentModeModel = $cart->mode ?? $modes->firstWhere('slug', $selectedMode);
            $cartTitle = $currentModeModel ? "{$currentModeModel->name} Cart" : 'Shopping Cart';
        @endphp
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('home') }}" class="hover:text-indigo-600 transition">Home</a>
                @if($currentModeModel)
                    <span>/</span>
                    <a href="{{ route('home', ['mode' => $currentModeModel->slug]) }}" class="hover:text-indigo-600 transition">{{ $currentModeModel->name }}</a>
                @endif
                <span>/</span>
                <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $cartTitle }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                <span class="text-indigo-600">🛒</span>
                <span>{{ $cartTitle }}</span>
                <span class="text-sm font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400" id="cartItemsBadge">
                    {{ $cart->totalQuantity() }} {{ Str::plural('item', $cart->totalQuantity()) }}
                </span>
            </h1>
        </div>

        @if($cart->items->count() > 0)
            <form method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('Are you sure you want to clear your entire cart?');">
                @csrf
                <input type="hidden" name="mode" value="{{ $cart->mode?->slug ?? $selectedMode }}">
                <button type="submit" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 rounded-xl border border-rose-200 dark:border-rose-900/50 transition cursor-pointer">
                    <i class="fa-regular fa-trash-can"></i>
                    <span>Clear Cart</span>
                </button>
            </form>
        @endif
    </div>

    <!-- Mode Filter Tabs (Only show modes that have items > 0 or currently active mode) -->
    @php
        $modesWithCartItems = $modes->filter(function($m) use ($modeItemCounts, $selectedMode) {
            return ($modeItemCounts[$m->slug] ?? 0) > 0 || $selectedMode === $m->slug;
        });
    @endphp

    @if($modesWithCartItems->count() > 1)
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
            @foreach($modesWithCartItems as $m)
                @php
                    $qtyForMode = $modeItemCounts[$m->slug] ?? 0;
                    $isActive = $selectedMode === $m->slug;
                @endphp
                <a href="{{ route('cart.index', ['mode' => $m->slug]) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $isActive ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    @if($m->icon)<i class="{{ $m->icon }}"></i>@endif
                    <span>{{ $m->name }}</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                        {{ $qtyForMode }}
                    </span>
                </a>
            @endforeach
        </div>
    @endif

    @if($cart->items->count() > 0)
        <!-- Free Delivery Meter -->
        @php
            $freeDeliveryProgress = min(100, round(($subtotal / max(1, $freeDeliveryThreshold)) * 100));
        @endphp
        <div class="p-4 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40">
            <div class="flex items-center justify-between text-xs font-semibold mb-2">
                <span class="text-indigo-900 dark:text-indigo-200 flex items-center gap-1.5">
                    <i class="fa-solid fa-truck-fast text-indigo-600"></i>
                    @if($amountNeededForFreeDelivery <= 0)
                        <span>Congratulations! You qualify for <strong>FREE Delivery</strong></span>
                    @else
                        <span>Add <strong>₹{{ number_format($amountNeededForFreeDelivery, 2) }}</strong> more for <strong>FREE Delivery</strong></span>
                    @endif
                </span>
                <span class="text-slate-500 dark:text-slate-400">Threshold: ₹{{ number_format($freeDeliveryThreshold, 0) }}</span>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-500 to-emerald-500 h-2 rounded-full transition-all duration-500" 
                     id="freeDeliveryProgressBar"
                     style="width: {{ $freeDeliveryProgress }}%"></div>
            </div>
        </div>

        <!-- Main Cart Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left 8 Cols: Cart Line Items -->
            <div class="lg:col-span-8 space-y-4" id="cartItemsList">
                @foreach($cart->items as $item)
                    @php
                        $p = $item->product;
                        $inStock = $p && $p->status && $p->stock > 0;
                        $isLowStock = $inStock && $p->stock <= 5;
                        $lineTotal = $item->lineTotal();
                        $hasDiscount = $p?->is_on_sale;
                    @endphp
                    <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 shadow-xs flex flex-col sm:flex-row items-start sm:items-center gap-4 transition" 
                         id="cart-item-row-{{ $p->id }}">
                        
                        <!-- Thumbnail Image -->
                        <div class="relative w-20 h-20 sm:w-24 sm:h-24 shrink-0 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600">
                            <img src="{{ $p->image_url ?? 'https://placehold.co/200x200/e2e8f0/475569?text=' . urlencode(substr($p->name, 0, 6)) }}" 
                                 alt="{{ $p->name }}"
                                 class="w-full h-full object-cover">
                            
                            @if($p->mode)
                                <span class="absolute top-1 left-1 text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-slate-900/80 text-white backdrop-blur-xs">
                                    {{ $p->mode->name }}
                                </span>
                            @endif
                        </div>

                        <!-- Product Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">
                                    {{ $p->category?->name ?? 'General' }}
                                </span>
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                    <i class="{{ $p->deliveryIcon() }} text-[9px]"></i>
                                    <span>{{ $p->deliveryEstimate() }}</span>
                                </span>
                                @if($isLowStock)
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50">
                                        Only {{ $p->stock }} left
                                    </span>
                                @endif
                            </div>

                            <h3 class="font-bold text-sm sm:text-base text-slate-900 dark:text-white truncate">
                                {{ $p->name }}
                            </h3>

                            <div class="flex items-baseline gap-2 mt-1">
                                <span class="text-base font-bold text-slate-900 dark:text-white">
                                    ₹{{ number_format((float) $item->unit_price, 2) }}
                                </span>
                                @if($hasDiscount)
                                    <span class="text-xs text-slate-400 line-through">
                                        ₹{{ number_format((float) $p->price, 2) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Quantity Stepper & Line Total -->
                        <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100 dark:border-slate-700/60">
                            <!-- Stepper -->
                            <div class="flex items-center border border-slate-200 dark:border-slate-600 rounded-xl overflow-hidden bg-slate-50 dark:bg-slate-700/50">
                                <button type="button" 
                                        onclick="updateCartItemQty({{ $p->id }}, {{ $item->quantity - 1 }})"
                                        class="w-8 h-8 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition cursor-pointer font-bold text-sm">
                                    <i class="fa-solid fa-minus text-xs"></i>
                                </button>
                                <span class="w-10 text-center font-bold text-xs text-slate-900 dark:text-white" 
                                      id="cart-qty-{{ $p->id }}">
                                    {{ $item->quantity }}
                                </span>
                                <button type="button" 
                                        onclick="updateCartItemQty({{ $p->id }}, {{ $item->quantity + 1 }})"
                                        class="w-8 h-8 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition cursor-pointer font-bold text-sm {{ $item->quantity >= $p->stock ? 'opacity-40 cursor-not-allowed' : '' }}"
                                        {{ $item->quantity >= $p->stock ? 'disabled' : '' }}>
                                    <i class="fa-solid fa-plus text-xs"></i>
                                </button>
                            </div>

                            <!-- Line Subtotal -->
                            <div class="text-right">
                                <span class="text-xs text-slate-400 block sm:hidden">Total:</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white" id="cart-line-total-{{ $p->id }}">
                                    ₹{{ number_format($lineTotal, 2) }}
                                </span>
                            </div>

                            <!-- Remove Action -->
                            <button type="button" 
                                    onclick="removeCartItem({{ $p->id }}, '{{ addslashes($p->name) }}')"
                                    class="text-xs text-slate-400 hover:text-rose-600 transition p-1 cursor-pointer"
                                    title="Remove item">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Right 4 Cols: Order Summary & Checkout Card -->
            <div class="lg:col-span-4 space-y-4">
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-5">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700/80 pb-3 flex items-center justify-between">
                        <span>Order Summary</span>
                        @if($cart->mode)
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-400">
                                {{ $cart->mode->name }}
                            </span>
                        @endif
                    </h2>

                    <!-- Price Calculations Breakdown -->
                    <div class="space-y-3 text-sm">
                        <!-- Subtotal -->
                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                            <span>Subtotal ({{ $cart->totalQuantity() }} items)</span>
                            <span class="font-semibold text-slate-900 dark:text-white" id="summarySubtotal">
                                ₹{{ number_format($subtotal, 2) }}
                            </span>
                        </div>

                        <!-- Delivery Fee -->
                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                            <span class="flex items-center gap-1.5">
                                <span>Delivery Fee</span>
                                <i class="fa-solid fa-circle-info text-[11px] text-slate-400" title="{{ $cart->mode?->name }} Delivery"></i>
                            </span>
                            <span class="font-semibold {{ $deliveryFee <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }}" id="summaryDelivery">
                                {{ $deliveryFee <= 0 ? 'FREE' : '₹' . number_format($deliveryFee, 2) }}
                            </span>
                        </div>

                        <!-- Tax GST -->
                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                            <span>Estimated Tax (5% GST)</span>
                            <span class="font-semibold text-slate-900 dark:text-white" id="summaryTax">
                                ₹{{ number_format($taxAmount, 2) }}
                            </span>
                        </div>

                        <!-- Coupon Discount if applied -->
                        <div class="flex items-center justify-between text-emerald-600 dark:text-emerald-400 {{ $discountAmount > 0 ? '' : 'hidden' }}" id="summaryDiscountRow">
                            <span class="flex items-center gap-1">
                                <span>Discount</span>
                                <span class="text-[10px] px-1.5 py-0.2 bg-emerald-100 dark:bg-emerald-950/60 rounded font-bold" id="summaryCouponCode">
                                    {{ $cart->coupon_code }}
                                </span>
                            </span>
                            <span class="font-bold" id="summaryDiscount">
                                -₹{{ number_format($discountAmount, 2) }}
                            </span>
                        </div>

                        <div class="border-t border-slate-100 dark:border-slate-700/80 pt-3">
                            <div class="flex items-center justify-between text-base">
                                <span class="font-bold text-slate-900 dark:text-white">Grand Total</span>
                                <span class="text-xl font-black text-indigo-600 dark:text-indigo-400" id="summaryGrandTotal">
                                    ₹{{ number_format($grandTotal, 2) }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5 text-right">Inclusive of all taxes</p>
                        </div>
                    </div>

                    <!-- Coupon Box -->
                    <div class="border-t border-slate-100 dark:border-slate-700/80 pt-4">
                        @if($cart->coupon_code)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40 text-xs">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-tag text-emerald-600"></i>
                                    <span class="font-semibold text-emerald-800 dark:text-emerald-300">{{ $cart->coupon_code }} applied</span>
                                </div>
                                <form method="POST" action="{{ route('cart.coupon.remove') }}">
                                    @csrf
                                    <input type="hidden" name="mode" value="{{ $cart->mode?->slug ?? $selectedMode }}">
                                    <button type="submit" class="text-rose-600 font-bold hover:underline cursor-pointer">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        @else
                            <form method="POST" action="{{ route('cart.coupon.apply') }}" class="flex gap-2">
                                @csrf
                                <input type="hidden" name="mode" value="{{ $cart->mode?->slug ?? $selectedMode }}">
                                <input type="text" 
                                       id="couponCodeInput"
                                       name="coupon_code" 
                                       placeholder="Promo code (e.g. WELCOME50)" 
                                       class="flex-1 px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white uppercase focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                       required>
                                <button type="submit" 
                                        class="px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-indigo-600 hover:bg-slate-800 dark:hover:bg-indigo-700 rounded-xl transition cursor-pointer">
                                    Apply
                                </button>
                            </form>

                            @if(isset($availableCoupons) && $availableCoupons->count() > 0)
                                <div class="mt-2.5 pt-2 border-t border-dashed border-slate-200 dark:border-slate-700/60">
                                    <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                                        <span>Available Offers</span>
                                        <a href="{{ route('coupons.index', ['mode' => $cart->mode?->slug ?? $selectedMode]) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                            View all &rarr;
                                        </a>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($availableCoupons->take(3) as $c)
                                            <button type="button" 
                                                    onclick="document.getElementById('couponCodeInput').value='{{ $c->code }}';"
                                                    class="px-2 py-0.5 text-[10px] font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-700 hover:border-indigo-500 bg-slate-50 dark:bg-slate-700/50 text-slate-700 dark:text-slate-300 transition cursor-pointer"
                                                    title="{{ $c->name }} (Min: ₹{{ (int)$c->min_order_amount }})">
                                                🏷️ {{ $c->code }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Proceed to Checkout Button -->
                    <div class="pt-2">
                        @auth
                            <a href="{{ url('/checkout') }}" 
                               class="w-full py-3.5 px-6 rounded-2xl font-bold text-sm text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-500/25 transition flex items-center justify-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-shield-halved text-xs"></i>
                                <span>Proceed to Checkout</span>
                                <i class="fa-solid fa-arrow-right text-xs ml-1"></i>
                            </a>
                        @else
                            <a href="{{ route('login') }}" 
                               class="w-full py-3.5 px-6 rounded-2xl font-bold text-sm text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-500/25 transition flex items-center justify-center gap-2 cursor-pointer">
                                <span>Sign In to Checkout</span>
                                <i class="fa-solid fa-arrow-right text-xs ml-1"></i>
                            </a>
                        @endauth
                    </div>

                    <!-- Trust Badges -->
                    <div class="flex items-center justify-around pt-3 border-t border-slate-100 dark:border-slate-700/80 text-[11px] text-slate-400">
                        <span class="flex items-center gap-1"><i class="fa-solid fa-lock text-slate-400"></i> Secure Payment</span>
                        <span>•</span>
                        <span class="flex items-center gap-1"><i class="fa-solid fa-rotate-left text-slate-400"></i> Easy Returns</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="py-16 text-center max-w-md mx-auto">
            <div class="w-20 h-20 mx-auto mb-4 rounded-3xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-500 dark:text-indigo-400 flex items-center justify-center shadow-xs">
                <i class="fa-solid fa-cart-shopping text-3xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Your Cart is Empty</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5 max-w-sm mx-auto">
                @if($currentModeModel)
                    Browse {{ $currentModeModel->name }} products and click "Add to Cart" to start your order.
                @else
                    Explore our store and add some products to your cart.
                @endif
            </p>
            <div class="mt-6">
                <a href="{{ $currentModeModel ? route('home', ['mode' => $currentModeModel->slug]) : route('home') }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span>Start Shopping {{ $currentModeModel ? $currentModeModel->name : '' }}</span>
                </a>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function updateCartItemQty(productId, newQty) {
    fetch('{{ route("cart.update") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: newQty
        })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            if (typeof toastr !== 'undefined') toastr.error(data.message || 'Could not update quantity');
            return;
        }

        if (data.item_removed) {
            const row = document.getElementById('cart-item-row-' + productId);
            if (row) {
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '0';
                row.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    row.remove();
                    if (document.querySelectorAll('#cartItemsList > div').length === 0) {
                        window.location.reload();
                    }
                }, 300);
            }
        } else {
            const qtyEl = document.getElementById('cart-qty-' + productId);
            if (qtyEl) qtyEl.textContent = data.item_quantity;

            const lineTotalEl = document.getElementById('cart-line-total-' + productId);
            if (lineTotalEl) lineTotalEl.textContent = '₹' + data.line_total;
        }

        // Update Summary Card
        const subtotalEl = document.getElementById('summarySubtotal');
        if (subtotalEl) subtotalEl.textContent = '₹' + data.subtotal;

        const deliveryEl = document.getElementById('summaryDelivery');
        if (deliveryEl) deliveryEl.textContent = data.delivery_fee == '0.00' ? 'FREE' : '₹' + data.delivery_fee;

        const taxEl = document.getElementById('summaryTax');
        if (taxEl) taxEl.textContent = '₹' + data.tax;

        const grandTotalEl = document.getElementById('summaryGrandTotal');
        if (grandTotalEl) grandTotalEl.textContent = '₹' + data.grand_total;

        // Update Navbar Badges
        const cartBadge = document.getElementById('cartCount');
        if (cartBadge) {
            cartBadge.textContent = data.cart_count;
            cartBadge.style.display = data.cart_count > 0 ? 'inline-block' : 'none';
        }
        const bottomBadge = document.getElementById('mobileBottomCartCount');
        if (bottomBadge) {
            bottomBadge.textContent = data.cart_count;
            bottomBadge.style.display = data.cart_count > 0 ? 'inline-block' : 'none';
        }

        const itemsBadge = document.getElementById('cartItemsBadge');
        if (itemsBadge) itemsBadge.textContent = data.cart_count + ' ' + (data.cart_count === 1 ? 'item' : 'items');
    })
    .catch(err => {
        console.error('Cart update error:', err);
    });
}

function removeCartItem(productId, productName) {
    if (!confirm('Remove "' + productName + '" from your cart?')) {
        return;
    }

    fetch('{{ url("/cart/item") }}/' + productId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById('cart-item-row-' + productId);
            if (row) {
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '0';
                row.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    row.remove();
                    if (document.querySelectorAll('#cartItemsList > div').length === 0) {
                        window.location.reload();
                    }
                }, 300);
            }

            // Update Summary Card
            const subtotalEl = document.getElementById('summarySubtotal');
            if (subtotalEl) subtotalEl.textContent = '₹' + data.subtotal;

            const deliveryEl = document.getElementById('summaryDelivery');
            if (deliveryEl) deliveryEl.textContent = data.delivery_fee == '0.00' ? 'FREE' : '₹' + data.delivery_fee;

            const taxEl = document.getElementById('summaryTax');
            if (taxEl) taxEl.textContent = '₹' + data.tax;

            const grandTotalEl = document.getElementById('summaryGrandTotal');
            if (grandTotalEl) grandTotalEl.textContent = '₹' + data.grand_total;

            // Update Navbar Badges
            const cartBadge = document.getElementById('cartCount');
            if (cartBadge) {
                cartBadge.textContent = data.cart_count;
                cartBadge.style.display = data.cart_count > 0 ? 'inline-block' : 'none';
            }
            const bottomBadge = document.getElementById('mobileBottomCartCount');
            if (bottomBadge) {
                bottomBadge.textContent = data.cart_count;
                bottomBadge.style.display = data.cart_count > 0 ? 'inline-block' : 'none';
            }

            if (typeof toastr !== 'undefined') toastr.info(data.message || 'Item removed from cart');
        }
    })
    .catch(err => {
        console.error('Cart remove error:', err);
    });
}
</script>
@endpush
@endsection
