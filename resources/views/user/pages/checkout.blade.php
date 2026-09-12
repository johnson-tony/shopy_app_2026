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

                    @php
                        $upiPayee = $upiId ?: 'shopy@upi';
                        $upiName = $upiMerchantName ?: 'Shopy Store';
                        $upiAmount = number_format($grandTotal, 2, '.', '');
                        $upiUri = "upi://pay?pa={$upiPayee}&pn=" . urlencode($upiName) . "&am={$upiAmount}&cu=INR&tn=" . urlencode("Order_{$cart->id}");
                        $qrCodeUrl = $upiQrImageUrl ?: "https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=" . urlencode($upiUri);
                    @endphp

                    <!-- Payment Options List -->
                    <div class="space-y-3">
                        @if($isCodEnabled)
                            <!-- COD / Pay on Delivery -->
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
                                            <span class="font-bold text-sm text-slate-900 dark:text-white">Cash on Delivery (Pay on Doorstep)</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400">
                                            Recommended
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        Pay at your doorstep via Cash or scan delivery partner's QR code. No advance payment required.
                                    </p>
                                </div>
                            </label>
                        @endif

                        @if($isUpiEnabled)
                            <!-- UPI / PhonePe / GPay / Instant QR -->
                            <label class="relative flex items-start gap-4 p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 cursor-pointer transition select-none payment-option-card">
                                <input type="radio" 
                                       name="payment_method" 
                                       value="mock_upi" 
                                       {{ !$isCodEnabled ? 'checked' : '' }}
                                       class="mt-1 text-indigo-600 focus:ring-indigo-500" 
                                       onchange="updatePaymentCardHighlight(this)">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-qrcode text-violet-600 dark:text-violet-400 text-lg"></i>
                                            <span class="font-bold text-sm text-slate-900 dark:text-white">UPI &amp; Instant QR (PhonePe / GPay / Paytm)</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-100 dark:bg-violet-950/60 text-violet-700 dark:text-violet-300">
                                            Instant Pay
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        One-tap open in PhonePe, Google Pay, Paytm, or scan QR code directly.
                                    </p>
                                </div>
                            </label>

                            <!-- Collapsible UPI Deep-Link & QR Interface -->
                            <div id="upiPaymentPanel" class="p-5 rounded-2xl border border-violet-200 dark:border-violet-900/60 bg-violet-50/50 dark:bg-violet-950/20 space-y-4 {{ $isCodEnabled ? 'hidden' : '' }}">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-violet-900 dark:text-violet-300 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="fa-solid fa-mobile-screen-button"></i> Mobile 1-Tap UPI Launch
                                    </span>
                                    <span class="text-xs font-black text-violet-600 dark:text-violet-400">
                                        Amount: ₹{{ number_format($grandTotal, 2) }}
                                    </span>
                                </div>

                                <!-- Mobile Deep-Link Trigger -->
                                <div class="space-y-2">
                                    <a href="{{ $upiUri }}" class="w-full py-3 px-4 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-sm shadow-md shadow-violet-500/20 flex items-center justify-center gap-2 text-center transition active:scale-[0.99]">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        <span>Tap to Pay with Any UPI App</span>
                                    </a>

                                    <div class="grid grid-cols-3 gap-2 pt-1">
                                        <a href="{{ $upiUri }}" class="py-2 px-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center hover:border-violet-400 transition text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center justify-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-purple-600"></span> PhonePe
                                        </a>
                                        <a href="{{ $upiUri }}" class="py-2 px-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center hover:border-violet-400 transition text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center justify-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-blue-500"></span> GPay
                                        </a>
                                        <a href="{{ $upiUri }}" class="py-2 px-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center hover:border-violet-400 transition text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center justify-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-sky-500"></span> Paytm
                                        </a>
                                    </div>
                                </div>

                                <!-- Desktop / QR View -->
                                <div class="pt-3 border-t border-violet-200/60 dark:border-violet-900/40">
                                    <div class="flex flex-col sm:flex-row items-center gap-4">
                                        <div class="p-2.5 bg-white rounded-2xl border border-slate-200 shadow-sm shrink-0 flex items-center justify-center">
                                            <img src="{{ $qrCodeUrl }}" alt="UPI QR Code" class="w-28 h-28 object-contain">
                                        </div>
                                        <div class="space-y-1.5 text-center sm:text-left flex-1">
                                            <span class="text-xs font-bold text-slate-900 dark:text-white block">
                                                Or Scan from your Phone
                                            </span>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                                Scan the QR code above with Google Pay, PhonePe, or Paytm to pay <strong>₹{{ number_format($grandTotal, 2) }}</strong>.
                                            </p>
                                            <div class="flex items-center justify-center sm:justify-start gap-2 pt-1">
                                                <span class="text-[11px] font-mono bg-white dark:bg-slate-900 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 select-all font-semibold" id="upiVpaText">{{ $upiPayee }}</span>
                                                <button type="button" onclick="copyUpiVpa()" class="px-2.5 py-1 rounded-lg bg-violet-100 dark:bg-violet-950 text-violet-700 dark:text-violet-300 text-[11px] font-bold hover:bg-violet-200 transition cursor-pointer" id="copyUpiBtn">
                                                    <i class="fa-regular fa-copy"></i> Copy
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($isCardEnabled)
                            <!-- Credit / Debit Card -->
                            <label class="relative flex items-start gap-4 p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 cursor-pointer transition select-none payment-option-card">
                                <input type="radio" 
                                       name="payment_method" 
                                       value="mock_card" 
                                       {{ (!$isCodEnabled && !$isUpiEnabled) ? 'checked' : '' }}
                                       class="mt-1 text-indigo-600 focus:ring-indigo-500" 
                                       onchange="updatePaymentCardHighlight(this)">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-regular fa-credit-card text-indigo-600 dark:text-indigo-400 text-lg"></i>
                                            <span class="font-bold text-sm text-slate-900 dark:text-white">Credit / Debit Card (Instant Approval)</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400">
                                            Visa / MC / RuPay
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        Enter card details for instant authorization. Gateway-ready simulated sandbox.
                                    </p>
                                </div>
                            </label>

                            <!-- Collapsible Card Input Form -->
                            <div id="cardPaymentPanel" class="p-5 rounded-2xl border border-indigo-200 dark:border-indigo-900/60 bg-indigo-50/40 dark:bg-indigo-950/20 space-y-4 {{ ($isCodEnabled || $isUpiEnabled) ? 'hidden' : '' }}">
                                <div class="flex items-center justify-between border-b border-indigo-100 dark:border-indigo-900/40 pb-2">
                                    <span class="text-xs font-bold text-indigo-900 dark:text-indigo-300 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="fa-solid fa-credit-card"></i> Card Details
                                    </span>
                                    <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-shield-halved"></i> 256-Bit Encrypted
                                    </span>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Card Number</label>
                                        <div class="relative">
                                            <input type="text" id="cardNumberInput" placeholder="4000 1234 5678 9010" maxlength="19" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs font-mono tracking-wider focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                            <div class="absolute right-3 top-2.5 flex items-center gap-1 text-slate-400 text-sm">
                                                <i class="fa-brands fa-cc-visa"></i>
                                                <i class="fa-brands fa-cc-mastercard"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Expires (MM/YY)</label>
                                            <input type="text" id="cardExpiryInput" placeholder="12/28" maxlength="5" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs font-mono tracking-wider focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">CVV / CVC</label>
                                            <input type="password" id="cardCvvInput" placeholder="•••" maxlength="4" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs font-mono tracking-wider focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Cardholder Name</label>
                                        <input type="text" id="cardHolderInput" placeholder="e.g. John Doe" value="{{ auth()->user()?->name ?? ($user->name ?? '') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                    </div>
                                </div>
                            </div>
                        @endif
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

        const upiPanel = document.getElementById('upiPaymentPanel');
        const cardPanel = document.getElementById('cardPaymentPanel');

        if (upiPanel) {
            if (radioEl.value === 'mock_upi') {
                upiPanel.classList.remove('hidden');
            } else {
                upiPanel.classList.add('hidden');
            }
        }

        if (cardPanel) {
            if (radioEl.value === 'mock_card') {
                cardPanel.classList.remove('hidden');
            } else {
                cardPanel.classList.add('hidden');
            }
        }
    }

    function copyUpiVpa() {
        const vpaText = document.getElementById('upiVpaText')?.innerText?.trim();
        const copyBtn = document.getElementById('copyUpiBtn');
        if (!vpaText) return;

        navigator.clipboard.writeText(vpaText).then(() => {
            if (copyBtn) {
                const originalHtml = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fa-solid fa-check text-emerald-600"></i> Copied!';
                copyBtn.classList.add('bg-emerald-100', 'text-emerald-700');
                setTimeout(() => {
                    copyBtn.innerHTML = originalHtml;
                    copyBtn.classList.remove('bg-emerald-100', 'text-emerald-700');
                }, 2000);
            }
        });
    }

    // Interactive card input formatting
    document.getElementById('cardNumberInput')?.addEventListener('input', function(e) {
        let val = e.target.value.replace(/\D/g, '').substring(0, 16);
        let formatted = val.match(/.{1,4}/g)?.join(' ') || val;
        e.target.value = formatted;
    });

    document.getElementById('cardExpiryInput')?.addEventListener('input', function(e) {
        let val = e.target.value.replace(/\D/g, '').substring(0, 4);
        if (val.length >= 2) {
            e.target.value = val.substring(0, 2) + '/' + val.substring(2);
        } else {
            e.target.value = val;
        }
    });

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
