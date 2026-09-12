<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the partner dashboard with assigned deliveries and return pickups.
     */
    public function index(Request $request): View
    {
        $partner = auth('partner')->user();
        $modeFilter = $request->input('mode');

        $query = Order::with(['mode', 'userAddress', 'items'])
            ->where('delivery_partner_id', $partner->id);

        if ($modeFilter && $modeFilter !== 'all') {
            $query->whereHas('mode', fn ($q) => $q->where('slug', $modeFilter));
        }

        $assignedOrders = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        // Approved return pickups assigned to this partner
        $returnPickups = Order::with(['mode', 'userAddress', 'items'])
            ->where('return_partner_id', $partner->id)
            ->where('status', Order::STATUS_RETURN_APPROVED)
            ->orderByDesc('return_requested_at')
            ->get();

        $stats = [
            'activeDeliveries' => Order::where('delivery_partner_id', $partner->id)
                ->whereIn('status', [
                    Order::STATUS_DELIVERY_ASSIGNED,
                    Order::STATUS_PICKED_UP,
                    Order::STATUS_OUT_FOR_DELIVERY,
                ])->count(),
            'pendingReturns' => $returnPickups->count(),
            'deliveredToday' => Order::where('delivery_partner_id', $partner->id)
                ->where('status', Order::STATUS_DELIVERED)
                ->whereDate('delivered_at', today())
                ->count(),
            'totalDelivered' => Order::where('delivery_partner_id', $partner->id)
                ->where('status', Order::STATUS_DELIVERED)
                ->count(),
        ];

        return view('partner.pages.dashboard', compact('partner', 'assignedOrders', 'returnPickups', 'stats', 'modeFilter'));
    }

    /**
     * Toggle the partner's on-duty / off-duty status.
     */
    public function toggleAvailability(): RedirectResponse
    {
        $partner = auth('partner')->user();
        $partner->is_available = !$partner->is_available;
        $partner->save();

        $state = $partner->is_available ? 'ONLINE 🟢 (Ready for orders)' : 'OFFLINE 🔴 (Duty paused)';

        return back()->with('success', "Your status is now {$state}");
    }
}