<div class="mobile-category-chips" aria-label="Quick Category Access">
    <a href="{{ Route::has('shop.index') ? route('shop.index') : url('/shop') }}" class="category-chip {{ request()->routeIs('shop.index') ? 'active' : '' }}">
        <i class="fas fa-border-all text-xs"></i> All
    </a>
    @foreach($navcategories ?? [] as $cat)
        <a href="{{ Route::has('shop.category.show') ? route('shop.category.show', $cat->slug) : url('/category/' . $cat->slug) }}"
           class="category-chip {{ request()->is('category/' . $cat->slug . '*') ? 'active' : '' }}">
            {{ $cat->name }}
        </a>
    @endforeach
</div>
