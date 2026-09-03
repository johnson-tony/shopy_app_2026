<div class="mega-dropdown-grid">
    @foreach($navcategories ?? [] as $category)
        <div class="mega-column">
            <h4 class="mega-title">
                <a href="{{ Route::has('shop.category.show') ? route('shop.category.show', $category->slug) : url('/category/' . $category->slug) }}">
                    {{ $category->name }}
                </a>
            </h4>

            @if(!empty($category->children) && $category->children->count())
                <ul>
                    @foreach($category->children as $child)
                        <li>
                            <a href="{{ Route::has('shop.category.show') ? route('shop.category.show', [$category->slug, $child->slug]) : url('/category/' . $category->slug . '/' . $child->slug) }}">
                                {{ $child->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endforeach
</div>
