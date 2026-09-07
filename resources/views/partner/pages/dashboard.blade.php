@extends('partner.layouts.partner')

@section('title', 'Dashboard')

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Dashboard</h1>
            <p class="text-xs text-slate-400 mt-1">Here are your assigned deliveries for today.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
            <i class="fa-solid fa-location-dot text-[11px]"></i> Live Dispatch Area
        </span>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
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
                <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider">Lifetime Delivered</p>
            </div>
        </div>
    </div>

    <!-- Assigned Deliveries List -->
    <div class="partner-card border p-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div>
                <h2 class="text-base font-bold text-white">Assigned Deliveries</h2>
                <p class="text-xs text-slate-400">Orders assigned to you by the dispatch system.</p>
            </div>
            <span class="text-[11px] text-slate-500 font-mono">{{ $assignedOrders->total() }} total</span>
        </div>

        @forelse ($assignedOrders as $order)
            <div class="py-4 border-b border-slate-800/60 flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="#" class="font-semibold text-white hover:text-emerald-400 transition">{{ $order->order_number }}</a>
                        @if($order->mode)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                {{ $order->mode->slug === 'minutes' ? 'bg-sky-500/10 text-sky-400 border border-sky-500/20' : ($order->mode->slug === 'food' ? 'bg-orange-500/10 text-orange-400 border border-orange-500/20' : 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20') }}">
                                {{ $order->mode->name }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ $order->userAddress?->city ?: 'Address on file' }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    @php $badge = $order->status_badge; @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $badge['bg'] }} {{ $badge['text'] }}">
                        <i class="{{ $badge['icon'] }}"></i> {{ $badge['label'] }}
                    </span>
                    <span class="text-sm font-bold text-slate-200">₹{{ number_format($order->grand_total, 2) }}</span>
                </div>
            </div>
        @empty
            <div class="py-12 text-center">
                <i class="fa-solid fa-box-open text-3xl text-slate-600 mb-3"></i>
                <p class="text-sm text-slate-400 font-medium">No assigned deliveries yet.</p>
                <p class="text-xs text-slate-500 mt-1">New orders matched to you will appear here in real time once dispatch is live.</p>
            </div>
        @endforelse

        <div class="pt-4">
            {{ $assignedOrders->links() }}
        </div>
    </div>
@endsection