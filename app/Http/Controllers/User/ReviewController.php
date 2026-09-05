<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    /**
     * Store or update a customer review with star rating, comments, and multiple photo uploads.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Please sign in to write a review.'], 401);
            }
            return redirect()->route('login')->with('info', 'Please sign in to write a review.');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'order_id'   => 'nullable|exists:orders,id',
            'rating'     => 'required|integer|min:1|max:5',
            'title'      => 'nullable|string|max:150',
            'comment'    => 'required|string|min:4|max:2000',
            'images'     => 'nullable|array|max:5',
            'images.*'   => 'image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $productId = (int) $request->input('product_id');
        $orderId = $request->input('order_id') ? (int) $request->input('order_id') : null;

        // Check verification: has this user purchased this product in any order?
        $verifiedOrder = OrderItem::where('product_id', $productId)
            ->whereHas('order', function ($q) use ($user, $orderId) {
                $q->where('user_id', $user->id)
                  ->where('status', '!=', Order::STATUS_CANCELLED);
                if ($orderId) {
                    $q->where('id', $orderId);
                }
            })
            ->first();

        $isVerifiedBuyer = !is_null($verifiedOrder);
        $finalOrderId = $verifiedOrder ? $verifiedOrder->order_id : null;

        // Handle uploaded images
        $uploadedPaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image->isValid()) {
                    $path = $image->store("reviews/{$productId}", 'public');
                    $uploadedPaths[] = $path;
                }
            }
        }

        // Find existing review by this user for this product
        $existing = ProductReview::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        $existingImages = ($existing && is_array($existing->images)) ? $existing->images : [];
        $combinedImages = array_slice(array_merge($existingImages, $uploadedPaths), 0, 5);

        $review = ProductReview::updateOrCreate(
            [
                'user_id'    => $user->id,
                'product_id' => $productId,
            ],
            [
                'order_id'          => $finalOrderId,
                'rating'            => (int) $request->input('rating'),
                'title'             => $request->input('title'),
                'comment'           => $request->input('comment'),
                'images'            => !empty($combinedImages) ? $combinedImages : null,
                'is_verified_buyer' => $isVerifiedBuyer,
                'status'            => true,
            ]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your review has been published.',
                'review'  => [
                    'id'                => $review->id,
                    'rating'            => $review->rating,
                    'title'             => $review->title,
                    'comment'           => $review->comment,
                    'is_verified_buyer' => $review->is_verified_buyer,
                    'images'            => $review->imageUrls(),
                    'user_name'         => $user->name,
                    'created_at'        => $review->created_at->format('M d, Y'),
                ],
            ]);
        }

        return back()->with('success', 'Thank you! Your review has been published.');
    }
}
