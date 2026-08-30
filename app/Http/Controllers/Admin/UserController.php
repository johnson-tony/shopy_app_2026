<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a paginated list of storefront users.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.pages.users', compact('users', 'search'));
    }

    /**
     * Directly log in as the storefront customer and redirect straight to their dashboard.
     */
    public function loginAs(User $user): RedirectResponse
    {
        if (!$user->isActive()) {
            return back()->with('error', "Cannot login as {$user->name} because the account is {$user->status}.");
        }

        $impersonateUrl = URL::temporarySignedRoute(
            'impersonate.login',
            now()->addMinutes(5),
            ['user' => $user->id]
        );

        return redirect()->away($impersonateUrl);
    }
}
