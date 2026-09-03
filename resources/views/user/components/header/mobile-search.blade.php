<div class="mobile-search-wrapper">
    <form action="{{ Route::has('search') ? route('search') : url('/search') }}" method="GET">
        <div class="mobile-search">
            <i class="fas fa-search"></i>
            <input type="text" name="query" placeholder="Search products..." value="{{ request('query') }}" autocomplete="off" />
            <button type="submit">Search</button>
        </div>
    </form>
</div>
