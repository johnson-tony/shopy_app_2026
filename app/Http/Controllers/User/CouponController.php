<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Mode;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    /**
     * Display the public coupons and offers directory.
     */
    public function index(Request $request): View
    {
        $selectedMode = $request->query('mode');
        if ($selectedMode === null) {
            $selectedMode = session('active_shopping_mode', 'all');
        }

        $modes = Mode::where('status', true)->orderBy('id')->get();

        $query = Coupon::active()->with('mode')->latest();

        if (!empty($selectedMode) && $selectedMode !== 'all') {
            $mode = $modes->firstWhere('slug', $selectedMode);
            if ($mode) {
                $query->where(function ($q) use ($mode) {
                    $q->whereNull('mode_id')->orWhere('mode_id', $mode->id);
                });
            }
        }

        $coupons = $query->paginate(12)->withQueryString();

        // Calculate coupon counts per store mode
        $modeCounts = [];
        $totalCoupons = Coupon::active()->count();

        foreach ($modes as $mode) {
            $modeCounts[$mode->slug] = Coupon::active()
                ->where(function ($q) use ($mode) {
                    $q->whereNull('mode_id')->orWhere('mode_id', $mode->id);
                })
                ->count();
        }

        return view('user.pages.coupons', compact(
            'coupons',
            'modes',
            'selectedMode',
            'modeCounts',
            'totalCoupons'
        ));
    }
}
