<div class="products-grid">
    @forelse($products as $product)
        @include('user.components.storefront.product-card', ['product' => $product])
    @empty
        <div class="col-span-full py-12 text-center text-slate-400">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-3 text-slate-400">
                <i class="fas fa-box-open text-2xl"></i>
            </div>
            <p class="font-semibold text-slate-600 dark:text-slate-300">No products available in this selection.</p>
            <p class="text-xs text-slate-400 mt-1">Please explore another shopping mode or category.</p>
        </div>
    @endforelse
</div>
