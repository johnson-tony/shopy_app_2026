<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Mode;
use App\Models\Order;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display customer order history with simple status & channel filters.
     */
    public function index(Request $request): View
    {
        $user = Auth::guard('web')->user();
        $status = $request->query('status', 'all');
        $modeSlug = $request->query('mode', 'all');
        $search = trim($request->query('search', ''));

        $query = $user->orders()->with(['items.product', 'mode'])->latest();

        // Simple grouped status filtering: delivered, in_progress, returns, cancelled
        if ($status === 'delivered') {
            $query->where('status', Order::STATUS_DELIVERED);
        } elseif ($status === 'in_progress' || $status === 'active') {
            $query->whereIn('status', [
                Order::STATUS_CONFIRMED,
                Order::STATUS_PROCESSING,
                Order::STATUS_READY_FOR_DELIVERY,
                Order::STATUS_DELIVERY_ASSIGNED,
                Order::STATUS_PICKED_UP,
                Order::STATUS_SHIPPED,
                Order::STATUS_OUT_FOR_DELIVERY,
            ]);
        } elseif ($status === 'returns') {
            $query->whereIn('status', [
                Order::STATUS_RETURN_REQUESTED,
                Order::STATUS_RETURN_APPROVED,
                Order::STATUS_RETURN_REJECTED,
                Order::STATUS_RETURNED,
            ]);
        } elseif ($status === 'cancelled') {
            $query->where('status', Order::STATUS_CANCELLED);
        } elseif ($status !== 'all' && !empty($status)) {
            $query->where('status', $status);
        }

        // Channel / Shopping Mode filter
        if ($modeSlug !== 'all' && !empty($modeSlug)) {
            $query->whereHas('mode', fn ($q) => $q->where('slug', $modeSlug));
        }

        // Keyword search (Order Number or Product Name)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('items', fn ($iq) => $iq->where('product_name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->paginate(10)->withQueryString();

        // Simple status tab counts for the user
        $counts = [
            'all'         => $user->orders()->count(),
            'delivered'   => $user->orders()->where('status', Order::STATUS_DELIVERED)->count(),
            'in_progress' => $user->orders()->whereIn('status', [
                Order::STATUS_CONFIRMED,
                Order::STATUS_PROCESSING,
                Order::STATUS_READY_FOR_DELIVERY,
                Order::STATUS_DELIVERY_ASSIGNED,
                Order::STATUS_PICKED_UP,
                Order::STATUS_SHIPPED,
                Order::STATUS_OUT_FOR_DELIVERY,
            ])->count(),
            'returns'     => $user->orders()->whereIn('status', [
                Order::STATUS_RETURN_REQUESTED,
                Order::STATUS_RETURN_APPROVED,
                Order::STATUS_RETURN_REJECTED,
                Order::STATUS_RETURNED,
            ])->count(),
            'cancelled'   => $user->orders()->where('status', Order::STATUS_CANCELLED)->count(),
        ];

        $modes = Mode::where('status', true)->orderBy('sort_order')->orderBy('id')->get();

        return view('user.pages.orders.index', compact('orders', 'status', 'modeSlug', 'search', 'counts', 'modes'));
    }

    /**
     * Display order tracking and details.
     */
    public function show(Request $request, string $orderNumber): View
    {
        $user = Auth::guard('web')->user();

        $order = $user->orders()
            ->where('order_number', $orderNumber)
            ->with(['items.product', 'mode', 'deliveryPartner', 'userAddress'])
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

    /**
     * Customer requests a return for a delivered order.
     */
    public function requestReturn(Request $request, string $orderNumber): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        $order = $user->orders()
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (!$order->isReturnEligible()) {
            return back()->with('error', 'This order is not eligible for return. Returns can only be requested on delivered orders within the return policy window.');
        }

        $validated = $request->validate([
            'return_reason' => ['required', 'string', 'max:150'],
            'return_note'   => ['nullable', 'string', 'max:1000'],
            'return_image'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        $imagePath = null;
        if ($request->hasFile('return_image')) {
            try {
                $imagePath = $request->file('return_image')->store('returns', 'public');
            } catch (\Throwable $e) {
                // Ignore upload failure if storage is constrained
            }
        }

        $order->update([
            'status'              => Order::STATUS_RETURN_REQUESTED,
            'return_status'       => 'requested',
            'return_reason'       => $validated['return_reason'],
            'return_note'         => $validated['return_note'] ?? null,
            'return_image'        => $imagePath,
            'return_requested_at' => now(),
        ]);

        return back()->with('success', "Return request for Order #{$order->order_number} has been submitted successfully. Our team will review it shortly.");
    }

    /**
     * Get live telemetry and rider location for active order tracking.
     */
    public function liveLocation(Request $request, string $orderNumber): JsonResponse
    {
        $user = Auth::guard('web')->user();

        $order = $user->orders()
            ->where('order_number', $orderNumber)
            ->with(['deliveryPartner', 'userAddress'])
            ->firstOrFail();

        $partner = $order->deliveryPartner;

        return response()->json([
            'status'         => $order->status,
            'is_active'      => in_array($order->status, [
                Order::STATUS_DELIVERY_ASSIGNED,
                Order::STATUS_PICKED_UP,
                Order::STATUS_OUT_FOR_DELIVERY,
            ]),
            'is_delivered'   => $order->status === Order::STATUS_DELIVERED,
            'delivered_at'   => $order->delivered_at?->format('d M Y, h:i A'),
            'delivery_proof' => $order->delivery_proof_image ? asset('storage/' . $order->delivery_proof_image) : null,
            'rider'          => $partner ? [
                'name'           => $partner->name,
                'phone'          => $partner->phone,
                'vehicle_type'   => ucfirst($partner->vehicle_type ?? 'bike'),
                'vehicle_number' => $partner->vehicle_number,
                'latitude'       => $partner->latitude ? (float) $partner->latitude : null,
                'longitude'      => $partner->longitude ? (float) $partner->longitude : null,
                'last_updated'   => $partner->last_location_at?->diffForHumans(),
            ] : null,
            'destination'    => [
                'address'        => $order->formatted_shipping_address,
                'recipient'      => $order->shipping_name ?? $order->userAddress?->full_name ?? $user->name,
                'phone'          => $order->shipping_phone ?? $order->userAddress?->phone,
            ],
        ]);
    }
}
