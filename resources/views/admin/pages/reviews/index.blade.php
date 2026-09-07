@extends('admin.layouts.admin')

@section('title', 'Customer Reviews & Ratings')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Customer Reviews</h1>
            <p class="text-sm text-slate-400 mt-1">Moderate product ratings, verified buyer feedback, and customer photos</p>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Reviews</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                    <i class="fa-solid fa-comments text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-white">{{ number_format($metrics['total']) }}</span>
            </div>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Average Rating</span>
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i class="fa-solid fa-star text-sm"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-black text-white">{{ $metrics['average'] }}</span>
                <div class="flex text-amber-400 text-xs">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-{{ $i <= round($metrics['average']) ? 'solid' : 'regular' }} fa-star"></i>
                    @endfor
                </div>
            </div>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Verified Buyers</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-badge-check text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-white">{{ number_format($metrics['verified']) }}</span>
            </div>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Hidden / Filtered</span>
                <div class="w-9 h-9 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center">
                    <i class="fa-solid fa-eye-slash text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-white">{{ number_format($metrics['hidden']) }}</span>
            </div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
        <form action="{{ route('admin.reviews.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search product, customer, or comment..." 
                           class="w-full pl-9 pr-4 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Rating Filter -->
            <div>
                <select name="rating" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Star Ratings</option>
                    <option value="5" {{ request('rating') === '5' ? 'selected' : '' }}>5 Stars ★★★★★</option>
                    <option value="4" {{ request('rating') === '4' ? 'selected' : '' }}>4 Stars ★★★★☆</option>
                    <option value="3" {{ request('rating') === '3' ? 'selected' : '' }}>3 Stars ★★★☆☆</option>
                    <option value="2" {{ request('rating') === '2' ? 'selected' : '' }}>2 Stars ★★☆☆☆</option>
                    <option value="1" {{ request('rating') === '1' ? 'selected' : '' }}>1 Star ★☆☆☆☆</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <select name="status" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Visibility</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Approved / Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Hidden</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'rating', 'status', 'verified']))
                    <a href="{{ route('admin.reviews.index') }}" class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs rounded-xl transition text-center" title="Clear Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Reviews Table -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-800/60 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Product</th>
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4">Rating</th>
                        <th class="py-3.5 px-4">Feedback &amp; Photos</th>
                        <th class="py-3.5 px-4">Order Link</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($reviews as $review)
                        <tr class="hover:bg-slate-800/40 transition">
                            <!-- Product -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $review->product?->image_url ?? 'https://placehold.co/80x80' }}" 
                                         alt="{{ $review->product?->name }}" 
                                         class="w-10 h-10 object-cover rounded-lg border border-slate-700 shrink-0">
                                    <div class="min-w-0">
                                        <a href="{{ route('product.show', $review->product?->slug ?? '') }}" target="_blank" class="font-bold text-white hover:text-indigo-400 transition truncate block max-w-[200px]">
                                            {{ $review->product?->name ?? 'Deleted Product' }}
                                        </a>
                                        <span class="text-[10px] text-slate-500 font-mono">{{ $review->product?->sku ?? '' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Customer -->
                            <td class="py-3.5 px-4">
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-200 truncate">{{ $review->user?->name ?? 'Guest/Anonymous' }}</div>
                                    <div class="text-[11px] text-slate-500 truncate">{{ $review->user?->email }}</div>
                                    @if($review->is_verified_buyer)
                                        <span class="inline-flex items-center gap-1 mt-0.5 px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <i class="fa-solid fa-badge-check text-[8px]"></i>
                                            <span>Verified Buyer</span>
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Rating -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <div class="flex text-amber-400 text-xs">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fa-{{ $i <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>
                                        @endfor
                                    </div>
                                    <span class="font-bold text-white text-xs">{{ $review->rating }}.0</span>
                                </div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">{{ $review->created_at->format('M d, Y') }}</span>
                            </td>

                            <!-- Feedback & Photos -->
                            <td class="py-3.5 px-4 max-w-sm">
                                @if($review->title)
                                    <div class="font-bold text-slate-200 mb-1 text-xs">{{ $review->title }}</div>
                                @endif
                                <p class="text-slate-400 text-xs line-clamp-2 leading-relaxed">{{ $review->comment }}</p>
                                @if(is_array($review->images) && count($review->images) > 0)
                                    <div class="flex items-center gap-1.5 mt-2">
                                        @foreach($review->images as $img)
                                            <a href="{{ asset('storage/' . $img) }}" target="_blank" class="block">
                                                <img src="{{ asset('storage/' . $img) }}" alt="Review photo" class="w-8 h-8 rounded-lg object-cover border border-slate-700 hover:scale-110 transition">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </td>

                            <!-- Order Link -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($review->order)
                                    <a href="{{ route('admin.orders.show', $review->order_id) }}" class="font-mono text-indigo-400 hover:underline flex items-center gap-1">
                                        <i class="fa-solid fa-receipt text-[10px]"></i>
                                        <span>{{ $review->order->order_number }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-500 text-[11px]">—</span>
                                @endif
                            </td>

                            <!-- Status Toggle -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <form action="{{ route('admin.reviews.toggleStatus', $review) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold transition cursor-pointer {{ $review->status ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/15 text-rose-400 border border-rose-500/30' }}" title="Click to toggle visibility">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $review->status ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                        <span>{{ $review->status ? 'Approved' : 'Hidden' }}</span>
                                    </button>
                                </form>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this customer review?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-400 transition" title="Delete Review">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                                    <i class="fa-solid fa-star-half-stroke"></i>
                                </div>
                                <p class="text-sm font-semibold">No customer reviews found</p>
                                <p class="text-xs text-slate-600 mt-1">When customers receive orders and write feedback, their reviews will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
