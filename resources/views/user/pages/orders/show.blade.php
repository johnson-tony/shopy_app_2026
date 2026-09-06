@extends('user.layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">Home</a>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                <a href="{{ route('orders.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">My Orders</a>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                <span class="text-slate-900 dark:text-white font-semibold">#{{ $order->order_number }}</span>
            </nav>
            <div class="flex flex-wrap items-center gap-3 mt-1">
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Order #{{ $order->order_number }}
                </h1>
                @php $badge = $order->status_badge; @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $badge['bg'] }} {{ $badge['text'] }}">
                    <i class="{{ $badge['icon'] }} text-[11px]"></i>
                    <span>{{ $badge['label'] }}</span>
                </span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if($order->canBeCancelled())
                <button type="button" 
                        onclick="openCancelModal();"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-ban text-[11px]"></i>
                    <span>Cancel Order</span>
                </button>
            @endif
            <a href="{{ route('orders.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-[11px]"></i>
                <span>Back to Orders</span>
            </a>
        </div>
    </div>

    <!-- Status Tracking Stepper -->
    @if($order->status !== 'cancelled')
        @php
            $steps = [
                'confirmed'  => ['label' => 'Order Confirmed', 'icon' => 'fa-solid fa-check'],
                'processing' => ['label' => 'Processing',      'icon' => 'fa-solid fa-box'],
                'shipped'    => ['label' => 'Shipped',         'icon' => 'fa-solid fa-truck-fast'],
                'delivered'  => ['label' => 'Delivered',       'icon' => 'fa-solid fa-house-chimney-check'],
            ];
            $statusOrder = ['confirmed' => 1, 'processing' => 2, 'shipped' => 3, 'delivered' => 4];
            $currentStepIndex = $statusOrder[$order->status] ?? 1;
        @endphp
        <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-6">Delivery Progress</h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 relative">
                @foreach($steps as $key => $stepData)
                    @php 
                        $stepNum = $statusOrder[$key]; 
                        $isCompleted = $currentStepIndex >= $stepNum;
                        $isCurrent = $currentStepIndex === $stepNum;
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 font-bold text-sm {{ $isCompleted ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/20' : 'bg-slate-100 dark:bg-slate-700 text-slate-400' }}">
                            <i class="{{ $stepData['icon'] }} text-xs"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold block {{ $isCompleted ? 'text-slate-900 dark:text-white' : 'text-slate-400' }}">
                                {{ $stepData['label'] }}
                            </span>
                            <span class="text-[10px] text-slate-400">
                                {{ $isCompleted ? ($isCurrent ? 'Current Status' : 'Completed') : 'Pending' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- Order Cancelled Banner -->
        <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 rounded-3xl p-5 flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-xmark text-lg"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-rose-800 dark:text-rose-300">Order Cancelled</h4>
                <p class="text-xs text-rose-600 dark:text-rose-400">
                    This order was cancelled on {{ $order->cancelled_at?->format('M d, Y h:i A') ?? $order->updated_at->format('M d, Y') }}.
                    @if($order->cancellation_reason) Reason: {{ $order->cancellation_reason }} @endif
                </p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left 8 Cols: Ordered Items & Review Opportunities -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-xs space-y-4">
                <h3 class="text-sm font-black text-slate-900 dark:text-white tracking-tight pb-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <span>Ordered Items ({{ $order->totalQuantity() }})</span>
                    <span class="text-xs text-slate-400 font-normal">Shopping Channel: {{ $order->mode?->name ?? 'Standard Store' }}</span>
                </h3>

                <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @foreach($order->items as $item)
                        @php 
                            $hasReviewed = in_array($item->product_id, $reviewedProductIds);
                        @endphp
                        <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4 min-w-0">
                                <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="w-16 h-16 object-cover rounded-2xl border border-slate-200 dark:border-slate-700 shrink-0">
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
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        @if($item->color)
                                            <span class="bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded-md">Color: <strong>{{ $item->color }}</strong></span>
                                        @endif
                                        @if($item->size)
                                            <span class="bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded-md">Size: <strong>{{ $item->size }}</strong></span>
                                        @endif
                                        @if($item->restaurant_name)
                                            <span class="bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded-md text-amber-700 dark:text-amber-400">
                                                <i class="fa-solid fa-utensils text-[9px] mr-1"></i>{{ $item->restaurant_name }}
                                            </span>
                                        @endif
                                        @if($item->hasAddons())
                                            <span class="bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40 px-2 py-0.5 rounded-md">
                                                Extras: <strong>{{ $item->formattedAddons() }}</strong>
                                            </span>
                                        @endif
                                        <span>Qty: <strong>{{ $item->quantity }}</strong></span>
                                        <span>&bull;</span>
                                        <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($item->subtotal, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Review Action Button -->
                            @if($item->product_id)
                                <div class="shrink-0 flex items-center gap-2">
                                    @if($hasReviewed)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                            <i class="fa-solid fa-circle-check text-[11px]"></i>
                                            <span>Reviewed</span>
                                        </span>
                                        <button type="button" 
                                                onclick="openReviewModal({{ $item->product_id }}, '{{ addslashes($item->product_name) }}', {{ $order->id }});"
                                                class="px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition">
                                            Edit
                                        </button>
                                    @else
                                        <button type="button" 
                                                onclick="openReviewModal({{ $item->product_id }}, '{{ addslashes($item->product_name) }}', {{ $order->id }});"
                                                class="px-4 py-2 rounded-xl text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 hover:bg-amber-100 dark:hover:bg-amber-900/50 border border-amber-200 dark:border-amber-800/60 transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                                            <i class="fa-solid fa-star text-amber-500"></i>
                                            <span>Rate &amp; Review Product</span>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Delivery Address Card -->
            <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-xs space-y-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Shipping Details</h3>
                <div class="text-sm">
                    <p class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>{{ $order->userAddress?->full_name }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                            {{ $order->userAddress?->address_type ?? 'Home' }}
                        </span>
                    </p>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                        {{ $order->formatted_shipping_address }}
                    </p>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-phone text-[10px]"></i>
                        <span>Phone: {{ $order->userAddress?->phone }}</span>
                    </p>
                    @if($order->notes)
                        <p class="text-xs text-slate-500 dark:text-slate-400 italic mt-2 bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl">
                            "{{ $order->notes }}"
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right 4 Cols: Payment & Financial Summary -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white dark:bg-slate-800/90 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-xs space-y-4">
                <h3 class="text-sm font-black text-slate-900 dark:text-white tracking-tight pb-3 border-b border-slate-100 dark:border-slate-700">
                    Payment Breakdown
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($order->subtotal, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span>Delivery Fee</span>
                        <span class="font-bold {{ $order->delivery_fee == 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }}">
                            {{ $order->delivery_fee == 0 ? 'FREE' : '₹' . number_format($order->delivery_fee, 2) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span>GST Tax (5%)</span>
                        <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($order->tax_amount, 2) }}</span>
                    </div>

                    @if($order->discount_amount > 0)
                        <div class="flex items-center justify-between text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 p-2 rounded-lg">
                            <span>Discount @if($order->coupon_code) ({{ $order->coupon_code }}) @endif</span>
                            <span class="font-bold">-₹{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex items-baseline justify-between text-sm">
                        <span class="font-black text-slate-900 dark:text-white">Grand Total</span>
                        <span class="text-xl font-black text-indigo-600 dark:text-indigo-400">
                            ₹{{ number_format($order->grand_total, 2) }}
                        </span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-700 space-y-2 text-xs">
                    <div>
                        <span class="text-slate-400 block">Payment Method</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $order->payment_method_label }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Payment Status</span>
                        <span class="font-bold uppercase tracking-wider text-[11px] {{ $order->payment_status === 'paid' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $order->payment_status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rate & Review Modal -->
<div id="reviewModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-6">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Rate &amp; Review Product</h3>
                <p id="modalProductName" class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-xs"></p>
            </div>
            <button type="button" onclick="closeReviewModal();" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>

        <form action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data" id="reviewForm" class="space-y-4">
            @csrf
            <input type="hidden" name="product_id" id="modalProductId" value="">
            <input type="hidden" name="order_id" id="modalOrderId" value="">
            <input type="hidden" name="rating" id="modalRatingValue" value="5">

            <!-- Star Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Overall Rating *</label>
                <div class="flex items-center gap-2" id="starContainer">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" 
                                onclick="setModalRating({{ $i }});"
                                class="text-2xl text-amber-400 hover:scale-125 transition modal-star-btn cursor-pointer" 
                                data-star="{{ $i }}">
                            <i class="fa-solid fa-star"></i>
                        </button>
                    @endfor
                    <span id="ratingDescriptor" class="text-xs font-bold text-amber-600 dark:text-amber-400 ml-2">Excellent (5/5)</span>
                </div>
            </div>

            <!-- Review Title -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Review Headline</label>
                <input type="text" name="title" placeholder="e.g. Outstanding quality, highly satisfied!" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <!-- Detailed Feedback / Comment -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Your Detailed Experience *</label>
                <textarea name="comment" rows="4" required placeholder="What did you like or dislike about this product? How did it fit/perform?" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>

            <!-- Customer Photo Uploads -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Upload Product Photos (Max 5 images)</label>
                <input type="file" name="images[]" multiple accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                <p class="text-[10px] text-slate-400 mt-1">Add real photos taken by you to help other shoppers make decisions.</p>
            </div>

            <!-- Verified Buyer Pill Notice -->
            <div class="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 flex items-center gap-2 text-xs text-emerald-800 dark:text-emerald-300">
                <i class="fa-solid fa-badge-check text-emerald-600 text-sm"></i>
                <span>Your review will receive the <strong>✔ Certified Buyer</strong> badge because this order is verified.</span>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="button" onclick="closeReviewModal();" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-500/25 cursor-pointer">
                    Submit Review
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Cancel Order Modal -->
@if($order->canBeCancelled())
    <div id="cancelModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4">
            <h3 class="text-base font-black text-slate-900 dark:text-white">Cancel Order #{{ $order->order_number }}?</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Are you sure you want to cancel this order? Stock will be restored and any applied discounts reverted.
            </p>

            <form action="{{ route('orders.cancel', $order->order_number) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Reason for Cancellation (Optional)</label>
                    <input type="text" name="cancellation_reason" placeholder="e.g. Placed by mistake, found better price" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="closeCancelModal();" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                        Nevermind
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm shadow-rose-500/25 cursor-pointer">
                        Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

@push('scripts')
<script>
    function openReviewModal(productId, productName, orderId) {
        document.getElementById('modalProductId').value = productId;
        document.getElementById('modalOrderId').value = orderId;
        document.getElementById('modalProductName').textContent = productName;
        document.getElementById('reviewModal').classList.remove('hidden');
        setModalRating(5);
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
    }

    function setModalRating(rating) {
        document.getElementById('modalRatingValue').value = rating;
        const labels = {
            1: 'Terrible (1/5)',
            2: 'Bad (2/5)',
            3: 'Average (3/5)',
            4: 'Good (4/5)',
            5: 'Excellent (5/5)'
        };
        document.getElementById('ratingDescriptor').textContent = labels[rating] || (rating + '/5');

        document.querySelectorAll('.modal-star-btn').forEach(btn => {
            const starNum = parseInt(btn.getAttribute('data-star'));
            const icon = btn.querySelector('i');
            if (starNum <= rating) {
                btn.classList.add('text-amber-400');
                btn.classList.remove('text-slate-300', 'dark:text-slate-600');
                if (icon) {
                    icon.classList.remove('fa-regular');
                    icon.classList.add('fa-solid');
                }
            } else {
                btn.classList.remove('text-amber-400');
                btn.classList.add('text-slate-300', 'dark:text-slate-600');
                if (icon) {
                    icon.classList.remove('fa-solid');
                    icon.classList.add('fa-regular');
                }
            }
        });
    }

    function openCancelModal() {
        document.getElementById('cancelModal')?.classList.remove('hidden');
    }

    function closeCancelModal() {
        document.getElementById('cancelModal')?.classList.add('hidden');
    }
</script>
@endpush
@endsection
