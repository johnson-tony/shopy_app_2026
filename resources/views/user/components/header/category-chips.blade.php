<div class="mobile-category-chips" aria-label="Quick Category Access">
    <a href="{{ route('home', ['mode' => 'shopy']) }}" class="category-chip {{ request()->query('mode') === 'shopy' ? 'active' : '' }}">
        <i class="fas fa-border-all text-xs"></i> All
    </a>
    @foreach($navcategories ?? [] as $cat)
        <a href="{{ Route::has('shop.category.show') ? route('shop.category.show', $cat->slug) : url('/category/' . $cat->slug) }}"
           class="category-chip {{ request()->is('category/' . $cat->slug . '*') ? 'active' : '' }}">
            {{ $cat->name }}
        </a>
    @endforeach
</div>
