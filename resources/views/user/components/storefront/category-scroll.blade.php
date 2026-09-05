<section class="featured-categories-scroll">
    <div class="section-header">
        <div class="header">
            <h2>Shop by Category</h2>
            <p>Explore top departments across our shopping channels</p>
        </div>
        <a href="#featured-products" class="view-all">
            Browse Products <i class="fas fa-arrow-right text-xs ml-1"></i>
        </a>
    </div>

    <div class="scroll-wrapper">
        <button type="button" class="scroll-btn left-btn" id="categoryScrollLeft" aria-label="Scroll left">
            <i class="fas fa-chevron-left"></i>
        </button>

        <div class="categories-scroll" id="categoriesScrollContainer">
            @forelse($topCategories as $category)
                @php
                    $isGrocery = str_contains(strtolower($category->name), 'grocery') || ($category->mode?->slug === 'minutes');
                    $isFood = str_contains(strtolower($category->name), 'food') || ($category->mode?->slug === 'food');
                    $bgClass = $isGrocery ? 'grocery-bg' : ($isFood ? 'food-bg' : '');
                @endphp
                <a href="{{ route('home', array_filter(['mode' => $currentMode?->slug, 'category' => $category->slug])) }}" 
                   class="category-card-link"
                   title="{{ $category->name }}">
                    <div class="category-card">
                        <div class="category-card-inner">
                            <div class="category-icon-bg {{ $bgClass }}">
                                @if($category->image_url)
                                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" loading="lazy"
                                         onerror="this.onerror=null;this.src='https://placehold.co/120x120/e2e8f0/475569?text={{ urlencode(substr($category->name, 0, 3)) }}';">
                                @else
                                    <img src="https://placehold.co/120x120/e2e8f0/475569?text={{ urlencode(substr($category->name, 0, 3)) }}" 
                                         alt="{{ $category->name }}" loading="lazy">
                                @endif
                            </div>
                            <h3>{{ $category->name }}</h3>
                        </div>
                    </div>
                </a>
            @empty
                <div class="py-8 text-center text-slate-400 text-sm w-full">
                    No active categories found in this shopping mode.
                </div>
            @endforelse
        </div>

        <button type="button" class="scroll-btn right-btn" id="categoryScrollRight" aria-label="Scroll right">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</section>
