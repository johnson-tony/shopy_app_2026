@extends('admin.layouts.admin')

@section('title', 'Partner: ' . $partner->name)

@section('content')
@if($partner->latitude && $partner->longitude)
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endif
@php
    $badge = $partner->status_badge;
    $adminUser = auth('admin')->user();
    $canEdit = $adminUser?->isSuperAdmin() || $adminUser?->hasPermission('partners.edit') || $adminUser?->hasPermission('orders.edit');
    $canDelete = $adminUser?->isSuperAdmin() || $adminUser?->hasPermission('partners.delete');
@endphp
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.delivery_partners.index') }}" class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition cursor-pointer" title="Back to Fleet">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 font-black text-xl shrink-0">
                    {{ strtoupper(substr($partner->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h1 class="text-xl md:text-2xl font-black text-white tracking-tight">{{ $partner->name }}</h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badge['bg'] }} {{ $badge['text'] }} border {{ $badge['border'] }}">
                            <i class="{{ $badge['icon'] }}"></i>
                            <span>{{ $badge['label'] }}</span>
                        </span>
                        @if($partner->status === \App\Models\DeliveryPartner::STATUS_ACTIVE)
                            @if($partner->is_available)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Online
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                    Offline
                                </span>
                            @endif
                        @endif
                    </div>
                    <p class="text-slate-400 text-xs flex flex-wrap items-center gap-3">
                        <span><i class="fa-solid fa-phone text-slate-500 mr-1"></i> {{ $partner->phone }}</span>
                        <span>&bull;</span>
                        <span><i class="fa-solid fa-envelope text-slate-500 mr-1"></i> {{ $partner->email }}</span>
                        <span>&bull;</span>
                        <span>Joined {{ $partner->created_at->format('d M Y') }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions & Status Controls -->
        <div class="flex flex-wrap items-center gap-2.5">
            @if($canEdit)
                @if($partner->isPendingApproval())
                    <form method="POST" action="{{ route('admin.delivery_partners.approve', $partner) }}" onsubmit="return confirm('Approve partner KYC and activate fleet account?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition cursor-pointer">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Approve KYC</span>
                        </button>
                    </form>
                    <button type="button" onclick="openDeclineModal();" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-rose-950/60 text-rose-400 border border-rose-900/40 hover:border-rose-700 font-semibold text-xs transition cursor-pointer">
                        <i class="fa-solid fa-ban"></i>
                        <span>Decline KYC</span>
                    </button>
                @elseif($partner->status === \App\Models\DeliveryPartner::STATUS_ACTIVE)
                    <form method="POST" action="{{ route('admin.delivery_partners.toggle_status', $partner) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-rose-950/60 text-rose-400 border border-rose-900/40 font-semibold text-xs transition cursor-pointer" onclick="return confirm('Suspend this partner from accepting deliveries?');">
                            <i class="fa-solid fa-user-slash"></i>
                            <span>Suspend</span>
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.delivery_partners.toggle_status', $partner) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition cursor-pointer">
                            <i class="fa-solid fa-user-check"></i>
                            <span>Reactivate</span>
                        </button>
                    </form>
                @endif

                <a href="{{ route('admin.delivery_partners.edit', $partner) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold text-xs transition">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Edit</span>
                </a>
            @endif

            @if($canDelete)
                <form method="POST" action="{{ route('admin.delivery_partners.destroy', $partner) }}" onsubmit="return confirm('Permanently remove {{ $partner->name }} from fleet?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-rose-950/60 text-slate-400 hover:text-rose-400 border border-slate-700 font-semibold text-xs transition cursor-pointer" title="Delete Partner">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($partner->rejection_reason)
        <div class="p-4 rounded-2xl bg-rose-950/40 border border-rose-900/60 text-rose-300 flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-rose-400 mt-0.5 text-base"></i>
            <div>
                <span class="font-bold text-xs uppercase tracking-wider text-rose-400 block">KYC Rejection Reason</span>
                <p class="text-sm mt-0.5">{{ $partner->rejection_reason }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Column: KYC Documents & Vehicle -->
        <div class="lg:col-span-7 space-y-6">
            <!-- KYC Documents -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-sm font-black text-white tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-id-card text-indigo-400"></i>
                        <span>KYC Onboarding Documents</span>
                    </h3>
                    @if($partner->approved_at)
                        <span class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-shield-halved"></i>
                            Approved {{ $partner->approved_at->format('d M Y') }}
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Driving License -->
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-300">Driving License</span>
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-slate-800 text-slate-400">DL</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-semibold block">License Number</span>
                            <span class="text-sm font-mono font-bold text-white">{{ $partner->license_number ?? 'Not provided' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-semibold block mb-1.5">Document Photo</span>
                            @if($partner->license_image)
                                <a href="{{ asset('storage/' . $partner->license_image) }}" target="_blank" class="block group relative overflow-hidden rounded-xl border border-slate-700 aspect-video bg-slate-900 flex items-center justify-center">
                                    <img src="{{ asset('storage/' . $partner->license_image) }}" alt="License Document" class="w-full h-full object-cover">
                                    <span class="absolute inset-0 bg-black/40 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition text-xs font-semibold">
                                        <i class="fa-solid fa-expand mr-1.5"></i> Inspect Document
                                    </span>
                                </a>
                            @else
                                <div class="h-24 rounded-xl bg-slate-900 border border-dashed border-slate-800 flex items-center justify-center text-slate-600 text-xs italic">
                                    No document uploaded
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Government ID Proof -->
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-300">Identity Proof</span>
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-slate-800 text-indigo-400">{{ strtoupper($partner->id_proof_type ?? 'ID') }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-semibold block">ID Number</span>
                            <span class="text-sm font-mono font-bold text-white">{{ $partner->id_proof_number ?? 'Not provided' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-semibold block mb-1.5">Document Photo</span>
                            @if($partner->id_proof_image)
                                <a href="{{ asset('storage/' . $partner->id_proof_image) }}" target="_blank" class="block group relative overflow-hidden rounded-xl border border-slate-700 aspect-video bg-slate-900 flex items-center justify-center">
                                    <img src="{{ asset('storage/' . $partner->id_proof_image) }}" alt="ID Proof Document" class="w-full h-full object-cover">
                                    <span class="absolute inset-0 bg-black/40 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition text-xs font-semibold">
                                        <i class="fa-solid fa-expand mr-1.5"></i> Inspect Document
                                    </span>
                                </a>
                            @else
                                <div class="h-24 rounded-xl bg-slate-900 border border-dashed border-slate-800 flex items-center justify-center text-slate-600 text-xs italic">
                                    No document uploaded
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Vehicle & Banking Details -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-800">
                    <div class="space-y-2 text-xs">
                        <span class="text-[10px] uppercase font-bold text-slate-500 block">Vehicle Information</span>
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                            <span class="text-slate-400">Vehicle Type</span>
                            <span class="font-bold text-white">{{ ucfirst($partner->vehicle_type ?? 'Bike') }}</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                            <span class="text-slate-400">Plate Number</span>
                            <span class="font-mono font-bold text-indigo-400">{{ $partner->vehicle_number ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs">
                        <span class="text-[10px] uppercase font-bold text-slate-500 block">Payout &amp; Banking</span>
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                            <span class="text-slate-400">Bank Account</span>
                            <span class="font-mono font-bold text-white">{{ $partner->bank_account_number ? '••••' . substr($partner->bank_account_number, -4) : 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                            <span class="text-slate-400">IFSC / UPI</span>
                            <span class="font-mono font-bold text-white">{{ $partner->upi_id ?: ($partner->bank_ifsc ?? 'N/A') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Delivery Orders -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <h3 class="text-sm font-black text-white tracking-tight pb-3 border-b border-slate-800 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-box-open text-indigo-400"></i>
                        <span>Assigned Deliveries ({{ $partner->orders->count() }})</span>
                    </span>
                    <span class="text-xs font-semibold text-slate-400">Latest 10</span>
                </h3>

                @if($partner->orders->isEmpty())
                    <p class="text-xs text-slate-500 italic text-center py-6">No delivery orders assigned to this partner yet.</p>
                @else
                    <div class="divide-y divide-slate-800/60">
                        @foreach($partner->orders as $order)
                            <div class="py-3 flex items-center justify-between gap-4 text-xs">
                                <div>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-bold text-indigo-400 hover:text-indigo-300">
                                        {{ $order->order_number }}
                                    </a>
                                    <p class="text-slate-400 text-[11px] mt-0.5">
                                        Customer: {{ $order->user?->name ?? 'Guest' }} &bull; {{ $order->created_at->format('d M Y, h:i A') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-white">₹{{ number_format($order->grand_total, 2) }}</span>
                                    @php $oBadge = $order->status_badge; @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $oBadge['bg'] }} {{ $oBadge['text'] }}">
                                        {{ $oBadge['label'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Shopping Channels & Live Telemetry -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Channel Assignment Form -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <div class="pb-3 border-b border-slate-800">
                    <h3 class="text-sm font-black text-white tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-layer-group text-indigo-400"></i>
                        <span>Authorized Channels</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Select which shopping modes this partner can deliver for.</p>
                </div>

                <form method="POST" action="{{ route('admin.delivery_partners.approve', $partner) }}" class="space-y-3">
                    @csrf
                    <div class="space-y-2">
                        @foreach($allModes as $mode)
                            <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 transition cursor-pointer">
                                <input type="checkbox"
                                       name="modes[]"
                                       value="{{ $mode->id }}"
                                       {{ $partner->modes->contains($mode->id) ? 'checked' : '' }}
                                       class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4 bg-slate-900 border-slate-700">
                                <div>
                                    <span class="font-bold text-white text-xs block">{{ $mode->name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ ucfirst($mode->slug) }} channel deliveries</span>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition cursor-pointer">
                        Save Channel Authorizations
                    </button>
                </form>
            </div>

            <!-- Live GPS & Telemetry Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-3 text-xs">
                <h3 class="text-sm font-black text-white tracking-tight pb-3 border-b border-slate-800 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-location-crosshairs text-indigo-400"></i>
                        <span>GPS Telemetry</span>
                    </span>
                    @if($partner->latitude && $partner->longitude)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Live Pin
                        </span>
                    @endif
                </h3>

                @if($partner->latitude && $partner->longitude)
                    <div id="partnerMap" class="w-full h-48 rounded-2xl overflow-hidden border border-slate-800 relative z-0"></div>
                @else
                    <div class="p-4 rounded-xl bg-slate-950 border border-dashed border-slate-800 text-center text-slate-500 italic text-xs">
                        <i class="fa-solid fa-satellite-dish text-slate-600 block text-lg mb-1"></i>
                        No GPS coordinates reported yet.
                    </div>
                @endif

                <div class="space-y-2 pt-1">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                        <span class="text-slate-400">Current Latitude</span>
                        <span class="font-mono font-bold text-white">{{ $partner->latitude ?? 'Not reported' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                        <span class="text-slate-400">Current Longitude</span>
                        <span class="font-mono font-bold text-white">{{ $partner->longitude ?? 'Not reported' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                        <span class="text-slate-400">Last Broadcast</span>
                        <span class="text-slate-300 font-medium">{{ $partner->last_location_at ? $partner->last_location_at->diffForHumans() : 'Never' }}</span>
                    </div>
                </div>

                @if($partner->latitude && $partner->longitude)
                    <a href="https://www.google.com/maps?q={{ $partner->latitude }},{{ $partner->longitude }}" target="_blank" class="w-full inline-flex items-center justify-center gap-2 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs transition">
                        <i class="fa-solid fa-map-location-dot"></i>
                        <span>Open on Google Maps</span>
                    </a>
                @endif
            </div>

            <!-- Return Pickup Tasks -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-3 text-xs">
                <h3 class="text-sm font-black text-white tracking-tight pb-3 border-b border-slate-800 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-rotate-left text-amber-400"></i>
                        <span>Return Pickup Tasks ({{ $partner->returnOrders->count() }})</span>
                    </span>
                </h3>

                @if($partner->returnOrders->isEmpty())
                    <p class="text-xs text-slate-500 italic text-center py-4">No return pickups assigned to this partner.</p>
                @else
                    <div class="divide-y divide-slate-800/60">
                        @foreach($partner->returnOrders as $rOrder)
                            <div class="py-2.5 flex items-center justify-between">
                                <div>
                                    <a href="{{ route('admin.orders.show', $rOrder) }}" class="font-mono font-bold text-purple-400 hover:text-purple-300">
                                        {{ $rOrder->order_number }}
                                    </a>
                                    <p class="text-[10px] text-slate-400">Customer: {{ $rOrder->user?->name ?? 'Guest' }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $rOrder->status === \App\Models\Order::STATUS_RETURNED ? 'bg-emerald-950 text-emerald-300' : 'bg-purple-950 text-purple-300' }}">
                                    {{ $rOrder->status === \App\Models\Order::STATUS_RETURNED ? 'Completed' : 'Assigned' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Decline KYC Modal -->
<div id="declineKycModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs">
    <div class="bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-800 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-black text-white">Decline KYC Registration</h3>
            <button type="button" onclick="closeDeclineModal();" class="w-8 h-8 rounded-full bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center cursor-pointer">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <p class="text-xs text-slate-400">
            State the exact reason for declining this partner's onboarding documents (e.g. expired driving license, illegible ID photo, invalid vehicle number).
        </p>
        <form method="POST" action="{{ route('admin.delivery_partners.reject', $partner) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Decline Reason *</label>
                <textarea name="rejection_reason" rows="3" required placeholder="State why documents were declined..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500 transition"></textarea>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeDeclineModal();" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800 cursor-pointer">
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
    function openDeclineModal() {
        document.getElementById('declineKycModal')?.classList.remove('hidden');
    }
    function closeDeclineModal() {
        document.getElementById('declineKycModal')?.classList.add('hidden');
    }
</script>

@if($partner->latitude && $partner->longitude)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const lat = {{ $partner->latitude }};
        const lng = {{ $partner->longitude }};
        const partnerMap = L.map('partnerMap').setView([lat, lng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(partnerMap);

        const markerIcon = L.divIcon({
            className: 'partner-gps-pin',
            html: '<div style="background:#4f46e5;color:white;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(79,70,229,0.6);border:2.5px solid white;"><i class="fa-solid fa-person-biking" style="font-size:16px;"></i></div>',
            iconSize: [36, 36],
            iconAnchor: [18, 18]
        });

        L.marker([lat, lng], { icon: markerIcon }).addTo(partnerMap)
            .bindPopup("<strong>{{ addslashes($partner->name) }}</strong><br>Last GPS Broadcast")
            .openPopup();
    });
</script>
@endif
@endsection
