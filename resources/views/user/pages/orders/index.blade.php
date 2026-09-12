@extends('user.layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">Home</a>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                <a href="{{ route('profile') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">Account</a>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                <span class="text-slate-900 dark:text-white font-semibold">My Orders</span>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1 flex items-center gap-2.5">
                <i class="fa-solid fa-box text-indigo-600"></i>
                <span>My Orders</span>
            </h1>
        </div>

        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
            <i class="fa-solid fa-bag-shopping"></i>
            <span>Browse Products</span>
        </a>
    </div>

    <!-- Simple Status Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        <!-- All Orders Tab -->
        <a href="{{ route('orders.index', array_filter(['mode' => $modeSlug !== 'all' ? $modeSlug : null, 'search' => $search ?: null])) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition shrink-0 cursor-pointer {{ $status === 'all' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-md' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
            <span>All Orders</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $status === 'all' ? 'bg-white/20 text-white dark:bg-slate-900/20 dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">
                {{ $counts['all'] }}
            </span>
        </a>

        <!-- Delivered Tab -->
        <a href="{{ route('orders.index', array_filter(['status' => 'delivered', 'mode' => $modeSlug !== 'all' ? $modeSlug : null, 'search' => $search ?: null])) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition shrink-0 cursor-pointer {{ $status === 'delivered' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
            <i class="fa-solid fa-circle-check {{ $status === 'delivered' ? 'text-white' : 'text-emerald-500' }}"></i>
            <span>Delivered</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $status === 'delivered' ? 'bg-white/20 text-white' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400' }}">
                {{ $counts['delivered'] }}
            </span>
        </a>

        <!-- In Progress Tab -->
        <a href="{{ route('orders.index', array_filter(['status' => 'in_progress', 'mode' => $modeSlug !== 'all' ? $modeSlug : null, 'search' => $search ?: null])) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition shrink-0 cursor-pointer {{ $status === 'in_progress' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
            <i class="fa-solid fa-motorcycle {{ $status === 'in_progress' ? 'text-white' : 'text-indigo-500' }}"></i>
            <span>In Progress / On the Way</span>
            @if($counts['in_progress'] > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $status === 'in_progress' ? 'bg-white/20 text-white' : 'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 animate-pulse' }}">
                    {{ $counts['in_progress'] }}
                </span>
            @else
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-slate-100 dark:bg-slate-700 text-slate-400">
                    0
                </span>
            @endif
        </a>

        <!-- Returns Tab -->
        <a href="{{ route('orders.index', array_filter(['status' => 'returns', 'mode' => $modeSlug !== 'all' ? $modeSlug : null, 'search' => $search ?: null])) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition shrink-0 cursor-pointer {{ $status === 'returns' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
            <i class="fa-solid fa-rotate-left {{ $status === 'returns' ? 'text-white' : 'text-amber-500' }}"></i>
            <span>Returns</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $status === 'returns' ? 'bg-white/20 text-white' : 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400' }}">
                {{ $counts['returns'] }}
            </span>
        </a>

        <!-- Cancelled Tab -->
        <a href="{{ route('orders.index', array_filter(['status' => 'cancelled', 'mode' => $modeSlug !== 'all' ? $modeSlug : null, 'search' => $search ?: null])) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition shrink-0 cursor-pointer {{ $status === 'cancelled' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
            <i class="fa-solid fa-ban {{ $status === 'cancelled' ? 'text-white' : 'text-rose-500' }}"></i>
            <span>Cancelled</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $status === 'cancelled' ? 'bg-white/20 text-white' : 'bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400' }}">
                {{ $counts['cancelled'] }}
            </span>
        </a>
    </div>

    <!-- Search & Channel Bar -->
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-3 border border-slate-200 dark:border-slate-700/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <!-- Shopping Channel Filter Chips -->
        <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-2">Channel:</span>
            <a href="{{ route('orders.index', array_filter(['status' => $status !== 'all' ? $status : null, 'search' => $search ?: null])) }}"
               class="px-2.5 py-1 rounded-xl text-xs font-semibold shrink-0 transition {{ $modeSlug === 'all' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 font-bold' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}">
                All
            </a>
            @foreach($modes as $mode)
                <a href="{{ route('orders.index', array_filter(['status' => $status !== 'all' ? $status : null, 'mode' => $mode->slug, 'search' => $search ?: null])) }}"
                   class="px-2.5 py-1 rounded-xl text-xs font-semibold shrink-0 transition {{ $modeSlug === $mode->slug ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 font-bold' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}">
                    {{ $mode->name }}
                </a>
            @endforeach
        </div>

        <!-- Search Input -->
        <form method="GET" action="{{ route('orders.index') }}" class="relative w-full sm:w-72">
            @if($status !== 'all') <input type="hidden" name="status" value="{{ $status }}"> @endif
            @if($modeSlug !== 'all') <input type="hidden" name="mode" value="{{ $modeSlug }}"> @endif
            <input type="text"
                   name="search"
                   value="{{ $search }}"
                   placeholder="Search by Order # or Product..."
                   class="w-full pl-9 pr-8 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            @if($search)
                <a href="{{ route('orders.index', array_filter(['status' => $status !== 'all' ? $status : null, 'mode' => $modeSlug !== 'all' ? $modeSlug : null])) }}"
                   class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="Clear Search">
                    <i class="fa-solid fa-times text-xs"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- Active Filter Indicator Banner (when filtered) -->
    @if($status !== 'all' || $modeSlug !== 'all' || !empty($search))
        <div class="flex items-center justify-between text-xs px-4 py-2.5 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 text-indigo-900 dark:text-indigo-200">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-filter text-indigo-600 dark:text-indigo-400 text-xs"></i>
                <span>
                    Filtered by:
                    @if($status !== 'all')
                        <strong>{{ ucfirst(str_replace('_', ' ', $status)) }}</strong>
                    @endif
                    @if($modeSlug !== 'all')
                        &bull; Channel: <strong>{{ ucfirst($modeSlug) }}</strong>
                    @endif
                    @if(!empty($search))
                        &bull; Search: "<strong>{{ $search }}</strong>"
                    @endif
                </span>
            </div>
            <a href="{{ route('orders.index') }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                <span>Reset All Filters</span>
                <i class="fa-solid fa-xmark text-[10px]"></i>
            </a>
        </div>
    @endif

    @if($orders->count() > 0)
        <!-- Orders List -->
        <div class="space-y-4">
            @foreach($orders as $order)
                @php $badge = $order->status_badge; @endphp
                <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-5 sm:p-6 border border-slate-200 dark:border-slate-700/80 shadow-xs space-y-4 hover:border-slate-300 dark:hover:border-slate-600 transition">
                    
                    <!-- Order Card Header -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex flex-wrap items-center gap-3 sm:gap-6 text-xs text-slate-500 dark:text-slate-400">
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-400">Order Placed</span>
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $order->created_at->format('M d, Y') }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-400">Total</span>
                                <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($order->grand_total, 2) }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-400">Order #</span>
                                <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $order->order_number }}</span>
                            </div>
                            @if($order->mode)
                                <div>
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Channel</span>
                                    <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $order->mode->name }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $badge['bg'] }} {{ $badge['text'] }}">
                                <i class="{{ $badge['icon'] }} text-[11px]"></i>
                                <span>{{ $badge['label'] }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Order Items Preview -->
                    <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @foreach($order->items as $item)
                            <div class="py-3 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="w-14 h-14 object-cover rounded-xl border border-slate-200 dark:border-slate-700 shrink-0">
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
                                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                            @if($item->color) <span>Color: <strong>{{ $item->color }}</strong></span> @endif
                                            @if($item->size) <span>Size: <strong>{{ $item->size }}</strong></span> @endif
                                            <span>Qty: <strong>{{ $item->quantity }}</strong></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="text-sm font-bold text-slate-900 dark:text-white block">
                                        ₹{{ number_format($item->subtotal, 2) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Order Card Footer Actions -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                            <span>{{ $order->payment_method_label }}</span>
                            @if($order->isDelivered())
                                <span>&bull;</span>
                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check text-[11px]"></i>
                                    Delivered on {{ $order->delivered_at?->format('M d, Y') ?? $order->updated_at->format('M d, Y') }}
                                </span>
                            @elseif(in_array($order->status, [\App\Models\Order::STATUS_OUT_FOR_DELIVERY, \App\Models\Order::STATUS_PICKED_UP]))
                                <span>&bull;</span>
                                <span class="text-indigo-600 dark:text-indigo-400 font-semibold flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-ping"></span>
                                    On the way
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if(in_array($order->status, [\App\Models\Order::STATUS_OUT_FOR_DELIVERY, \App\Models\Order::STATUS_PICKED_UP]))
                                <a href="{{ route('orders.show', $order->order_number) }}"
                                   class="px-3 py-1.5 rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 transition flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-dot text-emerald-500"></i>
                                    <span>Track Live</span>
                                </a>
                            @endif
                            <a href="{{ route('orders.show', $order->order_number) }}" 
                               class="px-4 py-2 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition flex items-center gap-1.5">
                                <i class="fa-solid fa-eye text-[11px]"></i>
                                <span>View Details &amp; Track</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pt-4">
            {{ $orders->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-12 text-center border border-slate-200 dark:border-slate-700/80 shadow-xs space-y-4">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-700/60 text-slate-400 flex items-center justify-center mx-auto text-2xl">
                <i class="fa-solid fa-box-open"></i>
            </div>
            @if($status !== 'all' || $modeSlug !== 'all' || !empty($search))
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">No Matching Orders Found</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                    We couldn't find any orders matching your selected filters. Try choosing a different status or clear your search.
                </p>
                <div class="pt-2">
                    <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 shadow-md shadow-indigo-500/20 transition">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Show All Orders</span>
                    </a>
                </div>
            @else
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">No Orders Placed Yet</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                    Explore our catalog and find the products you love. Your confirmed orders will appear here.
                </p>
                <div class="pt-2">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 shadow-md shadow-indigo-500/20 transition">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span>Start Shopping</span>
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
