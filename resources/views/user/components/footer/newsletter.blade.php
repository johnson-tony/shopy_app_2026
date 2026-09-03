<div class="footer-newsletter">
    <h4>Subscribe to our Newsletter</h4>
    <p>Get the latest updates, exclusive deals, and new arrivals directly to your inbox.</p>
    <form id="newsletterForm" class="newsletter-form" action="{{ Route::has('newsletter.subscribe') ? route('newsletter.subscribe') : url('/newsletter/subscribe') }}" method="POST">
        @csrf
        <input type="email" name="email" placeholder="Enter email address" required autocomplete="email" />
        <button type="submit">Subscribe</button>
    </form>
</div>
