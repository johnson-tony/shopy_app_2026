@extends('user.layouts.app')

@section('title', 'Exclusive Coupons & Offers — Shopy')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('home') }}" class="hover:text-indigo-600 transition">Home</a>
                <span>/</span>
                <span class="text-slate-700 dark:text-slate-300 font-medium">Coupons &amp; Offers</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                <span class="text-amber-500">🏷️</span>
                <span>Coupons &amp; Offers</span>
                <span class="text-sm font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                    {{ $coupons->total() }} {{ Str::plural('offer', $coupons->total()) }}
                </span>
            </h1>
        </div>

        <a href="{{ route('cart.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-200 dark:border-indigo-900/50 transition">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>View My Cart</span>
        </a>
    </div>

    <!-- Mode Filter Tabs -->
    @if(isset($modes) && count($modes) > 1)
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
            <!-- All Offers -->
            <a href="{{ route('coupons.index', ['mode' => 'all']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ empty($selectedMode) || $selectedMode === 'all' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>All Offers</span>
                <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ empty($selectedMode) || $selectedMode === 'all' ? 'bg-white/20 text-white dark:bg-slate-900/20 dark:text-slate-900' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                    {{ $totalCoupons ?? 0 }}
                </span>
            </a>

            @foreach($modes as $mode)
                @php
                    $countForMode = $modeCounts[$mode->slug] ?? 0;
                    $isActive = $selectedMode === $mode->slug;
                @endphp
                <a href="{{ route('coupons.index', ['mode' => $mode->slug]) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $isActive ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    @if($mode->icon)<i class="{{ $mode->icon }}"></i>@endif
                    <span>{{ $mode->name }}</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                        {{ $countForMode }}
                    </span>
                </a>
            @endforeach
        </div>
    @endif

    @if($coupons->count() > 0)
        <!-- Coupons Voucher Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($coupons as $coupon)
                @php
                    $isPercentage = $coupon->type === \App\Models\Coupon::TYPE_PERCENTAGE;
                    $isFixed = $coupon->type === \App\Models\Coupon::TYPE_FIXED;
                    $isFreeDelivery = $coupon->type === \App\Models\Coupon::TYPE_FREE_DELIVERY;
                @endphp
                <div class="relative rounded-3xl bg-white dark:bg-slate-800 border-2 border-dashed border-slate-200 dark:border-slate-700 p-6 flex flex-col justify-between shadow-xs hover:border-indigo-400 dark:hover:border-indigo-500 transition group overflow-hidden">
                    
                    <!-- Decorative Cutout Circles (Voucher Ticket Notch) -->
                    <div class="absolute -left-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-slate-50 dark:bg-slate-900 border-r-2 border-slate-200 dark:border-slate-700"></div>
                    <div class="absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-slate-50 dark:bg-slate-900 border-l-2 border-slate-200 dark:border-slate-700"></div>

                    <!-- Header Badges -->
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full {{ $isPercentage ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300' : ($isFreeDelivery ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300') }}">
                                <i class="fa-solid fa-tag text-[10px]"></i>
                                @if($isPercentage)
                                    {{ (int)$coupon->value }}% OFF
                                @elseif($isFixed)
                                    FLAT ₹{{ (int)$coupon->value }} OFF
                                @elseif($isFreeDelivery)
                                    FREE DELIVERY
                                @endif
                            </span>

                            @if($coupon->mode)
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center gap-1">
                                    @if($coupon->mode->icon)<i class="{{ $coupon->mode->icon }}"></i>@endif
                                    <span>{{ $coupon->mode->name }}</span>
                                </span>
                            @else
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400">
                                    All Stores
                                </span>
                            @endif
                        </div>

                        <!-- Coupon Title & Description -->
                        <h3 class="font-bold text-base text-slate-900 dark:text-white mb-1.5">
                            {{ $coupon->name }}
                        </h3>

                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-4">
                            {{ $coupon->description ?? 'Save big on your next purchase!' }}
                        </p>

                        <!-- Terms & Limits Meta -->
                        <div class="space-y-1 mb-5 text-[11px] text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-700/40 p-3 rounded-xl">
                            @if($coupon->min_order_amount > 0)
                                <div class="flex items-center justify-between">
                                    <span>Min. Order Value:</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-200">₹{{ number_format($coupon->min_order_amount, 0) }}</span>
                                </div>
                            @endif
                            @if($coupon->max_discount_amount)
                                <div class="flex items-center justify-between">
                                    <span>Max. Savings:</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-200">₹{{ number_format($coupon->max_discount_amount, 0) }}</span>
                                </div>
                            @endif
                            @if($coupon->expires_at)
                                <div class="flex items-center justify-between">
                                    <span>Valid Until:</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $coupon->expires_at->format('d M, Y') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Code & Actions Bar -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700/80 flex items-center gap-2">
                        <!-- Code Box with Click to Copy -->
                        <button type="button" 
                                onclick="copyCouponCode('{{ $coupon->code }}')"
                                class="flex-1 py-2 px-3 rounded-xl border border-indigo-200 dark:border-indigo-800/80 bg-indigo-50/50 dark:bg-indigo-950/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-mono font-bold text-xs flex items-center justify-between transition cursor-pointer"
                                title="Click to copy code">
                            <span>{{ $coupon->code }}</span>
                            <i class="fa-regular fa-copy text-indigo-400"></i>
                        </button>

                        <!-- Direct 1-Click Apply to Cart Form -->
                        <form method="POST" action="{{ route('cart.coupon.apply') }}">
                            @csrf
                            <input type="hidden" name="coupon_code" value="{{ $coupon->code }}">
                            <input type="hidden" name="mode" value="{{ $coupon->mode?->slug ?? session('active_shopping_mode', 'shopy') }}">
                            <button type="submit" 
                                    class="py-2 px-3.5 rounded-xl font-bold text-xs text-white bg-slate-900 hover:bg-indigo-600 dark:bg-slate-700 dark:hover:bg-indigo-600 transition shadow-xs cursor-pointer">
                                Apply
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $coupons->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="py-16 text-center max-w-md mx-auto">
            <div class="w-20 h-20 mx-auto mb-4 rounded-3xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 dark:text-amber-400 flex items-center justify-center shadow-xs">
                <i class="fa-solid fa-tags text-3xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">No Offers Right Now</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5 max-w-sm mx-auto">
                Check back soon! We are always cooking up new discounts and promo codes for you.
            </p>
            <div class="mt-6">
                <a href="{{ route('home') }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span>Explore Products</span>
                </a>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function copyCouponCode(code) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(code).then(() => {
            if (typeof toastr !== 'undefined') {
                toastr.success('Coupon code "' + code + '" copied to clipboard!');
            }
        });
    } else {
        const temp = document.createElement('input');
        temp.value = code;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        if (typeof toastr !== 'undefined') {
            toastr.success('Coupon code "' + code + '" copied!');
        }
    }
}
</script>
@endpush
@endsection
