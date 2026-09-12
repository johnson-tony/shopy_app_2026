<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPartner;
use App\Models\Mode;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryPartnerController extends Controller
{
    /**
     * Display a listing of delivery partners with status & KYC queue.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->input('status', 'all');
        $search = $request->string('search')->trim()->toString();
        $vehicleFilter = $request->input('vehicle');

        $query = DeliveryPartner::with(['modes'])->withCount(['orders', 'returnOrders']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        if ($vehicleFilter && in_array($vehicleFilter, ['bike', 'motorcycle', 'scooter', 'bicycle', 'ev', 'van'], true)) {
            $query->where('vehicle_type', $vehicleFilter);
        }

        if ($statusFilter && $statusFilter !== 'all' && in_array($statusFilter, DeliveryPartner::STATUSES, true)) {
            $query->where('status', $statusFilter);
        }

        $partners = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $stats = [
            'total'           => DeliveryPartner::count(),
            'pendingApproval' => DeliveryPartner::where('status', DeliveryPartner::STATUS_PENDING_APPROVAL)->count(),
            'active'          => DeliveryPartner::where('status', DeliveryPartner::STATUS_ACTIVE)->count(),
            'rejected'        => DeliveryPartner::where('status', DeliveryPartner::STATUS_REJECTED)->count(),
            'suspended'       => DeliveryPartner::where('status', DeliveryPartner::STATUS_SUSPENDED)->count(),
            'activeOnline'    => DeliveryPartner::where('status', DeliveryPartner::STATUS_ACTIVE)->where('is_available', true)->count(),
            'activeOffline'   => DeliveryPartner::where('status', DeliveryPartner::STATUS_ACTIVE)->where('is_available', false)->count(),
        ];

        return view('admin.delivery_partners.index', compact('partners', 'stats', 'statusFilter', 'vehicleFilter', 'search'));
    }

    /**
     * Show the form for creating a new delivery partner directly from admin.
     */
    public function create(): View
    {
        $allModes = Mode::where('status', true)->get();
        return view('admin.delivery_partners.create', compact('allModes'));
    }

    /**
     * Store a newly created delivery partner.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:100'],
            'email'               => ['required', 'string', 'email', 'max:150', 'unique:delivery_partners,email'],
            'phone'               => ['required', 'string', 'min:10', 'max:20'],
            'password'            => ['required', 'string', 'min:6'],
            'vehicle_type'        => ['required', 'string', 'in:bike,motorcycle,scooter,bicycle,ev,van,other'],
            'vehicle_number'      => ['nullable', 'string', 'max:30'],
            'modes'               => ['required', 'array', 'min:1'],
            'modes.*'             => ['exists:modes,id'],
            'license_number'      => ['nullable', 'string', 'max:50'],
            'license_image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'id_proof_type'       => ['nullable', 'string', 'in:aadhaar,pan,voter_id,passport'],
            'id_proof_number'     => ['nullable', 'string', 'max:50'],
            'id_proof_image'      => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'bank_account_number' => ['nullable', 'string', 'max:30'],
            'bank_ifsc'           => ['nullable', 'string', 'max:20'],
            'upi_id'              => ['nullable', 'string', 'max:100'],
            'status'              => ['required', 'in:active,pending_approval,suspended'],
        ]);

        $licenseImagePath = null;
        if ($request->hasFile('license_image')) {
            $licenseImagePath = $request->file('license_image')->store('partners/kyc/licenses', 'public');
        }

        $idProofImagePath = null;
        if ($request->hasFile('id_proof_image')) {
            $idProofImagePath = $request->file('id_proof_image')->store('partners/kyc/ids', 'public');
        }

        $partner = DeliveryPartner::create([
            'name'                => $validated['name'],
            'email'               => $validated['email'],
            'phone'               => $validated['phone'],
            'password'            => bcrypt($validated['password']),
            'vehicle_type'        => $validated['vehicle_type'],
            'vehicle_number'      => $validated['vehicle_number'] ?? null,
            'license_number'      => $validated['license_number'] ?? null,
            'license_image'       => $licenseImagePath,
            'id_proof_type'       => $validated['id_proof_type'] ?? null,
            'id_proof_number'     => $validated['id_proof_number'] ?? null,
            'id_proof_image'      => $idProofImagePath,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_ifsc'           => $validated['bank_ifsc'] ?? null,
            'upi_id'              => $validated['upi_id'] ?? null,
            'status'              => $validated['status'],
            'approved_at'         => $validated['status'] === DeliveryPartner::STATUS_ACTIVE ? now() : null,
            'is_available'        => false,
            'location_source'     => DeliveryPartner::LOCATION_SOURCE_STATIC,
        ]);

        if (!empty($validated['modes'])) {
            $partner->modes()->sync($validated['modes']);
        }

        return redirect()->route('admin.delivery_partners.index')
            ->with('success', "Delivery partner {$partner->name} has been created successfully.");
    }

    /**
     * Display the specified delivery partner profile with KYC documents and tracking.
     */
    public function show(DeliveryPartner $partner): View
    {
        $partner->load(['modes', 'orders' => fn ($q) => $q->latest()->take(10), 'returnOrders']);
        $allModes = Mode::where('status', true)->get();

        $stats = [
            'totalAssigned' => $partner->orders()->count(),
            'delivered'     => $partner->orders()->where('status', Order::STATUS_DELIVERED)->count(),
            'activeNow'     => $partner->orders()->whereIn('status', [
                Order::STATUS_DELIVERY_ASSIGNED,
                Order::STATUS_PICKED_UP,
                Order::STATUS_OUT_FOR_DELIVERY,
            ])->count(),
            'returnsDone'   => $partner->returnOrders()->where('status', Order::STATUS_RETURNED)->count(),
        ];

        return view('admin.delivery_partners.show', compact('partner', 'allModes', 'stats'));
    }

    /**
     * Show the form for editing the specified delivery partner.
     */
    public function edit(DeliveryPartner $partner): View
    {
        $partner->load('modes');
        $allModes = Mode::where('status', true)->get();

        return view('admin.delivery_partners.edit', compact('partner', 'allModes'));
    }

    /**
     * Update the specified delivery partner profile.
     */
    public function update(Request $request, DeliveryPartner $partner): RedirectResponse
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:100'],
            'email'               => ['required', 'string', 'email', 'max:150', 'unique:delivery_partners,email,' . $partner->id],
            'phone'               => ['required', 'string', 'min:10', 'max:20'],
            'password'            => ['nullable', 'string', 'min:6'],
            'vehicle_type'        => ['required', 'string', 'in:bike,motorcycle,scooter,bicycle,ev,van,other'],
            'vehicle_number'      => ['nullable', 'string', 'max:30'],
            'modes'               => ['nullable', 'array'],
            'modes.*'             => ['exists:modes,id'],
            'license_number'      => ['nullable', 'string', 'max:50'],
            'license_image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'id_proof_type'       => ['nullable', 'string', 'in:aadhaar,pan,voter_id,passport'],
            'id_proof_number'     => ['nullable', 'string', 'max:50'],
            'id_proof_image'      => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'bank_account_number' => ['nullable', 'string', 'max:30'],
            'bank_ifsc'           => ['nullable', 'string', 'max:20'],
            'upi_id'              => ['nullable', 'string', 'max:100'],
            'status'              => ['required', 'in:active,pending_approval,rejected,suspended'],
        ]);

        $data = [
            'name'                => $validated['name'],
            'email'               => $validated['email'],
            'phone'               => $validated['phone'],
            'vehicle_type'        => $validated['vehicle_type'],
            'vehicle_number'      => $validated['vehicle_number'] ?? null,
            'license_number'      => $validated['license_number'] ?? null,
            'id_proof_type'       => $validated['id_proof_type'] ?? null,
            'id_proof_number'     => $validated['id_proof_number'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_ifsc'           => $validated['bank_ifsc'] ?? null,
            'upi_id'              => $validated['upi_id'] ?? null,
            'status'              => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = bcrypt($validated['password']);
        }

        if ($request->hasFile('license_image')) {
            $data['license_image'] = $request->file('license_image')->store('partners/kyc/licenses', 'public');
        }

        if ($request->hasFile('id_proof_image')) {
            $data['id_proof_image'] = $request->file('id_proof_image')->store('partners/kyc/ids', 'public');
        }

        if ($validated['status'] === DeliveryPartner::STATUS_ACTIVE && empty($partner->approved_at)) {
            $data['approved_at'] = now();
            $data['rejection_reason'] = null;
        }

        $partner->update($data);

        if ($request->has('modes')) {
            $partner->modes()->sync($request->input('modes'));
        }

        return redirect()->route('admin.delivery_partners.show', $partner)
            ->with('success', "Delivery partner {$partner->name} profile updated successfully.");
    }

    /**
     * Delete a delivery partner from the fleet.
     */
    public function destroy(DeliveryPartner $partner): RedirectResponse
    {
        $activeOrdersCount = $partner->orders()
            ->whereIn('status', [Order::STATUS_DELIVERY_ASSIGNED, Order::STATUS_PICKED_UP, Order::STATUS_OUT_FOR_DELIVERY])
            ->count();

        if ($activeOrdersCount > 0) {
            return back()->with('error', "Cannot delete partner {$partner->name} because they currently have {$activeOrdersCount} active order(s) in delivery. Please re-assign those orders first.");
        }

        $name = $partner->name;
        $partner->modes()->detach();
        $partner->delete();

        return redirect()->route('admin.delivery_partners.index')
            ->with('success', "Delivery partner {$name} has been removed from the fleet.");
    }

    /**
     * Approve the partner's registration and KYC documents.
     */
    public function approve(Request $request, DeliveryPartner $partner): RedirectResponse
    {
        $partner->update([
            'status'           => DeliveryPartner::STATUS_ACTIVE,
            'approved_at'      => now(),
            'rejection_reason' => null,
        ]);

        if ($request->has('modes')) {
            $partner->modes()->sync($request->input('modes'));
        }

        return back()->with('success', "Delivery partner {$partner->name} has been approved and activated.");
    }

    /**
     * Reject the partner's registration or KYC documents with explanation.
     */
    public function reject(Request $request, DeliveryPartner $partner): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:255'],
        ]);

        $partner->update([
            'status'           => DeliveryPartner::STATUS_REJECTED,
            'rejection_reason' => $request->input('rejection_reason'),
            'is_available'     => false,
        ]);

        return back()->with('success', "Delivery partner KYC for {$partner->name} was declined.");
    }

    /**
     * Toggle the partner's active or suspended status.
     */
    public function toggleStatus(DeliveryPartner $partner): RedirectResponse
    {
        $newStatus = $partner->status === DeliveryPartner::STATUS_ACTIVE
            ? DeliveryPartner::STATUS_SUSPENDED
            : DeliveryPartner::STATUS_ACTIVE;

        $partner->update([
            'status'       => $newStatus,
            'is_available' => $newStatus === DeliveryPartner::STATUS_ACTIVE ? $partner->is_available : false,
        ]);

        return back()->with('success', "Partner status updated to " . ucfirst($newStatus));
    }
}

