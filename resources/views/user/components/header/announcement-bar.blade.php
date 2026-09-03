<div class="announcement-bar">
    <div class="container">
        <div class="announcement-left">
            <span class="announcement-badge">Special Offer</span>
            <span>⚡ Free Express Delivery on orders over ₹499 | Easy 7-Day Returns</span>
        </div>
        <div class="announcement-right">
            <span><i class="fas fa-headset mr-1"></i> Support: +91 63796 44145</span>
            <a href="{{ Route::has('orders.history') ? route('orders.history') : url('/orders') }}">
                <i class="fas fa-truck-fast mr-1"></i> Track Order
            </a>
            <a href="{{ Route::has('faqs') ? route('faqs') : url('/faqs') }}">
                <i class="fas fa-circle-question mr-1"></i> Help
            </a>
        </div>
    </div>
</div>
