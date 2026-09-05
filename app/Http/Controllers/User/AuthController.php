<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Mail\UserEmailOtpMail;
use App\Models\User;
use App\Models\UserEmailVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the user login view.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }

        return view('user.pages.auth.login');
    }

    /**
     * Handle user login request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::guard('web')->attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $user = Auth::guard('web')->user();

        // Verify account status: blocked or inactive users cannot log in
        if ($user->status === User::STATUS_BLOCKED || $user->status === User::STATUS_INACTIVE) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', "Your account is {$user->status}. Please contact support.");
        }

        // Check if account is pending email verification
        if ($user->status === User::STATUS_PENDING || is_null($user->email_verified_at)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->put('verify_email', $user->email);

            return redirect()->route('verification.notice', ['email' => $user->email])
                ->with('info', 'Please verify your email address to activate your account.');
        }

        if (!$user->isActive()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', "Your account is {$user->status}. Please contact support.");
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', "Welcome back, {$user->name}!");
    }

    /**
     * Display the user registration view.
     */
    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }

        return view('user.pages.auth.register');
    }

    /**
     * Handle user registration request.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'status' => User::STATUS_PENDING,
            'email_verified_at' => null,
        ]);

        $verification = UserEmailVerification::generateForUser($user, 15);

        try {
            Mail::to($user->email)->send(new UserEmailOtpMail($user, $verification->otp, $verification->token));
        } catch (\Throwable $e) {
            Log::error('Failed to send registration OTP email: ' . $e->getMessage());
        }

        $request->session()->put('verify_email', $user->email);

        return redirect()->route('verification.notice', ['email' => $user->email])
            ->with('success', "A 6-digit verification code has been sent to {$user->email}. Please enter the OTP to activate your account.");
    }

    /**
     * Display the OTP verification screen.
     */
    public function showVerifyOtpForm(Request $request): View|RedirectResponse
    {
        if (Auth::guard('web')->check() && Auth::guard('web')->user()->isActive()) {
            return redirect()->route('dashboard');
        }

        $email = $request->query('email') ?? $request->session()->get('verify_email');

        return view('user.pages.auth.verify-otp', compact('email'));
    }

    /**
     * Handle OTP submission (works across devices).
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'exists:users,email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'User not found for this email address.']);
        }

        // If user already active and verified
        if ($user->isActive() && $user->hasVerifiedEmail()) {
            Auth::guard('web')->login($user);
            $request->session()->regenerate();
            return redirect()->route('dashboard')->with('info', 'Your account is already verified.');
        }

        // Find latest unverified verification record
        $verification = UserEmailVerification::where('user_id', $user->id)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$verification || $verification->isExpired()) {
            return back()->withInput($request->only('email'))
                ->withErrors(['otp' => 'The verification code has expired. Please request a new one.']);
        }

        if (!hash_equals($verification->otp, trim($validated['otp']))) {
            return back()->withInput($request->only('email'))
                ->withErrors(['otp' => 'Invalid verification code. Please check and try again.']);
        }

        // Mark verified and activate account
        $verification->markAsVerified();
        $user->update([
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $request->session()->forget('verify_email');

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Your email has been verified successfully! Welcome to Shopy.');
    }

    /**
     * Handle cross-device 1-click verification link.
     */
    public function verifyOtpLink(Request $request, string $token): RedirectResponse
    {
        $verification = UserEmailVerification::where('token', $token)->first();

        if (!$verification) {
            return redirect()->route('login')->with('error', 'Invalid verification link.');
        }

        $user = $verification->user;

        // If already verified
        if ($user->isActive() && $user->hasVerifiedEmail()) {
            Auth::guard('web')->login($user);
            $request->session()->regenerate();
            return redirect()->route('dashboard')->with('info', 'Your account is already verified.');
        }

        if ($verification->isExpired()) {
            return redirect()->route('verification.notice', ['email' => $verification->email])
                ->with('error', 'This verification link has expired. Please request a new verification code.');
        }

        // Mark verified and activate account
        $verification->markAsVerified();
        $user->update([
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $request->session()->forget('verify_email');

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Your email has been verified successfully! You are now logged in.');
    }

    /**
     * Resend verification OTP email.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'exists:users,email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No user found with that email address.']);
        }

        if ($user->isActive() && $user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('info', 'Your account is already verified. Please log in.');
        }

        // Rate limit: 60 seconds cooldown between resends
        $latest = UserEmailVerification::where('user_id', $user->id)->latest()->first();
        if ($latest && $latest->created_at && $latest->created_at->diffInSeconds(now()) < 60) {
            $waitSeconds = 60 - $latest->created_at->diffInSeconds(now());
            return redirect()->route('verification.notice', ['email' => $user->email])
                ->with('error', "Please wait {$waitSeconds} seconds before requesting a new code.");
        }

        $verification = UserEmailVerification::generateForUser($user, 15);

        try {
            Mail::to($user->email)->send(new UserEmailOtpMail($user, $verification->otp, $verification->token));
        } catch (\Throwable $e) {
            Log::error('Failed to resend OTP email: ' . $e->getMessage());
        }

        $request->session()->put('verify_email', $user->email);

        return redirect()->route('verification.notice', ['email' => $user->email])
            ->with('success', 'A new 6-digit verification code has been sent to your email.');
    }

    /**
     * Log out the authenticated user.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
