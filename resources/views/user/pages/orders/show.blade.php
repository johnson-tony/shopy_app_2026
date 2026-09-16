@extends('user.layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">Home</a>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                <a href="{{ route('orders.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">My Orders</a>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                <span class="text-slate-900 dark:text-white font-semibold">#{{ $order->order_number }}</span>
            </nav>
            <div class="flex flex-wrap items-center gap-3 mt-1">
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Order #{{ $order->order_number }}
                </h1>
                @php $badge = $order->status_badge; @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $badge['bg'] }} {{ $badge['text'] }}">
                    <i class="{{ $badge['icon'] }} text-[11px]"></i>
                    <span>{{ $badge['label'] }}</span>
                </span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if($order->canBeCancelled())
                <button type="button" 
                        onclick="openCancelModal();"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-ban text-[11px]"></i>
                    <span>Cancel Order</span>
                </button>
            @endif
            @if($order->isReturnEligible())
                <button type="button" 
                        onclick="openReturnModal();"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-900/50 border border-amber-200 dark:border-amber-800/60 transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-rotate-left text-[11px]"></i>
                    <span>Request Return / Exchange</span>
                </button>
            @endif
            <a href="{{ route('support.create', ['order_number' => $order->order_number]) }}" class="px-4 py-2 rounded-xl text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-200 dark:border-indigo-800/60 transition flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-headset text-[11px]"></i>
                <span>Need Help?</span>
            </a>
            <a href="{{ route('orders.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-[11px]"></i>
                <span>Back to Orders</span>
            </a>
        </div>
    </div>

@push('styles')
    @if($order->deliveryPartner && in_array($order->status, [\App\Models\Order::STATUS_DELIVERY_ASSIGNED, \App\Models\Order::STATUS_PICKED_UP, \App\Models\Order::STATUS_SHIPPED, \App\Models\Order::STATUS_OUT_FOR_DELIVERY]))
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endif
@endpush

    <!-- Status Tracking Stepper -->
    @if($order->status !== 'cancelled')
        @php
            $steps = [
                'confirmed'         => ['label' => 'Order Placed', 'icon' => 'fa-solid fa-check'],
                'processing'        => ['label' => 'Preparing',    'icon' => 'fa-solid fa-box'],
                'out_for_delivery'  => ['label' => 'On the Way',   'icon' => 'fa-solid fa-motorcycle'],
                'delivered'         => ['label' => 'Delivered',    'icon' => 'fa-solid fa-house-chimney-check'],
            ];
            $stageMap = [
                'confirmed'             => 1,
                'processing'            => 2,
                'ready-for-delivery'    => 2,
                'delivery-assigned'     => 2,
                'picked-up'             => 3,
                'shipped'               => 3,
                'out-for-delivery'      => 3,
                'delivered'             => 4,
                'return-requested'      => 4,
                'return-approved'       => 4,
                'return-rejected'       => 4,
                'returned'              => 4,
            ];
            $currentStepIndex = $stageMap[$order->status] ?? 1;
        @endphp
        <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-6">Delivery Progress</h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 relative">
                @foreach($steps as $key => $stepData)
                    @php 
                        $stepNum = match($key) {
                            'confirmed' => 1,
                            'processing' => 2,
                            'out_for_delivery' => 3,
                            'delivered' => 4,
                        }; 
                        $isCompleted = $currentStepIndex >= $stepNum;
                        $isCurrent = $currentStepIndex === $stepNum;
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 font-bold text-sm {{ $isCompleted ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/20' : 'bg-slate-100 dark:bg-slate-700 text-slate-400' }}">
                            <i class="{{ $stepData['icon'] }} text-xs"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold block {{ $isCompleted ? 'text-slate-900 dark:text-white' : 'text-slate-400' }}">
                                {{ $stepData['label'] }}
                            </span>
                            <span class="text-[10px] text-slate-400">
                                {{ $isCompleted ? ($isCurrent ? 'Current Status' : 'Completed') : 'Pending' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- Order Cancelled Banner -->
        <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 rounded-3xl p-5 flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-xmark text-lg"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-rose-800 dark:text-rose-300">Order Cancelled</h4>
                <p class="text-xs text-rose-600 dark:text-rose-400">
                    This order was cancelled on {{ $order->cancelled_at?->format('M d, Y h:i A') ?? $order->updated_at->format('M d, Y') }}.
                    @if($order->cancellation_reason) Reason: {{ $order->cancellation_reason }} @endif
                </p>
            </div>
        </div>
    @endif

    @if($order->deliveryPartner && in_array($order->status, [\App\Models\Order::STATUS_DELIVERY_ASSIGNED, \App\Models\Order::STATUS_PICKED_UP, \App\Models\Order::STATUS_SHIPPED, \App\Models\Order::STATUS_OUT_FOR_DELIVERY]))
        <!-- Live Delivery Tracking & Interactive Map -->
        <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-xs font-bold text-emerald-600 dark:text-emerald-400 mb-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Live GPS Delivery Tracking</span>
                    </div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">
                        @if($order->status === \App\Models\Order::STATUS_OUT_FOR_DELIVERY)
                            Your order is out for delivery!
                        @elseif($order->status === \App\Models\Order::STATUS_PICKED_UP)
                            Rider picked up your order &amp; heading to your location
                        @else
                            Rider assigned &amp; preparing to collect your order
                        @endif
                    </h3>
                </div>
                <div id="liveTelemetryBadge" class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-satellite-dish text-indigo-500 animate-pulse"></i>
                    <span id="telemetryStatusText">Connecting live telemetry...</span>
                </div>
            </div>

            <!-- Rider Profile Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/60">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600/10 dark:bg-indigo-600/20 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-lg shrink-0">
                        <i class="fa-solid fa-motorcycle"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span>{{ $order->deliveryPartner->name }}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">
                                Delivery Partner
                            </span>
                        </h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ ucfirst($order->deliveryPartner->vehicle_type ?? 'Bike') }} &bull; {{ $order->deliveryPartner->vehicle_number ?? 'Fleet Unit' }}
                        </p>
                    </div>
                </div>
                @if($order->deliveryPartner->phone)
                    <a href="tel:{{ $order->deliveryPartner->phone }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm shadow-emerald-600/20 transition cursor-pointer shrink-0">
                        <i class="fa-solid fa-phone"></i>
                        <span>Call Rider</span>
                    </a>
                @endif
            </div>

            <!-- Interactive Leaflet Live Tracking Map -->
            <div class="relative rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-inner">
                <div id="liveOrderMap" style="height: 320px;" class="w-full bg-slate-100 dark:bg-slate-900"></div>
                <div class="absolute bottom-3 left-3 z-[400] bg-white/95 dark:bg-slate-900/95 backdrop-blur-xs px-3 py-1.5 rounded-xl border border-slate-200/80 dark:border-slate-700 shadow-md text-[11px] text-slate-600 dark:text-slate-300 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span id="mapFootnote">Live GPS updates every 10s</span>
                </div>
            </div>
        </div>
    @endif

    @if($order->delivery_proof_image)
        <!-- Doorstep Delivery Verification (POD) -->
        <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-6 border border-emerald-200 dark:border-emerald-800/60 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-camera text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white tracking-tight">Verified Doorstep Delivery</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Delivered on {{ $order->delivered_at?->format('d M Y, h:i A') ?? $order->updated_at->format('d M Y, h:i A') }}
                        </p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Proof of Delivery</span>
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                <div>
                    <a href="{{ asset('storage/' . $order->delivery_proof_image) }}" target="_blank" class="block group relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 aspect-video bg-slate-100 dark:bg-slate-900 flex items-center justify-center">
                        <img src="{{ asset('storage/' . $order->delivery_proof_image) }}" alt="Doorstep Delivery Verification Photo" class="w-full h-full object-cover">
                        <span class="absolute inset-0 bg-black/40 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition text-xs font-semibold">
                            <i class="fa-solid fa-expand mr-1.5"></i> View Full Photo
                        </span>
                    </a>
                </div>
                <div class="space-y-3 text-xs">
                    @if($order->deliveryPartner)
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-700/60">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Delivered By</span>
                            <p class="font-bold text-slate-900 dark:text-white mt-0.5">{{ $order->deliveryPartner->name }} ({{ ucfirst($order->deliveryPartner->vehicle_type ?? 'Bike') }})</p>
                        </div>
                    @endif
                    @if($order->delivery_notes)
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-700/60">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Rider Doorstep Note</span>
                            <p class="text-slate-700 dark:text-slate-300 italic mt-0.5">"{{ $order->delivery_notes }}"</p>
                        </div>
                    @endif
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-emerald-500"></i>
                        <span>Contactless &amp; verified delivery recorded securely.</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Return Status / Eligibility Alert -->
    @if($order->return_status || in_array($order->status, [\App\Models\Order::STATUS_RETURN_REQUESTED, \App\Models\Order::STATUS_RETURN_APPROVED, \App\Models\Order::STATUS_RETURN_REJECTED, \App\Models\Order::STATUS_RETURNED]))
        @if($order->status === \App\Models\Order::STATUS_RETURN_REQUESTED)
            <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-3xl p-5 sm:p-6 shadow-xs space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-rotate-left text-base"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-amber-900 dark:text-amber-200">Return Request Under Review</h4>
                        <p class="text-xs text-amber-700 dark:text-amber-400">
                            Requested on {{ $order->return_requested_at?->format('M d, Y h:i A') ?? 'Recently' }}. Our team is currently reviewing your request.
                        </p>
                    </div>
                </div>
                <div class="bg-white/80 dark:bg-slate-800/80 rounded-2xl p-4 text-xs space-y-2 border border-amber-200/60 dark:border-amber-900/40">
                    <div class="flex flex-wrap gap-2 text-slate-700 dark:text-slate-300">
                        <span class="font-semibold text-slate-500 dark:text-slate-400">Reason:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $order->return_reason }}</span>
                    </div>
                    @if($order->return_note)
                        <div class="text-slate-600 dark:text-slate-400">
                            <span class="font-semibold text-slate-500 dark:text-slate-400">Your Note:</span>
                            <p class="mt-0.5 italic">"{{ $order->return_note }}"</p>
                        </div>
                    @endif
                    @if($order->return_image)
                        <div class="pt-2">
                            <span class="font-semibold text-slate-500 dark:text-slate-400 block mb-1.5">Attached Photo:</span>
                            <a href="{{ asset('storage/' . $order->return_image) }}" target="_blank" class="inline-block">
                                <img src="{{ asset('storage/' . $order->return_image) }}" alt="Return Proof" class="w-20 h-20 object-cover rounded-xl border border-amber-200 dark:border-amber-700 shadow-xs hover:opacity-90 transition">
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @elseif($order->status === \App\Models\Order::STATUS_RETURN_APPROVED)
            <div class="bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/60 rounded-3xl p-5 sm:p-6 shadow-xs space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-clipboard-check text-base"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-purple-900 dark:text-purple-200">Return Approved &bull; Pickup in Progress</h4>
                        <p class="text-xs text-purple-700 dark:text-purple-400">
                            Approved on {{ $order->return_resolved_at?->format('M d, Y h:i A') ?? 'Recently' }}. Our pickup agent will collect the item. Please keep items packed with all tags and original packaging.
                        </p>
                    </div>
                </div>
            </div>
        @elseif($order->status === \App\Models\Order::STATUS_RETURN_REJECTED)
            <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 rounded-3xl p-5 sm:p-6 shadow-xs space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-circle-exclamation text-base"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-rose-900 dark:text-rose-200">Return Request Declined</h4>
                        <p class="text-xs text-rose-700 dark:text-rose-400">
                            Updated on {{ $order->return_resolved_at?->format('M d, Y h:i A') ?? 'Recently' }}.
                        </p>
                    </div>
                </div>
                @if($order->return_rejection_reason)
                    <div class="bg-white/80 dark:bg-slate-800/80 rounded-2xl p-4 text-xs text-rose-800 dark:text-rose-300 border border-rose-200/60 dark:border-rose-900/40">
                        <span class="font-bold block mb-1">Reason for Decline:</span>
                        <p>{{ $order->return_rejection_reason }}</p>
                    </div>
                @endif
            </div>
        @elseif($order->status === \App\Models\Order::STATUS_RETURNED)
            <div class="bg-slate-100 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-3xl p-5 sm:p-6 shadow-xs flex items-center gap-4">
                <div class="w-10 h-10 rounded-2xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-arrow-rotate-left text-base"></i>
                </div>
                <div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Return Completed &bull; Closed</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        This order has been returned and closed on {{ $order->return_resolved_at?->format('M d, Y h:i A') ?? $order->updated_at->format('M d, Y') }}.
                    </p>
                </div>
            </div>
        @endif
    @elseif($order->isReturnEligible())
        <div class="bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 rounded-3xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-shield-check text-sm"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white">{{ $order->return_window_text }}</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Need a different size or received an issue? You can request an easy return or exchange within the policy window.
                    </p>
                </div>
            </div>
            <button type="button" 
                    onclick="openReturnModal();"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-white dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-slate-700 border border-indigo-200 dark:border-indigo-700 shadow-xs transition shrink-0 cursor-pointer">
                Request Return
            </button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left 8 Cols: Ordered Items & Review Opportunities -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-xs space-y-4">
                <h3 class="text-sm font-black text-slate-900 dark:text-white tracking-tight pb-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <span>Ordered Items ({{ $order->totalQuantity() }})</span>
                    <span class="text-xs text-slate-400 font-normal">Shopping Channel: {{ $order->mode?->name ?? 'Standard Store' }}</span>
                </h3>

                <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @foreach($order->items as $item)
                        @php 
                            $hasReviewed = in_array($item->product_id, $reviewedProductIds);
                        @endphp
                        <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4 min-w-0">
                                <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="w-16 h-16 object-cover rounded-2xl border border-slate-200 dark:border-slate-700 shrink-0">
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                        @if($item->product)
                                            <a href="{{ route('product.show', $item->product_slug) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                                {{ $item->product_name }}
                                            </a>
                                        @else
                                            {{ $item->product_name }}
                                        @endif
                                    </h4>
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        @if($item->color)
                                            <span class="bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded-md">Color: <strong>{{ $item->color }}</strong></span>
                                        @endif
                                        @if($item->size)
                                            <span class="bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded-md">Size: <strong>{{ $item->size }}</strong></span>
                                        @endif
                                        @if($item->restaurant_name)
                                            <span class="bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded-md text-amber-700 dark:text-amber-400">
                                                <i class="fa-solid fa-utensils text-[9px] mr-1"></i>{{ $item->restaurant_name }}
                                            </span>
                                        @endif
                                        @if($item->hasAddons())
                                            <span class="bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40 px-2 py-0.5 rounded-md">
                                                Extras: <strong>{{ $item->formattedAddons() }}</strong>
                                            </span>
                                        @endif
                                        <span>Qty: <strong>{{ $item->quantity }}</strong></span>
                                        <span>&bull;</span>
                                        <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($item->subtotal, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Review Action Button -->
                            @if($item->product_id)
                                <div class="shrink-0 flex items-center gap-2">
                                    @if($order->isDelivered())
                                        @if($hasReviewed)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                                <i class="fa-solid fa-circle-check text-[11px]"></i>
                                                <span>Reviewed</span>
                                            </span>
                                            <button type="button" 
                                                    onclick="openReviewModal({{ $item->product_id }}, '{{ addslashes($item->product_name) }}', {{ $order->id }});"
                                                    class="px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition cursor-pointer">
                                                Edit
                                            </button>
                                        @else
                                            <button type="button" 
                                                    onclick="openReviewModal({{ $item->product_id }}, '{{ addslashes($item->product_name) }}', {{ $order->id }});"
                                                    class="px-4 py-2 rounded-xl text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 hover:bg-amber-100 dark:hover:bg-amber-900/50 border border-amber-200 dark:border-amber-800/60 transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                                                <i class="fa-solid fa-star text-amber-500"></i>
                                                <span>Rate &amp; Review Product</span>
                                            </button>
                                        @endif
                                    @elseif($order->status === \App\Models\Order::STATUS_CANCELLED)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium text-slate-400 bg-slate-100 dark:bg-slate-700/40 border border-slate-200/60 dark:border-slate-700">
                                            <i class="fa-solid fa-circle-xmark text-[11px]"></i>
                                            <span>Cancelled</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-100 dark:bg-slate-700/60 text-slate-500 dark:text-slate-400 border border-slate-200/80 dark:border-slate-700" title="Review becomes available once this order is marked delivered">
                                            <i class="fa-solid fa-truck-fast text-[11px] text-slate-400"></i>
                                            <span>Available on delivery</span>
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Delivery Address Card -->
            <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-xs space-y-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Shipping Details</h3>
                <div class="text-sm">
                    <p class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>{{ $order->userAddress?->full_name }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                            {{ $order->userAddress?->address_type ?? 'Home' }}
                        </span>
                    </p>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                        {{ $order->formatted_shipping_address }}
                    </p>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-phone text-[10px]"></i>
                        <span>Phone: {{ $order->userAddress?->phone }}</span>
                    </p>
                    @if($order->notes)
                        <p class="text-xs text-slate-500 dark:text-slate-400 italic mt-2 bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl">
                            "{{ $order->notes }}"
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right 4 Cols: Payment & Financial Summary -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-xs space-y-4">
                <h3 class="text-sm font-black text-slate-900 dark:text-white tracking-tight pb-3 border-b border-slate-100 dark:border-slate-700">
                    Payment Breakdown
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($order->subtotal, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span>Delivery Fee</span>
                        <span class="font-bold {{ $order->delivery_fee == 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }}">
                            {{ $order->delivery_fee == 0 ? 'FREE' : '₹' . number_format($order->delivery_fee, 2) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span>GST Tax (5%)</span>
                        <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($order->tax_amount, 2) }}</span>
                    </div>

                    @if($order->discount_amount > 0)
                        <div class="flex items-center justify-between text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 p-2 rounded-lg">
                            <span>Discount @if($order->coupon_code) ({{ $order->coupon_code }}) @endif</span>
                            <span class="font-bold">-₹{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex items-baseline justify-between text-sm">
                        <span class="font-black text-slate-900 dark:text-white">Grand Total</span>
                        <span class="text-xl font-black text-indigo-600 dark:text-indigo-400">
                            ₹{{ number_format($order->grand_total, 2) }}
                        </span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-700 space-y-2 text-xs">
                    <div>
                        <span class="text-slate-400 block">Payment Method</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $order->payment_method_label }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Payment Status</span>
                        <span class="font-bold uppercase tracking-wider text-[11px] {{ $order->payment_status === 'paid' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $order->payment_status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Order Support & Assistance Card -->
            <div class="bg-indigo-50/70 dark:bg-indigo-950/30 rounded-3xl p-6 border border-indigo-200 dark:border-indigo-800/50 shadow-xs space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shadow-indigo-600/30 shrink-0">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white">Need Help With This Order?</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Delivery issues, food quality, or refunds</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    Have a question or problem with your delivery or items? Connect with customer care instantly via dedicated chat or helpline.
                </p>

                <div class="space-y-2 pt-1">
                    <a href="{{ route('support.create', ['order_number' => $order->order_number]) }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-sm shadow-indigo-600/25 transition cursor-pointer">
                        <i class="fa-solid fa-comments"></i>
                        <span>Open Order Support Chat</span>
                    </a>
                    <a href="tel:+916379644145" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 font-bold text-xs transition">
                        <i class="fa-solid fa-phone text-emerald-600 dark:text-emerald-400"></i>
                        <span>Call Helpline (+91 63796 44145)</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rate & Review Modal -->
<div id="reviewModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-6">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Rate &amp; Review Product</h3>
                <p id="modalProductName" class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-xs"></p>
            </div>
            <button type="button" onclick="closeReviewModal();" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>

        <form action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data" id="reviewForm" class="space-y-4">
            @csrf
            <input type="hidden" name="product_id" id="modalProductId" value="">
            <input type="hidden" name="order_id" id="modalOrderId" value="">
            <input type="hidden" name="rating" id="modalRatingValue" value="5">

            <!-- Star Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Overall Rating *</label>
                <div class="flex items-center gap-2" id="starContainer">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" 
                                onclick="setModalRating({{ $i }});"
                                class="text-2xl text-amber-400 hover:scale-125 transition modal-star-btn cursor-pointer" 
                                data-star="{{ $i }}">
                            <i class="fa-solid fa-star"></i>
                        </button>
                    @endfor
                    <span id="ratingDescriptor" class="text-xs font-bold text-amber-600 dark:text-amber-400 ml-2">Excellent (5/5)</span>
                </div>
            </div>

            <!-- Review Title -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Review Headline</label>
                <input type="text" name="title" placeholder="e.g. Outstanding quality, highly satisfied!" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <!-- Detailed Feedback / Comment -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Your Detailed Experience *</label>
                <textarea name="comment" rows="4" required placeholder="What did you like or dislike about this product? How did it fit/perform?" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>

            <!-- Customer Photo Uploads -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Upload Product Photos (Max 5 images)</label>
                <input type="file" name="images[]" multiple accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                <p class="text-[10px] text-slate-400 mt-1">Add real photos taken by you to help other shoppers make decisions.</p>
            </div>

            <!-- Verified Buyer Pill Notice -->
            <div class="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 flex items-center gap-2 text-xs text-emerald-800 dark:text-emerald-300">
                <i class="fa-solid fa-badge-check text-emerald-600 text-sm"></i>
                <span>Your review will receive the <strong>✔ Certified Buyer</strong> badge because this order is verified.</span>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="button" onclick="closeReviewModal();" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-500/25 cursor-pointer">
                    Submit Review
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Cancel Order Modal -->
@if($order->canBeCancelled())
    <div id="cancelModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4">
            <h3 class="text-base font-black text-slate-900 dark:text-white">Cancel Order #{{ $order->order_number }}?</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Are you sure you want to cancel this order? Stock will be restored and any applied discounts reverted.
            </p>

            <form action="{{ route('orders.cancel', $order->order_number) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Reason for Cancellation (Optional)</label>
                    <input type="text" name="cancellation_reason" placeholder="e.g. Placed by mistake, found better price" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="closeCancelModal();" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                        Nevermind
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm shadow-rose-500/25 cursor-pointer">
                        Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

<!-- Request Return Modal -->
@if($order->isReturnEligible())
    <div id="returnModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Request Return / Exchange</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Order #{{ $order->order_number }} &bull; {{ $order->mode?->name ?? 'Shopy' }}</p>
                    </div>
                </div>
                <button type="button" onclick="closeReturnModal();" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center cursor-pointer">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <!-- Mode-Specific Return Policy Callout -->
            <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/40 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2.5">
                <i class="fa-solid fa-circle-info text-amber-600 dark:text-amber-400 mt-0.5 text-sm"></i>
                <div class="leading-relaxed">
                    @if($order->mode?->slug === 'minutes')
                        <strong class="font-bold block">Grocery Freshness &amp; Quality Guarantee (48 Hours)</strong>
                        <span>Report spoiled, expired, damaged or missing grocery items. Please provide a clear photo of items/packaging for fast approval.</span>
                    @elseif($order->mode?->slug === 'food')
                        <strong class="font-bold block">Food Order Quality Support (2 Hours)</strong>
                        <span>Please report food freshness or spill issues immediately after delivery.</span>
                    @else
                        <strong class="font-bold block">Shopy 7-Day Easy Return / Exchange Policy</strong>
                        <span>Items must be unused, unwashed, with all original tags attached and original box/packaging preserved.</span>
                    @endif
                </div>
            </div>

            <form action="{{ route('orders.return', $order->order_number) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Reason Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Reason for Return *</label>
                    <select name="return_reason" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <option value="">Select a reason...</option>
                        @if($order->mode?->slug === 'minutes')
                            <option value="Item spoiled / poor freshness / expired">Item spoiled / poor freshness / expired</option>
                            <option value="Damaged packaging or seal broken">Damaged packaging or seal broken</option>
                            <option value="Incorrect or missing grocery item">Incorrect or missing grocery item</option>
                            <option value="Product melted or temperature compromised">Product melted or temperature compromised (Dairy/Frozen)</option>
                            <option value="Quantity or weight less than ordered">Quantity or weight less than ordered</option>
                            <option value="Other quality concern">Other quality concern</option>
                        @elseif($order->mode?->slug === 'food')
                            <option value="Spilled or damaged food packaging">Spilled or damaged food packaging</option>
                            <option value="Food cold or unacceptable quality">Food cold or unacceptable quality</option>
                            <option value="Wrong dish or item delivered">Wrong dish or item delivered</option>
                            <option value="Missing food items from restaurant">Missing food items from restaurant</option>
                            <option value="Other food quality issue">Other food quality issue</option>
                        @else
                            <option value="Size or fit issue (Need exchange/return)">Size or fit issue (Need exchange/return)</option>
                            <option value="Defective or damaged product received">Defective or damaged product received</option>
                            <option value="Product not as described on website">Product not as described on website</option>
                            <option value="Received incorrect product or variant">Received incorrect product or variant</option>
                            <option value="Quality not as expected">Quality not as expected</option>
                            <option value="Missing accessories or parts">Missing accessories or parts</option>
                            <option value="Changed mind / No longer needed">Changed mind / No longer needed</option>
                        @endif
                    </select>
                </div>

                <!-- Explanation Note -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Additional Details (Optional)</label>
                    <textarea name="return_note" rows="3" placeholder="Please describe the issue in detail so our team can verify promptly..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <!-- Photo Evidence Upload -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Upload Photo Evidence (Optional, Recommended)</label>
                    <input type="file" name="return_image" accept="image/jpeg,image/png,image/jpg,image/webp" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-800 hover:file:bg-amber-100 cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Upload a clear photo of the damaged or spoiled item, tags, or package.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" onclick="closeReturnModal();" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-sm shadow-amber-500/25 transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane text-[11px]"></i>
                        <span>Submit Return Request</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

@push('scripts')
<script>
    function openReviewModal(productId, productName, orderId) {
        document.getElementById('modalProductId').value = productId;
        document.getElementById('modalOrderId').value = orderId;
        document.getElementById('modalProductName').textContent = productName;
        document.getElementById('reviewModal').classList.remove('hidden');
        setModalRating(5);
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
    }

    function setModalRating(rating) {
        document.getElementById('modalRatingValue').value = rating;
        const labels = {
            1: 'Terrible (1/5)',
            2: 'Bad (2/5)',
            3: 'Average (3/5)',
            4: 'Good (4/5)',
            5: 'Excellent (5/5)'
        };
        document.getElementById('ratingDescriptor').textContent = labels[rating] || (rating + '/5');

        document.querySelectorAll('.modal-star-btn').forEach(btn => {
            const starNum = parseInt(btn.getAttribute('data-star'));
            const icon = btn.querySelector('i');
            if (starNum <= rating) {
                btn.classList.add('text-amber-400');
                btn.classList.remove('text-slate-300', 'dark:text-slate-600');
                if (icon) {
                    icon.classList.remove('fa-regular');
                    icon.classList.add('fa-solid');
                }
            } else {
                btn.classList.remove('text-amber-400');
                btn.classList.add('text-slate-300', 'dark:text-slate-600');
                if (icon) {
                    icon.classList.remove('fa-solid');
                    icon.classList.add('fa-regular');
                }
            }
        });
    }

    function openCancelModal() {
        document.getElementById('cancelModal')?.classList.remove('hidden');
    }

    function closeCancelModal() {
        document.getElementById('cancelModal')?.classList.add('hidden');
    }

    function openReturnModal() {
        document.getElementById('returnModal')?.classList.remove('hidden');
    }

    function closeReturnModal() {
        document.getElementById('returnModal')?.classList.add('hidden');
    }
</script>

@if($order->deliveryPartner && in_array($order->status, [\App\Models\Order::STATUS_DELIVERY_ASSIGNED, \App\Models\Order::STATUS_PICKED_UP, \App\Models\Order::STATUS_SHIPPED, \App\Models\Order::STATUS_OUT_FOR_DELIVERY]))
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mapContainer = document.getElementById('liveOrderMap');
        if (!mapContainer) return;

        let initialLat = {{ $order->deliveryPartner->latitude ?? 12.9716 }};
        let initialLng = {{ $order->deliveryPartner->longitude ?? 77.5946 }};

        const liveMap = L.map('liveOrderMap').setView([initialLat, initialLng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(liveMap);

        const riderIcon = L.divIcon({
            className: 'rider-gps-marker',
            html: '<div style="background:#4f46e5;color:white;width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(79,70,229,0.5);border:2.5px solid white;"><i class="fa-solid fa-motorcycle" style="font-size:16px;"></i></div>',
            iconSize: [38, 38],
            iconAnchor: [19, 19]
        });

        let riderMarker = L.marker([initialLat, initialLng], { icon: riderIcon }).addTo(liveMap)
            .bindPopup("<strong>{{ addslashes($order->deliveryPartner->name) }}</strong><br>Delivery Partner on duty")
            .openPopup();

        function pollRiderLocation() {
            fetch("{{ route('orders.live_location', $order->order_number) }}")
                .then(r => r.json())
                .then(data => {
                    if (data.rider && data.rider.latitude && data.rider.longitude) {
                        const newCoords = [data.rider.latitude, data.rider.longitude];
                        riderMarker.setLatLng(newCoords);
                        liveMap.panTo(newCoords);

                        const badge = document.getElementById('telemetryStatusText');
                        if (badge) {
                            badge.textContent = 'Broadcasting live GPS (' + (data.rider.last_updated || 'just now') + ')';
                        }
                    }
                    if (data.is_delivered) {
                        window.location.reload();
                    }
                })
                .catch(e => {
                    console.log('Telemetry sync error:', e);
                });
        }

        setInterval(pollRiderLocation, 10000);
    });
</script>
@endif
@endpush
@endsection
