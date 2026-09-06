{{-- 2. Master Site Footer --}}
<footer class="site-footer">
    <div class="max-w-7xl mx-auto">
        {{-- 4-Column Footer Information & Links (Mobile Accordion-Enabled) --}}
        @include('user.components.footer.top-columns')

        {{-- Newsletter Subscribe Section --}}
        @include('user.components.footer.newsletter')

        {{-- Payment Methods & Security Trust Row --}}
        @include('user.components.footer.payment-methods')

        {{-- Copyright & Legal Links --}}
        @include('user.components.footer.bottom')
    </div>
</footer>

{{-- 3. Footer Interactivity Scripts (Accordions & Newsletter AJAX) --}}
@include('user.components.footer.scripts')
