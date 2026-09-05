@extends('user.layouts.app')

@section('title', 'Order Placed Successfully')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
    <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-6 sm:p-10 border border-slate-200 dark:border-slate-700/80 shadow-md text-center space-y-8">
        
        <!-- Celebratory Icon Badge -->
        <div class="flex justify-center">
            <div class="w-20 h-20 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-lg shadow-emerald-500/15 animate-bounce">
                <i class="fa-solid fa-check text-4xl"></i>
            </div>
        </div>

        <!-- Success Header -->
        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                <i class="fa-solid fa-badge-check"></i>
                <span>Order Confirmed</span>
            </span>
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                Thank You for Your Order!
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                We've received your order and are preparing it for delivery. A confirmation has been registered for your account.
            </p>
        </div>

        <!-- Order Snapshot Details -->
        <div class="bg-slate-50 dark:bg-slate-900/60 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/80 text-left grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block mb-0.5">Order Number</span>
                <div class="flex items-center gap-2">
                    <strong class="text-sm font-bold text-slate-900 dark:text-white font-mono">{{ $order->order_number }}</strong>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $order->order_number }}'); alert('Order number copied!');" class="text-slate-400 hover:text-indigo-600" title="Copy order number">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </div>
            </div>

            <div>
                <span class="text-slate-400 block mb-0.5">Payment Option</span>
                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $order->payment_method_label }}</span>
            </div>

            <div>
                <span class="text-slate-400 block mb-0.5">Estimated Delivery</span>
                <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-truck-fast"></i>
                    <span>{{ $order->mode?->slug === 'minutes' ? '10-15 Minutes' : ($order->mode?->slug === 'food' ? '30-45 Minutes' : '2-3 Business Days') }}</span>
                </span>
            </div>

            <div>
                <span class="text-slate-400 block mb-0.5">Total Amount</span>
                <span class="text-base font-black text-indigo-600 dark:text-indigo-400">₹{{ number_format($order->grand_total, 2) }}</span>
            </div>

            <div class="sm:col-span-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                <span class="text-slate-400 block mb-0.5">Delivery Address</span>
                <p class="font-medium text-slate-700 dark:text-slate-300">
                    <strong>{{ $order->shipping_name }}</strong> ({{ $order->shipping_phone }}) &bull; {{ $order->formatted_shipping_address }}
                </p>
            </div>
        </div>

        <!-- Items Ordered Snapshot -->
        <div class="border-t border-slate-100 dark:border-slate-700 pt-6 space-y-3 text-left">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                Ordered Items ({{ $order->totalQuantity() }})
            </h3>
            <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @foreach($order->items as $item)
                    <div class="py-2.5 flex items-center justify-between gap-4 text-xs">
                        <div class="flex items-center gap-3">
                            <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="w-10 h-10 object-cover rounded-lg border border-slate-200 dark:border-slate-700">
                            <div>
                                <h4 class="font-semibold text-slate-900 dark:text-white">{{ $item->product_name }}</h4>
                                <p class="text-slate-400 text-[11px]">
                                    @if($item->color) Color: {{ $item->color }} @endif
                                    @if($item->size) &bull; Size: {{ $item->size }} @endif
                                    &bull; Qty: {{ $item->quantity }}
                                </p>
                            </div>
                        </div>
                        <span class="font-bold text-slate-900 dark:text-white shrink-0">
                            ₹{{ number_format($item->subtotal, 2) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
            <a href="{{ route('orders.show', $order->order_number) }}" 
               class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-sm text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-500/20 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-box-open"></i>
                <span>Track &amp; View Order Details</span>
            </a>

            <a href="{{ route('home') }}" 
               class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-sm text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-bag-shopping"></i>
                <span>Continue Shopping</span>
            </a>
        </div>
    </div>
</div>
@endsection
