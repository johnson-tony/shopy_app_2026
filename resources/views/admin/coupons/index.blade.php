@extends('admin.layouts.admin')

@section('title', 'Coupons & Offers Management')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-xs font-semibold text-indigo-400 mb-2">
                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                <span>Marketing &amp; Promotions</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Coupons &amp; Offers</h1>
            <p class="text-slate-400 text-sm mt-1">
                Create promotional discount codes, manage channel restrictions, and track redemption analytics.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.coupons.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-600/30 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Create Coupon</span>
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Total Coupons -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Coupons</p>
            <p class="text-2xl font-black text-white mt-1.5">{{ $totalCoupons }}</p>
        </div>
        <!-- Active -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Offers</p>
            <p class="text-2xl font-black text-emerald-400 mt-1.5">{{ $activeCoupons }}</p>
        </div>
        <!-- Total Redemptions -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Times Redeemed</p>
            <p class="text-2xl font-black text-indigo-400 mt-1.5">{{ number_format($totalRedemptions) }}</p>
        </div>
        <!-- Total Discount Value -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Savings Given</p>
            <p class="text-2xl font-black text-amber-400 mt-1.5">₹{{ number_format($totalDiscountGiven, 2) }}</p>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl">
        <form method="GET" action="{{ route('admin.coupons.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-center">
            <!-- Search Keyword -->
            <div class="relative lg:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by coupon code, title, or description..."
                    class="w-full px-4 py-2.5 pr-10 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Mode Filter -->
            <div>
                <select name="mode" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">All Channels</option>
                    <option value="all_modes" {{ $modeFilter === 'all_modes' ? 'selected' : '' }}>Universal Only (All Modes)</option>
                    @foreach ($modes as $m)
                        <option value="{{ $m->id }}" {{ (string) $modeFilter === (string) $m->id ? 'selected' : '' }}>
                            {{ $m->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Type Filter -->
            <div>
                <select name="type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">All Types</option>
                    <option value="percentage" {{ $typeFilter === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                    <option value="fixed" {{ $typeFilter === 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                    <option value="free_delivery" {{ $typeFilter === 'free_delivery' ? 'selected' : '' }}>Free Delivery</option>
                </select>
            </div>

            <!-- Status Filter & Submit -->
            <div class="flex items-center gap-2">
                <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="expired" {{ $statusFilter === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>

                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-sm transition shrink-0 cursor-pointer">
                    Filter
                </button>
                @if($search || $modeFilter || $typeFilter || $statusFilter)
                    <a href="{{ route('admin.coupons.index') }}" class="p-2.5 rounded-xl bg-slate-800 hover:bg-rose-900/40 text-slate-400 hover:text-rose-400 transition shrink-0" title="Reset Filters">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Coupons Data Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-left border-collapse text-sm whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Coupon Code &amp; Title</th>
                        <th class="py-3 px-4">Discount</th>
                        <th class="py-3 px-4">Channel Mode</th>
                        <th class="py-3 px-4">Min Order</th>
                        <th class="py-3 px-4">Validity</th>
                        <th class="py-3 px-4">Redemptions</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-sm">
                    @forelse ($coupons as $coupon)
                        <tr class="hover:bg-slate-800/30 transition group">
                            <!-- Code & Title -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-start gap-2.5">
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-mono font-black tracking-wider bg-indigo-500/10 text-indigo-400 border border-indigo-500/30">
                                                {{ $coupon->code }}
                                            </span>
                                            <button type="button" onclick="navigator.clipboard.writeText('{{ $coupon->code }}'); toastr.success('Code {{ $coupon->code }} copied!')" class="text-slate-500 hover:text-slate-300 transition cursor-pointer" title="Copy code">
                                                <i class="fa-regular fa-copy text-xs"></i>
                                            </button>
                                        </div>
                                        <p class="font-bold text-slate-200 mt-1 text-xs whitespace-nowrap">{{ $coupon->name }}</p>
                                        @if($coupon->description)
                                            <p class="text-[11px] text-slate-400 whitespace-nowrap">{{ $coupon->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Discount Type & Value -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($coupon->type === 'percentage')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        <i class="fa-solid fa-percent text-[10px]"></i>
                                        {{ (float) $coupon->value }}% OFF
                                    </span>
                                    @if($coupon->max_discount_amount)
                                        <div class="text-[10px] text-slate-500 mt-0.5">Cap: ₹{{ number_format($coupon->max_discount_amount) }}</div>
                                    @endif
                                @elseif($coupon->type === 'fixed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <i class="fa-solid fa-indian-rupee-sign text-[10px]"></i>
                                        ₹{{ number_format($coupon->value) }} FLAT
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                        <i class="fa-solid fa-truck-fast text-[10px]"></i>
                                        Free Delivery
                                    </span>
                                @endif
                            </td>

                            <!-- Channel Mode -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($coupon->mode)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $coupon->mode->slug === 'minutes' ? 'bg-amber-400' : ($coupon->mode->slug === 'food' ? 'bg-rose-400' : 'bg-indigo-400') }}"></span>
                                        {{ $coupon->mode->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                                        <i class="fa-solid fa-globe text-[10px]"></i>
                                        All Channels
                                    </span>
                                @endif
                            </td>

                            <!-- Min Order -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($coupon->min_order_amount > 0)
                                    <span class="text-xs font-semibold text-slate-300">₹{{ number_format($coupon->min_order_amount) }}</span>
                                @else
                                    <span class="text-xs text-slate-500">No minimum</span>
                                @endif
                            </td>

                            <!-- Validity Date -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="text-xs space-y-0.5">
                                    @if($coupon->expires_at)
                                        @if($coupon->expires_at->isPast())
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-950/70 text-rose-300 border border-rose-500/30">
                                                Expired {{ $coupon->expires_at->format('d M Y') }}
                                            </span>
                                        @else
                                            <div class="text-slate-300 font-medium">{{ $coupon->expires_at->format('d M Y') }}</div>
                                            <div class="text-[10px] text-slate-500">{{ $coupon->expires_at->diffForHumans() }}</div>
                                        @endif
                                    @else
                                        <span class="text-slate-500 text-[11px]">No Expiration</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Redemptions Progress -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="font-bold text-slate-200">{{ $coupon->times_used }}</span>
                                        <span class="text-slate-500">/ {{ $coupon->usage_limit ? $coupon->usage_limit : '∞' }}</span>
                                    </div>
                                    @if($coupon->usage_limit)
                                        @php
                                            $percent = min(100, round(($coupon->times_used / $coupon->usage_limit) * 100));
                                        @endphp
                                        <div class="w-24 bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $percent }}%"></div>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Active Status Toggle -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.coupons.toggleStatus', $coupon) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold transition cursor-pointer {{ $coupon->status ? 'bg-emerald-950/70 text-emerald-300 border border-emerald-500/40 hover:bg-emerald-900/60' : 'bg-rose-950/70 text-rose-300 border border-rose-500/40 hover:bg-rose-900/60' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $coupon->status ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                        {{ $coupon->status ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- View Details / Usages -->
                                    <a href="{{ route('admin.coupons.show', $coupon) }}" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition" title="View Details & Usages">
                                        <i class="fa-solid fa-chart-pie text-xs"></i>
                                    </a>

                                    <!-- Edit Coupon -->
                                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="p-2 rounded-lg bg-slate-800 hover:bg-indigo-600 text-slate-300 hover:text-white transition" title="Edit Coupon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <!-- Delete Coupon -->
                                    <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Are you sure you want to delete coupon promo \'{{ $coupon->code }}\'?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white transition cursor-pointer" title="Delete Coupon">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-500 mb-3">
                                        <i class="fa-solid fa-ticket text-xl"></i>
                                    </div>
                                    <p class="text-slate-300 font-semibold text-sm">No coupons found</p>
                                    <p class="text-slate-500 text-xs mt-1">Get started by creating your first promotional coupon voucher.</p>
                                    <a href="{{ route('admin.coupons.create') }}" class="mt-4 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition">
                                        + Create Coupon
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($coupons->hasPages())
            <div class="pt-4 border-t border-slate-800">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
