@extends('admin.layouts.admin')

@section('title', 'Order ' . $order->order_number)

@section('content')
@php $badge = $order->status_badge; @endphp
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.orders.index', ['mode' => request('mode')]) }}" class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition cursor-pointer" title="Back to Orders">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-mono font-black tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        {{ $order->order_number }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badge['bg'] }} {{ $badge['text'] }}">
                        <i class="{{ $badge['icon'] }}"></i>
                        <span>{{ $badge['label'] }}</span>
                    </span>
                </div>
                <h1 class="text-xl md:text-2xl font-black text-white tracking-tight">
                    Order by {{ $order->user?->name ?? 'Guest' }}
                </h1>
                <p class="text-slate-400 text-xs mt-1">
                    Placed {{ $order->created_at->format('d M Y, h:i A') }} &bull;
                    Channel: {{ $order->mode?->name ?? 'Standard Store' }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Items & Financial Summary -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Ordered Items -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <h3 class="text-sm font-black text-white tracking-tight pb-3 border-b border-slate-800">
                    Ordered Items ({{ $quantity }})
                </h3>
                <div class="divide-y divide-slate-800/60">
                    @foreach ($order->items as $item)
                        <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4 min-w-0">
                                <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="w-14 h-14 object-cover rounded-xl border border-slate-700 shrink-0">
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-white truncate">{{ $item->product_name }}</h4>
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400 mt-1">
                                        @if($item->color)<span class="bg-slate-800 px-2 py-0.5 rounded-md">Color: <strong>{{ $item->color }}</strong></span>@endif
                                        @if($item->size)<span class="bg-slate-800 px-2 py-0.5 rounded-md">Size: <strong>{{ $item->size }}</strong></span>@endif
                                        @if($item->restaurant_name)<span class="bg-slate-800 px-2 py-0.5 rounded-md text-amber-400"><i class="fa-solid fa-utensils text-[9px] mr-1"></i>{{ $item->restaurant_name }}</span>@endif
                                        @if($item->hasAddons())<span class="bg-slate-800 px-2 py-0.5 rounded-md text-amber-300">Extras: {{ $item->formattedAddons() }}</span>@endif
                                        <span>Qty: <strong>{{ $item->quantity }}</strong></span>
                                        <span>&bull;</span>
                                        <span class="font-bold text-white">₹{{ number_format($item->subtotal, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Financial Breakdown -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-3">
                <h3 class="text-sm font-black text-white tracking-tight pb-3 border-b border-slate-800">Payment Summary</h3>
                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-bold text-white">₹{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Delivery Fee</span>
                        <span class="font-bold {{ $order->delivery_fee == 0 ? 'text-emerald-400' : 'text-white' }}">
                            {{ $order->delivery_fee == 0 ? 'FREE' : '₹' . number_format($order->delivery_fee, 2) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Tax</span>
                        <span class="font-bold text-white">₹{{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                        <div class="flex items-center justify-between text-emerald-400 bg-emerald-950/30 p-2 rounded-lg">
                            <span>Discount @if($order->coupon_code) ({{ $order->coupon_code }}) @endif</span>
                            <span class="font-bold">-₹{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="pt-3 border-t border-slate-800 flex items-baseline justify-between text-sm">
                        <span class="font-black text-white">Grand Total</span>
                        <span class="text-xl font-black text-indigo-400">₹{{ number_format($order->grand_total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Delivery Address & Customer Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Delivery Address</h3>
                    @if($order->userAddress)
                        <p class="font-bold text-white text-sm flex items-center gap-2">
                            <span>{{ $order->userAddress->full_name }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-400">{{ $order->userAddress->address_type ?? 'Home' }}</span>
                        </p>
                        <p class="text-xs text-slate-400 leading-relaxed">{{ $order->formatted_shipping_address }}</p>
                        @if($order->userAddress->phone)
                            <p class="text-xs text-slate-400 flex items-center gap-1.5"><i class="fa-solid fa-phone text-[10px]"></i> {{ $order->userAddress->phone }}</p>
                        @endif
                    @else
                        <p class="text-xs text-slate-500 italic">No address linked</p>
                    @endif
                    @if($order->notes)
                        <p class="text-xs text-slate-400 italic mt-2 bg-slate-800 p-2.5 rounded-xl">"{{ $order->notes }}"</p>
                    @endif
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-3 text-xs">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Customer &amp; Payment</h3>
                    @if($order->user)
                        <div class="space-y-1">
                            <p class="text-slate-300"><span class="text-slate-500 block text-[10px] uppercase">Name</span><span class="font-bold text-white">{{ $order->user->name }}</span></p>
                            <p class="text-slate-300"><span class="text-slate-500 block text-[10px] uppercase">Email</span><span class="text-slate-200">{{ $order->user->email }}</span></p>
                            @if($order->user->phone)
                                <p class="text-slate-300"><span class="text-slate-500 block text-[10px] uppercase">Phone</span><span class="text-slate-200">{{ $order->user->phone }}</span></p>
                            @endif
                        </div>
                    @else
                        <p class="text-slate-500 italic">Guest checkout</p>
                    @endif
                    <div class="pt-2 border-t border-slate-800">
                        <span class="text-slate-500 block text-[10px] uppercase">Payment Method</span>
                        <span class="font-bold text-white">{{ $order->payment_method_label }}</span>
                    </div>
                    @if($order->cancelled_at)
                        <div class="pt-2 border-t border-slate-800">
                            <span class="text-slate-500 block text-[10px] uppercase">Cancelled On</span>
                            <span class="font-bold text-rose-400">{{ $order->cancelled_at->format('d M Y, h:i A') }}</span>
                            @if($order->cancellation_reason)
                                <span class="block text-slate-400 mt-1">Reason: {{ $order->cancellation_reason }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Fulfillment Status Update -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
                <h3 class="text-sm font-black text-white tracking-tight pb-3 border-b border-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-truck-fast text-indigo-400"></i>
                    Update Fulfillment
                </h3>

                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="space-y-4 mt-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Order Status</label>
                        <select name="status" required class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" {{ $order->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Payment Status</label>
                        <select name="payment_status" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                            @foreach ($paymentStatuses as $value => $label)
                                <option value="{{ $value }}" {{ $order->payment_status === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('payment_status')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Cancellation Reason</label>
                        <textarea name="cancellation_reason" rows="2" placeholder="Required if cancelling the order..." class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">{{ $order->cancellation_reason }}</textarea>
                        @error('cancellation_reason')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-600/30 transition cursor-pointer">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Order Updates
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
