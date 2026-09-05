<div id="productQuickViewModal" class="shopy-modal" style="display: none;">
    <div class="shopy-modal-content">
        <button type="button" class="shopy-modal-close" id="closeQuickViewModal" aria-label="Close modal">&times;</button>
        
        <!-- Left: Product Image -->
        <div class="modal-left">
            <img id="modalProductImage" src="" alt="Product Preview" loading="lazy">
        </div>

        <!-- Right: Product Details -->
        <div class="modal-right">
            <div class="flex items-center gap-2 mb-2">
                <span id="modalProductCategory" class="text-xs font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 dark:bg-indigo-950/60 dark:text-indigo-400 px-2.5 py-0.5 rounded-full"></span>
                <span id="modalProductMode" class="text-xs font-semibold text-slate-500 bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-full"></span>
            </div>

            <h2 id="modalProductName" class="text-xl font-bold text-slate-900 dark:text-white leading-snug mb-3"></h2>

            <div class="rating mb-3">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-stroke"></i>
                <span class="rating-value text-xs text-slate-500 font-medium ml-1">4.8 Rating (Verified)</span>
            </div>

            <p id="modalProductDescription" class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-6"></p>

            <div class="flex items-baseline gap-3 mb-6">
                <span id="modalProductPrice" class="text-2xl font-black text-slate-900 dark:text-white"></span>
                <span id="modalProductCompare" class="text-sm text-slate-400 line-through"></span>
            </div>

            <div class="mt-auto flex items-center gap-3">
                <button type="button" 
                        id="modalAddToCartBtn"
                        class="flex-1 py-3 px-6 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition shadow-md shadow-indigo-600/30 flex items-center justify-center gap-2">
                    <i class="fas fa-bag-shopping"></i> Add to Cart
                </button>
                <button type="button" 
                        class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition"
                        title="Add to Wishlist"
                        onclick="if(typeof toastr !== 'undefined') toastr.info('Saved to Wishlist');">
                    <i class="far fa-heart"></i>
                </button>
            </div>
        </div>
    </div>
</div>
