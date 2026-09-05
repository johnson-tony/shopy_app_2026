<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Mode;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Display the shopping cart page with complete calculations.
     */
    public function index(Request $request): View
    {
        $user = Auth::guard('web')->user();
        $sessionId = $request->session()->getId();

        // Selected shopping mode
        $selectedMode = $request->query('mode');
        if ($selectedMode === null) {
            $selectedMode = session('active_shopping_mode', 'shopy');
        } elseif ($selectedMode !== 'all') {
            session(['active_shopping_mode' => $selectedMode]);
        }

        // Active shopping modes
        $modes = Mode::where('status', true)->orderBy('id')->get();

        // Find or create cart for current active mode
        $cart = Cart::getOrCreate($user, $sessionId, $selectedMode);
        $cart->load(['items.product.category', 'mode']);

        // Gather all other carts belonging to this user/session to display mode switcher tabs
        $allCustomerCarts = Cart::where(function ($q) use ($user, $sessionId) {
            if ($user) {
                $q->where('user_id', $user->id);
            } else {
                $q->where('session_id', $sessionId)->whereNull('user_id');
            }
        })
        ->with(['mode', 'items'])
        ->get();

        // Calculate count per mode
        $modeItemCounts = [];
        $totalItemsAllModes = 0;
        foreach ($allCustomerCarts as $c) {
            $qty = $c->totalQuantity();
            if ($c->mode) {
                $modeItemCounts[$c->mode->slug] = $qty;
            }
            $totalItemsAllModes += $qty;
        }

        // Pre-computed calculations for the active cart
        $subtotal = $cart->subtotal();
        $deliveryFee = $cart->deliveryFee();
        $taxAmount = $cart->taxAmount();
        $discountAmount = $cart->discount();
        $grandTotal = $cart->grandTotal();
        $freeDeliveryThreshold = $cart->freeDeliveryThreshold();
        $amountNeededForFreeDelivery = $cart->amountNeededForFreeDelivery();

        return view('user.pages.cart', compact(
            'cart',
            'modes',
            'selectedMode',
            'allCustomerCarts',
            'modeItemCounts',
            'totalItemsAllModes',
            'subtotal',
            'deliveryFee',
            'taxAmount',
            'discountAmount',
            'grandTotal',
            'freeDeliveryThreshold',
            'amountNeededForFreeDelivery'
        ));
    }

    /**
     * AJAX endpoint to add a product to the cart.
     */
    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity'   => ['nullable', 'integer', 'min:1'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $product = Product::with('mode')->findOrFail($validated['product_id']);

        if ($product->stock <= 0) {
            return response()->json([
                'success' => false,
                'message' => "Sorry, {$product->name} is currently out of stock.",
            ], 422);
        }

        $user = Auth::guard('web')->user();
        $sessionId = $request->session()->getId();
        $modeSlug = $product->mode?->slug ?? 'shopy';

        // Get or create cart for the product's shopping mode
        $cart = Cart::getOrCreate($user, $sessionId, $modeSlug);

        try {
            $item = $cart->addItem($product, $quantity);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        // Keep active shopping mode synced
        session(['active_shopping_mode' => $modeSlug]);

        $cartItemCount = $user ? $user->cartCount($modeSlug) : $cart->totalQuantity();
        $totalCartCount = $user ? $user->cartCount() : Cart::where('session_id', $sessionId)->with('items')->get()->sum(fn ($c) => $c->totalQuantity());

        return response()->json([
            'success'          => true,
            'action'           => 'added',
            'message'          => "Added {$product->name} to your {$product->mode?->name} cart!",
            'item_quantity'    => $item->quantity,
            'unit_price'       => number_format((float) $item->unit_price, 2),
            'line_total'       => number_format($item->lineTotal(), 2),
            'cart_count'       => $cartItemCount,
            'total_cart_count' => $totalCartCount,
            'mode_slug'        => $modeSlug,
            'subtotal'         => number_format($cart->subtotal(), 2),
            'delivery_fee'     => number_format($cart->deliveryFee(), 2),
            'tax'              => number_format($cart->taxAmount(), 2),
            'grand_total'      => number_format($cart->grandTotal(), 2),
        ]);
    }

    /**
     * AJAX endpoint to update the quantity of an item in the cart.
     */
    public function updateQuantity(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:0'],
        ]);

        $product = Product::with('mode')->findOrFail($validated['product_id']);
        $user = Auth::guard('web')->user();
        $sessionId = $request->session()->getId();
        $modeSlug = $product->mode?->slug ?? 'shopy';

        $cart = Cart::getOrCreate($user, $sessionId, $modeSlug);
        $item = $cart->updateItem($product->id, (int) $validated['quantity']);

        $cartItemCount = $user ? $user->cartCount($modeSlug) : $cart->totalQuantity();
        $totalCartCount = $user ? $user->cartCount() : Cart::where('session_id', $sessionId)->with('items')->get()->sum(fn ($c) => $c->totalQuantity());

        return response()->json([
            'success'          => true,
            'item_removed'     => is_null($item),
            'item_quantity'    => $item?->quantity ?? 0,
            'line_total'       => $item ? number_format($item->lineTotal(), 2) : '0.00',
            'cart_count'       => $cartItemCount,
            'total_cart_count' => $totalCartCount,
            'mode_slug'        => $modeSlug,
            'subtotal'         => number_format($cart->subtotal(), 2),
            'delivery_fee'     => number_format($cart->deliveryFee(), 2),
            'tax'              => number_format($cart->taxAmount(), 2),
            'discount'         => number_format($cart->discount(), 2),
            'grand_total'      => number_format($cart->grandTotal(), 2),
            'free_delivery_needed' => number_format($cart->amountNeededForFreeDelivery(), 2),
        ]);
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(Request $request, int $productId): RedirectResponse|JsonResponse
    {
        $product = Product::with('mode')->findOrFail($productId);
        $user = Auth::guard('web')->user();
        $sessionId = $request->session()->getId();
        $modeSlug = $product->mode?->slug ?? 'shopy';

        $cart = Cart::getOrCreate($user, $sessionId, $modeSlug);
        $cart->removeItem($productId);

        if ($request->expectsJson()) {
            $cartItemCount = $user ? $user->cartCount($modeSlug) : $cart->totalQuantity();
            return response()->json([
                'success'          => true,
                'message'          => "Removed {$product->name} from your cart.",
                'cart_count'       => $cartItemCount,
                'subtotal'         => number_format($cart->subtotal(), 2),
                'delivery_fee'     => number_format($cart->deliveryFee(), 2),
                'tax'              => number_format($cart->taxAmount(), 2),
                'grand_total'      => number_format($cart->grandTotal(), 2),
            ]);
        }

        return back()->with('success', "Removed {$product->name} from your cart.");
    }

    /**
     * Clear all items from the active cart.
     */
    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::guard('web')->user();
        $sessionId = $request->session()->getId();
        $modeSlug = $request->input('mode') ?? session('active_shopping_mode', 'shopy');

        $cart = Cart::getOrCreate($user, $sessionId, $modeSlug);
        $cart->clear();

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Cart has been cleared.',
                'cart_count' => 0,
            ]);
        }

        return back()->with('success', 'Your cart has been cleared.');
    }

    /**
     * Apply a discount coupon to the cart.
     */
    public function applyCoupon(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'coupon_code' => ['required', 'string', 'max:50'],
            'mode'        => ['nullable', 'string'],
        ]);

        $user = Auth::guard('web')->user();
        $sessionId = $request->session()->getId();
        $modeSlug = $validated['mode'] ?? session('active_shopping_mode', 'shopy');

        $cart = Cart::getOrCreate($user, $sessionId, $modeSlug);
        $code = strtoupper(trim($validated['coupon_code']));

        // Simple coupon rules (can be extended with coupon table)
        if ($code === 'SAVE10') {
            $discount = round($cart->subtotal() * 0.10, 2);
            $cart->coupon_code = 'SAVE10';
            $cart->discount_amount = $discount;
            $cart->save();
            return back()->with('success', "Coupon SAVE10 applied! You saved ₹{$discount}.");
        }

        if ($code === 'FLAT50') {
            if ($cart->subtotal() < 200) {
                return back()->with('error', 'Coupon FLAT50 requires a minimum cart subtotal of ₹200.');
            }
            $cart->coupon_code = 'FLAT50';
            $cart->discount_amount = 50.00;
            $cart->save();
            return back()->with('success', 'Coupon FLAT50 applied! ₹50 deducted from your total.');
        }

        return back()->with('error', "Invalid or expired coupon code '{$code}'.");
    }

    /**
     * Remove the active coupon from the cart.
     */
    public function removeCoupon(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();
        $sessionId = $request->session()->getId();
        $modeSlug = $request->input('mode') ?? session('active_shopping_mode', 'shopy');

        $cart = Cart::getOrCreate($user, $sessionId, $modeSlug);
        $cart->coupon_code = null;
        $cart->discount_amount = 0.00;
        $cart->save();

        return back()->with('info', 'Coupon removed.');
    }
}
