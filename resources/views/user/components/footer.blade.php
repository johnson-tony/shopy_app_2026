<footer class="site-footer">
    {{-- 4-Column Footer Information & Links --}}
    @include('user.components.footer.top-columns')

    {{-- Newsletter Subscribe Section --}}
    @include('user.components.footer.newsletter')

    {{-- Copyright & Bottom Info --}}
    @include('user.components.footer.bottom')
</footer>

{{-- Footer Scripts (Newsletter Submission) --}}
@include('user.components.footer.scripts')
