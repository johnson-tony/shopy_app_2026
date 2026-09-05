<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\Mode;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Display a listing of administrative roles.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $query = Role::with(['permissions', 'modes'])->withCount('admins');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $roles = $query->orderBy('id', 'asc')->paginate(15)->withQueryString();

        $stats = [
            'total' => Role::count(),
            'active' => Role::where('status', true)->count(),
            'permissions_count' => Permission::count(),
            'modes_count' => Mode::count(),
        ];

        return view('admin.roles.index', compact('roles', 'stats', 'search'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): View
    {
        $permissionsByModule = Permission::groupedByModule();
        $modes = Mode::active()->ordered()->get();

        return view('admin.roles.create', compact('permissionsByModule', 'modes'));
    }

    /**
     * Store a newly created role in database.
     */
    public function store(RoleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $data['status'] = $request->boolean('status', true);

        $role = Role::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
        ]);

        if (!empty($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        if (!empty($data['modes'])) {
            $role->modes()->sync($data['modes']);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): View
    {
        $role->load(['permissions', 'modes']);
        $permissionsByModule = Permission::groupedByModule();
        $modes = Mode::active()->ordered()->get();
        $assignedPermissionIds = $role->permissions->pluck('id')->all();
        $assignedModeIds = $role->modes->pluck('id')->all();

        return view('admin.roles.edit', compact(
            'role',
            'permissionsByModule',
            'modes',
            'assignedPermissionIds',
            'assignedModeIds'
        ));
    }

    /**
     * Update the specified role in database.
     */
    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();

        // Protect super-admin role from losing its identity
        if ($role->slug === 'super-admin') {
            $data['slug'] = 'super-admin';
            $data['status'] = true;
        } elseif (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $role->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'status' => $request->boolean('status', true),
        ]);

        // Sync permissions
        $role->permissions()->sync($data['permissions'] ?? []);

        // Sync modes
        $role->modes()->sync($data['modes'] ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' updated successfully.");
    }

    /**
     * Remove the specified role from database.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if ($role->slug === 'super-admin') {
            return back()->with('error', 'The Super Administrator system role cannot be deleted.');
        }

        if ($role->admins()->exists()) {
            return back()->with('error', "Role '{$role->name}' cannot be deleted because it is assigned to active administrator accounts.");
        }

        $name = $role->name;
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$name}' has been deleted successfully.");
    }
}
