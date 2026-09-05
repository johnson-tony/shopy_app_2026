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
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">Account</a>
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

    @if($orders->count() > 0)
        <!-- Orders List -->
        <div class="space-y-4">
            @foreach($orders as $order)
                @php $badge = $order->status_badge; @endphp
                <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-5 sm:p-6 border border-slate-200 dark:border-slate-700/80 shadow-xs space-y-4">
                    
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
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $order->payment_method_label }}
                        </span>

                        <div class="flex items-center gap-3">
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
        <!-- Empty Orders State -->
        <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-12 text-center border border-slate-200 dark:border-slate-700/80 shadow-xs space-y-4">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-700/60 text-slate-400 flex items-center justify-center mx-auto text-2xl">
                <i class="fa-solid fa-box-open"></i>
            </div>
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
        </div>
    @endif
</div>
@endsection
