<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartnerOrderController extends Controller
{
    /**
     * Display assigned order details with maps, customer info, and fulfillment actions.
     */
    public function show(Order $order): View|RedirectResponse
    {
        $partner = auth('partner')->user();

        // Ensure the order is assigned to this partner for delivery or return pickup
        if ($order->delivery_partner_id !== $partner->id && $order->return_partner_id !== $partner->id) {
            abort(403, 'You are not assigned to this delivery or return.');
        }

        $order->load(['user', 'mode', 'userAddress', 'items.product']);

        return view('partner.pages.orders.show', compact('partner', 'order'));
    }

    /**
     * Update order milestone: picked up or out for delivery.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $partner = auth('partner')->user();

        if ($order->delivery_partner_id !== $partner->id) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'status' => ['required', 'in:' . Order::STATUS_PICKED_UP . ',' . Order::STATUS_OUT_FOR_DELIVERY],
        ]);

        $newStatus = $request->input('status');

        if ($newStatus === Order::STATUS_PICKED_UP) {
            if (!in_array($order->status, [Order::STATUS_DELIVERY_ASSIGNED, Order::STATUS_READY_FOR_DELIVERY])) {
                return back()->with('error', 'Order cannot be marked picked up from its current status.');
            }
            $order->update([
                'status'         => Order::STATUS_PICKED_UP,
                'picked_up_at'   => now(),
            ]);
            $msg = "Order #{$order->order_number} marked as Picked Up from store.";
        } elseif ($newStatus === Order::STATUS_OUT_FOR_DELIVERY) {
            if (!in_array($order->status, [Order::STATUS_PICKED_UP, Order::STATUS_DELIVERY_ASSIGNED])) {
                return back()->with('error', 'Order must be picked up first.');
            }
            $order->update([
                'status'              => Order::STATUS_OUT_FOR_DELIVERY,
                'out_for_delivery_at' => now(),
            ]);
            $msg = "Order #{$order->order_number} is now Out for Delivery! Live tracking is active for the customer.";
        }

        return back()->with('success', $msg ?? 'Order status updated.');
    }

    /**
     * Partner delivers order: uploads proof of delivery photo, confirms payment, marks delivered.
     */
    public function deliver(Request $request, Order $order): RedirectResponse
    {
        $partner = auth('partner')->user();

        if ($order->delivery_partner_id !== $partner->id) {
            abort(403, 'Unauthorized.');
        }

        if (!in_array($order->status, [Order::STATUS_OUT_FOR_DELIVERY, Order::STATUS_PICKED_UP])) {
            return back()->with('error', 'Order must be Out for Delivery before marking as delivered.');
        }

        $validated = $request->validate([
            'delivery_proof_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'delivery_notes'       => ['nullable', 'string', 'max:500'],
            'cod_collected'        => [$order->payment_method === Order::PAYMENT_METHOD_COD ? 'required' : 'nullable', 'boolean'],
        ], [
            'delivery_proof_image.required' => 'Proof of Delivery photo is required. Please capture a photo of the package at delivery.',
        ]);

        $imagePath = null;
        if ($request->hasFile('delivery_proof_image')) {
            $imagePath = $request->file('delivery_proof_image')->store('deliveries/proofs', 'public');
        }

        $paymentStatus = $order->payment_status;
        if ($order->payment_method === Order::PAYMENT_METHOD_COD && $request->boolean('cod_collected')) {
            $paymentStatus = Order::PAYMENT_STATUS_PAID;
        }

        $order->update([
            'status'               => Order::STATUS_DELIVERED,
            'delivered_at'         => now(),
            'delivery_proof_image' => $imagePath,
            'delivery_notes'       => $request->input('delivery_notes') ?? $request->input('notes') ?? 'Delivered by ' . $partner->name,
            'payment_status'       => $paymentStatus,
        ]);

        return redirect()->route('partner.dashboard')->with('success', "Order #{$order->order_number} delivered successfully! Great job.");
    }

    /**
     * Partner collects returned item: inspects, uploads photo, auto-restocks inventory, sets refund.
     */
    public function pickupReturn(Request $request, Order $order): RedirectResponse
    {
        $partner = auth('partner')->user();

        if ($order->return_partner_id !== $partner->id && $order->delivery_partner_id !== $partner->id) {
            abort(403, 'Unauthorized.');
        }

        if ($order->status !== Order::STATUS_RETURN_APPROVED) {
            return back()->with('error', 'This order is not currently in an approved return state.');
        }

        $validated = $request->validate([
            'return_pickup_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'return_pickup_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'return_pickup_image.required' => 'Photo of the returned package/product is required for quality verification.',
        ]);

        $imagePath = null;
        if ($request->hasFile('return_pickup_image')) {
            $imagePath = $request->file('return_pickup_image')->store('returns/pickups', 'public');
        }

        // Restock inventory for all items in this returned order
        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        $order->update([
            'status'              => Order::STATUS_RETURNED,
            'return_status'       => 'completed',
            'return_pickup_image' => $imagePath,
            'return_pickup_notes' => $validated['return_pickup_notes'] ?? 'Picked up and verified by ' . $partner->name,
            'return_picked_up_at' => now(),
            'return_resolved_at'  => now(),
            'payment_status'      => Order::PAYMENT_STATUS_REFUNDED,
        ]);

        return redirect()->route('partner.dashboard')->with('success', "Return for Order #{$order->order_number} collected, restocked, and refund finalized!");
    }
}
