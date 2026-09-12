@extends('partner.layouts.partner')

@section('title', 'Delivery Partner Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header & Duty Switch -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Rider Dashboard</h1>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $partner->is_available ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
                    <span class="w-2 h-2 rounded-full {{ $partner->is_available ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' }}"></span>
                    <span>{{ $partner->is_available ? 'ON-DUTY (ONLINE)' : 'OFF-DUTY (OFFLINE)' }}</span>
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                {{ $partner->name }} &bull; {{ ucfirst($partner->vehicle_type ?? 'Bike') }} @if($partner->vehicle_number) ({{ $partner->vehicle_number }}) @endif
            </p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <!-- Duty Status Toggle Form -->
            <form method="POST" action="{{ route('partner.toggle_availability') }}">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl font-bold text-xs transition cursor-pointer flex items-center gap-2 shadow-lg {{ $partner->is_available ? 'bg-rose-600 hover:bg-rose-500 text-white shadow-rose-600/30' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-600/30' }}">
                    <i class="fa-solid {{ $partner->is_available ? 'fa-power-off' : 'fa-play' }}"></i>
                    <span>{{ $partner->is_available ? 'Go Off-Duty' : 'Go On-Duty (Ready for Orders)' }}</span>
                </button>
            </form>

            <!-- GPS Live Broadcast Button -->
            <button type="button" onclick="broadcastLiveGps();" id="gpsBtn"
                class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-bold text-xs border border-slate-700 transition cursor-pointer flex items-center gap-2">
                <i class="fa-solid fa-location-crosshairs text-emerald-400" id="gpsIcon"></i>
                <span id="gpsText">Update My GPS</span>
            </button>
        </div>
    </div>

    <!-- Metrics Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="partner-card border p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-violet-500/10 text-violet-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-person-biking"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-white">{{ $stats['activeDeliveries'] }}</p>
                <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider">Active Deliveries</p>
            </div>
        </div>

        <div class="partner-card border p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-rotate-left"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-purple-400">{{ $stats['pendingReturns'] }}</p>
                <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider">Return Pickups</p>
            </div>
        </div>

        <div class="partner-card border p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-white">{{ $stats['deliveredToday'] }}</p>
                <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider">Delivered Today</p>
            </div>
        </div>

        <div class="partner-card border p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-white">{{ $stats['totalDelivered'] }}</p>
                <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider">Total Delivered</p>
            </div>
        </div>
    </div>

    <!-- Return Pickups Section (If any assigned) -->
    @if($returnPickups->isNotEmpty())
        <div class="partner-card border border-purple-500/40 bg-purple-950/10 p-6 rounded-3xl shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-purple-500/20">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-white">Assigned Return Pickups ({{ $returnPickups->count() }})</h2>
                        <p class="text-xs text-purple-300">Customer returns approved by admin. Please visit customer location to inspect and collect item.</p>
                    </div>
                </div>
            </div>

            <div class="divide-y divide-purple-500/20">
                @foreach($returnPickups as $rOrder)
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('partner.orders.show', $rOrder) }}" class="font-bold text-white hover:text-purple-400 transition text-sm">
                                    #{{ $rOrder->order_number }}
                                </a>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                    Return Approved
                                </span>
                            </div>
                            <p class="text-xs text-slate-300">
                                {{ $rOrder->shipping_name ?: $rOrder->userAddress?->full_name }} &bull; {{ $rOrder->formatted_shipping_address }}
                            </p>
                            <p class="text-xs text-purple-300 italic">
                                Reason: {{ $rOrder->return_reason }}
                            </p>
                        </div>
                        <a href="{{ route('partner.orders.show', $rOrder) }}" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs shadow-md shadow-purple-600/30 transition text-center shrink-0">
                            Collect Return &rarr;
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Assigned Orders Section -->
    <div class="partner-card border p-6 space-y-4">
        <!-- Section Header & Filter Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
            <div>
                <h2 class="text-base font-bold text-white">Assigned Delivery Orders</h2>
                <p class="text-xs text-slate-400">Orders allocated to you for fulfillment.</p>
            </div>

            <!-- Mode Filter Tabs -->
            <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-800 text-xs font-semibold">
                <a href="{{ route('partner.dashboard') }}" class="px-3 py-1.5 rounded-lg transition {{ empty($modeFilter) || $modeFilter === 'all' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-400 hover:text-white' }}">
                    All
                </a>
                <a href="{{ route('partner.dashboard', ['mode' => 'minutes']) }}" class="px-3 py-1.5 rounded-lg transition {{ $modeFilter === 'minutes' ? 'bg-sky-600 text-white shadow-xs' : 'text-slate-400 hover:text-white' }}">
                    Minutes
                </a>
                <a href="{{ route('partner.dashboard', ['mode' => 'food']) }}" class="px-3 py-1.5 rounded-lg transition {{ $modeFilter === 'food' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-400 hover:text-white' }}">
                    Food
                </a>
                <a href="{{ route('partner.dashboard', ['mode' => 'shopy']) }}" class="px-3 py-1.5 rounded-lg transition {{ $modeFilter === 'shopy' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-400 hover:text-white' }}">
                    Shopy
                </a>
            </div>
        </div>

        <!-- Orders List -->
        @forelse ($assignedOrders as $order)
            <div class="py-4 border-b border-slate-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('partner.orders.show', $order) }}" class="font-bold text-white hover:text-emerald-400 transition text-sm">
                            #{{ $order->order_number }}
                        </a>
                        @if($order->mode)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                {{ $order->mode->slug === 'minutes' ? 'bg-sky-500/10 text-sky-400 border border-sky-500/20' : ($order->mode->slug === 'food' ? 'bg-orange-500/10 text-orange-400 border border-orange-500/20' : 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20') }}">
                                {{ $order->mode->name }}
                            </span>
                        @endif

                        @if($order->payment_method === \App\Models\Order::PAYMENT_METHOD_COD && $order->payment_status !== 'paid')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                COD: Collect ₹{{ number_format($order->grand_total, 2) }}
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                Prepaid
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-300 mt-1 truncate">
                        <i class="fa-solid fa-location-dot text-slate-500 mr-1"></i>
                        {{ $order->formatted_shipping_address }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Items: <strong>{{ $order->totalQuantity() }}</strong> &bull; Customer: <strong>{{ $order->shipping_name ?: $order->userAddress?->full_name }}</strong>
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    @php $badge = $order->status_badge; @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $badge['bg'] }} {{ $badge['text'] }}">
                        <i class="{{ $badge['icon'] }} text-[10px]"></i>
                        <span>{{ $badge['label'] }}</span>
                    </span>

                    <a href="{{ route('partner.orders.show', $order) }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-emerald-600 text-slate-200 hover:text-white font-bold text-xs transition border border-slate-700">
                        View &amp; Deliver &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div class="py-12 text-center">
                <i class="fa-solid fa-box-open text-3xl text-slate-600 mb-3"></i>
                <p class="text-sm text-slate-400 font-medium">No assigned deliveries under this filter.</p>
                <p class="text-xs text-slate-500 mt-1">Make sure you are On-Duty (Online) to receive new dispatch allocations.</p>
            </div>
        @endforelse

        <div class="pt-4">
            {{ $assignedOrders->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
function broadcastLiveGps() {
    const btn = document.getElementById('gpsBtn');
    const text = document.getElementById('gpsText');
    const icon = document.getElementById('gpsIcon');

    if (!navigator.geolocation) {
        toastr.error('Geolocation is not supported by your browser.');
        return;
    }

    text.textContent = 'Acquiring GPS...';
    icon.classList.add('fa-spin');

    navigator.geolocation.getCurrentPosition(
        function (position) {
            fetch("{{ route('partner.location.update') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                })
            })
            .then(res => res.json())
            .then(data => {
                icon.classList.remove('fa-spin');
                text.textContent = 'GPS Broadcasted ✔';
                toastr.success('Live GPS coordinates updated successfully!');
                setTimeout(() => { text.textContent = 'Update My GPS'; }, 3000);
            })
            .catch(err => {
                icon.classList.remove('fa-spin');
                text.textContent = 'Update My GPS';
                toastr.error('Failed to broadcast location to server.');
            });
        },
        function (error) {
            icon.classList.remove('fa-spin');
            text.textContent = 'Update My GPS';
            toastr.warning('Could not acquire GPS: ' + error.message);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

// Auto-broadcast location every 30 seconds if on-duty
@if($partner->is_available)
setInterval(function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            fetch("{{ route('partner.location.update') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    latitude: pos.coords.latitude,
                    longitude: pos.coords.longitude,
                    accuracy: pos.coords.accuracy
                })
            }).catch(() => {});
        }, null, { enableHighAccuracy: true });
    }
}, 30000);
@endif
</script>
@endpush
@endsection