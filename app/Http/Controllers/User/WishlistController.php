<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Mode;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WishlistController extends Controller
{
    /**
     * Display the authenticated user's wishlist page with optional mode filtering.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::guard('web')->user();
        $selectedMode = $request->query('mode');

        $modes = Mode::where('status', true)->orderBy('id')->get();

        $query = $user->wishlistProducts()
            ->with(['category', 'mode'])
            ->latest('wishlists.created_at');

        if (!empty($selectedMode)) {
            $query->whereHas('mode', function ($q) use ($selectedMode) {
                $q->where('slug', $selectedMode);
            });
        }

        $wishlistProducts = $query->paginate(12)->withQueryString();

        // Calculate counts per mode for the customer's wishlist
        $modeCounts = [];
        $totalCount = $user->wishlistCount();
        foreach ($modes as $mode) {
            $modeCounts[$mode->slug] = $user->wishlistProducts()
                ->where('products.mode_id', $mode->id)
                ->count();
        }

        return view('user.pages.wishlist', compact(
            'wishlistProducts',
            'modes',
            'selectedMode',
            'modeCounts',
            'totalCount'
        ));
    }

    /**
     * AJAX endpoint to toggle a product in/out of the user's wishlist.
     */
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        if (!Auth::guard('web')->check()) {
            return response()->json([
                'success'         => false,
                'unauthenticated' => true,
                'message'         => 'Please sign in to save items to your wishlist.',
                'login_url'       => route('login'),
            ], 401);
        }

        $user = Auth::guard('web')->user();
        $product = Product::findOrFail($validated['product_id']);

        $result = Wishlist::toggle($user->id, $product->id);

        return response()->json([
            'success'     => true,
            'action'      => $result['action'],
            'in_wishlist' => $result['in_wishlist'],
            'count'       => $result['count'],
            'message'     => $result['in_wishlist']
                ? "Added {$product->name} to your wishlist!"
                : "Removed {$product->name} from your wishlist.",
        ]);
    }

    /**
     * Remove a product from the user's wishlist.
     */
    public function destroy(Request $request, int $productId): RedirectResponse|JsonResponse
    {
        $user = Auth::guard('web')->user();

        $deleted = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->delete();

        $count = $user->wishlistCount();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'count'   => $count,
                'message' => 'Item removed from your wishlist.',
            ]);
        }

        return back()->with('success', 'Item removed from your wishlist.');
    }

    /**
     * Clear all items from the user's wishlist.
     */
    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::guard('web')->user();
        $user->wishlists()->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'count'   => 0,
                'message' => 'Your wishlist has been cleared.',
            ]);
        }

        return redirect()->route('wishlist.index')->with('success', 'Your wishlist has been cleared.');
    }
}
