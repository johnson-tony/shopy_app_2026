@extends('user.layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">

    <!-- Breadcrumb & Step Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">Home</a>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                <a href="{{ route('cart.index', ['mode' => $cart->mode?->slug]) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">Cart</a>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                <span class="text-slate-900 dark:text-white font-semibold">Checkout</span>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1 flex items-center gap-2.5">
                <i class="fa-solid fa-shield-check text-emerald-600"></i>
                <span>Secure Checkout</span>
            </h1>
        </div>

        <!-- Checkout Steps Pill -->
        <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800/80 p-1.5 rounded-2xl text-xs font-semibold">
            <span class="px-3 py-1 rounded-xl bg-white dark:bg-slate-700 text-slate-400 dark:text-slate-400 line-through">1. Cart</span>
            <i class="fa-solid fa-arrow-right text-[10px] text-slate-400"></i>
            <span class="px-3 py-1 rounded-xl bg-indigo-600 text-white shadow-xs">2. Order &amp; Delivery</span>
            <i class="fa-solid fa-arrow-right text-[10px] text-slate-400"></i>
            <span class="px-3 py-1 rounded-xl text-slate-400">3. Confirmation</span>
        </div>
    </div>

    <!-- Main Checkout Form -->
    <form action="{{ route('checkout.place_order') }}" method="POST" id="checkoutForm" class="space-y-8">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Column: Delivery Address & Payment Method Selection (7/12 Cols) -->
            <div class="lg:col-span-7 space-y-8">

                <!-- 1. Delivery Address Card -->
                <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-sm space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-sm">1</span>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white">Delivery Address</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Where should we deliver your order?</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('user.addresses.create', ['return_to' => 'checkout']) }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Add New Address</span>
                            </a>
                            <a href="{{ route('user.addresses.index') }}" 
                               target="_blank"
                               class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition" 
                               title="Manage in Profile Address Book">
                                <i class="fa-solid fa-gear text-xs"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Hidden address source input -->
                    <input type="hidden" name="address_source" value="existing">

                    <!-- Saved Addresses Selection -->
                    @if($addresses->count() > 0)
                        <div class="space-y-3">
                            <p class="text-xs text-slate-500 dark:text-slate-400">Select a delivery address from your profile:</p>
                            <div class="grid grid-cols-1 gap-3">
                                @foreach($addresses as $addr)
                                    <label class="relative flex items-start gap-4 p-4 rounded-2xl border-2 cursor-pointer transition select-none {{ (old('address_id', $defaultAddress?->id) == $addr->id) ? 'border-indigo-600 bg-indigo-50/40 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600' }}">
                                        <input type="radio" 
                                               name="address_id" 
                                               value="{{ $addr->id }}" 
                                               class="mt-1 text-indigo-600 focus:ring-indigo-500" 
                                               {{ (old('address_id', $defaultAddress?->id) == $addr->id) ? 'checked' : '' }}
                                               onchange="highlightSelectedAddress(this)">
                                        
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                                <span class="font-bold text-sm text-slate-900 dark:text-white">{{ $addr->full_name }}</span>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                                    {{ $addr->address_type ?? 'Home' }}
                                                </span>
                                                @if($addr->is_default)
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400">
                                                        Default
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                                                {{ $addr->formatted_address }}
                                            </p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5">
                                                <i class="fa-solid fa-phone text-[10px]"></i>
                                                <span>{{ $addr->phone }}</span>
                                            </p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('address_id')
                                <p class="text-xs text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1.5 font-medium">
                                    <i class="fa-solid fa-circle-exclamation text-xs"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>
                    @else
                        <div class="p-6 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-center space-y-3">
                            <div class="w-12 h-12 mx-auto rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-base text-slate-900 dark:text-white">Delivery Address Required</h3>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">You must save at least one delivery address in your profile before placing an order.</p>
                            </div>
                            <a href="{{ route('user.addresses.create', ['return_to' => 'checkout']) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition">
                                <i class="fa-solid fa-plus text-xs"></i>
                                <span>Add Address to Profile</span>
                            </a>
                        </div>
                    @endif
                </div>

                <!-- 2. Payment Method Selector (Mock / Cash on Delivery without Gateway Credentials) -->
                <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-sm space-y-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-700">
                        <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-sm">2</span>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">Select Payment Method</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Safe and flexible payment options</p>
                        </div>
                    </div>

                    <!-- Payment Options -->
                    <div class="space-y-3">
                        <!-- COD / Pay on Delivery (Pre-selected) -->
                        <label class="relative flex items-start gap-4 p-4 rounded-2xl border-2 border-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/30 cursor-pointer transition select-none payment-option-card">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="pay_on_delivery" 
                                   checked 
                                   class="mt-1 text-indigo-600 focus:ring-indigo-500" 
                                   onchange="updatePaymentCardHighlight(this)">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-hand-holding-dollar text-indigo-600 dark:text-indigo-400 text-lg"></i>
                                        <span class="font-bold text-sm text-slate-900 dark:text-white">Pay on Delivery (Cash / Doorstep UPI)</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400">
                                        Recommended
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Pay securely at your doorstep via Cash or scan delivery partner's QR code. No advance payment required.
                                </p>
                            </div>
                        </label>

                        <!-- Mock UPI -->
                        <label class="relative flex items-start gap-4 p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 cursor-pointer transition select-none payment-option-card">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="mock_upi" 
                                   class="mt-1 text-indigo-600 focus:ring-indigo-500" 
                                   onchange="updatePaymentCardHighlight(this)">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-qrcode text-indigo-600 dark:text-indigo-400 text-lg"></i>
                                        <span class="font-bold text-sm text-slate-900 dark:text-white">UPI / Instant QR (Test / Mock Mode)</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400">
                                        Instant Confirm
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Google Pay, PhonePe, Paytm, BHIM. (Payment gateway in simulation mode; order confirms immediately without deducting money).
                                </p>
                            </div>
                        </label>

                        <!-- Mock Credit / Debit Card -->
                        <label class="relative flex items-start gap-4 p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 cursor-pointer transition select-none payment-option-card">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="mock_card" 
                                   class="mt-1 text-indigo-600 focus:ring-indigo-500" 
                                   onchange="updatePaymentCardHighlight(this)">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-regular fa-credit-card text-indigo-600 dark:text-indigo-400 text-lg"></i>
                                        <span class="font-bold text-sm text-slate-900 dark:text-white">Credit / Debit Card (Test Mode)</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400">
                                        Visa / MC / RuPay
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Simulate card checkout. Instant approval without connecting third-party gateway credentials.
                                </p>
                            </div>
                        </label>
                    </div>

                    <!-- Delivery Instructions / Order Notes -->
                    <div class="pt-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Delivery Instructions or Order Notes (Optional)
                        </label>
                        <input type="text" 
                               name="notes" 
                               value="{{ old('notes') }}" 
                               placeholder="e.g. Leave package with security guard, or call before delivery" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <!-- 3. Review Order Items Preview -->
                <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-sm">3</span>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white">Items in this Order ({{ $cart->totalQuantity() }})</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Review selected quantities and variants</p>
                            </div>
                        </div>
                        <a href="{{ route('cart.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                            Edit Cart
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @foreach($cart->items as $item)
                            <div class="py-3.5 flex items-center gap-4">
                                <img src="{{ $item->product?->image_url ?? 'https://placehold.co/100x100' }}" 
                                     alt="{{ $item->product?->name }}" 
                                     class="w-14 h-14 object-cover rounded-xl border border-slate-200 dark:border-slate-700 shrink-0">
                                
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-slate-900 dark:text-white truncate">
                                        {{ $item->product?->name ?? 'Product' }}
                                    </h4>
                                    <div class="flex flex-wrap items-center gap-2 mt-1">
                                        @if($item->color)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded-md">
                                                Color: <strong>{{ $item->color }}</strong>
                                            </span>
                                        @endif
                                        @if($item->size)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded-md">
                                                Size: <strong>{{ $item->size }}</strong>
                                            </span>
                                        @endif
                                        @if($item->product?->restaurant)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                                <i class="fa-solid fa-utensils text-[9px] text-amber-500"></i>
                                                <span>{{ $item->product->restaurant->name }}</span>
                                            </span>
                                        @endif
                                        @if($item->hasAddons())
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-md border border-amber-200 dark:border-amber-800/40">
                                                <i class="fa-solid fa-plus-circle text-[9px]"></i>
                                                <span>{{ $item->formattedAddons() }}</span>
                                            </span>
                                        @endif
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                            Qty: <strong>{{ $item->quantity }}</strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="text-sm font-bold text-slate-900 dark:text-white block">
                                        ₹{{ number_format($item->unit_price * $item->quantity, 2) }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">
                                        ₹{{ number_format($item->unit_price, 2) }} each
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column: Price Summary & Place Order Sticky Card (5/12 Cols) -->
            <div class="lg:col-span-5 space-y-6 sticky top-24">
                
                <!-- Order Summary Card -->
                <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-sm space-y-5">
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight pb-3 border-b border-slate-100 dark:border-slate-700">
                        Order Summary
                    </h3>

                    <div class="space-y-3 text-sm">
                        <!-- Subtotal -->
                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                            <span>Item Subtotal</span>
                            <span class="font-semibold text-slate-900 dark:text-white">₹{{ number_format($subtotal, 2) }}</span>
                        </div>

                        <!-- Delivery Fee -->
                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                            <span class="flex items-center gap-1.5">
                                <span>Delivery Fee</span>
                                @if($deliveryFee == 0)
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">FREE</span>
                                @endif
                            </span>
                            <span class="font-semibold {{ $deliveryFee == 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }}">
                                {{ $deliveryFee == 0 ? 'FREE' : '₹' . number_format($deliveryFee, 2) }}
                            </span>
                        </div>

                        <!-- Taxes -->
                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                            <span>Estimated GST (5%)</span>
                            <span class="font-semibold text-slate-900 dark:text-white">₹{{ number_format($taxAmount, 2) }}</span>
                        </div>

                        <!-- Coupon Discount -->
                        @if($discountAmount > 0)
                            <div class="flex items-center justify-between text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 p-2.5 rounded-xl border border-emerald-100 dark:border-emerald-900/50">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-tag text-xs"></i>
                                    <span>Coupon Discount ({{ $cart->coupon_code }})</span>
                                </span>
                                <span class="font-bold">-₹{{ number_format($discountAmount, 2) }}</span>
                            </div>
                        @endif

                        <!-- Grand Total Divider -->
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex items-baseline justify-between">
                            <div>
                                <span class="text-base font-black text-slate-900 dark:text-white block">Total Amount</span>
                                <span class="text-[11px] text-slate-400 block">Inclusive of all taxes</span>
                            </div>
                            <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400 tracking-tight">
                                ₹{{ number_format($grandTotal, 2) }}
                            </span>
                        </div>
                    </div>

                    <!-- Place Order Button -->
                    <div class="pt-2">
                        @if($addresses->count() > 0)
                            <button type="submit" 
                                    id="placeOrderBtn"
                                    class="w-full py-4 px-6 rounded-2xl font-black text-base text-white bg-indigo-600 hover:bg-indigo-700 active:scale-[0.99] transition shadow-lg shadow-indigo-500/25 flex items-center justify-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-lock text-sm"></i>
                                <span>Confirm &amp; Place Order</span>
                                <i class="fa-solid fa-arrow-right text-xs ml-1"></i>
                            </button>
                        @else
                            <a href="{{ route('user.addresses.create', ['return_to' => 'checkout']) }}" 
                               class="w-full py-4 px-6 rounded-2xl font-black text-base text-white bg-amber-600 hover:bg-amber-700 active:scale-[0.99] transition shadow-lg shadow-amber-500/25 flex items-center justify-center gap-2 cursor-pointer text-center">
                                <i class="fa-solid fa-location-dot text-sm"></i>
                                <span>Add Delivery Address to Order</span>
                            </a>
                        @endif
                        <p class="text-center text-[11px] text-slate-400 mt-2">
                            By placing order you agree to the Terms of Service
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function highlightSelectedAddress(radioEl) {
        document.querySelectorAll('input[name="address_id"]').forEach(input => {
            const label = input.closest('label');
            if (label) {
                label.classList.remove('border-indigo-600', 'bg-indigo-50/40', 'dark:bg-indigo-950/30');
                label.classList.add('border-slate-200', 'dark:border-slate-700');
            }
        });
        const parentLabel = radioEl.closest('label');
        if (parentLabel) {
            parentLabel.classList.remove('border-slate-200', 'dark:border-slate-700');
            parentLabel.classList.add('border-indigo-600', 'bg-indigo-50/40', 'dark:bg-indigo-950/30');
        }
    }

    function updatePaymentCardHighlight(radioEl) {
        document.querySelectorAll('.payment-option-card').forEach(card => {
            card.classList.remove('border-indigo-600', 'bg-indigo-50/30', 'dark:bg-indigo-950/30');
            card.classList.add('border-slate-200', 'dark:border-slate-700');
        });
        const parent = radioEl.closest('.payment-option-card');
        if (parent) {
            parent.classList.remove('border-slate-200', 'dark:border-slate-700');
            parent.classList.add('border-indigo-600', 'bg-indigo-50/30', 'dark:bg-indigo-950/30');
        }
    }

    document.getElementById('checkoutForm')?.addEventListener('submit', function(e) {
        const btn = document.getElementById('placeOrderBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Placing Order...';
        }
    });
</script>
@endpush
@endsection
