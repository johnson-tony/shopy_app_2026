<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderStatusRequest;
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
            Order::STATUS_SHIPPED,
            Order::STATUS_DELIVERED,
            Order::STATUS_CANCELLED,
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
        $order->load(['user', 'mode', 'userAddress', 'items.product', 'reviews']);

        $totals = $order->items->reduce(function (array $carry, $item) {
            $carry['quantity'] += $item->quantity;
            return $carry;
        }, ['quantity' => 0]);

        return view('admin.orders.show', [
            'order'     => $order,
            'quantity'  => $totals['quantity'],
            'statuses'  => [
                Order::STATUS_CONFIRMED  => 'Order Confirmed',
                Order::STATUS_PROCESSING => 'Processing',
                Order::STATUS_SHIPPED    => 'Shipped',
                Order::STATUS_DELIVERED  => 'Delivered',
                Order::STATUS_CANCELLED  => 'Cancelled',
            ],
            'paymentStatuses' => [
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

        $order->status = $data['status'];

        if ($order->status === Order::STATUS_DELIVERED && empty($order->delivered_at)) {
            $order->delivered_at = now();
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
}
