<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\PartnerLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the partner login view.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::guard('partner')->check()) {
            return redirect()->route('partner.dashboard');
        }

        return view('partner.pages.auth.login');
    }

    /**
     * Handle a partner login request.
     */
    public function login(PartnerLoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::guard('partner')->attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'These credentials do not match our delivery partner records.']);
        }

        $partner = Auth::guard('partner')->user();

        // Verify active status
        if (!$partner->isActive()) {
            Auth::guard('partner')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('partner.login')
                ->with('error', "Your delivery partner account is {$partner->status}. Please contact support.");
        }

        $request->session()->regenerate();

        return redirect()->intended(route('partner.dashboard'))
            ->with('success', "Welcome back, {$partner->name}!");
    }

    /**
     * Log out of the partner portal.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('partner')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('partner.login')->with('success', 'Partner session terminated successfully.');
    }
}