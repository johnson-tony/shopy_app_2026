<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Mode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    /**
     * Display a listing of coupons with statistics, filters, and search.
     */
    public function index(Request $request): View
    {
        $admin = auth('admin')->user();
        $search = $request->string('search')->trim()->toString();
        $typeFilter = $request->input('type');
        $statusFilter = $request->input('status');
        $modeFilter = $request->input('mode');

        $query = Coupon::with('mode')->withCount('usages');

        // Mode scoping for non-superadmin
        if ($admin && !$admin->isSuperAdmin()) {
            $allowedModeIds = $admin->getAllowedModeIds();
            $query->where(function ($q) use ($allowedModeIds) {
                $q->whereNull('mode_id')->orWhereIn('mode_id', $allowedModeIds);
            });
        }

        // Search Keyword
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Mode Filter
        if ($modeFilter !== null && $modeFilter !== '') {
            if ($modeFilter === 'all_modes') {
                $query->whereNull('mode_id');
            } else {
                if ($admin && !$admin->isSuperAdmin() && !$admin->hasModeAccess((int) $modeFilter)) {
                    abort(403, 'Unauthorized shopping mode filter.');
                }
                $query->where('mode_id', (int) $modeFilter);
            }
        }

        // Type Filter
        if ($typeFilter && in_array($typeFilter, Coupon::TYPES, true)) {
            $query->where('type', $typeFilter);
        }

        // Status Filter
        if ($statusFilter === 'active') {
            $query->where('status', true)
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
        } elseif ($statusFilter === 'inactive') {
            $query->where('status', false);
        } elseif ($statusFilter === 'expired') {
            $query->whereNotNull('expires_at')->where('expires_at', '<', now());
        }

        $coupons = $query->orderByDesc('id')->paginate(15)->withQueryString();

        // Statistics Cards
        $statsQuery = Coupon::query();
        if ($admin && !$admin->isSuperAdmin()) {
            $allowedModeIds = $admin->getAllowedModeIds();
            $statsQuery->where(function ($q) use ($allowedModeIds) {
                $q->whereNull('mode_id')->orWhereIn('mode_id', $allowedModeIds);
            });
        }

        $totalCoupons = (clone $statsQuery)->count();
        $activeCoupons = (clone $statsQuery)->where('status', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->count();
        $totalRedemptions = (clone $statsQuery)->sum('times_used');
        $totalDiscountGiven = CouponUsage::sum('discount_amount');

        // Modes list for filter dropdown
        $modesQuery = Mode::where('status', true);
        if ($admin && !$admin->isSuperAdmin()) {
            $modesQuery->whereIn('id', $admin->getAllowedModeIds());
        }
        $modes = $modesQuery->orderBy('name')->get();

        return view('admin.coupons.index', compact(
            'coupons',
            'modes',
            'search',
            'typeFilter',
            'statusFilter',
            'modeFilter',
            'totalCoupons',
            'activeCoupons',
            'totalRedemptions',
            'totalDiscountGiven'
        ));
    }

    /**
     * Show the form for creating a new coupon.
     */
    public function create(): View
    {
        $admin = auth('admin')->user();
        $modesQuery = Mode::where('status', true);
        if ($admin && !$admin->isSuperAdmin()) {
            $modesQuery->whereIn('id', $admin->getAllowedModeIds());
        }
        $modes = $modesQuery->orderBy('name')->get();

        return view('admin.coupons.create', compact('modes'));
    }

    /**
     * Store a newly created coupon in storage.
     */
    public function store(CouponRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $coupon = Coupon::create($data);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', "Coupon promo '{$coupon->code}' has been created successfully!");
    }

    /**
     * Display the specified coupon with redemption logs.
     */
    public function show(Coupon $coupon): View
    {
        $this->checkModeAuthorization($coupon);

        $coupon->load('mode');
        $usages = $coupon->usages()
            ->with('user')
            ->orderByDesc('used_at')
            ->paginate(15);

        $totalSavings = $coupon->usages()->sum('discount_amount');

        return view('admin.coupons.show', compact('coupon', 'usages', 'totalSavings'));
    }

    /**
     * Show the form for editing the specified coupon.
     */
    public function edit(Coupon $coupon): View
    {
        $this->checkModeAuthorization($coupon);

        $admin = auth('admin')->user();
        $modesQuery = Mode::where('status', true);
        if ($admin && !$admin->isSuperAdmin()) {
            $modesQuery->whereIn('id', $admin->getAllowedModeIds());
        }
        $modes = $modesQuery->orderBy('name')->get();

        return view('admin.coupons.edit', compact('coupon', 'modes'));
    }

    /**
     * Update the specified coupon in storage.
     */
    public function update(CouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $this->checkModeAuthorization($coupon);

        $data = $request->validated();
        $coupon->update($data);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', "Coupon promo '{$coupon->code}' has been updated successfully!");
    }

    /**
     * Remove the specified coupon from storage.
     */
    public function destroy(Coupon $coupon): RedirectResponse
    {
        $this->checkModeAuthorization($coupon);

        $code = $coupon->code;
        $coupon->delete();

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', "Coupon promo '{$code}' has been deleted successfully.");
    }

    /**
     * Toggle the active status of a coupon.
     */
    public function toggleStatus(Coupon $coupon, Request $request): RedirectResponse|JsonResponse
    {
        $this->checkModeAuthorization($coupon);

        $coupon->status = !$coupon->status;
        $coupon->save();

        $statusText = $coupon->status ? 'activated' : 'deactivated';
        $message = "Coupon '{$coupon->code}' has been {$statusText}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status'  => $coupon->status,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Verify that the current admin user is authorized to manage this coupon's mode.
     */
    protected function checkModeAuthorization(Coupon $coupon): void
    {
        $admin = auth('admin')->user();
        if ($admin && !$admin->isSuperAdmin() && $coupon->mode_id && !$admin->hasModeAccess($coupon->mode_id)) {
            abort(403, 'Unauthorized. You do not have permission to manage coupons for this shopping mode.');
        }
    }
}
