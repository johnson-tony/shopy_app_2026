<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the partner dashboard with assigned deliveries.
     */
    public function index(): View
    {
        $partner = auth('partner')->user();

        $assignedOrders = Order::with(['mode', 'userAddress', 'items'])
            ->where('delivery_partner_id', $partner->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        $stats = [
            'activeDeliveries' => Order::where('delivery_partner_id', $partner->id)
                ->whereIn('status', [
                    Order::STATUS_DELIVERY_ASSIGNED,
                    Order::STATUS_PICKED_UP,
                    Order::STATUS_OUT_FOR_DELIVERY,
                ])->count(),
            'deliveredToday' => Order::where('delivery_partner_id', $partner->id)
                ->where('status', Order::STATUS_DELIVERED)
                ->whereDate('delivered_at', today())
                ->count(),
            'totalDelivered' => Order::where('delivery_partner_id', $partner->id)
                ->where('status', Order::STATUS_DELIVERED)
                ->count(),
        ];

        return view('partner.pages.dashboard', compact('partner', 'assignedOrders', 'stats'));
    }
}