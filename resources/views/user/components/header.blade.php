{{-- 1. Top Announcement / Notch Safe Area Bar --}}
@include('user.components.header.announcement-bar')

{{-- 2. Sticky Navbar --}}
<header class="navbar" id="siteHeader">
    <div class="nav-top-wrapper">
        @include('user.components.header.top-bar')
    </div>

    {{-- Mobile Search Bar (directly below top bar on small screens) --}}
    @include('user.components.header.mobile-search')

    {{-- Mobile Category Chips (App-like horizontal scrolling bar) --}}
    @include('user.components.header.category-chips')

    {{-- Desktop Second Row Navigation with Mega Menu --}}
    @include('user.components.header.navbar')
</header>

{{-- 3. Mobile Sidebar Drawer & Backdrop Overlay --}}
@include('user.components.header.mobile-menu')

{{-- 4. Mobile App Bottom Navigation Bar (Thumb-Friendly Experience) --}}
@include('user.components.header.bottom-nav')

{{-- 5. Subtle Header Divider --}}
<div class="header-divider"></div>

{{-- 6. Header Interactivity Scripts --}}
@include('user.components.header.scripts')
