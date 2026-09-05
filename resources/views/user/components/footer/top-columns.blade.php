<div class="footer-top">
    <!-- Column 1: Logo & Social -->
    <div class="footer-column">
        <div class="footer-logo">
            <img 
                src="{{ asset($logoMedia?->file_path ?? 'images/logo/logo.png') }}" 
                alt="{{ $homepageTitle ?? config('app.name', 'Shopy') }}"
                onerror="this.onerror=null;this.src='{{ asset('images/default-logo.svg') }}';"
            >
        </div>
        <p class="footer-tagline">
            Your destination for curated premium lifestyle and daily essentials. Built with modern quality and care.
        </p>
        <div class="footer-social">
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
    </div>

    <!-- Column 2: Information (Mobile Collapsible) -->
    <div class="footer-column" data-accordion>
        <h4>
            <span>Information</span>
            <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </h4>
        <ul>
            <li><a href="{{ Route::has('about') ? route('about') : url('/about') }}">About Us</a></li>
            <li><a href="{{ Route::has('contact') ? route('contact') : url('/contact') }}">Contact</a></li>
            <li><a href="{{ Route::has('coupons.index') ? route('coupons.index') : url('/coupons') }}">Coupons</a></li>
            <li><a href="{{ Route::has('orders.history') ? route('orders.history') : url('/orders') }}">Orders</a></li>
        </ul>
    </div>

    <!-- Column 3: Customer Services (Mobile Collapsible) -->
    <div class="footer-column" data-accordion>
        <h4>
            <span>Customer Services</span>
            <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </h4>
        <ul>
            <li><a href="{{ Route::has('return_exchange_policy') ? route('return_exchange_policy') : url('/return-policy') }}">Return Policy</a></li>
            <li><a href="{{ Route::has('faqs') ? route('faqs') : url('/faqs') }}">FAQ</a></li>
            <li><a href="{{ Route::has('privacy_policy') ? route('privacy_policy') : url('/privacy-policy') }}">Privacy Policy</a></li>
            <li><a href="{{ Route::has('terms_of_service') ? route('terms_of_service') : url('/terms-of-service') }}">Terms of Service</a></li>
        </ul>
    </div>

    <!-- Column 4: Contact / Questions (Mobile Collapsible) -->
    <div class="footer-column" data-accordion>
        <h4>
            <span>Have Questions?</span>
            <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </h4>
        <ul class="footer-contact">
            <li>
                <i class="fa fa-map-marker-alt"></i>
                <span>Karur, Tamil Nadu, India – 639001</span>
            </li>
            <li>
                <i class="fa fa-envelope"></i>
                <span>support@formann.in</span>
            </li>
            <li>
                <i class="fa fa-phone"></i>
                <span>+91 63796 44145</span>
            </li>
        </ul>
    </div>
</div>
