<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * Entry point for a signed impersonation link opened in a NEW tab.
     *
     * The signature is validated by the 'signed' middleware. Here we only log
     * the selected customer into the storefront's own session (its own cookie),
     * so it is fully independent from the admin panel session.
     */
    public function login(User $user): RedirectResponse
    {
        if (!$user->isActive()) {
            return redirect()->route('login')
                ->with('error', "This customer account is {$user->status} and cannot be accessed.");
        }

        Auth::guard('web')->login($user);
        session()->regenerate();
        session()->put('impersonating', true);

        return redirect()->route('dashboard');
    }

    /**
     * End the impersonation session in the storefront tab.
     *
     * Because the admin panel runs in its own tab with its own session, we only
     * need to log the customer out of this storefront session.
     */
    public function stop(Request $request): RedirectResponse|JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'redirect' => route('admin.users.index'),
            ]);
        }

        return redirect()->route('admin.users.index');
    }
}
