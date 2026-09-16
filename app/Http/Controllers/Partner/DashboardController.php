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

        $deliveredTodayCount = Order::where('delivery_partner_id', $partner->id)
            ->where('status', Order::STATUS_DELIVERED)
            ->whereDate('delivered_at', today())
            ->count();

        $returnsDoneTodayCount = Order::where('return_partner_id', $partner->id)
            ->where('status', Order::STATUS_RETURNED)
            ->whereDate('return_picked_up_at', today())
            ->count();

        $deliveredWeekCount = Order::where('delivery_partner_id', $partner->id)
            ->where('status', Order::STATUS_DELIVERED)
            ->where('delivered_at', '>=', now()->startOfWeek())
            ->count();

        $returnsDoneWeekCount = Order::where('return_partner_id', $partner->id)
            ->where('status', Order::STATUS_RETURNED)
            ->where('return_picked_up_at', '>=', now()->startOfWeek())
            ->count();

        $totalDeliveredCount = Order::where('delivery_partner_id', $partner->id)
            ->where('status', Order::STATUS_DELIVERED)
            ->count();

        $totalReturnsDoneCount = Order::where('return_partner_id', $partner->id)
            ->where('status', Order::STATUS_RETURNED)
            ->count();

        $ratePerDelivery = 50.0;
        $ratePerReturn = 35.0;

        $earningsToday = ($deliveredTodayCount * $ratePerDelivery) + ($returnsDoneTodayCount * $ratePerReturn);
        $earningsWeek = ($deliveredWeekCount * $ratePerDelivery) + ($returnsDoneWeekCount * $ratePerReturn);
        $earningsTotal = ($totalDeliveredCount * $ratePerDelivery) + ($totalReturnsDoneCount * $ratePerReturn);

        $codCollectedToday = (float) Order::where('delivery_partner_id', $partner->id)
            ->where('status', Order::STATUS_DELIVERED)
            ->whereDate('delivered_at', today())
            ->where('payment_method', 'cod')
            ->sum('grand_total');

        $stats = [
            'activeDeliveries' => Order::where('delivery_partner_id', $partner->id)
                ->whereIn('status', [
                    Order::STATUS_DELIVERY_ASSIGNED,
                    Order::STATUS_PICKED_UP,
                    Order::STATUS_OUT_FOR_DELIVERY,
                ])->count(),
            'pendingReturns' => $returnPickups->count(),
            'deliveredToday' => $deliveredTodayCount,
            'totalDelivered' => $totalDeliveredCount,
        ];

        $earnings = [
            'ratePerDelivery'   => $ratePerDelivery,
            'ratePerReturn'     => $ratePerReturn,
            'today'             => $earningsToday,
            'week'              => $earningsWeek,
            'total'             => $earningsTotal,
            'codCollectedToday' => $codCollectedToday,
            'tasksDoneToday'    => $deliveredTodayCount + $returnsDoneTodayCount,
        ];

        return view('partner.pages.dashboard', compact('partner', 'assignedOrders', 'returnPickups', 'stats', 'earnings', 'modeFilter'));
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