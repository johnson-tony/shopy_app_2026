@extends('admin.layouts.admin')

@section('title', 'Create Coupon')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.coupons.index') }}" class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white tracking-tight">Create Promotional Coupon</h1>
                <p class="text-slate-400 text-xs mt-0.5">Design a new discount voucher code with targeted rules and store channels.</p>
            </div>
        </div>
    </div>

    <!-- Coupon Form -->
    <form method="POST" action="{{ route('admin.coupons.store') }}" class="space-y-6" id="couponForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Core Fields (2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Details Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                        Coupon Voucher Identity
                    </h2>

                    <!-- Code & Generator -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="code" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                                Coupon Code <span class="text-rose-400">*</span>
                            </label>
                            <button type="button" onclick="generateRandomCode()" class="text-xs text-indigo-400 hover:text-indigo-300 transition font-medium flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-arrows-rotate text-[11px]"></i>
                                Generate Random
                            </button>
                        </div>
                        <div class="relative">
                            <input id="code" type="text" name="code" value="{{ old('code') }}" required
                                placeholder="e.g. FESTIVE50, SUMMER20"
                                class="w-full px-4 py-2.5 uppercase font-mono font-bold tracking-wider rounded-xl bg-slate-950 border border-slate-800 text-sm text-indigo-300 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition @error('code') border-rose-500 @enderror">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Uppercase letters, numbers, and dashes. Must be unique.</p>
                        @error('code')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Title / Headline -->
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Offer Title / Headline <span class="text-rose-400">*</span>
                        </label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required
                            placeholder="e.g. 50% Off First Order"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition @error('name') border-rose-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Description / Terms &amp; Conditions
                        </label>
                        <textarea id="description" name="description" rows="3"
                            placeholder="e.g. Valid on all groceries with minimum purchase of ₹299. Applicable once per user."
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition @error('description') border-rose-500 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Discount Rules & Calculations -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-calculator text-indigo-400 text-sm"></i>
                        Discount Calculation &amp; Limits
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Discount Type -->
                        <div>
                            <label for="type" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Discount Type <span class="text-rose-400">*</span>
                            </label>
                            <select id="type" name="type" required onchange="handleTypeChange()" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition @error('type') border-rose-500 @enderror">
                                <option value="percentage" {{ old('type', 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                                <option value="free_delivery" {{ old('type') === 'free_delivery' ? 'selected' : '' }}>Free Delivery</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Discount Value -->
                        <div id="valueWrapper">
                            <label for="value" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Discount Value <span id="valueUnit" class="text-indigo-400 font-bold">(%)</span> <span class="text-rose-400">*</span>
                            </label>
                            <input id="value" type="number" step="0.01" min="0" name="value" value="{{ old('value', '10') }}" required
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition @error('value') border-rose-500 @enderror">
                            @error('value')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Max Discount Cap -->
                        <div id="maxCapWrapper">
                            <label for="max_discount_amount" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Maximum Discount Cap (₹)
                            </label>
                            <input id="max_discount_amount" type="number" step="0.01" min="0" name="max_discount_amount" value="{{ old('max_discount_amount') }}"
                                placeholder="e.g. 150 (Leave blank for no cap)"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition @error('max_discount_amount') border-rose-500 @enderror">
                            <p class="text-[11px] text-slate-500 mt-1">Prevents excessive discount on large orders.</p>
                            @error('max_discount_amount')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Minimum Order Amount -->
                        <div>
                            <label for="min_order_amount" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Minimum Subtotal (₹)
                            </label>
                            <input id="min_order_amount" type="number" step="0.01" min="0" name="min_order_amount" value="{{ old('min_order_amount', '0') }}"
                                placeholder="e.g. 299"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition @error('min_order_amount') border-rose-500 @enderror">
                            <p class="text-[11px] text-slate-500 mt-1">Subtotal must reach this before code can apply.</p>
                            @error('min_order_amount')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Channel Scope & Usage Limits -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-store text-indigo-400 text-sm"></i>
                        Store Channel &amp; Usage Quotas
                    </h2>

                    <!-- Shopping Mode Selector -->
                    <div>
                        <label for="mode_id" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Shopping Channel Mode
                        </label>
                        <select id="mode_id" name="mode_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition @error('mode_id') border-rose-500 @enderror">
                            <option value="">Universal (Valid Across All Channels)</option>
                            @foreach ($modes as $mode)
                                <option value="{{ $mode->id }}" {{ old('mode_id') == $mode->id ? 'selected' : '' }}>
                                    {{ $mode->name }} Only ({{ $mode->slug }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">Leave as Universal or lock exclusively to Minutes, Food, or Shopy.</p>
                        @error('mode_id')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Total Usage Limit -->
                        <div>
                            <label for="usage_limit" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Total Redemptions Allowed
                            </label>
                            <input id="usage_limit" type="number" min="1" name="usage_limit" value="{{ old('usage_limit') }}"
                                placeholder="e.g. 500 (Blank for unlimited)"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition @error('usage_limit') border-rose-500 @enderror">
                            @error('usage_limit')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Per User Limit -->
                        <div>
                            <label for="usage_limit_per_user" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Max Redemptions Per Customer
                            </label>
                            <input id="usage_limit_per_user" type="number" min="1" name="usage_limit_per_user" value="{{ old('usage_limit_per_user', '1') }}"
                                placeholder="e.g. 1"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition @error('usage_limit_per_user') border-rose-500 @enderror">
                            @error('usage_limit_per_user')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Starts At -->
                        <div>
                            <label for="starts_at" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Valid From (Start Date &amp; Time)
                            </label>
                            <input id="starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition @error('starts_at') border-rose-500 @enderror">
                            <p class="text-[11px] text-slate-500 mt-1">Leave blank to activate immediately.</p>
                            @error('starts_at')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Expires At -->
                        <div>
                            <label for="expires_at" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Valid Until (Expiry Date &amp; Time)
                            </label>
                            <input id="expires_at" type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition @error('expires_at') border-rose-500 @enderror">
                            <p class="text-[11px] text-slate-500 mt-1">Leave blank for no expiration date.</p>
                            @error('expires_at')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Live Voucher Preview & Status (1 col) -->
            <div class="space-y-6">
                <!-- Status Switch Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Publishing Status
                    </h2>

                    <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-950 border border-slate-800">
                        <div>
                            <label for="status" class="text-sm font-semibold text-slate-200 block cursor-pointer">
                                Active Status
                            </label>
                            <span class="text-[11px] text-slate-500">Customers can apply this coupon code.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="status" name="status" value="1" {{ old('status', '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>
                </div>

                <!-- Live Voucher Preview Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4 sticky top-6">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-eye text-indigo-400 text-sm"></i>
                        Live Voucher Preview
                    </h2>

                    <!-- Visual Ticket Card -->
                    <div class="relative bg-gradient-to-br from-indigo-950/80 via-slate-900 to-slate-950 border border-indigo-500/30 rounded-2xl p-5 shadow-lg overflow-hidden">
                        <!-- Top Cutouts (Decorative Ticket Notches) -->
                        <div class="flex items-center justify-between border-b border-dashed border-slate-700/80 pb-3.5 mb-3.5">
                            <span id="previewModeBadge" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                                Universal Channel
                            </span>
                            <span id="previewDiscountBadge" class="text-xs font-black text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">
                                10% OFF
                            </span>
                        </div>

                        <!-- Ticket Body -->
                        <div class="space-y-1.5">
                            <h3 id="previewTitle" class="text-base font-black text-white tracking-tight">
                                Offer Title
                            </h3>
                            <p id="previewDescription" class="text-xs text-slate-400 line-clamp-2">
                                Terms and description will appear here...
                            </p>
                        </div>

                        <!-- Coupon Code Notch Box -->
                        <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">CODE:</span>
                                <span id="previewCode" class="font-mono font-black text-sm text-indigo-300 tracking-wider bg-slate-950 px-2 py-1 rounded border border-indigo-500/30">
                                    PROMOCODE
                                </span>
                            </div>
                            <span id="previewMinOrder" class="text-[11px] text-slate-400 font-medium">
                                Min ₹0
                            </span>
                        </div>
                    </div>

                    <!-- Helpful Tips Card -->
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-slate-400 space-y-2">
                        <p class="font-bold text-slate-200 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info text-indigo-400"></i>
                            Coupon Tips
                        </p>
                        <p>• Setting a <strong>Max Discount Cap</strong> ensures percentage vouchers do not cost more than intended on bulk purchases.</p>
                        <p>• Selecting a specific channel limits usability so quick-grocery codes aren't claimed on standard parcel products.</p>
                    </div>

                    <!-- Form Action Buttons -->
                    <div class="pt-2 flex items-center gap-3">
                        <button type="submit" class="flex-1 py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-md shadow-indigo-600/30 transition cursor-pointer text-center">
                            Save Coupon
                        </button>
                        <a href="{{ route('admin.coupons.index') }}" class="py-3 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-sm transition text-center cursor-pointer">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function generateRandomCode() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let code = 'SAVE';
    for (let i = 0; i < 4; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    const codeInput = document.getElementById('code');
    codeInput.value = code;
    updatePreview();
}

function handleTypeChange() {
    const type = document.getElementById('type').value;
    const valueWrapper = document.getElementById('valueWrapper');
    const valueUnit = document.getElementById('valueUnit');
    const maxCapWrapper = document.getElementById('maxCapWrapper');
    const valInput = document.getElementById('value');

    if (type === 'percentage') {
        valueWrapper.style.display = 'block';
        valueUnit.textContent = '(%)';
        maxCapWrapper.style.display = 'block';
        valInput.max = '100';
    } else if (type === 'fixed') {
        valueWrapper.style.display = 'block';
        valueUnit.textContent = '(₹)';
        maxCapWrapper.style.display = 'none';
        valInput.removeAttribute('max');
    } else { // free_delivery
        valueWrapper.style.display = 'none';
        maxCapWrapper.style.display = 'none';
        valInput.value = '0';
    }
    updatePreview();
}

function updatePreview() {
    const code = document.getElementById('code').value.trim() || 'PROMOCODE';
    const title = document.getElementById('name').value.trim() || 'Offer Title';
    const desc = document.getElementById('description').value.trim() || 'Terms and description will appear here...';
    const type = document.getElementById('type').value;
    const val = parseFloat(document.getElementById('value').value) || 0;
    const minOrder = parseFloat(document.getElementById('min_order_amount').value) || 0;
    const modeSelect = document.getElementById('mode_id');
    const modeText = modeSelect.options[modeSelect.selectedIndex].text;

    document.getElementById('previewCode').textContent = code.toUpperCase();
    document.getElementById('previewTitle').textContent = title;
    document.getElementById('previewDescription').textContent = desc;
    document.getElementById('previewModeBadge').innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span> ${modeText}`;
    document.getElementById('previewMinOrder').textContent = minOrder > 0 ? `Min ₹${minOrder}` : 'No Min';

    let badgeText = '';
    if (type === 'percentage') {
        badgeText = `${val}% OFF`;
    } else if (type === 'fixed') {
        badgeText = `₹${val} FLAT`;
    } else {
        badgeText = 'FREE DELIVERY';
    }
    document.getElementById('previewDiscountBadge').textContent = badgeText;
}

document.addEventListener('DOMContentLoaded', () => {
    ['code', 'name', 'description', 'value', 'min_order_amount', 'mode_id'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', updatePreview);
    });
    handleTypeChange();
    updatePreview();
});
</script>
@endsection
