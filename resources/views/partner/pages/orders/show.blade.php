@extends('partner.layouts.partner')

@section('title', 'Delivery #' . $order->order_number)

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('partner.dashboard') }}" class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition cursor-pointer" title="Back to Dashboard">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-black text-white">#{{ $order->order_number }}</h1>
                    @if($order->mode)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                            {{ $order->mode->slug === 'minutes' ? 'bg-sky-500/10 text-sky-400 border border-sky-500/20' : ($order->mode->slug === 'food' ? 'bg-orange-500/10 text-orange-400 border border-orange-500/20' : 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20') }}">
                            <i class="fa-solid fa-bolt text-[10px] mr-1"></i> {{ $order->mode->name }}
                        </span>
                    @endif
                    @php $badge = $order->status_badge; @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badge['bg'] }} {{ $badge['text'] }}">
                        <i class="{{ $badge['icon'] }} text-[11px]"></i>
                        <span>{{ $badge['label'] }}</span>
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-1">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
            </div>
        </div>

        <!-- Payment Reminder Pill -->
        <div>
            @if($order->payment_method === \App\Models\Order::PAYMENT_METHOD_COD && $order->payment_status !== 'paid')
                <div class="px-4 py-2 rounded-2xl bg-amber-500/20 border border-amber-500/30 text-amber-300 text-xs font-bold flex items-center gap-2 animate-pulse">
                    <i class="fa-solid fa-hand-holding-dollar text-base"></i>
                    <div>
                        <span class="block text-[10px] uppercase font-semibold text-amber-400">Cash on Delivery</span>
                        <span>Collect ₹{{ number_format($order->grand_total, 2) }}</span>
                    </div>
                </div>
            @else
                <div class="px-4 py-2 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-emerald-400">Prepaid Order</span>
                        <span>Do NOT collect cash</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Fulfillment Action Hub -->
    @if($order->status === \App\Models\Order::STATUS_RETURN_APPROVED && $order->return_partner_id === $partner->id)
        <!-- Return Pickup Action Card -->
        <div class="partner-card border border-purple-500/30 bg-purple-950/20 p-6 rounded-3xl shadow-xl space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-purple-500/20 text-purple-400 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-white">Reverse Pickup Assignment</h3>
                    <p class="text-xs text-purple-300">Customer requested return. Inspect item condition and collect package.</p>
                </div>
            </div>

            <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 text-xs space-y-1.5">
                <p class="text-slate-400"><span class="text-slate-500 font-bold uppercase text-[10px] block">Customer Return Reason:</span> {{ $order->return_reason }}</p>
                @if($order->return_note)
                    <p class="text-slate-400"><span class="text-slate-500 font-bold uppercase text-[10px] block">Customer Explanation:</span> "{{ $order->return_note }}"</p>
                @endif
            </div>

            <form method="POST" action="{{ route('partner.orders.pickup_return', $order) }}" enctype="multipart/form-data" class="space-y-4 pt-2">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-200 mb-1.5">Take Photo of Returned Item *</label>
                    <input type="file" name="return_pickup_image" required accept="image/*"
                        class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-600 file:text-white hover:file:bg-purple-500 cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Capture clear photo of the item, tags, and packaging.</p>
                    @error('return_pickup_image')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-200 mb-1.5">Verification Notes (Optional)</label>
                    <textarea name="return_pickup_notes" rows="2" placeholder="e.g. Tags intact, item in original box..."
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl text-white font-bold bg-purple-600 hover:bg-purple-500 transition shadow-lg shadow-purple-600/30 text-xs cursor-pointer flex items-center justify-center gap-2">
                    <i class="fa-solid fa-box-open"></i>
                    <span>Confirm Item Collected &amp; Restock</span>
                </button>
            </form>
        </div>
    @elseif(in_array($order->status, [\App\Models\Order::STATUS_DELIVERY_ASSIGNED, \App\Models\Order::STATUS_READY_FOR_DELIVERY]))
        <!-- Step 1: Pick Up from Store -->
        <div class="partner-card border border-amber-500/30 bg-amber-950/10 p-6 rounded-3xl shadow-xl space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-store"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-white">Step 1: Head to Store / Warehouse</h3>
                    <p class="text-xs text-slate-400">Collect the packaged items and verify all order contents before departure.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('partner.orders.update_status', $order) }}">
                @csrf
                <input type="hidden" name="status" value="{{ \App\Models\Order::STATUS_PICKED_UP }}">
                <button type="submit" class="w-full py-3.5 px-4 rounded-xl text-white font-black bg-amber-600 hover:bg-amber-500 transition shadow-lg shadow-amber-600/30 text-sm cursor-pointer flex items-center justify-center gap-2">
                    <i class="fa-solid fa-box-check"></i>
                    <span>Confirm Order Picked Up from Store</span>
                </button>
            </form>
        </div>
    @elseif($order->status === \App\Models\Order::STATUS_PICKED_UP)
        <!-- Step 2: Start Out for Delivery -->
        <div class="partner-card border border-sky-500/30 bg-sky-950/10 p-6 rounded-3xl shadow-xl space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-sky-500/20 text-sky-400 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-person-biking"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-white">Step 2: Ready to Ride?</h3>
                    <p class="text-xs text-slate-400">Starting delivery will activate live GPS tracking on the customer's phone.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('partner.orders.update_status', $order) }}">
                @csrf
                <input type="hidden" name="status" value="{{ \App\Models\Order::STATUS_OUT_FOR_DELIVERY }}">
                <button type="submit" class="w-full py-3.5 px-4 rounded-xl text-white font-black bg-sky-600 hover:bg-sky-500 transition shadow-lg shadow-sky-600/30 text-sm cursor-pointer flex items-center justify-center gap-2">
                    <i class="fa-solid fa-route"></i>
                    <span>Start Ride &bull; Mark Out for Delivery</span>
                </button>
            </form>
        </div>
    @elseif($order->status === \App\Models\Order::STATUS_OUT_FOR_DELIVERY)
        <!-- Step 3: Reached Customer & Proof of Delivery -->
        <div class="partner-card border border-emerald-500/40 bg-emerald-950/20 p-6 rounded-3xl shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-white">Step 3: Handover &amp; Proof of Delivery</h3>
                        <p class="text-xs text-emerald-400">Take a photo at customer doorstep to complete delivery.</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('partner.orders.deliver', $order) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Proof Image Upload (with Camera Access) -->
                <div>
                    <label class="block text-xs font-bold text-slate-200 mb-1.5">
                        <i class="fa-solid fa-camera text-emerald-400 mr-1"></i> Capture / Upload Delivery Photo *
                    </label>
                    <input type="file" name="delivery_proof_image" required accept="image/*" capture="environment"
                        class="w-full text-xs text-slate-400 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500 cursor-pointer">
                    <p class="text-[11px] text-slate-400 mt-1">Take a photo showing package handed to customer or placed at doorstep.</p>
                    @error('delivery_proof_image')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- COD Collection Checkbox -->
                @if($order->payment_method === \App\Models\Order::PAYMENT_METHOD_COD)
                    <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/30 space-y-1.5">
                        <label class="flex items-start gap-2.5 text-xs text-amber-200 font-bold cursor-pointer">
                            <input type="checkbox" name="cod_collected" value="1" required class="mt-0.5 w-4 h-4 rounded border-amber-500 text-emerald-500 focus:ring-emerald-500">
                            <span>I have collected the exact amount of ₹{{ number_format($order->grand_total, 2) }} in cash from the customer.</span>
                        </label>
                    </div>
                @endif

                <!-- Delivery Note -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Delivery Handover Note (Optional)</label>
                    <input type="text" name="delivery_notes" placeholder="e.g. Handed to customer at door / Left with security"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <button type="submit" class="w-full py-3.5 px-4 rounded-xl text-white font-black bg-emerald-600 hover:bg-emerald-500 transition shadow-lg shadow-emerald-600/30 text-sm cursor-pointer flex items-center justify-center gap-2">
                    <i class="fa-solid fa-circle-check text-base"></i>
                    <span>Confirm Delivery &amp; Close Order</span>
                </button>
            </form>
        </div>
    @elseif($order->status === \App\Models\Order::STATUS_DELIVERED)
        <!-- Delivered Success Card -->
        <div class="partner-card border border-emerald-500/20 bg-emerald-950/10 p-6 rounded-3xl space-y-4">
            <div class="flex items-center gap-3 text-emerald-400">
                <i class="fa-solid fa-circle-check text-2xl"></i>
                <div>
                    <h3 class="text-base font-bold text-white">Delivery Completed</h3>
                    <p class="text-xs text-slate-400">Delivered on {{ $order->delivered_at?->format('d M Y, h:i A') }} &bull; {{ $order->delivery_notes }}</p>
                </div>
            </div>

            @if($order->delivery_proof_image)
                <div class="pt-2">
                    <span class="text-slate-400 text-xs font-semibold block mb-2">Proof of Delivery Photo:</span>
                    <a href="{{ asset('storage/' . $order->delivery_proof_image) }}" target="_blank" class="inline-block group">
                        <img src="{{ asset('storage/' . $order->delivery_proof_image) }}" alt="Delivery Proof" class="w-40 h-40 object-cover rounded-2xl border border-slate-700 group-hover:border-emerald-400 transition shadow-lg">
                    </a>
                </div>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Customer & Drop-off Address Card -->
        <div class="partner-card border p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-emerald-400"></i>
                    Customer &amp; Drop-off
                </h3>
                @php $phone = $order->shipping_phone ?: $order->userAddress?->phone; @endphp
                @if($phone)
                    <a href="tel:{{ $phone }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition">
                        <i class="fa-solid fa-phone"></i>
                        <span>Call Customer</span>
                    </a>
                @endif
            </div>

            <div class="text-sm space-y-2">
                <p class="font-bold text-white text-base">{{ $order->shipping_name ?: $order->userAddress?->full_name }}</p>
                <p class="text-xs text-slate-300 leading-relaxed bg-slate-950 p-3 rounded-2xl border border-slate-800">
                    {{ $order->formatted_shipping_address }}
                </p>

                @if($order->notes)
                    <p class="text-xs text-amber-300 bg-amber-500/10 border border-amber-500/20 p-2.5 rounded-xl">
                        <i class="fa-solid fa-circle-info mr-1"></i> Customer Note: "{{ $order->notes }}"
                    </p>
                @endif

                <!-- Navigation Launcher -->
                <div class="pt-2">
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($order->formatted_shipping_address) }}" target="_blank"
                        class="w-full py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-semibold text-xs flex items-center justify-center gap-2 transition border border-slate-700 cursor-pointer">
                        <i class="fa-solid fa-diamond-turn-right text-emerald-400"></i>
                        <span>Open Turn-by-Turn Navigation (Google Maps)</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Ordered Items Checklist -->
        <div class="partner-card border p-6 space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 pb-3 border-b border-slate-800 flex items-center justify-between">
                <span>Items in Order ({{ $order->totalQuantity() }})</span>
                <span class="text-emerald-400 font-mono font-bold">Total: ₹{{ number_format($order->grand_total, 2) }}</span>
            </h3>

            <div class="divide-y divide-slate-800/60 max-h-80 overflow-y-auto pr-1">
                @foreach($order->items as $item)
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="w-12 h-12 object-cover rounded-xl border border-slate-800 shrink-0">
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-white truncate">{{ $item->product_name }}</h4>
                                <p class="text-[11px] text-slate-400">
                                    @if($item->size) Size: {{ $item->size }} &bull; @endif
                                    @if($item->color) Color: {{ $item->color }} &bull; @endif
                                    Qty: <strong>{{ $item->quantity }}</strong>
                                </p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-slate-200 shrink-0">₹{{ number_format($item->subtotal, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
