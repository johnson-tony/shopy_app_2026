<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * Display a listing of all customer product reviews with search and filters.
     */
    public function index(Request $request): View
    {
        $query = ProductReview::with(['user', 'product.mode', 'order'])->latest();

        // Search by customer name, email or product name
        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('comment', 'like', "%{$term}%")
                  ->orWhereHas('user', function ($uq) use ($term) {
                      $uq->where('name', 'like', "%{$term}%")
                         ->orWhere('email', 'like', "%{$term}%");
                  })
                  ->orWhereHas('product', function ($pq) use ($term) {
                      $pq->where('name', 'like', "%{$term}%")
                         ->orWhere('slug', 'like', "%{$term}%");
                  });
            });
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->input('rating'));
        }

        // Filter by status (active vs hidden)
        if ($request->filled('status')) {
            $query->where('status', $request->input('status') === '1');
        }

        // Filter by verified buyer
        if ($request->filled('verified')) {
            $query->where('is_verified_buyer', $request->input('verified') === '1');
        }

        $reviews = $query->paginate(15)->withQueryString();

        // Metrics for summary cards
        $metrics = [
            'total'     => ProductReview::count(),
            'average'   => round((float) ProductReview::avg('rating') ?: 0, 1),
            'verified'  => ProductReview::where('is_verified_buyer', true)->count(),
            'hidden'    => ProductReview::where('status', false)->count(),
        ];

        return view('admin.pages.reviews.index', compact('reviews', 'metrics'));
    }

    /**
     * Toggle the visibility status of the specified review.
     */
    public function toggleStatus(ProductReview $review, Request $request): JsonResponse|RedirectResponse
    {
        $review->status = !$review->status;
        $review->save();

        $statusText = $review->status ? 'approved & published' : 'hidden';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status'  => $review->status,
                'message' => "Review has been {$statusText}.",
            ]);
        }

        return back()->with('success', "Review has been {$statusText}.");
    }

    /**
     * Delete the specified product review.
     */
    public function destroy(ProductReview $review, Request $request): JsonResponse|RedirectResponse
    {
        $review->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Review has been permanently removed.',
            ]);
        }

        return back()->with('success', 'Review has been permanently removed.');
    }
}
