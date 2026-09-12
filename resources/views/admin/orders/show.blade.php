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

            <!-- Return Management Card -->
            @if($order->return_status || in_array($order->status, [\App\Models\Order::STATUS_RETURN_REQUESTED, \App\Models\Order::STATUS_RETURN_APPROVED, \App\Models\Order::STATUS_RETURN_REJECTED, \App\Models\Order::STATUS_RETURNED]))
                <div class="bg-slate-900 border {{ $order->status === \App\Models\Order::STATUS_RETURN_REQUESTED ? 'border-amber-500/50 shadow-amber-500/10' : 'border-slate-800' }} rounded-3xl p-6 shadow-xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <h3 class="text-sm font-black text-white tracking-tight flex items-center gap-2">
                            <i class="fa-solid fa-rotate-left text-amber-400"></i>
                            <span>Return Request</span>
                        </h3>
                        @php $rBadge = $order->status_badge; @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $rBadge['bg'] }} {{ $rBadge['text'] }}">
                            <i class="{{ $rBadge['icon'] }}"></i>
                            <span>{{ $rBadge['label'] }}</span>
                        </span>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase font-semibold">Reason for Return</span>
                            <p class="font-bold text-white text-sm mt-0.5">{{ $order->return_reason ?? 'Not specified' }}</p>
                        </div>

                        @if($order->return_note)
                            <div>
                                <span class="text-slate-500 block text-[10px] uppercase font-semibold">Customer Note</span>
                                <p class="text-slate-300 italic mt-0.5 bg-slate-950 p-2.5 rounded-xl border border-slate-800">
                                    "{{ $order->return_note }}"
                                </p>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-2 text-slate-400">
                            <div>
                                <span class="text-slate-500 block text-[10px] uppercase font-semibold">Requested At</span>
                                <span class="font-semibold text-slate-300">{{ $order->return_requested_at?->format('d M Y, h:i A') ?? 'N/A' }}</span>
                            </div>
                            @if($order->return_resolved_at)
                                <div>
                                    <span class="text-slate-500 block text-[10px] uppercase font-semibold">Resolved At</span>
                                    <span class="font-semibold text-slate-300">{{ $order->return_resolved_at->format('d M Y, h:i A') }}</span>
                                </div>
                            @endif
                        </div>

                        @if($order->return_image)
                            <div>
                                <span class="text-slate-500 block text-[10px] uppercase font-semibold mb-1.5">Photo Evidence</span>
                                <a href="{{ asset('storage/' . $order->return_image) }}" target="_blank" class="inline-block group">
                                    <div class="relative">
                                        <img src="{{ asset('storage/' . $order->return_image) }}" alt="Customer Return Evidence" class="w-28 h-28 object-cover rounded-xl border border-slate-700 group-hover:border-amber-400 transition">
                                        <span class="absolute inset-0 bg-black/40 rounded-xl flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition text-xs font-semibold">
                                            <i class="fa-solid fa-expand mr-1"></i> View Full
                                        </span>
                                    </div>
                                </a>
                            </div>
                        @endif

                        @if($order->return_rejection_reason)
                            <div class="p-3 rounded-xl bg-rose-950/40 border border-rose-900/60 text-rose-300">
                                <span class="font-bold block text-[10px] uppercase tracking-wider text-rose-400">Decline Explanation</span>
                                <p class="mt-0.5">{{ $order->return_rejection_reason }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Action Controls for Return Flow -->
                    @if($order->status === \App\Models\Order::STATUS_RETURN_REQUESTED)
                        <div class="pt-3 border-t border-slate-800 space-y-2">
                            <form method="POST" action="{{ route('admin.orders.approve_return', $order) }}" onsubmit="return confirm('Approve this return request? Customer will be notified to prepare package for pickup.');">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs shadow-md shadow-purple-600/30 transition cursor-pointer">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                    <span>Approve Return Request</span>
                                </button>
                            </form>

                            <button type="button" onclick="openAdminRejectModal();" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-slate-800 hover:bg-rose-950/60 text-rose-400 border border-rose-900/40 hover:border-rose-700/60 font-semibold text-xs transition cursor-pointer">
                                <i class="fa-solid fa-ban"></i>
                                <span>Reject Return Request</span>
                            </button>
                        </div>
                    @elseif($order->status === \App\Models\Order::STATUS_RETURN_APPROVED)
                        <div class="pt-3 border-t border-slate-800 space-y-2">
                            <form method="POST" action="{{ route('admin.orders.complete_return', $order) }}" onsubmit="return confirm('Complete return and restock inventory for all items in this order?');">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition cursor-pointer">
                                    <i class="fa-solid fa-box-open"></i>
                                    <span>Mark Return Completed &amp; Restock</span>
                                </button>
                            </form>
                        </div>
                    @elseif($order->status === \App\Models\Order::STATUS_RETURNED)
                        <div class="p-3 rounded-xl bg-emerald-950/40 border border-emerald-900/60 text-emerald-300 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-emerald-400"></i>
                            <span>Order returned, inventory restocked, and case closed.</span>
                        </div>
                    @endif
                    <!-- Return Partner Assignment -->
                    @if(in_array($order->status, [\App\Models\Order::STATUS_RETURN_APPROVED, \App\Models\Order::STATUS_RETURNED]))
                        <div class="pt-3 border-t border-slate-800 space-y-3">
                            <span class="text-slate-400 block text-xs font-bold uppercase tracking-wider">Return Pickup Partner</span>
                            @if($order->returnPartner)
                                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                                    <div>
                                        <p class="font-bold text-white">{{ $order->returnPartner->name }}</p>
                                        <p class="text-slate-400 text-[11px]">{{ $order->returnPartner->phone }} &bull; {{ ucfirst($order->returnPartner->vehicle_type ?? 'vehicle') }}</p>
                                    </div>
                                    <span class="text-[10px] px-2.5 py-1 rounded-md bg-purple-950/80 text-purple-300 border border-purple-800/80 font-bold">Assigned</span>
                                </div>
                            @endif

                            @if($order->status === \App\Models\Order::STATUS_RETURN_APPROVED)
                                <form method="POST" action="{{ route('admin.orders.assign_return_partner', $order) }}" class="space-y-2">
                                    @csrf
                                    <select name="return_partner_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200">
                                        <option value="">-- Assign partner for return pickup --</option>
                                        @foreach($deliveryPartners as $dp)
                                            <option value="{{ $dp->id }}" {{ $order->return_partner_id === $dp->id ? 'selected' : '' }}>
                                                {{ $dp->name }} ({{ ucfirst($dp->vehicle_type ?? 'bike') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="w-full py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs transition cursor-pointer">
                                        Assign Return Pickup
                                    </button>
                                </form>
                            @endif

                            @if($order->return_pickup_image)
                                <div class="mt-2 pt-2 border-t border-slate-800/60">
                                    <span class="text-slate-500 block text-[10px] uppercase font-semibold mb-1">Return Pickup Proof Photo</span>
                                    <a href="{{ asset('storage/' . $order->return_pickup_image) }}" target="_blank" class="inline-block group">
                                        <img src="{{ asset('storage/' . $order->return_pickup_image) }}" alt="Return Pickup Proof" class="w-24 h-24 object-cover rounded-xl border border-slate-700 group-hover:border-purple-400 transition">
                                    </a>
                                    @if($order->return_pickup_notes)
                                        <p class="text-[11px] text-slate-400 italic mt-1 bg-slate-950 p-2 rounded-lg">"{{ $order->return_pickup_notes }}"</p>
                                    @endif
                                    @if($order->return_picked_up_at)
                                        <p class="text-[10px] text-slate-500 mt-1">Picked up: {{ $order->return_picked_up_at->format('d M Y, h:i A') }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            <!-- Delivery Partner Assignment Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-sm font-black text-white tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-person-biking text-indigo-400"></i>
                        <span>Delivery Partner</span>
                    </h3>
                    @if($order->deliveryPartner)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Assigned
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            Unassigned
                        </span>
                    @endif
                </div>

                @if($order->deliveryPartner)
                    <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 font-bold">
                                    <i class="fa-solid fa-motorcycle"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white">{{ $order->deliveryPartner->name }}</h4>
                                    <p class="text-xs text-slate-400">{{ $order->deliveryPartner->phone }} &bull; {{ ucfirst($order->deliveryPartner->vehicle_type ?? 'vehicle') }} ({{ $order->deliveryPartner->vehicle_number ?? 'N/A' }})</p>
                                </div>
                            </div>
                            <a href="{{ route('admin.delivery_partners.show', $order->deliveryPartner) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition">
                                Profile <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                        @if($order->assigned_at)
                            <div class="text-[11px] text-slate-400 pt-2 border-t border-slate-800/80 flex items-center justify-between">
                                <span>Assigned at:</span>
                                <span class="text-slate-300 font-medium">{{ $order->assigned_at->format('d M Y, h:i A') }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                @if(!in_array($order->status, [\App\Models\Order::STATUS_DELIVERED, \App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_RETURNED]))
                    <form method="POST" action="{{ route('admin.orders.assign_partner', $order) }}" class="space-y-3 pt-2">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                {{ $order->deliveryPartner ? 'Re-assign Partner' : 'Select Delivery Partner' }}
                            </label>
                            <select name="delivery_partner_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                <option value="">-- Choose an active delivery partner --</option>
                                @foreach($deliveryPartners as $dp)
                                    <option value="{{ $dp->id }}" {{ $order->delivery_partner_id === $dp->id ? 'selected' : '' }}>
                                        {{ $dp->name }} ({{ ucfirst($dp->vehicle_type ?? 'bike') }}) - {{ $dp->is_available ? '🟢 Available' : '🔴 Off Duty' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/30 transition cursor-pointer">
                            <i class="fa-solid fa-check"></i>
                            <span>{{ $order->deliveryPartner ? 'Update Assigned Partner' : 'Assign to Delivery Partner' }}</span>
                        </button>
                    </form>
                @endif
            </div>

            <!-- Proof of Delivery Card (when present) -->
            @if($order->delivery_proof_image)
                <div class="bg-slate-900 border border-emerald-500/40 rounded-3xl p-6 shadow-xl space-y-3">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <h3 class="text-sm font-black text-white tracking-tight flex items-center gap-2">
                            <i class="fa-solid fa-camera text-emerald-400"></i>
                            <span>Proof of Delivery (POD)</span>
                        </h3>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            Verified
                        </span>
                    </div>
                    <div>
                        <a href="{{ asset('storage/' . $order->delivery_proof_image) }}" target="_blank" class="inline-block group w-full">
                            <div class="relative overflow-hidden rounded-2xl border border-slate-700 group-hover:border-emerald-400 transition aspect-video bg-slate-950 flex items-center justify-center">
                                <img src="{{ asset('storage/' . $order->delivery_proof_image) }}" alt="Proof of Delivery" class="w-full h-full object-cover">
                                <span class="absolute inset-0 bg-black/40 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition text-xs font-semibold">
                                    <i class="fa-solid fa-expand mr-1.5"></i> Expand Photo
                                </span>
                            </div>
                        </a>
                    </div>
                    @if($order->delivery_notes)
                        <div class="text-xs text-slate-300 italic bg-slate-950 p-2.5 rounded-xl border border-slate-800">
                            <span class="text-slate-500 block text-[10px] uppercase font-semibold not-italic">Rider Doorstep Note</span>
                            "{{ $order->delivery_notes }}"
                        </div>
                    @endif
                    @if($order->delivered_at)
                        <div class="text-[11px] text-slate-400 flex items-center justify-between">
                            <span>Delivered At:</span>
                            <span class="text-emerald-400 font-bold">{{ $order->delivered_at->format('d M Y, h:i A') }}</span>
                        </div>
                    @endif
                </div>
            @endif

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

<!-- Admin Reject Return Modal -->
<div id="adminRejectModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs">
    <div class="bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-800 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-black text-white">Decline Return Request</h3>
            <button type="button" onclick="closeAdminRejectModal();" class="w-8 h-8 rounded-full bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center cursor-pointer">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <p class="text-xs text-slate-400">
            Explain to the customer why this return cannot be accepted (e.g. policy timeframe expired, missing tags, damaged by customer).
        </p>
        <form method="POST" action="{{ route('admin.orders.reject_return', $order) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Reason for Declining *</label>
                <textarea name="rejection_reason" rows="3" required placeholder="Provide clear reason to the customer..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500 transition"></textarea>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeAdminRejectModal();" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 shadow-md shadow-rose-600/30 transition cursor-pointer">
                    Confirm Decline
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAdminRejectModal() {
        document.getElementById('adminRejectModal')?.classList.remove('hidden');
    }
    function closeAdminRejectModal() {
        document.getElementById('adminRejectModal')?.classList.add('hidden');
    }
</script>
@endsection
