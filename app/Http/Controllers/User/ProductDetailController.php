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
        $product = Product::with(['category.parent', 'mode'])
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

        return view('user.pages.product-detail', compact('product', 'relatedProducts', 'freeDeliveryThreshold'));
    }
}
