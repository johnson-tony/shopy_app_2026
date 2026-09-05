<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Mail\AdminInvitationMail;
use App\Models\Admin;
use App\Models\AdminInvitation;
use App\Models\Mode;
use App\Models\Role;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminManagementController extends Controller
{
    /**
     * Display a listing of administrators.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $roleFilter = $request->input('role');
        $statusFilter = $request->input('status');

        $query = Admin::with(['roles', 'modes', 'invitations' => function ($q) {
            $q->latest();
        }]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($roleFilter) {
            $query->whereHas('roles', fn ($q) => $q->where('roles.id', (int) $roleFilter));
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $admins = $query->orderBy('id', 'asc')->paginate(15)->withQueryString();

        $stats = [
            'total' => Admin::count(),
            'active' => Admin::where('status', Admin::STATUS_ACTIVE)->count(),
            'super_admins' => Admin::whereHas('roles', fn ($q) => $q->where('slug', 'super-admin'))->count(),
            'pending_invitations' => AdminInvitation::whereNull('accepted_at')->where('expires_at', '>', Carbon::now())->count(),
        ];

        $roles = Role::active()->orderBy('name', 'asc')->get();

        return view('admin.admins.index', compact('admins', 'stats', 'roles', 'search', 'roleFilter', 'statusFilter'));
    }

    /**
     * Show the form for creating a new administrator with invitation.
     */
    public function create(): View
    {
        $actor = auth('admin')->user();
        $roles = Role::active()
            ->when(!$actor->isSuperAdmin(), fn ($q) => $q->where('slug', '!=', 'super-admin'))
            ->orderBy('name', 'asc')
            ->get();

        $modes = Mode::active()->ordered()->get();

        return view('admin.admins.create', compact('roles', 'modes'));
    }

    /**
     * Store a newly created administrator and dispatch secure invitation.
     */
    public function store(StoreAdminRequest $request): RedirectResponse
    {
        $actor = auth('admin')->user();
        $data = $request->validated();

        // Admin created in inactive status until password is set via invitation
        $admin = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make(Str::random(32)),
            'status' => Admin::STATUS_INACTIVE,
        ]);

        // Attach selected role
        $admin->roles()->sync([$data['role_id']]);

        // Attach mode access
        if (!empty($data['modes'])) {
            $admin->modes()->sync($data['modes']);
        }

        // Generate secure expiring invitation token (48 hours)
        $invitation = AdminInvitation::create([
            'admin_id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'token' => AdminInvitation::generateUniqueToken(),
            'role_id' => $data['role_id'],
            'mode_ids' => $data['modes'] ?? [],
            'expires_at' => Carbon::now()->addHours(48),
            'created_by' => $actor->id,
        ]);

        // Dispatch invitation email
        try {
            Mail::to($admin->email)->send(new AdminInvitationMail($invitation));
        } catch (Exception $e) {
            Log::warning("Failed to send admin invitation email to {$admin->email}: " . $e->getMessage());
        }

        return redirect()->route('admin.admins.index')
            ->with('success', "Administrator '{$admin->name}' invited successfully. An activation email has been dispatched.");
    }

    /**
     * Show the form for editing the specified administrator.
     */
    public function edit(Admin $admin): View
    {
        $actor = auth('admin')->user();

        if (!$admin->canBeModifiedBy($actor)) {
            abort(403, 'Unauthorized. Super Administrator accounts can only be modified by Super Administrators.');
        }

        $roles = Role::active()
            ->when(!$actor->isSuperAdmin(), fn ($q) => $q->where('slug', '!=', 'super-admin'))
            ->orderBy('name', 'asc')
            ->get();

        $modes = Mode::active()->ordered()->get();
        $currentRole = $admin->roles->first();
        $assignedModeIds = $admin->modes->pluck('id')->all();

        return view('admin.admins.edit', compact('admin', 'roles', 'modes', 'currentRole', 'assignedModeIds'));
    }

    /**
     * Update the specified administrator in database.
     */
    public function update(UpdateAdminRequest $request, Admin $admin): RedirectResponse
    {
        $actor = auth('admin')->user();

        if (!$admin->canBeModifiedBy($actor)) {
            abort(403, 'Unauthorized. Super Administrator accounts can only be modified by Super Administrators.');
        }

        $data = $request->validated();

        $admin->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'] ?? $admin->status,
        ]);

        // Sync role
        $admin->roles()->sync([$data['role_id']]);

        // Sync modes
        $admin->modes()->sync($data['modes'] ?? []);

        return redirect()->route('admin.admins.index')
            ->with('success', "Administrator '{$admin->name}' updated successfully.");
    }

    /**
     * Remove the specified administrator from database.
     */
    public function destroy(Admin $admin): RedirectResponse
    {
        $actor = auth('admin')->user();

        // Cannot delete self
        if ($actor->id === $admin->id) {
            return back()->with('error', 'You cannot delete your own administrator account.');
        }

        // Cannot delete Super Admin
        if ($admin->isSuperAdmin()) {
            return back()->with('error', 'Super Administrator accounts cannot be deleted.');
        }

        if (!$admin->canBeModifiedBy($actor)) {
            abort(403, 'Unauthorized. You do not have permission to delete this administrator.');
        }

        $name = $admin->name;
        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', "Administrator '{$name}' has been deleted.");
    }

    /**
     * Resend an invitation with refreshed expiration token.
     */
    public function resendInvitation(Admin $admin): RedirectResponse
    {
        $actor = auth('admin')->user();

        if (!$admin->canBeModifiedBy($actor)) {
            abort(403, 'Unauthorized.');
        }

        // Invalidate older invitations and create a fresh one
        AdminInvitation::where('admin_id', $admin->id)->whereNull('accepted_at')->delete();

        $role = $admin->roles->first();

        $invitation = AdminInvitation::create([
            'admin_id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'token' => AdminInvitation::generateUniqueToken(),
            'role_id' => $role?->id,
            'mode_ids' => $admin->modes->pluck('id')->all(),
            'expires_at' => Carbon::now()->addHours(48),
            'created_by' => $actor->id,
        ]);

        try {
            Mail::to($admin->email)->send(new AdminInvitationMail($invitation));
        } catch (Exception $e) {
            Log::warning("Failed to resend invitation to {$admin->email}: " . $e->getMessage());
        }

        return back()->with('success', "A fresh invitation email has been sent to {$admin->email}.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(Admin $admin, Request $request): RedirectResponse|JsonResponse
    {
        $actor = auth('admin')->user();

        if ($actor->id === $admin->id) {
            return back()->with('error', 'You cannot change your own account status.');
        }

        if ($admin->isSuperAdmin()) {
            return back()->with('error', 'Super Administrator accounts cannot be deactivated.');
        }

        if (!$admin->canBeModifiedBy($actor)) {
            abort(403, 'Unauthorized.');
        }

        $admin->status = ($admin->status === Admin::STATUS_ACTIVE) ? Admin::STATUS_INACTIVE : Admin::STATUS_ACTIVE;
        $admin->save();

        $message = "Administrator '{$admin->name}' status changed to {$admin->status}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $admin->status,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
