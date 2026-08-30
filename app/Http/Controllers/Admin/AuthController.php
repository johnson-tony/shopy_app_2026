<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the admin login view.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->hasAnyRole(['admin', 'super-admin', 'order-manager'])) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.pages.auth.login');
    }

    /**
     * Handle an admin login request.
     */
    public function login(AdminLoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'These credentials do not match our administrator records.']);
        }

        $user = Auth::user();

        // Ensure user has administrative access
        if (!$user->hasAnyRole(['admin', 'super-admin', 'order-manager'])) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->with('error', 'Access denied. You do not have administrator permissions.');
        }

        // Verify active status
        if (!$user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->with('error', "Your administrator account is {$user->status}. Please contact the Super Admin.");
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'))
            ->with('success', "Welcome to the Admin Portal, {$user->name}!");
    }

    /**
     * Log out of the admin portal.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Admin session terminated successfully.');
    }
}
