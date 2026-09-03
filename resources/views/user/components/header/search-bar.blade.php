<div class="search-bar">
    <form action="{{ Route::has('search') ? route('search') : url('/search') }}" method="GET" style="display:flex; width:100%;">
        <div class="search-input-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" name="query" id="search-input" placeholder="" value="{{ request('query') }}" autocomplete="off" />
            <span class="animated-placeholder" id="animated-placeholder"></span>
        </div>
        <button type="submit">Search</button>
    </form>
</div>
