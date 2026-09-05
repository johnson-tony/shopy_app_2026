<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display customer order history.
     */
    public function index(Request $request): View
    {
        $user = Auth::guard('web')->user();

        $query = $user->orders()->with(['items.product', 'mode'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('mode')) {
            $query->whereHas('mode', fn ($q) => $q->where('slug', $request->query('mode')));
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('user.pages.orders.index', compact('orders'));
    }

    /**
     * Display order tracking and details.
     */
    public function show(Request $request, string $orderNumber): View
    {
        $user = Auth::guard('web')->user();

        $order = $user->orders()
            ->where('order_number', $orderNumber)
            ->with(['items.product', 'mode'])
            ->firstOrFail();

        // Get existing reviews by this user for the items in this order
        $reviewedProductIds = ProductReview::where('user_id', $user->id)
            ->whereIn('product_id', $order->items->pluck('product_id')->filter())
            ->pluck('product_id')
            ->toArray();

        return view('user.pages.orders.show', compact('order', 'reviewedProductIds'));
    }

    /**
     * Display celebratory Order Confirmation / Success page right after checkout.
     */
    public function success(Request $request, string $orderNumber): View
    {
        $user = Auth::guard('web')->user();

        $order = $user->orders()
            ->where('order_number', $orderNumber)
            ->with(['items.product', 'mode'])
            ->firstOrFail();

        return view('user.pages.order-success', compact('order'));
    }

    /**
     * Cancel an eligible order.
     */
    public function cancel(Request $request, string $orderNumber): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        $order = $user->orders()
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (!$order->canBeCancelled()) {
            return back()->with('error', 'This order cannot be cancelled as it has already progressed.');
        }

        $request->validate([
            'cancellation_reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($order, $request) {
            // Restore inventory stock for each item
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            $order->update([
                'status'              => Order::STATUS_CANCELLED,
                'cancelled_at'        => now(),
                'cancellation_reason' => $request->input('cancellation_reason', 'Cancelled by customer'),
            ]);
        });

        return back()->with('success', "Order #{$order->order_number} has been cancelled successfully.");
    }
}
