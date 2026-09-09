<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class ProductDetailController extends Controller
{
    /**
     * Display a rich, dedicated product detail page by slug.
     */
    public function show(string $slug): View
    {
        $product = Product::with(['category.parent', 'mode', 'approvedReviews.user'])
            ->where('slug', $slug)
            ->where('status', true)
            ->firstOrFail();

        // Sync active shopping mode in session to match the product's channel
        if ($product->mode) {
            session(['active_shopping_mode' => $product->mode->slug]);
        }

        // Related products in the same category or mode
        $relatedProducts = Product::with(['category', 'mode'])
            ->where('status', true)
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                $q->where('category_id', $product->category_id)
                  ->orWhere('mode_id', $product->mode_id);
            })
            ->limit(4)
            ->get();

        // Free delivery threshold for this product's shopping channel
        $freeDeliveryThreshold = match ($product->mode?->slug) {
            'minutes' => 199.00,
            'food'    => 350.00,
            default   => 499.00, // Shopy
        };

        // User purchase status & existing review check
        $currentUser = auth()->guard('web')->user();
        $hasPurchased = $currentUser ? $currentUser->hasPurchasedProduct($product) : false;
        $hasDelivered = $currentUser ? $currentUser->hasDeliveredProduct($product) : false;
        $userOrderId = $currentUser ? $currentUser->getDeliveredOrderIdForProduct($product) : null;
        $userReview = $currentUser ? $product->reviews()->where('user_id', $currentUser->id)->first() : null;

        return view('user.pages.product-detail', compact(
            'product',
            'relatedProducts',
            'freeDeliveryThreshold',
            'hasPurchased',
            'hasDelivered',
            'userOrderId',
            'userReview'
        ));
    }
}
