@extends('admin.layouts.admin')

@section('title', 'Order Management')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-xs font-semibold text-indigo-400 mb-2">
                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                <span>Orders &amp; Fulfillment</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Order Management</h1>
            <p class="text-slate-400 text-sm mt-1">
                Review incoming orders across all shopping channels and update their fulfillment status.
            </p>
        </div>
    </div>

    <!-- Mode Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <a href="{{ route('admin.orders.index', array_merge(request()->except('mode'), ['mode' => 'all'])) }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition shrink-0 cursor-pointer {{ $tab === 'all' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' }}">
            All Channels
        </a>
        <a href="{{ route('admin.orders.index', array_merge(request()->except('mode'), ['mode' => 'null'])) }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition shrink-0 cursor-pointer {{ $tab === 'null' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' }}">
            Standard Store
        </a>
        @foreach ($modes as $m)
            <a href="{{ route('admin.orders.index', array_merge(request()->except('mode'), ['mode' => $m->id])) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition shrink-0 cursor-pointer {{ (string) $tab === (string) $m->id ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' }}">
                {{ $m->name }}
            </a>
        @endforeach
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Orders</p>
            <p class="text-2xl font-black text-white mt-1.5">{{ number_format($totalOrders) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Revenue</p>
            <p class="text-2xl font-black text-emerald-400 mt-1.5">₹{{ number_format($totalRevenue, 2) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pending</p>
            <p class="text-2xl font-black text-indigo-400 mt-1.5">{{ number_format($pendingOrders) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Processing</p>
            <p class="text-2xl font-black text-amber-400 mt-1.5">{{ number_format($processingOrders) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Shipped</p>
            <p class="text-2xl font-black text-cyan-400 mt-1.5">{{ number_format($shippedOrders) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Delivered</p>
            <p class="text-2xl font-black text-emerald-400 mt-1.5">{{ number_format($deliveredOrders) }}</p>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-center">
            <input type="hidden" name="mode" value="{{ $tab }}">

            <!-- Search Keyword -->
            <div class="relative lg:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by order number or customer..."
                    class="w-full px-4 py-2.5 pr-10 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Status Filter -->
            <div>
                <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">All Statuses</option>
                    <option value="confirmed" {{ $statusFilter === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="processing" {{ $statusFilter === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="ready-for-delivery" {{ $statusFilter === 'ready-for-delivery' ? 'selected' : '' }}>Ready for Delivery</option>
                    <option value="delivery-assigned" {{ $statusFilter === 'delivery-assigned' ? 'selected' : '' }}>Delivery Assigned</option>
                    <option value="picked-up" {{ $statusFilter === 'picked-up' ? 'selected' : '' }}>Picked Up</option>
                    <option value="shipped" {{ $statusFilter === 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="out-for-delivery" {{ $statusFilter === 'out-for-delivery' ? 'selected' : '' }}>Out for Delivery</option>
                    <option value="delivered" {{ $statusFilter === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Payment Status Filter -->
            <div>
                <select name="payment" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">All Payments</option>
                    <option value="pending" {{ $paymentFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ $paymentFilter === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="failed" {{ $paymentFilter === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="refunded" {{ $paymentFilter === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-sm transition shrink-0 cursor-pointer">
                    Filter
                </button>
                @if($search || $statusFilter || $paymentFilter || $tab !== 'all')
                    <a href="{{ route('admin.orders.index') }}" class="p-2.5 rounded-xl bg-slate-800 hover:bg-rose-900/40 text-slate-400 hover:text-rose-400 transition shrink-0" title="Reset Filters">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Orders Data Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-left border-collapse text-sm whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Order</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Channel</th>
                        <th class="py-3 px-4">Items</th>
                        <th class="py-3 px-4">Total</th>
                        <th class="py-3 px-4">Payment</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Placed On</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-sm">
                    @forelse ($orders as $order)
                        @php $badge = $order->status_badge; @endphp
                        <tr class="hover:bg-slate-800/30 transition group">
                            <!-- Order Number -->
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-xs font-black text-indigo-400">{{ $order->order_number }}</span>
                            </td>

                            <!-- Customer -->
                            <td class="py-3.5 px-4">
                                @if($order->user)
                                    <p class="font-bold text-slate-200 text-xs">{{ $order->user->name }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $order->user->email }}</p>
                                @else
                                    <span class="text-xs text-slate-500 italic">Guest</span>
                                @endif
                            </td>

                            <!-- Channel Mode -->
                            <td class="py-3.5 px-4">
                                @if($order->mode)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $order->mode->slug === 'minutes' ? 'bg-amber-400' : ($order->mode->slug === 'food' ? 'bg-rose-400' : 'bg-indigo-400') }}"></span>
                                        {{ $order->mode->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                                        <i class="fa-solid fa-store text-[10px]"></i>
                                        Standard
                                    </span>
                                @endif
                            </td>

                            <!-- Items Count -->
                            <td class="py-3.5 px-4 text-slate-300 text-xs">{{ $order->items_count }} item(s)</td>

                            <!-- Total -->
                            <td class="py-3.5 px-4 font-bold text-slate-200">₹{{ number_format($order->grand_total, 2) }}</td>

                            <!-- Payment -->
                            <td class="py-3.5 px-4">
                                <div class="text-xs space-y-1">
                                    <span class="text-slate-300 capitalize">{{ str_replace('_', ' ', $order->payment_method) }}</span>
                                    @php
                                        $payClass = match ($order->payment_status) {
                                            'paid' => 'bg-emerald-950/70 text-emerald-300 border-emerald-500/40',
                                            'failed' => 'bg-rose-950/70 text-rose-300 border-rose-500/40',
                                            'refunded' => 'bg-amber-950/70 text-amber-300 border-amber-500/40',
                                            default => 'bg-slate-800 text-slate-300 border-slate-700',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $payClass }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold {{ $badge['bg'] }} {{ $badge['text'] }}">
                                    <i class="{{ $badge['icon'] }}"></i>
                                    <span>{{ $badge['label'] }}</span>
                                </span>
                            </td>

                            <!-- Placed At -->
                            <td class="py-3.5 px-4 text-xs text-slate-400">
                                {{ $order->created_at->format('d M Y, h:i A') }}
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-indigo-600 text-slate-300 hover:text-white text-xs font-semibold transition" title="View Order">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Manage
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-500 mb-3">
                                        <i class="fa-solid fa-box-open text-xl"></i>
                                    </div>
                                    <p class="text-slate-300 font-semibold text-sm">No orders found</p>
                                    <p class="text-slate-500 text-xs mt-1">Orders placed by customers will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($orders->hasPages())
            <div class="pt-4 border-t border-slate-800">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
