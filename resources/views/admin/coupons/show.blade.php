@extends('admin.layouts.admin')

@section('title', 'Coupon Analytics - ' . $coupon->code)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.coupons.index') }}" class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-mono font-black tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        {{ $coupon->code }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $coupon->status ? 'bg-emerald-950/70 text-emerald-300 border border-emerald-500/40' : 'bg-rose-950/70 text-rose-300 border border-rose-500/40' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $coupon->status ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                        {{ $coupon->status ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <h1 class="text-xl md:text-2xl font-black text-white tracking-tight">{{ $coupon->name }}</h1>
                @if($coupon->description)
                    <p class="text-slate-400 text-xs mt-1">{{ $coupon->description }}</p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/30 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Edit Coupon</span>
            </a>
        </div>
    </div>

    <!-- Analytics & Details Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Times Redeemed -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-xs font-semibold uppercase tracking-wider">Redemptions</span>
                <i class="fa-solid fa-receipt text-indigo-400"></i>
            </div>
            <p class="text-2xl font-black text-white">
                {{ number_format($coupon->times_used) }}
                <span class="text-xs text-slate-500 font-normal">/ {{ $coupon->usage_limit ?: 'Unlimited' }}</span>
            </p>
            @if($coupon->usage_limit)
                @php
                    $rate = min(100, round(($coupon->times_used / $coupon->usage_limit) * 100));
                @endphp
                <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $rate }}%"></div>
                </div>
            @endif
        </div>

        <!-- Total Discount Disbursed -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Disbursed</span>
                <i class="fa-solid fa-indian-rupee-sign text-emerald-400"></i>
            </div>
            <p class="text-2xl font-black text-emerald-400">
                ₹{{ number_format($totalSavings, 2) }}
            </p>
            <p class="text-[11px] text-slate-500">Savings delivered to shoppers</p>
        </div>

        <!-- Offer Specs -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-xs font-semibold uppercase tracking-wider">Offer Value</span>
                <i class="fa-solid fa-tag text-amber-400"></i>
            </div>
            <p class="text-xl font-black text-amber-400">
                @if($coupon->type === 'percentage')
                    {{ (float) $coupon->value }}% OFF
                    @if($coupon->max_discount_amount)
                        <span class="text-xs text-slate-500 block font-normal">Cap ₹{{ number_format($coupon->max_discount_amount) }}</span>
                    @endif
                @elseif($coupon->type === 'fixed')
                    ₹{{ number_format($coupon->value) }} FLAT
                @else
                    FREE DELIVERY
                @endif
            </p>
            <p class="text-[11px] text-slate-500">
                Min Order: {{ $coupon->min_order_amount > 0 ? '₹' . number_format($coupon->min_order_amount) : 'None' }}
            </p>
        </div>

        <!-- Scope & Channel -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-xs font-semibold uppercase tracking-wider">Store Channel</span>
                <i class="fa-solid fa-store text-cyan-400"></i>
            </div>
            <p class="text-lg font-black text-white">
                {{ $coupon->mode ? $coupon->mode->name : 'All Channels' }}
            </p>
            <p class="text-[11px] text-slate-500">
                @if($coupon->expires_at)
                    Expires {{ $coupon->expires_at->format('d M Y') }}
                @else
                    No Expiration Date
                @endif
            </p>
        </div>
    </div>

    <!-- Redemption Logs History Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div>
                <h2 class="text-base font-black text-white tracking-tight">Redemption History Audit</h2>
                <p class="text-slate-400 text-xs mt-0.5">Logs of every customer order where this code was applied.</p>
            </div>
            <span class="text-xs text-slate-400 font-mono bg-slate-950 px-3 py-1 rounded-xl border border-slate-800">
                {{ $usages->total() }} total redemptions
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Order Reference</th>
                        <th class="py-3 px-4">Discount Applied</th>
                        <th class="py-3 px-4 text-right">Redeemed At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-sm">
                    @forelse ($usages as $usage)
                        <tr class="hover:bg-slate-800/30 transition">
                            <!-- Customer -->
                            <td class="py-3.5 px-4">
                                @if($usage->user)
                                    <div>
                                        <p class="font-bold text-slate-200 text-xs">{{ $usage->user->name }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $usage->user->email }}</p>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-500 italic">Guest Checkout</span>
                                @endif
                            </td>

                            <!-- Order -->
                            <td class="py-3.5 px-4 font-mono text-xs text-slate-300">
                                @if($usage->order_id)
                                    #ORD-{{ str_pad($usage->order_id, 6, '0', STR_PAD_LEFT) }}
                                @else
                                    <span class="text-slate-500 italic">Cart Checkout</span>
                                @endif
                            </td>

                            <!-- Discount Applied -->
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 font-bold text-emerald-400 text-xs bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">
                                    - ₹{{ number_format($usage->discount_amount, 2) }}
                                </span>
                            </td>

                            <!-- Timestamp -->
                            <td class="py-3.5 px-4 text-right text-xs text-slate-400">
                                {{ $usage->used_at ? $usage->used_at->format('d M Y, h:i A') : 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-10 h-10 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-500 mb-2">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </div>
                                    <p class="text-slate-300 font-semibold text-xs">No redemptions recorded yet</p>
                                    <p class="text-slate-500 text-[11px] mt-0.5">When customers redeem this coupon in their cart or checkout, logs will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($usages->hasPages())
            <div class="pt-4 border-t border-slate-800">
                {{ $usages->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
