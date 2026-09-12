<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderStatusRequest;
use App\Models\DeliveryPartner;
use App\Models\Mode;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a listing of orders grouped by shopping mode tabs.
     */
    public function index(Request $request): View
    {
        $admin = auth('admin')->user();
        $search = $request->string('search')->trim()->toString();
        $statusFilter = $request->input('status');
        $paymentFilter = $request->input('payment');
        $tab = $request->input('mode', 'all');

        // Tabs: All + each mode the admin can access
        $modesQuery = Mode::where('status', true)->orderBy('sort_order')->orderBy('id');
        if ($admin && !$admin->isSuperAdmin()) {
            $modesQuery->whereIn('id', $admin->getAllowedModeIds());
        }
        $modes = $modesQuery->get();

        $query = Order::with(['mode', 'user'])->withCount('items');

        // Mode scoping for non-superadmin
        if ($admin && !$admin->isSuperAdmin()) {
            $allowedModeIds = $admin->getAllowedModeIds();
            $query->where(function ($q) use ($allowedModeIds) {
                $q->whereNull('mode_id')->orWhereIn('mode_id', $allowedModeIds);
            });
        }

        // Active tab mode filter
        if ($tab === 'null') {
            $query->whereNull('mode_id');
        } elseif ($tab !== 'all') {
            $query->where('mode_id', (int) $tab);
        }

        // Search keyword (order number or customer name/email)
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Status Filter
        if ($statusFilter && in_array($statusFilter, [
            Order::STATUS_CONFIRMED,
            Order::STATUS_PROCESSING,
            Order::STATUS_READY_FOR_DELIVERY,
            Order::STATUS_DELIVERY_ASSIGNED,
            Order::STATUS_PICKED_UP,
            Order::STATUS_SHIPPED,
            Order::STATUS_OUT_FOR_DELIVERY,
            Order::STATUS_DELIVERED,
            Order::STATUS_CANCELLED,
            Order::STATUS_RETURN_REQUESTED,
            Order::STATUS_RETURN_APPROVED,
            Order::STATUS_RETURN_REJECTED,
            Order::STATUS_RETURNED,
        ], true)) {
            $query->where('status', $statusFilter);
        }

        // Payment Status Filter
        if ($paymentFilter && in_array($paymentFilter, [
            Order::PAYMENT_STATUS_PENDING,
            Order::PAYMENT_STATUS_PAID,
            Order::PAYMENT_STATUS_FAILED,
            Order::PAYMENT_STATUS_REFUNDED,
        ], true)) {
            $query->where('payment_status', $paymentFilter);
        }

        $orders = $query->orderByDesc('id')->paginate(10)->withQueryString();

        // Statistics for the active tab
        $statsQuery = Order::query();
        if ($admin && !$admin->isSuperAdmin()) {
            $allowedModeIds = $admin->getAllowedModeIds();
            $statsQuery->where(function ($q) use ($allowedModeIds) {
                $q->whereNull('mode_id')->orWhereIn('mode_id', $allowedModeIds);
            });
        }
        $statsQuery->when($tab === 'null', fn ($q) => $q->whereNull('mode_id'))
            ->when($tab !== 'all' && $tab !== 'null', fn ($q) => $q->where('mode_id', (int) $tab));

        $totalOrders     = (clone $statsQuery)->count();
        $totalRevenue    = (clone $statsQuery)->sum('grand_total');
        $pendingOrders   = (clone $statsQuery)->where('status', Order::STATUS_CONFIRMED)->count();
        $processingOrders = (clone $statsQuery)->where('status', Order::STATUS_PROCESSING)->count();
        $shippedOrders   = (clone $statsQuery)->where('status', Order::STATUS_SHIPPED)->count();
        $deliveredOrders = (clone $statsQuery)->where('status', Order::STATUS_DELIVERED)->count();
        $cancelledOrders = (clone $statsQuery)->where('status', Order::STATUS_CANCELLED)->count();

        return view('admin.orders.index', compact(
            'orders',
            'modes',
            'tab',
            'search',
            'statusFilter',
            'paymentFilter',
            'totalOrders',
            'totalRevenue',
            'pendingOrders',
            'processingOrders',
            'shippedOrders',
            'deliveredOrders',
            'cancelledOrders'
        ));
    }

    /**
     * Display the specified order with items and fulfillment controls.
     */
    public function show(Order $order): View
    {
        $order->load(['user', 'mode', 'userAddress', 'items.product', 'reviews', 'deliveryPartner', 'returnPartner']);

        $totals = $order->items->reduce(function (array $carry, $item) {
            $carry['quantity'] += $item->quantity;
            return $carry;
        }, ['quantity' => 0]);

        $allStatuses = [
            Order::STATUS_CONFIRMED            => 'Order Confirmed',
            Order::STATUS_PROCESSING           => 'Processing',
            Order::STATUS_READY_FOR_DELIVERY   => 'Ready for Delivery',
            Order::STATUS_DELIVERY_ASSIGNED    => 'Delivery Assigned',
            Order::STATUS_PICKED_UP            => 'Picked Up',
            Order::STATUS_SHIPPED              => 'Shipped',
            Order::STATUS_OUT_FOR_DELIVERY     => 'Out for Delivery',
            Order::STATUS_DELIVERED            => 'Delivered',
            Order::STATUS_CANCELLED            => 'Cancelled',
        ];

        // Only surface statuses the order is legally allowed to move into.
        $statuses = collect($allStatuses)
            ->filter(fn ($label, $value) => $order->canTransitionTo($value))
            ->all();

        // Query active delivery partners who can fulfil this order's shopping mode
        $deliveryPartners = DeliveryPartner::where('status', DeliveryPartner::STATUS_ACTIVE)
            ->when($order->mode_id, function ($q) use ($order) {
                $q->whereHas('modes', fn ($mq) => $mq->where('modes.id', $order->mode_id));
            })->get();

        if ($deliveryPartners->isEmpty()) {
            $deliveryPartners = DeliveryPartner::where('status', DeliveryPartner::STATUS_ACTIVE)->get();
        }

        return view('admin.orders.show', [
            'order'            => $order,
            'quantity'         => $totals['quantity'],
            'statuses'         => $statuses ?: [$order->status => $allStatuses[$order->status] ?? ucwords(str_replace('-', ' ', $order->status))],
            'deliveryPartners' => $deliveryPartners,
            'paymentStatuses'  => [
                Order::PAYMENT_STATUS_PENDING  => 'Pending',
                Order::PAYMENT_STATUS_PAID     => 'Paid',
                Order::PAYMENT_STATUS_FAILED   => 'Failed',
                Order::PAYMENT_STATUS_REFUNDED => 'Refunded',
            ],
        ]);
    }

    /**
     * Update the order's status and payment status.
     */
    public function update(OrderStatusRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        if (!$order->canTransitionTo($data['status'])) {
            return back()
                ->withInput()
                ->with('error', "Order {$order->order_number} cannot move from '{$order->status}' to '{$data['status']}'.");
        }

        $order->status = $data['status'];

        // Set the appropriate lifecycle timestamps for delivery progress.
        $statusTimestamps = [
            Order::STATUS_READY_FOR_DELIVERY => 'ready_for_delivery_at',
            Order::STATUS_DELIVERY_ASSIGNED => 'assigned_at',
            Order::STATUS_PICKED_UP => 'picked_up_at',
            Order::STATUS_OUT_FOR_DELIVERY => 'out_for_delivery_at',
            Order::STATUS_DELIVERED => 'delivered_at',
        ];

        foreach ($statusTimestamps as $status => $column) {
            if ($order->status === $status && empty($order->{$column})) {
                $order->{$column} = now();
            }
        }

        if ($order->status === Order::STATUS_CANCELLED) {
            $order->cancelled_at = now();
            $order->cancellation_reason = $data['cancellation_reason'] ?? null;
        }

        if (array_key_exists('payment_status', $data)) {
            $order->payment_status = $data['payment_status'];
        }

        $order->save();

        return back()->with('success', "Order {$order->order_number} has been updated successfully.");
    }

    /**
     * Approve a customer's return request.
     */
    public function approveReturn(Request $request, Order $order): RedirectResponse
    {
        if ($order->status !== Order::STATUS_RETURN_REQUESTED) {
            return back()->with('error', "Order {$order->order_number} does not have an active return request.");
        }

        $order->update([
            'status'             => Order::STATUS_RETURN_APPROVED,
            'return_status'      => 'approved',
            'return_resolved_at' => now(),
        ]);

        return back()->with('success', "Return request for Order {$order->order_number} has been approved.");
    }

    /**
     * Complete the return process and optionally restock inventory.
     */
    public function completeReturn(Request $request, Order $order): RedirectResponse
    {
        if (!in_array($order->status, [Order::STATUS_RETURN_APPROVED, Order::STATUS_RETURN_REQUESTED])) {
            return back()->with('error', "Order {$order->order_number} is not in an approved return state.");
        }

        // Restock products upon return completion
        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        $order->update([
            'status'             => Order::STATUS_RETURNED,
            'return_status'      => 'completed',
            'return_resolved_at' => now(),
        ]);

        return back()->with('success', "Order {$order->order_number} return has been marked completed and inventory restocked.");
    }

    /**
     * Reject a customer's return request with an explanation.
     */
    public function rejectReturn(Request $request, Order $order): RedirectResponse
    {
        if ($order->status !== Order::STATUS_RETURN_REQUESTED) {
            return back()->with('error', "Order {$order->order_number} does not have an active return request.");
        }

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:255'],
        ]);

        $order->update([
            'status'                  => Order::STATUS_DELIVERED,
            'return_status'           => 'rejected',
            'return_rejection_reason' => $request->input('rejection_reason'),
            'return_resolved_at'      => now(),
        ]);

        return back()->with('success', "Return request for Order {$order->order_number} has been rejected.");
    }

    /**
     * Assign an active delivery partner to the order.
     */
    public function assignPartner(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'delivery_partner_id' => ['required', 'exists:delivery_partners,id'],
        ]);

        $partner = DeliveryPartner::findOrFail($request->input('delivery_partner_id'));

        if ($partner->status !== DeliveryPartner::STATUS_ACTIVE) {
            return back()->with('error', "Partner {$partner->name} is not active.");
        }

        $order->delivery_partner_id = $partner->id;

        // Advance to delivery-assigned if currently in a prior fulfilment state
        if (in_array($order->status, [Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING, Order::STATUS_READY_FOR_DELIVERY], true)) {
            $order->status = Order::STATUS_DELIVERY_ASSIGNED;
            if (empty($order->assigned_at)) {
                $order->assigned_at = now();
            }
        }

        $order->save();

        return back()->with('success', "Order {$order->order_number} has been assigned to {$partner->name}.");
    }

    /**
     * Assign a delivery partner to pick up an approved customer return.
     */
    public function assignReturnPartner(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'return_partner_id' => ['required', 'exists:delivery_partners,id'],
        ]);

        $partner = DeliveryPartner::findOrFail($request->input('return_partner_id'));

        if ($partner->status !== DeliveryPartner::STATUS_ACTIVE) {
            return back()->with('error', "Partner {$partner->name} is not active.");
        }

        $order->return_partner_id = $partner->id;
        $order->save();

        return back()->with('success', "Return pickup for Order {$order->order_number} has been assigned to {$partner->name}.");
    }
}
