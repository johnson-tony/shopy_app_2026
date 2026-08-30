<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin control panel dashboard.
     */
    public function index(Request $request): View
    {
        $admin = $request->user('admin')->load(['roles.permissions']);

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', User::STATUS_ACTIVE)->count(),
            'total_admins' => Admin::count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
        ];

        $recentAdmins = Admin::with('roles')->latest()->take(5)->get();

        return view('admin.pages.dashboard', compact('admin', 'stats', 'recentAdmins'));
    }
}
